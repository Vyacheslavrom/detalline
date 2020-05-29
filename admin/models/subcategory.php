<?php
class Subcategory extends AppModel
{			
	var $name = 'Subcategory';
	var $belongsTo = array('Category');
       
var $hasMany = array(
    'SubcategoryAnalog' => array(
    'className' => 'SubcategoryAnalog',
    'foreignKey' => 'subcategory_id',
    'dependent'=> true
    ), 'SubcategoryException' => array(
    'className' => 'SubcategoryException',
    'foreignKey' => 'subcategory_id',
    'dependent'=> true
    )
);

	var $validate = array(
	  'category_id' => VALID_NOT_EMPTY,
	  'name' => VALID_NOT_EMPTY,
	  'description' => VALID_NOT_EMPTY
	);  
}
?>