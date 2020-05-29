<?php
uses('sanitize');

class AdvertsController extends AppController
{
	var $name = 'Adverts';
	var $helpers = array("Html", "Pagination");
	var $components = array('File');
	var $show;
	var $sortBy;
	var $direction;
	var $page;
	var $order;


	function __construct() {
		$this->show = empty($_GET['show'])? '10': Sanitize::paranoid($_GET['show']);
		$this->sortBy = empty($_GET['sort'])? 'id': Sanitize::paranoid($_GET['sort']);
		$this->direction = empty($_GET['direction'])? 'ASC': Sanitize::paranoid($_GET['direction']);
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}

	function delete_picture($id=null) {
	 	$data = $this->Advert->read(null, $id); 	 	
		$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'rblocks'.DS.'photos'.DS.$data['Advert']['image']);
		$this->redirect('adverts/edit/'.$id);		
	}
	
	function edit($id = null) {	
		if (!empty($this->data)) {	
			$errors = false;
			
			if(!$this->Advert->validates($this->data)) {
				$errors = true;
			}			
			
			if(!$errors && is_uploaded_file($this->data['Advert']['pic_file']['tmp_name'])) {
	
				$path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'rblocks'.DS.'photos'.DS;							
				$filename = mt_rand();			
				
				$this->File->allowed_extentions = array('jpg','png','gif');
		        if ($this->File->upload($this->data['Advert']['pic_file'],$filename,$path)) {
		             
					// удаляем старый файл
					$this->File->delete($path.@$this->data['Advert']['image']);   
					// записываем имя в переменную для сохранения в базу
					$this->data['Advert']['image'] = $this->File->filename;	
					
		         } else {
					$this->set("error", $this->File->error);
		            $errors = true;
				}
				
			}
			
			if(!$errors) {				
				if ($this->Advert->save($this->data)) {
					$this->Session->setFlash('Запись сохранена','flash');
					$this->redirect("adverts");
				}
			}
			else {
				$this->Session->setFlash('<font color=blue>При сохранении обнаружены ошибки</font>','flash');
			}
		} else {
			$this->params['data'] = $this->Advert->read(null, $id); 
		}
		
		if(!$this->params['data'])
			$this->params['data']['Advert']['active'] = true;
        
		$this->set('types', $this->Advert->AdvertType->findAll());
		$this->set('title', 'Редактирование рекламного блока');
	}
	
	
	
	function active($id=null, $activity=1) {
	 	$ids = array();
	 	
	 	if($id && $id!=='all') { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
		 	$data = $this->Advert->read(null, $id); 		
			$data['Advert']['active'] = $activity;	      	 
			$this->Advert->save($data);		
		}
	    $this->redirect('adverts');		
	}
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {
			$data = $this->Advert->read(null, $id); 	 	
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'rblocks'.DS.'photos'.DS.$data['Advert']['image']);
			$this->Advert->del($id);	
		}
	    $this->redirect('adverts');
	}
		    
	function index() {
	 
		$this->set('title','Реклама');
		
	 	$where = array();
	 	
		if(!empty($_GET['status']) || @$_GET['status']==='0')
	 	{$where[] = "active = ".Sanitize::paranoid($_GET['status']);}	
	 	
		$data = $this->Advert->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 2);

		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/'.@$this->params['url']['url'].'?status='.@$_GET['status'].'&page=';		
		$paging['count'] = $this->Advert->findCount(implode(" AND ",$where),2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);

		$this->set('sel_txt', @$_GET['txt']);
		$this->set('sel_status', @$_GET['status']);
	}
}
?>