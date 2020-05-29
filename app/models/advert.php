<?php
class Advert extends AppModel
{			
	var $name = 'Advert';
    var $belongsTo = array('AdvertType');
	var $validate = array(
	  'advert_type_id' => VALID_NOT_EMPTY
   );  
}
?>