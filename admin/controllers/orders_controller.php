<?php
uses('sanitize');

class OrdersController extends AppController
{
	var $name = 'Orders';
	var $helpers = array("Html", "Pagination");
	var $show;
	var $sortBy;
	var $direction;
	var $page;
	var $order;
	var $uses = array("Order","OrderProduct","RequestAnswer","HistoryPrice","Turnover","City","Responsible","Price","DateStatus","HistoryCost","Storage");

	function __construct() {
		$this->show = empty($_GET['show'])? '10': Sanitize::paranoid($_GET['show']);
		$this->sortBy = empty($_GET['sort'])? 'created': Sanitize::paranoid($_GET['sort']);
		$this->direction = empty($_GET['direction'])? 'DESC': Sanitize::paranoid($_GET['direction']);
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}

	function beforeFilter() {	
	 	parent::beforeFilter();	 	

               
		$this->set('statuses',$this->Order->Status->findAll());
	}
	
	function delete($id=null) {

	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			
				$this->Order->del($id);	
				@$flash .= "Заказ $id удален. ";
			
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('orders');
	}
	
	function decline($id=null) {

	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			    $order = $this->Order->read(null, $id);	
				$order['Order']['decline'] = 1;
				
				// отправка письма клиенту
				$this->Sendmail->subject = 'Изменился стаус заказа на "отказ"';
				$this->Sendmail->message = 
'Уважаемый(ая) '.$order['Customer']['fname'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей detalline.ru.

К сожалению, запчастей по Вашему заказу № '.($order['Order']['order_number'] ? $order['Order']['pre_number'].$order['Order']['order_number'] : $order['Order']['id']).' от  '.date('d.m.Y H:i:s', strtotime($order['Order']['created'])).' нет 
в наличие на складе и мы вынуждены отказать Вам в их поставке. Попробуйте перезаказать их позже.

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
+7 (905) 485 62 22
http://detalline.ru/
support@detalline.ru
';

				$this->Sendmail->to_email = $order['Customer']['email'];
				$this->Sendmail->to_name = $order['Customer']['lname'].' '.$order['Customer']['fname'];
				$this->Sendmail->send();
				//--------------------------------------
				
				$this->Order->save($order);	
				@$flash .= "По заказу $id отправлен отказ. ";
			
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('orders/?cust='.@$_GET['cust']);
	}
	
	function index() {

	/*if(!empty($_GET['who'])){
		$products = mysql_query('SELECT id,price, price_id FROM `order_products` WHERE cost IS NULL');
		while($product = mysql_fetch_array($products)){
			$costs = mysql_fetch_array(mysql_query('SELECT price_pers FROM `prices` WHERE price_pers IS NOT NULL AND id='.$product[2]));
			$cost['OrderProduct']['cost'] = $product[1]/(100+$costs[0])*100;
			$cost['OrderProduct']['id'] = $product[0];
			$this->OrderProduct->save($cost);
			
		}
	}
	$product_ships = mysql_query('SELECT id, price_id FROM `order_products` WHERE shipping IS NULL');
	while($product_ship = mysql_fetch_array($product_ships)){
		$price = $this->Price->findById($product_ship[1]);
		$product_save['OrderProduct']['id'] = $product_ship[0];
		$product_save['OrderProduct']['shipping'] = $price['Price']['days_shipping_garant'];
		$this->OrderProduct->save($product_save);
	}*/
	
		$this->set('title','Заказы');
		$this->set("payments",$this->Order->Payment->findAll());
		
		if(!empty($_GET['del_prod']))
		{
			$this->OrderProduct->del((int)$_GET['del_prod']);
			
			$this->redirect('/'.@$this->params['url']['url'].'?cust='.@$_GET['cust'].'&insert_date_from='.@$_GET['insert_date_from'].'&insert_date_to='.@$_GET['insert_date_to'].'&page='.@$_GET['page']);
		}
		
		if(!empty($_GET['add_prod']))
		{
			$data["OrderProduct"]["order_id"] = (int)$_GET['add_prod'];
			$this->OrderProduct->save($data);
			
			$this->redirect('/'.@$this->params['url']['url'].'?cust='.@$_GET['cust'].'&insert_date_from='.@$_GET['insert_date_from'].'&insert_date_to='.@$_GET['insert_date_to'].'&page='.@$_GET['page']);
		}
		
		if($_POST['archive']){
			$order = $this->Order->read(null, $_POST['order_id']);
			$order['Order']['archive_admin'] = 1;
			$this->Order->save($order);
		}
		
		if($_POST['save']) {
		
			$order = $this->Order->read(null, $_POST["order_id"]);
			$customer = $this->Order->Customer->read(null, $order['Order']['customer_id']);
			
			$count_turnover = $this->Turnover->findCount();
			if(!empty($_POST['actually_received'])){
			$turnover['Turnover']['id']=++$count_turnover;
			$turnover['Turnover']['order_id']=$_POST['order_id'];
			$turnover['Turnover']['price']=$_POST['actually_received'];
			$turnover['Turnover']['date']=date('Y-m-t H:i:s');
			$this->Turnover->save($turnover);}
			
			if(!empty($_POST['issued'])){
			$turnover['Turnover']['id']=++$count_turnover;
			$turnover['Turnover']['order_id']=$_POST['order_id'];
			$turnover['Turnover']['price']=$_POST['issued'];
			$turnover['Turnover']['date']=date('Y-m-t H:i:s');
			$turnover['Turnover']['issued']=1;
			$this->Turnover->save($turnover);}		
		
			$historyid=$this->HistoryPrice->findCount();
			$historycostid=$this->HistoryCost->findCount();
			foreach($_POST as $key=>$val) {
			
				$matches = array();
				if(preg_match('/^status(\d+)_(\d+)$/i', $key, $matches)) {
					$data[$matches[2]]["OrderProduct"]["id"] = $matches[2];
					$data[$matches[2]]["OrderProduct"]["status".$matches[1]] = $val;
				}
				
				if(preg_match('/^delayed(\d+)_(\d+)$/i', $key, $matches)) {
					$data[$matches[2]]["OrderProduct"]["id"] = $matches[2];
					$data[$matches[2]]["OrderProduct"]["delayed".$matches[1]] = $val;
				}
				
				if(preg_match('/^price_id_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["price_id"] = $val;
				}
				
				if(preg_match('/^name_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["name"] = $val;
				}
				
				if(preg_match('/^manufacturer_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["manufacturer"] = $val;
				}
				
				if(preg_match('/^mark_and_model_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["mark_and_model"] = $val;
				}
				
				if(preg_match('/^year1_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["year1"] = $val;
				}
				
				if(preg_match('/^year2_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["year2"] = $val;
				}
				
				if(preg_match('/^number_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["number"] = $val;
				}
				
				if(preg_match('/^shipping_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["shipping"] = $val;
				}
				
				if(preg_match('/^articul_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["articul"] = $val;
				}
				
				if(preg_match('/^description_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["description"] = $val;
				}
				
				if(preg_match('/^comment_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["comment"] = $val;
				}
				
				if(preg_match('/^count_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["count"] = $val;
				}
				
				if(preg_match('/^oldprice_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["HistoryPrice"]["id"] = ++$historyid;
					$data[$matches[1]]["HistoryPrice"]["order_product_id"] = $matches[1];
					$data[$matches[1]]["HistoryPrice"]["price"] = $val;
					$data[$matches[1]]["HistoryPrice"]["changed"] = date('Y-m-d H:i:s');
				}
				
				if(preg_match('/^price_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["order_product_id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["price"] = $val;
				}
				
				if(preg_match('/^oldpricecost_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["HistoryCost"]["id"] = ++$historycostid;
					$data[$matches[1]]["HistoryCost"]["order_product_id"] = $matches[1];
					$data[$matches[1]]["HistoryCost"]["price"] = $val;
					$data[$matches[1]]["HistoryCost"]["date"] = date('Y-m-d H:i:s');
				}
				
				if(preg_match('/^pricecost_(\d+)$/i', $key, $matches) && $_POST['save']) {
					$data[$matches[1]]["OrderProduct"]["order_product_id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["cost"] = $val;
				}
				
				if(preg_match('/^decline_(\d+)$/i', $key, $matches)) {
					$data[$matches[1]]["OrderProduct"]["id"] = $matches[1];
					$data[$matches[1]]["OrderProduct"]["decline"] = $val;
				}
			}	
			
			$status_is=9;
			
			foreach($data as $key=>$val) {

if(!empty($val['OrderProduct']['price'])) $this->HistoryPrice->save($val);
if(empty($val['OrderProduct']['price'])) $val['OrderProduct']['price']=$val['HistoryPrice']['price'];

if(!empty($val['OrderProduct']['cost'])) $this->HistoryCost->save($val);
if(empty($val['OrderProduct']['cost'])) $val['OrderProduct']['cost']=$val['HistoryCost']['price'];

				
					$order = $this->Order->read(null, $_POST["order_id"]);
					
					if($_POST["payment_id"] == 2) //безнал
			{					
				$order['Order']['organization'] = $customer['Customer']['organization'] = $_POST["organization"];
				$order['Order']['jur_address'] = $customer['Customer']['jur_address'] = $_POST["jur_address"];
				$order['Order']['inn'] = $customer['Customer']['inn'] = $_POST["inn"];
				$order['Order']['kpp'] = $customer['Customer']['kpp'] = $_POST["kpp"];
				$order['Order']['ogrn'] = $customer['Customer']['ogrn'] = $_POST["ogrn"];
				$order['Order']['bank'] = $customer['Customer']['bank'] = $_POST["bank"];
				$order['Order']['bank_address'] = $customer['Customer']['bank_address'] = $_POST["bank_address"];
				$order['Order']['bik'] = $customer['Customer']['bik'] = $_POST["bik"];
				$order['Order']['corr_schet'] = $customer['Customer']['corr_schet'] = $_POST["corr_schet"];
				$order['Order']['raschet_shet'] = $customer['Customer']['raschet_shet'] = $_POST["raschet_shet"];
				
				if($order['Order']['payment_id'] != $_POST["payment_id"]) {
					if(empty($order['Order']['schet_number'])) {
						$schet_number = mysql_fetch_row(mysql_query("SELECT max(schet_number) FROM `orders` WHERE year(created) = ".date('Y')." and site_url='".$order['Order']['site_url']."' and payment_id=2 "));	
						$order['Order']['schet_number'] =(int)$schet_number[0] + 1;
					}
					$order['Order']['payment_id'] = $_POST["payment_id"];
				}
				$this->Order->Customer->save($customer);				
			}
			else
			{
				if($order['Order']['payment_id'] != $_POST["payment_id"]) {
					if(empty($order['Order']['check_number'])) {
						$check_number = mysql_fetch_row(mysql_query("SELECT max(check_number) FROM `orders` WHERE year(created) = ".date('Y')." and site_url='".$order['Order']['site_url']."' and payment_id in (1,3) "));	
						$order['Order']['check_number'] =(int)$check_number[0] + 1;
					}
					$order['Order']['payment_id'] = $_POST["payment_id"];					
					$order['Order']['organization'] = '';
					$order['Order']['jur_address'] = '';
					$order['Order']['inn'] = '';
					$order['Order']['kpp'] = '';
					$order['Order']['ogrn'] = '';
					$order['Order']['bank'] = '';
					$order['Order']['bank_address'] = '';
					$order['Order']['bik'] = '';
					$order['Order']['corr_schet'] = '';
					$order['Order']['raschet_shet'] = '';
				}				
			}
					
					$order['Order']['id_request'] = $_POST['id_request'];
					$order['Order']['request_name'] = $_POST['request_name'];
					$order['Order']['request_email'] = $_POST['request_email'];
					$order['Order']['request_phone'] = $_POST['request_phone'];
					$order['Order']['responsible_id'] = $_POST['responsible_id'];
					$order['Order']['delivery_id'] = $_POST['delivery_id'];
					$order['Order']['adress1'] = $_POST['adress1'];
					$order['Order']['adress2'] = $_POST['adress2'];					
					if($_POST['city_h']=='1r6d1')
						$order['Order']['city']=$_POST['city'];
					else{
						$city['City']['region_id']=$_POST['region_id'];
						$city['City']['name']=$_POST['city_h'];
						$this->City->save($city);
						$order['Order']['city']=$this->City->findCount();
					}
					$order['Order']['zipcode'] = $_POST['zipcode'];
					$order['Order']['region_id'] = $_POST['region_id'];
					$order['Order']['payment_id'] = $_POST["payment_id"];
					$this->Order->save($order);					
					
					$oldstatus=$order['Order']['status_id'];
					if($val['OrderProduct']['decline']!=1){
						
						$status_id=1;
						if($val['OrderProduct']['status1']>0)$status_id=2;
						if($val['OrderProduct']['status2']>0)$status_id=3;
						if($val['OrderProduct']['status3']>0)$status_id=4;
						if($val['OrderProduct']['status4']>0)$status_id=5;
						if($val['OrderProduct']['status5']>0)$status_id=6;
						if($val['OrderProduct']['status6']>0)$status_id=7;
						
						if($status_id < $status_is) $status_is = $status_id;
					}
					$order['Order']['status_id'] = $status_is;
					$order['Order']['decline']=$val['OrderProduct']['decline'];
					$order['Order']['stage_date'] = date('Y-m-d H:i:s');
					$this->Order->save($order);
					if($oldstatus!=$order['Order']['status_id']){
						mysql_query("INSERT INTO orders_statuses(order_id, status_id,product, stage_date) VALUES ('".$order['Order']['id']."','".$order['Order']['status_id']."','".$val['OrderProduct']['id']."','".$order['Order']['stage_date']."');");
					}
					$i=5;
					if($val['OrderProduct']['status'.($i+1)]==0) {mysql_query("DELETE FROM orders_statuses WHERE order_id='".$order['Order']['id']."' AND status_id='".($i+2)."' AND product='".$val['OrderProduct']['id']."'");}
					while($i>0){
					if($val['OrderProduct']['status'.$i]==0) {mysql_query("DELETE FROM orders_statuses WHERE order_id='".$order['Order']['id']."' AND status_id='".($i+1)."' AND product='".$val['OrderProduct']['id']."'");}
						if($val['OrderProduct']['status'.($i+1)]>$val['OrderProduct']['status'.$i]){if($val['OrderProduct']['status'.$i]==0){
							mysql_query("INSERT INTO orders_statuses(order_id, status_id,product, stage_date) VALUES ('".$order['Order']['id']."','".($i+1)."','".$val['OrderProduct']['id']."','".date('Y-m-d H:i:s')."');");
						$val['OrderProduct']['status'.$i]=$val['OrderProduct']['status'.($i+1)];}}
					$i--;}
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
			
			if(isset($_GET['cust'])) {
				$search_fields = array();
				$search_fields["insert_date_from"] = @$_GET["insert_date_from"];
				$search_fields["insert_date_to"] = @$_GET["insert_date_to"];
				$search_fields["number"] = @$_GET["number"];
				$search_fields["cust"] = @$_GET["cust"];
				$search_fields["customer_phone"] = @$_GET["customer_phone"];
				$search_fields["customer_email"] = @$_GET["customer_email"];
				$search_fields["id_request"] = @$_GET["id_request"];
				$search_fields["request_phone"] = @$_GET["request_phone"];
				$search_fields["request_name"] = @$_GET["request_name"];
				$search_fields["request_email"] = @$_GET["request_email"];
				$search_fields["responsible"] = @$_GET["responsible"];
				$search_fields["archive"] = @$_GET["archive"];
				$search_fields["other_number"] = @$_GET["other_number"];
				
				$this->Session->del('search_orders');	
				$this->Session->write('search_orders',$search_fields);	
			}
			
			if($this->Session->valid('search_orders'))
				$sess_search = $this->Session->read('search_orders');
			
			if(!empty($sess_search) && is_array($sess_search))
			{
				if(!empty($sess_search["insert_date_from"])) 
					$where[] = " Order.created >= '". date('Y-m-d 00:00:00', strtotime($sess_search['insert_date_from'])) . "' "; 
				
				if(!empty($sess_search["insert_date_to"])) 
					$where[] = " Order.created <= '". date('Y-m-d 23:59:59', strtotime($sess_search['insert_date_to'])) . "' "; 				
			
				if(!empty($sess_search["cust"])) 
					$where[] = " Order.customer_id = '".Sanitize::paranoid($sess_search["cust"])."' "; 	
					
				if(!empty($sess_search["customer_phone"])) 
					$where[] = " ( Customer.mphone LIKE '%".$sess_search["customer_phone"]."%' OR Customer.hphone LIKE '%".$sess_search["customer_phone"]."%' ) "; 	
				
				if(!empty($sess_search["customer_email"])) 
					$where[] = " Customer.email LIKE '%".$sess_search["customer_email"]."%' "; 

				if(!empty($sess_search["id_request"])) 
					$where[] = " Order.id_request LIKE '".$sess_search["id_request"]."' "; 
					
				if(!empty($sess_search["request_name"])) 
					$where[] = " Order.request_name LIKE '%".$sess_search["request_name"]."%' "; 
					
				if(!empty($sess_search["request_email"])) 
					$where[] = " Order.request_email LIKE '%".$sess_search["request_email"]."%' "; 
					
				if(!empty($sess_search["request_phone"])) 
					$where[] = " Order.request_phone LIKE '%".$sess_search["request_phone"]."%' "; 
					
				if(!empty($sess_search["responsible"])) 
					$where[] = " Order.responsible LIKE '%".$sess_search["responsible"]."%' ";
				
				if(!empty($sess_search["other_number"])){
						$orderproduct_numbers = mysql_query("SELECT order_id FROM `order_products` WHERE number LIKE '%".Sanitize::paranoid($sess_search["other_number"],$this->_alphabet())."%' OR articul LIKE '%".Sanitize::paranoid($sess_search["other_number"],$this->_alphabet())."%' OR description  LIKE '%".Sanitize::paranoid($sess_search["other_number"],$this->_alphabet())."%' OR comment LIKE '%".Sanitize::paranoid($sess_search["other_number"],$this->_alphabet())."%'");
						$id_in='-1';
						while($orderproduct_number = mysql_fetch_array($orderproduct_numbers)){
							$id_in.=','.$orderproduct_number[0];
						}
						$where[] = " Order.id IN (".$id_in.")";
					}
					
						
				if($sess_search["archive"]==0) 
					$where[] = " Order.archive_admin IS NULL";
				else
					$where[] = " Order.archive_admin=1";
								
				if(!empty($sess_search["number"])) {
					list($pre, $number) = explode("-", $sess_search["number"]);
					if($number) {
						$where[] = " Order.pre_number = '".$pre."-' AND Order.order_number ='".$number."' "; 	
					} else {
						$where[] = " Order.id = '".$pre."' ";
					}
				}
			}
			
			$data = $this->Order->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 3);

			$paging['style'] = 'html'; //set the style of the links: html or ajax
			$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
			$paging['count'] = $this->Order->findCount(implode(" AND ",$where),2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			$paging['show'] = array('20','50','150');
			$this->set('paging',$paging);
			$this->set('data', $data);
			
			$history = $this->HistoryPrice->findAll("ORDER BY changed DESC");
			$this->set('history_price', $history);
			$this->set('cost_history', $this->HistoryCost->findAll("ORDER BY date DESC"));
			$this->set('turnovers',$this->Turnover->findAll());
			$this->set('deliveries', $this->Order->Delivery->findAll());
			$this->set('regions', $this->Order->Region->findAll());
			$this->set('cities', $this->City->findAll());
			$this->set('responsibles', $this->Responsible->findAll());
			$this->set('prices', $this->Price->findAll());
			$this->set('date_statuses', $this->DateStatus->findAll());
		}

		$this->set('insert_date_from', @$sess_search['insert_date_from']);
		$this->set('insert_date_to', @$sess_search['insert_date_to']);
		$this->set('number', @$sess_search['number']);
		$this->set('customer_id', @$sess_search['cust']);
		$this->set('customer_phone', @$sess_search['customer_phone']);
		$this->set('customer_email', @$sess_search['customer_email']);
		$this->set('id_request', @$sess_search['id_request']);
		$this->set('request_email', @$sess_search['request_email']);
		$this->set('request_name', @$sess_search['request_name']);
		$this->set('request_phone', @$sess_search['request_phone']);
		$this->set('responsible', @$sess_search['responsible']);
		$this->set('archive', @$sess_search['archive']);
		$this->set('other_number', @$sess_search['other_number']);
		$sel_customer_name = $this->Order->Customer->read(null, @$sess_search['cust']);
		$this->set('customer', $sel_customer_name ? $sel_customer_name['Customer']['lname'].' '.substr($sel_customer_name['Customer']['fname'],0,1).'. '.substr($sel_customer_name['Customer']['otch'],0,1).'. ('.$sel_customer_name['Customer']['mphone'].')':'');
	}
	
	function print_check(){
		if(!$_GET['id'])
			$this->redirect("/orders/");
		$this->layout = "blank";
		$this->set("payments",$this->Order->Payment->findAll());
		
	
			
			if($this->Session->valid('search_orders'))
				$sess_search = $this->Session->read('search_orders');
			
			
			
			$data = $this->Order->findAll('WHERE Order.id='.$_GET["id"]);

			
			$this->set('paging',$paging);
			$this->set('data', $data);
			
			$history = $this->HistoryPrice->findAll("ORDER BY changed DESC");
			$this->set('history_price', $history);
			$this->set('cost_history', $this->HistoryCost->findAll("ORDER BY date DESC"));
			$this->set('turnovers',$this->Turnover->findAll());
			$this->set('deliveries', $this->Order->Delivery->findAll());
			$this->set('regions', $this->Order->Region->findAll());
			$this->set('cities', $this->City->findAll());
			$this->set('responsibles', $this->Responsible->findAll());
			$this->set('prices', $this->Price->findAll());
			$this->set('date_statuses', $this->DateStatus->findAll());
		

		
	}
}
?>