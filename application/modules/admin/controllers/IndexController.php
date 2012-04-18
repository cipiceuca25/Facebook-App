<?php

class Admin_IndexController extends Fancrank_Admin_Controller_BaseController
{

    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));

        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_helper->redirector('dasboard');   
        }
    }

    public function indexAction()
    {
        //load the login page
        
        
    }


}

