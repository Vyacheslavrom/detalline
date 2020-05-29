<?php
class Message extends AppModel
{			
	var $name = 'Message';
	var $validate = array(
	  'subject' => VALID_NOT_EMPTY,
	  'body' => VALID_NOT_EMPTY,
	  'fio' => VALID_NOT_EMPTY,
	  'email' => VALID_EMAIL
   );  
}
?>