<?php
class CallsController extends AppController
{	
	var $uses = array("Call","CallMessage","Message","Request","Order","Reverse","ReverseMessage");
	
	function _call_center_pre()
	{
		$this->layout = "call_center";
	}
	function center()
	{
		$this->_call_center_pre();
	}
	
	function center_email()
	{
		$this->_call_center_pre();
		
		if(isset($_POST['send'])) {
			// отправка письма клиенту
			$this->Sendmail->subject = $_POST['subject'];
			$this->Sendmail->message = 
$_POST['body'];

			$this->Sendmail->to_email = $_POST['email'];
			$this->Sendmail->to_name = $_POST['email'];
			$this->Sendmail->send();
			//--------------------------------------

			$data = array();
			$data['Message']['fio'] = 'Оператор';
			$data['Message']['email'] =  $_POST['email'];
			$data['Message']['subject'] =  $_POST['subject'];
			$data['Message']['body'] =  $_POST['body'];
			$data['Message']['new'] = 0;
			$this->Message->save($data);
			unset($data);
		} 
		if(!empty($_GET['id']))	{
		    $message = $this->Message->read(null, Sanitize::paranoid($_GET['id']));
			
			$message['Message']['new'] = 0;
			$this->Message->save($message);
			
			$this->set('row', $message);
			
			$other_messages = $this->Message->findAll("email='".$message["Message"]["email"]."'", null, "created ASC");
			$this->set('other_messages', $other_messages);
		} 
	}
	
	function center_call()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		    $call = $this->Call->read(null, Sanitize::paranoid($_GET['id']));
			
			$call['Call']['new'] = 0;
			$this->Call->save($call);
			
			$this->set('call', $call);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function center_order()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		    $order = $this->Order->read(null, Sanitize::paranoid($_GET['id']));
			
			$order['Order']['new'] = 0;
			$this->Order->save($order);
			
			$this->set('row', $order);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function center_request()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		    $request = $this->Request->read(null, Sanitize::paranoid($_GET['id']));
			
			$request['Request']['new'] = 0;
			$this->Request->save($request);
			
			$this->set('row', $request);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function center_message()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		    $message = $this->Message->read(null, Sanitize::paranoid($_GET['id']));
			
			$message['Message']['new'] = 0;
			$this->Message->save($message);
			
			$this->set('row', $message);
			
			$other_messages = $this->Message->findAll("email='".$message["Message"]["email"]."'", null, "created ASC");
			$this->set('other_messages', $other_messages);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function center_reverse()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		$_GET['order']=$_GET['id'];
		    $message = $this->Reverse->read(null, Sanitize::paranoid($_GET['order']));
			
			$message['Reverse']['new'] = 0;
			$this->Reverse->save($message);
			
			$this->set('rows', $message);
			$order = $this->Order->read(null, Sanitize::paranoid($message['Reverse']['order_id']));
			$this->set('row', $order);
			$other_messages = $this->Reverse->findAll("email='".$message["Reverse"]["email"]."'", null, "created ASC");
			$this->set('other_messages', $other_messages);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function center_callback()
	{
		$this->_call_center_pre();
		
		if(!empty($_GET['id']))	{
		    $call = $this->Call->read(null, Sanitize::paranoid($_GET['id']));
			$call['Call']['new'] = 0;
			$this->Call->save($call);
			
			$this->set('row', $call);
		} else {
			$this->redirect("/call_center.html");	
		}
	}
	
	function process()
	{
		$this->layout = "ajax";
		$function = $_POST['function'];   
		$log = array();		
		switch($function) {
		
			case('getState'):
				 
				 $log['state'] = $this->CallMessage->findCount("call_id = ".Sanitize::paranoid($_POST['chatId']),2);
				 break;	
    	
			case('update'):
				$state = $_POST['state'];
				$chatId = Sanitize::paranoid($_POST['chatId']);
				$count =  $this->CallMessage->findCount("call_id = ".$chatId,2);
				$call =  $this->Call->read(null,$chatId);
				if(!empty($call['Call']['exit']))
					$count++;
				$hasNew = false;
				
				 if($state == $count){
					 $log['state'] = $count;
					 $log['text'] = false;					 
					 }
					 else{
						 $text= array();
						 $log['state'] = $count;
						 $data = $this->CallMessage->findAll("call_id = ".$chatId);
						 $line_num = 0;	
                         if(!empty($call['Call']['exit']))
							$state--;				
							
						 foreach ($data as $line)
						 {
							if($line_num >= $state){
								if($_POST['admin']=='admin')
									$str = '<span>'.($line['CallMessage']['user_id'] ? 'Оператор' : 'Клиент').':</span>'.date('d.m.Y H:i',strtotime($line['CallMessage']['created'])).' '.$line['CallMessage']['text'];
							    else
									$str = '<span>'.($line['CallMessage']['user_id'] ? 'Оператор' : 'Я').':</span>'.date('d.m.Y H:i',strtotime($line['CallMessage']['created'])).' '.$line['CallMessage']['text'];								
								$text[] = iconv("WINDOWS-1251", "UTF-8", $str);			
								$hasNew = $line['CallMessage']['user_id'] > 0 ? true : false;
							}
							$line_num ++;
						}
						
						if(!empty($call['Call']['exit']) && $_POST['admin']=='admin') {
							$str = '<font color=red>Клиент вышел '.date('d.m.Y в H:i:s',strtotime($call['Call']['exit'])).'. 
							При отправке новых сообщений переписка будет выслана на email клиенту.</font>';
							$text[] = iconv("WINDOWS-1251", "UTF-8", $str);		
						}
						
						 $log['text'] = $text; 
						 $log['hasNew'] = $hasNew;
					 }
				  
				 break;
				 
			case('update_menu'):
			    
				$calls = $this->Call->findAll(" callback=0 AND (Call.new=1 OR (1=1 "
					.(!empty($_POST['custName']) ? " and name like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and 1=0 " :"")
					.(!empty($_POST['custEmail']) ? " and email like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custEmail'])."%' " :"")
					.(!empty($_POST['custPhone']) ? " and phone like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custPhone'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Call.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Call.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.")) ");
				$callbacks = $this->Call->findAll(" callback=1 AND (Call.new=1 OR (1=1  "
					.(!empty($_POST['custName']) ? " and name like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and 1=0 " :"")
					.(!empty($_POST['custEmail']) ? " and 1=0 " :"")
					.(!empty($_POST['custPhone']) ? " and phone like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custPhone'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Call.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Call.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.")) ");
				$messages = $this->Message->findAll(" Message.fio <> 'Оператор' AND (Message.new=1 OR (1=1 "
					.(!empty($_POST['custName']) ? " and fio like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and fio like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custLName'])."%' " :"")
					.(!empty($_POST['custPhone']) ? " and 1=0 " :"")
					.(!empty($_POST['custEmail']) ? " and email like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custEmail'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Message.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Message.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.")) ");
				$requests = $this->Request->findAll(" Request.customer_name IS NULL AND (Request.new=1 OR (1=1 "
					.(!empty($_POST['custName']) ? " and Customer.fname like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and Customer.lname like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custLName'])."%' " :"")
					.(!empty($_POST['custEmail']) ? " and Customer.email like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custEmail'])."%' " :"")
					.(!empty($_POST['custPhone']) ? " and Customer.mphone like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custPhone'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Request.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Request.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.")) ");
				$orders = $this->Order->findAll(" Order.new=1 OR (1=1 "
					.(!empty($_POST['custName']) ? " and Customer.fname like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and Customer.lname like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custLName'])."%' " :"")
					.(!empty($_POST['custEmail']) ? " and Customer.email like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custEmail'])."%' " :"")
					.(!empty($_POST['custPhone']) ? " and Customer.mphone like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custPhone'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Order.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Order.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.") ");
				$reverse = $this->Reverse->findAll(" Reverse.name <> 'Оператор' AND (Reverse.new=1 OR (1=1 "
					.(!empty($_POST['custName']) ? " and name like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custName'])."%' " :"")
					.(!empty($_POST['custLName']) ? " and name like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custLName'])."%' " :"")
					.(!empty($_POST['custPhone']) ? " and phone like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custPhone'])."%' " :"")
					.(!empty($_POST['custEmail']) ? " and email like '%".iconv("UTF-8", "WINDOWS-1251", $_POST['custEmail'])."%' " :"")
					.(!empty($_POST['dateS']) ? " and Reverse.created >= '".date('Y-m-d 00:00:00', strtotime($_POST['dateS']))."' " :"")
					.(!empty($_POST['datePo']) ? " and Reverse.created <= '".date('Y-m-d 23:59:59', strtotime($_POST['datePo']))."' " :"")
					.")) ");
		
		        $hasNew = false;
				$str = '<h4 style="padding-top:0px;margin-top:0px;">On-line консультант</h4>
				<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">'; 
				foreach($calls as $call) {
					if($call['Call']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_call.html?id='.$call['Call']['id'].'">'.$call['Call']['name'].'</a> '.($call['Call']['new'] ? ' <font color=red><b>Новое сообщение!</b></font>' : '').'</li>';
				}
				$str .= '</ul>
				<h4 style="padding-top:0px;margin-top:0px;">Обратный звонок</h4>
				<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">';
				foreach($callbacks as $call) {
					if($call['Call']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_callback.html?id='.$call['Call']['id'].'">'.$call['Call']['name'].'</a> '.($call['Call']['new'] ? ' <font color=red><b>Новый!</b></font>' : '').'</li>';
				}
				$str .= '</ul>
	<h4 style="padding-top:0px;margin-top:0px;">Обратная связь</h4>
	<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">';
				foreach($messages as $call) {
					if($call['Message']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_message.html?id='.$call['Message']['id'].'">'.$call['Message']['fio'].'</a> '.($call['Message']['new'] ? ' <font color=red><b>Новое!</b></font>' : '').'</li>';
				}
				$str .= '</ul>
	<h4 style="padding-top:0px;margin-top:0px;">Запросы запчастей</h4>
	<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">';
				foreach($requests as $call) {
					if($call['Request']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_request.html?id='.$call['Request']['id'].'">'.$call['Customer']['lname'].' '.$call['Customer']['fname'].' '.$call['Customer']['otch'].'</a> '.($call['Request']['new'] ? ' <font color=red><b>Новый!</b></font>' : '').'</li>';
				}
				$str .= '</ul>
	<h4 style="padding-top:0px;margin-top:0px;">Заказы</h4>
	<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">';
				foreach($orders as $call) {
					if($call['Order']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_order.html?id='.$call['Order']['id'].'">'.$call['Customer']['lname'].' '.$call['Customer']['fname'].' '.$call['Customer']['otch'].'</a> '.($call['Order']['new'] ? ' <font color=red><b>Новый!</b></font>' : '').'</li>';
				}
				$str .= '</ul>
	<h4 style="padding-top:0px;margin-top:0px;">Задать вопрос</h4>
	<ul id="fields_requests" style="list-style:none;padding:0px;margin:0px;">';
				foreach($reverse as $call) {
					if($call['Reverse']['new'])
						$hasNew = true;
					$str .= '<li><a href="/call_center_reverse.html?id='.$call['Reverse']['id'].'"> заказ №'.$call['Reverse']['order'].' от '.$call['Reverse']['name'].'</a> '.($call['Reverse']['new'] ? ' <font color=red><b>Новое!</b></font>' : '').'</li>';
				}
				$str .= '</ul>';
				$str = iconv("WINDOWS-1251", "UTF-8", $str);
				$log['text'] =$str;
				$log['hasNew'] =$hasNew;
				 break;
			
			case('send'):
				 $chatId = Sanitize::paranoid($_POST['chatId']);				 
				 $message = htmlspecialchars(strip_tags(iconv("UTF-8", "WINDOWS-1251", $_POST['message'])));
				 if(($message) != "\n"){
					$data['CallMessage']['call_id'] = $chatId;
					$data['CallMessage']['text'] = str_replace("\n", " ", $message);
					$data['CallMessage']['user_id'] = $_POST['admin']=='admin' ?  $this->othAuth->user("id") : null;					
					$this->CallMessage->save($data);

					$data['Call']['id'] = $chatId;
					$data['Call']['new'] = $_POST['admin']!='admin' ? 1 : 0;
					$this->Call->save($data);
					
					$call =  $this->Call->read(null,$chatId);
					if(!empty($call['Call']['exit'])) {
						$data = $this->CallMessage->findAll("call_id = ".$chatId);
						
						// отправка письма клиенту
						$this->Sendmail->subject = 'Ваша переписка с оператором DETALLINE.RU (Деталь-Лайн)';
						$this->Sendmail->message = 
'Уважаемый(ая) '.$call['Call']['name'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей DETALLINE.RU.

+------------------------------------------------------------------
|   ВАША ПЕРЕПИСКА С ОПЕРАТОРОМ
|   '.date('d.m.Y',strtotime($call['Call']['created'])).'
+------------------------------------------------------------------';
foreach ($data as $line) {
$this->Sendmail->message.= '
|  '.($line['CallMessage']['user_id'] ? 'Оператор' : 'Я').': '.date('d.m.Y H:i',strtotime($line['CallMessage']['created'])).' '.$line['CallMessage']['text'].'
+------------------------------------------------------------------';
}			
$this->Sendmail->message .='

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
8-904-503-07-07
http://detalline.ru/
support@detalline.ru
';

						$this->Sendmail->to_email = $call['Call']['email'];
						$this->Sendmail->to_name = $call['Call']['name'];
						$this->Sendmail->send();
						//--------------------------------------	
					}
				 }
				 break;
			 
			 case('close'):
				 $chatId = Sanitize::paranoid($_POST['chatId']);
				 $data['Call']['id'] = $chatId;
				 $data['Call']['new'] = $_POST['admin']!='admin' ? 1 : 0;
				 $data['Call']['exit'] = date('Y-m-d H:i:s');
				 $this->Call->save($data);
				 break;
		}
		echo json_encode($log);

	}
	
	function add($id) 
	{
        $this->layout = "blank";
        if (!empty($this->data)) {		
				$errors = false;
				if(!$this->Call->validates($this->data)) {	
					$errors = true;
				}
				if(empty($this->data['Call']['model'])) {
					$this->Call->invalidate('model');
					$errors = true;
				}
				if(empty($this->data['Call']['email']) || !preg_match('/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i', $this->data['Call']['email'])) {
					$this->Call->invalidate('email');
					$errors = true;
				}
				if(!$this->CallMessage->validates($this->data)) {	
					$errors = true;
				}
                	
				if(!$errors) {

					$this->Call->save($this->data);
					
					$this->data['CallMessage']['call_id'] = $this->Call->id;
					$this->CallMessage->save($this->data);
					
           			$this->redirect("call.html?id=".$this->Call->id);		
				} else {
					$this->Session->setFlash('<font color=red>Обнаружены ошибки</font><p>','flash');
				}
		}
		else if(!empty($_GET['id']))	{
		    $this->layout = "ajax";
			//$call = $this->Call->read(null, Sanitize::paranoid($_GET['id']));
			//$this->set('call', $call);
			$this->render('chat');
		}
	}
	
	function add_callback() 
	{
        $this->layout = "blank";
        if (!empty($this->data)) {		
				$errors = false;
				if(!$this->Call->validates($this->data)) {	
					$errors = true;
				}				
				if(!$this->CallMessage->validates($this->data)) {	
					$errors = true;
				}               	
				if(!$errors) {

					$this->data['Call']['callback'] = 1;
					$this->Call->save($this->data);
					
					$this->data['CallMessage']['call_id'] = $this->Call->id;
					$this->CallMessage->save($this->data);
					
           			$this->set("sent","1");
				} else {
					$this->Session->setFlash('<font color=red>Обнаружены ошибки</font><p>','flash');
				}
			 if(!empty($_GET['id']))	{
		    $this->layout = "ajax";
			$call = $this->Reverse->read(null, Sanitize::paranoid($_GET['id']));
			$this->set('call', $call);
			$this->render('chat');
		}
		}		
	}
	
	function add_reverse() 
	{
        $this->layout = "blank";
		$this->set('order',@$_GET['order']);
		$this->set('name',@$_GET['name']);
		$this->set('phone',@$_GET['phone']);
		$this->set('order_id',@$_GET['order_id']);
		
		$rows=$this->Reverse->findAll('WHERE Reverse.order='.$_GET['order']);
		if(!empty($rows)) foreach($rows as $row) {$this->redirect("reverse.html?orderid=".$row['Reverse']['id']);}
		
		
        if (!empty($this->data)) {		
				$errors = false;				
				if(!$this->ReverseMessage->validates($this->data)) {	
					$errors = true;
				}
				if(empty($this->data['Reverse']['email']) || !preg_match('/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i', $this->data['Reverse']['email'])) {
					$this->Reverse->invalidate('email');
					$errors = true;
				}
				if(!$errors) {

					$this->Reverse->save($this->data);
					
					$this->data['ReverseMessage']['reverse_id'] = $this->Reverse->id;
					$this->ReverseMessage->save($this->data);
					
           			$this->redirect("reverse.html?orderid=".$this->Reverse->id);
				} else {
					$this->Session->setFlash('<font color=red>Обнаружены ошибки</font><p>','flash');
				}
						
				 
		}
				if(!empty($_GET['orderid']))	{					
					//$call = $this->Reverse->read(null, Sanitize::paranoid($_GET['id']));
					//$this->set('call', $call);
					$this->layout = "ajax";
					$this->render('chat');
				}		
	}
	
	function processr()
	{
		$this->layout = "ajax";
		$function = $_POST['function'];   
		$log = array();		
		switch($function) {
		
			case('getState'):
				 
				 $log['state'] = $this->ReverseMessage->findCount("reverse_id = ".Sanitize::paranoid($_POST['chatId']),2);
				 break;	
    	
			case('update'):
				$state = $_POST['state'];
				$chatId = Sanitize::paranoid($_POST['chatId']);
				$count =  $this->ReverseMessage->findCount("reverse_id = ".$chatId,2);
				$call =  $this->Reverse->read(null,$chatId);
				
				$hasNew = false;
				
				 if($state == $count){
					 $log['state'] = $count;
					 $log['text'] = false;					 
					 }
					 else{
						 $text= array();
						 $log['state'] = $count;
						 $data = $this->ReverseMessage->findAll("reverse_id = ".$chatId);
						 $line_num = 0;	
                         if(!empty($call['Call']['exit']))
							$state--;				
							
						 foreach ($data as $line)
						 {
							if($line_num >= $state){
								if($_POST['admin']=='admin')
									$str = '<span>'.($line['ReverseMessage']['user_id'] ? 'Оператор' : 'Клиент').':</span>'.date('d.m.Y H:i',strtotime($line['ReverseMessage']['created'])).' '.$line['ReverseMessage']['text'];
							    else
									$str = '<span>'.($line['ReverseMessage']['user_id'] ? 'Оператор' : 'Я').':</span>'.date('d.m.Y H:i',strtotime($line['ReverseMessage']['created'])).' '.$line['ReverseMessage']['text'];								
								$text[] = iconv("WINDOWS-1251", "UTF-8", $str);			
								$hasNew = $line['ReverseMessage']['user_id'] > 0 ? true : false;
							}
							$line_num ++;
						}
						
											
						 $log['text'] = $text; 
						 $log['hasNew'] = $hasNew;
					 }
				  
				 break;
				 
				 case('send'):
				 $chatId = Sanitize::paranoid($_POST['chatId']);				 
				 $message = htmlspecialchars(strip_tags(iconv("UTF-8", "WINDOWS-1251", $_POST['message'])));
				 if(($message) != "\n"){
					$data['ReverseMessage']['reverse_id'] = $chatId;
					$data['ReverseMessage']['text'] = str_replace("\n", " ", $message);
					if($_POST['admin']=='admin'){$data['ReverseMessage']['user_id'] = $_POST['admin']=='admin' ?  $this->othAuth->user("id") : null;	}
					$this->ReverseMessage->save($data);

					$data['Reverse']['id'] = $chatId;
					$data['Reverse']['new'] = $_POST['admin']!='admin' ? 1 : 0;
					$this->Reverse->save($data);
					
					$call =  $this->Reverse->read(null,$chatId);
					/*if(!empty($call['Call']['exit'])) {
						$data = $this->CallMessage->findAll("call_id = ".$chatId);	
					}*/
				 }
				 break;
		}
		echo json_encode($log);

	}
	
}