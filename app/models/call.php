<?php
class Call extends AppModel
{			
var $name = 'Call';
var $hasMany = array('CallMessage');	
var $validate = array(
	  'name' => VALID_NOT_EMPTY,
	  'phone' => VALID_NOT_EMPTY
   );  
}
?>