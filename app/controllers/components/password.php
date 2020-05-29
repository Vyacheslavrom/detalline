<?php
class PasswordComponent extends Object {

function generate_password($length = 10) {
	        $allowable_characters = "abcdefghjkmnpqrstuvxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	        $ps_len = strlen($allowable_characters);
	        mt_srand((double)microtime()*1000000);
	        $pass = "";
	        for($i = 0; $i < $length; $i++) {
	                $pass .= $allowable_characters[mt_rand(0,$ps_len-1)];
	        }
	        return $pass;
}
}