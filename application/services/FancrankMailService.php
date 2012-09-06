<?php
class Service_FancrankMailService {
	
	protected $_smtpConnection;
	protected $_server;
	protected $_mailList;
	protected $_user;
	
	public function __construct($user=null) {
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$this->_mailList = new Zend_Config_Xml(APPLICATION_PATH.'/configs/emails.xml', 'cron_mail');
		$this->_user = $user;

		$mailConfig = $config->email;
		$this->_server = $mailConfig->server;
		$mailConfig = $mailConfig->toArray();
		 
		$this->_smtpConnection = new Zend_Mail_Transport_Smtp($this->_server, $mailConfig);
	}
	
	public function sendErrorMail($err) {
		$this->sendMail($err);
	}
	
	public function sendOKMail($msg) {
		$this->sendMail($msg);
	}
	
	protected function sendMail($msg) {
		$mail = new Zend_Mail();
		//$mail->setBodyText($err);
		$mail->setBodyHtml($msg);
		$mail->setFrom($this->_server, 'fancrank');
		
		if(! $this->is_multi_array($this->_mailList->email->toArray())) {
			$mailTo = $this->_mailList->email;
			$mail->addTo($mailTo->address, $mailTo->name);
		}else if(count($this->_mailList->email) > 1) {
			foreach ($this->_mailList->email as $mailTo) {
				$mail->addTo($mailTo->address, $mailTo->name);
			}
		}else {
			return;
		}
		
		if(!empty($this->_user->facebook_user_first_name) && !empty($this->_user->facebook_user_email)) {
			$mail->addTo($this->_user->facebook_user_email, $this->_user->facebook_user_first_name);
		}

		$date = Zend_Date::now();
		$mail->setSubject('Fanpage Ready Notification: ' .$date->toString(Zend_date::ISO_8601).PHP_EOL);
		try {
			$mail->send($this->_smtpConnection);
		} catch (Exception $e) {
			//Log Fail Mail
			throw new Exception($e->getMessage());
		}
	}
	
	private function is_multi_array($a) {
	    foreach ($a as $v) {
	        if (is_array($v)) return true;
	    }
	    return false;
	}
	
}
?>