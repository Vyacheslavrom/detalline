<?php
uses('sanitize');

class ManufacturersController extends AppController
{
	var $name = 'Manufacturers';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'name';
	var $direction = 'ASC';
	var $page;
	var $order;
	var $uses = array('Manufacturer','Product');

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
			if($this->Product->findByManufacturerId(Sanitize::paranoid($id))) {
				@$flash .= "Удаление производителя $id невозможно, так как используется товарами. ";
			} else {
				$this->Manufacturer->del($id);	
				@$flash .= "Производитеь $id удален. ";
			}
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('manufacturers');
	}
	
	function index($id=null) {
	 
		$this->set('title','Производители');
		
		if (!empty($this->data)) {	
			 	if($this->Manufacturer->save($this->data)) {
					$this->Session->setFlash('Запись сохранена.','flash');
					$this->redirect('manufacturers');
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{$this->params['data'] = $this->Manufacturer->read(null, Sanitize::paranoid($id));}
	 	
		$data = $this->Manufacturer->findAll("", null, $this->order, $this->show, $this->page, 2);
		
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/manufacturers/?page=';		
		$paging['count'] = $this->Manufacturer->findCount("",2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);		
	}
}