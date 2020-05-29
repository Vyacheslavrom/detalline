<?php
uses('sanitize');

class MovementsController extends AppController
{
	var $name = 'Movements';
	var $uses = array('Movement','Customer','MovementDirection','MovementObject','Credential');
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'created';
	var $direction = 'DESC';
	var $page;
	var $order;
	
	
	function __construct() {
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			$this->Movement->id = $id;
			$this->data['Movement']['deleted'] = 1;
			$this->Movement->save($this->data);	
			@$flash .= "Запись $id удалена. ";
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('movements');
	}
	
	function index($id=null) {
	 
		if($_POST['new_date']){
			if(date('Y-m-d')>=date('Y-m-d',strtotime($_POST['created_from']))){
				$movement=$this->Movement->read(null,$_POST['id']);
				$movement['Movement']['created']=date('Y-m-d ', strtotime($_POST['created_from'])).date('H:i:s');
				$this->Movement->save($movement);
			}
		}
	 
	 
		$this->set('title','Баланс');
		if (!empty($this->data)) {	
			 	
				$errors = false;
			
				if(!$this->Movement->validates($this->data)) {
					$errors = true;
				}
			
				if(!$errors) {
				
					//название начинается с заглавной
					if($this->Movement->save($this->data)) {
						$this->Session->setFlash('Запись сохранена.','flash');
						$this->redirect('movements');
					}
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		/*if ($id) {
            $this->params['data'] = $this->Movement->read(null, Sanitize::paranoid($id));
        }*/
	 		
		if (isset($_GET["object"])) {		
			$search_fields = array();
			$search_fields["created_from"] = @$_GET["created_from"];
			$search_fields["created_to"] = @$_GET["created_to"];
			$search_fields["object"] = @$_GET["object"];
			$search_fields["customer"] = @$_GET["customer"];
			$search_fields["note"] = @$_GET["note"];
			
			$this->Session->del('search_movements');	
			$this->Session->write('search_movements',$search_fields);		
		}
		
		if($this->Session->valid('search_movements'))
			$sess_search = $this->Session->read('search_movements');
			
		$where = "1=1";
		if(!empty($sess_search) && is_array($sess_search))
		{
			if(!empty($sess_search["created_from"])) 
				$where .= " AND Movement.created >= '". date('Y-m-d 00:00:00', strtotime($sess_search['created_from'])) . "' "; 	
				
			if(!empty($sess_search["created_to"])) 
				$where .= " AND Movement.created <= '". date('Y-m-d 23:59:59', strtotime($sess_search['created_to'])) . "' "; 	
			
			if(!empty($sess_search["object"])) 
				$where .= " AND Movement.movement_object_id = '".Sanitize::paranoid($sess_search["object"])."' "; 	
			
			if(!empty($sess_search["customer"])) 
				$where .= " AND Movement.customer_id = '".Sanitize::paranoid($sess_search["customer"])."' ";
				
			if(!empty($sess_search["note"])) 
				$where .= " AND Movement.note LIKE '%".$_GET["note"]."%' ";
        }
				
		//------------сводные данные-----------------
		$summery = mysql_fetch_array(mysql_query("select sum(CASE WHEN Movement.movement_object_id = 1 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id = 1 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as money,
		sum(CASE WHEN Movement.movement_object_id = 2 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id = 2 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as parts,
		sum(CASE WHEN Movement.movement_object_id > 2 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id > 2 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as other_objects
		from movements as Movement where ".$where." AND deleted IS NULL"));
		$this->set('summery', $summery);
		//------------сводные данные-----------------
		
		$data = $this->Movement->findAll($where, null, $this->order, $this->show, $this->page, 2);
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/movements/?page=';		
		$paging['count'] = $this->Movement->findCount($where,2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);

		$this->set('directions', $this->Movement->MovementDirection->findAll());
		$this->set('objects', $this->Movement->MovementObject->findAll());
		$this->Movement->Customer->unbindModel(array('belongsTo' => array('Group','Region'),
					'hasMany' => array('Order'),
					'hasAndBelongsToMany' => array('Product')));
		$this->set('customers', $this->Movement->Customer->findAll(null,null,'lname ASC'));
		
		$this->set('sel_created_from', @$sess_search['created_from']);
		$this->set('sel_created_to', @$sess_search['created_to']);
		$this->set('sel_object', @$sess_search['object']);
		$this->set('sel_customer', @$sess_search['customer']);
		$this->set('sel_note', @$sess_search['note']);
		
		$sel_customer_name = $this->Movement->Customer->read(null, @$sess_search['customer']);
		$this->set('sel_customer_name', $sel_customer_name ? $sel_customer_name['Customer']['lname'].' '.substr($sel_customer_name['Customer']['fname'],0,1).'. '.substr($sel_customer_name['Customer']['otch'],0,1).'. ('.$sel_customer_name['Customer']['mphone'].')':'');
	}

	function credentials() {
		
		if(isset($_POST['save'])){
		
			$credentials['Credential']['id'] = 1;
			$credentials['Credential']['name'] = $_POST['name'];
			$credentials['Credential']['jname'] = $_POST['jname'];
			$credentials['Credential']['tip'] = $_POST['tip'];
			$credentials['Credential']['jadress'] = $_POST['jadress'];
			$credentials['Credential']['inn'] = $_POST['inn'];
			$credentials['Credential']['kpp'] = $_POST['kpp'];
			$credentials['Credential']['ogrn'] = $_POST['ogrn'];
			$credentials['Credential']['name_bank'] = $_POST['name_bank'];
			$credentials['Credential']['adress_bank'] = $_POST['adress_bank'];
			$credentials['Credential']['bik'] = $_POST['bik'];
			$credentials['Credential']['korr_schet'] = $_POST['korr_schet'];
			$credentials['Credential']['rasch_schet'] = $_POST['rasch_schet'];
			$this->Credential->save($credentials);
		
		}
		
		$this->set("credentials", $this->Credential->findAll());
		
	}
	
}
?>