<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
    public function indexAction()
    {
        
    }

  	public function topfansAction()
  	{
      //maybe we should be asking for a relavant time from the api user and pass it as a parameter in the queries
  		$model = new Model_Rankings;
  		$this->view->top_fans = $model->getRanking($this->_getParam('id'), 'FAN');
  		$this->view->most_popular = $model->getRanking($this->_getParam('id'), 'POPULAR');
  		$this->view->top_talker = $model->getRanking($this->_getParam('id'), 'TALKER');
      $this->view->top_clicker = $model->getRanking($this->_getParam('id'), 'CLICKER');
  	}

    public function logoutAction()
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));   
    }
}

