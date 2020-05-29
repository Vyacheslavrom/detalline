<?php
class StoRequest extends AppModel
{			
	var $name = 'StoRequest';
	var $belongsTo = array('Sto');
        var $validate = array(
	  'fio' => VALID_NOT_EMPTY,
	  'phone' => VALID_NOT_EMPTY,
	  'mark_and_model' => VALID_NOT_EMPTY,
          'date' => VALID_NOT_EMPTY,  
	  'hh' => '(\d{2})',
          'mm' => '(\d{2})'	    	    	  
   );  
}
?>