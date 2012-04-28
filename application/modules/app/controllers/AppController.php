<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
    public function indexAction()
    {
        
    }

  	public function topfansAction()
  	{

  		$model = new Model_TopFans;
  		$this->view->top_fans = $model->getTopFans($this->_getParam('id'));
  		$this->view->most_popular = $model->getMostPopular($this->_getParam('id'));
  		$this->view->top_talker = $model->getTopTalker($this->_getParam('id'));
      $this->view->top_clicker = $model->getTopClicker($this->_getParam('id'));
  	}
}

