<?php
uses('sanitize');

class ModelsController extends AppController
{
	var $name = 'Models';
	
	function select() {	
	
		if(!empty($_GET['id'])) {
			$where = "mark_id = '".Sanitize::paranoid($_GET['id'])."' ";
		} else {
      $where = "1=0";
		}
		$data = $this->Model->findAll($where,null, "name ASC");
		$this->set('data',$data);
	}
}
?>