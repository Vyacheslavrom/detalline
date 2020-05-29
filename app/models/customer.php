<?
class Customer extends AppModel
{
var $name = 'Customer';
var $belongsTo = array('Group','Region');
var $hasMany = array('Order' =>
                         array('className'     => 'Order',
                               'dependent'     => true
                         )
                  );
var $recursive = 5;
var $hasAndBelongsToMany = array('Product' =>
		array('className' => 'Product',
		'joinTable' => 'customers_products'));	
				
var $validate = array(
	  'lname' => VALID_NOT_EMPTY,
	  'fname' => VALID_NOT_EMPTY,
	  'otch' => VALID_NOT_EMPTY,
	  'email' => VALID_EMAIL,
      'password' => '/^[a-z0-9]+$/i',
      'mphone' => VALID_NOT_EMPTY,
      'city' => VALID_NOT_EMPTY,
	  'zipcode' => VALID_NOT_EMPTY,
	  'address1' => VALID_NOT_EMPTY,
	  'address2' => VALID_NOT_EMPTY
   );  
}   
?>