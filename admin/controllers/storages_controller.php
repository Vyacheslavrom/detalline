<?php
uses('sanitize');

class StoragesController extends AppController
{
	var $name = 'Storages';
	var $helpers = array('Html', 'Pagination');
	var $uses = array("Storage","Order","OrderProduct","RequestAnswer","StorageNumber","StorageColor","Product","HistoryStorage","Price");

	function __construct() {
		$this->show = empty($_GET['show'])? '10': Sanitize::paranoid($_GET['show']);
		$this->showt = empty($_GET['show'])? '100': Sanitize::paranoid($_GET['show']);
		$this->sortBy = empty($_GET['sort'])? 'created': Sanitize::paranoid($_GET['sort']);
		$this->direction = empty($_GET['direction'])? 'DESC': Sanitize::paranoid($_GET['direction']);
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = 'Order.'.$this->sortBy.' '.strtoupper($this->direction);		
		$this->storage = 'Storage.id '.strtoupper($this->direction);		
	}
	
	function index(){
	
	/*if(!empty($_GET['upp'])){
		$storages = $this->Storage->findAll();
		foreach($storages as $storage){
			$upp['Storage']['id'] = $storage['Storage']['id'];
			$upp['Storage']['complect_number'] = $storage['Storage']['id'];
			$this->Storage->save($upp);
		}
	}*/
	
		$this->set('title','Склады');
		$this->set("payments",$this->Order->Payment->findAll());
		
		if(!empty($_GET['del_prod']))
		{
			$this->OrderProduct->del((int)$_GET['del_prod']);
			
			$this->redirect('/'.@$this->params['url']['url'].'?cust='.@$_GET['cust'].'&insert_date_from='.@$_GET['insert_date_from'].'&insert_date_to='.@$_GET['insert_date_to'].'&page='.@$_GET['page']);
		}
		
		if($_POST['save_storage']){
			$storageNumber['StorageNumber']['storage']=$this->StorageNumber->findCount();
			$storageNumber['StorageNumber']['name']=$_POST['storage_name'];
			$storageNumber['StorageNumber']['status']=$_POST['storage_status'];
			$storageNumber['StorageNumber']['price_id']=$_POST['storage_price_id'];
			$this->StorageNumber->save($storageNumber);
		}
		if($_POST['storage_delete']){
			$storageNumber['StorageNumber']['id']=$_POST['storage_id'];
			$storageNumber['StorageNumber']['visible']=0;
			$this->StorageNumber->save($storageNumber);
		}
		if($_POST['save_red_storage']){
			$storageNumber['StorageNumber']['id']=$_POST['storage_red_id'];
			$storageNumber['StorageNumber']['status']=$_POST['storage_status_red'];
			$storageNumber['StorageNumber']['price_id']=$_POST['storage_price_id_red'];
			$this->StorageNumber->save($storageNumber);
		}
		
			if(isset($_POST['save_color_storage'])){
				$colors = $this->StorageColor->findAll('WHERE price_id='.$_POST['color_price_id']);
				if(!empty($colors)){
					foreach($colors as $color){
						$save_color['StorageColor']['price_id'] = $color['StorageColor']['price_id'];
						$save_color['StorageColor']['id'] = $color['StorageColor']['id'];
					}
				}
				else{
					$save_color['StorageColor']['price_id'] = $_POST['color_price_id'];
				}
				$save_color['StorageColor']['color'] = $_POST['storage_color'];
				
				$this->StorageColor->save($save_color);
			}
		
		if($_POST['save']) {
		
			$order = $this->Order->read(null, $_POST["order_id"]);
			$customer = $this->Order->Customer->read(null, $order['Order']['customer_id']);
			
			foreach($_POST as $key=>$val) {
			
				$matches = array();
				if(preg_match('/^id_product_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$check=$val;
				}
				if($check==1){
				
					if(preg_match('/^id_storage_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$orprid = $matches[1];		
						$stid = $val;
					}
					if(preg_match('/^id_order_(\d+)$/i', $key, $matches) && $_POST['save']) {		
						$orid = $val;
					}
					if(preg_match('/^id_customer_(\d+)$/i', $key, $matches) && $_POST['save']) {	
						$cusid = $val;
					}
				
					if(preg_match('/^delayed(\d+)_(\d+)$/i', $key, $matches)) {
						$data[$matches[2]]["OrderProduct"]["id"] = $matches[2];
						$data[$matches[2]]["OrderProduct"]["delayed".$matches[1]] = $val;
					}
				
					if(preg_match('/^price_id_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["price_id"] = $val;
						$prid = $val;
					}
					
					if(preg_match('/^name_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["name"] = $val;
						$name = $val;
					}
					
					if(preg_match('/^manufacturer_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["manufacturer"] = $val;
						$mnf = $val;
					}
					
					if(preg_match('/^mark_and_model_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["mark_and_model"] = $val;
						$mrkmd = $val;
					}
					
					if(preg_match('/^year1_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["year1"] = $val;
						$ya1 = $val;
					}
					
					if(preg_match('/^year2_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["year2"] = $val;
						$ya2 = $val;
					}
					
					if(preg_match('/^number_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["number"] = $val;
						$nmb = $val;
					}
					
					if(preg_match('/^shipping_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["shipping"] = $val;
						$shpi = $val;
					}
					
					if(preg_match('/^articul_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["articul"] = $val;
						$arti = $val;
					}
					
					if(preg_match('/^description_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["description"] = $val;
						$desc = $val;
					}
					
					if(preg_match('/^comment_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["comment"] = $val;
					}
					
					if(preg_match('/^count_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
						$data[$matches[1]]["OrderProduct"]["count"] = $val;
					}
					
					if(preg_match('/^complect_number_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$complect_number = $val;
					}
					
					if(preg_match('/^order_complect_id_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$order_complect_id = explode(',',$val);
					}
					
					if(preg_match('/^where_(\d+)$/i', $key, $matches) && $_POST['save']) {
						$stors = $this->StorageNumber->findAll('WHERE storage='.$val);
						foreach($stors as $stor){
							$wstatus = $stor['StorageNumber']['status'];
							$where = $stor['StorageNumber']['storage'];
						}
					}
				}
			}
			
			foreach($data as $key=>$val) {
					$i=0;$n=1;$count=0;
					while($i<$n){
					if(!empty($order_complect_id[$i])){
						$n++;
						if(!empty($_POST['product_id_'.$order_complect_id[$i]])) $count++;
					}	
					$i++;
					}
					switch($wstatus){
						case 0:{$val["OrderProduct"]["status1"]=0;$data[$matches[1]]["OrderProduct"]["status2"]=0;$val["OrderProduct"]["status3"]=0;$val["OrderProduct"]["status4"]=0;$val["OrderProduct"]["status5"]=0;$val["OrderProduct"]["status6"]=0;break;}
						case 1:{$val["OrderProduct"]["status1"]=$count;$val["OrderProduct"]["status2"]=0;$val["OrderProduct"]["status3"]=0;$val["OrderProduct"]["status4"]=0;$val["OrderProduct"]["status5"]=0;$val["OrderProduct"]["status6"]=0;break;}
						case 2:{$val["OrderProduct"]["status2"]=$count;$val["OrderProduct"]["status3"]=0;$val["OrderProduct"]["status4"]=0;$val["OrderProduct"]["status5"]=0;$val["OrderProduct"]["status6"]=0;break;}
						case 3:{$val["OrderProduct"]["status3"]=$count;$val["OrderProduct"]["status4"]=0;$val["OrderProduct"]["status5"]=0;$val["OrderProduct"]["status6"]=0;break;}
						case 4:{$val["OrderProduct"]["status4"]=$count;$val["OrderProduct"]["status5"]=0;$val["OrderProduct"]["status6"]=0;break;}
						case 5:{$val["OrderProduct"]["status5"]=$count;$val["OrderProduct"]["status6"]=0;break;}
						case 6:{$val["OrderProduct"]["status6"]=$count;$val["OrderProduct"]["status5"]=$count;break;}
						case 7:{$val["OrderProduct"]["decline"]=1;break;}
					}
					$this->OrderProduct->save($val);
					$storages = $this->Storage->findAll('WHERE order_product_id = '.$val['OrderProduct']['id']);
					foreach($storages as $storage){
						$storage['Storage']['price_id'] = $val['OrderProduct']['price_id'];
						$storage['Storage']['name'] = $val['OrderProduct']['name'];
						$storage['Storage']['articul'] = $val['OrderProduct']['articul'];
						$storage['Storage']['manuf_number'] = $val['OrderProduct']['number'];
						$this->Storage->save($storage);
					}
			}
			$complect_number_new=mysql_fetch_row(mysql_query("SELECT max(complect_number) FROM storages"));
			$i=0;$n=1;
			if(!empty($_GET['storage'])) $old_where=$_GET['storage'];
			else $old_where=0;
			while($i<$n){
			if(!empty($order_complect_id[$i])){
				$n++;
				
				$storage['Storage']['id']=$order_complect_id[$i];
				$storage['Storage']['order_id']=$orid;
				$storage['Storage']['order_product_id']=$orprid;
				$storage['Storage']['name']=$name;
				$storage['Storage']['price_id']=$prid;
				$storage['Storage']['customer_id']=$cusid;
				if(empty($_POST['product_id_'.$order_complect_id[$i]])){
					$storage['Storage']['complect_number']=$complect_number_new[0]+1;
					$storage['Storage']['storage']=$old_where;
				}
				else{
					$storage['Storage']['storage']=$where;
					$storage['Storage']['complect_number']=$complect_number;
					$history_storage['HistoryStorage']['storage_id'] = $order_complect_id[$i];
					$history_storage['HistoryStorage']['storage_num'] = $where;
					$history_storage['HistoryStorage']['date'] = date('Y-m-d h:i:s');
					$this->HistoryStorage->save($history_storage);
				}
				if($wstatus!=7){ 
					mysql_query("DELETE FROM orders_statuses WHERE order_id =".$orid." AND status_id > ".$wstatus);
					mysql_query("INSERT INTO orders_statuses(order_id, status_id,product, stage_date) VALUES ('".$orid."','".++$wstatus."','".$orprid."','".date('Y-m-d H:i:s')."');");
				}
				if($storage['Storage']['price_id'] >= 100 && $storage['Storage']['price_id'] <= 200){
					if($wstatus > 4){
						$product_quantity = mysql_fetch_row(mysql_query("SELECT id,quantity FROM products WHERE price_id = ".$storage['Storage']['price_id']." AND name LIKE '".$storage['Storage']['name']."' AND articul LIKE '".$arti."'"));
						$product['Product']['id'] = $product_quantity[0];
						//$product['Product']['quantity'] = --$product_quantity[1];
						$this->Product->save($product);
					}
				}else{
					if($wstatus == 1 || $wstatus == 5){
						$product_quantity = mysql_fetch_row(mysql_query("SELECT id,quantity FROM products WHERE description LIKE '".$desc."' AND number LIKE '".$nmb."' AND mark_and_model LIKE '".$mrkmd."' AND name LIKE '".$storage['Storage']['name']."' AND manufacturer LIKE '".$mnf."' AND price_id = 106 AND articul LIKE '".$arti."'"));
						$products['Product']['id'] = $product_quantity[0];
						$products['Product']['quantity'] = 1 + $product_quantity[1];
						$products['Product']['year1'] = $ya1;
						$products['Product']['year2'] = $ya2;
						$products['Product']['description'] = $desc;
						$products['Product']['days_shipping'] = $shpi;
						$products['Product']['number'] = $nmb;
						$products['Product']['mark_and_model'] = $mrkmd;
						$products['Product']['name'] = $storage['Storage']['name'];
						$products['Product']['manufacturer'] = $mnf;
						$products['Product']['price_id'] = 106;
						$products['Product']['articul'] = $arti;
						$products['Product']['price'] = 100500;
						$products['Product']['bu'] = 1;
						$storage['Storage']['price_id'] = 106;
						$this->Product->save($products);
					}
				}
				$this->Storage->save($storage);
			}
			$i++;
			}
		}


	 	$where = array();

		if(isset($_GET['oplach_scheta'])) {
			$where[] = " Order.status_id >=3 AND Order.payment_id in (2,3) ";
			if(!empty($_GET['year']))
			{ $where[] = " Order.created >= '".$_GET['year']."-01-01 00:00:00' AND Order.created <= '".$_GET['year']."-12-31 23:59:59' "; }
			$where[] = " Order.archive_admin  IS NULL";
			$data = $this->Order->findAll(implode(" AND ",$where));
			
			$this->set('data', $data);
			$this->set('year', @$_GET['year']);
			
			$this->render('oplach_scheta');
			
		} else {
			
			$search_fields = array();
			$search_fields["number"] = @$_GET["number"];
			$search_fields["other_number"] = @$_GET["other_number"];
			$search_fields["price_id"] = @$_GET["price_id"];
			
			if(!empty($search_fields["number"])) {
					$where[] = "Order.order_number ='".$search_fields["number"]."' "; 
			}
			if(!empty($search_fields["other_number"])){
						$orderproduct_numbers = mysql_query("SELECT order_id FROM `order_products` WHERE number LIKE '%".Sanitize::paranoid($search_fields["other_number"],$this->_alphabet())."%' OR articul LIKE '%".Sanitize::paranoid($search_fields["other_number"],$this->_alphabet())."%' OR description  LIKE '%".Sanitize::paranoid($search_fields["other_number"],$this->_alphabet())."%' OR comment LIKE '%".Sanitize::paranoid($search_fields["other_number"],$this->_alphabet())."%'");
						$id_in='-1';
						while($orderproduct_number = mysql_fetch_array($orderproduct_numbers)){
							$id_in.=','.$orderproduct_number[0];
						}
						$where[] = " Order.id IN (".$id_in.")";
			}
			
			if(!empty($_GET['storage'])) $storag_numb=$_GET['storage'];
			else $storag_numb=0;
			/*if(empty($_GET['page'])){
				$max=1;
			}else{
				$max=$_GET['page'];
			}*/
			if(!empty($search_fields["price_id"])) {
				$storages = $this->Storage->findAll("WHERE storage=".$storag_numb." AND price_id =".$search_fields["price_id"]." AND order_id!=0 ");	
			}else{
				$storages = $this->Storage->findAll("WHERE storage=".$storag_numb." AND order_id!=0 ");
			}
			$id_in='-1';
			foreach($storages as $storage){
				$id_in.=','.$storage['Storage']['order_id'];
			}
			$where[] = "Order.id IN(".$id_in.")";
			$data = $this->Order->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 3);
			$paging['style'] = 'html'; //set the style of the links: html or ajax
			$paging['link'] = '/'.@$this->params['url']['url'].'?number='.$_GET["number"].'&'.'price_id='.$_GET["price_id"].'&'.'other_number='.$_GET["other_number"].'&'.'storage='.$_GET["storage"].'&'.'year='.$_GET["year"].'&'.'page=';		
			$paging['count'] = $this->Order->findCount(implode(" AND ",$where),2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			$paging['show'] = array('20','50','150');
			$this->set('storages',$storages);
			$this->set('colors',$this->StorageColor->findAll());
			$this->set('paging',$paging);
			$this->set('data', $data);
			$this->set('storage_numbers',$this->StorageNumber->findAll('WHERE visible=1'));
			
			$this->set('deliveries', $this->Order->Delivery->findAll());
		}
		$this->set('number', @$sess_search['number']);
		$this->set('other_number', @$sess_search['other_number']);
		$sel_customer_name = $this->Order->Customer->read(null, @$sess_search['cust']);
		$this->set('customer', $sel_customer_name ? $sel_customer_name['Customer']['lname'].' '.substr($sel_customer_name['Customer']['fname'],0,1).'. '.substr($sel_customer_name['Customer']['otch'],0,1).'. ('.$sel_customer_name['Customer']['mphone'].')':'');
	
	
	}
	
		function history(){
		
			$where_to = '';
			if(!empty($_GET['id'])){
				$where_to .= ' AND id='.$_GET['id'];
			}
			if(!empty($_GET['name'])){
				$where_to .= ' AND name LIKE "%'.$_GET['name'].'%"';
			}
			if(!empty($_GET['number'])){
				$where_to .= ' AND manuf_number LIKE "%'.$_GET['number'].'%"';
			}
			if(!empty($_GET['articul'])){
				$where_to .= ' AND articul LIKE "%'.$_GET['articul'].'%"';
			}
			if(empty($_GET['all']) || !empty($where_to))
			$where_t = 'WHERE 1=1'.$where_to;
			else
			$where_t = 'WHERE 1>1';
		
			$storages = $this->Storage->findAll($where_t, null, $this->storage, $this->showt, $this->page, 3);
			$paging['style'] = 'html'; //set the style of the links: html or ajax
			$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
			$paging['count'] = $this->Storage->findCount($where_t,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->showt;
			$paging['show'] = array('20','50','150');
			$this->set('paging',$paging);
			
			$where = '-1';
			
			foreach($storages as $storage){
				$where .= ','.$storage['Storage']['id'];
			}
			$history = $this->HistoryStorage->findAll('WHERE storage_id IN('.$where.')');
			$this->set('prices', $this->Price->findAll());
			$this->set('storage_nums', $this->StorageNumber->findAll());
			$this->set('historys', $history);
			$this->set('storages', $storages);
		}
	
}