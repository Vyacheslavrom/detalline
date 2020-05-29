<?php
class StoRequestsController extends AppController
{	
	function add() 
	{
                $this->layout = "blank";
                $this->set("stos",$this->StoRequest->Sto->findAll());
		if (!empty($this->data)) {		
				$errors = false;
				if(!$this->StoRequest->validates($this->data)) {	
					$errors = true;
				}
                                if(empty($this->data['StoRequest']['sto_id'])) {
				          $this->StoRequest->invalidate('sto_id');
				          $errors = true;
			        }
				if(!$this->Tuning->CheckCode($this->data['StoRequest']['code'], $this->data['StoRequest']['hash'])) 	   {
					$this->StoRequest->invalidate('code');
					$errors = true;
				} 	
				if(!$errors) {

                                        $row_sto = $this->StoRequest->Sto->read(null,$this->data['StoRequest']['sto_id']);

                                        $hh = empty($this->data['StoRequest']['hh']) ? '00' : $this->data['StoRequest']['hh'];
                                        $mm = empty($this->data['StoRequest']['mm']) ? '00' : $this->data['StoRequest']['mm'];
                                        $this->data["StoRequest"]["dateOfRepair"] = date('Y-m-d', strtotime($this->data['StoRequest']['date'])).' '.$hh.':'.$mm.':00';
					$this->StoRequest->save($this->data);
					
           
					// �������� ������ ������
					$this->Sendmail->subject = '����� ������ �� ���';
					$this->Sendmail->message = 
'+------------------------------------------------------------------
|   ����� ������ �� ���
|   '.date('d.m.Y H:i').'
+------------------------------------------------------------------
|  ��: '.$this->data['StoRequest']['fio'].', �.'.$this->data['StoRequest']['phone'].', email: '.$this->data['StoRequest']['email'].'
+------------------------------------------------------------------
|  ����� � ������ ����: '.$this->data['StoRequest']['mark_and_model'].'
+------------------------------------------------------------------
|  �������� ����� �������: '.$this->data['StoRequest']['date'].' � '.$hh.':'.$mm.'
+------------------------------------------------------------------
|  ���: ID'.$row_sto['Sto']['id'].', '.$row_sto['Sto']['name'].', �.'.$row_sto['Sto']['phone'].', '.$row_sto['Sto']['person'].' 
+------------------------------------------------------------------
|  �������� ��������: '.$this->data['StoRequest']['description'];				
					$this->Sendmail->send();
					$this->set("sent","1");
				} else {
					$this->Session->setFlash('<font color=red>���������� ������</font><p>','flash');
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
}