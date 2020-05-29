<?php

class ProductsController extends AppController
{
	var $name = 'Products';
	var $show = 50;
	var $where = 'active=1';
	var $sortBy = 'id';
	var $direction = 'ASC';
	var $uses = array('CountSearch','VisibleSearch','Price','TimeUpdatePrice','Search');
	var $page;
	var $order;
	
	function __construct()
	{
	 	parent::__construct();
	 	
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);
	}
	
	function interest_suggestions()
	{
		$this->layout = 'ajax';
		
		// Array indexes are 0-based, jCarousel positions are 1-based.
		$first = max(0, intval($_GET['first'])-1);
		$last  = max($first + 1, intval($_GET['last'])-1);

		$length = $last - $first + 1;
		
		// ---
		$where = "Product.interest=1";
        $total = $this->Product->findCount($where,2);
		$result = mysql_query("select * from products where interest=1 order by id ASC limit ".$first.",".$length);
		// ---

		header('Content-Type: text/xml; charset=windows-1251');

		echo '<data>';

		// Return total number of images so the callback
		// can set the size of the carousel.
		echo '  <total>' . $total . '</total>';

		while ($row = mysql_fetch_array($result)) {
			$image = '';
			$dir =   $row['price_id'] < 100 ? 'photos' : 'photos_'.$row['price_id'];
			if($row['price_id']>=100  && file_exists("files/products/".$dir."/".$row['articul'].".jpg")) { 
				$image = '/files/products/'.$dir.'/'.$row['articul'].'.jpg';
			} elseif(file_exists("files/products/photos/".$row['articul'].".jpg")) {
				$image = '/files/products/photos/'.$row['articul'].'.jpg';
			}		

			echo '  <product>
						<image>'.$image.'</image>
						<mark_and_model>'.iconv("WINDOWS-1251", "UTF-8", $row['mark_and_model']).'</mark_and_model>
						<name>'.$row['name'].'</name>
						<price>'.$row['price']*(1+intval($this->othAuth->user("skidka"))/100).'</price>
						<id>'.$row['id'].'</id>
						<qty>'.$row['quantity'].'</qty>
			</product>';
		}

		echo '</data>';
	}
	
	function select_prod()
	{
	
	$buy_count=0;
	$find_orders = mysql_query('SELECT id FROM orders WHERE created BETWEEN LAST_DAY(NOW()) + INTERVAL 1 DAY - INTERVAL 1 MONTH AND LAST_DAY(NOW())');
	while($find_order = mysql_fetch_array($find_orders)){
		$true_count = mysql_fetch_row(mysql_query('SELECT id FROM order_products WHERE price_id = 29 AND order_id = '.$find_order[0]));
		if($true_count[0]>0)
			$buy_count++;
	}
	
	$this->set('buy_count',$buy_count);
	$all_count = mysql_fetch_row(mysql_query("SELECT SUM(count) FROM `count_searches`"));
	$this->set('all_count', $all_count[0]);
	
		//$this->Session->del('select_for_prod_edit');	
		$this->layout = "blank";
		$this->set('title', 'Выбор запчасти');		

		if(isset($_GET['search_prod']))
		{
			$search_fields = array();
			$search_fields["mark"] = @$_GET["mark"];
			$search_fields["model"] = @$_GET["model"];
			$search_fields["year"] = @$_GET["year"];
			$search_fields["cat"] = @$_GET["cat"];
			$search_fields["name"] = @$_GET["name"];
			$search_fields["prc1"] = @$_GET["prc1"];
			$search_fields["prc2"] = @$_GET["prc2"];
			$search_fields["tm1"] = @$_GET["tm1"];
			$search_fields["tm2"] = @$_GET["tm2"];	
			$search_fields["sortBy"] = @$_GET["sortBy"];
			$search_fields["exists"] = @$_GET["exists"];	
			
			if(isset($_GET['prod_edit'])) {				
				$this->Session->del('select_for_prod_edit');	
				$this->Session->write('select_for_prod_edit',$search_fields);		
			} else {
				$this->Session->del('select_for_request');	
				$this->Session->write('select_for_request',$search_fields);		
			}
		}
		if(isset($_GET['search_prod_number']))
		{
			$search_fields["number"] = @$_GET["number"];
			
			if(isset($_GET['prod_edit'])) {				
				$this->Session->del('select_for_prod_edit');	
				$this->Session->write('select_for_prod_edit',$search_fields);		
			} else {
				$this->Session->del('select_for_request');	
				$this->Session->write('select_for_request',$search_fields);		
			}
			
			$count_search['CountSearch']['count']=1;
			if($this->othAuth->user('id')=='') $id_user = 0; else $id_user=$this->othAuth->user('id');
		  $olders = $this->CountSearch->findAll('WHERE customer_id='.$id_user);
		  foreach($olders as $older)
			if(!empty($older['CountSearch']['id'])){
				$count_search['CountSearch']['id']=$older['CountSearch']['id'];
				$check_date=explode('-',$older['CountSearch']['date']);
				if($check_date[1]==date('m'))
				$count_search['CountSearch']['count']=++$older['CountSearch']['count'];
			}
		  $count_search['CountSearch']['customer_id']=$this->othAuth->user('id');
		  $count_search['CountSearch']['date']=date('Y-m-d H:i:s');
		  $this->CountSearch->save($count_search);
		  
		  $buy_count=0;
		  $find_orders = mysql_query('SELECT id FROM orders WHERE created BETWEEN LAST_DAY(NOW()) + INTERVAL 1 DAY - INTERVAL 1 MONTH AND LAST_DAY(NOW())');
		  while($find_order = mysql_fetch_array($find_orders)){
			$true_count = mysql_fetch_row(mysql_query('SELECT id FROM order_products WHERE price_id = 29 AND order_id = '.$find_order[0]));
			if($true_count[0]>0)
				$buy_count++;
		  }
		
		  $all_count = mysql_fetch_row(mysql_query("SELECT SUM(count) FROM count_searches"));
		  $visible_status['VisibleSearch']['id']=3;
		  if(round($all_count[0]*0.197)>$buy_count)
			$visible_status['VisibleSearch']['status']=0;
			else $visible_status['VisibleSearch']['status']=2;
			
		  $this->VisibleSearch->save($visible_status);
		}
		
		if($this->Session->valid('select_for_prod_edit') && isset($_GET['prod_edit'])) 
			$sess_search = $this->Session->read('select_for_prod_edit');			
		if($this->Session->valid('select_for_request') && !isset($_GET['prod_edit']))
			$sess_search = $this->Session->read('select_for_request');
			
		$this->set('sess_search', $sess_search);	
		
		if(!empty($sess_search) && is_array($sess_search))
		{
			$this->where = "Product.active=1 ";
        
			if(!empty($sess_search["cat"])) {
				 if($sess_search["cat"] == 'new')
						  $this->where .= " AND Product.bu = 0 ";
				 if($sess_search["cat"] == 'bu')
						  $this->where .= " AND Product.bu = 1 ";
			}		
		
			if(!empty($sess_search["mark"])) {

			  $mark = $this->Mark->read(null,$sess_search["mark"]);
			  $this->where .= " AND (Product.mark_id = '".Sanitize::paranoid($sess_search["mark"])."' OR
			  Product.mark_and_model LIKE '%".$mark["Mark"]["name"]."%')"; 	
			}
			
			if(!empty($sess_search["model"])) {	
			
			  $model = $this->Model->read(null,$sess_search["model"]);
			  $this->where .= " AND (Product.model_id = '".Sanitize::paranoid($sess_search["model"])."'   OR
			  Product.mark_and_model LIKE '%".$model["Model"]["name"]."%' )"; 
			}
			
			if(!empty($sess_search["year"])) {				
			  $this->where .= " AND Product.year1 <= '".Sanitize::paranoid($sess_search["year"])."' 
			  AND Product.year2 >= '".Sanitize::paranoid($sess_search["year"])."' "; 	
			}

			if(!empty($sess_search["name"])) {	
			
			  $this->where .= " AND Product.name LIKE '%".Sanitize::paranoid($sess_search["name"],$this->_alphabet())."%' "; 

			}
			if(!empty($sess_search["number"])) {	
			
			  $this->where .= " AND Product.number LIKE '".Sanitize::paranoid($sess_search["number"],$this->_alphabet())."' ";
			  $res = mysql_query("SELECT * FROM nproducts  WHERE manuf_number = '".$sess_search["number"]."'");
			  $this->set('nproducts', $res);

			}
			if(!empty($sess_search["prc1"])) {				
			  $this->where .= " AND Product.price >= '".Sanitize::paranoid($sess_search["prc1"])."' "; 	
			}
			
			if(!empty($sess_search["prc2"])) {				
			  $this->where .= " AND Product.price <= '".Sanitize::paranoid($sess_search["prc2"])."' "; 	
			}
			
			if(!empty($sess_search["tm1"])) {				
			  $this->where .= " AND Product.days_shipping >= '".Sanitize::paranoid($sess_search["tm1"])."' "; 	
			}
			if(!empty($sess_search["tm2"])) {				
			  $this->where .= " AND Product.days_shipping <= '".Sanitize::paranoid($sess_search["tm2"])."' "; 	
			}
			
			if(!empty($sess_search["exists"])) {				
			  $this->where .= " AND Product.quantity >0 "; 	
			}
			
			if(!empty($sess_search["sortBy"])) {
			  $this->sortBy = Sanitize::paranoid($sess_search["sortBy"],array("_"));
			  $this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction).', '.$this->modelClass.'.id ASC';
			}
			$data = $this->Product->findAll($this->where, null, $this->order, $this->show, $this->page, 2);
						
			$this->set('products', $data);
			
			$paging['style'] = 'html'; 
			$paging['link'] = '/'.@$this->params['url']['url'].'?'.(isset($_GET['prod_edit'])?'prod_edit&':'').'&i='.$_GET['i'].'&j='.$_GET['j'].'&page=';		
			$paging['count'] = $this->Product->findCount($this->where,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			$paging['show'] = array('10','20','30');
			$this->set('paging',$paging);	
		}
		
	}
    
	function zapchasti($bu=0) {

	$update_price = $this->TimeUpdatePrice->read(null,1);
		$date = new DateTime();
		$date->modify('-'.$update_price['TimeUpdatePrice']['days'].' day');
		$thisdate=$date->format('Y-m-d H:i:s');
		$update_price_check = explode(',',$update_price['TimeUpdatePrice']['prices']);
		$price_check_true[0] = 0;
		
		$price_check = $this->Price->findAll();
		foreach($price_check as $row){
			$datetime1 = new DateTime($row['Price']['uploaded']);
			$datetime2 = new DateTime($thisdate);
			$interval =	date_diff($datetime1,$datetime2);
			
			for($i=0; $i<count($update_price_check); $i++)
				if($update_price_check[$i] == $row['Price']['id'] && (int)$interval->format('%R%a') > 0){
					
					$n++;
					$price_check_true[$n] = $row['Price']['id'];
					
				}
		}
		$this->set('price_check_true', $price_check_true);

		$this->Product->query("SET SQL_MAX_JOIN_SIZE  = 4294967295");

			
			if(!empty($this->params['model']))	{
		        
				$mark = $this->Mark->findByName($this->params['mark']);				
                $modif = mysql_fetch_array(mysql_query("select * from model_modifications where name = '".str_replace('_',' ',$this->params['model'])."'"));
				$model = $this->Model->find("Model.id='".$modif['model_id']."'");
				
		        $result = mysql_query("SELECT count(DISTINCT Product.id) as cnt, 
categories.id as category_id,
categories.name as category_name,
subcategories.id, 
subcategories.description, 
subcategories.slug FROM subcategories
INNER JOIN categories ON  categories.id = subcategories.category_id
INNER JOIN products_subcategories ON products_subcategories.subcategory_id=subcategories.id 
INNER JOIN products as Product ON Product.id = products_subcategories.product_id 
WHERE EXISTS (SELECT * FROM products_models WHERE product_id=Product.id AND model_id = '".$model['Model']['id']."')
 AND Product.`year1` <= '".$modif['year_max']."' AND Product.`year1` >=  '".$modif['year_min']."' 
GROUP BY categories.id, subcategories.id 
ORDER BY categories.name, subcategories.description;");
$cats = array();
while($row = mysql_fetch_array($result))
{
      $cats[$row['category_id']]['name'] = $row['category_name'];
      $cats[$row['category_id']]['subcats'][] = array('id' => $row['id'], 'description' => $row['description'], 'slug' => $row['slug'], 'product_cnt' => $row['cnt']);
}

						$this->where = "Product.active=1 ";
			        $this->where .= " AND EXISTS (SELECT * FROM products_models WHERE product_id=Product.id AND model_id = '".$model['Model']['id']."' ) ";
			        $this->where .= " AND Product.year1 <= '".$modif['year_max']."' 
			          AND Product.year1 >= '".$modif['year_min']."' ";
                       
												        
	        $show_list = false;
                if($this->othAuth->group("id") == 5)
                          $show_list = true;

		        if(!empty($this->params['subcat'])) {
		        
			        $subcat = $this->Subcategory->findBySlug($this->params['subcat']);			        
			        $this->where .= " AND EXISTS (SELECT * FROM products_subcategories WHERE product_id=Product.id AND subcategory_id = '".$subcat['Subcategory']['id']."' ) ";
			       $show_list = true;
				  
					//$data = $this->Product->findAll($this->where, null, 'Product.quantity DESC, Product.days_shipping, Product.id', $this->show, $this->page, 2);			         
					$data = $this->Product->findAll($this->where." AND Product.bu=0 ", null, 'Product.quantity DESC, Price.order DESC, Product.id');
			        $this->set('products', $data);	
					$data = $this->Product->findAll($this->where." AND Product.bu=1 ", null, 'Product.quantity DESC, Product.days_shipping, Product.id');
			        $this->set('products_bu', $data);	
		        }


                      

if(empty($this->params['subcat']))
{
$this->set('title','Запчасти '.($bu ? 'БУ ':'').' '.$mark['Mark']['name_rus'].' '.$modif['name'].' ('.$mark['Mark']['name'].' '.$modif['name'].'). Интернет-магазин. Каталог '.($bu ? 'БУ ':'').'автозапчастей.');
$this->set('meta_description', 'Запчасти '.($bu ? 'БУ ':'').'  '.$mark['Mark']['name_rus'].' '.$modif['name'].' ('.$mark['Mark']['name'].' '.$modif['name'].') в Ростове-на-Дону.'); 
$this->set('meta_keywords', 'запчасти '.$mark['Mark']['name_rus'].' '.$modif['name'].', кузовные запчасти '.$mark['Mark']['name_rus'].' '.$modif['name'].', фары '.$mark['Mark']['name_rus'].' '.$modif['name'].', радиатор '.$mark['Mark']['name_rus'].' '.$modif['name'].', запчасти ходовая '.$mark['Mark']['name_rus'].' '.$modif['name'].', двигатель '.$mark['Mark']['name_rus'].' '.$modif['name']); 
}
else
{

$this->set('title', $subcat['Subcategory']['description'].' '.($bu ? 'БУ ':'').' '.$mark['Mark']['name_rus'].' '.$modif['name'].' ('.$mark['Mark']['name'].' '.$modif['name'].').');
$this->set('meta_description', 'Запчасти '.($bu ? 'БУ ':'').'  '.$mark['Mark']['name_rus'].' '.$modif['name'].' ('.$mark['Mark']['name'].' '.$modif['name'].') в Ростове-на-Дону. Кузовные запчасти  '.$mark['Mark']['name_rus'].' '.$modif['name'].', фары, радиаторы охлаждения, передняя подвеска, стекла на авто, рычаг подвески.');
$this->set('meta_keywords', 'запчасти '.$mark['Mark']['name_rus'].' '.$modif['name'].', кузовные запчасти '.$mark['Mark']['name_rus'].' '.$modif['name'].', фары '.$mark['Mark']['name_rus'].' '.$modif['name'].', радиатор '.$mark['Mark']['name_rus'].' '.$modif['name'].', запчасти ходовая '.$mark['Mark']['name_rus'].' '.$modif['name'].', двигатель '.$mark['Mark']['name_rus'].' '.$modif['name'].', противотуманные фары '.$mark['Mark']['name_rus'].' '.$modif['name'].'. ');

$this->set('h1', $subcat['Subcategory']['description'].' '.($bu ? 'БУ ':'').' '.$mark['Mark']['name_rus'].' '.$modif['name'].' ('.$mark['Mark']['name'].' '.$modif['name'].').');
$this->set('intro', 'В нашем каталоге не все запчасти доступны в on-line режиме. Для заказа свяжитесь с нами или оставьте <a href="/zapros_zapchastei.html">Запрос запчастей</a>.');
}
                        

		        $this->set('mark', $mark);
		        $this->set('model', $model);
				$this->set('modif', $modif);
		        $this->set('cats', $cats);
		      $this->set('show_list', $show_list);

				$this->render("parts");
										   				
			} 
			elseif(!empty($this->params['mark']) )	{
                                $mark = $this->Mark->findByName($this->params['mark']);
		                 $this->set('mark', $mark);
						 
$result = mysql_query("SELECT COUNT(DISTINCT products.id) as cnt, 
models.name as model,
model_modifications.name as modif,
categories.id as category_id,
categories.name as category_name,
subcategories.id, 
subcategories.description, 
subcategories.slug
FROM products 
INNER JOIN products_models ON products_models.product_id = products.id
INNER JOIN models ON models.id = products_models.model_id
INNER JOIN model_modifications ON products_models.model_id = model_modifications.model_id AND products.year1 >= model_modifications.year_min AND products.year1 <= model_modifications.year_max
INNER JOIN products_subcategories ON products_subcategories.product_id=products.id
INNER JOIN subcategories ON  subcategories.id = products_subcategories.subcategory_id
INNER JOIN categories ON  categories.id = subcategories.category_id
WHERE models.mark_id = ".Sanitize::paranoid($mark['Mark']['id'])."
GROUP BY models.name, model_modifications.name, categories.id, subcategories.id
ORDER BY models.name, model_modifications.name, categories.name, subcategories.description");

	$data = array();
	while($row = mysql_fetch_array($result))
	{
		  $data[$row['model']][$row['modif']][$row['category_id']]['name'] = $row['category_name'];
		  $data[$row['model']][$row['modif']][$row['category_id']]['subcats'][] = array('id' => $row['id'], 'description' => $row['description'], 'slug' => $row['slug'], 'product_cnt' => $row['cnt']);
	}
	$this->set('data', $data);


$this->set('title','Запчасти '.$mark['Mark']['name_rus'].' ('.$mark['Mark']['name'].'). Интернет-магазин DETALLINE.RU (Ростов-на-Дону)');
$this->set('meta_description', "В нашем интернет-магазине Вы можете купить запчасти ".$mark['Mark']['name_rus']." по доступным ценам"); 
$this->set('meta_keywords', "запчасти ".$mark['Mark']['name'].", купить запчасти ".$mark['Mark']['name_rus']); 

	
              
                                 if($bu)
                                        $this->render("models_bu");
                                 else
		                        $this->render("models");
			} else {
			
$this->set('title','Каталог запчастей. Интернет-магазин DETALLINE.RU (Ростов-на-Дону)');
$this->set('meta_description', 'Каталог запчастей интернет-магазина DETALLINE.RU (Ростов-на-Дону)'); 
$this->set('meta_keywords', 'электронный каталог запчастей, онлайн каталог запчастей, каталог оригинальных запчастей, каталог запчастей автомобилей'); 

		                 $this->set('data', $this->Mark->findAll("active=1",null,"Mark.name ASC"));
				
				$this->render("marks");
				
			}
			
			
	}

	
	function search() {
		
	
		$this->where = "0=1 ";
		
		$title = 'Поиск запчастей';
		if($this->Session->valid('search'))
			$sess_search = $this->Session->read('search');
		
				
		if(!empty($sess_search) && is_array($sess_search))
		{
		  if($sess_search["tp"]=="numbers") {
		  $count_search['CountSearch']['count']=1;
		  if($this->othAuth->user('id')=='') $id_user = 0; else $id_user=$this->othAuth->user('id');
		  $olders = $this->CountSearch->findAll('WHERE customer_id='.$id_user);
		  foreach($olders as $older)
			if(!empty($older['CountSearch']['id'])){
				$count_search['CountSearch']['id']=$older['CountSearch']['id'];
				$check_date=explode('-',$older['CountSearch']['date']);
				if($check_date[1]==date('m'))
				$count_search['CountSearch']['count']=++$older['CountSearch']['count'];
			}
		  $count_search['CountSearch']['customer_id']=$this->othAuth->user('id');
		  $count_search['CountSearch']['date']=date('Y-m-d H:i:s');
		  $this->CountSearch->save($count_search);
		  
		  $buy_count=0;
		  $find_orders = mysql_query('SELECT id FROM orders WHERE created BETWEEN LAST_DAY(NOW()) + INTERVAL 1 DAY - INTERVAL 1 MONTH AND LAST_DAY(NOW())');
		  while($find_order = mysql_fetch_array($find_orders)){
			$true_count = mysql_fetch_row(mysql_query('SELECT id FROM order_products WHERE price_id = 29 AND order_id = '.$find_order[0]));
			if($true_count[0]>0)
				$buy_count++;
		  }
		
		  $all_count = mysql_fetch_row(mysql_query("SELECT SUM(count) FROM count_searches"));
		  $visible_status['VisibleSearch']['id']=3;
		  if(round($all_count[0]*0.197)>$buy_count)
			$visible_status['VisibleSearch']['status']=0;
		  /*else 
			if(round($all_count[0]*0.19)>$buy_count)
				$visible_status['VisibleSearch']['status']=1;*/
			else $visible_status['VisibleSearch']['status']=2;
			
		  $this->VisibleSearch->save($visible_status);
		  
		  
		  
			$title = 'Поиск запчастей по номеру';
			 $this->where = "Product.active=1 ";
			
			if(!empty($sess_search["nums"])) {
			  $nums = preg_replace('/[^0-9a-z]+/i','',$sess_search["nums"]);        
			  $this->where .= " AND (Product.number = '".$nums."' OR Product.articul = '".$nums."') "; 

			  $this->set('num', $nums);
			  
			  $search_rows = $this->Search->findAll("WHERE customer_id = ".$this->othAuth->user('id')." AND number = ".$nums);
			  foreach($search_rows as $search_row) $search_true = true;
			  if($search_true !=true && $this->othAuth->user('id')>0){
				  $search_history['Search']['customer_id'] = $this->othAuth->user('id');
				  $search_history['Search']['number'] = $nums;
				  $search_history['Search']['date'] = date('Y-m-d');
				  $this->Search->save($search_history);
			  }
			  
			  			   
			} else {
			  $this->where .= " AND 1=0";
			} 			
		  }
		  
		  if($sess_search["tp"]=="spareparts") {
			 $this->where = "Product.active=1 ";
			
			if(!empty($sess_search["cat"])) {
				 if($sess_search["cat"] == 'new')
						  $this->where .= " AND Product.bu = 0 ";
				 if($sess_search["cat"] == 'bu')
						  $this->where .= " AND Product.bu = 1 ";
			}			
			
			if(!empty($sess_search["mark"])) {

			  $mark = $this->Mark->read(null,$sess_search["mark"]);
			  $this->where .= " AND (Product.mark_id = '".Sanitize::paranoid($sess_search["mark"])."' OR
			  Product.mark_and_model LIKE '%".$mark["Mark"]["name"]."%')"; 	
			  
			  $title = 'Запчасти на '.$mark["Mark"]["name_rus"];
					 $title2 = 'Запчасти на '. $mark["Mark"]["name"];
			}
			if(!empty($sess_search["model"])) {	
			
			  $model = $this->Model->read(null,$sess_search["model"]);
			  $this->where .= " AND (Product.model_id = '".Sanitize::paranoid($sess_search["model"])."'   OR
			  Product.mark_and_model LIKE '%".$model["Model"]["name"]."%' )"; 
			  $title .= ' '.$model["Model"]["name_rus"];
					   $title2 .= ' '.$model["Model"]["name"];
			}
			
			if(!empty($sess_search["year"])) {				
			  $this->where .= " AND Product.year1 <= '".Sanitize::paranoid($sess_search["year"])."' 
			  AND Product.year2 >= '".Sanitize::paranoid($sess_search["year"])."' "; 	
			  $title .= ' '.$sess_search["year"];
					  $title2 .= ' '.$sess_search["year"];
			}
			$title .= ' ('.$title2.')';
			if(!empty($sess_search["name"])) {	
			
			  $this->where .= " AND Product.name LIKE '%".Sanitize::paranoid($sess_search["name"],$this->_alphabet())."%' "; 
				$title .= ', '.$sess_search["name"];
			}
			if(!empty($sess_search["prc1"])) {				
			  $this->where .= " AND Product.price >= '".Sanitize::paranoid($sess_search["prc1"])."' "; 	
			}
			
			if(!empty($sess_search["prc2"])) {				
			  $this->where .= " AND Product.price <= '".Sanitize::paranoid($sess_search["prc2"])."' "; 	
			}
			
			if(!empty($sess_search["tm1"])) {				
			  $this->where .= " AND Product.days_shipping >= '".Sanitize::paranoid($sess_search["tm1"])."' "; 	
			}
			if(!empty($sess_search["tm2"])) {				
			  $this->where .= " AND Product.days_shipping <= '".Sanitize::paranoid($sess_search["tm2"])."' "; 	
			}
			
			
			if(!empty($sess_search["exists"])) {				
				$this->where .= " AND Product.quantity >0 "; 	
			  }
				
			   if(!empty($sess_search["sortBy"])) {
				  $this->sortBy = Sanitize::paranoid($sess_search["sortBy"],array("_"));
				  $this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction).', '.$this->modelClass.'.id ASC';
			   }
			
			$data = $this->Product->findAll($this->where, null, $this->order, $this->show, $this->page, 2);
			$this->set('products', $data);	
			$this->set('mark', @$mark);	
			$this->set('model', @$model);	
			$this->set('category', @$category);	
			
			$paging['style'] = 'html'; 
			$paging['link'] = '/'.@$this->params['url']['url'].'?page=';		
			$paging['count'] = $this->Product->findCount($this->where,2);
			$paging['page'] = $this->page;
			$paging['limit'] = $this->show;
			$paging['show'] = array('10','20','30');
			$this->set('paging',$paging);
		  }
		  
		  
		}

		
		$this->set('tp', $sess_search["tp"]);		
		$this->set('title',$title);
        $this->set('h1',$title);
		
		$update_price = $this->TimeUpdatePrice->read(null,1);
		$date = new DateTime();
		$date->modify('-'.$update_price['TimeUpdatePrice']['days'].' day');
		$thisdate=$date->format('Y-m-d H:i:s');
		$update_price_check = explode(',',$update_price['TimeUpdatePrice']['prices']);
		$price_check_true[0] = 0;
		
		$price_check = $this->Price->findAll();
		foreach($price_check as $row){
			$datetime1 = new DateTime($row['Price']['uploaded']);
			$datetime2 = new DateTime($thisdate);
			$interval =	date_diff($datetime1,$datetime2);
			
			for($i=0; $i<count($update_price_check); $i++)
				if($update_price_check[$i] == $row['Price']['id'] && (int)$interval->format('%R%a') > 0){
					
					$n++;
					$price_check_true[$n] = $row['Price']['id'];
					
				}
		}
		$this->set('price_check_true', $price_check_true);

		
	}
	
}
?>