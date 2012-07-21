<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    app
 * @package     app
 * @copyright   Copyright (c) 2012 Francrank
 * @license     
 */
class App_AppController extends Fancrank_App_Controller_BaseController
{
	protected $_fanpageId;
	protected $_userId;
	protected $_accessToken;

	/**
	 * Initilized fanpage id and login user variables
	 */
	public function preDispatch() {
		parent::preDispatch();
		if (APPLICATION_ENV != 'production') {
			$this->_fanpageId = $this->_getParam('id');
			if(empty($this->_facebook_user->facebook_user_id)) {
				$this->_userId = $this->_getParam('user_id'); //set test user id from url
			}else {
				$this->_userId = $this->_facebook_user->facebook_user_id;
			}
		}else {
			$this->_userId = $this->_facebook_user->facebook_user_id;
			if (isset($_REQUEST['signed_request'])) {
				$fb = new Service_FancrankFBService();
				$this->_fanpageId = $fb->getFanPageId();
				// Zend_Debug::dump($fb->getSignedData());
			} else {
				$this->_fanpageId = $this->_getParam('id');
			}
		}

		if(!empty($this->_fanpageId)) {
			$token = new Model_Fanpages();
			$token = $token->find($this->_fanpageId)->current();
			//Zend_Debug::dump($token);
			$this->_accessToken = $token ->access_token;			
		}

		//echo $token ->access_token;
		$this->view->facebook_user_access_token = $this->_facebook_user->facebook_user_access_token;
		$this->view->access_token = $this->_accessToken;
		$this->view->fanpage_id = $this->_fanpageId;
		$this->view->user_id = $this->_userId;
	}

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout2');
    	$model = new Model_Rankings;
    	$colorChoice = new Model_UsersColorChoice;
    	$user = new Model_FacebookUsers();
    	$user = $user->find($this->_userId)->current();
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
    	
    	$topPosts = $model->getTopPosts($this->_fanpageId, 5);
    	
    	//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    	
    	$color = $colorChoice ->getColorChoice($this->_fanpageId);
    	
    	//exit();
    	//$this->view->top_fans = $topFans;
    	//$this->view->most_popular = $mostPopular;
    	//$this->view->top_talker = $topTalker;
    	//$this->view->top_clicker = $topClicker;
    	$this->view->top_post = $topPosts;
    	//$this->view->latest_post = $latestPost;
    	$this->view->color_choice = $color->color_choice;

   		$user = new Model_FacebookUsers();	
   		
    	$user = $user->find($this->_userId)->current();
    	//Zend_Debug::dump($this->_userId);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	
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

    /* Action to retrieve top five fans by default */
  	public function topfansAction()
  	{	
  		
  		$this->_helper->layout->disableLayout();
  		
  		$user = new Model_FacebookUsers();
  		$user = $user->find($this->_userId)->current();
  		//Zend_Debug::dump($user);
  		if($user) {
  			$this->view->facebook_user = $user;
  			//$access_token = $this->facebook_user->facebook_user_access_token;
  			//$this->view->feed = $this->getFeed($access_token);
  		}else {
  			$this->view->facebook_user = null;
  		}
  		
  		$this->view->fanpage_id = $this->_fanpageId;
  		
  		$follow = new Model_Subscribes();
  		$model = new Model_Rankings;
    	
   		$topFans = $model->getTopFans($this->_fanpageId, 5);
    	//Zend_Debug::dump($topFans);
    	 
    	$mostPopular = $model->getMostPopular($this->_fanpageId, 5);
    	//Zend_Debug::dump($mostPopular);
    	 
    	$topTalker = $model->getTopTalker($this->_fanpageId, 5);
    	//Zend_Debug::dump($topTalker);
    	 
    	$topClicker = $model->getTopClicker($this->_fanpageId, 5);
    	//Zend_Debug::dump($topClicker);

    	//exit();
    	$this->view->top_fans = $topFans;
    	$this->view->most_popular = $mostPopular;
    	$this->view->top_talker = $topTalker;
    	$this->view->top_clicker = $topClicker;
    
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
    	
    	$this->view->user_top_fans = $model->getUserRanking($this->_fanpageId, 'FAN', $this->_userId);
    	$this->view->user_most_popular = $model->getUserRanking($this->_fanpageId, 'POPULAR', $this->_userId);
    	$this->view->user_top_talker = $model->getUserRanking($this->_fanpageId, 'TALKER', $this->_userId);
    	$this->view->user_top_clicker = $model->getUserRanking($this->_fanpageId, 'CLICKER', $this->_userId);
    	
    	$this->render("topfans");
  	}

  	/* Action to show login user's wall post */
    public function newsfeedAction() 
    {	
    	$this->_helper->layout->disableLayout();

    	
    	$user = new Model_FacebookUsers();
    	 
    	$user = $user->find($this->_userId)->current();
    	//Zend_Debug::dump($this->_userId);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}	

    	
    	$fanpage = new Model_Fanpages();
    	$fanpage = $fanpage-> find($this->_fanpageId)->current();
   
    	/*
   		$user = new Model_FacebookUsers();
    	
    	$user = $user->find($this->_userId)->current();
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	$user = $user->find($this->_facebook_user->facebook_user_id)->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    		$access_token = $this->_facebook_user->facebook_user_access_token;
    		$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	*/
    	$fanpage = new Model_Fanpages();
    	$fanpage = $fanpage-> find($this->_fanpageId)->current();
    	//Zend_Debug::dump($fanpage);
    	$this->view->fanpage_name = $fanpage->fanpage_name;
    	$this->view->fanpage_id = $this->_fanpageId;
    	$this->render("newsfeed");
    }
    
    public function gettoppostAction(){
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$model = new Model_Rankings;
    	$topPosts = $model->getTopPosts($this->_fanpageId, 5);
    	//Zend_Debug::dump($user); exit();
    	
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$count=0;
    	
    	foreach ($topPosts as $posts){
    		//echo $top['facebook_user_id'];
    		$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts['post_id'], $this->_userId );
    		//echo $likes[$count];
    		$count++;
    	}

    	$this->view->likes = $likes;
    	$this->view->top_post = $topPosts;

    	$this->render("gettoppost");
    }
    
 
    
    
    public function getlatestpostAction(){
    	
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
   		$post = new Model_Posts;
    	$latestPost = $post ->getLatestPost($this->_fanpageId,5);
    	
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$count=0;
    	foreach ($latestPost as $posts){
    		//echo $top['facebook_user_id'];
    		$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts['post_id'], $this->_userId );
    		//echo $likes[$count];
    		$count++;
    	}
    	 
    	$this->view->likes = $likes;

    	$this->view->latest_post = $latestPost;
    	
    	$this->render("getlatestpost");
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
    	
    	$user = $user->find($this->_userId)->current();
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
   		
    	$user = $user->find($this->_userId)->current();
    	//Zend_Debug::dump($this->_userId);
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

    public function fancrankfeedAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$viewAs = $this->_request->getParam('viewAs');
    	$result = array();
    	$result = $this->getFeed(8, $viewAs);
    	//$result = json_encode($result);
    	//Zend_Debug::dump($result);
    	
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$count=0;
    	foreach ($result as $posts){
    		//echo $top['facebook_user_id'];
    		$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    		//echo $likes[$count];
    		$count++;
    	} 
    	
    	$this->view->likes = $likes;
    	$this->view->post = $result;
    	
    	$this->render("fancrankfeed");
    }
    
    
    public function fancrankfeedcommentAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$postId = $this->_request->getParam('post_id');

    	$limit = $this->_request->getParam('limit');
    	$total = $this->_request->getParam('total');
    	$result = array();
    	$result = $this->getFeedComment($postId, $limit);
    	//$result = json_encode($result);

    	$likesModel = new Model_Likes();
    	$likes = array();
    	$count=0;

    	if(!empty($result)) {
    		foreach ($result as $posts){
    			//echo $top['facebook_user_id'];
    			$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    			//echo $likes[$count];
    			$count++;
    		}    		
    	}
    	 
    	//$postTop = explode('_', $postId);
    	//$postTop = $postTop[0].'_'.$postTop[1];
    	
    	//$this->view->postTopId = $postTop;
    	//$this->view->postTopName =$this-> getOwnerOfPost($postTop);
    	
    	$this->view->likes = $likes;
    	$this->view->postOwner = $this->getOwnerOfPost($postId);
    	//Zend_debug::dump($this->getOwnerOfPost($postId));
    	$this->view->total = $total;
    	$this->view->limit = $limit;
    	$this->view->comments = $result;
    	$this->view->postId = $postId;

    	$this->render("fancrankfeedcomment");
    }
    
    public function recentactivitiesAction(){
    	$this->_helper->layout->disableLayout();
    	
    	$activities = new Model_FancrankActivities();
    	$activities = $activities -> getRecentActivities($this->_userId, $this->_fanpageId, 10);
    	
    	$this->view->activities = $activities;
    	
    	//Zend_Debug::dump($activities);
    	
    	$this->render("recentactivities");
    }
    
    protected function getOwnerOfPost($postId){
    	
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $postId);
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);

    	
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	if(!empty ($result)) {
    	
    		return array( 'user_id' => $result->from->id, 'user_name' => $result->from->name);
    	}
    	
    }
    
    //this grabs the comments for a specific post and returns the data to fancrankfeedcommentAction
    protected function getFeedComment($postId, $limit) {

    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $postId ."/comments");
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);
    	$client->setParameterGet('limit', $limit);
    
    	$response = $client->request();
    
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	 
    	if(!empty ($result->data)) {    

    		return $result->data;
    	}
    }
    
    //this grabs the facebook feed for the current fanpage id and returns the list to fancrankAction 
	protected function getFeed($limit, $view) {

    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/feed");
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);
    	$client->setParameterGet('limit', $limit);
    	 
    	$response = $client->request();
    	 
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	if(!empty ($result->data)) {

    		switch ($view){
    			case 'admin':
    				$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/posts");
     				$response = $client->request();
    				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    				//Zend_Debug::dump($result->data);
    				return $this->feedFilterByAdmin($result->data, $this->_fanpageId);
    			case 'all':
    					return $result->data;
    			case 'user':
    				return $this->feedFilterByUser($result->data, $this->_fanpageId);
    			
    			default:
    				return $result->data;
    		}
    		return $result->data;
    	}
    }
    
    protected function feedFilterByAdmin($data, $fanpageId) {
    	$result = array();
    	foreach ($data as $value) {
			if(!empty($value->from->id) && $value->from->id === $fanpageId) {
				$result[] = $value;
			}
    	}
    	return $result;	
    }
    
    protected function feedFilterByUser($data, $fanpageId) {
    	$result = array();
    	foreach ($data as $value) {
    		if(!empty($value->from->id) && $value->from->id !== $fanpageId) {
    			$result[] = $value;
    		}
    	}
    	return $result;
    }
    
    public function toppostAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$result = array();
    	try {
    		$topPosts = new Model_Rankings();
    		$result = $topPosts->getTopPosts($this->_fanpageId);
    		Zend_Debug::dump($result);
    		return $result;
    	} catch (Exception $e) {
    		return array();
    	}

    }
 
    /**
     * 
     * ACTIVITIES FEED
		case 'user':
    					$feed = new Model_FancrankActivities();
    					//we should implement with memcache here
    					$result = $feed->getFeed(5);
    					break;
	
	
	**/
    /*
     public function fancrankfeedAction() {
    $this->_helper->layout->disableLayout();
    //$this->_helper->viewRenderer->setNoRender(true);
    $viewAs = $this->_request->getParam('viewAs');
    $result = array();
    
    $result = $this->getFeed($this->_request->getParam('fanpage_id'), $this->_request->getParam('access_token'),8, $viewAs);
    //$result = json_encode($result);
    //Zend_Debug::dump($result);
    $this->view->post = $result;
    $this->render("fancrankfeed");
    }*/

    
    /*

    public function fancrankfeedAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$viewAs = $this->_getParam('viewAs');
    	$result = array();
    	$result = $this->getFeed($this->_request->getParam('fanpage_id'), $this->_request->getParam('access_token'),8, $viewAs);
    	
    	$this->view->post = $result;
    	$this->render("fancrankfeed");
	}
    */

    public function adminfeedAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$result = $this->getAdminFeed($this->_getParam('id'), $this->_getParam('access_token'), 5);
    	
    	$this->_helper->json($result);
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
    	//$this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));
    	$this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));
    }
    
    public function insightsAction() 
    {
    	$this->_helper->layout->setLayout('insights_layout');
    }
}

