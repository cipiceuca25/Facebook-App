<?php
class Fancrank_Fc_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	
	protected $_username;
	protected $_password;
	
	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($username, $password) {
		$this->_username = $username;
		$this->_password = $password;
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
		
		$users = array (
				'username'=> 'admin',
				'password'=> 'admin123' 
		);
		
		if ( $this->_username == $users['username'] && $this->_password != $users['password'] ) {
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->_username);
		} else if ($this->_username != $users['username']) {
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $this->_username);
		}
		
		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, null);
	}
}

?>