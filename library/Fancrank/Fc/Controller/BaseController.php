<?php
abstract class Fancrank_Fc_Controller_BaseController extends Fancrank_Controller_Action 
{
	protected $_auth;
	
	public function preDispatch() {
		// Initialize Zend_Application
		
		$this->_auth = Zend_Auth::getInstance ();
		$this->_auth->setStorage ( new Zend_Auth_Storage_Session ( 'Internal_Admin' ) );
		if (! $this->isAuthenticate()) {
			$this->_redirect ( '/fc/auth/index' );
		}
	}
	
	protected function isAuthenticate() {
		return $this->_auth->hasIdentity();
	}
}
