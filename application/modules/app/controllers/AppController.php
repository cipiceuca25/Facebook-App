<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
    public function indexAction()
    {
        
    }

  	public function topfansAction()
  	{
  		die('jere');
  		$model = new Model_TopFans;
  		$this->view->top_fans = $model->getTopFans($this->_getParam('id'));
  		$this->view->most_popular = $model->getMostPopular($this->_getParam('id'));
  		//$this->view->top_talker = $model->getTopTalker($this->_getParam('id'));

  		die(print_r($this->view->most_popular));
  	}
}

