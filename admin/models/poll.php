<?php
class Poll extends AppModel
{			
	var $name = 'Poll';
	var $hasMany = array('PollVariant','PollChoice');
	var $validate = array(
	  'name' => VALID_NOT_EMPTY
   );  
}
?>