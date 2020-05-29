<?
class Customer extends AppModel
{
var $name = 'Customer';
var $belongsTo = array('Group','Region');
var $recursive = 5;
var $hasAndBelongsToMany = array('Product' =>
		array('className' => 'Product',
		'joinTable' => 'customers_products'));	
		
var $hasMany = array('Order' =>
                         array('className'     => 'Order',
                               'dependent'     => true
                         )
                  );
		
var $validate = array(
	  'lname' => VALID_NOT_EMPTY,
	  'fname' => VALID_NOT_EMPTY,
	  'email' => VALID_EMAIL,
      'password' => '/^[a-z0-9]+$/i',
      'zipcode' => VALID_NOT_EMPTY,  
	   'address1' => VALID_NOT_EMPTY,
      'city' => VALID_NOT_EMPTY 	  
   );  
   
function afterFind($results) {

		foreach($results as $key=>$val) {	
			if(isset($val['Customer']['birthday']) && $val['Customer']['birthday']!='0000-00-00 00:00:00') {
				$birthday = strtotime($val['Customer']['birthday']); 
				$results[$key]["Customer"]["birthday_day"] = date("d",$birthday);
				$results[$key]["Customer"]["birthday_month"] = date("m",$birthday);
				$results[$key]["Customer"]["birthday_year"] = date("Y",$birthday);
			}
		}		
		return $results;
	}  
}
?>