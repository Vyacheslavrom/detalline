<?php
uses('sanitize');

class ProductsController extends AppController
{
	var $name = 'Products';
	var $helpers = array("Html", "Pagination");
	var $components = array('File');
	var $show;
	var $sortBy;
	var $direction;
	var $page;
	var $order;
        var $forbidden_prices = array(104);
		var $uses = array('Product','Manufacturer','Storage');


	function __construct() {
		$this->show = empty($_GET['show'])? '50': Sanitize::paranoid($_GET['show']);
		$this->sortBy = empty($_GET['sort'])? 'id': Sanitize::paranoid($_GET['sort']);
		$this->direction = empty($_GET['direction'])? 'ASC': Sanitize::paranoid($_GET['direction']);
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}

	function delete_picture($id=null) {
	 	$data = $this->Product->read(null, $id); 
                 $dir =  $data['Product']['price_id'] < 100 ? 'photos' : 'photos_'.$data['Product']['price_id'];
                $this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.$dir.DS.$data['Product']['articul'].'.jpg');
		$this->redirect('products/edit/'.$id);		
	}
	
	function edit($id = null) {	
		if (!empty($this->data)) {
		
			$errors = false;
			
			if(!$this->Product->validates($this->data)) {
				$errors = true;
			}
			if($_POST['mm']==2 && empty($this->data['Product']['mark_and_model'])) 	   {
					$this->Product->invalidate('mark_and_model');
					$errors = true;
			}  
                        
                        $this->data['Product']['model_id'] = $_POST['model'];
			if($_POST['mm']==1 && (empty($this->data['Product']['mark_id']) || empty($this->data['Product']['model_id']) )) 	   {
					$this->Product->invalidate('mark_and_model2');
					$errors = true;
			} 

			if(is_uploaded_file($this->data['Product']['pic_file']['tmp_name'])) {
	
        $this->File->allowed_extentions = array('jpg');
				if(empty($this->data['Product']['articul'])) {
          $this->set("error", "Для загрузки фото необходимо ввести артикул запчасти");
					$errors = true;
				} elseif (!$this->File->validate($this->data['Product']['pic_file'])) {
					$this->set("error", $this->File->error);
					$errors = true;
				} 
			}
			
			if(!$errors) {
				
				if($_POST['mm']==1) {
					$mark = $this->Product->Mark->read("Mark.name", $this->data['Product']['mark_id']);
					$model = $this->Product->Model->read("Model.name", $this->data['Product']['model_id']);
					$this->data['Product']['mark_and_model'] = $mark['Mark']['name'].' '.$model['Model']['name'];
				}
				if($_POST['mm']==2) {
					$this->data['Product']['mark_id'] = null;
					$this->data['Product']['model_id'] = null;
				}
				
				if ($this->Product->save($this->data)) {
				
				
				
          if(is_uploaded_file($this->data['Product']['pic_file']['tmp_name'])) {
	
                 $dir =  $this->data['Product']['price_id'] < 100 ? 'photos' : 'photos_'.$this->data['Product']['price_id'];

                $path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.$dir.DS;	

if (!is_dir($path))
  {
    mkdir($path, 0777);
  }
            $filename = $this->data['Product']['articul'];
            
            $this->File->allowed_extentions = array('jpg');
            if (!$this->File->upload($this->data['Product']['pic_file'],$filename,$path)) {
              $this->set("error", $this->File->error);
              $errors = true;
            } 
          }
				
				$i=0;
				$storage_id = $this->Storage->findCount();
				while($i<$this->params['data']['Product']['quantity']){
					$i++;
					$storage_id++;
					$storage['Storage']['price_id'] = $this->params['data']['Product']['price_id'];
					$storage['Storage']['name'] = $this->params['data']['Product']['name'];
					$storage['Storage']['articul'] = $this->params['data']['Product']['articul'];
					$storage['Storage']['manuf_number'] = '';
					$storage['Storage']['storage'] = 1;
					$storage['Storage']['id'] = $storage_id;
					$this->Storage->save($storage);
				}
				
					$this->Session->setFlash('Запись сохранена','flash');
					$this->redirect("products");
				}
			}
			else {
				$this->Session->setFlash('<font color=blue>При сохранении обнаружены ошибки</font>','flash');
			}
		} else {
				$this->params['data'] = $this->Product->read(null, $id); 
		}
		if(!$this->params['data']) {
			$this->params['data']['Product']['active'] = true;
			$this->params['data']['Product']['price_id'] = 104;//40609
		}else{
			if(!empty($_POST['storage_name'])){
				$storages = $this->Storage->findAll('WHERE price_id = '.$_POST['storage_price_id'].' AND name LIKE "'.$_POST['storage_name'].'" AND articul LIKE "'.$_POST['storage_articul'].'"');
				foreach($storages as $storage){
					$storage['Storage']['price_id'] = $this->params['data']['Product']['price_id'];
					$storage['Storage']['name'] = $this->params['data']['Product']['name'];
					$storage['Storage']['articul'] = $this->params['data']['Product']['articul'];
					$this->Storage->save($storage);
				}
			}
		}
		
		$this->set('categories', $this->Product->Category->findAll());
		$this->set('marks', $this->Product->Mark->findAll(null,null,"Mark.name ASC"));
		$this->set('models', $this->Product->Model->findAll());	
		$this->set('manufacturers', $this->Manufacturer->findAll("active=1"));	

$where_for_price = "Price.id>=100";	
if(!in_array('1', $this->othAuth->permission(id)))
         $where_for_price = 'Price.id>=100 AND Price.id NOT IN ('.implode(',',$this->forbidden_prices).')';

		$this->set('prices', $this->Product->Price->findAll($where_for_price));			
		$this->set('title', 'Редактирование информации о запчасти');
	}
	
	
	
	function active($id=null, $activity=1) {
	 	$ids = array();
	 	
	 	if($id && $id!=='all') { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
		 	$data = $this->Product->read(null, $id); 		
			$data['Product']['active'] = $activity;	      	 
			$this->Product->save($data);		
		}
	    $this->redirect('products');		
	}
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		
		foreach($ids as $id) {		 
		 	$data = $this->Product->read(null, $id); 

			// удаляем основное изображение
            if($data['Product']['price_id'] >= 100)	 	
		        $this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'photos_'.$data['Product']['price_id'].DS.$data['Product']['articul'].'.jpg');
		 	//$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'photos'.DS.$data['Product']['articul'].".jpg");

			// удаляем дополнительные изображения
			foreach($data['ProductPicture'] as $picture) {
					
				$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.$picture['picture']);
				$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.'180'.DS.$picture['picture']);
				$this->File->delete(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'dop_photos'.DS.'98'.DS.$picture['picture']); 
						
				$this->Product->ProductPicture->del($picture['id']);
			}	
			$this->Product->del($data['Product']['id']);	
		}
	    $this->redirect('products');
	}
		    
	function index() {
		$this->set('title','Запчасти');	
	 	
		if(!isset($_GET['price'])) {
			$_GET['price'] = 104;
		}
		
		if(!empty($_GET['WATAS'])){
			$product_alls = $this->Product->findAll();
			$complect_number=mysql_fetch_row(mysql_query("SELECT max(complect_number) FROM storages"));
			$storage_id = $this->Storage->findCount();
			foreach($product_alls as $product_all){
				$i=0;
				while($i < $product_all['Product']['quantity']){
					$i++;
					$storage['Storage']['id'] = ++$storage_id;
					$storage['Storage']['complect_number'] = 0;
					$storage['Storage']['order_product_id'] = 0;
					$storage['Storage']['order_id'] = 0;
					$storage['Storage']['price_id'] = $product_all['Product']['price_id'];
					$storage['Storage']['name'] = $product_all['Product']['name'];
					$storage['Storage']['manuf_number'] = '';
					$storage['Storage']['articul'] = $product_all['Product']['articul'];
					$storage['Storage']['customer_id'] = 0;
					$storage['Storage']['storage'] = 1;
					$storage['Storage']['date'] = $product_all['Product']['created'];
					$this->Storage->save($storage);
				}
			}
		}
		
        if(isset($_GET['price'])) {

                $where = array();
	 	if(!empty($_GET['price']))
	 	{$where[] = "price_id = ".Sanitize::paranoid($_GET['price']);}
                else
                {
                           if(!in_array('1', $this->othAuth->permission(id)))
                                   $where[] = 'price_id>=100 AND price_id NOT IN ('.implode(',',$this->forbidden_prices).') ';
                            else
                                    $where[] = "price_id >=100 ";
                }
	 	if(!empty($_GET["mark"])) {

                  $mark = $this->Product->Mark->read(null,$_GET["mark"]);
                  $where[] .= "(Product.mark_id = '".Sanitize::paranoid($_GET["mark"])."' OR
                  Product.mark_and_model LIKE '%".$mark["Mark"]["name"]."%')"; 	
                }
                  if(!empty($_GET["model"])) {	
        
                    $model = $this->Product->Model->read(null,$_GET["model"]); 
                    $where[] .= "(Product.model_id = '".Sanitize::paranoid($_GET["model"])."'   OR
                         Product.mark_and_model LIKE '%".$model["Model"]["name"]."%' )"; 	
                 }
                if(!empty($_GET['yr']))
	 	{$where[] = "year1 <= ".Sanitize::paranoid($_GET['yr'])." AND year2 >= ".Sanitize::paranoid($_GET['yr']);}
		if(!empty($_GET['nm']))
	 	{$where[] = "Product.name LIKE  '%".Sanitize::paranoid($_GET['nm'],$this->_alphabet())."%'";}		
	 	if(!empty($_GET['num']))
	 	{$where[] = "Product.number = '".Sanitize::paranoid($_GET['num'],$this->_alphabet())."'";}	
	 	if(!empty($_GET['art']))
	 	{$where[] = "Product.articul = '".Sanitize::paranoid($_GET['art'],$this->_alphabet())."'";}		
		if(!empty($_GET['status']) || @$_GET['status']==='0')
	 	{$where[] = "active = ".Sanitize::paranoid($_GET['status']);}
                if(!empty($_GET['interest']) || @$_GET['interest']==='0')
	 	{$where[] = "interest = ".Sanitize::paranoid($_GET['interest']);}	
	 	if(!empty($_GET['insert_date_from']))
	 	{$where[] = "created >= '". date('Y-m-d 00:00:00', strtotime($_GET['insert_date_from'])) . "'";}
	 	if(!empty($_GET['insert_date_to']))
	 	{$where[] = "created <= '". date('Y-m-d 23:59:59', strtotime($_GET['insert_date_to'])). "'";}
	 	
		$data = $this->Product->findAll(implode(" AND ",$where), null, $this->order, $this->show, $this->page, 2);
		$paging['style'] = 'html'; //set the style of the links: html or ajax
		$paging['link'] = '/'.@$this->params['url']['url'].'?price='.@$_GET['price'].'&mark='.@$_GET['mark'].'&model='.@$_GET['model'].'&yr='.@$_GET['yr'].'&
		nm='.@$_GET['nm'].'&num='.@$_GET['num'].'&art='.@$_GET['art'].'&insert_date_from='.@$_GET['insert_date_from'].'&insert_date_to='.@$_GET['insert_date_to'].'&status='.@$_GET['status'].'&interest='.@$_GET['interest'].'&page=';		
		$paging['count'] = $this->Product->findCount(implode(" AND ",$where),2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);
                }

		$this->set('marks', $this->Product->Mark->findAll(null,null,"Mark.name ASC"));
		$this->set('models', $this->Product->Model->findAll());	

$where_for_price = "Price.id>=100";	
if(!in_array('1', $this->othAuth->permission(id)))
         $where_for_price = 'Price.id>=100 AND Price.id NOT IN ('.implode(',',$this->forbidden_prices).')';
		$this->set('prices', $this->Product->Price->findAll($where_for_price));

                $this->set('sel_price', @$_GET['price']);
                $this->set('sel_mark', @$_GET['mark']);
                $this->set('sel_model', @$_GET['model']);
                $this->set('sel_year', @$_GET['yr']);
		$this->set('sel_nm', @$_GET['nm']);
		$this->set('sel_num', @$_GET['num']);
		$this->set('sel_art', @$_GET['art']);
		$this->set('sel_status', @$_GET['status']);
                $this->set('sel_interest', @$_GET['interest']);
		$this->set('insert_date_from', @$_GET['insert_date_from']);
		$this->set('insert_date_to', @$_GET['insert_date_to']);
	}
}
?>