<?
class OrderProduct extends AppModel
{
	var $name = 'OrderProduct';
	var $belongsTo = array('Order','RequestAnswer','Price');
}
?>