<?php
class PriceParamName extends AppModel
{			
	var $name = 'PriceParamName';
	var $hasMany = array('PriceParamValue');
}
?>