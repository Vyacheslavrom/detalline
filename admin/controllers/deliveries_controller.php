<?php
uses('sanitize');

class DeliveriesController extends AppController
{
	var $name = 'Deliveries';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'name';
	var $direction = 'ASC';
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
			if($this->Delivery->Order->findByDeliveryId(Sanitize::paranoid($id))) {
				@$flash .= "Удаление способа доставки $id невозможно, так как используется заказами. ";
			} else {
				$this->Delivery->del($id);	
				@$flash .= "Способ доставки $id удален. ";
			}
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('deliveries');
	}
	
	function index($id=null) {
	 
		$this->set('title','Способы доставки');
		
		if (!empty($this->data)) {	
			 	if($this->Delivery->save($this->data)) {
					$this->Session->setFlash('Запись сохранена.','flash');
					$this->redirect('deliveries');
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{$this->params['data'] = $this->Delivery->read(null, Sanitize::paranoid($id));}
	 	
		$data = $this->Delivery->findAll("", null, $this->order, $this->show, $this->page, 2);
		
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/deliveries/?page=';		
		$paging['count'] = $this->Delivery->findCount("",2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);
		
	}
}