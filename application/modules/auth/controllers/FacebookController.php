<?php
class Auth_FacebookController extends Fancrank_Auth_Controller_BaseController
{
	public function preDispatch()
	{
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		//$this->data['page']['id'] = $this->_getParam('id');
		
		if($this->_auth->hasIdentity()) {
			$this->_forward('index', 'app', 'app', $this->_getAllParams());
		}
		
		parent::preDispatch();
	}

}