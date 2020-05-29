<?php
class Order extends AppModel
{			
var $name = 'Order';
var $belongsTo = array('Customer','Status','Payment','Delivery','Region');
var $recursive = 5;  
var $hasMany = array('OrderProduct');
var $hasAndBelongsToMany = array('PrevStatus' =>
                               array('className'    => 'Status',
                                     'joinTable'    => 'orders_statuses',
                                     'foreignKey'   => 'order_id',
                                     'associationForeignKey'=> 'status_id',
                                     'finderQuery' =>
'SELECT `PrevStatus`.`id`, `PrevStatus`.`name`, `orders_statuses`.`stage_date` as `stage_date`, `orders_statuses`.`order_id` as `order_id`, `orders_statuses`.`status_id` as `status_id`, `orders_statuses`.`product` as `product`
 FROM `statuses` AS `PrevStatus` 
JOIN `orders_statuses` ON (`orders_statuses`.`order_id` =  {$__cakeID__$}  AND `orders_statuses`.`status_id` = `PrevStatus`.`id`) 
WHERE 1 = 1'
                               )
                               );

	
function skidka($sum){
	if($sum>=10000 && $sum<25000) return 2;
	if($sum>=25000 && $sum<50000) return 3.5;
	if($sum>=50000 && $sum<75000) return 7.5;
	if($sum>=75000 && $sum<100000) return 10;
	if($sum>=100000 && $sum<150000) return 12;
	if($sum>=150000 && $sum<200000) return 13;
	if($sum>=200000 && $sum<250000) return 14;
	if($sum>=250000) return 15;
	return 0;
}
}
?>