<?php
class SendmailComponent extends Object {
	
	//var $server = 'smtp.spaceweb.ru';
	//var $port = 2525;
	//var $login = 'detalline.ru+support';
	//var $pass = 'belmondo15619';
	
	var $from_email = 'support@detalline.ru';
	var $from_name = 'Detalline.ru - Магазин автозапчастей ';
	
	var $to_email = 'support@detalline.ru';
	var $to_name = 'Detalline.ru - Магазин автозапчастей';
	
	var $subject = 'Без темы';
	var $message = '';
	
	var $debug = 0;
	var $not_to_send = 0;
	
	function send() {
		if($this->not_to_send) return;

		mail($this->to_email, $this->subject, $this->message, $this->get_header());
	}
	function get_header() {	
		$header="Date: ".date("D, j M Y G:i:s")." +0700\r\n";
		$header.="From: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode($this->from_name)))."?= <".$this->from_email.">\r\n";
		$header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
		$header.="Reply-To: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode($this->from_name)))."?= <".$this->from_email.">\r\n";
		$header.="X-Priority: 3 (Normal)\r\n";
		$header.="Message-ID: <172562218.".date("YmjHis")."@mail.ru>\r\n";
		$header.="To: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode($this->to_name)))."?= <".$this->to_email.">\r\n";
		$header.="Subject: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode($this->subject)))."?=\r\n";
		$header.="MIME-Version: 1.0\r\n";
		$header.="Content-Type: text/plain; charset=windows-1251\r\n";
		$header.="Content-Transfer-Encoding: 8bit\r\n";
		return $header;
	}
	/*function get_data($smtp_conn)
	{
		$data="";
		while($str = fgets($smtp_conn,515))
		{
			$data .= $str;
			if(substr($str,3,1) == " ") { break; }
		}
		return $data;
	}*/
}
?>