<?php
uses('sanitize');

class ArticlePicturesController extends AppController
{
	var $name = 'ArticlePictures';
	var $helpers = array("Html", "Pagination");
	var $components = array('File');
	
	function view($article=null) {
	 	
	 	if(!$article) {
			$this->redirect('/');
		}
		$this->set("article", $article);
		
	    $data = $this->ArticlePicture->findAll("article_id='".Sanitize::paranoid($article)."'");
	 	$this->set("data", $data);
	 	$this->set("title", "Редактирование информации о товаре");
	}
	
	function add($article = null) {
	 
	    if(!$article) {
			$this->redirect('/');
		}
		
		$this->set("article", $article);

		if (!empty($this->params['form'])) { 
		 
		 	$errors = false;
			$path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'articles'.DS.'dop_photos'.DS;				
			$filename = mt_rand();
				
			if(!$this->File->upload_image($this->params['form']['picture'],$filename,$path) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,180) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,98))
			{
			
				$this->ArticlePicture->invalidate('picture');
				$this->set("error", $this->File->error);
				$errors = true;			
			}						 		  
			
			if(!$errors) {
				
				$this->data['ArticlePicture']['id'] = null; 
				$this->data['ArticlePicture']['article_id'] = $article; 
				$this->data['ArticlePicture']['picture'] = $this->File->filename;
					
				if ($this->ArticlePicture->save($this->data)) {
			 		$this->redirect('article_pictures/view/'.$article);
			 	}
			}
			else {
				$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
			}
		}		
		$this->set("title", "Редактирование информации о товаре");
	}
	
	function delete($id=null) {
		$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {			
		 	$data = $this->ArticlePicture->read(null, $id); 

			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'articles'.DS.'dop_photos'.DS.$data['ArticlePicture']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'articles'.DS.'dop_photos'.DS.'180'.DS.$data['ArticlePicture']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'articles'.DS.'dop_photos'.DS.'98'.DS.$data['ArticlePicture']['picture']);

			$this->ArticlePicture->del($data['ArticlePicture']['id']);	
		}
	    $this->redirect('article_pictures/view/'.$data['ArticlePicture']['article_id']);
	}
}
?>