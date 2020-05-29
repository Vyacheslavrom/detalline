<?php
class ReverseMessage extends AppModel
{			
var $name = 'ReverseMessage';
var $validate = array(
	  'text' => VALID_NOT_EMPTY	  
   );  
}
?>