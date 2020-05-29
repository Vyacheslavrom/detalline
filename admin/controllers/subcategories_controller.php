<?php
uses('sanitize');

class SubcategoriesController extends AppController
{
	var $name = 'Subcategories';
	var $helpers = array('Html', 'Pagination');
	var $show = 25;
	var $sortBy = 'name';
	var $direction = 'ASC';
	var $page;
	var $order;
	
	function __construct() {
		$this->page = empty($_GET['page'])? '1': Sanitize::paranoid($_GET['page']);
		parent::__construct();
		$this->order = $this->modelClass.'.'.$this->sortBy.' '.strtoupper($this->direction);		
	}
	
	function delete($id=null) {
	 	$ids = array();
	 	
	 	if($id) { $ids[] = $id; }
	 	else {
			foreach($_POST as $key=>$val) 
				if(strstr($key,'chk_del_')!='') $ids[] = str_replace('chk_del_','',$key);	
		}
		foreach($ids as $id) {
			$this->Subcategory->del($id);	
			@$flash .= "Подкатегория $id удалена. ";
		}
		$this->Session->setFlash(@$flash,'flash');
	    $this->redirect('subcategories');
	}
	
 function updateStatistics() { 
	
             ini_set("max_execution_time","600");

		if (class_exists('DATABASE_CONFIG')) {
			$db_con = new DATABASE_CONFIG();
		}
		
$link = mysqli_init();
mysqli_options($link, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=0");
mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
mysqli_real_connect($link, $db_con->default['host'], $db_con->default['login'],  $db_con->default['password'], $db_con->default['database']);  
  
if (mysqli_connect_errno())
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
  
$som = 1;
$query = "CALL update_statistcs2()";
 
if (mysqli_real_query($link,$query))
{
  if ($result2 = mysqli_store_result($link))
  {
    while ($row = mysqli_fetch_assoc($result2))
    {
      var_dump($row);
    }
  }
}
  
mysqli_close($link); 

                      $this->Session->setFlash('Статистика удачно обновлена','flash');	
                       $this->redirect('subcategories');	
            
	}



    function index($id=null) {
	 
		$this->set('title','Подкатегории');
		
		if (!empty($this->data)) {	
			 	
				$errors = false;
			
				if(!$this->Subcategory->validates($this->data)) {
					$errors = true;
				}
			
				if(!$errors) {
					
                                       $this->data['Subcategory']['slug'] = Inflector::slug($this->data['Subcategory']['name']);

					//в аналоги добавляем name
					$analogs = array_diff(explode(',',$_POST['analogs']), array(''));
					if(!in_array($this->data['Subcategory']['name'], $analogs))
						$analogs[] = $this->data['Subcategory']['name'];
                                         $exceptions = array_diff(explode(',',$_POST['exceptions']), array(''));
					
					//описание начинается с заглавной
					$this->data['Subcategory']['description'] = ucfirst($this->data['Subcategory']['description']);
					
					if($this->Subcategory->save($this->data)) {

                                                mysql_query("delete from subcategory_analogs where subcategory_id = '".$this->Subcategory->id."' ");
                                                 foreach($analogs as $analog)
                                                                 mysql_query("insert into subcategory_analogs(subcategory_id, name) values('".$this->Subcategory->id."', '".$analog."') ");

                                                mysql_query("delete from subcategory_exceptions where subcategory_id = '".$this->Subcategory->id."' ");
                                                 foreach($exceptions as $exception)
                                                                 mysql_query("insert into subcategory_exceptions(subcategory_id, name) values('".$this->Subcategory->id."', '".$exception."') ");

						$this->Session->setFlash('Запись сохранена.','flash');
						$this->redirect('subcategories?cat='.$this->data['Subcategory']['category_id']);
					}
				}
				else {
					$this->Session->setFlash('При сохранении обнаружены ошибки','flash');
				}
		}

		if($id) 
		{
                         $this->params['data'] = $this->Subcategory->read(null, Sanitize::paranoid($id));

                          $analogs = array();
                        foreach($this->params['data']['SubcategoryAnalog'] as $analog)
                                $analogs[] = $analog['name'];
                        $this->set('analogs', implode(',', $analogs));

                         $exceptions = array();
                        foreach($this->params['data']['SubcategoryException'] as $exception)
                                $exceptions[] = $exception['name'];
                        $this->set('exceptions', implode(',', $exceptions));
                 }
	 	
		
		if(isset($_GET["cat"])) {		
			$search_fields = array();
			$search_fields["cat"] = @$_GET["cat"];
			$search_fields["nm"] = @$_GET["nm"];
			
			$this->Session->del('search_subcats');	
			$this->Session->write('search_subcats',$search_fields);		
		}
		
		if($this->Session->valid('search_subcats'))
			$sess_search = $this->Session->read('search_subcats');
			
		$where = array();
		if(!empty($sess_search) && is_array($sess_search))
		{
			if(!empty($sess_search["cat"])) 
				$where[] = " Subcategory.category_id = '".Sanitize::paranoid($sess_search["cat"])."' "; 
			if(!empty($sess_search["nm"])) 
				$where[] = " Subcategory.name LIKE '%".$sess_search["nm"]."%' "; 
        }
				
		$data = $this->Subcategory->findAll($where, null, $this->order, $this->show, $this->page, 2);
		$paging['style'] = 'html'; //set the style of the links: html or ajax

		$paging['link'] = '/subcategories/?page=';		
		$paging['count'] = $this->Subcategory->findCount($where,2);
		$paging['page'] = $this->page;
		$paging['limit'] = $this->show;
		$paging['show'] = array('20','50','150');
		$this->set('paging',$paging);
		$this->set('data', $data);

		$this->set('cats', $this->Subcategory->Category->findAll(null,null,"Category.name ASC"));
		$this->set('sel_cat', @$sess_search['cat']);
		$this->set('sel_nm', @$sess_search['nm']);
	}
}
?>