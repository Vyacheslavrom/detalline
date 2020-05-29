<?php
uses('sanitize');

class ModelModificationsController extends AppController
{
	var $name = 'ModelModifications';
	var $helpers = array("Html", "Pagination");
	
	function view($model=null) {
	 	
	 	if(!$model) {
			$this->redirect('/');
		}
		$this->set("model", $this->ModelModification->Model->find("id=".$model));
		
	    $data = $this->ModelModification->findAll("model_id='".Sanitize::paranoid($model)."'");
	 	$this->set("data", $data);
	 	$this->set("title", "Редактирование информации о модели");
	}
	
	function add($id = null) {

		if (!empty($this->params['form'])) { 

			if(!$this->ModelModification->save($this->data))	
				$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
			else {
				$this->redirect('model_modifications/view/'.$this->data['ModelModification']['model_id']);
			}
		} else {
		    if($id) {
				$this->params['data'] = $this->ModelModification->read(null, $id);
			}
			if(!empty($_GET['model'])) {
				$this->params['data']['ModelModification']['model_id'] = $_GET['model'];	
			}
        }		
		$this->set("model", $this->ModelModification->Model->find("id=".$this->params['data']['ModelModification']['model_id']));
		$this->set("title", "Добавление/редактирование модификации модели");
	}
	
	function delete($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->ModelModification->read(null, $id); 
			$this->ModelModification->del($data['ModelModification']['id']);	
		}
	    $this->redirect('model_modifications/view/'.$data['ModelModification']['model_id']);
	}
}
?>