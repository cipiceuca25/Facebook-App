<?php
class Auth_FacebookController extends Fancrank_Auth_Controller_BaseController
{
	public function preDispatch()
	{
		$this->_auth = Zend_Auth::getInstance();
		$url = '/';
		if($this->_request->getActionName() === 'login') {
			$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));
			$url = '/admin/dashboard';
		}else if($this->_request->getActionName() === 'authorize'){
			$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
			$url = '/app/app/index/' .$this->_getParam('id');
		}else {
			return;
		}
		//$this->data['page']['id'] = $this->_getParam('id');
		
		//Zend_Debug::dump($this->_auth->hasIdentity());
		//exit();
		if($this->_auth->hasIdentity()) {
			$this->_redirect($url); 
		}
		
		parent::preDispatch();
	}

}