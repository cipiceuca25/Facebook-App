<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{
    public function indexAction()
    {
        //load the dashboard page
        
        
    }

    public function logoutAction() 
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }


}

