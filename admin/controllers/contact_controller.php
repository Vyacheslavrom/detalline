<?php
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);


uses('sanitize');

class ContactController extends AppController
{
	var $name = 'contact';
	var $uses = array('Poll', 'PollVariant');
	var $helpers = array("Html", "Pagination");
	
	function index() {
	    $data = $this->Poll->findAll();
	 	$this->set("data", $data);
	 	$this->set("title", "Contact");
		//echo $_POST['personal']; 
		//echo htmlspecialchars(print_r($_POST, true));
	}
 
}
?>