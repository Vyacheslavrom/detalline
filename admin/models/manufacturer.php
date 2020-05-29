<?php
class Manufacturer extends AppModel
{			
	var $name = 'Manufacturer';
	var $validate = array(
	  'name' => VALID_NOT_EMPTY
	);  
}
?>