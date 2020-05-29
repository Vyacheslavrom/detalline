<?php
class RequestAnswer extends AppModel
{			
	var $name = 'RequestAnswer';
       var $belongsTo = array('Supplier'=>array('className'    => 'Customer', 'foreignKey'    => 'supplier_id'));
}
?>