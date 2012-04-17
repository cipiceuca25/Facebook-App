<?php

abstract class Fancrank_Admin_Controller_BaseController extends Fancrank_Controller_Action
{
    protected $_auth;
    protected $_identity;

    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();

        if($this->_auth->hasIdentity()) {
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('dasboard');   
        } else {
            //bring the user to the login page if he is not logged in
            $this->_helper->redirector('index');
        }
    }

    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/admin/'. $this->_request->getControllerName() . '.js');
    }
}

