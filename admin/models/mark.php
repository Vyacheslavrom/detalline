<?php
class Mark extends AppModel
{			
	var $name = 'Mark';
        var $hasMany = array(
    'MarkAnalog' => array(
    'className' => 'MarkAnalog',
    'foreignKey' => 'mark_id',
    'dependent'=> true
    )
);
	var $validate = array(
	  'name' => VALID_NOT_EMPTY
	);  
}
?>