<?php
class Order extends AppModel
{			
var $name = 'Order';
var $belongsTo = array('Customer','Status','Payment','Delivery','Region');
var $hasMany = array('OrderProduct');	

var $validate = array(
	  'lname' => VALID_NOT_EMPTY,
	  'fname' => VALID_NOT_EMPTY,
	  'otch' => VALID_NOT_EMPTY,
      'zipcode' => VALID_NOT_EMPTY,  
	  'address1' => VALID_NOT_EMPTY,
      'city' => VALID_NOT_EMPTY	    	    	  
   );  

function skidka($sum){
	if($sum>=10000 && $sum<25000) return 2;
	if($sum>=25000 && $sum<50000) return 3.5;
	if($sum>=50000 && $sum<75000) return 7.5;
	if($sum>=75000 && $sum<100000) return 10;
	if($sum>=100000 && $sum<150000) return 12;
	if($sum>=150000 && $sum<200000) return 13;
	if($sum>=200000 && $sum<250000) return 14;
	if($sum>=250000) return 15;
	return 0;
}
}
?>