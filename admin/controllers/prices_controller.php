<?php
class PricesController extends AppController
{
	var $name = 'Prices';
	var $components = array("File");
        var $uses = array('Price','Mark','Model','Product','PriceParamValue', 'PriceParamName','Manufacturer','TimeUpdatePrice');
        var $forbidden_prices = array(104);

	function edit($id = null) {	
		if (!empty($this->data)) {	
			$errors = false;
			
			if(!$this->Price->validates($this->data)) {
				$errors = true;
			}		
			
			if($this->data['Price']['manufacturer'] === '0' && $this->data['Price']['manufacturer_csv'] === '') {
				$this->Price->invalidate('manufacturer_csv');
				$errors = true;
			}
			
			if(!$errors) {	
			    if(!($this->data['Price']['manufacturer'] === '0')) 
					$this->data['Price']['manufacturer_csv'] = $this->data['Price']['manufacturer'];
					
				if ($this->Price->save($this->data)) {
					$this->Session->setFlash('Запись сохранена','flash');
					$this->redirect("prices");
				}
			}
			else {
				$this->Session->setFlash('<font color=blue>При сохранении обнаружены ошибки</font>','flash');
			}
		} else {
			$this->params['data'] = $this->Price->read(null, $id); 
			
			$this->params['data']['Price']['manufacturer'] = !$id || is_numeric($this->params['data']['Price']['manufacturer_csv']) ? 0 : $this->params['data']['Price']['manufacturer_csv'];
			$this->params['data']['Price']['manufacturer_csv'] = !$id || is_numeric($this->params['data']['Price']['manufacturer_csv']) ? $this->params['data']['Price']['manufacturer_csv'] : '';
		}
        
		$this->set('price_params', $this->PriceParamName->findAll());
		$this->set('manufacturers', $this->Manufacturer->findAll());
		$this->set('title', 'Редактирование загрузчика прайса');
	}
	
	function index()
	{
	
	///////////////////////////////////////////////////////////////////////////////////////////
	$need = mysql_fetch_array(mysql_query('SELECT date FROM `date_statuses` ORDER BY date DESC'));
	$this_month = explode('-',$need[0]);
	$this->set('this_month',$this_month[1]);
	if(isset($_POST['update_stat'])){
		$statuses = mysql_query("select * from `orders_statuses` where status_id = 5 AND stage_date >  curdate() - interval 30 day");
		while($status = mysql_fetch_array($statuses)){
			$products = mysql_query("select id,order_id,price_id from `order_products` where id=".$status[2]);
			while($product = mysql_fetch_array($products)){
				$price=$product[2];
				$stat[$price]['count'] = ++$stat[$price]['count'];
			}
			$orders = mysql_query("select created from `orders` where id=".$status[0]);
			while($order = mysql_fetch_array($orders)){
				$date1 = new DateTime($order[0]);
				$date2 = new DateTime($status[3]);
				$interval =	date_diff($date1,$date2);
				$stat[$price]['days'] += $interval->days;
				if($stat[$price]['long1']=='') $stat[$price]['long1']=0;
				if($stat[$price]['long1']<$interval->days){
					$stat[$price]['long3']=$stat[$price]['long2'];
					$stat[$price]['long2']=$stat[$price]['long1'];
					$stat[$price]['long1']=$interval->days;
				}else
					if($stat[$price]['long2']<$interval->days){
						$stat[$price]['long3']=$stat[$price]['long2'];
						$stat[$price]['long2']=$interval->days;
					}else
						if($stat[$price]['long3']<$interval->days) $stat[$price]['long3']=$interval->days;
			}
		}
		$prices = mysql_query("select id from `prices`");
		while($price = mysql_fetch_array($prices)){
			if(!empty($stat[$price[0]])){
				mysql_query("delete from `date_statuses` where price_id =".$price[0]);
				$days0=$stat[$price[0]]['days']/$stat[$price[0]]['count'];
				mysql_query("insert into `date_statuses` (`price_id`, `days`, `status`, `date`) values(".$price[0].",".$days0.",0,NOW())");
				if($stat[$price[0]]['long2']==0) $long = 1; else
					if($stat[$price[0]]['long3']==0) $long = 2; else $long = 3;
				$days = $stat[$price[0]]['long1']+$stat[$price[0]]['long2']+$stat[$price[0]]['long3'];
				mysql_query("insert into `date_statuses` (`price_id`, `days`, `status`, `date`) values(".$price[0].",".($days/$long).",1,NOW())");
			}
		}
	}
	///////////////////////////////////////////////////////////////////////////////////////////
	

		/*$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS."city.csv", "r");
		while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		if($data[1]=='"3159"'){
		$a1=explode('"',$data[3]);
		$a2=explode('"',$data[2]);
		switch($a2[1]){
			case "3160":{$a3=150;break;}
			case "3223":{$a3=151;break;}
			case "3251":{$a3=152;break;}
			case "3282":{$a3=153;break;}
			case "3352":{$a3=154;break;}
			case "3371":{$a3=155;break;}
			case "3437":{$a3=156;break;}
			case "3468":{$a3=157;break;}
			case "3503":{$a3=158;break;}
			case "3529":{$a3=159;break;}
			case "3673":{$a3=160;break;}
			case "3675":{$a3=161;break;}
			case "3703":{$a3=162;break;}
			case "3751":{$a3=163;break;}
			case "3761":{$a3=164;break;}
			case "3841":{$a3=165;break;}
			case "5191":{$a3=166;break;}
			case "3921":{$a3=167;break;}
			case "3952":{$a3=168;break;}
			case "3872":{$a3=169;break;}
			case "4052":{$a3=224;break;}
			case "4105":{$a3=170;break;}
			case "4176":{$a3=171;break;}
			case "4198":{$a3=172;break;}
			case "4925":{$a3=173;break;}
			case "4227":{$a3=174;break;}
			case "4312":{$a3=175;break;}
			case "4481":{$a3=176;break;}
			case "3563":{$a3=177;break;}
			case "4503":{$a3=178;break;}
			case "4528":{$a3=179;break;}
			case "4561":{$a3=180;break;}
			case "4593":{$a3=181;break;}
			case "4633":{$a3=182;break;}
			case "4657":{$a3=183;break;}
			case "4689":{$a3=184;break;}
			case "4734":{$a3=185;break;}
			case "4773":{$a3=186;break;}
			case "3160":{$a3=187;break;}
			case "3296":{$a3=188;break;}
			case "3407":{$a3=189;break;}
			case "3630":{$a3=190;break;}
			case "3827":{$a3=192;break;}
			case "3892":{$a3=193;break;}
			case "3994":{$a3=194;break;}
			case "4270":{$a3=195;break;}
			case "4287":{$a3=196;break;}
			case "5011":{$a3=197;break;}
			case "5151":{$a3=198;break;}
			case "5246":{$a3=199;break;}
			case "5312":{$a3=200;break;}
			case "2316497":{$a3=201;break;}
			case "4800":{$a3=223;break;}
			case "4861":{$a3=202;break;}
			case "4891":{$a3=203;break;}
			case "4969":{$a3=204;break;}
			case "5080":{$a3=205;break;}
			case "5161":{$a3=206;break;}
			case "5191":{$a3=225;break;}
			case "5225":{$a3=207;break;}
			case "3784":{$a3=208;break;}
			case "5291":{$a3=209;break;}
			case "5326":{$a3=210;break;}
			case "5356":{$a3=211;break;}
			case "5404":{$a3=212;break;}
			case "5432":{$a3=213;break;}
			case "5473":{$a3=215;break;}
			case "2499002":{$a3=216;break;}
			case "5507":{$a3=217;break;}
			case "5543":{$a3=218;break;}
			case "5555":{$a3=219;break;}
			case "5600":{$a3=220;break;}
			case "5052":{$a3=226;break;}
			case "5019394":{$a3=221;break;}
			case "5625":{$a3=222;break;}
			case "1998532":{$a3=227;break;}
			case "3872":{$a3=228;break;}
			case "4026":{$a3=229;break;}
			case "4243":{$a3=230;break;}
			case "5458":{$a3=231;break;}
			case "2415585":{$a3=232;break;}
			default:{$a3=$a2[1];break;}
		}
		//mysql_query("INSERT INTO `region_news` (id, name) VALUES ('".preg_replace('/[^0-9]+/i','',$data[0])."', '".$a1[1]."');");
		mysql_query("INSERT INTO `cities` (region_id, name) VALUES ('".$a3."', '".$a1[1]."');");
		}
	
	}*/	
	
		if(isset($_POST['save_update_price'])){
			$time_price['TimeUpdatePrice']['id'] = 1;
			$time_price['TimeUpdatePrice']['days'] = $_POST['days_update'];
			$time_price['TimeUpdatePrice']['prices'] = $_POST['prices_update'];
			$this->TimeUpdatePrice->save($time_price);
		}
		
		$this->set('title','Прайсы');
	
$where = 'Price.active=1 ';	
//if(!in_array('1', $this->othAuth->permission(id)))
        // $where .= ' Price.id NOT IN ('.implode(',',$this->forbidden_prices).') ';

		$data = $this->Price->findAll($where,null,"Price.id ASC");
		$this->set('data', $data);
		
		$update_price = $this->TimeUpdatePrice->read(null,1);
		$this->set('update_price',$update_price);
		
	}
	

function stolenPhoto()
{

    $result = mysql_query("select articul from products where price_id =27; ");
//echo mysql_num_rows(mysql_query("select articul from products where price_id in (5,6,7,8,9) ; "));
$i=0;
    while($row = mysql_fetch_row($result))
    {


           $url = 'http://forwardsp.ru/linkpics/clip/'.$row[0].'.jpg';
           $path = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'photos'.DS.$row[0].'.jpg';
		   if(!file_exists($path)) {
				echo "".(++$i)." ".$row[0]."<br>";
			  $content = file_get_contents($url);
			  if($content)
				   file_put_contents($path, $content); 
		}
    }

    $this->render('index');
}


	function skidka($id) { 
	
		if(empty($id)) {
			$this->redirect('prices');	
		}

if(!in_array('1', $this->othAuth->permission(id)) && in_array($id, $this->forbidden_prices))  {
			$this->redirect('prices');	
		}

		if (!empty($this->data)) {
      
      mysql_query("UPDATE `products` SET price=price*".(1+($this->data['Price']['skidka']/100))." WHERE price_id = '".$id."'");
      $this->redirect('prices');		
		}
	}
	
function days_shipping()
{
		if (!empty($this->data)) {
      $this->Price->save($this->data);
      mysql_query("UPDATE `products` SET days_shipping='".$this->data[Price][days_shipping_garant]."', shipping_info='".$this->data[Price][days_shipping_min]."- (".$this->data[Price][days_shipping_garant].")  дн.'  WHERE price_id = '".$this->data[Price][id]."'");
      $this->redirect('prices');		
		}
}

/*function updateAnalogsAndExceptions()
{

 mysql_query("DELETE FROM mark_analogs");
mysql_query("DELETE FROM model_analogs");
mysql_query("DELETE FROM model_exceptions");
mysql_query("DELETE FROM subcategory_analogs");
mysql_query("DELETE FROM subcategory_exceptions");

 $marks = $this->Mark->findAll("active=1");
                        
foreach($marks as $mark) {

$mark_analogs = explode(',',$mark['Mark']['analogs']);
foreach($mark_analogs as $value) {
       mysql_query("INSERT INTO mark_analogs(mark_id,name) VALUES('".$mark['Mark']['id']."','".$value."')");
}

$models = $this->Model->findAll("Model.mark_id=".$mark['Mark']['id']);
foreach($models as $model) {

$model_analogs = explode(',',$model['Model']['analogs']);
foreach($model_analogs as $value) {
	 mysql_query("INSERT INTO model_analogs(model_id,name) VALUES('".$model['Model']['id']."','".$value."')");
}

$model_exeptions = explode(',',$model['Model']['exeptions']);
foreach($model_exeptions as $value) {
	if($value)
		mysql_query("INSERT INTO model_exceptions(model_id,name) VALUES('".$model['Model']['id']."','".$value."')");
}
}
}


 $res = mysql_query("select * from subcategories");
		       while($row = mysql_fetch_array($res)) {
		
			     $subcat_analogs = explode(',',$row['analogs']);
			        foreach($subcat_analogs as $value) {
				      mysql_query("INSERT INTO subcategory_analogs(subcategory_id,name) VALUES('".$row['id']."','".$value."')");
			        }
			       $subcat_exeptions = explode(',',$row['exeptions']);
			       foreach($subcat_exeptions as $value) {
				        if($value)
					   mysql_query("INSERT INTO subcategory_exceptions(subcategory_id,name) VALUES('".$row['id']."','".$value."')");
			        }
			}

$this->redirect('prices');		
}*/

         function updateStatistics() { 
	
             $this->layout = 'blank';
             ini_set('memory_limit', '128M');
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

if($_GET['step'] == 1)
{

   if(mysqli_real_query($link,' DELETE FROM products_models '));
   if (mysqli_real_query($link,"CALL update_statistcs('Acura', 'Daihatsu')"))
   {mysqli_real_query($link,'INSERT INTO products_models (model_id, product_id) VALUES ("1","1");');
      if ($result = mysqli_store_result($link))
      {
           while ($row = mysqli_fetch_assoc($result))
            {
                var_dump($row);
             }
       }
    }
}  
if($_GET['step'] == 2)
{
   mysqli_real_query($link,"CALL update_statistcs('Dodge', 'Lancia')");
}  
if($_GET['step'] == 3)
{
   mysqli_real_query($link,"CALL update_statistcs('Land Rover', 'Seat')");
} 
if($_GET['step'] == 4)
{
   mysqli_real_query($link,"CALL update_statistcs('Skoda', 'Volvo')");
} 
if($_GET['step'] == 5)
{ 
   mysqli_real_query($link,"UPDATE marks SET products = 
(SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id 
INNER JOIN models ON models.id=products_models.model_id 
WHERE models.mark_id = marks.id AND products.bu = 0),
products_bu  = (SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id 
INNER JOIN models ON models.id=products_models.model_id 
WHERE models.mark_id = marks.id AND products.bu = 1)");

mysqli_real_query($link,"UPDATE models SET products = 
(SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id  
WHERE products_models.model_id = models.id AND products.bu = 0),
products_bu  = (SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id  
WHERE products_models.model_id = models.id AND products.bu = 1)");
} 
if($_GET['step'] == 6)
{
   mysqli_real_query($link,"CALL update_statistcs2()");
} 
mysqli_close($link); 
}

/*function fotoRename()
{
       $ndir = ROOT.DS.'app'.DS.'webroot'.DS.'files'.DS.'products'.DS.'photos_bu'.DS;
       $dir = opendir ($ndir);
       while ( $oldname = readdir ($dir)) {
             echo $oldname.'<br>';
             if(strpos( $oldname, "IMG_" ) !== false) {
                   $newname = str_replace("IMG_", "W",  $oldname);
                   rename($ndir.$oldname,   $ndir.$newname);                    
              }
       }
$this->render('index');
}*/

function deletePrice($id) { 

		if(empty($id)) {
			$this->redirect('prices');	
		}

		//mysql_query("delete from prices where id='".$id."'");
		mysql_query("delete from products_models where product_id in (select id from products where price_id='".$id."')");
		mysql_query("delete from products_subcategories where product_id in (select id from products where price_id='".$id."')");
		mysql_query("delete from products where price_id='".$id."'");	
        mysql_query("delete from nproducts where price_id='".$id."'");				
		mysql_query("UPDATE marks SET products = 
(SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id 
INNER JOIN models ON models.id=products_models.model_id 
WHERE models.mark_id = marks.id AND products.bu = 0),
products_bu  = (SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id 
INNER JOIN models ON models.id=products_models.model_id 
WHERE models.mark_id = marks.id AND products.bu = 1)");
		mysql_query("UPDATE models SET products = 
(SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id  
WHERE products_models.model_id = models.id AND products.bu = 0),
products_bu  = (SELECT COUNT(DISTINCT products.id) FROM products
INNER JOIN products_models ON products_models.product_id=products.id  
WHERE products_models.model_id = models.id AND products.bu = 1)");
		$this->redirect('prices');
}

function downloadPrice($id) { 

ini_set('memory_limit', '128M');
	   ini_set("max_execution_time","1800");
	   
     if(!in_array('1', $this->othAuth->permission(id)) && in_array($id, $this->forbidden_prices))  {
			$this->redirect('prices');	
		}

	$this->layout = "blank";
 

		if(empty($id)) {
			$this->redirect('prices');	
		}

		$nl_symbols = array('\r\n', '\n\r', '\n', '\r' );
		$empty_symbols = array(' ', ' ', ' ', ' ' );
		
		$res = mysql_query("select * from products where price_id = ".$id." ");
		
	
		$price = $this->Product->Price->read(null,$id);
			
		
		if(isset($_GET['wholesale']))  {
		
			
				$str = "; Detalline.ru;  \r\n ; Ростов-на-Дону, ул. Вавилова, 78г,; \r\n ; въезд со стартовой; \r\n ; Поставка запчастей по Ростову; \r\n ; в течении 1 часа; \r\n ; т. +7 (904) 503-07-07; \r\n \r\n ;Оптовый прайс ".$id." на ".date('d.m.Y')." г.;\r\n  \r\n ";
				$str .= "№; Наименование; Марка, модель; Состояние;  Производитель; Номер производителя; Цена опт.; \r\n";
				$i = 0;
				while($row = mysql_fetch_array($res))
				{
				  $str .= "".(++$i).";".str_replace($nl_symbols, $empty_symbols, $row['name']).";".str_replace($nl_symbols, $empty_symbols, $row['mark_and_model']).";".($row['bu'] ? "Б/у":"").";".$row['manufacturer'].";".$row['manuf_number'].";".number_format($row['wholesale_price'],0,".","").";".";\r\n";
				}
			
			$filename = $id.'_опт_'.date('d.m.Y').'.csv';
		
		} else if(isset($_GET['remains'])) {
			
			$str = " ;Остатки со склада ".$price['Price']['name']." на ".date('d.m.Y')." г.;\r\n  \r\n ";
			$str .= "№; Наименование; Марка, модель; Состояние;  Производитель; Номер производителя; Артикул; Остаток; \r\n";
			$i = 0;
			while($row = mysql_fetch_array($res))
			{
			  $str .= "".(++$i).";".str_replace($nl_symbols, $empty_symbols, $row['name']).";".str_replace($nl_symbols, $empty_symbols, $row['mark_and_model']).";".($row['bu'] ? "Б/у":"").";".$row['manufacturer'].";".$row['manuf_number'].";".strval($row['articul']).";".$row['quantity'].";".";\r\n";
			}
			$filename = $id.'_остатки_'.date('d.m.Y').'.csv';
		} else {
			
			$str = "№; Наименование; Марка, модель; Состояние;  Описание; Производитель; Номер производителя; Артикул; Оригинальный номер; Год начала выпуска; Год конца выпуска; Остаток; Цена; Примечание;\r\n";
			$i = 0;
			while($row = mysql_fetch_array($res))
			{
			  $str .= "".(++$i).";".str_replace($nl_symbols, $empty_symbols, $row['name']).";".str_replace($nl_symbols, $empty_symbols, $row['mark_and_model']).";".($row['bu'] ? "Б/у":"").";".str_replace($nl_symbols, $empty_symbols, $row['description']).";".$row['manufacturer'].";".$row['manuf_number'].";".strval($row['articul']).";".$row['number'].";".$row['year1'].";".$row['year2'].";".$row['quantity'].";".number_format($row['price'],0,".","").";".str_replace($nl_symbols, $empty_symbols, $row['note']).";\r\n";
			}
			$filename = $id.'.csv';
		}
		
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);
header("Content-Type: application/x-msexcel");
header("Content-Disposition: attachment; filename=\"" .$filename. "\";");
header("Content-Transfer-Encoding:­ binary");
header("Content-Length: " . strlen($str));
echo $str;
}


function addcrosses()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");
			 
	mysql_query("delete from crosses where part=0 and file IS NULL");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."crosses.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part) 
		VALUES ('".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[2]."', '".preg_replace('/[^0-9a-z]+/i','',$data[3])."', '".strtolower($data[4])."', 0);");
	}
	$this->redirect('prices');	
}

function addsupercross()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");

	mysql_query("delete from crosses where part=0  and file = 'supercross' ");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."supercross.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, part, file) 
		VALUES ('".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[2]."', '".preg_replace('/[^0-9a-z]+/i','',$data[3])."', 0, 'supercross');");
	}
	
	/*mysql_query("DELETE c2 FROM crosses c2 
   JOIN   (SELECT manuf_number, number, part, MAX( id ) AS maxid
FROM `crosses` c1
GROUP BY manuf_number, number, part
HAVING COUNT( * ) >1
    ) AS lim
      ON (c2.manuf_number, c2.number, c2.part) = (lim.manuf_number, lim.number, lim.part) AND c2.id < maxid ");*/
	  
	$this->redirect('prices');	
}

function addcrosskyb1()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");

	mysql_query("delete from crosses where part=0  and file = 'crosskyb1' ");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."crosskyb1.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, part, file) 
		VALUES ('KYB', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[3]."', '".preg_replace('/[^0-9a-z]+/i','',$data[0])."', 0, 'crosskyb1');");
	}
  
	$this->redirect('prices');	
}

function addcrosskyb2()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");

	mysql_query("delete from crosses where part=0  and file = 'crosskyb2' ");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."crosskyb2.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, number, part, file) 
		VALUES ('KYB', '".preg_replace('/[^0-9a-z]+/i','',$data[2])."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', 0, 'crosskyb2');");
	}
  
	$this->redirect('prices');	
}

function addcrossbilstein()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");

	mysql_query("delete from crosses where part=0  and file = 'crossbilstein' ");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."crossbilstein.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, part, file) 
		VALUES ('BILSTEIN', '".preg_replace('/[^0-9a-z]+/i','',$data[2])."', '".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', 0, 'crossbilstein');");
	}
  
	$this->redirect('prices');	
}

function addcrosses_parts()
{
	ini_set('memory_limit', '128M');
    ini_set("max_execution_time","1800");
			 
	mysql_query("delete from crosses where part=1  and file IS NULL");
			 
	$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS."crosses_parts.csv", "r");
	while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
		mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part) 
		VALUES ('".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[2]."', '".preg_replace('/[^0-9a-z]+/i','',$data[3])."', '".strtolower($data[4])."', 1);");
	}
	$this->redirect('prices');	
}


	function uploadPrice($id) { 
	
		if(strpos($id,'24_') !== false) {
			$sub_price = str_replace("24_","",$id);
			$id = 24;
		}
		
		if(strpos($id,'25_') !== false) {
			$sub_price = str_replace("25_","",$id);
			$id = 25;
		}
		
		if(!in_array('1', $this->othAuth->permission(id)) && in_array($id, $this->forbidden_prices))  {
			$this->redirect('prices');	
		}

	   ini_set('memory_limit', '128M');
	   ini_set("max_execution_time","1800");

		if(empty($id)) {
			$this->redirect('prices');	
		}
		if (!empty($this->data)) {
			
			$this->File->allowed_extentions = array('csv');
			$this->File->upload($this->data['Price']['file'], ($id == 24 || $id == 25 ? $id.'_'.$sub_price : $id), ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS);
		 	
			if($this->File->error) {
				$this->Session->setFlash('Ошибка при загрузке прайса. '.$this->File->error.'','flash');			
			} else {
			
				
			    $current_price = $this->Price->read(null, $id);
                $days_shipping = $current_price['Price']['days_shipping_garant'];
                $shipping_info = $current_price[Price][days_shipping_min]."- (".$current_price[Price][days_shipping_garant].") дн.";
				$price_pers = 1 + $current_price[Price][price_pers] / 100;
				//$wholesale_price = 1 + $current_price[Price][wholesale_price] / 100;
				$table_name = $current_price[Price][param2_id] == 4 ? 'nproducts' : 'products';
				$write_in_bd=true;
				$check_bu=false;
				
				

				mysql_query("DELETE FROM `products` WHERE price_id = '".$id."'");
				mysql_query("DELETE FROM `nproducts` WHERE price_id = '".$id."' ".($id==24 || $id == 25 ? " AND sub_price ='".$sub_price ."' ":""));
				mysql_query("DELETE FROM `crosses` WHERE file = 'cross".$id."'");
				
				$handle = fopen(ROOT.DS.'app'.DS.'webroot'.DS.'prices'.DS.($id == 24 || $id == 25 ? $id.'_'.$sub_price : $id).".csv", "r");

				$mark="";
				$model = "";
				while (($data = $this->__fgetcsv_club($handle, 1000, ";","\r\n")) !== FALSE) {
					//прайс
					if($data[3]==' Состояние') $check_bu=true;
					
					if($current_price['Price']['row']>0){$current_price['Price']['row']--;}else{
					
					if($check_bu==true){$bu=($data[3]==' Б/у'? 0:1);}else{$bu=($current_price[Price][param3_id]==6? 0:1);}
					
					if($current_price[Price][param5_id] == 9 || $current_price[Price][param5_id] == 11 || $current_price[Price][param5_id] == 12) {
						if($table_name == 'nproducts') {
							$qty_arr = explode(',', $current_price[Price][quantity_csv]);
							$qty_sum = 0;
							for($i = 0; $i < count($qty_arr); $i++) {
								$qty_sum += intval($data[$qty_arr[$i]]);
							}
							if(!empty($data[$current_price[Price][manuf_number_csv]]) && !empty($data[$current_price[Price][price_csv]]))	
								mysql_query("INSERT INTO `".$table_name."` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
										created,modified) VALUES (".$id.", '".(is_numeric($current_price[Price][manufacturer_csv]) ? $data[$current_price[Price][manufacturer_csv]] : $current_price[Price][manufacturer_csv])."', '".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][manuf_number_csv]])."', 
										'".strtolower($data[$current_price[Price][name_csv]])."', '".($price_pers*str_replace(array(","," ","руб."),array(".","",""),$data[$current_price[Price][price_csv]]))."', '".$qty_sum."', 
										".$days_shipping.", '".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
							
						} else {
							$qty_arr = explode(',', $current_price[Price][quantity_csv]);
							$qty_sum = 0;
							for($i = 0; $i < count($qty_arr); $i++) {
								$qty_sum += intval($data[$qty_arr[$i]]);
							}
							if (preg_match("/[0-9]/", $current_price[Price][year2])){
								$year1=$data[$current_price[Price][year1]];
								$year2=$data[$current_price[Price][year2]];}
								else{
								
								$year_arr = explode(',', $current_price[Price][year2]);
								$year[1]=$data[$current_price[Price][year1]];
								$a=0;
								
								for($i = 0; $i < count($year_arr); $i++) {
									$year=explode($year_arr[$i], $year[1]);
									
									if (is_numeric($year[0])){
										$year_sum[$a]=$year[0];
										$a++;
										if(is_numeric($year[1])) {$year_sum[1]=$year[1];}
									}else{
										if($year[1]!=''){
											$tyear=explode(" ",$year[(count($year)-2)]);
											if (is_numeric($tyear[(count($tyear)-1)])){
												$year_sum[$a]=$tyear[(count($tyear)-1)];
												$a++;
											}
											$tyear=explode(" ",$year[(count($year)-1)]);
											if (is_numeric($tyear[0])){
												$year_sum[$a]=$tyear[0];
												$a++;
											}
										}
									}
								}
									$year1=$year_sum[0];
									$year2=$year_sum[1];
									if($year1>=100) $year1=0;
									if($year2>=100) $year1=0;
								}
							if($year1=='') $year1=0;
							if($year2=='') $year2=0;
							if($year2==0) $year2=3000;
							$year_sum[0]=NULL;
							$year_sum[1]=NULL;
							
							if($year1<100 && $year1!=0){
								if($year1>40) {$year1 = "19".strval($year1);}
								else{$year1 = "20".strval($year1);}
							}
							if($year2<100){
								if($year2>40) {$year2 = "19".strval($year2);}
								else{$year2 = "20".strval($year2);}
							}
							if(empty($year2)) $year2=3000;
							if($year1>date('Y')) $year1=$year1-100;
							if($year1>date('Y')) $year1=0;
							if($year1<1000 && $year1!=0) $year1=0;
							if($year2>3000) $year2=3000;
							
							//if($current_price[Price][name_csv]==5 && $current_price[Price][mark_and_model]==3 && $current_price[Price][articul]==1) $name=iconv('UTF-8', 'WINDOWS-1251', $data[$current_price[Price][name_csv]]); else $name=$data[$current_price[Price][name_csv]];
							$name=$data[$current_price[Price][name_csv]];
							
								if($data[$current_price[Price][price_csv]]==''){
									$write_in_bd=false;
								}else{
									$write_in_bd=true;
								}
							$mark_and_model=$data[$current_price[Price][mark_and_model]];
							if($write_in_bd==true){
							
							if($current_price[Price][wholesale_price_pers]>0){
								$cost_price = ceil(str_replace(array(" Рубль"," "),array("",""),$data[$current_price[Price][price_csv]]));
								$price_pers_bd = $cost_price <= 1000 ? 60 : 45;
								$price = ceil($cost_price * (100 + $price_pers_bd) / 100 / 100) * 100;
								$wholesale_price_bd = ceil($cost_price * (100 + $wholesale_price_pers) / 100 / 100) * 100;
							}else{
								$price = ($price_pers*str_replace(array(","," ","руб."),array(".","",""),$data[$current_price[Price][price_csv]]));
								$wholesale_price_bd = ($wholesale_price*str_replace(array(","," ","руб."),array(".","",""),$data[$current_price[Price][wholesale_price]]));
								$price_pers_bd = 0;
								$cost_price = 0;
							}
							
							if(preg_match('/([^\(]+)\(([^\)-]+)-([^\)]*)\)/i', $mark_and_model, $m)) $mark_and_model=$m[1];
							
							$mark_and_model = str_replace('\\','/',$mark_and_model);
							$mark_and_model = substr($mark_and_model,strpos($mark_and_model,"/"));
							
							$mark_and_model = str_replace("/",' ',$mark_and_model);
							
								mysql_query("INSERT INTO `".$table_name."` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,manufacturer,manuf_number,number,articul,cost_price,price,wholesale_price,
						  price_pers, wholesale_price_pers,quantity,days_shipping,shipping_info,note,bu,
						  interest,active,created,modified) VALUES (".$id.", NULL, NULL, NULL, '".(is_numeric($current_price[Price][mark_and_model]) ? $mark_and_model : $current_price[Price][mark_and_model])."',
						".$year1.",".$year2.",'0','".$name."','".strtolower($data[$current_price[Price][description]])."','".$data[$current_price[Price][manufacturer_csv]]."', '".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][manuf_number_csv]])."','".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][number]])."',
						'".preg_replace('/\s/','',$data[$current_price[Price][articul]])."','".$cost_price."','".$price."','".$wholesale_price_bd."','".$price_pers_bd."','".$current_price[Price][wholesale_price_pers]."',
						'".$qty_sum."','".$days_shipping."','".$shipping_info."','".$data[$current_price[Price][note]]."','".$bu."',0,1,'".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
						}
						}
					}
					
					
					//кросс
					if($current_price[Price][param5_id] == 10 || $current_price[Price][param5_id] == 11 || $current_price[Price][param5_id] == 12) {
					
					if(empty($name))  $name=iconv('UTF-8', 'WINDOWS-1251', $data[$current_price[Price][name_csv]]); else $name=$data[$current_price[Price][name_csv]];
					if($id == 17){$name=$data[$current_price[Price][name_csv]];}
						//if(!empty($data[$current_price[Price][manuf_number_csv]]) && !empty($data[$current_price[Price][number_csv]]))
								mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
							VALUES ('".(is_numeric($current_price[Price][manufacturer_csv]) ? $data[$current_price[Price][manufacturer_csv]] : $current_price[Price][manufacturer_csv])."', 
							'".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][manuf_number_csv]])."', 
							'".$data[$current_price[Price][mark]]."', 
							'".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][number_cross]])."', 
							'".$name."', 0, 'cross".$id."');");

					}

					continue;
					
					switch($id) {
					
					
//////////////////////////////////////////////////////////
						case 10: //автосоюз (поиск по номеру)
		
						
						if(!empty($data[$current_price[Price][manuf_number_csv]]) && !empty($data[$current_price[Price][price_csv]]))	
									mysql_query("INSERT INTO `".$table_name."` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
									created,modified) VALUES (".$id.", '".$data[$current_price[Price][manufacturer_csv]]."', '".preg_replace('/[^0-9a-z]+/i','',$data[$current_price[Price][manuf_number_csv]])."', 
									'".strtolower($data[$current_price[Price][name_csv]])."', '".(1.3*str_replace(array(","," ","руб."),array(".","",""),$data[$current_price[Price][price_csv]]))."', '".intval($data[$current_price[Price][quantity_csv]])."', ".$days_shipping.", 
													'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
							
           						
              
						break;
						
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////
						case 16: //вечный двигатель (поиск по номеру)
						
						if($data[2] && $data[4]) 
							mysql_query("INSERT INTO `nproducts` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
									created,modified) VALUES (".$id.", '".$data[1]."', '".str_replace(array('-',' ','.','/'),array('','','',''),$data[2])."', 
									'".strtolower($data[0])."', '".(1.3*str_replace(array(","," "),array(".",""),$data[4]))."', '".intval($data[3])."', ".$days_shipping.", 
													'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
							
						
                       
						break;
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////						
						case 17: //капот
						
						if(intval($data[5]) == 0)
							continue;
							
									
			mysql_query("INSERT INTO `nproducts` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
									created,modified) VALUES (".$id.", 'Тайвань', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', 
									'".strtolower($data[3])."', '".(1.45*str_replace(array(","," "),array(".",""),$data[5]))."', '".intval($data[4])."', ".$days_shipping.", 
													'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
			if(!empty($data[1]) && !empty($data[2]))
			mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
		VALUES ('Тайвань', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[6]."', '".preg_replace('/[^0-9a-z]+/i','',$data[2])."', '".strtolower($data[3])."', 0, 'cross".$id."');");

						break;
//////////////////////////////////////////////////////////
						
						
//////////////////////////////////////////////////////////
               case 21: //сапсан
					   
							mysql_query("INSERT INTO `nproducts` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,number,articul,price,quantity,days_shipping,shipping_info,note,bu,
						  interest,active,created,modified) VALUES (".$id.", 1, NULL, NULL, '".$data[4]."', NULL, NULL, '0', 
						  '".$data[5]."', 'Производитель - ".$data[3]."', '".$data[1]."', '".$data[2]."', 
						  ".(1.3*str_replace(array(","," "),array(".",""),$data[10])).", 
						  ".(intval($data[6])+intval($data[7])).", ".$days_shipping.", '".$shipping_info."','',0,0,1,
						  '".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");

					     break;
//////////////////////////////////////////////////////////						 
//////////////////////////////////////////////////////////						 
						  case 22:// тарекс-авто (api-depo-parts)

if(strpos($data[2], "'")===false)
        continue;

$name =  str_replace("'"," ",$data[2]);


mysql_query("INSERT INTO `nproducts` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
			created,modified) VALUES (".$id.", '".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$name."',
				'".ceil($data[3]*1.45)."', '".(intval($data[4]))."', ".$days_shipping.", 
							'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
if(!empty($data[1]) && !empty($data[6]))
mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
		VALUES ('".$data[0]."', '".preg_replace('/[^0-9a-z]+/i','',$data[1])."', '".$data[5]."', '".preg_replace('/[^0-9a-z]+/i','',$data[6])."', '".$name."', 0, 'cross".$id."');");
							
							

break;
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////
case 23:// экспресс-авто (поиск только по номерам)

mysql_query("INSERT INTO `nproducts` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
			created,modified) VALUES (".$id.", '".$data[0]."', '".str_replace('-','',$data[1])."', '".strtolower($data[2])."',
				'".ceil($data[4]*1.45)."', '".$data[3]."', ".$days_shipping.", 
							'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");

break;
 //////////////////////////////////////////////////////////
 
 case 26:	//автотрейд
						
						if(empty($data[1]))
							continue;
						
						$mark_and_model = preg_replace('/^[^a-z]+/i','',$data[1]);
						
						if(preg_match('/.*(\d{2})(\S*)(-)+(\S*)(\d{2})/i', $data[1], $m2) ||
						   preg_match('/.*(\d{2})(\S*)(-)+.*/i', $data[1], $m2) ) {
							
							  
							  $year1 = $m2[1];
							  $year2 = $m2[5];

							  if(intval($year1)>=40 && intval($year1)<100) $year1 = "19".strval($year1);
							  if(intval($year2)>=40 && intval($year2)<100) $year2 = "19".strval($year2);
							  if(intval($year1)<40 && !empty($year1)) $year1 = "20".strval($year1);
							  if(intval($year2)<40 && !empty($year2)) $year2 = "20".strval($year2);
							  if(empty($year2) && $year!="00") $year2=3000;

						}     
						if($year2<$year1)
						  continue;
				  
						  mysql_query("INSERT INTO `products` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,manufacturer,manuf_number,number,articul,price,wholesale_price,quantity,days_shipping,shipping_info,note,bu,
						  interest,active,created,modified) VALUES (".$id.", 3, NULL, NULL, '".$mark_and_model."', ".($mark_and_model ? "'".$year1."'":'NULL').", 
						  ".($mark_and_model ? "'".$year2."'":'NULL').", '0', '".$data[1]."', 
						  'Бренд - ".$data[2]."','".$data[2]."',
						  '".preg_replace('/[^0-9a-z]+/i','',$data[0])."', 
						  '', 
						  '".preg_replace('/[^0-9a-z]+/i','',$data[0])."', 
						  ".floor(str_replace(array(","," "),array(".",""),$data[5])*1.45).", 
						  ".floor(str_replace(array(","," "),array(".",""),$data[5])*1.12).", 
						  ".($data[6] == ">5" ? "5" : intval($data[6])).", ".$days_shipping.", 
						  '".$shipping_info."','',0,0,1,'".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
						  break;
						  
case 27:	//форвард
						
						if(empty($data[1]))
							continue;
						
						
						$mark_and_model = str_replace('\\','/',$data[3]);
						$mark_and_model = substr($mark_and_model,strpos($mark_and_model,"/")+1);
						
						$year1 = "";
						$year2 = "";
						if(strpos($data[4],"-")) {
							$year1=trim(substr($data[4],0,strpos($data[4],"-")));
							$year2=trim(substr($data[4],strpos($data[4],"-")+1));
						}

						if(intval($year1)>=40) $year1 = "19".strval($year1);
						if(intval($year2)>=40) $year2 = "19".strval($year2);
						if(intval($year1)<40 && !empty($year1)) $year1 = "20".strval($year1);
						if(intval($year2)<40 && !empty($year2)) $year2 = "20".strval($year2);
						if(empty($year2)) $year2=3000;
				  
						mysql_query("INSERT INTO `products` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,manufacturer,manuf_number,number,articul,price,quantity,days_shipping,shipping_info,note,bu,
						  interest,active,created,modified) VALUES (".$id.", 3, NULL, NULL, '".$mark_and_model."', '".$year1."', '".$year2."', 
						  '0', '".iconv('UTF-8', 'WINDOWS-1251', $data[5])."', '', '".($data[15]?$data[15]:'Тайвань')."',
						  '".preg_replace('/[^0-9a-z]+/i','',$data[16])."', 
						  '".preg_replace('/[^0-9a-z]+/i','',(strpos($data[2],"/")===false ? $data[2] : substr($data[2],0,strpos($data[2],"/"))))."',
						  '".$data[1]."', ".(1.45*str_replace(array(","," "),array(".",""),$data[6])).",
						  ".(empty($data[7]) && empty($data[8]) && empty($data[10]) ?"0":"1").", ".$days_shipping.", 
						  '".$shipping_info."','',0,0,1,'".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
						  
						if(!empty($data[16]) && !empty($data[2]))
						mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
		VALUES ('".($data[15]?$data[15]:'Тайвань')."', '".preg_replace('/[^0-9a-z]+/i','',$data[16])."', NULL, '".preg_replace('/[^0-9a-z]+/i','',(strpos($data[2],"/")===false ? $data[2] : substr($data[2],0,strpos($data[2],"/"))))."', '".iconv('UTF-8', 'WINDOWS-1251', $data[5])."', 0, 'cross".$id."');");

						break;
//////////////////////////////////////////////////////////
case 28: //реалавто

if(empty($data[6]))
	continue;
	
	mysql_query("INSERT INTO `nproducts` (price_id, manufacturer, manuf_number, name, price, quantity, days_shipping, shipping_info,
			created,modified) VALUES (".$id.", '".$data[3]."', '".preg_replace('/[^0-9a-z]+/i','',preg_replace('/\(.+\)/i','',$data[5]))."', '".$data[2]."',
				'".ceil(1.45*str_replace(array(","," "),array(".",""),$data[6]))."', '".(empty($data[7])?'0':'1')."', ".$days_shipping.", 
							'".$shipping_info."','".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
	if(!empty($data[5]) && !empty($data[4]))
mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
		VALUES ('".$data[3]."', '".preg_replace('/[^0-9a-z]+/i','',preg_replace('/\(.+\)/i','',$data[5]))."', NULL, '".preg_replace('/[^0-9a-z]+/i','',$data[4])."', '".$data[2]."', 0, 'cross".$id."');");
	
	break;
//////////////////////////////////////////////////////////

case 101: //наличие (Бу)
case 102: //Наличие (Алмаз)
case 100: //наличие (Тайвань)
case 103: //Наличие (Некрасовская) 
						
					    if(!empty($data[1])&&!empty($data[10])) {
							$data[3] = trim($data[3]);
							mysql_query("INSERT INTO `products` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,manufacturer,manuf_number,number,articul,price,quantity,days_shipping,shipping_info,note,bu,
						  interest,active,created,modified) VALUES (".$id.", 1, NULL, NULL, '".trim($data[2])."', '".$data[9]."', '".$data[10]."', '0',
							'".trim($data[1])."', '".trim($data[4])."','".trim($data[5])."','".trim($data[6])."', '".trim($data[8])."', 
							'".trim($data[7])."', ".$data[12].", 
							".$data[11].", 0,
							'в наличии','".trim($data[13])."',".($data[3]=='Б/у' ? 1:0).",0,1,'".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");

					    }
	
						break;
				    
				    
				    
case 104: //Наличие (Центр)
case 105: 
						
					    if(!empty($data[2])) {
					    
                if(preg_match('/([^\(]+)\(([^\)-]+)-([^\)]*)\)/i', $data[6], $m)) {
                  $mark_and_model = trim($m[1]);
                  $year1 = $m[2];
                  $year2 = $m[3];
                  if(intval($year1)>=40) $year1 = "19".strval($year1);
                  if(intval($year2)>=40) $year2 = "19".strval($year2);
                  if(intval($year1)<40 && !empty($year1)) $year1 = "20".strval($year1);
                  if(intval($year2)<40 && !empty($year2)) $year2 = "20".strval($year2);
                  if(empty($year2)) $year2=3000;
				  $name = trim($data[1]);
				  
				  /* Prices */
                  $cost_price = ceil(str_replace(array(" Рубль"," "),array("",""),$data[7]));
				  $price_pers = $cost_price <= 1000 ? 60 : 45;
				  $price = ceil($cost_price * (100 + $price_pers) / 100 / 100) * 100;
				  $wholesale_price_pers = 23;
				  $wholesale_price = ceil($cost_price * (100 + $wholesale_price_pers) / 100 / 100) * 100;
				  
				  
                  mysql_query("INSERT INTO `products` (price_id, category_id, mark_id, model_id, mark_and_model,
						  year1, year2,subcategories,name,description,manufacturer,manuf_number,number,articul,cost_price,price,wholesale_price,
						  price_pers, wholesale_price_pers, quantity,days_shipping,shipping_info,bu,
						  interest,active,created,modified) VALUES (".$id.", 1, NULL, NULL, '".$mark_and_model."', '".$year1."', '".$year2."', '0', 
						  '".$name."', '','".$data[5]."','".preg_replace('/[^0-9a-z]+/i','',$data[3])."',
						   '', 
						   '".intval($data[4])."', '".$cost_price."', '".$price."', '".$wholesale_price."', '".$price_pers."', '".$wholesale_price_pers."',
						   ".intval($data[2]).", 0, 'в наличии', ".($id == 104 ? 0 : 1).",0,1,'".date("Y-m-d 00:00:00")."','".date("Y-m-d 00:00:00")."');");
						   
						/*if(!empty($data[16]) && !empty($data[2]))
						mysql_query("INSERT INTO `crosses` (manufacturer, manuf_number, mark, number, name, part, file) 
		VALUES ('".($data[15]?$data[15]:'Тайвань')."', '".preg_replace('/[^0-9a-z]+/i','',$data[16])."', NULL, '".preg_replace('/[^0-9a-z]+/i','',
		(strpos($data[2],"/")===false ? $data[2] : substr($data[2],0,strpos($data[2],"/"))))."', '".$data[5]."', 0, 'cross".$id."');");*/

                
                } 
								

					    }
	
						break;
				    
				    }
				    }
				}
				
				
				fclose($handle);

				$priceData["id"] = $id;
				$priceData["uploaded"] = date("Y-m-d H:i:s");
				$this->Price->save($priceData);
				$this->Session->setFlash('Прайс удачно загружен','flash');				
			}
			//$this->redirect('prices/detectSubcats/'.$id);
			
			mysql_query("DELETE c2 FROM products c2 
   JOIN   (SELECT name,articul,price_id, MAX(id) AS maxid  FROM `products` c1
     GROUP BY name,articul,price_id
     HAVING  COUNT(*) > 1
    ) AS lim
      ON (c2.name, c2.articul, c2.price_id) = (lim.name,lim.articul,lim.price_id) AND c2.id < maxid AND (c2.price_id=26 OR c2.price_id=27)");
			mysql_query("delete from crosses where manuf_number='' or number=''");
			
			$this->redirect('prices');		
		}
	}
	
	function detectSubcats($id) {
		if(empty($id)) {
			$this->redirect('prices');	
		}		
		
if(!in_array('1', $this->othAuth->permission(id)) && in_array($id, $this->forbidden_prices))  {
			$this->redirect('prices');	
		}

		ini_set("max_execution_time","600");
		
		$res = mysql_query("select * from subcategories");
		while($row = mysql_fetch_array($res)) {
		
			$subcat_analogs = explode(',',$row['analogs']);
			$subcat_str1 = '';
			foreach($subcat_analogs as $value) {
				$subcat_str1 .= " OR `products`.`name` LIKE '%".$value."%' ";
			}
			$subcat_exeptions = explode(',',$row['exeptions']);
			$subcat_str2 = '';
			foreach($subcat_exeptions as $value) {
				if($value)
					$subcat_str2 .= " AND `products`.`name` NOT LIKE '%".$value."%' ";
			}
			
			//echo "UPDATE `products` SET subcategories = CONCAT(subcategories,\",".$row['id']."\") WHERE price_id = '".$id."' AND (1=0 ".$subcat_str.")<br>";
			
			mysql_query("UPDATE `products` SET subcategories = CONCAT(subcategories,\",".$row['id']."\") WHERE price_id = '".$id."' AND (1=0 ".$subcat_str1.") ".$subcat_str2);
		}		
		$this->redirect('prices');	
	}
	
	function __fgetcsv_club($f_handle, $length, $delimiter=',', $enclosure='"')
	{
	    if (!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	        return fgetcsv($f_handle, $length, $delimiter, $enclosure);
	    if (!$f_handle || feof($f_handle))
	        return false;
	           
	    if (strlen($delimiter) > 1)
	        $delimiter = substr($delimiter, 0, 1);
	    elseif (!strlen($delimiter))          // There _MUST_ be a delimiter
	        return false;
	                       
	    if (strlen($enclosure) > 1)         // There _MAY_ be an enclosure
	        $enclosure = substr($enclosure, 0, 1);
	                       
	    $line = fgets($f_handle, $length);
	    if (!$line)
	        return false;
	    $result = array();
	    $csv_fields = explode($delimiter, trim($line));
	    $csv_field_count = count($csv_fields);
	    $encl_len = strlen($enclosure);
	    for ($i=0; $i<$csv_field_count; $i++)
	    {
	        // Removing possible enclosures
	        if ($encl_len && $csv_fields[$i]{0} == $enclosure)
	            $csv_fields[$i] = substr($csv_fields[$i], 1);
	        if ($encl_len && $csv_fields[$i]{strlen($csv_fields[$i])-1} == $enclosure)
	            $csv_fields[$i] = substr($csv_fields[$i], 0, strlen($csv_fields[$i])-1);
	        // Double enclosures are just original symbols
	        $csv_fields[$i] = str_replace($enclosure.$enclosure, $enclosure, $csv_fields[$i]);
	        $result[] = $csv_fields[$i];
	    }
	    return $result;
	}
	
	function __get_price($price)
	{
		if($price<=500) 
			return ceil(($price+100)*1.45);
		elseif($price>500 && $price<=1000)
			return ceil(($price+150)*1.45);
		elseif($price>1000 && $price<=2000)
			return ceil(($price+250)*1.45);
		elseif($price>2000)
			return ceil(($price+350)*1.45);
	}
}
?>