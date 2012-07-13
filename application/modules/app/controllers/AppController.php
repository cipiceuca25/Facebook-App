<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
    public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        //$this->data = $this->getSignedRequest($this->_getParam('signed_request'));
        $view = new Zend_View;
        $view->addHelperPath(APPLICATION_PATH.'/modules/app/views/helper','Helper');
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
        	$this->data['user_id'] = $this->_getParam('facebook_user_id'); //set test user id from url
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
    	$this->_helper->layout->setLayout('default_layout2');
    	
    	$model = new Model_Rankings;
    	
    	$colorChoice = new Model_UsersColorChoice;
    	
    	
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
    	
    	
    	//$topFans = $model->getTopFans($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topFans);
    	 
    	//$mostPopular = $model->getMostPopular($this->data['page']['id'], 5);
    	//Zend_Debug::dump($mostPopular);
    	 
    	//$topTalker = $model->getTopTalker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topTalker);
    	 
    	//$topClicker = $model->getTopClicker($this->data['page']['id'], 5);
    	//Zend_Debug::dump($topClicker);
    	
    	$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    	
    	//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    	
    	
    	
    	$color = $colorChoice ->getColorChoice(1);
    	
    	//exit();
    	//$this->view->top_fans = $topFans;
    	//$this->view->most_popular = $mostPopular;
    	//$this->view->top_talker = $topTalker;
    	//$this->view->top_clicker = $topClicker;
    	$this->view->top_post = $topPosts;
    	//$this->view->latest_post = $latestPost;
    	
    
    	
    	$this->view->color_choice = $color->color_choice;
    	
    	
    	
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
		
    	$this->render('newsfeed');
    }
    




  	public function topfansAction()
  	{	
  		
  		$this->_helper->layout->disableLayout();
  		
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
  		
  		$this->view->fanpage_id = $this->data['page']['id'];
  		
  		$follow = new Model_Subscribes();
  		$model = new Model_Rankings;
    	$post = new Model_Posts;
    	
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
    	//echo ($user['facebook_user_id']);
    	 

    	$topArray = NULL;
    	$popularArray = NULL;
    	$talkerArray = NULL;
    	$clickerArray = NULL;
    	$count=0;
    	foreach ($topFans as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($user->facebook_user_id, $top['facebook_user_id']);
    		//echo $topArray[$count];
    		$count++;
    		
    	}    	 
    	$count=0;
    	foreach ($mostPopular as $mp){
    		//echo $top['facebook_user_id'];
    		$popularArray[$count] = $follow->getRelation($user->facebook_user_id, $mp['facebook_user_id']);
    		//echo $topArray[$count];
    		$count++;
    	
    	}    	
    	$count=0;
    	foreach ($topTalker as $tt){
    		//echo $top['facebook_user_id'];
    		$talkerArray[$count] = $follow->getRelation($user->facebook_user_id, $tt['facebook_user_id']);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
  		$count=0;
    	foreach ($topClicker as $tc){
    		//echo $top['facebook_user_id'];
    		$clickerArray[$count] = $follow->getRelation($user->facebook_user_id, $tc['facebook_user_id']);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	
    	$this->view->topFanArray = $topArray ;
    	$this->view->popularArray = $popularArray ;
    	$this->view->talkerArray = $talkerArray ;
    	$this->view->clickerArray = $clickerArray ;
    	
    	$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
    	$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
    	$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
    	$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
    	$this->render("topfans");
  	}

  	
    public function newsfeedAction() 
    {	
    	$this->_helper->layout->disableLayout();
    	
    	
    	
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
    	/*
    	$user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    		$access_token = $this->_facebook_user->facebook_user_access_token;
    		$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	*/
    
    	$model = new Model_Rankings;
    	$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    	//Zend_Debug::dump($user); exit();
    	$this->view->top_post = $topPosts;
    	$this->render("newsfeed");
    }
    
    public function commentAction()
    {
    	$this->_helper->layout->disableLayout();
    	   	/*
    	$user = new Model_FacebookUsers();
    	$target = new Model_FacebookUsers();
    	$post = new Model_Posts() ;
    	$comment = new Model_Comments() ;
    	
    	$user = $user->find($this->data['user_id'])->current();
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	
    	$target = $target->find( $this->_request->getParam('target'))->current();
    	//Zend_Debug::dump($user);
    	if($target) {
    		$this->view->target = $target;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->target = null;
    	}
    	
    	$comment = $comment->getCommentsByPostId($this->_request->getParam('post_id') , 5);
    	
    	$post = $post->find( $this->_request->getParam('post_id'))->current();
    	*/
    	/*
    	 $user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    	$this->view->facebook_user = $user;
    	$access_token = $this->_facebook_user->facebook_user_access_token;
    	$this->view->feed = $this->getFeed($access_token);
    	}else {
    	$this->view->facebook_user = null;
    	}
    	*/
    
    	
    	//$model = new Model_Rankings;
    	//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    	//Zend_Debug::dump($user); exit();
    	//$this->view->post = $post;
    	//$this->view->comment = $comment;
    	
    	
    	
    	
    	$this->render("comment");
    }
    
    public function awardsAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	//Zend_Debug::dump($this->_facebook_user);
    	//$this->view->facebook_user = $this->_facebook_user;
    	
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
    	 
    	
    	$this->render("awards");
    }
    
    public function myprofileAction() {

    	$this->_helper->layout->disableLayout();
    	//check for user authorization
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

    	
    	$follow = new Model_Subscribes();
    	$follower = $follow->getFollowers($user->facebook_user_id);
    	$following = $follow->getFollowing($user->facebook_user_id);
    	$friends = $follow->getFriends($user->facebook_user_id);
    	
    	$this->view->following = $following;
    	$this->view->follower = $follower;
    	$this->view->friends = $friends;
    	
    	$this->render("myprofile");
    }
    
    public function userprofileAction() {
    
    	$this->_helper->layout->disableLayout();
    	//check for user authorization
    	$user = new Model_FacebookUsers(); // the person the user is looking at
    	$user2 = new Model_FacebookUsers(); // the actual user
    	
    	$user = $user->find( $this->_request->getParam('target'))->current();
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	
    	$user2 = $user2->find( $this->_request->getParam('facebook_user_id'))->current();
    	//Zend_Debug::dump($user);
    	if($user2) {
    		$this->view->facebook_user2 = $user2;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user2 = null;
    	}
    	
    	$follow = new Model_Subscribes();
    	
    	$follower = $follow->getFollowers($user->facebook_user_id);
    	$following = $follow->getFollowing($user->facebook_user_id);
    	$friends = $follow->getFriends($user->facebook_user_id);
    	$relation = $follow->getRelation($user2->facebook_user_id, $user->facebook_user_id);
    
    	$this->view->following = $following;
    	$this->view->follower = $follower;
    	$this->view->friends = $friends;
    	$this->view->relation = $relation;
    	 
    	$this->render("userprofile");
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
    		$result = $topPosts->getTopPosts($this->_getParam('fanpage_id'));
    		Zend_Debug::dump($result);
    		return $result;
    	} catch (Exception $e) {
    		return array();
    	}
    }
    
    public function fancrankfeedAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$feed = new Model_FancrankActivities();
    	//we should implement with memcache here 
    	Zend_Debug::dump($feed->getFeed(5));	
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

