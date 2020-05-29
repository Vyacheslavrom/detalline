<?php
class Price extends AppModel
{			
	var $name = 'Price';
	var $belongsTo = array('Param1' =>
                           array('className'  => 'PriceParamValue',
                                 'foreignKey' => 'param1_id'
                           ),
						   'Param2' =>
                           array('className'  => 'PriceParamValue',
                                 'foreignKey' => 'param2_id'
                           ),
						   'Param3' =>
                           array('className'  => 'PriceParamValue',
                                 'foreignKey' => 'param3_id'
                           ),
						   'Param4' =>
                           array('className'  => 'PriceParamValue',
                                 'foreignKey' => 'param4_id'
                           ),
						   'Param5' =>
                           array('className'  => 'PriceParamValue',
                                 'foreignKey' => 'param5_id'
                           )
                     );
	var $validate = array(
	'id' => '/^[0-9]+$/i',
	'name' => VALID_NOT_EMPTY,
	  'param1_id' => VALID_NOT_EMPTY,
	  'param2_id' => VALID_NOT_EMPTY,
	  'param3_id' => VALID_NOT_EMPTY,
	  'param4_id' => VALID_NOT_EMPTY,
	  'param5_id' => VALID_NOT_EMPTY,
	  'manuf_number_csv' => '/^[0-9]+$/i',
	  'name_csv' => '/^[0-9]+$/i',
	  'price_csv' => '/^[0-9]+$/i',
	  'price_pers' => '/^[0-9\.]+$/i',
	  'quantity_csv' => '/^[0-9,]+$/i'
   );  
}
?>