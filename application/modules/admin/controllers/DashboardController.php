<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{

    public function indexAction()
    {

    }

    public function fanpagesAction() 
    {
        $fanpages_model = new Model_Fanpages;
        
        $pages = $fanpages_model->getActiveFanpagesByUserId($this->_identity->user_id);

        $this->view->pages = $pages;
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

    public function previewAction()
    {
        $fanpages_model = new Model_Fanpages;
        $fanpage = $fanpages_model->findByFanpageId($this->_getParam('id'))->current();

        $this->view->installed = $fanpage->installed;
        $this->view->preview = '/app/index/' . $this->_getParam('id');
        $this->view->page_id = $this->_getParam('id');
    }

}

