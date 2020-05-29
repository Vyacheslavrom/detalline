<?php
class CallMessage extends AppModel
{			
var $name = 'CallMessage';
var $validate = array(
	  'text' => VALID_NOT_EMPTY	  
   );  
}
?>