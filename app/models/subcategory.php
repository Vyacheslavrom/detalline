<?php
class Subcategory extends AppModel
{			
	var $name = 'Subcategory';
	var $belongsTo = array('Category');
}
?>