<?php
class UsersController extends AppController
{

var $uses = array('User','ResolutionUser');

    function login()
	{
		if(isset($this->params['data']))
		{
			$auth_num = $this->othAuth->login($this->params['data']['User']);			
			$this->set('auth_msg', $this->othAuth->getMsg($auth_num));
		}
		$this->set("title", "Вход в панель администрирования");
	}

    function logout()
    {
        $this->othAuth->logout();
		$this->set("title", "Вход в панель администрирования");
		$this->render("login");
    }
	
	function index()
	{
		$this->set('users',$this->User->findAll());
		if(isset($_POST['save'])){
			$user['User']['id'] = $this->othAuth->user('id');
			$user['User']['password'] = md5($_POST['changepass']);
			$this->User->save($user);
		}
		
		if($_GET['id']!='new' && !empty($_GET['id'])){
			$this->set('subs', $this->ResolutionUser->findAll('WHERE user_id='.$_GET['id']));
		}
		
		if(isset($_POST['save_user'])){
			if($_GET['id']!='new'){
				$resolutions = $this->ResolutionUser->findAll('WHERE user_id='.$_GET['id']);
				foreach($resolutions as $resolution) $newuser['ResolutionUser']['id'] = $resolution['ResolutionUser']['id'];
				$user['User']['id']=$_GET['id'];
				$user['User']['username'] = $_POST['user_login'];
				$user['User']['name'] = $_POST['user_name'];
				if(!empty($_POST['user_pass']))
					$user['User']['password'] = md5($_POST['user_pass']);
				$user['User']['email'] = $_POST['user_email'];
				$this->User->save($user);
				$newuser['ResolutionUser']['user_id'] = $_GET['id'];
			}else{
				$user['User']['username'] = $_POST['user_login'];
				$user['User']['name'] = $_POST['user_name'];
				$user['User']['password'] = md5($_POST['user_pass']);
				$user['User']['email'] = $_POST['user_email'];
				$user['User']['group_id'] = 2;
				$user['User']['active'] = 1;
				$user['User']['created'] = date('Y-m-d H:i:s');
				$user['User']['modified'] = date('Y-m-d H:i:s');
				$this->User->save($user);
				$allusers = $this->User->findAll();
				foreach($allusers as $alluser)
				$newuser['ResolutionUser']['user_id'] = ++$alluser['User']['id'];
			}
			$i=0;
			while($i<23){
				$i++;
				$newuser['ResolutionUser']['sub'.$i] = ($_POST['sub'.$i])? $_POST['sub'.$i]:0;
			}
			$this->ResolutionUser->save($newuser);
			$this->redirect('/users/');
		}
		
	}
}
?>
