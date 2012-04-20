<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{

    public function indexAction()
    {
        
    }

    public function fanpagesAction() 
    {

    }

    public function myaccountAction()
    {
        
    }
    
    public function actionSources()
    {
    	
    }

    public function logoutAction() 
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }


}

