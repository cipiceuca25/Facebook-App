<?php

abstract class Fancrank_App_Controller_BaseController extends Fancrank_Controller_Action
{
	public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));

        if(!$this->_auth->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id')));  
        } else {
            $this->_identity = $this->_auth->getIdentity();
            $this->view->user = $this->_identity;
        }
    }

    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/app/'. $this->_request->getControllerName() . '.js');

        $this->_helper->layout()->controller = $this->_request->getControllerName();
        $this->_helper->layout()->action = $this->_request->getActionName();
    }
}

