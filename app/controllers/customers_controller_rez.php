<?php
class CustomersController extends AppController
{
       var $show = 15;
       var $uses = array("Customer","Product","Order","Mark","Model","Category","Article","Subcategory","EngineVolume","EngineType","WheelDrive","BodyType","Transmission","Market","PartState","Request","RequestPart","RequestAnswer","City","Ip","RegistrDiscount","Price","PriceParamValue","PriceParamName","Manufacturer");
	
        function __construct()
	{
	 	parent::__construct();
	 	
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
	}

	function beforeFilter() {	 
	 	parent::beforeFilter();	 	
		$this->set('regions',$this->Customer->Region->findAll(null,null,'Region.name ASC'));
	}
	
	function where() 
	{	
		$this->set('regions',$this->Customer->Region->findAll());
		if(isset($_POST['save'])){
			mysql_query("DELETE FROM ips WHERE ip='".$_SERVER["REMOTE_ADDR"]."'");
			$ip['Ip']['ip']=$_SERVER["REMOTE_ADDR"];
			$ip['Ip']['region_id']=$_POST['region_id'];
			$ip['Ip']['city_id']=$_POST['city'];
			$this->Ip->save($ip);
			$_SESSION['where']=$_POST['city'];
			$this->redirect('');
		}
	}
	
	function registr() 
	{	

		if (!empty($this->data)) {
		
				$errors = false;
			 	if(!$this->Customer->validates($this->data)) {	
					$errors = true;
				}
				
				if($this->data['Customer']['password']!=$this->data['Customer']['password_again'])  {
					$this->Customer->invalidate('password_again');
					$errors = true;
				} 
				
				if($this->Customer->findByEmail($this->data['Customer']['email']))  {
					$this->Customer->invalidate('email_exist');
					$errors = true;
				}
				if(!isset($_POST['nextStep']) && !$this->Tuning->CheckCode($this->data['Customer']['code'], $this->data['Customer']['hash'])) 	   			{
					$this->Customer->invalidate('code');
					$errors = true;
				} 
				if(isset($_POST[postavshik])) {
					$selected_marks = array();
					foreach($_POST as $key=>$val) {
						if(strpos($key,"chk_mark_")!==false) {
							$selected_marks[] = str_replace("chk_mark_","",$key);
						}
					}
					if(count($selected_marks)==0) {
						$this->Customer->invalidate('marks');
					    $errors = true;
					}
				}
				
				if(isset($_POST['jur_lico'])) {
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
			 	if(!$errors) {
				
					if($this->data['Customer']['city']=='1r6d1')
						$this->data['Customer']['city']=$_POST['city'];
					else{
						$city['City']['region_id']=$this->data['Customer']['region_id'];
						$city['City']['name']=$this->data['Customer']['city'];
						$city['City']['id']=$this->City->findCount()+1;
						$this->City->save($city);
						$this->data['Customer']['city']=$city['City']['id'];
					}
				
					$this->data['Customer']['login'] = $this->data['Customer']['email'];
					$this->data['Customer']['passw'] = $this->data['Customer']['password'];
					$this->data['Customer']['site_url'] ='detalline.ru';
					
					$discount = $this->RegistrDiscount->read(null,1);
					$this->data['Customer']['skidka'] = $discount['RegistrDiscount']['discount'];
					
					
					$this->data['Customer']['password'] = md5($this->data['Customer']['password']); 
					if(isset($_POST[postavshik])) {
						$customer_price_id = mysql_fetch_row(mysql_query("SELECT max(id) FROM `prices` "));
						if($customer_price_id[0] < 200) 
							$customer_price_id[0] = 200;
						else
							$customer_price_id[0]++;
						$this->data['Customer']['price_id'] = $customer_price_id[0];
						$this->data['Customer']['group_id'] = 4; 
						$this->data['Customer']['marks'] = implode(',',$selected_marks);
						$price['Price']['id'] = $customer_price_id[0];
						$this->Price->save($price);
					} 
					$this->Customer->save($this->data);
					
					mysql_query("DELETE FROM ips WHERE ip='".$_SERVER["REMOTE_ADDR"]."'");
					$ip['Ip']['ip']=$_SERVER["REMOTE_ADDR"];
					$ip['Ip']['region_id']=$this->data['Customer']['region_id'];
					$ip['Ip']['city_id']=$this->data['Customer']['city'];
					$this->Ip->save($ip);
					$_SESSION['where']=$_POST['city'];
					$auth_num = $this->othAuth->login($this->data['Customer']);

					if($this->logged = $this->othAuth->check()) {						
							if(isset($_POST['nextStep'])) {
									$this->redirect("/check_out.html");
								}
								else {
									$this->Session->setFlash('Спасибо за регистрацию. Ваш аккаунт активен.','flash');	
									$this->render("message");
								}						
					}
					else {
						$this->Session->setFlash('<p><font color=red>'.$this->othAuth->getMsg($auth_num).'</font>','flash');
						if(isset($_POST['nextStep'])) {
							$this->render("check_out_signin");
						}
					}
				}
				else {
					$this->Session->setFlash('<p><font color=red>При сохранении обнаружены ошибки</font>','flash');
					$this->data['Customer']['password'] = "";
					$this->data['Customer']['password_again'] = "";
					if(isset($_POST['nextStep'])) {
						$this->render("check_out_signin");
					}
				}
		}


  // captcha
	 	$this->Tuning->width = 35;
		$this->Tuning->height = 15;
		$this->Tuning->GetCode();
		$this->Tuning->msg = $this->Tuning->hashcode;
		$this->Tuning->DrawImage();
	 	$this->set('tuning_url','/files/t.png');
	 	$this->set('tuning_hash',$this->Tuning->hashvalue);
	 	//------------
	 	
		$this->set('title', (isset($_GET[postavshik]) || isset($_POST[postavshik]) ? 'Регистрация поставщика.':'Регистрация покупателя.').' Интернет-магазин SM-JM.RU (Ростов-на-Дону)');
		$this->set('meta_description', '');
		$this->set('meta_keywords', '');
	}
	
    function add_request() {
                  $this->set('title', 'Запрос запчастей');
                 
                  $this->set('engine_volumes', $this->EngineVolume->findAll(null,null, "name ASC"));
$this->set('engine_types', $this->EngineType->findAll(null,null, "name ASC"));
$this->set('wheel_drives', $this->WheelDrive->findAll(null,null, "name ASC"));
$this->set('body_types', $this->BodyType->findAll(null,null, "name ASC"));
$this->set('transmissions', $this->Transmission->findAll(null,null, "name ASC"));
$this->set('markets', $this->Market->findAll(null,null, "name ASC"));
$this->set('part_states', $this->PartState->findAll(null,null, "name ASC"));

                if(isset($this->params['data']['Customer']))
		{ 
			$auth_num = $this->othAuth->login($this->params['data']['Customer']);			
			$this->set('auth_msg', "<br><font color=red>Ошибка. ".$this->othAuth->getMsg($auth_num).".</font>");
			$this->logged = $this->othAuth->check();
		}

                 if(isset($this->params['data']['Request']))
                 {		 
			
                        $this->data['Request']['customer_id'] = $this->othAuth->user("id");
                        $this->data['Request']['model_id'] = $_POST['model'];

                        $errors = false;
						if(!$this->Request->validates($this->data)) {	
							$errors = true;
						}
						if(empty($this->data['Request']['customer_id'])) {
							$this->Request->invalidate('customer_id');
							$errors = true;
						}
						if(empty($this->data['Request']['engine_type_id'])) {
							$this->Request->invalidate('engine_type_id');
							$errors = true;
						}
						if(!preg_match('/^[0-9]+$/',$this->data['Request']['engine_power'])) {
							$this->Request->invalidate('engine_power');
							$errors = true;
						}
						if(empty($this->data['Request']['wheel_drive_id'])) {
							$this->Request->invalidate('wheel_drive_id');
							$errors = true;
						}
						if(empty($this->data['Request']['body_type_id'])) {
							$this->Request->invalidate('body_type_id');
							$errors = true;
						}
						if(empty($this->data['Request']['transmission_id'])) {
							$this->Request->invalidate('transmission_id');
							$errors = true;
						}
						if(empty($this->data['Request']['market_id'])) {
							$this->Request->invalidate('market_id');
							$errors = true;
						}
						
                        $request_parts_errors = array();
                        $i=0;	
                        foreach($this->data['RequestParts'] as $request_part) {
                                $i++;
                                if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && empty($request_part["part_state_id"]))	{
                                         $request_parts_errors[$i]['part_state_id'] = '<br><font color=red><small>обязательное поле</small></font>';
                                          $errors = true;
                                }			
			}

			if(!$errors) {
                                       $this->Request->save($this->data);

                                        $request_id = $this->Request->getLastInsertId();

                                        foreach($this->data['RequestParts'] as $request_part) {
                                                 if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && !empty($request_part["part_state_id"]))	{
					                     $request_part["request_id"] = $request_id;
                                                             $request_part["number"] = $request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"];
					                     $data["RequestPart"] = $request_part;
					                     $this->RequestPart->save($data);
                                                             unset($this->RequestPart->id);
                                                }
				      }
    
$row_customer = $this->Customer->read(null,$this->data['Request']['customer_id']);                                  
$row_mark = $this->Mark->read("name",$this->data['Request']['mark_id']);
$row_model = $this->Model->read("name",$this->data['Request']['model_id']);

                // отправка письма админу (поставщику)
				$this->Sendmail->subject = 'Новый запрос запчастей';
				$this->Sendmail->message = 
'+------------------------------------------------------------------
|   НОВЫЙ ЗАПРОС ЗАПЧАСТЕЙ
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------	
|  от покупателя: 
+------------------------------------------------------------------
|  ID: '.$row_customer['Customer']['id'].'
|  Имя: '.$row_customer['Customer']['lname'].' '.$row_customer['Customer']['fname'].' '.$row_customer['Customer']['otch'].'
|  Email: '.$row_customer['Customer']['email'].'
|  Дом. телефон: '.($row_customer['Customer']['hphone']?$row_customer['Customer']['hphone']:'-').'
|  Моб. телефон: '.($row_customer['Customer']['mphone']?$row_customer['Customer']['mphone']:'-').'
+-------------------------------------------------------------------	
|  автомобиль: 
|  '.$row_mark['Mark']['name'].'  '.$row_model['Model']['name'].'  '.$this->data['Request']['year'].'    
+------------------------------------------------------------------	
|  запчасти:';
foreach($this->data['RequestParts'] as $request_part) {
if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && !empty($request_part["part_state_id"]))	{
$row_part_state = $this->PartState->read("PartState.name",$request_part["part_state_id"]);
	$this->Sendmail->message.='
|  '.$request_part["name"].', ориг. номер: '.($request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"]).', состояние:  '.$row_part_state['PartState']['name'];
}
}			
$this->Sendmail->message .='
+------------------------------------------------------------------
|  примечание: 
|  '.$this->data['Request']['comments'].'    
+------------------------------------------------------------------';

				$suppliers = $this->Customer->findAll("Customer.id<>'".$row_customer['Customer']['id']."' AND Customer.group_id=5 AND (Customer.marks like '".$this->data['Request']['mark_id'].",%'
				OR Customer.marks like '%,".$this->data['Request']['mark_id']."' OR Customer.marks like '%,".$this->data['Request']['mark_id'].",%') ");     
				foreach($suppliers as $row_supplier) {
					$this->Sendmail->to_email = $row_supplier['Customer']['email'];
					$this->Sendmail->to_name = $row_supplier['Customer']['lname'].' '.$row_supplier['Customer']['fname'];
					$this->Sendmail->send();
				}
				//-------------------------------------- 


                 // отправка письма клиенту
				$this->Sendmail->subject = 'Ваш запрос запчастей';
				$this->Sendmail->message = 
'Уважаемый(ая) '.$row_customer['Customer']['fname'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей DETALLINE.RU.

+------------------------------------------------------------------
|   ВАШ ЗАПРОС ЗАПЧАСТЕЙ
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------	
|  автомобиль: 
|  '.$row_mark['Mark']['name'].'  '.$row_model['Model']['name'].'  '.$this->data['Request']['year'].'    
+------------------------------------------------------------------	
|  запчасти:';
foreach($this->data['RequestParts'] as $request_part) {
if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && !empty($request_part["part_state_id"]))	{
$row_part_state = $this->PartState->read("PartState.name",$request_part["part_state_id"]);
	$this->Sendmail->message.='
|  '.$request_part["name"].', ориг. номер: '.($request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"]).', состояние:  '.$row_part_state['PartState']['name'];
}
}			
$this->Sendmail->message .='
+------------------------------------------------------------------
|  примечание: 
|  '.$this->data['Request']['comments'].'    
+------------------------------------------------------------------

Просмотреть предложения  по данному запросу можно в разделе "Ваши Запросы Запчастей" в личном кабинете на сайте нашего магазина автозапчастей SM-JM.RU

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
+7 (904) 503 07 07
http://detalline.ru/
support@detalline.ru
';

				$this->Sendmail->to_email = $row_customer['Customer']['email'];
				$this->Sendmail->to_name = $row_customer['Customer']['lname'].' '.$row_customer['Customer']['fname'];
				$this->Sendmail->send();
				//-------------------------------------- 
              
                // отправка писем поставщикам				
				$this->Sendmail->subject = 'Новый запрос запчастей на сайте SM-JM.RU';
				$this->Sendmail->message = 
'Уважаемый поставщик,
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей SM-JM.RU.

Вам пришел запрос запчастей. Ответить на него можно в разделе "Запросы Покупателей" в личном кабинете на сайте нашего магазина автозапчастей SM-JM.RU. 

+------------------------------------------------------------------
|   НОВЫЙ ЗАПРОС ЗАПЧАСТЕЙ
|   '.date('d.m.Y H:i').'
+-------------------------------------------------------------------	
|  автомобиль: 
|  '.$row_mark['Mark']['name'].'  '.$row_model['Model']['name'].'  '.$this->data['Request']['year'].'    
+------------------------------------------------------------------	
|  запчасти:';
foreach($this->data['RequestParts'] as $request_part) {
if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && !empty($request_part["part_state_id"]))	{
$row_part_state = $this->PartState->read("PartState.name",$request_part["part_state_id"]);
		$this->Sendmail->message.='
|  '.$request_part["name"].',  ориг. номер: '.($request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"]).', состояние:  '.$row_part_state['PartState']['name'];
}
}			
$this->Sendmail->message .='
+------------------------------------------------------------------
|  примечание: 
|  '.$this->data['Request']['comments'].'    
+------------------------------------------------------------------

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
+7 (904) 503 07 07
http://detalline.ru/
support@detalline.ru
';
				$suppliers = $this->Customer->findAll("Customer.id<>'".$row_customer['Customer']['id']."' AND Customer.group_id=4 AND (Customer.marks like '".$this->data['Request']['mark_id'].",%'
				OR Customer.marks like '%,".$this->data['Request']['mark_id']."' OR Customer.marks like '%,".$this->data['Request']['mark_id'].",%') ");     
				foreach($suppliers as $row_supplier) {
					$this->Sendmail->to_email = $row_supplier['Customer']['email'];
					$this->Sendmail->to_name = $row_supplier['Customer']['lname'].' '.$row_supplier['Customer']['fname'];
					$this->Sendmail->send();
				}
				//-------------------------------------- 
				
                                $this->Session->setFlash('<p><B>Спасибо, Ваш запрос принят. </B>','flash');	
								$this->redirect("/my_requests.html");		
                        } else {
                                  $this->Session->setFlash('<font color=red>Обнаружены ошибки</font><p>','flash');	
                                  $this->set('request_parts_errors',$request_parts_errors);
                        }
		}

        }

	function personal_details() {
	    $this->set('title', 'Персональные данные');

		if(!$this->logged) {
			$this->redirect("/");
		}  				
		if (!empty($this->data)) {

			$errors = false;
			if(!$this->Customer->validates($this->data)) {	
				$errors = true;
			}
			if($this->othAuth->group('id') == 4 || $this->othAuth->group('id') == 5) {
				$selected_marks = array();
				foreach($_POST as $key=>$val) {
					if(strpos($key,"chk_mark_")!==false) {
						$selected_marks[] = str_replace("chk_mark_","",$key);
					}
				}
				if(count($selected_marks)==0) {
					$this->Customer->invalidate('marks');
					$errors = true;
				}
			}
			if(isset($_POST['jur_lico'])) {
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
			if(!$errors) {
			
					if($this->data['Customer']['city']=='1r6d1')
						$this->data['Customer']['city']=$_POST['city'];
					else{
						$city['City']['region_id']=$this->data['Customer']['region_id'];
						$city['City']['name']=$this->data['Customer']['city'];
						$city['City']['id']=$this->City->findCount()+1;
						$this->City->save($city);
						$this->data['Customer']['city']=$city['City']['id'];
					}
				
				$this->data['Customer']['id'] = $this->othAuth->user("id");
				$this->data['Customer']['email'] = $this->othAuth->user("email");
				if($this->othAuth->group('id') == 4 || $this->othAuth->group('id') == 5) {
					$this->data['Customer']['marks'] = implode(',',$selected_marks);
				}
				if(!isset($_POST['jur_lico'])) {
					$this->data['Customer']['organization'] = $this->data['Customer']['jur_address'] = $this->data['Customer']['inn'] =
					$this->data['Customer']['kpp'] = $this->data['Customer']['ogrn'] = $this->data['Customer']['bank'] = $this->data['Customer']['bank_address'] =
					$this->data['Customer']['bik'] = $this->data['Customer']['corr_schet'] = $this->data['Customer']['raschet_shet'] = '';
				}
				mysql_query("DELETE FROM ips WHERE ip='".$_SERVER["REMOTE_ADDR"]."'");
					$ip['Ip']['ip']=$_SERVER["REMOTE_ADDR"];
					$ip['Ip']['region_id']=$this->data['Customer']['region_id'];
					$ip['Ip']['city_id']=$this->data['Customer']['city'];
					$this->Ip->save($ip);
					$_SESSION['where']=$_POST['city'];
				$this->Customer->save($this->data);

				//$this->Session->setFlash('Ваш профайл изменен','flash');
				
				//$this->render("message");
				//return;
				
			} else {
				$this->data['Customer']['email'] = $this->othAuth->user("email");
				$this->Session->setFlash('<p><font color=red>При сохранении обнаружены ошибки</font>','flash');				
				$this->render("personal_details_edit");
				return;
			}
		}
		

		$this->params['data'] = $this->Customer->findByEmail($this->othAuth->user("email"));
		$this->set('city_name', $this->City->read(null,$this->params['data']['Customer']['city']));
		
		if(isset($_POST['delete'])) {
			$this->Customer->del($this->othAuth->user("id"));
			$this->othAuth->logout();
			$this->redirect("/");			
		}
		
		if(isset($_POST['edit'])) {
			$this->render("personal_details_edit");
		}
		else {
			$this->render("personal_details_view");
		}
	}
	
	
	function change_password() {
	
		if(!$this->logged) {
			$this->redirect("/");
		}  	

$this->set('title', 'Смена пароля');

		if (!empty($this->data)) {
												
				$errors = false;
				if($this->othAuth->user('password') != md5($this->data['Customer']['password_old'])) {
					$this->Customer->invalidate('password_old');
					$errors = true;
				}
				
				if(!preg_match('/^[a-z0-9]+$/i',$this->data['Customer']['password'])) {	
					$this->Customer->invalidate('password');
					$errors = true;
				}
				
				if($this->data['Customer']['password'] !== $this->data['Customer']['password_again'] ) {	
					$this->Customer->invalidate('password_again');
					$errors = true;
				}
				
				if(!$errors) {
					$row = $this->Customer->findByEmail($this->othAuth->user("email"));	
					$row['Customer']['password'] = md5($this->data['Customer']['password']);
					
					$row['Customer']['passw'] = $this->data['Customer']['password'];
					$row['Customer']['login'] = $row['Customer']['email'];
					
					$this->Customer->save($row);
					
					$this->othAuth->logout();
					$auth_num = $this->othAuth->login($row['Customer']);
					
					$this->Session->setFlash('Ваш пароль изменен','flash');
				
					$this->render("message");
					return;
				} else {
				
					$this->Session->setFlash('<p><font color=red>Обнаружены ошибки!</font>','flash');
				
				}
		}
	}
	
	function fogotten_password() {
$this->set('title', 'Забыли пароль?');

		if (!empty($this->data)) {
				
				$errors = false;
				if(!$this->data['Customer']['fp_email']) {
					$this->Session->setFlash('<font color=red>Введите email</font>','flash');
					$errors = true;
				}
				elseif(!($row = $this->Customer->findByEmail($this->data['Customer']['fp_email']))) {
					$this->Session->setFlash('<font color=red>Данный email не зарегистрирован</font>','flash');
					$errors = true;
				}
				
				if(!$errors) {
					// генерация нового пароля
					$password_new = $this->Password->generate_password();
					//------------------------------
					
					$rows['Customer']['id'] = $row['Customer']['id'];
	 				$rows['Customer']['password'] = md5($password_new);

					$this->Customer->save($rows);
					
					// отправка письма
					$this->Sendmail->subject = 'Ваш новый пароль';
					$this->Sendmail->message = 
'Уважаемый(ая) '.$row['Customer']['fname'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей Detalline.ru.

Ниже вы найдете новый пароль к Вашему аккаунту на нашем сайте. Мы советуем вам держать эту информацию конфеденциальной.

Логин: '.$row['Customer']['email'].'
Пароль: '.$password_new.'

Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
+7 (904) 503 07 07
http://detalline.ru/
support@detalline.ru
';
					$this->Sendmail->to_email = $row['Customer']['email'];
					$this->Sendmail->to_name = $row['Customer']['fname'];
					$this->Sendmail->send();
					//--------------------------------------
					
					$this->set("sent", "1");
					if(isset($_POST["from_check_out"])){
						$this->render("check_out_fp");
					}
			  }else{
					
					if(isset($_POST["from_check_out"])) {
						$this->render("check_out_fp");
					}
			  }
		}
		
	}
	
	function login() {
$this->set('title', 'Авторизация');

		if (!empty($this->data)) {
		 
			$auth_num = $this->othAuth->login($this->params['data']['Customer']);			
			$this->set('auth_msg', "<font color=red>Ошибка. ".$this->othAuth->getMsg($auth_num).".</font><p>");
			$this->logged = $this->othAuth->check();
			mysql_query("DELETE FROM ips WHERE ip='".$_SERVER["REMOTE_ADDR"]."'");
			$ip['Ip']['ip']=$_SERVER["REMOTE_ADDR"];
			$ip['Ip']['region_id']=$this->othAuth->user('region_id');
			$ip['Ip']['city_id']=$this->othAuth->user('city');
			$this->Ip->save($ip);
			$_SESSION['where']=$this->othAuth->user('city');
		}
	}	
	
	function cart() {
	  $this->set('title', 'Корзина покупок');

		if(!empty($_GET['add'])) {
			$id = Sanitize::paranoid($_GET['add']);
			$product = $this->Product->find('Product.id='.$id);
			if($product) {
				@$_SESSION['cart'][$id]['count'] += intval(@$_GET['skolko']) > 0 ? intval(@$_GET['skolko']) : 1;
			}
			$this->redirect($_SERVER["HTTP_REFERER"]);
		}
		
		if(!empty($_GET['add_from_search_by_num'])) {
			$id = Sanitize::paranoid($_GET['add_from_search_by_num']);
			$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$id."'"));
			if($product) {
				@$_SESSION['cart_from_search_by_num'][$id]['count'] += intval(@$_GET['skolko']) > 0 ? intval(@$_GET['skolko']) : 1;
			}
			$this->redirect($_SERVER["HTTP_REFERER"]);
		}
		
        if(!empty($_GET['add_from_request'])) {
			$answer_id = Sanitize::paranoid($_GET['add_from_request']);
			$answer = $this->RequestAnswer->find('RequestAnswer.id='.$answer_id);
			if($answer) {
                        @$_SESSION['cart_from_request'][$answer_id]['mark_and_model'] = $_GET['mark_and_model'];
                        @$_SESSION['cart_from_request'][$answer_id]['year'] = $_GET['year'];
                        @$_SESSION['cart_from_request'][$answer_id]['name'] = $_GET['name'];
                        @$_SESSION['cart_from_request'][$answer_id]['number'] = $_GET['number'];
				@$_SESSION['cart_from_request'][$answer_id]['count'] += intval(@$_GET['skolko']) > 0 ? intval(@$_GET['skolko']) : 1;
			}
			$this->redirect($_SERVER["HTTP_REFERER"]);
		}
		
		if(isset($_GET['add_from_avtoto'])) {
			$id = Sanitize::paranoid($_GET['add_from_avtoto']);
             @$_SESSION['cart_from_avtoto'][$id]['name'] = $_GET['name'];
             @$_SESSION['cart_from_avtoto'][$id]['code'] = $_GET['code'];
			 @$_SESSION['cart_from_avtoto'][$id]['manuf'] = $_GET['manuf'];
			 @$_SESSION['cart_from_avtoto'][$id]['price'] = $_GET['price'];
			 @$_SESSION['cart_from_avtoto'][$id]['price_id'] = $_GET['price_id'];
			 @$_SESSION['cart_from_avtoto'][$id]['Price'] = $_GET['Price'];
			  @$_SESSION['cart_from_avtoto'][$id]['Storage'] = $_GET['Storage'];
			   @$_SESSION['cart_from_avtoto'][$id]['Delivery'] = $_GET['Delivery'];
			    @$_SESSION['cart_from_avtoto'][$id]['BaseCount'] = $_GET['BaseCount'];
				 @$_SESSION['cart_from_avtoto'][$id]['SearchID'] = $_GET['SearchID'];
			 
			 @$_SESSION['cart_from_avtoto'][$id]['count'] += intval(@$_GET['skolko']) > 0 ? intval(@$_GET['skolko']) : 1;
			
			$this->redirect($_SERVER["HTTP_REFERER"]);
		}


		foreach($_POST as $key=>$val) {
			if(strstr($key,'edit_')!='' && floatval($val)>0) {
				$id = Sanitize::paranoid(str_replace('edit_','',$key));
				@$_SESSION['cart'][$id]['count'] = floatval($val);
			}
			
			if(strstr($key,'editFR_')!='' && floatval($val)>0) {
				$id = Sanitize::paranoid(str_replace('editFR_','',$key));
				@$_SESSION['cart_from_request'][$id]['count'] = floatval($val);
			}
			
			if(strstr($key,'editFSN_')!='' && floatval($val)>0) {
				$id = Sanitize::paranoid(str_replace('editFSN_','',$key));
				@$_SESSION['cart_from_search_by_num'][$id]['count'] = floatval($val);
			}
			
			if(strstr($key,'editFA_')!='' && floatval($val)>0) {
				$id = Sanitize::paranoid(str_replace('editFA_','',$key));
				@$_SESSION['cart_from_avtoto'][$id]['count'] = floatval($val);
			}
		}

        if(isset($_POST['checkout'])) {
            $this->redirect('/check_out_signin.html');
        }

		if(isset($_GET['del'])) {
			unset($_SESSION['cart'][$_GET['del']]);
		}

        if(isset($_GET['delFR'])) {
			unset($_SESSION['cart_from_request'][$_GET['delFR']]);
		}
		
		if(isset($_GET['delFSN'])) {
			unset($_SESSION['cart_from_search_by_num'][$_GET['delFSN']]);
		}
		
		if(isset($_GET['delFA'])) {
			unset($_SESSION['cart_from_avtoto'][$_GET['delFA']]);
		}

		
		$cart = @$_SESSION['cart'] ? $_SESSION['cart'] : array();
		$products = array();		
		foreach($cart as $id=>$prod) {
				$product = $this->Product->find('Product.id='.Sanitize::paranoid($id));
				$product['count'] = $prod['count'];
				$products[] = $product;
		}
		$this->set('products',$products);

        $cart_from_request = @$_SESSION['cart_from_request'] ? $_SESSION['cart_from_request'] : array();
		$products_from_request = array();		
		foreach($cart_from_request as $id=>$answer_data) {
				$answer = $this->RequestAnswer->find('RequestAnswer.id='.Sanitize::paranoid($id));
				$answer['mark_and_model'] = $answer_data['mark_and_model'];
                $answer['year'] = $answer_data['year'];
                $answer['name'] = $answer_data['name'];
                $answer['number'] = $answer_data['number'];
                $answer['count'] = $answer_data['count'];
				$products_from_request[] = $answer;
		}
		$this->set('products_from_request',$products_from_request);
		
		$cart_from_search_by_num = @$_SESSION['cart_from_search_by_num'] ? $_SESSION['cart_from_search_by_num'] : array();
		$products_from_search_by_num = array();		
		foreach($cart_from_search_by_num as $id=>$prod) {
				$product = mysql_fetch_array(mysql_query("select * from nproducts where id='".$id."'"));
				$product['count'] = $prod['count'];
				$products_from_search_by_num[] = $product;
		}
		$this->set('products_from_search_by_num',$products_from_search_by_num);

		$products_from_avtoto = @$_SESSION['cart_from_avtoto'] ? $_SESSION['cart_from_avtoto'] : array();
		$this->set('products_from_avtoto',$products_from_avtoto);
	}
	
	function paid($id=null) {
		if(empty($id))
			$this->redirect("/");
			
		$order = $this->Order->read(null, $id);	
		if($order['Order']['customer_id'] != $this->othAuth->user("id"))
			$this->redirect("/");
			
		$order['Order']['paid'] = 1;
		$order['Order']['paid_time'] = date('Y-m-d H:i:s');
		
		
		// отправка письма админу
		$this->Sendmail->subject = 'Произведена оплата заказа';
		$this->Sendmail->message = 
'Заказ  № '.($order['Order']['order_number'] ? $order['Order']['pre_number'].$order['Order']['order_number'] : $order['Order']['id']).' от  '.date('d.m.Y H:i:s', strtotime($order['Order']['created'])).' оплачен клиентом.

Оплата произведена '.date('d.m.Y H:i:s').'
';
		$this->Sendmail->send();
		//--------------------------------------
		
		$this->Order->save($order);	
		@$flash .= "Уведомление об оплате отправлено адмиистраторам. ";
		$this->redirect("/orders.html");
	}
	
	function orders() {
	$this->set('title', 'Мои заказы');

		if(!$this->logged) {
			$this->redirect("/login.html");
		}  	
		
               $where = 'Order.customer_id='. $this->othAuth->user("id").' AND Order.archive IS NULL';
			   if($_GET['status']==1||$_GET['status']==2||$_GET['status']==3||$_GET['status']==4||$_GET['status']==5||$_GET['status']==6){
				$where = 'Order.customer_id='.$this->othAuth->user("id").' AND Order.status_id ='.$_GET['status'];
			   }
			   if(isset($_GET['archivesave'])){
				$this->Customer->query('UPDATE `orders` SET archive=1 WHERE id='.$_GET['archivesave']);
			   }
			   if(isset($_GET['archive'])){
				$where = 'Order.customer_id='.$this->othAuth->user("id").' AND Order.archive=1';
			   }
			   if(isset($_GET['decline'])) $where = 'Order.customer_id='.$this->othAuth->user("id").' AND Order.decline ='.$_GET['decline'];
		$data = $this->Customer->Order->findAll($where,null,'Order.created DESC',$this->show, $this->page, 2);
		$this->set('data', $data);
		$data = $this->Customer->Order->Status->findAll();
		$this->set('statuses',$data);

                 $paging['style'] = 'html'; 
			$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
			$paging['count'] = $this->Customer->Order->findCount($where ,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			 $paging['show'] = array('10','20','30');
			  $this->set('paging',$paging);
			$this->set('city_names', $this->City->findAll());
	}

     function my_requests() {
	$this->set('title', 'Мои запросы');

		if(!$this->logged) {
			$this->redirect("/login.html");
		}  
            
$this->Request->RequestPart->bindModel(array('hasMany' => array(
        'RequestAnswer')));

               $where = "Request.customer_name IS NULL AND Request.customer_id = '".$this->othAuth->user("id")."'";

		$data = $this->Request->findAll($where,null,'Request.created DESC',$this->show, $this->page, 2);
		$this->set('data', $data);

                      $paging['style'] = 'html'; 
			$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
			$paging['count'] = $this->Request->findCount($where,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			 $paging['show'] = array('10','20','30');
			  $this->set('paging',$paging);	
	}


     function customer_requests() {
	$this->set('title', 'Запросы покупателей');

		if(!$this->logged) {
			$this->redirect("/login.html");
		}  

           if($this->othAuth->group('id') !=4 && $this->othAuth->group('id') !=5 && $this->othAuth->group('id') !=6)  {
			$this->redirect("/");
		}  		

$this->Request->RequestPart->bindModel(array('hasMany' => array(
        'RequestAnswer' => array('conditions'=>array('RequestAnswer.supplier_id'=>$this->othAuth->user("id"))),
        'RequestAnotherAnswer' => array(
                      'className' => 'RequestAnswer',
                      'conditions'=>array('RequestAnotherAnswer.supplier_id'=>'<>'.$this->othAuth->user("id"))
       )
)));

 $row_customer = $this->Customer->read(null,$this->othAuth->user("id"));          

                     
                if($this->othAuth->group('id') ==4)
                        $where = " Request.customer_name IS NULL  AND Request.customer_id <> '".$this->othAuth->user("id")."' AND Request.mark_id IN (".$row_customer['Customer']['marks'].") ";
                 else
               {

                    $where = " Request.customer_name IS NULL  ";
                    if(!empty($_GET[number]))
                               $where .= " AND Request.id='".$_GET[number]."' ";
                      if(!empty($_GET[date]))
                               $where .= " AND Request.created>='".date('Y-m-d 00:00:00', strtotime($_GET[date]))."'  AND  Request.created<='".date('Y-m-d 23:59:59', strtotime($_GET[date]))."'  ";
                      if(!empty($_GET[lastname]))
                               $where .= " AND Customer.lname LIKE '%".$_GET[lastname]."%' ";
                     if(!empty($_GET[phone]))
                               $where .= " AND (Customer.mphone LIKE '%".$_GET[phone]."'  OR Customer.hphone LIKE '%".$_GET[phone]."') ";
                 }
  
		$data = $this->Request->findAll($where,null,'Request.created DESC',$this->show, $this->page, 2);
		$this->set('data', $data);

                $paging['style'] = 'html'; 
			$paging['link'] = '/'.@$this->params['url']['url'].'?number='.$_GET[number].'&date='.$_GET[date].'&lastname='.$_GET[lastname].'&phone='.$_GET[phone].'&page=';		
			$paging['count'] = $this->Request->findCount( $where ,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			 $paging['show'] = array('10','20','30');
			  $this->set('paging',$paging);	
	}

       function answer_request() {
            $this->set('title', 'Ответ на запрос');

		if(!$this->logged) {
			$this->redirect("/login.html");
		}  

$auth_id = $this->othAuth->user("id");
$group_id = $this->othAuth->group('id');

            if(empty($auth_id) || ($group_id !=4 && $group_id !=5 && $group_id !=6))  {
			$this->redirect("/");
		}  	



		$this->Request->RequestPart->bindModel(array('hasMany' => array(
        'RequestAnswer' => array('conditions'=>array('RequestAnswer.supplier_id'=>$auth_id)))));

$row_customer = $this->Customer->read(null,$auth_id);              
 
          $where = "Request.id=".Sanitize::paranoid($_GET['id']);
           if($this->othAuth->group('id') ==4)
                 $where .=  " AND Request.customer_name IS NULL AND Request.customer_id <> '".$auth_id."' AND Request.mark_id IN (".$row_customer['Customer']['marks'].") ";
            $data_request = $this->Request->findAll($where,null,null,null,null,2);
            if(!$data_request) {
			$this->redirect("/customer_requests.html");
		}
		$this->set('data', $data_request);
	

                            $answer_exists = false;
                           foreach($data_request[0]['RequestPart'] as $part) { 
                                     if(count($part['RequestAnswer'])>0) 
	                                  $answer_exists = true;
                            }
                           if($answer_exists) {
                                    $this->redirect("/customer_requests.html");

                           } else {

                            if(isset($this->params['data']['RequestAnswer']))
                            {
                                   //-----------проверка ошибок--------
                                   $price_exists = false;
                                   $errors = array();
                                   foreach($this->params['data']['RequestAnswer'] as $part_id=>$answer) {
                                           foreach($answer as $number_of_var=>$variant) {
                                                  if(!empty($variant['start_price'])) {
                                                            $price_exists = true;
                                                           if(!preg_match('/[0-9]+/i',$variant['start_price'])) {
					$errors[$part_id][$number_of_var]['start_price'] = '<br><small style="color:red">целое число</small>';					
                                                            } 
                                                            if(empty($variant['delivery_term'])) {
					$errors[$part_id][$number_of_var]['delivery_term'] = '<br><small style="color:red">обязательное поле</small>';					
                                                            } 
                                                           if(empty($variant['state'])) {
					$errors[$part_id][$number_of_var]['state'] = '<br><small style="color:red">обязательное поле</small>';					
                                                            } 
                                                  }
                                           }
                                    }
                                    if(!$price_exists)
                                               $errors['no_answers'] = '<p><span style="color:red">Нет ни одного ответа для отправки!</span>';
                                    //---------проверка ошибок-------------

	
                                    if(count($errors)==0) {

                                                foreach($this->params['data']['RequestAnswer'] as $part_id=>$answer) {
                                                         foreach($answer as $number_of_var=>$variant) {
                                                               if(!empty($variant['start_price'])) {
                                                                           $variant["supplier_id"] = $auth_id;
                                                                           $variant["request_part_id"] = $part_id;
																		   if($group_id ==4)
																				$variant["itog_price"] = ceil($variant["start_price"]*1.25);
																			else
																				$variant["itog_price"] = $variant["start_price"];

                                                                           $request_part["number"] = $request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"];
					      $data["RequestAnswer"] = $variant;
					      $this->RequestAnswer->save($data);
                                                                            unset($this->RequestAnswer->id);
                                                                }
                                                           }
                                                 }

                                               $row_customer = $this->Customer->read(null,$data_request[0]['Request']['customer_id']);  

                                               // отправка письма клиенту
			    $this->Sendmail->subject = 'Ответ на Ваш запрос запчастей';
			    $this->Sendmail->message = 
'Уважаемый(ая) '.$row_customer['Customer']['fname'].',
								
Мы благодарим Вас за интерес, проявленный к on-line магазину автозапчастей SM-JM.RU.

Вам пришел ответ на Ваш запрос запчастей. Просмотреть его можно в личном кабинете в разделе "Ваши Запросы Запчастей" на сайте нашего магазина автозапчастей SM-JM.RU

+------------------------------------------------------------------
|   ПО ВАШЕМУ ЗАПРОСУ ЗАПЧАСТЕЙ
|   '.date('d.m.Y H:i', strtotime($data_request[0]['Request']['year'])).'
+------------------------------------------------------------------	
|  Вы запрашивали запчасти на автомобиль: 
|  '.$data_request[0]['Mark']['name'].'  '.$data_request[0]['Model']['name'].'  '.$data_request[0]['Request']['year'].'    
+------------------------------------------------------------------	
|  запчасти:';
foreach($data_request[0]['RequestPart'] as $request_part) {
if(!empty($request_part["name"]) && $request_part["name"]!='Наименование'  && !empty($request_part["part_state_id"]))	{
$row_part_state = $this->PartState->read("PartState.name",$request_part["part_state_id"]);
	$this->Sendmail->message.='
|  '.$request_part["name"].',  номер: '.($request_part["number"] == 'Ориг. номер' ? '-': $request_part["number"]).', состояние:  '.$row_part_state['PartState']['name'];
}
}			
$this->Sendmail->message .='
+------------------------------------------------------------------


Если Вам требуется дополнительная информация, будем рады помочь Вам.

С уважением,
Клиентская служба
+7 (904) 503 07 07
http://detalline.ru/
support@detalline.ru
';

				$this->Sendmail->to_email = $row_customer['Customer']['email'];
				$this->Sendmail->to_name = $row_customer['Customer']['lname'].' '.$row_customer['Customer']['fname'];
				$this->Sendmail->send();
				//-------------------------------------- 


                                                $this->Session->setFlash('<p><B>Спасибо, Ваш ответ отправлен покупателю. </B>','flash');	
		                    $this->redirect("/customer_requests.html");		
                                    } else {
                                                $this->Session->setFlash('<p><font color=red>Обнаружены ошибки</font>','flash');	
                                                $this->set('errors',$errors);
                                     }
                            }
                           }
		
	}
	
	function logout()
    {
        $this->othAuth->logout();
		$this->redirect("/");
    }
		
	function check_out_fp() {
$this->set('title', 'Забыли пароль?');

	}
	
	function check_out_signin() 
	{
$this->set('title', 'Авторизация');


		if(isset($this->params['data']))
		{
					if($this->data['Customer']['city']=='1r6d1')
						$this->data['Customer']['city']=$_POST['city'];
					else{
						$city['City']['region_id']=$this->data['Customer']['region_id'];
						$city['City']['name']=$this->data['Customer']['city'];
						$city['City']['id']=$this->City->findCount()+1;
						$this->City->save($city);
						$this->data['Customer']['city']=$city['City']['id'];
					}
			$auth_num = $this->othAuth->login($this->params['data']['Customer']);			
			$this->set('auth_msg', "<font color=red>".$this->othAuth->getMsg($auth_num)."</font>");
			$this->logged = $this->othAuth->check();
		}		
		if($this->logged) {
			$this->redirect("/check_out.html");
		}
	}
	
	function price(){
	
		/*$need = mysql_fetch_array(mysql_query('SELECT date FROM `date_statuses` ORDER BY date DESC'));
		$this_month = explode('-',$need[0]);
		$this->set('this_month',$this_month[1]);
		if(isset($_POST['update_stat'])){
			$statuses = mysql_query("select * from `orders_statuses` where status_id = 5 AND stage_date >  curdate() - interval 30 day");
			while($status = mysql_fetch_array($statuses)){
				$products = mysql_query("select id,order_id,price_id from `order_products` where id=".$status[2]);
				while($product = mysql_fetch_array($products)){
					$price=$product[2];
					$stat[$price]['count'] = ++$stat[$price]['count'];
				}
				$orders = mysql_query("select created from `orders` where id=".$status[0]);
				while($order = mysql_fetch_array($orders)){
					$date1 = new DateTime($order[0]);
					$date2 = new DateTime($status[3]);
					$interval =	date_diff($date1,$date2);
					$stat[$price]['days'] += $interval->days;
					if($stat[$price]['long1']=='') $stat[$price]['long1']=0;
					if($stat[$price]['long1']<$interval->days){
						$stat[$price]['long3']=$stat[$price]['long2'];
						$stat[$price]['long2']=$stat[$price]['long1'];
						$stat[$price]['long1']=$interval->days;
					}else
						if($stat[$price]['long2']<$interval->days){
							$stat[$price]['long3']=$stat[$price]['long2'];
							$stat[$price]['long2']=$interval->days;
						}else
							if($stat[$price]['long3']<$interval->days) $stat[$price]['long3']=$interval->days;
				}
			}
			$prices = mysql_query("select id from `prices`");
			while($price = mysql_fetch_array($prices)){
				if(!empty($stat[$price[0]])){
					mysql_query("delete from `date_statuses` where price_id =".$price[0]);
					$days0=$stat[$price[0]]['days']/$stat[$price[0]]['count'];
					mysql_query("insert into `date_statuses` (`price_id`, `days`, `status`, `date`) values(".$price[0].",".$days0.",0,NOW())");
					if($stat[$price[0]]['long2']==0) $long = 1; else
						if($stat[$price[0]]['long3']==0) $long = 2; else $long = 3;
					$days = $stat[$price[0]]['long1']+$stat[$price[0]]['long2']+$stat[$price[0]]['long3'];
					mysql_query("insert into `date_statuses` (`price_id`, `days`, `status`, `date`) values(".$price[0].",".($days/$long).",1,NOW())");
				}
			}
		}	*/
			
			$this->set('title','Прайсы');
		
			$where = 'Price.active=1 ';
			$where.= 'AND Price.id='.$this->othAuth->user('price_id');

			$data = $this->Price->findAll($where,null,"Price.id ASC");
			$this->set('data', $data);
	
	}
	
	function edit_price(){
	
		if(isset($_POST['save'])){
			$price['Price']['id'] = $this->othAuth->user('price_id');
			$price['Price']['name'] = $_POST['name'];
			$price['Price']['param1_id'] = $_POST['param1_id'];
			$price['Price']['param2_id'] = $_POST['param2_id'];
			$price['Price']['param3_id'] = $_POST['param3_id'];
			$price['Price']['param4_id'] = $_POST['param4_id'];
			$price['Price']['param5_id'] = $_POST['param5_id'];
			if($_POST['manufacturer']=='0') $price['Price']['manufacturer_csv'] = $_POST['manufacturer_csv'];
			else $price['Price']['manufacturer_csv'] = $_POST['manufacturer'];
			
			$price['Price']['name_csv'] = $_POST['name_csv'];
			$price['Price']['manuf_number_csv'] = $_POST['manuf_number_csv'];
			$price['Price']['price_csv'] = $_POST['price_csv'];
			$price['Price']['quantity_csv'] = $_POST['quantity_csv'];
			$price['Price']['days_shipping_min'] = $_POST['days_shipping_min'];
			$price['Price']['days_shipping_garant'] = $_POST['days_shipping_garant'];
			$price['Price']['mark_and_model'] = $_POST['mark_and_model'];
			$price['Price']['articul'] = $_POST['articul'];
			$price['Price']['description'] = $_POST['description'];
			$price['Price']['note'] = $_POST['note'];
			$price['Price']['year1'] = $_POST['year1'];
			$price['Price']['year2'] = $_POST['year2'];
			$price['Price']['number'] = $_POST['number'];
			//$price['Price']['wholesale_price'] = $_POST['wholesale_price'];
			$price['Price']['row'] = $_POST['row'];
			$price['Price']['wholesale_price_pers'] = $_POST['wholesale_price_pers'];
			$price['Price']['mark'] = $_POST['mark'];
			$price['Price']['number_cross'] = $_POST['number_cross'];
			$this->Price->save($price);
		}
		
		$where='Price.id='.$this->othAuth->user('price_id');
		$data = $this->Price->findAll($where,null,"Price.id ASC");
		$this->set('price_params', $this->PriceParamName->findAll());
		$this->set('manufacturers', $this->Manufacturer->findAll());
		$this->set('data', $data);
					
	}
}
?>
