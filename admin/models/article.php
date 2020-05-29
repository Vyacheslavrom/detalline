<?php
class Article extends AppModel
{			
	var $name = 'Article';
    var $belongsTo = array('ArtCategory');
	var $hasMany = 'ArticlePicture';
	var $validate = array(
	  'art_category_id' => VALID_NOT_EMPTY,
	  'header' => VALID_NOT_EMPTY
   );  
}
?>