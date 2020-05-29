<?php
class Product extends AppModel
{			
	var $name = 'Product';
	var $hasMany= 'ProductPicture';
	var $belongsTo = array('Mark','Model','Price','Category');

       /*function findProductsWithPictures($count)
       {
			  $were = "Product.price_id NOT IN (1,2,3,4,10,11,12,13,14,15,16,17,18,19,20,21) AND Product.active=1 AND Product.quantity>0
			  AND exists (select * from photofiles where photofiles.code=Product.articul)";             
			  $all = $this->findCount($were,2);
			  $page = rand(1, ceil($all/$count));
              $data = $this->findAll($were, null, "Product.id ASC", $count, $page, 2);
			  return $data;
       }*/
	   
	   function findProductsWithPictures($count)
       {
			  $were = "Product.price_id NOT IN (1,2,3,4,10,11,12,13,14,15,16,17,18,19,20,21) AND Product.active=1 AND Product.quantity>0";             
			  $all = $this->findCount($were,2);
			  $page = rand(1, ceil($all/$count));
              $data = $this->findAll($were, null, "Product.id ASC", $count, $page, 2);
			  return $data;
       }

      function findInterestProducts($count)
       {
              $were = "Product.interest=1";
              $all = $this->findCount($were,2);
              $page = rand(1, ceil($all/$count));
              $result = $this->findAll($were, null, "Product.id ASC", $count, $page, 2);
              return $result;
             
       }
}
?>