<?php
uses('sanitize');
class MessagesController extends AppController
{
	var $name = 'Messages';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'id';
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
				$this->Message->del($id);	
				@$flash .= "Сообщение $id удалено. ";
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('messages');
	}
	
	function active($id=null, $activity=1) {
	 	$ids = array();
	 	
	 	if($id && $id!=='all') { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
		 	$data = $this->Message->read(null, $id); 		
			$data['Message']['active'] = $activity;	      	 
			$this->Message->save($data);		
		}
	    $this->redirect('messages');		
	}
	
	function index($id=null) {
	 
		$this->set('title','Сообщения');
		
		$data = $this->Message->findAll("fio <> 'Оператор'", null, $this->order, $this->show, $this->page, 2);
		
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/messages/?page=';				
		$paging['count'] = $this->Message->findCount(null,2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);		
	}
}