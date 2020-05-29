<?php
class Movement extends AppModel
{			
var $name = 'Movement';
var $belongsTo = array('Customer','MovementDirection','MovementObject');

var $validate = array(
	  'movement_direction_id' => VALID_NOT_EMPTY,
	  'movement_object_id' => VALID_NOT_EMPTY,
	  'customer_id' => VALID_NOT_EMPTY,
      'sum' => '/^[0-9]+\.?[0-9]*$/'   	    	  
   );  
}
?>