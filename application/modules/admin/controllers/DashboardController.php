<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{

    public function indexAction()
    {
        
    }

    public function fanpagesAction() 
    {
        $fanpages_model = new Model_Fanpages;
        $fanpages = $fanpages_model->facebookRequest('me', $this->_identity->user_access_token, array('accounts'));
        
        $authorized_pages = $fanpages_model->getAuthorizedFanpages($this->_identity->user_id);

        if (count($authorized_pages)) {
            foreach($authorized_pages as $authorized_page) {
                $authorized_ids[] = $authorized_page->fanpage_id;
            }
        } else {
             $authorized_ids = array();
        }
        $this->view->accounts = $fanpages->accounts->data;
        $this->view->authorized = $authorized_ids;
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

