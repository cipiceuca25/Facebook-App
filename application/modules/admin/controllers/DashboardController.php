<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{
    public function indexAction()
    {
        //load the dashboard page
       // $fanpage_users = new Model_FanpageUsers();
        //$this->view->fanpage_users = $fanpage_users->fanpageUserSummary($this->_getParam('fanpage_id'));
        $model = new Model_Photos;
        
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

