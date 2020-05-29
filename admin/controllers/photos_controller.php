<?php
uses('sanitize');

class PhotosController extends AppController
{
	var $name = 'Photos';
	var $helpers = array("Html", "Pagination");
	var $components = array('File');
	
	function index() {		
	 	$this->set("data", $this->Photo->findAll());
	 	$this->set("title", "Галерея объектов");
	}
	
	function add() {
	 
		if (!empty($this->params['form'])) { 
		 
		 	$errors = false;
			$path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'objects'.DS;				
			$filename = mt_rand();
		
			if(!$this->File->upload_image($this->params['form']['picture'],$filename,$path) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,180) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,98))
			{
			
				$this->Photo->invalidate('picture');
				$this->set("error", $this->File->error);
				$errors = true;			
			}						 		  
			
			if(!$errors) {
				
				$this->data['Photo']['id'] = null; 
				$this->data['Photo']['picture'] = $this->File->filename;
					
				if ($this->Photo->save($this->data)) {
			 		$this->redirect('photos');
			 	}
			}
			else {
				$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
			}
		}		
		$this->set("title", "Добавление изображения в фотогалерею");
	}
	
	function delete($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->Photo->read(null, $id); 

			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'objects'.DS.$data['Photo']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'objects'.DS.'180'.DS.$data['Photo']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'objects'.DS.'98'.DS.$data['Photo']['picture']);

			$this->Photo->del($data['Photo']['id']);	
		}
	    $this->redirect('photos');
	}

       
}
?>