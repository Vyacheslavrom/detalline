<?php

//������� ���� ����� ����������� ��������������� ��������
define('CACHE_DIR_OUT', ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS);
//������� �� �������� ����� ������� url ��������
define('CACHE_DIR_URL', ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS);

define('HASH_DELTA', 3914); //��������� �������� ����
define('HASH_FIELDNAME', 'hh'); //�������� ���� � �����

class TuningComponent extends Object {

var $width = 35; //������ ��������
var $height = 15; //������ ��������

var $transparent = 1; //������������
var $interlace = false;

var $msg = 'TUNING'; //������������ �����
//��������� ������
var $fonttype = ''; //��� ������, ���� �� �����, �� ������������ ���������
var $font = 5; // full path to your font
var $tuningfount;
var $size = 14;
var $rotation = 0;
//������ ������ �� ����
var $pad_x = 0;
var $pad_y = 0;

// RGB ���� ������
var $fg_r = 0;
var $fg_g = 0;
var $fg_b = 0;
// RGB ���� ����
var $bg_r = 255;
var $bg_g = 255;
var $bg_b = 255;

//�������� ������ �����
var $ShowDot = true;
var $ShowFig = false;

//��� ����
var $hashcode; //��� ���
var $hashvalue; //��� ������������� ��������
var $hashfield; //����� ����, ��� �������� ��� ������ �������������

 function TuningComponent(){
 //��� �������� ����� ������, ���� ����� �������������� ������ ��������� ������
 if (!function_exists('imagettftext')){
 die('Your server does not have FreeType installed in php.'.
 ' True Type Fonts (.ttf) are not supported');
 }
 $this->tuningfount = $this->font;
 }

 //���������� ���, �� ���� �������� ��������, � ������� ����� ���������� ��������� ����� �
 //��� ���� ����� � �����, ��� ������� �������� html ���
 function GetCode($hmin=1000, $hmax=9999, $fieldname=HASH_FIELDNAME){
 //��� ���
 $this->hashcode = rand($hmin, $hmax);
 //�������� ��� ������������������� �����
 $this->hashvalue = md5($this->hashcode + HASH_DELTA);
 $this->hashfield = '';
 }

 //��������� ������������ ����������� ���� � ��� ����
 function CheckCode($code, $hash){
 return ($hash == md5($code + HASH_DELTA));
 }

 function DrawImage() {
 $image = ImageCreate($this->width+($this->pad_x*2),$this->height+($this->pad_y*2));
 // ���� ����
 $bg = ImageColorAllocate($image, $this->bg_r, $this->bg_g, $this->bg_b);
 // ���� ������
 $fg = ImageColorAllocate($image, $this->fg_r, $this->fg_g, $this->fg_b);

 if ($this->transparent)
 ImageColorTransparent($image, $bg);

 ImageInterlace($image, $this->interlace);

 if ($this->fonttype == 'ttf'){ //��� TrueType �������
 ImageTTFText($image, $this->size, $this->rotation,
 $this->pad_x, $this->pad_y, $fg, $this->font, $this->msg);
 } else { //��������� �����
 ImageString($image, $this->tuningfount, $this->pad_x, $this->pad_y, $this->msg, $fg);
 }

 //������ � ����������� ���
 if ($this->ShowFig){
 $dc = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
 ImageRectangle($image, rand(0, $this->width/2 ), rand(0, $this->height/2 ),
 rand($this->width / 2, $this->width) ,rand($this->height / 2, $this->height), $dc);
 $dc = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
 ImageRectangle($image, rand(0, $this->width/2 ), rand(0, $this->height/2 ),
 rand($this->width / 2, $this->width) ,rand($this->height / 2, $this->height), $dc);
 }

 //���� � ���� �����
 if ($this->ShowDot){
 for($i = $this->width * $this->height / 10; $i >= 0;$i--) {
 ImageSetPixel($image, rand(0,$this->width), rand(0,$this->height),
 ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255)));
 }
 }

 //���� � ������� ������������ ����� ��������, ��� ������ ����� �� �����
 //����� ������������ ����� ImagePNG($image)
 $fname = 't.png';
 ImagePNG($image, CACHE_DIR_OUT.$fname);
 ImageDestroy($image);
 //url ��������
 return CACHE_DIR_URL.$fname;
 }
}

function checktuning($tuning, $hash){
 require_once('tuning.php');
 $t = &new TProtectCode;
 if (!$t->CheckCode($tuning, $hash)){
// echo "����������� ������ �������� ��� !";
 return false;
 }
 return true;
}

/*

��������� ����� ����� ����� � ������������� � ����� ��������� ����������� � �������, � ������� ����� ���� ���������� ��������. ���������� ������� ������������� ������ tuning.php (��������� ����). ��� ��������� ������ ���� � url �������� ����� �������������� ��� ������ ����:
require_once('tuning.php');
$t = new TProtectCode;
$t->width = 35;
$t->height = 15;
$t->GetCode();
$t->msg = $t->hashcode;
$tuning_url = $t->DrawImage();
$tuning = 0;

����� �������� � �������� ����� � ��������� �����, ����� ������������ ���:
<form>
<table>
<tr>
<td>*���:</td>
<td align=left>
<img src='{$tuning_url}' border=0 width=35 height=15></a>
<input style="width: 100px;" type=text name=tuning maxlength=4 value="{$tuning}">{$t->hashfield}
</td>
</tr>
<table>
</form>

��� �������� ������������ ���� ������ ������������ ��������� �������:
function checktuning($tuning, $hash){
 require_once('tuning.php');
 $t = &new TProtectCode;
 if (!$t->CheckCode($tuning, $hash)){
 echo "����������� ������ �������� ��� !";
 return false;
 }
 return true;
}

����� ������� ���������� �� ����� �������� ���� � ����:
$this->tuning = isset($_POST['tuning']) ? intval($_POST['tuning']) : 0;
$this->page = isset($_GET['page']) ? intval($_GET['page']) : 1;

*/
?>
