<?php
uses('sanitize');

class CitiesController extends AppController
{
	var $name = 'Cities';
	
	function select() {	
	
		if(!empty($_GET['id'])) {
			$where = "region_id = '".Sanitize::paranoid($_GET['id'])."' AND new=0 ";
		} else {
      $where = "1=0";
		}
		$data = $this->City->findAll($where,null, "name ASC");
		$this->set('data',$data);
	}
}
?>