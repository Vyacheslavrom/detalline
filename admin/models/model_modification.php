<?
class ModelModification extends AppModel
{
	var $name = 'ModelModification';
	var $belongsTo = array('Model');
	
	var $validate = array(
	  'name' => '/^([a-zA-Z0-9\-\(\) ]+)$/',
	  'year_min' => '/^([0-9]{4})$/',
	  'year_max' => '/^([0-9]{4})$/'
	);  
}
?>