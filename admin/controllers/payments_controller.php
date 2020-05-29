<?php
uses('sanitize');

class PaymentsController extends AppController
{
	var $name = 'Payments';
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
			if($this->Payment->Order->findByPaymentId(Sanitize::paranoid($id))) {
				@$flash .= "Удаление способа оплаты $id невозможно, так как используется заказами. ";
			} else {
				$this->Payment->del($id);	
				@$flash .= "Способ оплаты $id удален. ";
			}
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('payments');
	}
	
	function index($id=null) {
	 
		$this->set('title','Способы оплаты');
		
		if (!empty($this->data)) {	
			 	if($this->Payment->save($this->data)) {
					$this->Session->setFlash('Запись сохранена.','flash');
					$this->redirect('payments');
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{$this->params['data'] = $this->Payment->read(null, Sanitize::paranoid($id));}
	 	
		$data = $this->Payment->findAll("", null, $this->order, $this->show, $this->page, 2);
		
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/payments/?page=';		
		$paging['count'] = $this->Payment->findCount("",2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);
		
	}
}