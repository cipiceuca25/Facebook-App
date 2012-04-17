<?php

class Admin_IndexController extends Fancrank_Admin_Controller_BaseController
{

    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();

        if($this->_auth->hasIdentity()) {
            $this->_identity = $this->_auth->getIdentity();
            //bring the user into the app if he is already logged in
            $this->_helper->redirector('dasboard');   
        }
    }

    public function indexAction()
    {
        //load the login page
        
        
    }


}

