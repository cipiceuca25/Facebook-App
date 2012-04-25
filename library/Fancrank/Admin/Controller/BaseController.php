<?php

abstract class Fancrank_Admin_Controller_BaseController extends Fancrank_Controller_Action
{
    protected $_auth;
    protected $_identity;

    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));

        if(!$this->_auth->hasIdentity()) {
            $this->_helper->redirector('index', 'index');   

            //set the proper navbar
            $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array());
        } else {
            $this->_identity = $this->_auth->getIdentity();
            $this->view->user = $this->_identity;

            //set the proper navbar
            $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedin.phtml', array());
        }
    }

    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/admin/'. $this->_request->getControllerName() . '.js');

        $this->_helper->layout()->controller = $this->_request->getControllerName();
        $this->_helper->layout()->action = $this->_request->getActionName();
    }
}

