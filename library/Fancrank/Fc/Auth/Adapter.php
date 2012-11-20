<?php
class Fancrank_Fc_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	
	protected $_username;
	protected $_password;
	protected $_aclList;
	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($username, $password) {
		$this->_username = $username;
		$this->_password = $password;
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$this->_aclList = new Zend_Config_Xml(APPLICATION_PATH.'/configs/acl.xml', 'internal');
	}
	
	/**
	 * Performs an authentication attempt
	 *
	 * @throws Zend_Auth_Adapter_Exception If authentication cannot
	 *         be performed
	 * @return Zend_Auth_Result
	 */
	public function authenticate() {
		
		if (empty($this->_username) || empty($this->_password)) {
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->_username);
		}
		
		foreach ($this->getUserList() as $user) {
			if ( $this->_username == $user['username'] && $this->_password != $user['password'] ) {
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->_username);
			} 
			
			if ( $this->_username == $user['username'] && $this->_password == $user['password'] ) {
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, null);
			}
		}
		
		return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $this->_username);
	}
	
	private function getUserList() {
		if (isset($this->_aclList->user->username)) {
			return array($this->_aclList->user->toArray());
		}
		
		return $this->_aclList->user->toArray();
	}
}

?>