<?php
uses('sanitize');

class PhotosController extends AppController
{
	var $name = 'Photos';
	var $helpers = array("Html", "Pagination");
	
	function index() {		
	 	$this->set("data", $this->Photo->findAll());
	}
	
	
}
?>