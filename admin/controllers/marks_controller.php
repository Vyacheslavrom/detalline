<?php
uses('sanitize');

class MarksController extends AppController
{
	var $name = 'Marks';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'name';
	var $direction = 'ASC';
	var $page;
	var $order;
	var $uses = array('Mark','SmModel');

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
			if($this->SmModel->findByMarkId(Sanitize::paranoid($id))) {
				@$flash .= "Удаление марки $id невозможно, так как используется моделями. ";
			} else {
				$this->Mark->del($id);	
				@$flash .= "Марка $id удалена. ";
			}
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('marks');
	}
	
	function index($id=null) {
	 
		$this->set('title','Марки');
		
		if (!empty($this->data)) {	
			 	
				if(!$this->Mark->validates($this->data)) {
					$errors = true;
				}
			
				if(!$errors) {
					
					//название начинается с заглавной
					$this->data['Mark']['name'] = ucfirst($this->data['Mark']['name']);
					
					$analogs = array_diff(explode(',',$_POST['analogs']), array(''));
					if(!in_array($this->data['Mark']['name'], $analogs))
						$analogs[] = $this->data['Mark']['name'];
					
					if($this->Mark->save($this->data)) {
                                                 mysql_query("delete from mark_analogs where mark_id = '".$this->Mark->id."' ");
                                                 foreach($analogs as $analog)
                                                                 mysql_query("insert into mark_analogs(mark_id, name) values('".$this->Mark->id."', '".$analog."') ");

						$this->Session->setFlash('Запись сохранена.','flash');
						//$this->redirect('marks');
					}
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{   
                      $this->params['data'] = $this->Mark->read(null, Sanitize::paranoid($id));
                      $analogs = array();
                      foreach($this->params['data']['MarkAnalog'] as $analog)
                                $analogs[] = $analog['name'];

                      $this->set('analogs', implode(',', $analogs));
               }
	 	
		$data = $this->Mark->findAll("", null, $this->order, $this->show, $this->page, 2);
		
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/marks/?page=';		
		$paging['count'] = $this->Mark->findCount("",2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);
		
	}
}