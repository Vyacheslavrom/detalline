<?php
class Product extends AppModel
{			
	var $name = 'Product';
	var $belongsTo = array('Mark', 'Model', 'Category','Price');
	var $hasMany = 'ProductPicture';
	var $validate = array(
	  'price_id' => VALID_NOT_EMPTY,
	 // 'category_id' => VALID_NOT_EMPTY,
	  'name' => VALID_NOT_EMPTY,
	  'year1' => "/(\d+)/",
	  'year2' => "/(\d+)/",
	  'price' => "/[0-9\.]/",
	  'cost_price' => "/[0-9\.]/",
	  'wholesale_price' => "/[0-9\.]/",
	  'price_pers' => "/[0-9\.]/",
	  'wholesale_price_pers' => "/[0-9\.]/",
	  'days_shipping' => "/([0-9\.]+)/",
	  'shipping_info' => VALID_NOT_EMPTY
   );  
}
?>