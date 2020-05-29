<?php

class SemanticComponent extends Object
{
  var $_1_2  = array(1=>"���� ", "��� ");
  var $_1_19 = array(1=>"���� ","��� ","��� ","������ ","���� ","����� ","���� ","������ ","������ ","������ ",
                          "���������� ","���������� ","���������� ","������������ ","���������� ","����������� ","���������� ",
                          "������������ ","������������ ");
  var $des = array(2=>"�������� ", "�������� ","����� ","��������� ","���������� ","��������� ","���������� ","��������� "); 
  var $hang = array(1=>"��� ", "������ ","������ ","��������� ","������� ","�������� ","������� ","��������� ","��������� "); 
  var $namerub = array(1=>"����� ","����� ","������ ");  
  var $nametho=array(1=>"������ ","������ ","����� ");
  var $namemil=array(1=>"������� ","�������� ","��������� ");  
  var $namemrd=array(1=>"�������� ","��������� ","���������� ");  
  var $kopeek=array(1=>"������� ","������� ","������ ");  
  var $sb=array("�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�");

  function semantic($i,&$words,&$fem,$f){ 

    $words=""; 
    $fl=0; 
    if($i >= 100){ 
    $jkl = intval($i / 100); 
    $words.=$this->hang[$jkl]; 
    $i%=100; 
    } 
    if($i >= 20){ 
    $jkl = intval($i / 10); 
    $words.=$this->des[$jkl]; 
    $i%=10; 
    $fl=1; 
    } 
    switch($i){ 
    case 1: $fem=1; break; 
    case 2: 
    case 3: 
    case 4: $fem=2; break; 
    default: $fem=3; break; 
    } 
    if( $i ){ 
    if( $i < 3 && $f > 0 ){ 
    if ( $f >= 2 ) { 
    $words.=$this->_1_19[$i]; 
    } 
    else { 
    $words.=$this->_1_2[$i]; 
    } 
    } 
    else { 
    $words.=$this->_1_19[$i]; 
    } 
    } 
  } 


  function num2str($L){ 
   
    $s=" "; 
    $s1=" "; 
    $s2=" "; 
    $kop=intval( ( $L*100 - intval( $L )*100 )); 
    $L=intval($L); 
    if($L>=1000000000){ 
    $many=0; 
    $this->semantic(intval($L / 1000000000),$s1,$many,3); 
    $s.=$s1.$this->namemrd[$many]; 
    $L%=1000000000; 
    } 

    if($L >= 1000000){ 
    $many=0; 
    $this->semantic(intval($L / 1000000),$s1,$many,2); 
    $s.=$s1.$this->namemil[$many]; 
    $L%=1000000; 
    if($L==0){ 
    $s.="������ "; 
    } 
    } 

    if($L >= 1000){ 
    $many=0; 
    $this->semantic(intval($L / 1000),$s1,$many,1); 
    $s.=$s1.$this->nametho[$many]; 
    $L%=1000; 
    if($L==0){ 
    $s.="������ "; 
    } 
    } 

    if($L != 0){ 
    $many=0; 
    $this->semantic($L,$s1,$many,0); 
    $s.=$s1.$this->namerub[$many]; 
    } 

    if($kop > 0){ 
    $many=0; 
    $this->semantic($kop,$s1,$many,1); 
    $s.=$s1.$this->kopeek[$many]; 
    } 
    else { 
    $s.=" 00 ������"; 
    } 
    $s=trim($s);
    $ss=substr($s,0,1);
    $ss=$this->sb[$ss];
    $s=$ss.substr($s,1);

    return $s; 
  } 

}