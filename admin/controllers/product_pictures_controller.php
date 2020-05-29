<?php
uses('sanitize');

class ProductPicturesController extends AppController
{
	var $name = 'ProductPictures';
	var $helpers = array("Html", "Pagination");
	var $components = array('File');
	
	function view($product=null) {
	 	
	 	if(!$product) {
			$this->redirect('/');
		}
		$this->set("product", $product);
		
	    $data = $this->ProductPicture->findAll("product_id='".Sanitize::paranoid($product)."'");
	 	$this->set("data", $data);
	 	$this->set("title", "Редактирование информации о товаре");
	}
	
	function add($product = null) {
	 
	    if(!$product) {
			$this->redirect('/');
		}
		
		$this->set("product", $product);

		if (!empty($this->params['form'])) { 
		 
		 	$errors = false;
			$path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS;				
			$filename = mt_rand();
				
			if(!$this->File->upload_image($this->params['form']['picture'],$filename,$path) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,180) ||
				!$this->File->upload_image_and_do_small($this->params['form']['picture'],$filename,$path,98))
			{
			
				$this->ProductPicture->invalidate('picture');
				$this->set("error", $this->File->error);
				$errors = true;			
			}						 		  
			
			if(!$errors) {
				
				$this->data['ProductPicture']['id'] = null; 
				$this->data['ProductPicture']['product_id'] = $product; 
				$this->data['ProductPicture']['picture'] = $this->File->filename;
					
				if ($this->ProductPicture->save($this->data)) {
			 		$this->redirect('product_pictures/view/'.$product);
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
		 	$data = $this->ProductPicture->read(null, $id); 

			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.$data['ProductPicture']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.'180'.DS.$data['ProductPicture']['picture']);
			$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.'98'.DS.$data['ProductPicture']['picture']);

			$this->ProductPicture->del($data['ProductPicture']['id']);	
		}
	    $this->redirect('product_pictures/view/'.$data['ProductPicture']['product_id']);
	}
}
?>