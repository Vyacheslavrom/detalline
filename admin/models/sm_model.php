<?php
class SmModel extends AppModel
{			
	var $name = 'Model';
	var $useTable = 'models';
	var $belongsTo = array('Mark');
        var $hasMany = array(
    'ModelAnalog' => array(
    'className' => 'ModelAnalog',
    'foreignKey' => 'model_id',
    'dependent'=> true
    ), 'ModelException' => array(
    'className' => 'ModelException',
    'foreignKey' => 'model_id',
    'dependent'=> true
    ), 'ModelModification' => array(
    'className' => 'ModelModification',
    'foreignKey' => 'model_id',
    'dependent'=> true
    )
);

	var $validate = array(
	  'mark_id' => VALID_NOT_EMPTY,
	  'name' => VALID_NOT_EMPTY
	);  

}
?>