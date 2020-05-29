<?php
class MessagesController extends AppController
{	
	function add() 
	{
		if (!empty($this->data)) {		
				$errors = false;
				if(!$this->Message->validates($this->data)) {	
					$errors = true;
				}
				if(!$this->Tuning->CheckCode($this->data['Message']['code'], $this->data['Message']['hash'])) 	   {
					$this->Message->invalidate('code');
					$errors = true;
				} 	
				if(!$errors) {
					$this->data['Message']['site'] = $_SERVER['HTTP_HOST'];
					$this->Message->save($this->data);
					
					// отправка письма админу
					$this->Sendmail->subject = 'Новое сообщение';
					$this->Sendmail->message = 
'+------------------------------------------------------------------
|   НОВОЕ СООБЩЕНИЕ
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------
|  от: '.$this->data['Message']['fio'].', '.$this->data['Message']['email'].'
+------------------------------------------------------------------
|  тема: '.$this->data['Message']['subject'].'
+------------------------------------------------------------------
'.$this->data['Message']['body'];				
					$this->Sendmail->send();
					$this->set("sent","1");
				} else {
					$this->Session->setFlash('<p><font color=red>Обнаружены ошибки</font>','flash');
				}
		}
		
		// captcha
	 	$this->Tuning->width = 35;
		$this->Tuning->height = 15;
		$this->Tuning->GetCode();
		$this->Tuning->msg = $this->Tuning->hashcode;
		$this->Tuning->DrawImage();
	 	$this->set('tuning_url','/files/t.png');
	 	$this->set('tuning_hash',$this->Tuning->hashvalue);

	 	//------------
	}

        function barbie_look() 
	{
                if(!empty($_POST)) {
                                        $this->Sendmail->from_name = 'BARBIE-LOOK - сайт наращивания ресниц';

                                        $this->Sendmail->to_email = $_POST['master_email'];
                                        $this->Sendmail->to_name = $_POST['master_email']; 

                                        $this->Sendmail->subject = 'Новое сообщение';
					$this->Sendmail->message = 
'+------------------------------------------------------------------
|   НОВОЕ СООБЩЕНИЕ
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------
|  от: '.$_POST['name'].', '.$_POST['email'].'
+------------------------------------------------------------------
'.$_POST['text'];			
                                       
					$this->Sendmail->send(); 

                                         $this->redirect('http://barbie-look.narod.ru/mail_sent.html');

                } else {
                       $this->redirect('http://barbie-look.narod.ru/contacts.html');
                }
		
	}
}