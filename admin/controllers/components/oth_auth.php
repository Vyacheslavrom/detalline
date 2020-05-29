<?php

// othAuth By othman ouahbi.
// comments, bug reports are welcome crazylegs@gmail.com

class othAuthComponent extends Object
{
	
/**
* Constants to modify the behaviour of othAuth Component
*/
	var $user_login_var        = 'username';
	var $user_passw_var        = 'password';
	var $user_group_var        = 'group_id';
	
	var $user_table       	   = 'users';
	
	var $user_table_login      = 'username';
	var $user_table_passw      = 'password';
	var $user_table_gid        = 'group_id';
	var $user_table_active     = 'active';
	var $user_table_last_visit = 'last_visit';
	var $auth_url_redirect_var = 'from';
	var $user_model       = 'User';
	var $group_model      = 'Group';
	var $permission_model = 'Permission';

	/*
	 * Internals you don't normally need to edit those
	 */
	var $components = array('Session');
	var $controller = true;
	
	var $redirect_page;
	var $hashkey = "mYpERsOnALhaSHkeY";
	var $auto_redirect;
	var $gid = 3;
	var $login_page = '';
	var $logout_page = '';
	var $access_page = '';
	var $strict_gid_check = true;
	
	function init($auth_config = null) 
	{
		if(is_array($auth_config) && !is_null($auth_config) && !empty($auth_config))
		{
			$this->login_page       = isset($auth_config['login_page']) ? $auth_config['login_page']  : 'users/login';
			$this->logout_page      = isset($auth_config['logout_page'])? $auth_config['logout_page'] : 'users/logout';
			$this->access_page      = isset($auth_config['access_page'])? $auth_config['access_page'] : $this->login_page;
			$this->auto_redirect    = isset($auth_config['auto_redirect']) ? (boolean)$auth_config['auto_redirect']  : true;
			$this->hashkey          = isset($auth_config['hashkey'])? (string) $auth_config['hashkey'] : 'mYpERsOnALhaSHkeY';
			$this->strict_gid_check = isset($auth_config['strict_gid_check']) ? (boolean)$auth_config['strict_gid_check']  : true;
		}
		else
		{
			$this->login_page       = 'users/login';
			$this->logout_page      = 'users/logout';
			$this->auto_redirect    = true;
			$this->hashkey          = "mYpERsOnALhaSHkeY";
			$this->strict_gid_check = true;
		}
		
		// pass auth data to the view so it can be used by the helper
		$this->_passAuthData();
	}
	
	function login($params) // username,password,group
	{	 
		 if($this->Session->valid() && $this->Session->check('othAuth_'.$this->hashkey))
		 {
		 	return 1;
		 }
		 
		 if($params == null || 
		 	!isset($params[$this->user_login_var]) || 
		 	!isset($params[$this->user_passw_var]))
		 {
		 	return 0;
		 }
		 
		 uses('sanitize');
		 $login = Sanitize::paranoid($params[$this->user_login_var]);
		 $passw = Sanitize::paranoid($params[$this->user_passw_var]);
		 if(isset($params[$this->user_group_var]))
		 {
		 	
		 	$this->gid = (int) Sanitize::paranoid($params[$this->user_group_var]);
			if( $this->gid < 1)
			{
				$this->gid = 1;
			}
		 }

		 if($login == "" || $passw == "") 
		 {
		 	return -1;
		 }
		 
		$passw = md5($passw);
		$gid_check_op = ($this->strict_gid_check)?'':'<=';
		$conditions = array(
							"{$this->user_model}.".$this->user_table_login => "$login",
							"{$this->user_model}.".$this->user_table_passw => "$passw",
							"{$this->user_model}.".$this->user_table_gid => "$gid_check_op{$this->gid}",
							"{$this->user_model}.".$this->user_table_active => 1); 
		
		$this->controller->{$this->user_model}->recursive = 5;			
		$row = $this->controller->{$this->user_model}->find($conditions);
		
		
		$num_users = (int) $this->controller->{$this->user_model}->findCount($conditions);
		if( empty($row) || $num_users != 1 )
		{			
			return -2;
		}
		else
		{
			
			$this->_saveSession($row);
			
			// Update the last visit date to now
			if(isset($this->user_table_last_visit))
			{	
				$row["{$this->user_model}"][$this->user_table_last_visit] = date('Y-m-d h:i:s');
				//$res = $this->controller->{$this->user_model}->save($row,true,array($this->user_table_last_visit)); 
				$res = $this->controller->{$this->user_model}->save($row); 
			}
			if($this->auto_redirect == true)
			{
				$this->redirect($this->access_page);
			}
			
			return 1;
		}
		 
	}
	
	function _saveSession($row)
	{	
		 $login = $row[$this->user_model][$this->user_table_login];
		 $passw = $row[$this->user_model][$this->user_table_passw];
		 $gid   = $row[$this->user_model][$this->user_table_gid];
		 //$hk = md5($this->hashkey.$login.$passw.$this->gid);
		 $hk    = md5($this->hashkey.$login.$passw.$gid);
		 $row["{$this->user_model}"]['login_hash'] = $hk;
 		 $row["{$this->user_model}"]['hashkey']    = $this->hashkey;
 		 //$this->Session->write('othAuth_'.$this->gid,$row);
		 $this->Session->write('othAuth_'.$this->hashkey,$row);

	}
	
	function __notcurrent($page)
	{
		if($page == "") return false;
		
		$c = strtolower($this->controller->name);
		$a = strtolower($this->controller->action);
		
		$page = strtolower($page.'/');
		
		$c_a = $this->_handleCakeAdmin($c,$a);
		
		$not_current = strpos($page,$c_a);
		// !== is required, $not_current might be boolean(false)
		return ((!is_int($not_current)) || ($not_current !== 0));
	}
	
 	function redirect($page = "",$back = false)
	{
		if($page == "") 
			//$page = $this->redirect_page;
			$page = $this->logout_page;
			
		if(isset($this->auth_url_redirect_var))
		{
			if(!isset($this->controller->params['url'][$this->auth_url_redirect_var]))
			{	
				//die("1");
				if($back == true)
				{
					$this->Session->write('othauth_from_page',@$this->controller->params['url']['url']);
					$page .= "?".$this->auth_url_redirect_var."=".@$this->controller->params['url']['url'];
				}
				else 
				{	
				 				 
					if($this->Session->check('othauth_from_page'))
					{
						$page = $this->Session->read('othauth_from_page');
						$this->Session->del('othauth_from_page');
					}
				}
				if($this->__notcurrent($page))
					$this->controller->redirect($page);
			}
		}
		else
		{
			if($this->__notcurrent($page))
				$this->controller->redirect($page);
		}
    }
	
	// users/login users/logout
    // Logout the user
    function logout ()
	{
		$us = 'othAuth_'.$this->hashkey;
		
		if($this->Session->valid($us))
		{
			$ses = $this->Session->read($us);
			if(!empty($ses) && is_array($ses))
			{
				// two logins of different hashkeys can exist
				if($this->hashkey == $ses["{$this->user_model}"]['hashkey'])
				{
					$this->Session->del($us);
					$this->Session->del('othauth_from_page');					
				}
			}
		}
		if($this->auto_redirect == true) 
		{				
			$this->redirect($this->logout_page);
		}
    }
	

    // Confirms that an existing login is still valid
    function check()
	{
		// is there a restriction list && action is in
		if($this->_validRestrictions())
		{

			$us 	   = 'othAuth_'.$this->hashkey;
			
			// does session exists
			//if(!empty($ses) && is_array($ses))
			if($this->Session->valid() && 
			   $this->Session->check($us))
			{
				$ses 	   = $this->Session->read($us);
				$login     = $ses["{$this->user_model}"][$this->user_table_login];
				$password  = $ses["{$this->user_model}"][$this->user_table_passw];
				$gid       = $ses["{$this->user_model}"][$this->user_table_gid];
				$hk        = $ses["{$this->user_model}"]['login_hash'];
				
				// is user invalid
				
				if (md5($this->hashkey.$login.$password.$gid) != $hk)
				{
					/*
					if($this->auto_redirect == true) 
					{
						$this->logout();	
					}
					*/
					
					$this->logout();
					return false;
				}
				
				// check permissions on the current controller/action/p/a/r/a/m/s
				if(!$this->_checkPermission($ses))
				{
					if($this->auto_redirect == true) 
					{
						// should probably add $this->noaccess_page too or just flash
						$this->redirect($this->login_page,true);
					}
					return false;
				}
				
				return true;
				
			}
			
			if($this->auto_redirect == true) 
			{
				$this->redirect($this->login_page,true);
			}
			return false;	
		}
		
		return true;
    }
	
	function _validRestrictions()
	{
		
		$isset   = isset($this->controller->othAuthRestrictions);
		if($isset)
		{
			$oth_res = $this->controller->othAuthRestrictions;
			
			if(is_string($oth_res))
			{
				
				
				if(($oth_res === "*") ||(
				defined('CAKE_ADMIN') && (($oth_res === CAKE_ADMIN) || $this->isCakeAdminAction())))
				{
					if(
					   $this->__notcurrent($this->login_page) && 
					   $this->__notcurrent($this->logout_page))
					{
						//die('here');
						return true;
					}	
				}
				
			}
			elseif(is_array($oth_res))
			{
				if(defined('CAKE_ADMIN'))
				{
					if(in_array(CAKE_ADMIN,$oth_res))
					{
						if($this->isCakeAdminAction())
						{
							if($this->__notcurrent($this->login_page) && 
							   $this->__notcurrent($this->logout_page))
							{
								return true;
							}
						}
					}
				}
				foreach($oth_res as $r)
				{
					$pos = strpos($r."/",$this->controller->action."/");
					if($pos === 0)
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	function _checkPermission(&$ses)
	{
		//die('c');
		$c   = strtolower($this->controller->name);
		$a   = strtolower($this->controller->action);
		$h   = strtolower($this->controller->here);
		$c_a = $this->_handleCakeAdmin($c,$a);// controller/admin_action -> admin/controller/action
		
		// extract params
		$aa  =  substr( $c_a, strpos($c_a,'/'));
		
		$params = isset($this->controller->params['pass'])?implode('/',$this->controller->params['pass']): '';
		
		$c_a_p = $c_a.$params;
		
		
		$return = false;
		
		if(!isset($ses[$this->group_model][$this->permission_model]))
		{
			return false;
		}
		$ses_perms = $ses[$this->group_model][$this->permission_model];
		
		
		// quickly check if the group has full access (*) or 
		// current_controller/* or CAKE_ADMIN/current_controller/*
		// full params check isn't supported atm
		foreach($ses_perms as $sp)
		{
			if($sp['name'] == '*')
			{
				return true;
			}else
			{
				$sp_name = strtolower($sp['name']);
				$perm_parts = explode('/',$sp_name);
				// users/edit/1 users/edit/*
				//  users/* users/*
				
				if(defined('CAKE_ADMIN'))
				{
					
					if((count($perm_parts) > 1)  && 
					   ($perm_parts[0] == CAKE_ADMIN) &&
					   ($perm_parts[1] == strtolower($c)) && 
					   ($perm_parts[2] == "*"))
					{
						return true;
					}
				}else
				{
					if((count($perm_parts) > 1)  && 
					   ($perm_parts[0] == strtolower($c)) && 
					   ($perm_parts[1] == "*"))
					{
						return true;
					}
				}

			}
		}
		
		
		if(is_string($this->controller->othAuthRestrictions))
		{
			$is_checkall   = $this->controller->othAuthRestrictions === "*";
			$is_cake_admin = defined('CAKE_ADMIN') && ($this->controller->othAuthRestrictions === CAKE_ADMIN);
			if($is_checkall || $is_cake_admin)
			{
				foreach($ses_perms as $p)
				{	
					if(strpos($c_a_p,strtolower($p['name'])) === 0)
					{
						$return = true;
						break;
					}
				}
			}
		}
		else 
		{
			$a_p_in_array = in_array($a.'/'.$params, $this->controller->othAuthRestrictions);
			
			// if current url is restricted, do a strict compare
			// ex if current url action/p and current/p is in the list
			// then the user need to have it in perms
			// current/p/s current/p
			if($a_p_in_array)
			{
				
				foreach($ses_perms as $p)
				{
					if($c_a_p == strtolower($p['name']))
					{
						$return = true;
						break;
					}
				}
			}
			// allow a user with permssion on the current action to access deeper levels
			// ex: user access = 'action', allow 'action/p'
			else 
			{
				foreach($ses_perms as $p)
				{
					if(strpos($c_a_p,strtolower($p['name'])) === 0)
					{
						$return = true;
						break;
					}
				}
			}
			
		}
		
		//return $return;
		return true;
	}
	
	function _handleCakeAdmin($c,$a)
	{
		if(defined('CAKE_ADMIN'))
		{
			$strpos = strpos($a,CAKE_ADMIN.'_');
			if($strpos === 0)
			{
				$function = substr($a,strlen(CAKE_ADMIN.'_'));
				if($c == null) return $function.'/';
				$c_a = CAKE_ADMIN.'/'.$c.'/'.$function.'/';
				return $c_a;
			}else
			{
				if($c == null) return $a.'/';
			}	
		}
		return $c.'/'.$a.'/';
	}
	
	function getSafeCakeAdminAction()
	{
		if(defined('CAKE_ADMIN'))
		{
			$a = $this->controller->action;
			$strpos = strpos($a,CAKE_ADMIN.'_');
			if($strpos === 0)
			{
				$function = substr($a,strlen(CAKE_ADMIN.'_'));
				
				return $function;
			}
		}
		return $this->controller->action;
	}
	
	function isCakeAdminAction()
	{
		if(defined('CAKE_ADMIN'))
		{
			$a = $this->controller->action;
			$strpos = strpos($a,CAKE_ADMIN.'_');
			if($strpos === 0)
			{
				return true;
			}
		}
		return false;
	}
	
	// helper methods
	function user($arg)
	{
		$us = 'othAuth_'.$this->hashkey;
		// does session exists
		if($this->Session->valid() && $this->Session->check($us))
		{
			$ses = $this->Session->read($us);
			if(isset($ses["{$this->user_model}"][$arg]))
			{
				return $ses["{$this->user_model}"][$arg];
			}
			else
			{
				return false;
			}
		}
		return false;	
	}
	
	// helper methods
	function group($arg)
	{
		$us = 'othAuth_'.$this->hashkey;
		// does session exists
		if($this->Session->valid() && $this->Session->check($us))
		{
			$ses = $this->Session->read($us);
			if(isset($ses["{$this->group_model}"][$arg]))
			{
				return $ses["{$this->group_model}"][$arg];
			}
			else
			{
				return false;
			}
		}
		return false;	
	}
	
	
	// helper methods
	function permission($arg)
	{
		$us = 'othAuth_'.$this->hashkey;
		// does session exists
		if($this->Session->valid() && $this->Session->check($us))
		{
			$ses = $this->Session->read($us);
			if(isset($ses[$this->permission_model]))
			{
				$ret = array();
				if(is_array($ses[$this->permission_model]))
				{
					for($i = 0; $i < count($ses[$this->permission_model]); $i++ )
					{
						$ret[] = $ses[$this->permission_model][$i][$arg];	
					}
				}
				return $ret;
			}
			else
			{
				return false;
			}
		}
		return false;	
	}
	
	function getData()
	{
		$us = 'othAuth_'.$this->hashkey;
		// does session exists
		if($this->Session->valid() && $this->Session->check($us))
		{
			return $this->Session->read($us);
		}
		return false;	
	}
	
	// passes data to the view to be used by the helper
	function _passAuthData()
	{

		$data['hashkey']                 = $this->hashkey;
		$data['user_model']              = $this->user_model;
		$data['group_model']             = $this->group_model;
		$data['permission_model']        = $this->permission_model;
		$data['login_page']              = $this->login_page;
		$data['logout_page']             = $this->logout_page;
		$data['access_page']             = $this->access_page;
		$data['auth_url_redirect_var']   = $this->auth_url_redirect_var;
		$data['strict_gid_check']        = $this->strict_gid_check;
		
		$this->controller->set('othAuth_data',$data);	
	}
	
	function getMsg($id) 
	{
		switch($id) {
		case 1:
			{
				return "You are Logged In !";
			}break;
		case 0:
			{
				return "Please Log In !";
			}break;
		case -1:
			{
				 return $this->user_login_var."/".$this->user_passw_var." Empty!";
			}break;
		case -2:
			{
				 return "Incorrect ".$this->user_login_var."/".$this->user_passw_var."!";
			}break;
		default:
			{
				 return "Invalid error ID";
			}break;
		
		}
	}
}
?>
