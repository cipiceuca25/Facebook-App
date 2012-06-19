<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
	
    public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        //$this->data = $this->getSignedRequest($this->_getParam('signed_request'));
        /*
        if (APPLICATION_ENV != 'production') {
        	$this->view->fanpage_id = '178384541065';
            $this->data['page']['id'] = $this->_getParam('id');
            //$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
            $this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
        }
        */
        try {
        	$this->data['page']['id'] = Zend_Registry::get('fanpageId');
        } catch (Exception $e) {
        	//TOLOG
        	$this->data['page']['id'] = $this->_getParam('id');
        }
        
        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('index', 'app', 'app', array($this->data['page']['id'] => ''));   
        }
	
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout');
		//$this->_helper->redirector('login', 'index', 'app', array($this->data['page']['id'] => ''));
    }
    
}

