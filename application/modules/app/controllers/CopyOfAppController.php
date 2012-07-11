<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        //$this->data = $this->getSignedRequest($this->_getParam('signed_request'));

        try {
        	$this->data['page']['id'] = Zend_Registry::get('fanpageId');
        
        } catch (Exception $e) {
        	//TOLOG
        	$this->data['page']['id'] = $this->_getParam('id');
        }
        
        if (APPLICATION_ENV != 'production') {
        	$this->data['page']['id'] = $this->_request->getParam('fanpage_id');
        	$this->view->fanpage_id = $this->_request->getParam('fanpage_id');
        	//$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
        	$this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
        }
        
        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('index', 'app', 'app', array($this->data['page']['id'] => ''));   
        }
	
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
    }
	
    public function indexAction()
    {
    	//$this->_forward('newsfeed');
    $user = new Model_FacebookUsers();
  
    	
   		
    	$user = $user->find($this->data['user_id'])->current();
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	
    	
    	$colorChoice = new Model_UsersColorChoice;
    	$c = $this->_request->getParam('colorChange');
    	if(!is_null($c)){
    		$colorChoice ->change(1, $c );
    	}
    	$color = $colorChoice ->getColorChoice(1);
    	$this->render('newsfeed');
    	
    }

  	public function topfansAction()
  	{
  		$this->_helper->layout->disableLayout();
  		$this->view->fanpage_id = $this->data['page']['id'];
  		$post = new Model_Posts;
  		$model = new Model_Rankings;
   		$topFans = $model->getTopFans($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topFans);
    	 
    	$mostPopular = $model->getMostPopular($this->data['page']['id'], 5);
    	//Zend_Debug::dump($mostPopular);
    	 
    	$topTalker = $model->getTopTalker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topTalker);
    	 
    	$topClicker = $model->getTopClicker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topClicker);
    	$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    	//exit();
    	$this->view->top_fans = $topFans;
    	$this->view->most_popular = $mostPopular;
    	$this->view->top_talker = $topTalker;
    	$this->view->top_clicker = $topClicker;
    	$this->view->latest_post = $latestPost;
    	
    	
    	$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
    	$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
    	$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
    	$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
  	}

    public function newsfeedAction() 
    {
    	$this->_helper->layout->disableLayout();
    
    	$model = new Model_Rankings;
    	
   		
    	
    	
    
    	$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    	//Zend_Debug::dump($user); exit();
    	$this->view->top_post = $topPosts;
    	$this->render("newsfeed");
    }
    
    public function awardsAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	Zend_Debug::dump($this->facebook_user);
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
    
    public function toppostAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$result = array();
    	try {
    		$topPosts = new Model_Rankings();
    		$result = $topPosts->getTopPosts($this->data['page']['id']);
    		Zend_Debug::dump($result);
    		return $result;
    	} catch (Exception $e) {
    		return array();
    	}
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

