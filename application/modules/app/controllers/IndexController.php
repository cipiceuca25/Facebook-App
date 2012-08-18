<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
    public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        //$this->data = $this->getSignedRequest($this->_getParam('signed_request'));
        if (APPLICATION_ENV != 'production') {
            $this->data['page']['id'] = $this->_request->getParam('id');
            $this->view->fanpage_id = $this->_request->getParam('id');
            //$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
            $this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
            $this->view->user_id = $this->data['user_id'];
            $this->view->access_token = $this->_getParam('access_token');
            //Zend_debug::dump($this->data['user_id']);
        }else {
            if (isset($_REQUEST['signed_request'])) {
                $fb = new Service_FancrankFBService();
                $this->data['page']['id']= $fb->getFanPageId();
                $this->data['user_id']=$fb->getFanPageUserId();
                $this->view->user_id = $this->data['user_id'];
                // Zend_Debug::dump($fb->getSignedData());
            } else {
                $this->data['page']['id'] = $this->_getParam('id');
            }
			
			$this->view->user_id = $this->data['user_id'];
			$this->view->fanpage_id = $this->data['page']['id'];
        }
        
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
			$this->_redirect('/app/app/index/' .$this->data['page']['id']);   
		}
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout');
    	$model = new Model_Rankings;
    	//$post = new Model_Posts;
    	$follow = new Model_Subscribes();
    	//$colorChoice = new Model_UsersColorChoice;
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
    	
    
    	
    	$topFans = $model->getTopFans($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topFans);
    	 
    	$mostPopular = $model->getMostPopular($this->data['page']['id'], 5);
    	//Zend_Debug::dump($mostPopular);
    	 
    	$topTalker = $model->getTopTalker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topTalker);
    	 
    	$topClicker = $model->getTopClicker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topClicker);
    	
    	//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    	$topFollowed = $follow->getTopFollowed($this->data['page']['id'], 5);
    	//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);

   		
    	//Zend_Debug::dump($color); exit();
    	//$this->view->user_name= $this->getUserName();
    	$this->view->top_fans = $topFans;
    	$this->view->most_popular = $mostPopular;
    	$this->view->top_talker = $topTalker;
    	$this->view->top_clicker = $topClicker;
    	$this->view->top_followed = $topFollowed;
    	//$this->view->top_post = $topPosts;
    	//$this->view->latest_post = $latestPost;
    	
    	//$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
    	//$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
    	//$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
    	//$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
    	
    	/*
    	$this->view->top_fans = $model->getRanking($this->data['page']['id'], 'FAN', false, 5);
    	$this->view->most_popular = $model->getRanking($this->data['page']['id'], 'POPULAR', false, 5);
    	$this->view->top_talker = $model->getRanking($this->data['page']['id'], 'TALKER', false, 5);
    	$this->view->top_clicker = $model->getRanking($this->data['page']['id'], 'CLICKER', false, 5);

    	$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
    	$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
    	$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
    	$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
    	*/
		//$this->_helper->redirector('login', 'index', 'app', array($this->data['page']['id'] => ''));
		
    	
    }
    
    protected function getUserName(){
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $this->data['user_id']);
    	$client->setMethod(Zend_Http_Client::GET);
 		
    	
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	if(!empty ($result)) {
    		
    		return $result->first_name;
    	}
    	
    	
    }
    
}

