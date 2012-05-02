<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
	 public function preDispatch()
    {die('here');
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));

        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('index', 'app', 'app', array($this->_getParam('id') => ''));   
        }
    }

    public function indexAction()
    {
        $this->view->fanpage_id = $this->_getParam('id');
    }
}
