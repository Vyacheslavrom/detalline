<?php
class OrdersController extends AppController
{	

var $uses = array("City","Responsible","Order","OrderProduct","RequestAnswer","HistoryPrice","Turnover","Price","DateStatus","HistoryCost","Storage","HistoryStorage","Credential","Customer");

	function check_out() 
	{	
			
		if(!@$_SESSION['cart'] && !@$_SESSION['cart_from_request']&& !@$_SESSION['cart_from_search_by_num'] && !@$_SESSION['cart_from_avtoto']) {
			$this->redirect("/");
		}  			
		if(!$this->logged) {
			$this->redirect("/check_out_signin.html");
		}  	
		$row = $this->Customer->findByEmail($this->othAuth->user('email'));
		$this->set("payments",$this->Order->Payment->findAll());
		$this->set("responsible",$this->Responsible->findAll("customer_id='".$row['Customer']['id']."'"));
		$this->set("deliveries",$this->Order->Delivery->findAll());
		$this->set("regions",$this->Order->Region->findAll(null,null,'Region.name ASC'));
		$this->set("responsibles",$this->Responsible->findAll());
		
		
		
		
		if (!empty($this->data)) { 
			
			$errors = false;

			
			if(empty($this->data['Order']['delivery_id'])) {
				$this->Order->invalidate('delivery_id');
				$errors = true;
			}
			if(!isset($this->data['Order']['payment_id'])) {
				$this->Order->invalidate('payment_id');
				$errors = true;
			}
			if(!$this->Order->validates($this->data)) {	
				$errors = true;
			}	
			if($this->data['Order']['payment_id']==2) {
				if(empty($this->data['Customer']['organization'])) {
					$this->Customer->invalidate('organization');
					$errors = true;
				}
				if(empty($this->data['Customer']['jur_address'])) {
					$this->Customer->invalidate('jur_address');
					$errors = true;
				}
				if(empty($this->data['Customer']['inn'])) {
					$this->Customer->invalidate('inn');
					$errors = true;
				}
				if(empty($this->data['Customer']['ogrn'])) {
					$this->Customer->invalidate('ogrn');
					$errors = true;
				}
				if(empty($this->data['Customer']['bank'])) {
					$this->Customer->invalidate('bank');
					$errors = true;
				}
				if(empty($this->data['Customer']['bank_address'])) {
					$this->Customer->invalidate('bank_address');
					$errors = true;
				}
				if(empty($this->data['Customer']['bik'])) {
					$this->Customer->invalidate('bik');
					$errors = true;
				}
				if(empty($this->data['Customer']['corr_schet'])) {
					$this->Customer->invalidate('corr_schet');
					$errors = true;
				}
				if(empty($this->data['Customer']['raschet_shet'])) {
					$this->Customer->invalidate('raschet_shet');
					$errors = true;
				}
			}
			if($errors) {
				$this->Session->setFlash('<font color=red>Обнаружены ошибки</font><p>','flash');
				
			}else{
			
			if($this->data['Order']['city']=='1r6d1')
				$this->data['Order']['city']=$_POST['city'];
			else{
				$city['City']['region_id']=$this->data['Order']['region_id'];
				$city['City']['name']=$this->data['Order']['city'];
				$city['City']['id']=$this->City->findCount()+1;
				$this->City->save($city);
				$this->data['Order']['city']=$city['City']['id'];
			}
			
				$row_payment = $this->Order->Payment->read("Payment.name",$this->data['Order']['payment_id']);
				$row_delivery = $this->Order->Delivery->read("Delivery.name, Delivery.price",$this->data['Order']['delivery_id']);
				$row_region = $this->Order->Region->read(null,$this->data['Order']['region_id']);
				$i=0;
				$complect_number = mysql_fetch_row(mysql_query("SELECT MAX(complect_number) FROM `storages`"));
				function storag($price,$name,$customer,$n,$manuf,$articul,$count,$complect){
					$storage['Storage']['customer_id'] = $customer;
					$storage['Storage']['date'] = date('Y-m-d H:i:s');
					$orderproductcount = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM `order_products`"));
					$storage['Storage']['order_product_id'] = $orderproductcount[0]+$n;
					$ordercount = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM `orders`"));
					$storage['Storage']['order_id'] = ++$ordercount[0];
					$storage['Storage']['complect_number'] = $complect;
					$storage['Storage']['price_id'] = $price;
					$storage['Storage']['name'] = $name;
					$storage['Storage']['manuf_number'] = $manuf;
					$storage['Storage']['articul'] = $articul;
					$storage['Storage']['storage'] = 0 ;
					$chet=0;
					$history_storage_id = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM history_storages"));
					$history_storage['HistoryStorage']['id'] = $history_storage_id[0];
					$history_storage['HistoryStorage']['storage_num'] = 0;
					$history_storage['HistoryStorage']['date'] = date('Y-m-d H:i:s');
					while($chet<$count){
					$history_storage['HistoryStorage']['id']++;
					$chet++;
					$check = mysql_fetch_row(mysql_query("SELECT storage,id FROM storages WHERE price_id = ".$storage['Storage']['price_id']." AND name LIKE '".$storage['Storage']['name']."' AND manuf_number LIKE '".$storage['Storage']['manuf_number']."' AND articul LIKE '".$storage['Storage']['articul']."' AND storage=1"));
					if($check[0]!=1){
						$storages = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM storages"));
						$storage['Storage']['id'] = ++$storages[0];
						mysql_query("INSERT INTO storages (id,complect_number,order_id, order_product_id, price_id, name, manuf_number, articul, customer_id, storage, date) VALUES (".$storage['Storage']['id'].",".$storage['Storage']['complect_number'].",".$storage['Storage']['order_id'].",".$storage['Storage']['order_product_id'].",".$storage['Storage']['price_id'].",'".$storage['Storage']['name']."','".$storage['Storage']['manuf_number']."','".$storage['Storage']['articul']."','".$storage['Storage']['customer_id']."',0,'".$storage['Storage']['date']."')");
					}
					else{
						$storage['Storage']['id']=$check[1];
						mysql_query("UPDATE `storages` SET complect_number=".$storage['Storage']['complect_number'].", order_id=".$storage['Storage']['order_id'].", order_product_id=".$storage['Storage']['order_product_id'].", customer_id=".$storage['Storage']['customer_id'].", price_id =".$storage['Storage']['price_id'].",storage=0,date='".$storage['Storage']['date']."' WHERE id=".$storage['Storage']['id']);
					}
					$history_storage['HistoryStorage']['storage_id'] = $storage['Storage']['id'];
					mysql_query("INSERT INTO history_storages (id,storage_id,storage_num, date) VALUES (".$history_storage['HistoryStorage']['id'].",".$history_storage['HistoryStorage']['storage_id'].",".$history_storage['HistoryStorage']['storage_num'].",'".$history_storage['HistoryStorage']['date']."')");
					}
				}
				
				// занесение в базу
				$this->data['Order']['customer_id'] = $row['Customer']['id'];	
                        $order_sum=0;
				foreach(@$_SESSION['cart'] as $product_id => $prod) {
					$product = $this->Product->findById(Sanitize::paranoid($product_id));
					
					// добавляем цены и другие параметры
					$prod['price'] = $product['Product']['price']*(1+intval($this->othAuth->user("skidka"))/100);
					$i++;
					$cost = mysql_query('SELECT price_pers FROM `prices` WHERE id = '.$product['Product']['price_id']);
					$prod['cost'] = ($product['Product']['price']/(100+$cost[0]))*100;
					$prod['price_id'] = $product['Product']['price_id'];
					$prod['category_id'] = $product['Product']['category_id'];
					$prod['mark_and_model'] = $product['Product']['mark_and_model'];
					$prod['year1'] = $product['Product']['year1'];
					$prod['year2'] = $product['Product']['year2'];
					$prod['name'] = $product['Product']['name'];
					$prod['description'] = $product['Product']['description'];
					$prod['number'] = $product['Product']['number'];
					$prod['articul'] = $product['Product']['articul'];
					$product['Product']['quantity'] = $product['Product']['quantity'] - $prod['count'];
					if($product['Product']['quantity'] < 0){$prod['count'] = $prod['count'] + $product['Product']['quantity'];$product['Product']['quantity']=0;}
					$complect_number[0]++;
					storag($product['Product']['price_id'],$product['Product']['name'],$row['Customer']['id'],$i,NULL,$product['Product']['articul'],$prod['count'],$complect_number[0]);
					$this->Product->save($product);
					
					$order_sum += $prod['count']*$prod['price'];
					@$_SESSION['cart'][$product_id] = $prod;				
				}
                foreach(@$_SESSION['cart_from_request'] as $answer_id => $prod) {
					$answer = $this->RequestAnswer->findById(Sanitize::paranoid($answer_id));
					
					// добавляем цены и другие параметры
					$i++;
					$complect_number[0]++;
					storag($prod['price_id'],$prod['name'],$row['Customer']['id'],$i,$prod['manuf_number'],$prod['articul'],$prod['count'],$complect_number[0]);
					$prod['price'] = $answer['RequestAnswer']['itog_price'];
					$prod['request_answer_id'] = $answer_id;	
                    $prod['year1'] = $prod['year'];
					$prod['year2'] = $prod['year'];	
					$prod['description'] = $answer['RequestAnswer']['state'].', '.$answer['RequestAnswer']['note'];
					
					$order_sum += $prod['count']*$prod['price'];
					@$_SESSION['cart_from_request'][$answer_id] = $prod;				
				}
				foreach(@$_SESSION['cart_from_search_by_num'] as $product_id => $prod) {
					$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$product_id."'"));
					
					// добавляем цены и другие параметры
					$prod['price'] = $product['price']*(1+intval($this->othAuth->user("skidka"))/100);
					$i++;
					$cost = mysql_fetch_array(mysql_query('select price_pers from `prices` where id=17'));
					$prod['cost'] = ($product['price']/(100+$cost[0]))*100;
					$prod['price_id'] = $product['price_id'];
					$prod['name'] = $product['name'];
					$prod['manufacturer'] = $product['manufacturer'];
					$prod['number'] = $product['manuf_number'];
					//$product['quantity'] = $product['quantity'] - $prod['count'];
					//if($product['quantity'] < 0){$prod['count'] = $prod['count'] + $product['quantity'];$product['quantity']=0;}
					$complect_number[0]++;
					storag($product['price_id'],$product['name'],$row['Customer']['id'],$i,$product['manuf_number'],NULL,$prod['count'],$complect_number[0]);
					$this->Product->save($product);
					
					$order_sum += $prod['count']*$prod['price'];
					@$_SESSION['cart_from_search_by_num'][$product_id] = $prod;				
				}
				foreach(@$_SESSION['cart_from_avtoto'] as $product_id => $product) {
					$prod = $product;
					$i++;
					$complect_number[0]++;
					storag($product['price_id'],$product['name'],$row['Customer']['id'],$i,$product['manuf'],$product['articul'],$prod['count'],$complect_number[0]);
					$prod['price_id'] = 29;
					$prod['manufacturer'] = $product['manuf'];
					$prod['number'] = $product['code'];
					$prod['price'] = $product['price']*(1+intval($this->othAuth->user("skidka"))/100);
					$prod['storage'] = $product['Storage'];
					$prod['delivery'] = $product['Delivery'];
					
					$order_sum += $prod['count']*$prod['price'];
					@$_SESSION['cart_from_avtoto'][$product_id] = $prod;							
				}
				
				$this->data['Order']['skidka'] = 0;
				$this->data['Order']['sum'] = $order_sum;
				$this->data['Order']['ship_sum'] = $row_delivery['Delivery']['price'];
                $this->data['Order']['stage_date'] = date('Y-m-d H:i:s');
                $this->data['Order']['site_url'] ='detalline.ru';
				$this->data['Order']['pre_number'] ='DL-';
				
				if($this->data['Order']['payment_id']==2) {
					$this->data['Order']['organization'] = $this->data['Customer']['organization'];
					$this->data['Order']['jur_address'] = $this->data['Customer']['jur_address'];
					$this->data['Order']['inn'] = $this->data['Customer']['inn'];
					$this->data['Order']['kpp'] = $this->data['Customer']['kpp'];
					$this->data['Order']['ogrn'] = $this->data['Customer']['ogrn'];
					$this->data['Order']['bank'] = $this->data['Customer']['bank'];
					$this->data['Order']['bank_address'] = $this->data['Customer']['bank_address'];
					$this->data['Order']['bik'] = $this->data['Customer']['bik'];
					$this->data['Order']['corr_schet'] = $this->data['Customer']['corr_schet'];
					$this->data['Order']['raschet_shet'] = $this->data['Customer']['raschet_shet'];
				}
				
				
				//----------номер заказа---------
				$order_number = mysql_fetch_row(mysql_query("SELECT max(order_number) FROM `orders` WHERE year(created) = ".date('Y')." and site_url='detalline.ru'"));
				$this->data['Order']['order_number'] =(int)$order_number[0] + 1;
				
				if($this->data['Order']['payment_id']==2) {
					$schet_number = mysql_fetch_row(mysql_query("SELECT max(schet_number) FROM `orders` WHERE year(created) = ".date('Y')." and site_url='detalline.ru' and payment_id=2 "));	
					$this->data['Order']['schet_number'] =(int)$schet_number[0] + 1;
				} else {
					$check_number = mysql_fetch_row(mysql_query("SELECT max(check_number) FROM `orders` WHERE year(created) = ".date('Y')." and site_url='detalline.ru' and payment_id in (1,3) "));	
					$this->data['Order']['check_number'] =(int)$check_number[0] + 1;
				}
				//----------номер заказа---------
				
				$this->Order->save($this->data);
				$order_id = $this->Order->getLastInsertId();
				
                foreach(@$_SESSION['cart'] as $product_id => $prod) {
					$prod["order_id"] = $order_id;
					$dataProd["OrderProduct"] = $prod;
					$this->Order->OrderProduct->save($dataProd);
                    unset($this->Order->OrderProduct->id);
				}
                foreach(@$_SESSION['cart_from_request'] as $answer_id => $prod) {
					$prod["order_id"] = $order_id;
					$dataProd["OrderProduct"] = $prod;
					$this->Order->OrderProduct->save($dataProd);
                    unset($this->Order->OrderProduct->id);
				}
				foreach(@$_SESSION['cart_from_search_by_num'] as $product_id => $prod) {
					$prod["order_id"] = $order_id;
					$dataProd["OrderProduct"] = $prod;
					$this->Order->OrderProduct->save($dataProd);
                    unset($this->Order->OrderProduct->id);
				}
				foreach(@$_SESSION['cart_from_avtoto'] as $product_id => $prod) {
					$prod["order_id"] = $order_id;
					$dataProd["OrderProduct"] = $prod;
					$this->Order->OrderProduct->save($dataProd);
					@$_SESSION['cart_from_avtoto'][$product_id]['RemoteID'] = $this->Order->OrderProduct->id;
                    unset($this->Order->OrderProduct->id);
				}
				
				$this->data['Customer']['payment_id'] = $this->data['Order']['payment_id'];
				$this->data['Customer']['delivery_id'] = $this->data['Order']['delivery_id'];
				$this->data['Customer']['id'] = $row['Customer']['id'];	
				$this->Customer->save($this->data);
								
				// отправка письма админу
				$this->Sendmail->subject = 'Новый заказ на сайте "detalline.ru"';
				$this->Sendmail->message = 
'+------------------------------------------------------------------
|   НОВЫЙ ЗАКАЗ  НА САЙТЕ "detalline.ru"
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------
|  от покупателя: 
+------------------------------------------------------------------
|  ID: '.$row['Customer']['id'].'
|  Имя: '.$row['Customer']['lname'].' '.$row['Customer']['fname'].' '.$row['Customer']['otch'].'
|  Email: '.$row['Customer']['email'].'
|  Дом. телефон: '.($row['Customer']['hphone']?$row['Customer']['hphone']:'-').'
|  Моб. телефон: '.($row['Customer']['mphone']?$row['Customer']['mphone']:'-').'
+-------------------------------------------------------------------		
|  товары: 
+------------------------------------------------------------------';
foreach(@$_SESSION['cart'] as $product_id => $prod) {
	$product = $this->Product->findById(Sanitize::paranoid($product_id));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$product['Product']['name'].'  
|  Марка и модель авто: '.$product['Product']['mark_and_model'].'
|  Годы выпуска: '.$product['Product']['year1'].' - '.$product['Product']['year2'].'
|  Ориг. номер: '.$product['Product']['number'].' 
|  Артикул: '.$product['Product']['articul'].'
|  Доп. информация о запчасти: '.$product['Product']['description'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
foreach(@$_SESSION['cart_from_request'] as $answer_id => $prod) {
	$answer = $this->RequestAnswer->findById(Sanitize::paranoid($answer_id));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$prod['name'].'  
|  Марка и модель авто: '.$prod['mark_and_model'].'
|  Год выпуска: '.$prod['year'].'
|  Ориг. номер: '.$prod['number'].' 
|  Доп. информация о запчасти: '.$prod['description'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
foreach(@$_SESSION['cart_from_search_by_num'] as $product_id => $prod) {
	$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$product_id."'"));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$product['name'].'  
|  Производитель: '.$product['manufacturer'].'
|  Номер: '.$product['manuf_number'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
foreach(@$_SESSION['cart_from_avtoto'] as $product_id => $prod) {
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$prod['name'].'  
|  Производитель: '.$prod['manuf'].'
|  Номер: '.$prod['code'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
$this->Sendmail->message .='
|  ВСЕГО: '.number_format($order_sum,2,'.',' ').' руб.
+------------------------------------------------------------------
|  СКИДКА: '.number_format($order_sum*$this->othAuth->user('skidka')/100,2,'.',' ').' руб.
+------------------------------------------------------------------
|  ИТОГО: '.number_format($this->data['Order']['sum'],2,'.',' ').' руб.
+------------------------------------------------------------------
|  доставка осуществляется по адресу: 
+------------------------------------------------------------------
|  Кому:  '.$this->data['Order']['lname'].' '.$this->data['Order']['fname'].' '.$this->data['Order']['otch'].'
|  Дом. телефон: '.($this->data['Order']['hphone']?$this->data['Order']['hphone']:'-').'
|  Моб. телефон: '.($this->data['Order']['mphone']?$this->data['Order']['mphone']:'-').'
|  По адресу: '.$row_region['Region']['name'].', '.$this->data['Order']['zipcode'].' '.$this->data['Order']['city'].'
|             '.$this->data['Order']['address1'].'  '.$this->data['Order']['address2'].'
+------------------------------------------------------------------
|  способ доставки:  '.$row_delivery['Delivery']['name'].'
+------------------------------------------------------------------
|  способ оплаты:  '.$row_payment['Payment']['name'].'
+------------------------------------------------------------------
|  комментарий:  '.$this->data['Order']['comment'].'
+------------------------------------------------------------------';

				$this->Sendmail->send();
				//--------------------------------------
					
					
				// отправка письма клиенту
				$this->Sendmail->subject = 'Ваш заказ';
				$this->Sendmail->message = 
'Уважаемый(ая) '.$row['Customer']['fname'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей detalline.ru.

+------------------------------------------------------------------
|   ВАШ ЗАКАЗ
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------		
|  товары: 
+------------------------------------------------------------------';
foreach(@$_SESSION['cart'] as $product_id => $prod) {
	$product = $this->Product->findById(Sanitize::paranoid($product_id));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$product['Product']['name'].'  
|  Марка и модель авто: '.$product['Product']['mark_and_model'].'
|  Годы выпуска: '.$product['Product']['year1'].' - '.$product['Product']['year2'].'
|  Ориг. номер: '.$product['Product']['number'].' 
|  Артикул: '.$product['Product']['articul'].'
|  Доп. информация о запчасти: '.$product['Product']['description'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
foreach(@$_SESSION['cart_from_request'] as $answer_id => $prod) {
	$answer = $this->RequestAnswer->findById(Sanitize::paranoid($answer_id));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$prod['name'].'  
|  Марка и модель авто: '.$prod['mark_and_model'].'
|  Год выпуска: '.$prod['year'].'
|  Ориг. номер: '.$prod['number'].' 
|  Доп. информация о запчасти: '.$prod['description'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}	
foreach(@$_SESSION['cart_from_search_by_num'] as $product_id => $prod) {
	$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$product_id."'"));
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$product['name'].'  
|  Производитель: '.$product['manufacturer'].'
|  Номер: '.$product['manuf_number'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}
foreach(@$_SESSION['cart_from_avtoto'] as $product_id => $prod) {
	$this->Sendmail->message.=' 
|  Наименование запчасти: '.$prod['name'].'  
|  Производитель: '.$prod['manuf'].'
|  Номер: '.$prod['code'].'  
|  Количество: '.$prod['count'].' 
|  Цена: '.number_format($prod['price'],2,'.',' ').' руб.
+------------------------------------------------------------------';
}			
$this->Sendmail->message .='
|  ВСЕГО: '.number_format($order_sum,2,'.',' ').' руб.
+------------------------------------------------------------------
|  СКИДКА: '.number_format($order_sum*$this->othAuth->user('skidka')/100,2,'.',' ').' руб.
+------------------------------------------------------------------
|  ИТОГО: '.number_format($this->data['Order']['sum'],2,'.',' ').' руб.
+------------------------------------------------------------------
|  доставка осуществляется по адресу: 
+------------------------------------------------------------------
|  Кому:  '.$this->data['Order']['lname'].' '.$this->data['Order']['fname'].' '.$this->data['Order']['otch'].'
|  Дом. телефон: '.($this->data['Order']['hphone']?$this->data['Order']['hphone']:'-').'
|  Моб. телефон: '.($this->data['Order']['mphone']?$this->data['Order']['mphone']:'-').'
|  По адресу: '.$row_region['Region']['name'].', '.$this->data['Order']['zipcode'].' '.$this->data['Order']['city'].'
|             '.$this->data['Order']['address1'].'  '.$this->data['Order']['address2'].'
+------------------------------------------------------------------
|  способ доставки:  '.$row_delivery['Delivery']['name'].'
+------------------------------------------------------------------
|  способ оплаты:  '.$row_payment['Payment']['name'].'
+------------------------------------------------------------------
|  комментарий:  '.$this->data['Order']['comment'].'
+------------------------------------------------------------------

Наши администраторы свяжутся с вами в течении 1-2 дней

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
8-904-503-07-07
http://detalline.ru/
support@detalline.ru
';

				$this->Sendmail->to_email = $row['Customer']['email'];
				$this->Sendmail->to_name = $row['Customer']['lname'].' '.$row['Customer']['fname'];
				$this->Sendmail->send();
				//--------------------------------------
				
				// отправка письма поставщику
			$this->Sendmail->to_email = '';
			$this->Sendmail->to_name = '';
			$all_prices = $this->Price->findAll();
			foreach($all_prices as $all_price){
				$post_email = '';
				$postavshik = $this->Customer->findAll('WHERE price_id='.$all_price['Price']['id']);
				foreach($postavshik as $postav){
					$post_email = $postav['Customer']['email'];
					$post_name = $postav['Customer']['lname'].' '.$postav['Customer']['fname'];
				}
				$need_send = false;	
				if($post_email != ''){
				$this->Sendmail->to_email = $post_email;
				$this->Sendmail->to_name = $post_name;
					$this->Sendmail->subject = 'Новый заказ на сайте "detalline.ru"';
					$this->Sendmail->message = 
					'+------------------------------------------------------------------
					|   НОВЫЙ ЗАКАЗ  НА САЙТЕ "detalline.ru"
					|   '.date('d.m.Y H:i').'
					+-------------------------------------------------------------------		
					|  товары: 
					+------------------------------------------------------------------';
					foreach(@$_SESSION['cart'] as $product_id => $prod) {
						$product = $this->Product->findById(Sanitize::paranoid($product_id));
						if($all_price['Price']['id'] == $product['Product']['price_id']){
							$need_send = true;
						$this->Sendmail->message.=' 
					|  Наименование запчасти: '.$product['Product']['name'].'  
					|  Марка и модель авто: '.$product['Product']['mark_and_model'].'
					|  Годы выпуска: '.$product['Product']['year1'].' - '.$product['Product']['year2'].'
					|  Ориг. номер: '.$product['Product']['number'].' 
					|  Артикул: '.$product['Product']['articul'].'
					|  Доп. информация о запчасти: '.$product['Product']['description'].'  
					|  Количество: '.$prod['count'].' 
					+------------------------------------------------------------------';
					}
					}	
					foreach(@$_SESSION['cart_from_request'] as $answer_id => $prod) {
						$answer = $this->RequestAnswer->findById(Sanitize::paranoid($answer_id));
						if($all_price['Price']['id'] == $prod['Product']['price_id']){
							$need_send = true;
						$this->Sendmail->message.=' 
					|  Наименование запчасти: '.$prod['name'].'  
					|  Марка и модель авто: '.$prod['mark_and_model'].'
					|  Год выпуска: '.$prod['year'].'
					|  Ориг. номер: '.$prod['number'].' 
					|  Доп. информация о запчасти: '.$prod['description'].'  
					|  Количество: '.$prod['count'].' 
					+------------------------------------------------------------------';}
					}	
					foreach(@$_SESSION['cart_from_search_by_num'] as $product_id => $prod) {
						$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$product_id."'"));
						if($all_price['Price']['id'] == $product['price_id']){
							$need_send = true;
						$this->Sendmail->message.=' 
					|  Наименование запчасти: '.$product['name'].'  
					|  Производитель: '.$product['manufacturer'].'
					|  Номер: '.$product['manuf_number'].'  
					|  Количество: '.$prod['count'].' 
					+------------------------------------------------------------------';}
					}		
					
					if($need_send == true)
						$this->Sendmail->send();
				}
			}
				//--------------------------------------
				
				//автото
				if(@$_SESSION['cart_from_avtoto']) {
					try {
						$client = new SoapClient("http://www.avtoto.ru/services/search/soap.wsdl", 
						Array('soap_version' => SOAP_1_1));

						// Запчасть для добавления в корзину
						$elements_for_basket = array();
						foreach(@$_SESSION['cart_from_avtoto'] as $product_id => $prod) {
							$element["Code"] = $prod["code"];
							$element["Manuf"] = $prod["manuf"];
							$element["Name"] = iconv('WINDOWS-1251','UTF-8',$prod["name"]);
							$element["Count"] = $prod["count"];
							$element["PartId"] = $product_id % 1000;
							$element["Storage"] = iconv('WINDOWS-1251','UTF-8',$prod["Storage"]);
							$element["Delivery"] = $prod["Delivery"];
							$element["Price"] = $prod["Price"];
							$element["SearchID"] = $prod["SearchID"];
							$element["RemoteID"] = $prod["RemoteID"];
							$element["BaseCount"] = $prod["BaseCount"];
							$elements_for_basket[] = $element;
						}
						

						// Параметры для добавления в корзигу
						$params_basket = Array(
							'user' => Array(
								'user_id' => 33593, 
								'user_login' => 'detalline',
								'user_password' => 'cvbnmx1'
							),
							'parts' => $elements_for_basket
						);
						
						// Добавление в козину
						$result = $client->AddToBasket($params_basket);		

						// Обработка результата
						/*if (isset($result['Done']) && count($result['Done'])==count($elements_for_basket)) {
							echo 'Все эелементы добавлены в корзину';
						} else {
							if (isset($result['Errors']) && count($result['Errors'])>0) {
								echo 'Внимание! ';
								print_r($result['Errors']);
							}
						}*/
					} catch(Exception $e) {
		
					}					
				}


				// Очистка корзины
				unset($_SESSION['cart']);
                        unset($_SESSION['cart_from_request']);
						unset($_SESSION['cart_from_search_by_num']);
						unset($_SESSION['cart_from_avtoto']);
				$this->Session->setFlash('<P><B>Спасибо, Ваш заказ принят. <br>
				Счет либо квитанция на оплату будет выставлена в течении суток после того, как данному заказу будет присвоен статуc "Резерв". 
				Их можно будет скачать на страничке Ваши заказы. 
</B></P>','flash');	
				$this->redirect("/orders.html");			
			}		
		} else {
		
			$this->data['Order']['lname'] = $row['Customer']['lname'];
			$this->data['Order']['fname'] = $row['Customer']['fname'];
			$this->data['Order']['otch'] = $row['Customer']['otch'];
			$this->data['Order']['mphone'] = $row['Customer']['mphone'];
			$this->data['Order']['hphone'] = $row['Customer']['hphone'];
			$this->data['Order']['address1'] = $row['Customer']['address1'];
			$this->data['Order']['address2'] = $row['Customer']['address2'];
			$this->data['Order']['city'] = $row['Customer']['city'];
			$this->data['Order']['zipcode'] = $row['Customer']['zipcode'];	
			$this->data['Order']['region_id'] = $row['Customer']['region_id'];

			$this->data['Customer'] = $row['Customer'];
		}
	}
	
	function schet() {

    if(!$_GET['id'])
      $this->redirect("/");
    
    $this->layout = "blank";
    
    if($this->othAuth->group("id")>2)
      $data = $this->Order->findAll("Order.id='".Sanitize::paranoid($_GET['id'])."' AND Customer.id='".$this->othAuth->user("id")."'"); 
    else if ( strpos($_SERVER['HTTP_REFERER'],'detalline.ru/admin/orders') !== false || strpos($_SERVER['HTTP_REFERER'],'detalline333.ru/admin/orders') !== false)
      $data = $this->Order->findAll("Order.id='".Sanitize::paranoid($_GET['id'])."' "); 
    $this->set('data', $data[0]);
	$this->set('credentials', $this->Credential->findAll());

    if($data[0]["Order"]["payment_id"]==3) {
      if(isset($_GET['check']))
		$this->render("cash");
	  else
		$this->render("sbrf");
	}
    elseif($data[0]["Order"]["payment_id"]==2) 
      $this->render("wire");
    elseif($data[0]["Order"]["payment_id"]==1) 
      $this->render("cash");
    else
      $this->redirect("/");
	}
	
	function __construct() {
		$this->show = empty($_GET['show'])? '10': Sanitize::paranoid($_GET['show']);
		$this->sortBy = empty($_GET['sort'])? 'created': Sanitize::paranoid($_GET['sort']);
		$this->direction = empty($_GET['direction'])? 'DESC': Sanitize::paranoid($_GET['direction']);
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}
	
	function orders(){
	
		$responsibles=$this->Responsible->findAll("Where customer_id=".$this->othAuth->user('id'));
		if(empty($responsibles))
			$this-redirect('/');
		
		$this->set('title','Заказы');
		$this->set("payments",$this->Order->Payment->findAll());

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

	function result_pay(){
	
		$mrh_pass2 = "tdm909bVk";

		$tm=getdate(time()+9*3600);
		$date=date("Y-m-d H:i:s");
	
		$out_summ = $_REQUEST["OutSum"];
		$inv_id = $_REQUEST["InvId"];
		$shp_item = $_REQUEST["Shp_item"];
		$crc = $_REQUEST["SignatureValue"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_item=$shp_item"));
		
		$result['Turnover']['order_id']=$inv_id;
		$result['Turnover']['date']=$date;
		$result['Turnover']['price']=$out_summ;
		$result['Turnover']['issued']=0;
		if ($my_crc !=$crc)
		{
			exit;
		}
		
		$this->Turnover->save($result);
	}
	
}
	