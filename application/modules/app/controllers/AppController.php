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
			$fanpage = new Model_Fanpages();
			$fanpage = $fanpage->find($this->_fanpageId)->current();
			//Zend_Debug::dump($token);
			$this->_accessToken = $fanpage ->access_token;
			$this->view->fanpage_name = $fanpage->fanpage_name;			
		}else {
			//$this->_redirect('http://www.fancrank.com');
		}
		
// 		$name = new Model_FacebookUsers();
// 		$name = $name->find($this->_userId)->current();
// 		$this->view->username = $name->facebook_user_first_name.' '.$name->facebook_user_last_name;
		//echo $token ->access_token;
		$this->view->username = $this->_facebook_user->facebook_user_name;
		$this->view->facebook_user_access_token = $this->_facebook_user->facebook_user_access_token;
		//$this->view->access_token = $this->_accessToken;
		$this->view->fanpage_id = $this->_fanpageId;
		$this->view->user_id = $this->_userId;
		if(isset($this->_facebook_user->fancrankAppTour)) {
			$this->view->tour = $this->_facebook_user->fancrankAppTour;
		}else {
			$this->_facebook_user->fancrankAppTour = 0;
		}
	}

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout2');
    	//$this->render('newsfeed');
    }

    public function topfanAction()
    {
    	$this->_helper->layout->disableLayout();
    
    	//$user = new Model_FacebookUsers();
    	//$user = $user->find($this->_userId)->current();
    	$user = $this->_facebook_user;
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    
    	$this->view->fanpage_id = $this->_fanpageId;
    	$model = new Model_Rankings;
    	$follow = new Model_Subscribes();
    	$fanpage = array();
    	
    	if(!empty($this->_fanpageId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    	
    		try {
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($this->_fanpageId)){
    				//echo 'db look up';
    				//Look up the $fanpageId
    				$fanpage['topFans'] = $model->getTopFans($this->_fanpageId, 5);
    	
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($fanpage, $this->_fanpageId);
    			}else {
    				//echo 'memcache look up';
    				$fanpage = $cache->load($this->_fanpageId);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
   	 
    	$topFans = $fanpage['topFans'];
    	$this->view->top_fans = $topFans;
    	$topArray = NULL;
   
    	$count=0;
    	foreach ($topFans as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($user->facebook_user_id, $top['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    
    	}
    	
    	$userLeaderBoardData = array();
    	 
    	if(!empty($this->_fanpageId) && !empty($user->facebook_user_id)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    		 
    		try {
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$user->facebook_user_id)){
    				//Look up the $fanpageId
    				$userLeaderBoardData['topFans'] = $model->getUserTopFansRank($this->_fanpageId, $user->facebook_user_id);
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($userLeaderBoardData, $this->_fanpageId .'_' .$user->facebook_user_id);
    			}else {
    				//echo 'memcache look up';
    				$userLeaderBoardData = $cache->load($this->_fanpageId .'_' .$user->facebook_user_id);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}

    	$this->view->topFanYou =  $userLeaderBoardData['topFans'];
    	$this->view->topFanArray = $topArray ;

    	$this->render("topfan");
    }
    
    
    
    /* Action to retrieve top five fans by default */
  	public function leaderboardAction()
  	{	
  		
  		$this->_helper->layout->disableLayout();
  		
  		$user = $this->_facebook_user;
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
  		$fanpage = array(
  				'topFans'=>array(),
  				'mostPopular'=>array(),
  				'topTalker'=>array(),
  				'topClicker'=>array(),
  				'topFollowed'=>array()
  		);
  		
  		if(!empty($this->_fanpageId)) {
  			$cache = Zend_Registry::get('memcache');
  			$cache->setLifetime(3600);
  		
  			try {
  		
  				//Check to see if the $fanpageId is cached and look it up if not
  				if(isset($cache) && !$cache->load($this->_fanpageId)){
  					//echo 'db look up';
  					//Look up the $fanpageId
  					$fanpage['topFans'] = $model->getTopFans($this->_fanpageId, 5);
  					//Zend_Debug::dump($topFans);
  		
  					$fanpage['mostPopular'] = $model->getMostPopular($this->_fanpageId, 5);
  					//Zend_Debug::dump($mostPopular);
  		
  					$fanpage['topTalker'] = $model->getTopTalker($this->_fanpageId, 5);
  					//Zend_Debug::dump($topTalker);
  		
  					$fanpage['topClicker'] = $model->getTopClicker($this->_fanpageId, 5);
  					//Zend_Debug::dump($topClicker);
  						
  					//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
  					$fanpage['topFollowed'] = $follow->getTopFollowed($this->_fanpageId, 5);
  					//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
  		
  					//Save to the cache, so we don't have to look it up next time
  					$cache->save($fanpage, $this->_fanpageId);
  				}else {
  					//echo 'memcache look up';
  					$fanpage = $cache->load($this->_fanpageId);
  				}
  			} catch (Exception $e) {
  				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
  				//echo $e->getMessage();
  			}
  		}
  		
    	//exit();
    	$this->view->top_fans = $fanpage['topFans'];
    	$this->view->most_popular = $fanpage['mostPopular'];
    	$this->view->top_talker = $fanpage['topTalker'];
    	$this->view->top_clicker = $fanpage['topClicker'];
    	$this->view->top_followed = $fanpage['topFollowed'];

    	$topArray = NULL;
    	$popularArray = NULL;
    	$talkerArray = NULL;
    	$clickerArray = NULL;
    	$followedArray = NULL;
    	$count=0;
    	foreach ($fanpage['topFans'] as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($user->facebook_user_id, $top['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		
    	}    	 
    	$count=0;
    	foreach ($fanpage['mostPopular'] as $mp){
    		//echo $top['facebook_user_id'];
    		$popularArray[$count] = $follow->getRelation($user->facebook_user_id, $mp['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    	
    	}    	
    	$count=0;
    	foreach ($fanpage['topTalker'] as $tt){
    		//echo $top['facebook_user_id'];
    		$talkerArray[$count] = $follow->getRelation($user->facebook_user_id, $tt['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
  		$count=0;
    	foreach ($fanpage['topClicker'] as $tc){
    		//echo $top['facebook_user_id'];
    		$clickerArray[$count] = $follow->getRelation($user->facebook_user_id, $tc['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	$count=0;
    	foreach ($fanpage['topFollowed'] as $tf){
    		//echo $top['facebook_user_id'];
    		$followedArray[$count] = $follow->getRelation($user->facebook_user_id, $tf['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	
    	$userLeaderBoardData = array();
    	
    	if(!empty($this->_fanpageId) && !empty($user->facebook_user_id)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    	
    		try {
    	
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$user->facebook_user_id)){
    				//Look up the $fanpageId
    				$userLeaderBoardData['topFans'] = $model->getUserTopFansRank($this->_fanpageId, $user->facebook_user_id);
    	
    				$userLeaderBoardData['mostPopular'] = $model->getUserMostPopularRank($this->_fanpageId, $user->facebook_user_id);
    	
    				$userLeaderBoardData['topTalker'] = $model->getUserTopTalkerRank($this->_fanpageId, $user->facebook_user_id);
    				//Zend_Debug::dump($topTalker);
    	
    				$userLeaderBoardData['topClicker'] = $model->getUserTopClickerRank($this->_fanpageId, $user->facebook_user_id);
    				//Zend_Debug::dump($topClicker);
    	
    				//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    				$userLeaderBoardData['topFollowed'] = $follow->getTopFollowedRank($this->_fanpageId, $user->facebook_user_id);
    				//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    	
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($userLeaderBoardData, $this->_fanpageId .'_' .$user->facebook_user_id);
    			}else {
    				//echo 'memcache look up';
    				$userLeaderBoardData = $cache->load($this->_fanpageId .'_' .$user->facebook_user_id);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	 
    	$this->view->topFanYou =  $userLeaderBoardData['topFans'];
    	$this->view->popularYou = $userLeaderBoardData['mostPopular'];
    	$this->view->talkerYou = $userLeaderBoardData['topTalker'];
    	$this->view->clickerYou = $userLeaderBoardData['topClicker'];
    	$this->view->followedYou = $userLeaderBoardData['topFollowed'];
    	
    	$this->view->topFanArray = $topArray ;
    	$this->view->popularArray = $popularArray ;
    	$this->view->talkerArray = $talkerArray ;
    	$this->view->clickerArray = $clickerArray ;
    	$this->view->followedArray = $followedArray ;
    	 
    	$this->render("leaderboard");
  	}

  	/* Action to show login user's wall post */
    public function newsfeedAction() 
    {	
    	$this->_helper->layout->disableLayout();

		$this->view->facebook_user = $this->_facebook_user;		    	
   
		$result = $this->feedFirstQuery();

		$latest = $result['posts']->data;
		$feed = $result['feed']->data;
	
    	$likesModel = new Model_Likes();
    	$latestlike = array();
    	$count=0;
    	//Zend_Debug::dump($result);
    	
    	if ($latest != null ){
    		$latestlike = $likesModel->getLikes($this->_fanpageId, $latest[0]->id, $this->_userId );
    	}
    	//Zend_debug::dump($latest);
    	$this->view->latestlike = $latestlike;
    	$this->view->latest = $latest ;
    	

    	$follow = new Model_Subscribes();
    	$likes = array();
    	$relation = array();
    	$count=0;
    	
    	if ($feed != null){
    		
    			foreach ($feed as $posts){
    				//echo $top['facebook_user_id'];
    				$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    				$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
    				//echo $likes[$count];
    				$count++;
    	
    			}
    		
    	}
    	
    	$this->view->relation = $relation;
    	
    	$this->view->likes = $likes;
    	$this->view->post = $feed;
    	//Zend_Debug::dump($fanpage);
    	$this->view->fanpage_id = $this->_fanpageId;
    	
    	$this->render("newsfeed");
    }
    
    public function gettoppostAction(){
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$model = new Model_Rankings;
    	$topPost = array();
    	if(!empty($this->_fanpageId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    		$topPostId = $this->_fanpageId .'_toppost';
    		try {
    	
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($topPostId)){
    				//echo 'db look up';
    				$topPosts = $model->getTopPosts($this->_fanpageId, 5);
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($topPosts, $topPostId);
    			}else {
    				//echo 'memcache look up';
    				$topPosts = $cache->load($topPostId);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
	   	}
    	 
    	//Zend_Debug::dump($user); exit();

    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$relation = array();
    	//$picture = array();
    	$count=0;
		//Zend_Debug::dump($topPosts);
    	foreach ($topPosts as $posts){
    		//echo $top['facebook_user_id'];
    		$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts['post_id'], $this->_userId );
    		//echo $likes[$count];
    		$relation[$count] = $follow->getRelation($this->_userId, $posts['facebook_user_id'],$this->_fanpageId);
    		//$pic = $this->getPost($posts['post_id']);
    		//Zend_Debug::dump($pic);
    		//if (($pic->type == 'photo') ||($pic->type == 'video')  ){
    		//	$picture[$count] = $pic -> picture;
    		//}else{
    		//	$picture[$count] = null;
    		//}
    		$count++;
    	}
		
    	
    	
    	
    	//Zend_Debug::dump($topPosts);
    //	$this->view->picture = $picture;
    	$this->view->likes = $likes;
    	$this->view->top_post = $topPosts;
    	$this->view->relation = $relation;
    	$this->render("gettoppost");
    }
    
/*
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
    
    */
    
    public function popuppostAction()
    {
    	$this->_helper->layout->disableLayout();

    	$postId = $this->_request->getParam('post_id');
    	
    	$limit = $this->_request->getParam('limit');
    	//$total = $this->_request->getParam('total');
    	
    	//$originalPost = $this->getPost($postId);
    	$post= $this->getPost($postId);
    	
    	$result = array();
    	$result = $this->getFeedComment($postId, $limit);
    	//$result = json_encode($result);
    	
    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();
    	$likesPost;
    	$likes = array();
    	$relation = array();
    	$count=0;
    	$likes[$count]=null;
    	$relation[$count] =$follow->getRelation($this->_userId, $post->from->id,$this->_fanpageId);
    	
    	$count=1;
    	if(!empty($result)) {
    		foreach ($result as $posts){
    			//echo $top['facebook_user_id'];
    			$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    			$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
    			//echo $likes[$count];
    			$count++;
    		}
    	}
    	
    	$likesPost = $likesModel->getLikes($this->_fanpageId, $postId, $this->_userId );
    	//$postTop = explode('_', $postId);
    	//$postTop = $postTop[0].'_'.$postTop[1];
    	 
    	//$this->view->postTopId = $postTop;
    	//$this->view->postTopName =$this-> getOwnerOfPost($postTop);
    	$this->view->post = $post;
    	$this->view->likepost = $likesPost;
    	$this->view->relation = $relation;
    	$this->view->likes = $likes;
    	//$this->view->postOwner = $this->getOwnerOfPost($postId);
    	//Zend_debug::dump($this->getOwnerOfPost($postId));
    	//$this->view->total = $post->'comments'->'count';
    	$this->view->limit = $limit;
    	$this->view->comments = $result;
    	$this->view->userid = $this->_userId;
    	//$this->view->postId = $postId;    	

    	
    	$this->render("popuppost");
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
    	$userBadges = new Model_BadgeEvents();
    	$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $user->facebook_user_id);
    	
    	//Zend_Debug::dump($userBadgeCount);
    	$allBadges = new Model_Badges();
    	$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    	$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	 
    	 
    	$this->view->user_badge = $userBadgeCount[0]['count'];
    	$this->view->all_badge = $allBadges[0]['count'];
    	$this->view->overall_achievement = $overallAchievement;
    	
    	//$badges = new Model_Badges();
    	
    	$badges = $this->badgeArray($this->_fanpageId, $user->facebook_user_id);
    	//Zend_Debug::dump($badges);
    	
    	$this->view->badges = $badges;
    	
    	$this->render("awards");
    }
    
    public function redeemAction() {
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
    	 
    	$this->render("redeem");
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
    		// aplly memcache
			$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    		$fan = null;
    		try {
    			$fanProfileId = $this->_fanpageId .'_' .$user->facebook_user_id .'_fan';
    		
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanProfileId)){
	    			//echo 'db look up';
	    			$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
	    			//Save to the cache, so we don't have to look it up next time
	    			$cache->save($fan, $fanProfileId);
    			}else {
    			//echo 'memcache look up';
    				$fan = $cache->load($fanProfileId);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
   			}
    	}else {
    		$this->view->facebook_user = null;
    	}
		
    	$follow = new Model_Subscribes();
    	$follower = $follow->getFollowers($user->facebook_user_id, $this->_fanpageId);
    	$following = $follow->getFollowing($user->facebook_user_id, $this->_fanpageId);
    	//$friends = $follow->getFriends($user->facebook_user_id, $this->_fanpageId);
    	
    	$fan_level = null;
    	$fan_since = null;
    	$fan_country = null;
    	 
    	if(!$fan->isNewFan()) {
    		$fan_level = $fan->getFanLevel();
    		$fan_since = $fan->getFanSince();
    		$fan_country = $fan->getFanCountry();
    	}
    	
    	$userBadges = new Model_BadgeEvents();
    	$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $user->facebook_user_id);

    	//Zend_Debug::dump($userBadgeCount);
    	$allBadges = new Model_Badges();
    	$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    	$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	
    	
    	$this->view->user_badge = $userBadgeCount[0]['count'];
    	$this->view->all_badge = $allBadges[0]['count'];
    	$this->view->overall_achievement = $overallAchievement;
    	
    	$this->view->fan_point = $fan->getFanCurrency();
    	$fan_exp = $fan->getCurrentEXP();
    	$fan_exp_required = $fan->getNextLevelRequiredXP();
    	
    	
    	$this->view->fan_exp = $fan_exp;
    	$this->view->fan_exp_required = $fan_exp_required - $fan_exp;
    	$this->view->fan_level_exp = $fan_exp_required;
    	$this->view->fan_exp_percentage = $fan_exp/$fan_exp_required*100;
    	
    	//Zend_Debug::dump($fan_level);
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	if(!empty($stat)) {
    		$stat_post = $stat[0]['total_posts'];
    		$stat_comment = $stat[0]['total_comments'];
    		$stat_like = $stat[0]['total_likes'];
    		$stat_get_comment = $stat[0]['total_get_comments'];
    		$stat_get_like = $stat[0]['total_get_likes'];
    	}else {
    		$stat_post = null;
    		$stat_comment = null;
    		$stat_like = null;
    		$stat_get_comment = null;
    		$stat_get_like = null;
    	}
    	
    	
    	$this->view->fan_level = $fan_level;
    	$this->view->fan_since = $fan_since;
    	$this->view->fan_country = $fan_country;
    	$this->view->following = $following;
    	$this->view->follower = $follower;
    	//$this->view->friends = $friends;
    	
    	
    	//Zend_Debug::dump($stat_post);
    	
    	$this->view->stat_post = $stat_post;
    	$this->view->stat_comment = $stat_comment;
    	$this->view->stat_like = $stat_like;
    	$this->view->stat_get_comment = $stat_get_comment;
    	$this->view->stat_get_like = $stat_get_like;
    	
    	
    	$this->render("myprofile");
    }
    
    
    //THIS IS PROBABLY SEARCHING 
    public function userprofileAction() {
    
    	$this->_helper->layout->disableLayout();
    	//check for user authorization
    	$user = new Model_FacebookUsers(); // the person the user is looking at
    	$user2 = new Model_FacebookUsers(); // the actual user
    	
    	$user = $user->find( $this->_request->getParam('target'))->current();// the target
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {

    		$client = new Zend_Http_Client;
    		$client->setUri("https://graph.facebook.com/". $this->_request->getParam('target') );
    		$client->setMethod(Zend_Http_Client::GET);
    		//$client->setParameterGet('access_token', $this->_accessToken);
    		
    		$response = $client->request();
    		 
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    		 
    		if(!empty ($result)) {
    		
    			$user = array('facebook_user_id' => $result->id,
    							'facebook_user_first_name'=> $result->first_name, 
    							'facebook_user_last_name'=>$result->last_name,
    							'created_time'=> 'notuser',
    							'hometown' => 'Not Avaliable'
    						);
    			$user = (object)$user;
    		}
    		//Zend_Debug::dump($user);
    		
    		$this->view->facebook_user = $user;
    		//$this->view->facebook_user = null;
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

    	$follower = $follow->getFollowers($user->facebook_user_id, $this->_fanpageId);
    	$following = $follow->getFollowing($user->facebook_user_id, $this->_fanpageId);
    	//$friends = $follow->getFriends($user->facebook_user_id, $this->_fanpageId);
    	$relation = $follow->getRelation($user2->facebook_user_id, $user->facebook_user_id, $this->_fanpageId);
    	
    	
    	$cache = Zend_Registry::get('memcache');
    	$cache->setLifetime(3600);
    	$fan = null;
    	try {
    		$fanProfileId = $this->_fanpageId .'_' .$user->facebook_user_id .'_fan';
    	
    		//Check to see if the $fanpageId is cached and look it up if not
    		if(isset($cache) && !$cache->load($fanProfileId)){
    			//echo 'db look up';
    			$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    			//Save to the cache, so we don't have to look it up next time
    			$cache->save($fan, $fanProfileId);
    		}else {
    			//echo 'memcache look up';
    			$fan = $cache->load($fanProfileId);
    		}
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    		//echo $e->getMessage();
    	}
    	
    	$fan_level = null;
    	$fan_since = null;
    	$fan_country = null;
    	 
    	if(!$fan->isNewFan()) {
    		$fan_level = $fan->getFanLevel();
    		$fan_since = $fan->getFanSince();
    		$fan_country = $fan->getFanCountry();
    	}
    	
    	$userBadges = new Model_BadgeEvents();
    	$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $user->facebook_user_id);

    	//Zend_Debug::dump($userBadgeCount);
    	$allBadges = new Model_Badges();
    	$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    	$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	
    	
    	$this->view->user_badge = $userBadgeCount[0]['count'];
    	$this->view->all_badge = $allBadges[0]['count'];
    	$this->view->overall_achievement = $overallAchievement;
    	
    	$this->view->fan_point = $fan->getFanCurrency();
    	$fan_exp = $fan->getCurrentEXP();
    	$fan_exp_required = $fan->getNextLevelRequiredXP();
    	$this->view->fan_exp = $fan_exp;
    	$this->view->fan_exp_required = $fan_exp_required - $fan_exp;
    	$this->view->fan_level_exp = $fan_exp_required;
    	$this->view->fan_exp_percentage = $fan_exp/$fan_exp_required*100;
    	//Zend_Debug::dump($fan_level);
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	$stat_post = $stat[0]['total_posts'];
    	$stat_comment = $stat[0]['total_comments'];
    	$stat_like = $stat[0]['total_likes'];
    	$stat_get_comment = $stat[0]['total_get_comments'];
    	$stat_get_like = $stat[0]['total_get_likes'];
    	
    	$activity = new Model_Posts();
    	$activity = $activity->getUserActivity($this->_fanpageId, $user->facebook_user_id, 15);
    	$followingList = $follow -> getFollowersList($user->facebook_user_id,$this-> _fanpageId, 15);
    	$followingList2 = $follow -> getFollowingList($user->facebook_user_id,$this-> _fanpageId, 15);
    	
    	for($i=0; $i<count($followingList);$i++){
    		
    		if ($followingList[$i]['update_time'] == null){
    			$followingList[$i]['update_time'] = $followingList[$i]['created_time'];
    		}
    		$followingList[$i]['user_created_time'] = $followingList[$i]['update_time'];
    		//echo $f['user_created_time'] ;
    	}
    	for($i=0; $i<count($followingList2);$i++){
    	
    		if ($followingList2[$i]['update_time'] == null){
    			$followingList2[$i]['update_time'] = $followingList2[$i]['created_time'];
    		}
    		$followingList2[$i]['user_created_time'] = $followingList2[$i]['update_time'];
    		//echo $f['user_created_time'] ;
    	}
    	$fullactivity = array();
    	$a =0;
    	$b = 0;
    	for($i=0; $i<count($followingList) + count($activity) ; $i++){
    		if (($a < count($activity)) && ($b < count($followingList))){
	    		if ($activity[$a]['user_created_time'] > $followingList[$b]['user_created_time'] ){
	    			$fullactivity[$i] = $activity[$a];
	    			$a++;
	    		}else{
	    			$fullactivity[$i] = $followingList[$b];
	    			$b++;
	    		}

    		}else{
	    		if ($a >= count($activity)){
	    			$fullactivity[$i] = $followingList[$b];  
	    			$b++;  			
	    		}
	    		if ($b >= count($followingList)){
	    			$fullactivity[$i] = $activity[$a];
	    			$a++;
	    		}
    		}
    		
    	
    		
    	}
    	$a =0;
    	$b = 0;
    	for($i=0; $i<count($followingList2) + count($fullactivity) ; $i++){
    		if (($a < count($fullactivity)) && ($b < count($followingList2))){
    			if ($fullactivity[$a]['user_created_time'] > $followingList2[$b]['user_created_time'] ){
    				$fullactivity2[$i] = $fullactivity[$a];
    				$a++;
    			}else{
    				$fullactivity2[$i] = $followingList2[$b];
    				$b++;
    			}
    	
    		}else{
    			if ($a >= count($fullactivity)){
    				$fullactivity2[$i] = $followingList2[$b];
    				$b++;
    			}
    			if ($b >= count($followingList2)){
    				$fullactivity2[$i] = $fullactivity[$a];
    				$a++;
    			}
    		}
    	
    		 
    	
    	}
    	//Zend_Debug::dump($fullactivity2);
    	//$activity = array_merge($activity, $followingList);
    	
    	
    	//Zend_debug::dump($followingList);
    
    	$this->view->relation = $relation;
    	
    	$this->view->fan_level = $fan_level;
    	$this->view->fan_since = $fan_since;
    	$this->view->fan_country = $fan_country;
    	$this->view->following = $following;
    	$this->view->follower = $follower;
    	//$this->view->friends = $friends;
    	
    	$this->view->post = $fullactivity2;
    	//Zend_Debug::dump($stat_post);
    	
    	$this->view->stat_post = $stat_post;
    	$this->view->stat_comment = $stat_comment;
    	$this->view->stat_like = $stat_like;
    	$this->view->stat_get_comment = $stat_get_comment;
    	$this->view->stat_get_like = $stat_get_like;
    	 
    	$this->render("userprofile");
    }


    public function fancrankfeedAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$viewAs = $this->_request->getParam('viewAs');
    	
    	$until = $this->_request->getParam('until');
    	//echo $until;
    	$result = array();
    	$result = $this->getFeed($until,  $viewAs);
    	//$result = json_encode($result);
    	//Zend_Debug::dump($result);
    	
    	$likesModel = new Model_Likes();
		$follow = new Model_Subscribes();
    	$likes = array();
		$relation = array();
    	$count=0;
		
		if ($result != null){
		if ($viewAs == 'myfeed'){
			foreach ($result as $posts){
				//Zend_Debug::dump($posts);
				//echo $top['facebook_user_id'];
				$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts['post_id'], $this->_userId );
				$relation[$count] = $follow->getRelation($this->_userId, $posts['facebook_user_id'],$this->_fanpageId);
				//echo $likes[$count];
				$count++;
			}	
		
		}else{
			foreach ($result as $posts){
				//echo $top['facebook_user_id'];
				$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
				$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
				//echo $likes[$count];
				$count++;
				
			}
		}
		}
	
		$this->view->relation = $relation;

    	$this->view->likes = $likes;
    	$this->view->post = $result;
    	if($viewAs == 'myfeed'){
    		$this->view->myfeedcount = $count;
    		$this->render("fancrankfeedm");
    	}else{
    		$this->render("fancrankfeed");
    	}
    	
    }
    
    public function fancrankfeedcommentAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$postId = $this->_request->getParam('post_id');
		$postType = $this->_request->getParam('post_type');
    	$limit = $this->_request->getParam('limit');
    	$total = $this->_request->getParam('total');
    	
    	$latest = $this->_request->getParam('latest');
    	
    	$result = array();
    	//Zend_Debug::dump($limit);
    	$result = $this->getFeedComment($postId, $limit);
    	//$result = json_encode($result);
		
    	
    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$relation = array();
    	$count=0;
		

    	if(!empty($result)) {
    		foreach ($result as $posts){
    			//echo $top['facebook_user_id'];
    			$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );

    			$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);

    			//echo $likes[$count];
    			$count++;
    		}    		
    	}
    	 

    	//$postTop = explode('_', $postId);
    	//$postTop = $postTop[0].'_'.$postTop[1];
    	
    	//$this->view->postTopId = $postTop;
    	//$this->view->postTopName =$this-> getOwnerOfPost($postTop);
    	
    	$this->view->relation = $relation;
    	$this->view->likes = $likes;
    	$this->view->postOwner = $this->getOwnerOfPost($postId);
    	//Zend_debug::dump($this->getOwnerOfPost($postId));
		$this->view->userid = $this->_userId;
    	$this->view->total = $total;
    	$this->view->limit = $limit;
    	$this->view->comments = $result;
    	$this->view->postId = $postId;
    	$this->view->postType = $postType;
    	
    	if($latest){
    		$this->view->latest = $latest;
    	}
    	
    	$this->render("fancrankfeedcomment");
    }
    
    public function recentactivitiesAction(){
    	$this->_helper->layout->disableLayout();
    	
    	$activitiesModel = new Model_FancrankActivities();
    	
    	$limit = 20;
    	$activities = null;
    	if(!empty($this->_fanpageId ) && !empty($this->_userId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    		
    		try {
    			$fanActivityId = $this->_fanpageId .'_' .$this->_userId. '_fan_activity';
    			 
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanActivityId)){
    				//echo 'db look up';
    				//$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    				$activities = $activitiesModel->getRecentActivities($this->_userId, $this->_fanpageId, $limit);
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($activities, $fanActivityId);
    			}else {
    				//echo 'memcache look up';
    				$activities = $cache->load($fanActivityId);
    				// merge new activity
    			    $newActivity = array();
				    if(!empty($activities[0]['created_time'])) {
				    	$newActivity = $activitiesModel->getRecentActivitiesSince($this->_userId, $this->_fanpageId, $limit, $activities[0]['created_time']);
				    }
			    	
			    	if(count($newActivity) >= $limit) {
			    		//Zend_Debug::dump($newActivity);
			    		$activities = $newActivity;
			    		$cache->save($activities, $fanActivityId);
			    	}else if(count($newActivity) > 0){
			    		$activities = array_merge($newActivity, array_slice($activities, count($newActivity)));
			    	}
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	
    	$this->view->activities = $activities;
    	$this->view->user_id = $this->_userId;
    	
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
    	//echo $limit;
    	//if ($limit !== false){
    	$client->setParameterGet('limit', $limit);
    	//}
    	$response = $client->request();
    
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		//Zend_Debug::dump($result->data);	
		if(!empty ($result->data)) {    

    		return $result->data;
    	}
    }
    
	
    protected function getPost($postId){
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $postId);
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);
    
    	
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	//Zend_debug::dump($result);
    	
    	if(!empty ($result)) {
    	
    		return $result;
    	}
    }
    

    //this grabs the facebook feed for the current fanpage id and returns the list to fancrankAction 
	protected function getFeed($until, $view) {

    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/feed");
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);
    	$client->setParameterGet('limit', 10);
    	if($view != 'myfeed'){
	    	if ($until != 'undefined'){
	    		$client->setParameterGet('until', $until);
	    	}
    	}
    	$response = $client->request();
    	 
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	//Zend_Debug::dump($result->data);
    	if(!empty ($result->data)) {

    		switch ($view){
    			case 'admin':
    				$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/posts");
     				$response = $client->request();
    				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    				//Zend_Debug::dump($result->data);
    				return $result->data;
    			case 'all':
    					return $result->data;
    			case 'user':
    				return $this->feedFilterByUser($result->data, $this->_fanpageId);
    			case 'myfeed':
    				return $this->feedFilterByMyFeed($this->_fanpageId, $until);
    			default:
    				return $result->data;
    		}
    		return $result->data;
    	}
    }
    /*
    protected function feedFilterByAdmin($data, $fanpageId) {
    	$result = array();
    	foreach ($data as $value) {
			if(!empty($value->from->id) && $value->from->id === $fanpageId) {
				$result[] = $value;
			}
    	}
    	return $result;	
    }*/
    
    protected function feedFilterByUser($data, $fanpageId) {
    	$result = array();
    	foreach ($data as $value) {
    		if(!empty($value->from->id) && $value->from->id !== $fanpageId) {
    			$result[] = $value;
    		}
    	}
    	return $result;
    }
    
    protected function feedFilterByMyFeed($fanpageId, $myfeedoffset) {
    	$result = array();
    	$post = new Model_Posts();
		$post = $post->getMyFeedPost($fanpageId, $this->_userId , 10, $myfeedoffset);
		$result = $post;
		
		
    	//Zend_Debug::dump($result);
    	return $result;
    	
    	
		/* I dont care. it works . and its ok fast. 
		 * 
		 * 
		 * 
		 * Select * from(
		
		SELECT post.* 
		FROM fancrank.subscribes sub , fancrank.posts post
		where sub.facebook_user_id = 28117303 && sub.fanpage_id = 197221680326345 && sub.follow_enable = 1
		&& post.facebook_user_id = sub.facebook_user_id_subscribe_to && sub.fanpage_id = post.fanpage_id
		
		union 
		
		SELECT post.*
		FROM fancrank.subscribes sub , fancrank.likes likes, fancrank.posts post
		where sub.facebook_user_id = 28117303 && sub.fanpage_id = 197221680326345 && sub.follow_enable = 1
		&& sub.facebook_user_id_subscribe_to = likes.facebook_user_id && likes.fanpage_id = sub.fanpage_id
		&& likes.post_id = post.post_id && likes.post_type != 'comment'
		
		union 
		
		SELECT post.*
		FROM fancrank.subscribes sub , fancrank.comments com, fancrank.posts post
		where sub.facebook_user_id = 28117303 && sub.fanpage_id = 197221680326345 && sub.follow_enable = 1
		&& sub.facebook_user_id_subscribe_to = com.facebook_user_id && com.fanpage_id = sub.fanpage_id
		&& post.post_id = com.comment_post_id && post.fanpage_id = sub.fanpage_id 
		
		union 
		
		SELECT post.*
		FROM fancrank.subscribes sub , fancrank.likes likes, fancrank.comments com, fancrank.posts post
		where sub.facebook_user_id = 28117303 && sub.fanpage_id = 197221680326345 && sub.follow_enable = 1
		&& sub.facebook_user_id_subscribe_to = likes.facebook_user_id && likes.fanpage_id = sub.fanpage_id
		&& likes.post_id = com.comment_id && likes.post_type = 'comment' && com.comment_post_id = post.post_id
		)as h
		
		order by created_time DESC
		
		
		*/
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

    
    public function getfollowersAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$limit = $this->_request->getParam('limit');
    	$target = $this->_request->getParam('target');
    	$mini = $this->_request->getParam('mini');
    	
    	$follow = new Model_Subscribes();
  
    	$userName = $this-> _request->getParam('targetname');
    	$follow = new Model_Subscribes();
    	
    	$result = $follow->getFollowersList($target, $this->_fanpageId, $limit);
    	
    	$relation = array();
    	
    	$count = 0;
    	foreach ($result as $r){
    		//echo $top['facebook_user_id'];
    		$relation[$count] = $follow->getRelation($this->_userId, $r['facebook_user_id'], $this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    	
    	}
    	
    	$this->view->relation= $relation;
    	$this->view->result = $result;
    	

    	$this->view->title = 'Followers';
    	$this->view->user_name = $userName;
    	$this->view->user_id = $target;
    	
    	if ($mini){
    		$this->render("miniuserlist");
    	}else{
    		$this->render("userlist");
    	}
    }
    /*
    public function getfriendsAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$limit = $this->_request->getParam('limit');
    	$target = $this->_request->getParam('target');
    	$userName = $this-> _request->getParam('targetname');
    	$follow = new Model_Subscribes();
    	$result= $follow->getFriendsList($target, $this->_fanpageId, $limit);
    	
    	$relation = array();
    	 
    	$count = 0;
    	foreach ($result as $r){
    		//echo $top['facebook_user_id'];
    		$relation[$count] = $follow->getRelation($this->_userId, $r['facebook_user_id']);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	
    	//Zend_Debug::dump($result);
    	$this->view->relation= $relation;
    	$this->view->result = $result;
    	$this->view->title = 'Friends';
    	$this->view->user_name = $userName;
    	$this->view->user_id = $target;
    	$this->render("userlist");
    }
    */
    public function getfollowingAction(){    	
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$limit = $this->_request->getParam('limit');
    	$target = $this->_request->getParam('target');
    	$userName = $this-> _request->getParam('targetname');
    	$mini = $this->_request->getParam('mini');

    	$follow = new Model_Subscribes();
    	$relation = array();
    	$result = $follow->getFollowingList($target, $this->_fanpageId, $limit);
    	$count = 0;
    	foreach ($result as $r){
    		//echo $top['facebook_user_id'];
    		$relation[$count] = $follow->getRelation($this->_userId, $r['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	
    	//Zend_Debug::dump($result);
    	
    	$this->view->relation= $relation;
    	$this->view->result = $result;
    	$this->view->title = 'Following';
    	
    	$this->view->user_name = $userName;
    	$this->view->user_id = $target;
    	
    	if ($mini){
    		$this->render("miniuserlist");
    	}else{
    		$this->render("userlist");
    	}

    }
    
    
    public function popoverprofileAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$user = $this->_request->getParam('facebook_user_id');
    	
    	$fan = new Model_Fans($user, $this->_fanpageId);
    	$follow = new Model_Subscribes();
    	$relation = $follow->getRelation($this->_userId, $user, $this->_fanpageId);
    	
    	$fan = $fan->getFanProfile();
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user);
    	
    	$this->view->relation=$relation;
    	$this->view->stat= $stat;
    	$this->view->fan = $fan;
    	$this->render("popoverprofile");
    }
    
    /*
    public function adminfeedAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$result = $this->getAdminFeed($this->_getParam('id'), $this->_getParam('access_token'), 5);
    	
    	$this->_helper->json($result);
    }
	*/
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
    	$this->_redirect('/app/index/index/' .$this->_getParam('id') .'?user_id=' .$this->_userId);
    }
    
    public function insightsAction() 
    {
    	$this->_helper->layout->setLayout('insights_layout');
    }
    
    public function badgetestAction(){
    	$this->_helper->layout->setLayout('default_layout');
    	echo $this->badgeArray('197221680326345', '28117303');
    	$this->render("badgetest");
    
    }
    
    
    protected function badgeArray($fanpage_id, $facebook_user_id){
    		$badge = new Model_Badges();
    		$fan = new Model_FansObjectsStats();
    		$follow = new Model_Subscribes();
    		$fanRecord = $fan->findFanRecord($fanpage_id, $facebook_user_id);
    		$badge = $badge->findAll();
    		//Zend_Debug::dump($badge);
    		$array = array();
    		
    	
    		$cg10s =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    		$cg1m =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    		$cl10s =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    		$cl1m =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    		$cp10s=  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    		$cp1m =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    		$cs10s=  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    		$cs1m =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    		$cv10s = $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    		$cv1m =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    		$gcg10s =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    		$gcg1m =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    		$gcl10s =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    		$gcg1m =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    		$gcp10s =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    		$gcp1m =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    		$gcs10s =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    		$gcs1m =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    		$gcv10s =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    		$gcv1m=  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    		$lg10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    		$lg1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    		$lp10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    		$ll1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    		$ll10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    		$lp1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    		$ls10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    		$ls1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    		$lv10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    		$lv1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    		$lc10s =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'comment');
    		$lc1m =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'comment');
    		$glg10s =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    		$glg1m =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    		$glg10s =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    		$gll1m =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    		$glp10s =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    		$glp1m =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    		$gls10s =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    		$gls1m =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    		$glv10s =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    		$glv1m =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    		$fw = $follow -> getFollowing($facebook_user_id, $fanpage_id);
    		$fwd = $follow -> getFollowers($facebook_user_id, $fanpage_id);
    	
    		foreach ($badge as $b){
    			//echo $b['name']. ' '.$b['description'] .' '. $b['quantity'];
    			$array[$b['name']][(string)$b['quantity']]['description'] = $b['description'];
    			$array[$b['name']][(string)$b['quantity']]['picture'] = $b['picture'];
    			$array[$b['name']][(string)$b['quantity']]['weight'] = $b['weight'];
    			$array[$b['name']][(string)$b['quantity']]['stylename'] = $b['stylename'];
    			$array[$b['name']][(string)$b['quantity']]['percentage'] = 'none';
    			switch ($b['name']){
    				case 'Comment-General':
    				//$temp =  $fan->getTotalComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    				break;
    				case 'Comment-General-10sec':
    				
	    				$temp =  $cg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    				break;
	    				
	    			case 'Comment-General-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Link':
	    				//$temp =  $fan->getLinkComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['link_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Comment-Link-10sec':
	    				
	    				$temp =  $cl10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Link-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cl1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo':
	    				//$temp =  $fan->getPhotoComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['photo_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo-10sec':
	    				
	    				$temp =  $cp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo-1min':
	    			
	    				//Zend_Debug::dump($temp);
	    				
	    				$temp =  $cp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status':
	    				//$temp =  $fan->getStatusComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['status_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status-10sec':
	    				
	    				$temp =  $cs10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cs1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video':
	    				//$temp =  $fan->getVideoComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['video_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video-10sec':
	    				
	    				$temp =  $cv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Follow' :
	    				
	    				 $temp =  $fw[0]['Following'] / $b['quantity'];
	    				 $temp = ($temp> 1)? 1 : $temp;
	    				 $array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Followed':
	    				
	    				$temp =  $fwd[0]['Follower'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General':
	    				//$temp =  $fan->getTotalGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_get_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General-10sec':
	    				
	    				$temp =  $gcg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break; 
	    			case 'Get-Comment-Link':
	    				//$temp =  $fan->getLinkGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_link_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Link-10sec':
	    				
	    				$temp =  $gcl10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Link-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo':
	    				//$temp =  $fan->getPhotoGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_photo_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo-10sec':
	    				$temp =  $gcp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status':
	    				//$temp =  $fan->getStatusGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_status_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status-10sec':
	    				
	    				$temp =  $gcs10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcs1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video':
	    				//$temp =  $fan->getVideoGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_video_comments'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video-10sec':
	    				
	    				$temp =  $gcv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General':
	    				//$temp =  $fan->getTotalLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General-10sec':
	    				
	    			
	    				$temp =  $lg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link':
	    				//$temp =  $fan->getLinkLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['link_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link-10sec':
	    				
	    				$temp =  $ll10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $ll1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo':
	    				//$temp =  $fan->getPhotoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['photo_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo-10sec':
	    				
	    				$temp =  $lp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status':
	    				//$temp =  $fan->getStatusLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['status_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status-10sec':
	    				
	    				$temp =  $ls10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $ls1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['video_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video-10sec':
	    				
	    				$temp =  $lv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['comment_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment-10sec':
	    				
	    				$temp =  $lc10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment-1min':
	    			
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lc1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    		
	 
	    			
	    			case 'Get-Like-General':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_get_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Get-Like-General-10sec':
	    				
	    				$temp =  $glg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-General-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Link':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_link_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			 
	    			case 'Get-Like-Link-10sec':
	   
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Link-1min':
	    				
	    				$temp =  $gll1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_photo_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo-10sec':
	    				
	    				$temp =  $glp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_status_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status-10sec':
	    				
	    				$temp =  $gls10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gls1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_video_likes'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video-10sec':
	    				
	    				$temp =  $glv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-General':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_posts'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Link':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_link'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Photo':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_photo'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Status':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_status'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_video'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-on-Post':
	    				$temp =  $fan->getHighestLikeOnPostCount($fanpage_id, $facebook_user_id);
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['post_likes_count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-on-Post':
	    				$temp =  $fan->getHighestCommentOnPostCount($fanpage_id, $facebook_user_id);
	    				//$temp = $temp->current();
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				$temp =  $temp[0]['post_comments_count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-on-Comment':
	    				$temp =  $fan->getHighestLikeOnCommentCount($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				$temp =  $temp[0]['comment_likes_count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Admin-Comment':
	    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Admin-Like':
	    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['count'] / $b['quantity'];
	    				$temp = ($temp> 1)? 1 : $temp;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Multiple-Comment':
	    				$temp = $array['Comment-General'][(string)$b['quantity']]['percentage'] + 
	    						$array['Comment-Status'][(string)$b['quantity']]['percentage'] +
	    						$array['Comment-Photo'][(string)$b['quantity']]['percentage'] +
	    						$array['Comment-Video'][(string)$b['quantity']]['percentage'] +
	    						$array['Comment-Link'][(string)$b['quantity']]['percentage'];
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp/5 ;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Multiple-Like':
	    				$temp = $array['Like-General'][(string)$b['quantity']]['percentage'] +
	    				$array['Like-Status'][(string)$b['quantity']]['percentage'] +
	    				$array['Like-Photo'][(string)$b['quantity']]['percentage'] +
	    				$array['Like-Video'][(string)$b['quantity']]['percentage'] +
	    				$array['Like-Link'][(string)$b['quantity']]['percentage'];
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp/5 ;
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			/*
	    			case 'Fan-Favorite-Month': $array[$b['name']][(string)$b['quantity']]['percentage'] = 'none';break;
	    			case 'Fan-Favorite-Week': $array[$b['name']][(string)$b['quantity']]['percentage'] ='none';break;
	    			case 'Fan-Favorite-Year':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none'; break;
	    			case 'Top-Clicker-Month': $array[$b['name']][(string)$b['quantity']]['percentage'] ='none'; break;
	    			case 'Top-Clicker-Week':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Top-Clicker-Year': $array[$b['name']][(string)$b['quantity']]['percentage'] ='none'; break;
	    			case 'Top-Fan-Month':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Top-Fan-Week':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Top-Fan-Year':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Top-Talker-Month':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Top-Talker-Week': $array[$b['name']][(string)$b['quantity']]['percentage'] ='none'; break;
	    			case 'Top-Talker-Year':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			case 'Watched-Tutorial':$array[$b['name']][(string)$b['quantity']]['percentage'] ='none';  break;
	    			*/
	    			case 'default':
	    				break;
    			/*
    			
    				
    			*/
    			}
    		}
    		//Zend_Debug::dump($array);
    		//exit();
    		return $array;
    }
    
    protected function hasBadge($user_id){
    		$badge = $this->badgeArray();

    }
    
    public function tourAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$tour = $this->_request->getParam('tour');
    	
    	if($tour) {
    		$this->_facebook_user->fancrankAppTour = 1;
    	}else {
    		$this->_facebook_user->fancrankAppTour = 0;
    	}
    }
    
    private function feedFirstQuery() {
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$this->_fanpageId/feed?limit=10");
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$this->_fanpageId/posts?limit=1");
    
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$this->_accessToken;
    
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/?". $batchQueries);
    	$client->setMethod(Zend_Http_Client::POST);
    
    	$response = $client->request();
    
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	 
    	$feed = array();
    	$posts = array();
    	if(!empty($result[0]->body)) {
    		$feed = json_decode($result[0]->body);
    	}
    
    	if(!empty($result[1]->body)) {
    		$posts = json_decode($result[1]->body);
    	}
    	 
    	$finalResult['feed'] = $feed;
    	$finalResult['posts'] = $posts;
    
    	return $finalResult;
    }
    
}

