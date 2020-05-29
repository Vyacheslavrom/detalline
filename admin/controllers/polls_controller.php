<?php
uses('sanitize');

class PollsController extends AppController
{
	var $name = 'Polls';
	var $uses = array('Poll', 'PollVariant');
	var $helpers = array("Html", "Pagination");
	
	function index() {
	    $data = $this->Poll->findAll();
	 	$this->set("data", $data);
	 	$this->set("title", "Опросы");
	}
	
	function add($id = null) {

		if (!empty($this->params['form'])) { 

			if(!$this->Poll->save($this->data))	
				$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
			else {
				mysql_query("delete from poll_variants where poll_id = '".$this->Poll->id."'");
				foreach($this->data['PollVariant'] as $variant)
					if(!empty($variant))
						mysql_query("insert into poll_variants(poll_id,name) values('".$this->Poll->id."','".$variant."')");
				$this->redirect('polls/index');
			}
		} else {
		    if($id) {
				$this->params['data'] = $this->Poll->read(null, $id);
				$i=0;
				foreach($this->params['data']['PollVariant'] as $variant)
					$this->params['data']['PollVariant']['name_'.($i++)] = $variant['name'];
			}
        }		
		$this->set("title", "Добавление/редактирование опроса");
	}
	
	function delete($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->Poll->read(null, $id); 
			foreach($data['PollVariant'] as $variant)
				$this->PollVariant->del($variant['id']);	
			$this->Poll->del($data['Poll']['id']);	
		}
	    $this->redirect('polls/index');
	}
	
	function activate($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->Poll->read(null, $id); 
			$data['Poll']['status'] = 1;	
			$this->Poll->save($data);	
		}
	    $this->redirect('polls/index');
	}
	
	function close($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->Poll->read(null, $id); 
			$data['Poll']['status'] = 2;	
			$this->Poll->save($data);	
		}
	    $this->redirect('polls/index');
	} 
}
?>