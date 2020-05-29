<?php
uses('sanitize');

class MovementsController extends AppController
{
	var $name = 'Movements';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'created';
	var $direction = 'DESC';
	var $page;
	var $order;
	
	function __construct() {
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}
	
	
	function index($id=null) {
	 
		$this->set('title', 'ћои взаиморасчеты');

		if(!$this->logged) {
			$this->redirect("/");
		}  
	 		
		if (isset($_GET["created_from"])) {		
			$search_fields = array();
			$search_fields["created_from"] = @$_GET["created_from"];
			$search_fields["created_to"] = @$_GET["created_to"];
			
			$this->Session->del('search_movements');	
			$this->Session->write('search_movements',$search_fields);		
		}
		
		if($this->Session->valid('search_movements'))
			$sess_search = $this->Session->read('search_movements');
			
		$where = " Movement.customer_id = ".$this->othAuth->user("id")." AND deleted IS NULL ";
		if(!empty($sess_search) && is_array($sess_search))
		{
			if(!empty($sess_search["created_from"])) 
				$where .= " AND Movement.created >= '". date('Y-m-d 00:00:00', strtotime($sess_search['created_from'])) . "' "; 	
				
			if(!empty($sess_search["created_to"])) 
				$where .= " AND Movement.created <= '". date('Y-m-d 23:59:59', strtotime($sess_search['created_to'])) . "' "; 		
        }
				
		//------------сводные данные-----------------
		$summery = mysql_fetch_array(mysql_query("select sum(CASE WHEN Movement.movement_object_id = 1 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id = 1 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as money,
		sum(CASE WHEN Movement.movement_object_id = 2 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id = 2 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as parts,
		sum(CASE WHEN Movement.movement_object_id > 2 AND Movement.movement_direction_id = 1 THEN -sum WHEN Movement.movement_object_id > 2 AND Movement.movement_direction_id = 2 THEN sum ELSE 0 END) as other_objects
		from movements as Movement where ".$where));
		$this->set('summery', $summery);
		//------------сводные данные-----------------
		
		$data = $this->Movement->findAll($where, null, $this->order, $this->show, $this->page, 2);
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/movements/?page=';		
		$paging['count'] = $this->Movement->findCount($where,2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);
		
		$this->set('sel_created_from', @$sess_search['created_from']);
		$this->set('sel_created_to', @$sess_search['created_to']);
	}
}
?>