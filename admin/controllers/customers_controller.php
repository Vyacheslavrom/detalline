<?php
uses('sanitize');

class CustomersController extends AppController
{
	var $name = 'Customers';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'created';
	var $direction = 'ASC';
	var $page;
	var $order;
	var $uses = array('Responsible','CountSearch','VisibleSearch','City','RegistrDiscount');

	function __construct() {
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
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
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			
				$this->Customer->del($id);	
				@$flash .= "Покупатель $id удален. ";
			
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('customers');
	}

    function givePerms($group_id=null, $id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			
				$row = $this->Customer->read(null, $id);
                          	$row['Customer']['group_id'] = $group_id;
                                $this->Customer->save($row);

				@$flash .= "Права пользователя $id изменены. ";
			
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('customers');
	}
	
	
	function index() {
	
	$buy_count=0;
	$find_orders = mysql_query('SELECT id FROM orders WHERE created BETWEEN LAST_DAY(NOW()) + INTERVAL 1 DAY - INTERVAL 1 MONTH AND LAST_DAY(NOW())');
	while($find_order = mysql_fetch_array($find_orders)){
		$true_count = mysql_fetch_row(mysql_query('SELECT id FROM order_products WHERE price_id = 29 AND order_id = '.$find_order[0]));
		if($true_count[0]>0)
			$buy_count++;
	}
	
	$this->set('buy_count',$buy_count);
	//$this->redirect($_POST['hand_s']);
	
	if($_POST['hand_s']!='')
		if($_POST['hand_s']==3){
			$visible_search['VisibleSearch']['id']=1;
			$visible_search['VisibleSearch']['status']=0;
		}else{
			$visible_search['VisibleSearch']['id']=1;
			$visible_search['VisibleSearch']['status']=1;
			$this->VisibleSearch->save($visible_search);
			$visible_search['VisibleSearch']['id']=2;
			$visible_search['VisibleSearch']['status']=$_POST['hand_s'];
		}
		$this->VisibleSearch->save($visible_search);
	
	
		$this->set('visibles',$this->VisibleSearch->findAll());
	
	if(!empty($_GET['who'])){
		$customes = $this->Customer->findAll('WHERE member=1');
		$i=0;
		foreach($customes as $custome){
			$i++;
			$who['Responsible']['id']=$i;
			$who['Responsible']['customer_id'] = $custome['Customer']['id'];
			$who['Responsible']['who'] = $custome['Customer']['lname'].' - '.$custome['Customer']['fname'];
			$this->Responsible->save($who);
		}
	}
	
	if(isset($_POST['save_discount'])){
		$discount['RegistrDiscount']['id'] = 1;
		$discount['RegistrDiscount']['discount'] = $_POST['discount'];
		$this->RegistrDiscount->save($discount);
	}
		
	
      if(isset($_POST['apply'])) {    
        
        if(abs(intval($_POST['skidka']))>100) {
          $this->Session->setFlash('<font color=blue>Скидка задана неверно</font>','flash');
        } else {
        
          $data['Customer']['id'] = $_POST['id'];
          $data['Customer']['skidka'] = intval($_POST['skidka']);
          $this->Customer->save($data);
        }
      
      }
	  
	  if(isset($_GET['wholesale'])) {    
        
        $data['Customer']['id'] = $_GET['id'];
          $data['Customer']['wholesale'] = $_GET['wholesale']=='true' ? 1:0;
          $this->Customer->save($data);
		  $this->redirect('customers/?page='.$_GET['page']);
      }
	  
	  if(isset($_GET['member'])) {    
        
		
          if($_GET['member']=='true'){
			  $member = $this->Customer->read(null,$_GET['id']);
			  $who['Responsible']['customer_id'] = $member['Customer']['id'];
			  $who['Responsible']['who'] = $member['Customer']['lname'].' - '.$member['Customer']['fname'];
			  $this->Responsible->save($who);}
		  else{mysql_query("DELETE FROM `responsibles` WHERE customer_id=".$_GET['id']);}
		  $this->redirect('customers/?page='.$_GET['page']);
      }
      
	  $where = array();
	  if(isset($_GET['cust'])) {
				$search_fields = array();
				$search_fields["cust"] = @$_GET["cust"];
				
				$this->Session->del('search_customers');	
				$this->Session->write('search_customers',$search_fields);	
			}
			
			if($this->Session->valid('search_customers'))
				$sess_search = $this->Session->read('search_customers');
			
			if(!empty($sess_search) && is_array($sess_search))
			{			
				if(!empty($sess_search["cust"])) 
					$where[] = " Customer.id = '".Sanitize::paranoid($sess_search["cust"])."' "; 				
			}
			
		$this->set('title','Покупатели');		
		$data = $this->Customer->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 2);
		$customer_id='0';
		foreach($data as $row){
			$customer_id.=','.$row['Customer']['id'];
		}
		$count = $this->CountSearch->findAll('WHERE customer_id IN ('.$customer_id.')');
		$all_count = mysql_fetch_row(mysql_query("SELECT SUM(count) FROM `count_searches`"));
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/customers/?page=';				
		$paging['count'] = $this->Customer->findCount(implode(" AND ",$where),2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);		
		$this->set('count_searches', $count);
		$this->set('all_count', $all_count[0]);
		
		
		$this->set('customer_id', @$sess_search['cust']);
		$sel_customer_name = $this->Customer->read(null, @$sess_search['cust']);
		$this->set('customer', $sel_customer_name ? $sel_customer_name['Customer']['lname'].' '.substr($sel_customer_name['Customer']['fname'],0,1).'. '.substr($sel_customer_name['Customer']['otch'],0,1).'. ('.$sel_customer_name['Customer']['mphone'].')':'');
		$discount = $this->RegistrDiscount->read(null, 1);
		$this->set('discount',$discount['RegistrDiscount']['discount']);
		$this->set('responsibles', $this->Responsible->findAll());

	}
	
	function select_json() {
		$this->layout = "blank";
		$keyword = iconv('utf-8','windows-1251',$_GET['term']);
		$this->Customer->unbindModel(array('belongsTo' => array('Group','Region'),
					'hasMany' => array('Order'),
					'hasAndBelongsToMany' => array('Product')));
		$data = $this->Customer->findAll("Customer.lname LIKE '".$keyword."%'");
		$this->set('data', $data);		

		// заголовки необходимы для браузеров
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/json; charset=windows-1251;');
	}
	
	function edit() {
	    $this->set('title', 'Персональные данные');
		
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
			
			if(!$errors) {
			
					if($_POST['city_h']!='1r6d1'){
						$city['City']['region_id']=$this->data['Customer']['region_id'];
						$city['City']['name']=$_POST['city_h'];
						$city['City']['id']=$this->City->findCount()+1;
						$this->City->save($city);
						$this->data['Customer']['city']=$city['City']['id'];
					}
				
				$this->data['Customer']['id'] = $this->othAuth->user("id");
				if($this->othAuth->group('id') == 4 || $this->othAuth->group('id') == 5) {
					$this->data['Customer']['marks'] = implode(',',$selected_marks);
				}
				if(!isset($_POST['jur_lico'])) {
					$this->data['Customer']['organization'] = $this->data['Customer']['jur_address'] = $this->data['Customer']['inn'] =
					$this->data['Customer']['kpp'] = $this->data['Customer']['ogrn'] = $this->data['Customer']['bank'] = $this->data['Customer']['bank_address'] =
					$this->data['Customer']['bik'] = $this->data['Customer']['corr_schet'] = $this->data['Customer']['raschet_shet'] = '';
				}
				
				$this->data['Customer']['id'] = $_GET['id'];
				if(!empty($_POST['password1'])){
					if($_POST['password1'] == $_POST['password2']){
						$this->data['Customer']['password'] = md5($_POST['password2']);
					}
				}
				$this->Customer->save($this->data);

				$this->Session->setFlash('Ваш профайл изменен','flash');
				
				//$this->render("message");
				//return;
				
			} else {
				$this->data['Customer']['email'] = $this->othAuth->user("email");
				$this->Session->setFlash('<p><font color=red>При сохранении обнаружены ошибки</font>','flash');				
				return;
			}
		}
		

		$this->params['data'] = $this->Customer->findById($_GET['id']);
		$this->set('city_name', $this->City->read(null,$this->params['data']['Customer']['city']));
		$this->set('cities', $this->City->findAll());
	}
	
	function properties(){
		
		if(isset($_POST['save'])){
			$customer['Customer']['id'] = $_GET['id'];
			$customer['Customer']['group_id'] = $_POST['group_id'];
			$customer['Customer']['price_id'] = $_POST['price_id'];
			$customer['Customer']['wholesale'] = $_POST['wholesale'];
			$this->Customer->save($customer);
			mysql_query("DELETE FROM `responsibles` WHERE customer_id=".$_GET['id']);
			if(isset($_POST['member'])){
				$customer = $this->Customer->read(null,$_GET['id']);
				$responsible['Responsible']['who'] = $customer['Customer']['lname'].' - '.$customer['Customer']['fname'];
				$responsible['Responsible']['customer_id'] = $_GET['id'];
				$this->Responsible->save($responsible);
			}
			
		}
		
		$where = "Customer.id = ".$_GET['id'];
		$this->set('properties', $this->Customer->findAll($where));
		$where = "Responsible.customer_id = ".$_GET['id'];
		$this->set('responsible', $this->Responsible->findAll($where));
		
	}
}