<?php
uses('sanitize');

class ArticlesController extends AppController
{
	var $name = 'Articles';
	var $helpers = array("Html", "Pagination");
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

	
	function edit($id = null) {	
		if (!empty($this->data)) {	
			if ($this->Article->save($this->data)) {
				$this->Session->setFlash('Запись сохранена','flash');
				$this->redirect("articles");
			}
			else {
				$this->Session->setFlash('<font color=blue>При сохранении обнаружены ошибки</font>','flash');
			}
		} else {
			$this->params['data'] = $this->Article->read(null, $id); 
		}
		
		if(!$this->params['data'])
			$this->params['data']['Article']['active'] = true;
        	
                $sess_search = $this->Session->read('search_articles');	
                $this->set('categories', $this->Article->ArtCategory->findAll('site_id='.$sess_search['site_id']));	
                $this->set('site_id', $sess_search['site_id']);
		$this->set('title', 'Редактирование статьи');
	}
	
	
	
	function active($id=null, $activity=1) {
	 	$ids = array();
	 	
	 	if($id && $id!=='all') { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
		 	$data = $this->Article->read(null, $id); 		
			$data['Article']['active'] = $activity;	      	 
			$this->Article->save($data);		
		}
	    $this->redirect('articles');		
	}
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
			$this->Article->del($id);	
		}
	    $this->redirect('articles');
	}
		    
	function index() {
	 
		$this->set('title','Статьи');
		
              if(isset($_GET['site_id'])) {
                       $search_fields = array();
			$search_fields["site_id"] = @$_GET["site_id"];

                        $this->Session->del('search_articles');
                        $this->Session->write('search_articles',$search_fields);			
              }

		if(isset($_GET["cat"])) {		
			$search_fields = array();
			$search_fields["cat"] = @$_GET["cat"];
			$search_fields["status"] = @$_GET["status"];
			$search_fields["txt"] = @$_GET["txt"];
                        $search_fields["site_id"] = @$_GET["site_id"];
			
			$this->Session->del('search_articles');	
			$this->Session->write('search_articles',$search_fields);		
		}
		
		if($this->Session->valid('search_articles'))
			$sess_search = $this->Session->read('search_articles');
			
		$where = array();
		if(!empty($sess_search) && is_array($sess_search))
		{
			$where[] = 'ArtCategory.site_id='.$sess_search['site_id'];
			if(!empty($sess_search['txt']))
		 	{$where[] = "Article.text LIKE  '%".Sanitize::paranoid($sess_search['txt'],$this->_alphabet())."%'";}	
			if(!empty($sess_search['status']) || @$sess_search['status']==='0')
		 	{$where[] = "active = ".Sanitize::paranoid($sess_search['status']);}	
			if(!empty($sess_search['cat']))
		 	{$where[] = "art_category_id = ".Sanitize::paranoid($sess_search['cat']);}	
                }

	 	
		$data = $this->Article->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 2);

		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
		$paging['count'] = $this->Article->findCount(implode(" AND ",$where),2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);

		$this->set('sel_txt', @$sess_search['txt']);
		$this->set('sel_status', @$sess_search['status']);
		$this->set('sel_cat', @$sess_search['cat']);
                $this->set('site_id', $sess_search['site_id']);
                $this->set('categories', $this->Article->ArtCategory->findAll('site_id='.$sess_search['site_id']));
	}
}
?>