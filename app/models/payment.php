<?php
class Payment extends AppModel
{			
	var $name = 'Payment';
	var $hasMany = array('Order');
}
?>