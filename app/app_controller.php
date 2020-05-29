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
 uses('sanitize');
 
class AppController extends Controller {

	var $helpers = array("Html", "Pagination", "Ajax");
	var $components = array("Sendmail","RequestHandler","othAuth","Password","Session","Tuning","Semantic");
	var $uses = array("Customer","Product","Order","Mark","Model","Category","Article","Subcategory","RequestAnswer","Advert","Poll","Search","City","Ip","Range","Responsible","Search");
	var $othAuthRestrictions = "*";
	var $logged = false;

	
	function beforeFilter() {		
		$auth_conf = array('auto_redirect' => false,
		'strict_gid_check' => false
		);
		$this->othAuth->controller = &$this;
		$this->othAuth->init($auth_conf);
		$this->logged = $this->othAuth->check();
		
		if($_SESSION['where']==''){
			$ipcheck = $this->Ip->findByIp($_SERVER["REMOTE_ADDR"]);
			$_SESSION['where'] = $ipcheck['Ip']['city_id'];
			if($_SESSSION['where']!=''){
					mysql_query("DELETE FROM ips WHERE ip='".$_SERVER["REMOTE_ADDR"]."'");
					$ip['Ip']['ip']=$_SERVER["REMOTE_ADDR"];
					$ip['Ip']['region_id']=$this->othAuth->user('region_id');
					$ip['Ip']['city_id']=$this->othAuth->user('city');
					$this->Ip->save($ip);
					$_SESSION['where']=$this->othAuth->user('city');
			}
		}
		
		$ipcheck = $this->Ip->findByIp($_SERVER["REMOTE_ADDR"]);
		$range= $this->Range->findByRegion_id($ipcheck['Ip']['region_id']);
		$_SESSION['range'] = $range['Range']['range'];
		
		$city = $this->City->read(null,$_SESSION['where']);
		$this->set('search_num',$this->Search->findAll('Where customer_id ='.$this->othAuth->user('id').' ORDER BY id DESC LIMIT 0,30'));
		$this->set('where', $city);
		$this->set('responsibles',$this->Responsible->findAll());
		$this->set('categories', $this->Category->findAll("Category.id IN (1,2,3,4)"));
		$this->set('marks', $this->Mark->findAll("Mark.active=1",null, "name ASC"));
		$this->set('searches', $this->Search->findAll("Search.custumer_id=".$this->othAuth->user("id")));
                $this->set('mrks_prod_cnt', $mrks_prod_cnt);

        //$this->set('our_news', $this->Article->findAll("art_category_id=2 AND active=1", null, "created DESC"));
        //$this->set('other_news', $this->Article->findAll("art_category_id=3 AND active=1", null, "created DESC"));
        $this->set('articles', $this->Article->findAll("art_category_id=1 AND active=1", null, "created DESC"));		
		
		if(empty($_SESSION['where'])) $yourcity='(?)'; else $yourcity = '('.$city['City']['name'].')';
		
		if(isset($_GET["tp"]) && $_GET["tp"]=="spareparts") {
		
            $search_fields = array();
			$search_fields["tp"] = "spareparts";
			$search_fields["cat"] = @$_GET["cat"];
			$search_fields["mark"] = @$_GET["mark"];
			$search_fields["model"] = @$_GET["model"];
			$search_fields["year"] = @$_GET["year"];
			$search_fields["name"] = @$_GET["name"];
			$search_fields["prc1"] = @$_GET["prc1"];
			$search_fields["prc2"] = @$_GET["prc2"];
			$search_fields["tm1"] = @$_GET["tm1"];
			$search_fields["tm2"] = @$_GET["tm2"];	
			$search_fields["sortBy"] = @$_GET["sortBy"];
			$search_fields["exists"] = @$_GET["exists"];	
			
			$this->Session->del('search');	
			$this->Session->write('search',$search_fields);		
		}
		if(isset($_GET["tp"]) && $_GET["tp"]=="numbers") {
		
			$search_fields = array();
			$search_fields["tp"] = "numbers";
			$search_fields["nums"] = $_GET["nums"];	
			$search_fields["sortBy"] = @$_GET["sortBy"];
			$search_fields["exists"] = @$_GET["exists"];	
			
			$this->Session->del('search');	
			$this->Session->write('search',$search_fields);		
		}
		
		$this->set('sess_search', $this->Session->read('search'));

               $this->set('products_show_window', $this->Product->findProductsWithPictures(4));
                

              $top_advert = $this->Advert->findAll("Advert.advert_type_id=1 AND Advert.active=1 AND Advert.site_id=1");
		$this->set('top_advert', $top_advert[0]);

		$left_advert = $this->Advert->findAll("Advert.advert_type_id=3 AND Advert.active=1 AND Advert.site_id=1");
		$this->set('left_advert', $left_advert);
		
		//$this->Session->del('has_voted');	
		if(isset($_POST['vote']))
		{
			if(isset($_POST['poll_variant'])) {
				if($_POST['poll_variant']=='poll_var_other') {
					mysql_query("insert into poll_choices(poll_id, other, created) values ('".$_POST['poll_id']."','".$_POST['poll_other_txt']."', now())");
				} else {
					mysql_query("insert into poll_choices(poll_id, poll_variant_id, created) values ('".$_POST['poll_id']."','".str_replace("poll_var_","",$_POST['poll_variant'])."', now())");
				}				
			} else {
				foreach($_POST as $key=>$val)
				{
					if(strpos($key,'poll_var_')!==false) {
						if($key == 'poll_var_other')
							mysql_query("insert into poll_choices(poll_id, other, created) values ('".$_POST['poll_id']."','".$_POST['poll_other_txt']."', now())");
						else
							mysql_query("insert into poll_choices(poll_id, poll_variant_id, created) values ('".$_POST['poll_id']."','".str_replace("poll_var_","",$key)."', now())");
					}
				}
			}
			$this->Session->write('has_voted',1);	
		}
		$poll = $this->Poll->findAll("Poll.status=1");
		$this->set('poll', $poll[0]);
		$this->set('has_voted', $this->Session->read('has_voted'));

		switch($this->params['url']['url'])
		{
		    case 'forum.html':
		        $this->set('title', 'Форум по автозапчастям и ремонту иномарок.'); break;
		    case 'sto_online.html':
		        $this->set('title', 'Запись на СТО on-line'); break;
			case 'call.html':
		        $this->set('title', 'Чат DETALLINE.RU "Деталь Лайн"'); break;		    
            case 'zapchasti_na_zakaz.html':
		        $this->set('title', 'Запчасти на заказ. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); 
				$this->set('meta_description', 'В нашем интернет-магазине Вы можете купить запчасти на заказ для любой марки авто'); 
				$this->set('meta_keywords', 'запчасти под заказ, запчасти для иномарок на заказ, заказ запчастей через интернет'); 
				break;				
			case 'kak_oformit_zakaz.html':
				$this->set('title', 'Как оформить заказ. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'oplata.html':
				$this->set('title', 'Оплата товара. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'dostavka.html':
				$this->set('title', 'Доставка запчастей. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'vozvrat.html':
				$this->set('title', 'Возврат автозапчастей клиентом. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'kak_zagruzit_price.html':
				$this->set('title', 'Как загрузить прайс-лист. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'vacancy.html':
				$this->set('title', 'Вакансии. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;				
			case 'rasprodaja.html':
				$this->set('title', 'Распродажа автозапчастей. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'skidki.html':
				$this->set('title', 'Скидки на покупку автозапчастей. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'reklama.html':
				$this->set('title', 'Реклама на сайте. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); break;
			case 'websites.html':
				$this->set('title', 'Автомобильные сайты'); break;
			case 'sto.html':
		        $this->set('title', 'СТО в Ростове-на-Дону'); break;
		    case 'strahovanie.html':
		        $this->set('title', 'Автострахование в Ростове-на-Дону. Каско, Осаго'); break;
			case 'message.html':
		        $this->set('title', 'Обратная связь. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); 
				break;
			case 'contacts.html':
		        $this->set('title', 'Контакты. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); 
				break;
			case 'shops.html':
		        $this->set('title', 'Адреса магазинов. Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); 	
				break;
           case 'vikup_razbitih_avto_posle_dtp.html': 
		        $this->set('title', 'Выкуп авто (разбитых, неисправных, сгоревших, аварийных). Интернет-магазин DETALLINE.RU "Деталь Лайн" '.$yourcity); 
				$this->set('meta_description', 'Наша компания предлагает срочный выкуп авто на выгодных условиях'); 
				$this->set('meta_keywords', 'срочный выкуп авто, выкуп автомобилей, выкуп битых авто, выкуп аварийных авто, выкуп автомобилей дорого'); 
				break;			
		    default: 				
				$this->set('title', 'Запчасти для иномарок. Интернет-магазин автозапчастей DETALLINE.RU "Деталь Лайн" '.$yourcity); 
$this->set('meta_description', 'Специализированный интернет-магазин автозапчастей detalline.ru "Деталь Лайн" реализует оптом и в розницу различные запчасти для иномарок'); 
$this->set('meta_keywords', 'запчасти, купить запчасти, запчасти для иномарок, каталог запчастей, интернет магазин автозапчастей, продажа запчастей'); 
break;
		}
	}
	
	function beforeRender() {		
		header('Content-Type: text/html; charset=windows-1251');
	}
	
	function _alphabet() {
		return array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф',
			'х','ц','ч','ш','щ','ь','ы','ъ','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М',
			'Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ь','Ы','Ъ','Э','Ю','Я',' ');
	}

}
?>