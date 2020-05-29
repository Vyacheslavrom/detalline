<?php
class Delivery extends AppModel
{			
	var $name = 'Delivery';
	var $hasMany = array('Order');
}
?>