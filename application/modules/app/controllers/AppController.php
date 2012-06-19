<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		if (APPLICATION_ENV != 'production') {
			$this->data['page']['id'] = '178384541065';
			//$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
			$this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
		}
		try {
			$fanpageId = Zend_Registry::get('fanpageId');
			echo 'fanpageId: ' .$fanpageId;
			$this->data['page']['id'] = $fanpageId;
		} catch (Exception $e) {
			$fanpageId = $this->_getParam('id');
		}
		parent::preDispatch();
	}
	
    public function indexAction()
    {
    	//$this->_forward('newsfeed');
    	$user = new Model_FacebookUsers();
    	$user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    		$access_token = $this->_facebook_user->facebook_user_access_token;
    		$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	$this->render('newsfeed');
    }

  	public function topfansAction()
  	{
  		$this->_helper->layout->disableLayout();
  		$this->view->fanpage_id = $this->data['page']['id'];

  		$model = new Model_Rankings;

  		$this->view->top_fans = $model->getRanking($this->data['page']['id'], 'FAN', false, 5);
  		$this->view->most_popular = $model->getRanking($this->data['page']['id'], 'POPULAR', false, 5);
  		$this->view->top_talker = $model->getRanking($this->data['page']['id'], 'TALKER', false, 5);
  		$this->view->top_clicker = $model->getRanking($this->data['page']['id'], 'CLICKER', false, 5);

  		/*
  		 $client = new Zend_Http_Client;
  		$client->setUri('https://graph.facebook.com/' . $this->view->fan_id;
  				$client->setMethod(Zend_Http_Client::GET);

  				try {
  				$response = $client->request();
  				} catch (Exception $e) {

  				}

  				$json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

  				if (property_exists($json, 'error')) {
  				// try again

  				} else {
  				$this->view->me = $json->data;
  				}
  				*/

  		$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
  		$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
  		$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
  		$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
  	}

    public function newsfeedAction() 
    {
    	$this->_helper->layout->disableLayout();
    	 
    	$user = new Model_FacebookUsers();
    	$user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    		$access_token = $this->_facebook_user->facebook_user_access_token;
    		$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	//Zend_Debug::dump($user); exit();
    	$this->render("newsfeed");
    }
    
    public function awardsAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	Zend_Debug::dump($this->_facebook_user);
    	$this->view->facebook_user = $this->_facebook_user;
    	$this->render("awards");
    }
    
    public function myprofileAction() {
    	$this->_helper->layout->disableLayout();
    	//check for user authorization
    	$user = new Model_FacebookUsers();
    	$user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    	}else {
    		$this->view->facebook_user = null;
    	}
    }
    
    protected function getFeed($access_token) {
    	
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/me/feed');
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $access_token);
    	 
    	$response = $client->request();
    	 
    	$data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	 
    	return $data;
    }
    
    public function logoutAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_auth = Zend_Auth::getInstance();
    	if($this->_auth->hasIdentity()) {
    		$this->_identity = $this->_auth->clearIdentity();
    	}
    	
    	//$this->_helper->redirector('login', $this->getRequest()->getControllerName(), $this->getRequest()->getModuleName(), array($this->_getParam('id') => null));
    	$this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));
    }
}

