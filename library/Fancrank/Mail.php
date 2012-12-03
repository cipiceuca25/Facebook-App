<?php
class Fancrank_Mail extends Zend_Mail {
	
	protected $_smtpConnection;
	protected $_server;
	protected $_mailTo;
	
	public function __construct($mailTo) {
		$validator = new Zend_Validate_EmailAddress();
		
		if($validator->isValid($mailTo)) {
			$this->_mailTo = $mailTo;		
		}else {
			throw new InvalidArgumentException('invalid email');				
		}
		
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$mailConfig = $config->email;
		$this->_server = $mailConfig->server;
		$mailConfig = $mailConfig->toArray();
		$this->_smtpConnection = new Zend_Mail_Transport_Smtp($this->_server, $mailConfig);
	}
	
	public function sendMail($msg) {
		$this->setBodyHtml($msg);
		//$this->setFrom($this->_server, 'fancrank');
		
		$this->addTo($this->_mailTo);
		
		try {
			$this->send($this->_smtpConnection);
		} catch (Exception $e) {
			//Log Fail Mail
			throw new Exception($e->getMessage());
		}
	}
	
	private function formatMessage($msg) {
		return '<a target="_blank" href="'. $msg .'">tracking link</a>';
	}
}
?>