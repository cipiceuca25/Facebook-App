<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
	
    public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        $this->data = $this->getSignedRequest($this->_getParam('signed_request'));

        if (APPLICATION_ENV != 'production') {
        	$this->view->fanpage_id = '178384541065';
            $this->data['page']['id'] = $this->_getParam('id');
            //$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
            $this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
        }

        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('index', 'app', 'app', array($this->data['page']['id'] => null));   
        }
	
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout');
    }
    
	public function loginAction() {
		$this->_helper->layout->setLayout('default_layout');
	}
	
	public function logoutAction()
	{
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_auth = Zend_Auth::getInstance();
		if($this->_auth->hasIdentity()) {
			$this->_identity = $this->_auth->clearIdentity();
		}
		//$this->_helper->redirector('login', $this->getRequest()->getControllerName(), $this->getRequest()->getModuleName(), array($this->_getParam('id') => null));
		$this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => null));
	}
}

