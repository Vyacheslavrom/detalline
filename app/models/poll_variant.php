<?php
class PollVariant extends AppModel
{			
	var $name = 'PollVariant';
	var $hasMany = 'PollChoice';
	var $validate = array(
	  'name' => VALID_NOT_EMPTY
   );  
}
?>