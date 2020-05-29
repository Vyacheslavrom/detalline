<?php
class Buyer extends AppModel
{			
var $name = 'Buyer';
var $belongsTo = array('Customer','Mark','Model','EngineVolume','EngineType','WheelDrive','BodyType','Transmission','Market','Responsible');
var $hasMany = array('BuyerPart');	
var $validate = array(
	   'mark_id' => VALID_NOT_EMPTY,
	  'model_id' => VALID_NOT_EMPTY,
          'year' => VALID_NOT_EMPTY,
          'engine_volume_id' => VALID_NOT_EMPTY	  
   );  
}
?>