<?php
/* SVN FILE: $Id: routes.php 6305 2008-01-02 02:33:56Z phpnut $ */
/**
 * Short description for file.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.app.config
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6305 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-01 20:33:56 -0600 (Tue, 01 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.thtml)...
 */
	$Route->connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
       
	
	

	$Route->connect('/sto_online.html', array('controller' => 'sto_requests', 'action' => 'add'));
        
	$Route->connect('/choose_models.html', array('controller' => 'models', 'action' => 'select'));
	$Route->connect('/choose_cities.html', array('controller' => 'cities', 'action' => 'select'));
       $Route->connect('/barbie-look.html', array('controller' => 'messages', 'action' => 'barbie_look'));
	
/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	
	$Route->connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
	$Route->connect('/index.html', array('controller' => 'pages', 'action' => 'display', 'home'));
	$Route->connect('/e-shopping.html', array('controller' => 'products', 'action' => 'catalog'));
        $Route->connect('/zapchasti_bu.html', array('controller' => 'products', 'action' => 'catalog','1'));
	$Route->connect('/search.html', array('controller' => 'products', 'action' => 'search'));
	$Route->connect('/contacts.html', array('controller' => 'pages', 'action' => 'display', 'contacts'));
	$Route->connect('/message.html', array('controller' => 'messages', 'action' => 'add'));
	$Route->connect('/skidki.html', array('controller' => 'pages', 'action' => 'display', 'skidki'));
	$Route->connect('/rasprodaja.html', array('controller' => 'pages', 'action' => 'display', 'rasprodaja'));
        $Route->connect('/zapchasti_na_zakaz.html', array('controller' => 'pages', 'action' => 'display', 'zapchasti_na_zakaz'));
        $Route->connect('/kak_oformit_zakaz.html', array('controller' => 'pages', 'action' => 'display', 'kak_oformit_zakaz'));
        $Route->connect('/vozvrat.html', array('controller' => 'pages', 'action' => 'display', 'vozvrat'));
        $Route->connect('/kak_zagruzit_price.html', array('controller' => 'pages', 'action' => 'display', 'kak_zagruzit_price'));
	$Route->connect('/oplata.html', array('controller' => 'pages', 'action' => 'display', 'oplata'));
	$Route->connect('/dostavka.html', array('controller' => 'pages', 'action' => 'display', 'dostavka'));
	$Route->connect('/forum.html', array('controller' => 'pages', 'action' => 'display', 'forum'));
	$Route->connect('/article.html', array('controller' => 'articles', 'action' => 'view'));
	$Route->connect('/vacancy.html', array('controller' => 'pages', 'action' => 'display', 'vacancy'));
	$Route->connect('/reklama.html', array('controller' => 'pages', 'action' => 'display', 'reklama'));
	$Route->connect('/shops.html', array('controller' => 'pages', 'action' => 'display', 'shops'));
	$Route->connect('/strahovanie.html', array('controller' => 'pages', 'action' => 'display', 'strahovanie'));
	$Route->connect('/sto.html', array('controller' => 'pages', 'action' => 'display', 'sto'));
	$Route->connect('/call.html', array('controller' => 'calls', 'action' => 'add'));
	$Route->connect('/callback.html', array('controller' => 'calls', 'action' => 'add_callback'));
	$Route->connect('/reverse.html', array('controller' => 'calls', 'action' => 'add_reverse'));
	$Route->connect('/call_process.html', array('controller' => 'calls', 'action' => 'process'));
	$Route->connect('/reverse_process.html', array('controller' => 'calls', 'action' => 'processr'));
	$Route->connect('/call_center.html', array('controller' => 'calls', 'action' => 'center'));
	$Route->connect('/call_center_call.html', array('controller' => 'calls', 'action' => 'center_call'));
	$Route->connect('/call_center_order.html', array('controller' => 'calls', 'action' => 'center_order'));
	$Route->connect('/call_center_request.html', array('controller' => 'calls', 'action' => 'center_request'));
	$Route->connect('/call_center_message.html', array('controller' => 'calls', 'action' => 'center_message'));
	$Route->connect('/call_center_callback.html', array('controller' => 'calls', 'action' => 'center_callback'));
	$Route->connect('/call_center_email.html', array('controller' => 'calls', 'action' => 'center_email'));
	$Route->connect('/call_center_reverse.html', array('controller' => 'calls', 'action' => 'center_reverse'));
	$Route->connect('/help.html', array('controller' => 'pages', 'action' => 'display', 'help'));
	$Route->connect('/found_pay.html', array('controller' => 'pages', 'action' => 'display', 'found_pay'));
	
        $Route->connect('/websites.html', array('controller' => 'pages', 'action' => 'display', 'websites'));
        $Route->connect('/vikup_razbitih_avto_posle_dtp.html', array('controller' => 'pages', 'action' => 'display', 'vikup_avto'));
		$Route->connect('/articles/:link', array('controller' => 'articles', 'action' => 'view'));
     
        $Route->connect('/zapchasti/:mark/:model/:subcat/', array('controller' => 'products', 'action' => 'zapchasti'), array('mark'=>'[a-zA-Z0-9\_]+', 'model'=>'([a-zA-Z0-9\_\(\)\-]+)', 'subcat'=>'([a-zA-Z0-9\_]+)'));	
	$Route->connect('/zapchasti_bu/:mark/:model/:subcat/', array('controller' => 'products', 'action' => 'zapchasti','1'), array('mark'=>'[a-zA-Z0-9\_]+', 'model'=>'([a-zA-Z0-9\_\(\)\-]+)',  'subcat'=>'([a-zA-Z0-9\_]+)'));
	$Route->connect('/zapchasti/:mark/:model/:subcat/index.html', array('controller' => 'products', 'action' => 'zapchasti'), array('mark'=>'[a-zA-Z0-9\_]+', 'model'=>'([a-zA-Z0-9\_\(\)\-]+)', 'subcat'=>'([a-zA-Z0-9\_]+)'));	
	$Route->connect('/zapchasti_bu/:mark/:model/:subcat/index.html', array('controller' => 'products', 'action' => 'zapchasti','1'), array('mark'=>'[a-zA-Z0-9\_]+', 'model'=>'([a-zA-Z0-9\_\(\)\-]+)',  'subcat'=>'([a-zA-Z0-9\_]+)'));
	$Route->connect('/interest_suggestions.html', array('controller' => 'products', 'action' => 'interest_suggestions'));	
	
	$Route->connect('/cart.html', array('controller' => 'customers', 'action' => 'cart'));	
	$Route->connect('/wishlist.html', array('controller' => 'customers', 'action' => 'wishlist'));
	$Route->connect('/orders.html', array('controller' => 'customers', 'action' => 'orders'));
	$Route->connect('/registr.html', array('controller' => 'customers', 'action' => 'registr'));
	$Route->connect('/login.html', array('controller' => 'customers', 'action' => 'login'));
	$Route->connect('/logout.html', array('controller' => 'customers', 'action' => 'logout'));
	$Route->connect('/where.html', array('controller' => 'customers', 'action' => 'where'));
	$Route->connect('/personal_details.html', array('controller' => 'customers', 'action' => 'personal_details'));
	$Route->connect('/change_password.html', array('controller' => 'customers', 'action' => 'change_password'));
	$Route->connect('/fogotten_password.html', array('controller' => 'customers', 'action' => 'fogotten_password'));
	$Route->connect('/price.html', array('controller' => 'customers', 'action' => 'price'));
	$Route->connect('/edit_price.html', array('controller' => 'customers', 'action' => 'edit_price'));
	$Route->connect('/check_out_fp.html', array('controller' => 'customers', 'action' => 'check_out_fp'));
	$Route->connect('/check_out_signin.html', array('controller' => 'customers', 'action' => 'check_out_signin'));
        $Route->connect('/zapros_zapchastei.html', array('controller' => 'customers', 'action' => 'add_request'));
       $Route->connect('/customer_requests.html', array('controller' => 'customers', 'action' => 'customer_requests'));
       $Route->connect('/answer_request.html', array('controller' => 'customers', 'action' => 'answer_request'));
      $Route->connect('/my_requests.html', array('controller' => 'customers', 'action' => 'my_requests'));
	  
	 $Route->connect('/all_requests.html', array('controller' => 'requests', 'action' => 'all_requests'));
	 $Route->connect('/add_request.html', array('controller' => 'requests', 'action' => 'add_request'));
	 $Route->connect('/check.html', array('controller' => 'requests', 'action' => 'check'));
	 $Route->connect('/select_prod.html', array('controller' => 'products', 'action' => 'select_prod'));
	
	$Route->connect('/check_out.html', array('controller' => 'orders', 'action' => 'check_out'));
$Route->connect('/result_pay.html', array('controller' => 'orders', 'action' => 'result_pay'));	
	$Route->connect('/check_out_confirm.html', array('controller' => 'orders', 'action' => 'check_out_confirm'));
	$Route->connect('/schet.html', array('controller' => 'orders', 'action' => 'schet'));
	$Route->connect('/orders_list.html', array('controller' => 'orders', 'action' => 'orders'));

	$Route->connect('/balance.html', array('controller' => 'movements', 'action' => 'index'));

      
?>