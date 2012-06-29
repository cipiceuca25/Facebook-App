<?php
class Service_FancrankMailService {
	
	protected $_smtpConnection;
	protected $_server;
	protected $_mailList;
	
	public function __construct() {
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$this->_mailList = new Zend_Config_Xml(APPLICATION_PATH.'/configs/emails.xml', 'cron_mail');
		
		$mailConfig = $config->email;
		$this->_server = $mailConfig->server;
		$mailConfig = $mailConfig->toArray();
		 
		$this->_smtpConnection = new Zend_Mail_Transport_Smtp($this->_server, $mailConfig);
	}
	
	public function sendErrorMail($err) {
		$mail = new Zend_Mail();
		//$mail->setBodyText($err);
		$mail->setBodyHtml($err);
		$mail->setFrom($this->_server, 'fancrank');
		
		if(count($this->_mailList->email) === 1) {
			$mailTo = $this->_mailList->email;
			$mail->addTo($mailTo->address, $mailTo->name);
		}else if(count($this->_mailList->email) > 1) {
			foreach ($this->_mailList->email as $mailTo) {
				$mail->addTo($mailTo->address, $mailTo->name);
			}
		}else {
			return;
		}

		$date = Zend_Date::now();
		$mail->setSubject('Cron Job Error Notifiation: ' .$date->toString(Zend_date::ISO_8601).PHP_EOL);
		try {
			$mail->send($this->_smtpConnection);
		} catch (Exception $e) {
			//Log Fail Mail
			echo $e->getMessage();
		}
	}
}
?>