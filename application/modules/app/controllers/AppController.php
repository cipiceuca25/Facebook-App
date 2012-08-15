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
		
		$name = new Model_FacebookUsers();
		$name = $name->find($this->_userId)->current();
		$this->view->username = $name->facebook_user_first_name.' '.$name->facebook_user_last_name;

		
		
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

    public function topfanAction()
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
    

    
    	//exit();
    	$this->view->top_fans = $topFans;

    	//echo ($user['facebook_user_id']);
    
    	$topArray = NULL;
   
    	$count=0;
    	foreach ($topFans as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($user->facebook_user_id, $top['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    
    	}

    	 
    	$topFanYou = $model->getUserTopFansRank($this->_fanpageId, $user->facebook_user_id);

    	//Zend_Debug::dump($topFanYou);
    	 
    	 
    	 
    	$this->view->topFanYou =  $topFanYou;
    	
    	//$this-view->talkerYou =
    	//$this-view->clickerYou =
    	 
    	$this->view->topFanArray = $topArray ;
    
    
    	/*
    	 $this->view->user_top_fans = $model->getUserRanking($this->_fanpageId, 'FAN', $this->_userId);
    	$this->view->user_most_popular = $model->getUserRanking($this->_fanpageId, 'POPULAR', $this->_userId);
    	$this->view->user_top_talker = $model->getUserRanking($this->_fanpageId, 'TALKER', $this->_userId);
    	$this->view->user_top_clicker = $model->getUserRanking($this->_fanpageId, 'CLICKER', $this->_userId);
    	*/
    	$this->render("topfan");
    }
    
    
    
    /* Action to retrieve top five fans by default */
  	public function leaderboardAction()
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

    	$topFollowed = $follow->getTopFollowed($this->_fanpageId, 5);
    	//Zend_Debug::dump($topClicker);
    
    	//exit();
    	$this->view->top_fans = $topFans;
    	$this->view->most_popular = $mostPopular;
    	$this->view->top_talker = $topTalker;
    	$this->view->top_clicker = $topClicker;
    	$this->view->top_followed = $topFollowed;
    	//echo ($user['facebook_user_id']);

    	$topArray = NULL;
    	$popularArray = NULL;
    	$talkerArray = NULL;
    	$clickerArray = NULL;
    	$followedArray = NULL;
    	$count=0;
    	foreach ($topFans as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($user->facebook_user_id, $top['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		
    	}    	 
    	$count=0;
    	foreach ($mostPopular as $mp){
    		//echo $top['facebook_user_id'];
    		$popularArray[$count] = $follow->getRelation($user->facebook_user_id, $mp['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    	
    	}    	
    	$count=0;
    	foreach ($topTalker as $tt){
    		//echo $top['facebook_user_id'];
    		$talkerArray[$count] = $follow->getRelation($user->facebook_user_id, $tt['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
  		$count=0;
    	foreach ($topClicker as $tc){
    		//echo $top['facebook_user_id'];
    		$clickerArray[$count] = $follow->getRelation($user->facebook_user_id, $tc['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	$count=0;
    	foreach ($topFollowed as $tf){
    		//echo $top['facebook_user_id'];
    		$followedArray[$count] = $follow->getRelation($user->facebook_user_id, $tf['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    		 
    	}
    	
    	$topFanYou = $model->getUserTopFansRank($this->_fanpageId, $user->facebook_user_id);
    	$popularYou = $model->getUserMostPopularRank($this->_fanpageId, $user->facebook_user_id);
    	$talkerYou = $model->getUserTopTalkerRank($this->_fanpageId, $user->facebook_user_id);
    	$clickerYou = $model->getUserTopClickerRank($this->_fanpageId, $user->facebook_user_id);
    	
    	$followedYou = $follow->getTopFollowedRank($this->_fanpageId, $user->facebook_user_id);
    	//Zend_Debug::dump($followedYou);
    	
    	
    	
    	$this->view->topFanYou =  $topFanYou;
    	$this->view->popularYou = $popularYou;
    	$this->view->talkerYou = $talkerYou;
    	$this->view->clickerYou =$clickerYou;
    	$this->view->followedYou =$followedYou;
    	//$this-view->talkerYou =
    	//$this-view->clickerYou =
    	
    	$this->view->topFanArray = $topArray ;
    	$this->view->popularArray = $popularArray ;
    	$this->view->talkerArray = $talkerArray ;
    	$this->view->clickerArray = $clickerArray ;
    	$this->view->followedArray = $followedArray ;
    	 
    	/*
    	$this->view->user_top_fans = $model->getUserRanking($this->_fanpageId, 'FAN', $this->_userId);
    	$this->view->user_most_popular = $model->getUserRanking($this->_fanpageId, 'POPULAR', $this->_userId);
    	$this->view->user_top_talker = $model->getUserRanking($this->_fanpageId, 'TALKER', $this->_userId);
    	$this->view->user_top_clicker = $model->getUserRanking($this->_fanpageId, 'CLICKER', $this->_userId);
    	*/
    	$this->render("leaderboard");
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
    
    public function commentAction()
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
    	
    	$badges = new Model_Badges();
    	
    	$badges = $badges->findAll();
    	
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
    	}else {
    		$this->view->facebook_user = null;
    	}
		
    	$follow = new Model_Subscribes();
    	$follower = $follow->getFollowers($user->facebook_user_id, $this->_fanpageId);
    	$following = $follow->getFollowing($user->facebook_user_id, $this->_fanpageId);
    	//$friends = $follow->getFriends($user->facebook_user_id, $this->_fanpageId);
    	
    	$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    	
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
    	$this->view->fan_exp_required = $fan_exp_required;
    	$this->view->fan_level_exp = $fan_exp + $fan_exp_required;
    	$this->view->fan_exp_percentage = $fan_exp/($fan_exp + $fan_exp_required)*100;
    	
    	//Zend_Debug::dump($fan_level);
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	$stat_post = $stat[0]['total_posts'];
    	$stat_comment = $stat[0]['total_comments'];
    	$stat_like = $stat[0]['total_likes'];
    	$stat_get_comment = $stat[0]['total_get_comments'];
    	$stat_get_like = $stat[0]['total_get_likes'];
    	
    	
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
    	
    	$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    	
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
    	$this->view->fan_exp_required = $fan_exp_required;
    	$this->view->fan_level_exp = $fan_exp + $fan_exp_required;
    	$this->view->fan_exp_percentage = $fan_exp/($fan_exp + $fan_exp_required)*100;
    	
    	//Zend_Debug::dump($fan_level);
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	$stat_post = $stat[0]['total_posts'];
    	$stat_comment = $stat[0]['total_comments'];
    	$stat_like = $stat[0]['total_likes'];
    	$stat_get_comment = $stat[0]['total_get_comments'];
    	$stat_get_like = $stat[0]['total_get_likes'];
    	
    	
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
    	 
    	$this->render("userprofile");
    }


    public function fancrankfeedAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$viewAs = $this->_request->getParam('viewAs');
    	$limit = $this->_request->getParam('limit');
    	$result = array();
    	$result = $this->getFeed($limit, $viewAs);
    	//$result = json_encode($result);
    	//Zend_Debug::dump($result);
    	
    	$likesModel = new Model_Likes();
		$follow = new Model_Subscribes();
    	$likes = array();
		$relation = array();
    	$count=0;
		//Zend_Debug::dump($result);
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
    	
		
		
	

		$this->view->relation = $relation;

    	$this->view->likes = $likes;
    	$this->view->post = $result;
    	if($viewAs == 'myfeed'){
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
    	$this->render("fancrankfeedcomment");
    }
    
    public function recentactivitiesAction(){
    	$this->_helper->layout->disableLayout();
    	
    	$activities = new Model_FancrankActivities();
    	$activities = $activities -> getRecentActivities($this->_userId, $this->_fanpageId, 20);
    	
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
	protected function getFeed($limit, $view) {

    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/feed");
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $this->_accessToken);
    	$client->setParameterGet('limit', $limit);
    	 
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
    				return $this->feedFilterByAdmin($result->data, $this->_fanpageId);
    			case 'all':
    					return $result->data;
    			case 'user':
    				return $this->feedFilterByUser($result->data, $this->_fanpageId);
    			case 'myfeed':
    				return $this->feedFilterByMyFeed($this->_fanpageId, $limit);
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
    
    protected function feedFilterByMyFeed($fanpageId, $limit) {
    	$result = array();
    	$post = new Model_Posts();
		$post = $post->getMyFeedPost($fanpageId, $this->_userId ,$limit);
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
    		foreach ($badge as $b){
    			//echo $b['name']. ' '.$b['description'] .' '. $b['quantity'];
    			$array[$b['name']][$b['quantity']]['description'] = $b['description'];
    			$array[$b['name']][$b['quantity']]['picture'] = $b['picture'];
    			$array[$b['name']][$b['quantity']]['weight'] = $b['weight'];
    			$array[$b['name']][$b['quantity']]['stylename'] = $b['stylename'];
    			
    			if ($b['name'] == 'Comment-General'){
    				//$temp =  $fan->getTotalComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['total_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-General-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-General-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Link'){
    				//$temp =  $fan->getLinkComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['link_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Link-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Link-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Photo'){
    				//$temp =  $fan->getPhotoComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['photo_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Photo-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Photo-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Status'){
    				//$temp =  $fan->getStatusComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['status_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Status-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Status-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Video'){
    				//$temp =  $fan->getVideoComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['video_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Video-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-Video-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			
    			if ($b['name'] == 'Follow' ){
    				 $temp = $follow -> getFollowing($facebook_user_id, $fanpage_id);
    				 $temp =  $temp[0]['Following'] / $b['quantity'];
    				 $temp = ($temp> 1)? 1 : $temp;
    				 $array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Followed'){
    				$temp = $follow -> getFollowers($facebook_user_id, $fanpage_id);
    				$temp =  $temp[0]['Follower'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-General'){
    				//$temp =  $fan->getTotalGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['total_get_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-General-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-General-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			} 
    			if ($b['name'] == 'Get-Comment-Link'){
    				//$temp =  $fan->getLinkGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_link_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Link-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Link-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Photo'){
    				//$temp =  $fan->getPhotoGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_photo_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Photo-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Photo-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Status'){
    				//$temp =  $fan->getStatusGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_status_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Status-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Status-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Video'){
    				//$temp =  $fan->getVideoGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_video_comments'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Video-10sec'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Comment-Video-1min'){
    				$temp =  $fan->getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-General'){
    				//$temp =  $fan->getTotalLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['total_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-General-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-General-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Link'){
    				//$temp =  $fan->getLinkLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['link_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Link-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Link-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Photo'){
    				//$temp =  $fan->getPhotoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['photo_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Photo-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Photo-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Status'){
    				//$temp =  $fan->getStatusLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['status_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Status-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Status-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Video'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['video_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Video-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Video-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Comment'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['comment_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Comment-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 10 second', 'comment');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-Comment-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, 'interval 1 minute', 'comment');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    		
 
    			
    			if ($b['name'] == 'Get-Like-General'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['total_get_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			
    			if ($b['name'] == 'Get-Like-General-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'all');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-General-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'all');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Link'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_link_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			 
    			if ($b['name'] == 'Get-Like-Link-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'link');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Link-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'link');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Photo'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_photo_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Photo-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'photo');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Photo-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'photo');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Status'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_status_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Status-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'status');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Status-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'status');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Video'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['get_video_likes'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Video-10sec'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 10 second', 'video');
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Like-Video-1min'){
    				$temp =  $fan->getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, 'interval 1 minute', 'video');
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Post-General'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['total_posts'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Post-Link'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['post_link'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Post-Photo'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['post_photo'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Post-Status'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['post_status'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Post-Video'){
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $fanRecord[0]['post_video'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-on-Post'){
    				$temp =  $fan->getHighestLikeOnPostCount($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['post_likes_count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Comment-on-Post'){
    				$temp =  $fan->getHighestCommentOnPostCount($fanpage_id, $facebook_user_id);
    				//$temp = $temp->current();
    				$temp =  $temp[0]['post_comments_count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Like-on-Comment'){
    				$temp =  $fan->getHighestLikeOnCommentCount($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['comment_likes_count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Admin-Comment'){
    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			if ($b['name'] == 'Get-Admin-Like'){
    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp> 1)? 1 : $temp;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			
    			if ($b['name'] == 'Multiple-Comment'){
    				$temp = $array['Comment-General'][$b['quantity']]['percentage'] + 
    						$array['Comment-Status'][$b['quantity']]['percentage'] +
    						$array['Comment-Photo'][$b['quantity']]['percentage'] +
    						$array['Comment-Video'][$b['quantity']]['percentage'] +
    						$array['Comment-Link'][$b['quantity']]['percentage'];
    				//Zend_Debug::dump($temp);
    				$temp =  $temp/5 ;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			
    			if ($b['name'] == 'Multiple-Like'){
    				$temp = $array['Like-General'][$b['quantity']]['percentage'] +
    				$array['Like-Status'][$b['quantity']]['percentage'] +
    				$array['Like-Photo'][$b['quantity']]['percentage'] +
    				$array['Like-Video'][$b['quantity']]['percentage'] +
    				$array['Like-Link'][$b['quantity']]['percentage'];
    				//Zend_Debug::dump($temp);
    				$temp =  $temp/5 ;
    				$array[$b['name']][$b['quantity']]['percentage'] = $temp;
    			}
    			
    		
    			/*
    
    	
    			*/
    		}
    		Zend_Debug::dump($array);
    		return $badge;
    }
    
    protected function hasBadge($user_id){
    		$badge = $this->badgeArray();
    		
    	
    
    		foreach ($badge['Fan-Favorite-Month']as $a){
    			
    		}
    		foreach ($badge['Fan-Favorite-Week']as $a){
    			
    		}
    		foreach ($badge['Fan-Favorite-Year']as $a){
    			
    		}
    	
    	
    	
    
    		
    		foreach ($badge['Multiple-Comment']as $a){
    			
    		}
    		foreach ($badge['Multiple-Like']as $a){
    			
    		}
  
    		foreach ($badge['Top-Clicker-Month']as $a){
    			
    		}
    		foreach ($badge['Top-Clicker-Week']as $a){
    			
    		}
    		foreach ($badge['Top-Clicker-Year']as $a){
    			
    		}
    		foreach ($badge['Top-Fan-Month']as $a){
    			
    		}
    		foreach ($badge['Top-Fan-Week']as $a){
    			
    		}
    		foreach ($badge['Top-Fan-Year']as $a){
    			
    		}
    		foreach ($badge['Top-Talker-Month']as $a){
    			
    		}
    		foreach ($badge['Top-Talker-Week']as $a){
    			
    		}
    		foreach ($badge['Top-Talker-Year']as $a){
    			
    		}
    		foreach ($badge['Watched-Tutorial']as $a){
    			
    		}
    		
 
    			
    		
    
    }
    
    
}

