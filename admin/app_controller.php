<?php
/* SVN FILE: $Id: app_controller.php 2951 2006-05-25 22:12:33Z phpnut $ */
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP v 0.2.9
 * @version			$Revision: 2951 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2006-05-25 17:12:33 -0500 (Thu, 25 May 2006) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * This is a placeholder class.
 * Create the same file in app/app_controller.php
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		cake
 * @subpackage	cake.cake
 */
class AppController extends Controller
{
 	var $components = array('othAuth','RequestHandler','Sendmail');
 	var $othAuthRestrictions = "*";
	var $uses = array("Customer","ResolutionUser");
 
 	function __construct()
	{
		parent::__construct();
	}
	
	function beforeFilter()
	{
		$auth_conf = array('auto_redirect' => true,
		'login_page'  => 'users/login',
		'logout_page' => 'users/logout',
		'access_page' => '/',
		'hashkey'     => 'MySEcEeTHaSHKeYz',
		'strict_gid_check' => false
		);

		$this->othAuth->controller = &$this;
		$this->othAuth->init($auth_conf);
		$this->othAuth->check();
		
		$where = explode('/',$_SERVER['REQUEST_URI']);
		if($this->othAuth->user('id')!=''){
			$subs = $this->ResolutionUser->findAll('WHERE user_id='.$this->othAuth->user('id'));
			$url_can = array('BAD','products','products','prices','customers','orders','messages','articles','articles','adverts','polls','BAD','BAD','BAD','marks','models','subcategories','payments','manufacturers','movements','storages','users','movements/credentials','contact');
			foreach($subs as $sub){
				$i=0;
				while($i<23){
					$i++;
					if($i==23){
					if($where[2].'/'.$where[3]==$url_can[$i]) if($sub['ResolutionUser']['sub'.$i]==0) if($where[3]!='logout')$this->redirect('/');}
					else{
					if($where[2]==$url_can[$i]) if($sub['ResolutionUser']['sub'.$i]==0) if($where[3]!='logout')$this->redirect('/');}			
				}
			}
			$this->set('subs',$subs);
		}

        if(isset($_GET['del_sessions'])) {
		  mysql_query("update customers set session_uniq_code = ''");
		  mysql_query("update customers set session_die = '1'");
          $this->redirect('/');
		}		
 	}
	
	function beforeRender() {		
		header('Content-Type: text/html; charset=windows-1251');
	}
	
	function title_translate($title) {
	 	switch($title) {
	 	 	case "Home" : return "Панель администрирования"; break;
			default: return $title; break;
		}				
	}
	
	function _alphabet() {
		return array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф',
			'х','ц','ч','ш','щ','ь','ы','ъ','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М',
			'Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ь','Ы','Ъ','Э','Ю','Я');
	}
}
?>
