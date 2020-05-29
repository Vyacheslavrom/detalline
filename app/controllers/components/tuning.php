<?php

//Каталог куда будет выкладывать сгенерированная картинка
define('CACHE_DIR_OUT', ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS);
//Каталог из которого будет браться url картинки
define('CACHE_DIR_URL', ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS);

define('HASH_DELTA', 3914); //случайное смещение кода
define('HASH_FIELDNAME', 'hh'); //название поля с хешем

class TuningComponent extends Object {

var $width = 35; //Высота картинки
var $height = 15; //Ширина картинки

var $transparent = 1; //Прозрачность
var $interlace = false;

var $msg = 'TUNING'; //Отображаемый текст
//Настройка шрифта
var $fonttype = ''; //Вид шрифта, если не задан, то используется системный
var $font = 5; // full path to your font
var $tuningfount;
var $size = 14;
var $rotation = 0;
//Отступ текста от края
var $pad_x = 0;
var $pad_y = 0;

// RGB цвет текста
var $fg_r = 0;
var $fg_g = 0;
var $fg_b = 0;
// RGB цвет фона
var $bg_r = 255;
var $bg_g = 255;
var $bg_b = 255;

//Признаки вывода помех
var $ShowDot = true;
var $ShowFig = false;

//Для хеша
var $hashcode; //сам код
var $hashvalue; //его зашифрованное значение
var $hashfield; //текст поля, для удобства при частом использовании

 function TuningComponent(){
 //эту проверку можно убрать, если будут использоваться только системные шрифты
 if (!function_exists('imagettftext')){
 die('Your server does not have FreeType installed in php.'.
 ' True Type Fonts (.ttf) are not supported');
 }
 $this->tuningfount = $this->font;
 }

 //генерируем код, на вход подается диапазон, в котором будет выбираться случайное число и
 //имя поля формы с кодом, для которой строится html код
 function GetCode($hmin=1000, $hmax=9999, $fieldname=HASH_FIELDNAME){
 //сам код
 $this->hashcode = rand($hmin, $hmax);
 //получаем хеш трансформированного числа
 $this->hashvalue = md5($this->hashcode + HASH_DELTA);
 $this->hashfield = '';
 }

 //проверяем корректность полученного кода и его хеша
 function CheckCode($code, $hash){
 return ($hash == md5($code + HASH_DELTA));
 }

 function DrawImage() {
 $image = ImageCreate($this->width+($this->pad_x*2),$this->height+($this->pad_y*2));
 // Цвет фона
 $bg = ImageColorAllocate($image, $this->bg_r, $this->bg_g, $this->bg_b);
 // Цвет текста
 $fg = ImageColorAllocate($image, $this->fg_r, $this->fg_g, $this->fg_b);

 if ($this->transparent)
 ImageColorTransparent($image, $bg);

 ImageInterlace($image, $this->interlace);

 if ($this->fonttype == 'ttf'){ //для TrueType шрифтов
 ImageTTFText($image, $this->size, $this->rotation,
 $this->pad_x, $this->pad_y, $fg, $this->font, $this->msg);
 } else { //Системный шрифт
 ImageString($image, $this->tuningfount, $this->pad_x, $this->pad_y, $this->msg, $fg);
 }

 //Вносим в изображение шум
 if ($this->ShowFig){
 $dc = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
 ImageRectangle($image, rand(0, $this->width/2 ), rand(0, $this->height/2 ),
 rand($this->width / 2, $this->width) ,rand($this->height / 2, $this->height), $dc);
 $dc = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
 ImageRectangle($image, rand(0, $this->width/2 ), rand(0, $this->height/2 ),
 rand($this->width / 2, $this->width) ,rand($this->height / 2, $this->height), $dc);
 }

 //Шумы в виде точек
 if ($this->ShowDot){
 for($i = $this->width * $this->height / 10; $i >= 0;$i--) {
 ImageSetPixel($image, rand(0,$this->width), rand(0,$this->height),
 ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255)));
 }
 }

 //файл в который производится вывод картинки, для вывода сразу на экран
 //можно использовать вызов ImagePNG($image)
 $fname = 't.png';
 ImagePNG($image, CACHE_DIR_OUT.$fname);
 ImageDestroy($image);
 //url картинки
 return CACHE_DIR_URL.$fname;
 }
}

function checktuning($tuning, $hash){
 require_once('tuning.php');
 $t = &new TProtectCode;
 if (!$t->CheckCode($tuning, $hash)){
// echo "Неправильно введен защитный код !";
 return false;
 }
 return true;
}

/*

Описанный класс очень прост в использовании и может применять многократно в проекте, с помощью всего лишь нескольких вызвовов. Рассмотрим примеры использования модуля tuning.php (фрагменты кода). Для получения самого кода и url картинки может использоваться код такого типа:
require_once('tuning.php');
$t = new TProtectCode;
$t->width = 35;
$t->height = 15;
$t->GetCode();
$t->msg = $t->hashcode;
$tuning_url = $t->DrawImage();
$tuning = 0;

Вывод картинку с защитным кодом в некоторой форме, можно организовать так:
<form>
<table>
<tr>
<td>*Код:</td>
<td align=left>
<img src='{$tuning_url}' border=0 width=35 height=15></a>
<input style="width: 100px;" type=text name=tuning maxlength=4 value="{$tuning}">{$t->hashfield}
</td>
</tr>
<table>
</form>

Для проверки корректности кода удобно использовать отдельную функцию:
function checktuning($tuning, $hash){
 require_once('tuning.php');
 $t = &new TProtectCode;
 if (!$t->CheckCode($tuning, $hash)){
 echo "Неправильно введен защитный код !";
 return false;
 }
 return true;
}

Чтобы считать переданные из формы значения кода и хеша:
$this->tuning = isset($_POST['tuning']) ? intval($_POST['tuning']) : 0;
$this->page = isset($_GET['page']) ? intval($_GET['page']) : 1;

*/
?>
