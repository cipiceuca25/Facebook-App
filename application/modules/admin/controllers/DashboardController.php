<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{

    public function indexAction()
    {
        
    }

    public function fanpagesAction() 
    {
        $fanpages_model = new Model_Fanpages;
        $facebook_request = $fanpages_model->facebookRequest('me', $this->_identity->user_access_token, array('accounts'));
        $this->view->accounts = $facebook_request->accounts->data;
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

