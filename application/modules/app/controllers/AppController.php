<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
    public function indexAction()
    {
        
    }

  	public function topfansAction()
  	{
      //maybe we should be asking for a relavant time from the api user and pass it as a parameter in the queries
  		$model = new Model_TopFans;
  		$this->view->top_fans = $model->getTopFans($this->_getParam('id'));
  		$this->view->most_popular = $model->getMostPopular($this->_getParam('id'));
  		$this->view->top_talker = $model->getTopTalker($this->_getParam('id'));
      $this->view->top_clicker = $model->getTopClicker($this->_getParam('id'));
  	}

    public function logoutAction()
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));   
    }
}

