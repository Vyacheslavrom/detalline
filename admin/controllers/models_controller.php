<?php
uses('sanitize');

class ModelsController extends AppController
{
	var $name = 'Models';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'name';
	var $direction = 'ASC';
	var $page;
	var $order;
	var $uses = array('SmModel');
	
	function __construct() {
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}
	
	function select() {	
		if(!empty($_GET['id'])) {
			$where = "mark_id = '".Sanitize::paranoid($_GET['id'])."' ";
		} else {
			$where = "1=0";
		}
		$data = $this->SmModel->findAll($where,null, "Model.name ASC");
		$this->set('data',$data);
	}
	
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			$this->SmModel->del($id);	
			@$flash .= "Модель $id удалена. ";
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('models');
	}
	
	function index($id=null) {
	 
		$this->set('title','Модели');
		
		if (!empty($this->data)) {	
			 	
				$errors = false;
			
				if(!$this->SmModel->validates($this->data)) {
					$errors = true;
				}
			
				if(!$errors) {
				
					//название начинается с заглавной
					$this->data['Model']['name'] = ucfirst($this->data['Model']['name']);
					
                                        $analogs = array_diff(explode(',',$_POST['analogs']), array(''));
					if(!in_array($this->data['Model']['name'], $analogs))
						$analogs[] = $this->data['Model']['name'];
                                         $exceptions = array_diff(explode(',',$_POST['exceptions']), array(''));
		
					if($this->SmModel->save($this->data)) {

                                                 mysql_query("delete from model_analogs where model_id = '".$this->SmModel->id."' ");
                                                 foreach($analogs as $analog)
                                                                 mysql_query("insert into model_analogs(model_id, name) values('".$this->SmModel->id."', '".$analog."') ");

                                                mysql_query("delete from model_exceptions where model_id = '".$this->SmModel->id."' ");
                                                 foreach($exceptions as $exception)
                                                                 mysql_query("insert into model_exceptions(model_id, name) values('".$this->SmModel->id."', '".$exception."') ");

						$this->Session->setFlash('Запись сохранена.','flash');
						$this->redirect('models?mark='.$this->data['Model']['mark_id']);
					}
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{
                        $this->params['data'] = $this->SmModel->read(null, Sanitize::paranoid($id));
                        $analogs = array();
                        foreach($this->params['data']['ModelAnalog'] as $analog)
                                $analogs[] = $analog['name'];
                        $this->set('analogs', implode(',', $analogs));

                         $exceptions = array();
                        foreach($this->params['data']['ModelException'] as $exception)
                                $exceptions[] = $exception['name'];
                        $this->set('exceptions', implode(',', $exceptions));
                 }
	 	
		
		if(isset($_GET["mark"])) {		
			$search_fields = array();
			$search_fields["mark"] = @$_GET["mark"];
			$search_fields["nm"] = @$_GET["nm"];
			
			$this->Session->del('search_models');	
			$this->Session->write('search_models',$search_fields);		
		}
		
		if($this->Session->valid('search_models'))
			$sess_search = $this->Session->read('search_models');
			
		$where = array();
		if(!empty($sess_search) && is_array($sess_search))
		{
			if(!empty($sess_search["mark"])) 
				$where[] = " Model.mark_id = '".Sanitize::paranoid($sess_search["mark"])."' "; 	
			
			if(!empty($sess_search["nm"])) 
				$where[] = " Model.name LIKE '%".$sess_search["nm"]."%' "; 
        }
				
		$data = $this->SmModel->findAll($where, null, $this->order, $this->show, $this->page, 2);
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/models/?page=';		
		$paging['count'] = $this->SmModel->findCount($where,2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);

		$this->set('marks', $this->SmModel->Mark->findAll(null,null,"Mark.name ASC"));
		$this->set('sel_mark', @$sess_search['mark']);
		$this->set('sel_nm', @$sess_search['nm']);
	}
}
?>