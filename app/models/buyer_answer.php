<?php
class BuyerAnswer extends AppModel
{			
	var $name = 'BuyerAnswer';
       var $belongsTo = array('Supplier'=>array('className'    => 'Customer', 'foreignKey'    => 'supplier_id'));
}
?>