<?php
uses('sanitize');

class ArticlesController extends AppController
{
	var $name = 'Articles';
	
	function view() {	
		if(!empty($this->params['link'])) {
			$where = "Article.url = '".Sanitize::paranoid($this->params['link'],array('.','_','-'))."'  and active=1";
			$data = $this->Article->findAll($where);
			$this->set('data',$data);
			$this->set('title', $data[0]['Article']['meta_title']);
			$this->set('meta_description', $data[0]['Article']['meta_description']);
			$this->set('meta_keywords', $data[0]['Article']['meta_keywords']);
		} else {
			$this->redirect("/");
		}
	}
}
?>