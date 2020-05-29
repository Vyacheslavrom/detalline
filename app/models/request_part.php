<?php
class RequestPart extends AppModel
{			
	var $name = 'RequestPart';
        var $belongsTo = array('PartState');
}
?>