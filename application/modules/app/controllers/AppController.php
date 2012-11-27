<?php
/**
 * FanCrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the FanCrank OEM license
 *
 * @category    app
 * @package     app
 * @copyright   Copyright (c) 2012 FanCrank
 * @license     
 */
class App_AppController extends Fancrank_App_Controller_BaseController
{
	protected $_fanpageId;
	protected $_userId;
	protected $_accessToken;
	protected $_fanpageProfile;
	protected $_fan;
	//protected $_lastUpdateTime;
	/**
	 * Initilized fanpage id and login user variables
	 */
	public function preDispatch() {
		parent::preDispatch();
		
		if (APPLICATION_ENV != 'stage') {
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
			$this->_fanpageProfile = $fanpage;
			//Zend_Debug::dump($token);
			$this->_accessToken = $fanpage ->access_token;
			$this->view->fanpage_name = $fanpage->fanpage_name;	
			//$update_time = new Model_CronLog();
			//$update_time = $update_time->getLastUpdate($this->_fanpageId);
			//$this->_lastUpdateTime = $update_time[0]['end_time'];
		}else {
			
			//$this->_redirect('http://www.fancrank.com');
		}
		
		if(isset($this->_facebook_user->fancrankAppTour)) {
			$this->view->tour = $this->_facebook_user->fancrankAppTour;
		}else {
			$this->_facebook_user->fancrankAppTour = 0;
		}
// 		$name = new Model_FacebookUsers();
// 		$name = $name->find($this->_userId)->current();
// 		$this->view->username = $name->facebook_user_first_name.' '.$name->facebook_user_last_name;
		//echo $token ->access_token;
		
		//Zend_Debug::dump($this->_facebook_user);
		
		if($this->_userId) {
			//$badges = $this->badgeArray2D($this->_fanpageId, $this->_userId, 3);
			//$access_token = $this->facebook_user->facebook_user_access_token;
			//$this->view->feed = $this->getFeed($access_token);
			// aplly memcache
			$cache = Zend_Registry::get('memcache');
			$cache->setLifetime(1800);
			
			try {
				$fanProfileId = $this->_fanpageId .'_' .$this->_userId .'_fan';
				//$cache->remove($fanProfileId);
				//Check to see if the $fanpageId is cached and look it up if not
				if(isset($cache) && !$cache->load($fanProfileId)){
		
					//echo 'db look up';
					$this->_fan = new Model_Fans($this->_userId, $this->_fanpageId);
	
					if ($this->_fan->isNewFan()){
						
						$updateTime = new Zend_Date(time(), Zend_Date::TIMESTAMP);
						
						$fansData = array(
								'facebook_user_id'  => $this->_facebook_user->facebook_user_id,
								'fanpage_id'        => $this->_facebook_user->fanpage_id,
								'fan_name'			=> trim($this->_facebook_user->facebook_user_name),
								'fan_first_name'	=> $this->_facebook_user->facebook_user_first_name,
								'fan_last_name'		=> $this->_facebook_user->facebook_user_last_name,
								'fan_gender'		=> $this->_facebook_user->facebook_user_gender,
								'fan_locale'		=> $this->_facebook_user->facebook_user_locale,
								'fan_user_avatar'	=> $this->_facebook_user->facebook_user_avatar,
								
								'first_login_time'	=> $updateTime->toString('YYYY-MM-dd HH:mm:ss'),
								'last_login_time'	=> $updateTime->toString('YYYY-MM-dd HH:mm:ss'),
								'login_count'		=> 1,
								'updated_time'		=> $updateTime->toString('YYYY-MM-dd HH:mm:ss')
						);

						$this->_fan->insertNewFan($fansData);
						$this->_fan = new Model_Fans($this->_userId, $this->_fanpageId);
					}
					
					$this->_fan = $this->_fan->getFanProfile();
					//Zend_Debug::dump($this->_fan);
					//Save to the cache, so we don't have to look it up next time
					$cache->save($this->_fan, $fanProfileId);
				}else {
					//echo 'memcache look up';
					$this->_fan = $cache->load($fanProfileId);
				}
			} catch (Exception $e) {
				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
				//echo $e->getMessage();
			}
		}
		
		if ($this->_fanpageProfile -> fanpage_level <3){
			$this->_fan['fan_point']='?';
		}
		if ($this->_fanpageProfile -> fanpage_level ==1){
			$this->_fan['fan_exp']='?';
		}
		
		$color = new Model_UsersColorChoice();
		$color = $color->getColorChoice($this->_fanpageId);
		
		$this->view->username = $this->_facebook_user->facebook_user_name;
		$this->view->facebook_user_access_token = $this->_facebook_user->facebook_user_access_token;
		$this->view->fanpage_id = $this->_fanpageId;
		$this->view->fanpage_level = $this->_fanpageProfile -> fanpage_level;
		$this->view->user_id = $this->_userId;
		$this->view->color = $color['color_choice'];
		//Zend_Debug::dump($this->_fan);
		$this->view->fan = $this->_fan;
		//$this->view->notibadges = $badges;
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
    	/*$user = $this->_facebook_user;
    	//Zend_Debug::dump($user);
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    	}
    	*/
    	//$this->view->fanpage_id = $this->_fanpageId;
    	$model = new Model_Rankings;
    	$follow = new Model_Subscribes();
    	$fanpage = array();
    	
    	if(!empty($this->_fanpageId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
    		//$cache->remove($this->_fanpageId);
    		
    		try {
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($this->_fanpageId)){
    				//echo 'db look up';
    				//Look up the $fanpageId
    				$fanpage['topFans'] = $model->getTopFansByWeek($this->_fanpageId, 5);

    				$fanpage['mostPopular'] = $model->getMostPopularByWeek($this->_fanpageId, 5);
    				//Zend_Debug::dump($mostPopular);
    				
    				$fanpage['topTalker'] = $model->getTopTalkerByWeek($this->_fanpageId, 5);
    				//Zend_Debug::dump($topTalker);
    				
    				$fanpage['topClicker'] = $model->getTopClickerByWeek($this->_fanpageId, 5);
    				//Zend_Debug::dump($topClicker);
    				
    				//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    				$fanpage['topFollowed'] = $model->getTopFollowedByWeek($this->_fanpageId, 5);
    				$fanpage['topFansAllTime'] = $model->getTopFans($this->_fanpageId, 5);
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
    	//$topArray = NULL;
   
    	$count=0;
    	$topArray = null;
    	foreach ($topFans as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[$count] = $follow->getRelation($this->_userId, $top['facebook_user_id'],$this->_fanpageId);
    		//echo $topArray[$count];
    		$count++;
    
    	}
    	
    	$userLeaderBoardData = array();
    	 
    	if(!empty($this->_fanpageId) && !empty($this->_userId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
    		//$cache->remove($this->_fanpageId .'_' .$this->_userId);    		 
    		try {
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId)){
    				//Look up the $fanpageId
    				$userLeaderBoardData['topFans'] = $model->getUserTopFansRank($this->_fanpageId, $this->_userId);
    				
    				$userLeaderBoardData['mostPopular'] = $model->getUserMostPopularRank($this->_fanpageId, $this->_userId);
    				 
    				$userLeaderBoardData['topTalker'] = $model->getUserTopTalkerRank($this->_fanpageId, $this->_userId);
    				//Zend_Debug::dump($topTalker);
    				 
    				$userLeaderBoardData['topClicker'] = $model->getUserTopClickerRank($this->_fanpageId, $this->_userId);
    				//Zend_Debug::dump($topClicker);
    				 
    				//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    				$userLeaderBoardData['topFollowed'] = $model->getTopFollowedRankByWeek($this->_fanpageId, $this->_userId);
    				//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    				$userLeaderBoardData['topFansAllTime'] = $model->getUserTopFansRank($this->_fanpageId, $this->_userId);
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($userLeaderBoardData, $this->_fanpageId .'_' .$this->_userId);
    			}else {
    				//echo 'memcache look up';
    				$userLeaderBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId);
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
    public function toplistAction(){
    	$this->_helper->layout->disableLayout();
    	$list = $this->_request->getParam('list');
    	$follow = new Model_Subscribes();
    	$model = new Model_Rankings;
    	
    	$toplist = array();
    	
    	if(!empty($this->_fanpageId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
//     		$cache->remove($this->_fanpageId . '_topfan');
//     		$cache->remove($this->_fanpageId . '_topfanall');
//     		$cache->remove($this->_fanpageId . '_topclicker');
//     		$cache->remove($this->_fanpageId . '_topfollowed');
//     		$cache->remove($this->_fanpageId . '_toptalker');
//     		$cache->remove($this->_fanpageId . '_fanfavorite');
    		try {
    			//Check to see if the $fanpageId is cached and look it up if not
    			switch($list){
    				case 'top-followed':
    					if(isset($cache) && !$cache->load($this->_fanpageId . '_topfollowed')){
    						$toplist = $model->getTopFollowedByWeek($this->_fanpageId, 5);
    				
    						$cache->save($toplist, $this->_fanpageId . '_topfollowed');
    					}else{
    						$toplist = $cache->load($this->_fanpageId . '_topfollowed');
    					}
    					break;
    				case 'top-clicker':
    					if(isset($cache) && !$cache->load($this->_fanpageId . '_topclicker')){
    						$toplist = $model->getTopClickerByWeek($this->_fanpageId, 5);
    				
    						$cache->save($toplist, $this->_fanpageId . '_topclicker');
    					}else{
    						$toplist = $cache->load($this->_fanpageId . '_topclicker');
    					}
    					break;
    				case 'fan-favorite':
		    			if(isset($cache) && !$cache->load($this->_fanpageId . '_fanfavorite')){
		    				$toplist = $model->getMostPopularByWeek($this->_fanpageId, 5);
		    				
		    				$cache->save($toplist, $this->_fanpageId . '_fanfavorite');
		    			}else{
		    				$toplist = $cache->load($this->_fanpageId . '_fanfavorite');
		    			}
	    				break;
	    			case 'top-talker':
    					if(isset($cache) && !$cache->load($this->_fanpageId . '_toptalker')){
    						$toplist = $model->getTopTalkerByWeek($this->_fanpageId, 5);
    				
    						$cache->save($toplist, $this->_fanpageId . '_topfan');
    					}else{
    						$toplist = $cache->load($this->_fanpageId . '_topfan');
    					}
    					break;

	    			case 'top-fan-all':
	    				if(isset($cache) && !$cache->load($this->_fanpageId . '_topfanall')){
	    					$toplist = $model->getTopFans($this->_fanpageId, 5);
	    					$cache->save($toplist, $this->_fanpageId . '_topfanall');
	    				}else{
	    					$toplist = $cache->load($this->_fanpageId . '_topfanall');
	    				}
	    				
	    				break;
    				
    				default:
    					if(isset($cache) && !$cache->load($this->_fanpageId . '_topfan')){
    						$toplist = $model->getTopFansByWeek($this->_fanpageId, 5);
    						$cache->save($toplist, $this->_fanpageId . '_topfan');
    					}else{
    						$toplist = $cache->load($this->_fanpageId . '_topfan');
    					}
    					break;
    			}
    			
    		} catch (Exception $e) {
  				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
  				//echo $e->getMessage();
  			}    	
    	}
    	
    	if ($list == 'top-fan' || $list == 'top-fan-all'){
	    	$stat_model = new Model_FansObjectsStats();
	    	 
	    	$count=0;
	    	$topArray = array();
	    	$topFanStats = null;
	    	foreach ($toplist as $top){
	    		//echo $top['facebook_user_id'];
	    		$topArray[$count] = $follow->getRelation($this->_userId, $top['facebook_user_id'],$this->_fanpageId);
	    		//echo $topArray[$count];
	    		$stat = $stat_model ->findFanRecord($this->_fanpageId, $top['facebook_user_id']);
	    		 
	    		$topFanStats[$count]['total_posts'] = $stat[0]['total_posts'];
	    		$topFanStats[$count]['total_comments'] = $stat[0]['total_comments'];
	    		$topFanStats[$count]['total_likes'] = $stat[0]['total_likes'];
	    		$topFanStats[$count]['total_get_comments'] = $stat[0]['total_get_comments'];
	    		$topFanStats[$count]['total_get_likes'] = $stat[0]['total_get_likes'];
	    	
	    		$count++;
	    	
	    	}
	    	
	    	$stat = new Model_FansObjectsStats();
	    	$stat = $stat->findFanRecord($this->_fanpageId, $this->_userId);
	    	
	    	$this->view->top_fans_stat = $topFanStats;
	    	$this->view->your_stat = $stat;
    	 
    	}
    	
    	
    	
    	$topArray = array();
    	

		
    	foreach ($toplist as $top){
    		//echo $top['facebook_user_id'];
    		$topArray[] = $follow->getRelation($this->_userId, $top['facebook_user_id'],$this->_fanpageId);
    	}
    	
    	$userBoardData = array();
    	 
    	if(!empty($this->_fanpageId) && !empty($this->_userId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
 
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_topclicker');
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_topfollowed');
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_topfan');
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_toptalker');
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_fanfavorite');
//     		$cache->remove( $this->_fanpageId .'_' .$this->_userId . '_topfanall');
    		try {
    			switch($list){
    				case 'top-followed':
    					if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_topfollowed')){
    						//Look up the $fanpageId
    						$userBoardData= $model->getTopFollowedRankByWeek($this->_fanpageId, $this->_userId);
    							
    						$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_topfollowed');
    					}else {
    						//echo 'memcache look up';
    						$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_topfollowed');
    					}
    					break;
    				case 'top-clicker':
    					if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_topclicker')){
    						//Look up the $fanpageId
    						$userBoardData= $model->getUserTopClickerRankByWeek($this->_fanpageId, $this->_userId);
    							
    						$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_topclicker');
    					}else {
    						//echo 'memcache look up';
    						$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_topclicker');
    					}
    					break;
    					
    				case 'top-talker':
    					if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_toptalker')){
    						//Look up the $fanpageId
    						$userBoardData= $model->getUserTopTalkerRankByWeek($this->_fanpageId, $this->_userId);
    							
    						$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_toptalker');
    					}else {
    						//echo 'memcache look up';
    						$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_toptalker');
    					}
    					break;
    					
    				case 'fan-favorite':
    					if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_fanfavorite')){
    						//Look up the $fanpageId
    						$userBoardData= $model->getUserMostPopularRankByWeek($this->_fanpageId, $this->_userId);
    					
    						$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_fanfavorite');
    					}else {
    						//echo 'memcache look up';
    						$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_fanfavorite');
    					}
    					break;
    				case 'top-fan-all':
    						if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_topfanall')){
    							//Look up the $fanpageId
    							$userBoardData= $model->getUserTopFansRank($this->_fanpageId, $this->_userId);
    								
    							$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_topfanall');
    						}else {
    							//echo 'memcache look up';
    							$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_topfanall');
    						}
    						break;
    				default:
    					if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId . '_topfan')){
    						//Look up the $fanpageId
    						$userBoardData= $model->getUserTopFansRankByWeek($this->_fanpageId, $this->_userId);
    					
    						$cache->save($userBoardData, $this->_fanpageId .'_' .$this->_userId . '_topfan');
    					}else {
    						//echo 'memcache look up';
    						$userBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId . '_topfan');
    					}
    					break;
    			}
    			//Check to see if the $fanpageId is cached and look it up if not
	    			
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level < 3) {
    		for ($i=0; $i<count($toplist); $i++){
    			$toplist[$i]['count'] = '?';
    		}   		 
    	
    		if 	($userBoardData !=null) {
    			$userBoardData['count'] = '?';
    		}
    	}
    	
    	//Zend_Debug::dump($userBoardData);
    
    	//Zend_Debug::dump($toplist);
    	
    	$this->view->toplist = $toplist;
    	$this->view->toplistYou =  $userBoardData;
    	$this->view->toplistArray = $topArray ;
    	$this->view->list = $list;
    	
    	$this->render('toplist');
    }
    
    
    
  	public function leaderboardAction()
  	{	
  		
  		$this->_helper->layout->disableLayout();
  		/*
  		$user = $this->_facebook_user;
  		//Zend_Debug::dump($user);
  		if($user) {
  			$this->view->facebook_user = $user;
  			//$access_token = $this->facebook_user->facebook_user_access_token;
  			//$this->view->feed = $this->getFeed($access_token);
  		}else {
  			$this->view->facebook_user = null;
  		}
  		*/
  		//$this->view->fanpage_id = $this->_fanpageId;
  		
//   		$follow = new Model_Subscribes();
//   		$model = new Model_Rankings;
//   		$fanpage = array(
//   				'topFans'=>array(),
//   				'mostPopular'=>array(),
//   				'topTalker'=>array(),
//   				'topClicker'=>array(),
//   				'topFollowed'=>array(),
//   				'topFansAllTime'=>array()
//   		);
  		
//   		if(!empty($this->_fanpageId)) {
//   			$cache = Zend_Registry::get('memcache');
//   			$cache->setLifetime(1800);
//   			//$cache->remove($this->_fanpageId);
//   			try {
  		
//   				//Check to see if the $fanpageId is cached and look it up if not
//   				if(isset($cache) && !$cache->load($this->_fanpageId)){
//   					//echo 'db look up';
//   					//Look up the $fanpageId
//   					$fanpage['topFans'] = $model->getTopFansByWeek($this->_fanpageId, 5);
//   					//Zend_Debug::dump($topFans);
  		
//   					$fanpage['mostPopular'] = $model->getMostPopularByWeek($this->_fanpageId, 5);
//   					//Zend_Debug::dump($mostPopular);
 
//   					$fanpage['topTalker'] = $model->getTopTalkerByWeek($this->_fanpageId, 5);
//   					//Zend_Debug::dump($topTalker);
  		
//   					$fanpage['topClicker'] = $model->getTopClickerByWeek($this->_fanpageId, 5);
//   					//Zend_Debug::dump($topClicker);
  						
//   					//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
//   					$fanpage['topFollowed'] = $model->getTopFollowedByWeek($this->_fanpageId, 5);
//   					//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
//   					$fanpage['topFansAllTime'] = $model->getTopFans($this->_fanpageId, 5);
//   					//Save to the cache, so we don't have to look it up next time
//   					$cache->save($fanpage, $this->_fanpageId);
//   				}else {
//   					//echo 'memcache look up';
//   					$fanpage = $cache->load($this->_fanpageId);
//   				}
//   			} catch (Exception $e) {
//   				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
//   				//echo $e->getMessage();
//   			}
//   		}
//   		//Zend_Debug::dump($fanpage);
//     	//exit();
    
//     	$stat_model = new Model_FansObjectsStats();
    	
//     	$count=0;
//     	$topArray = array();
//     	$topFanStats = null;
//     	foreach ($fanpage['topFans'] as $top){
//     		//echo $top['facebook_user_id'];
//     		$topArray[$count] = $follow->getRelation($this->_userId, $top['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
    		
//     		$stat = $stat_model ->findFanRecord($this->_fanpageId, $top['facebook_user_id']);
    		 
//     		$topFanStats[$count]['total_posts'] = $stat[0]['total_posts'];
//     		$topFanStats[$count]['total_comments'] = $stat[0]['total_comments'];
//     		$topFanStats[$count]['total_likes'] = $stat[0]['total_likes'];
//     		$topFanStats[$count]['total_get_comments'] = $stat[0]['total_get_comments'];
//     		$topFanStats[$count]['total_get_likes'] = $stat[0]['total_get_likes'];
    		
//     		$count++;
    		
//     	}    	 
//     	$count=0;
//     	$popularArray = array();
//     	foreach ($fanpage['mostPopular'] as $mp){
//     		//echo $top['facebook_user_id'];
//     		$popularArray[$count] = $follow->getRelation($this->_userId, $mp['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
//     		$count++;
    	
//     	}    	
//     	$count=0;
//     	$talkerArray = array();
//     	foreach ($fanpage['topTalker'] as $tt){
//     		//echo $top['facebook_user_id'];
//     		$talkerArray[$count] = $follow->getRelation($this->_userId, $tt['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
//     		$count++;
    		 
//     	}
//   		$count=0;
//   		$clickerArray = array();
//     	foreach ($fanpage['topClicker'] as $tc){
//     		//echo $top['facebook_user_id'];
//     		$clickerArray[$count] = $follow->getRelation($this->_userId, $tc['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
//     		$count++;
    		 
//     	}
//     	$count=0;
//     	$followedArray = array();
//     	foreach ($fanpage['topFollowed'] as $tf){
//     		//echo $top['facebook_user_id'];
//     		$followedArray[$count] = $follow->getRelation($this->_userId, $tf['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
//     		$count++;
    		 
//     	}
//   		$count=0;
  		
//     	$topFansAllTimeArray = array();
//     	foreach ($fanpage['topFansAllTime'] as $top){
//     		//echo $top['facebook_user_id'];
//     		$topFansAllTimeArray[$count] = $follow->getRelation($this->_userId, $top['facebook_user_id'],$this->_fanpageId);
//     		//echo $topArray[$count];
    		
//     		$stat = $stat_model ->findFanRecord($this->_fanpageId, $top['facebook_user_id']);
    		 
//     		$topFansAllTimeStats[$count]['total_posts'] = $stat[0]['total_posts'];
//     		$topFansAllTimeStats[$count]['total_comments'] = $stat[0]['total_comments'];
//     		$topFansAllTimeStats[$count]['total_likes'] = $stat[0]['total_likes'];
//     		$topFansAllTimeStats[$count]['total_get_comments'] = $stat[0]['total_get_comments'];
//     		$topFansAllTimeStats[$count]['total_get_likes'] = $stat[0]['total_get_likes'];
    		
//     		$count++;
    		
//     	} 
    	
//     	$userLeaderBoardData = array();
    	
//     	if(!empty($this->_fanpageId) && !empty($this->_userId)) {
//     		$cache = Zend_Registry::get('memcache');
//     		$cache->setLifetime(1800);
//     		//$cache->remove($this->_fanpageId .'_' .$this->_userId);
    	
//     		try {
    	
//     			//Check to see if the $fanpageId is cached and look it up if not
//     			if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$this->_userId)){
//     				//Look up the $fanpageId
//     				$userLeaderBoardData['topFans'] = $model->getUserTopFansRank($this->_fanpageId, $this->_userId);
    	
//     				$userLeaderBoardData['mostPopular'] = $model->getUserMostPopularRank($this->_fanpageId, $this->_userId);
    	
//     				$userLeaderBoardData['topTalker'] = $model->getUserTopTalkerRank($this->_fanpageId, $this->_userId);
//     				//Zend_Debug::dump($topTalker);
    	
//     				$userLeaderBoardData['topClicker'] = $model->getUserTopClickerRank($this->_fanpageId,$this->_userId);
//     				//Zend_Debug::dump($topClicker);
    	
//     				//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
//     				$userLeaderBoardData['topFollowed'] = $model->getTopFollowedRankByWeek($this->_fanpageId, $this->_userId);
//     				//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
//     				$userLeaderBoardData['topFansAllTime'] = $model->getUserTopFansRank($this->_fanpageId, $this->_userId);
//     				//Save to the cache, so we don't have to look it up next time
//     				$cache->save($userLeaderBoardData, $this->_fanpageId .'_' .$this->_userId);
//     			}else {
//     				//echo 'memcache look up';
//     				$userLeaderBoardData = $cache->load($this->_fanpageId .'_' .$this->_userId);
//     			}
//     		} catch (Exception $e) {
//     			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
//     			//echo $e->getMessage();
//     		}
//     	}
		
//     	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level < 3) {
//     		for ($i=0; $i<count($fanpage['topFans']); $i++){
//     			$fanpage['topFans'][$i]['number_of_posts'] = '?';
//     		}	
    		
//     		for ($i=0; $i<count($fanpage['mostPopular']); $i++){
//     			$fanpage['mostPopular'][$i]['count'] = '?';
//     		}	
    		
//     		for ($i=0; $i<count($fanpage['topTalker']); $i++){
//     			$fanpage['topTalker'][$i]['number_of_posts'] = '?';
//     		}
    		
//     		for ($i=0; $i<count($fanpage['topClicker']); $i++){
//     			$fanpage['topClicker'][$i]['number_of_likes'] = '?';
    		
//     		}
    		
//     		for ($i=0; $i<count($fanpage['topFollowed']); $i++){
//     			$fanpage['topFollowed'][$i]['count'] = '?';
//     		}
//     		for ($i=0; $i<count($fanpage['topFansAllTime']); $i++){
//     			$fanpage['topFansAllTime'][$i]['number_of_posts'] = '?';
//     		}
    		
    	
    		
//     		if 	($userLeaderBoardData['topFans'] !=null) {
//     			$userLeaderBoardData['topFans']['number_of_posts'] = '?';
//     		}
    		
//     		if 	($userLeaderBoardData['mostPopular'] !=null) {
//     			$userLeaderBoardData['mostPopular']['count'] = '?';
//     		}	
    		
//     		if 	($userLeaderBoardData['topTalker'] !=null) {
//     			$userLeaderBoardData['topTalker']['number_of_posts'] = '?';
//     		}	
    		
//     		if 	($userLeaderBoardData['topClicker'] !=null) {
//     			$userLeaderBoardData['topClicker']['number_of_likes'] = '?';
//     		}
    		
//     		if 	($userLeaderBoardData['topFollowed'] !=null) {
//     			$userLeaderBoardData['topFollowed']['count'] = '?';
//     		}
//     		if 	($userLeaderBoardData['topFansAllTime'] !=null) {
//     			$userLeaderBoardData['topFansAllTime']['count'] = '?';
//     		}
//     	}
//     	$stat = new Model_FansObjectsStats();
//     	$stat = $stat->findFanRecord($this->_fanpageId, $this->_userId);
//     	//Zend_Debug::dump($userLeaderBoardData['topFans']);
//     	$stat2 = new Model_FansObjectsStats();
//     	$stat2 = $stat2->findFanRecord($this->_fanpageId, $this->_userId);
    	
    	
//     	$this->view->top_fans_stat = $topFanStats;
//     	$this->view->top_fans_all_time_stat = $topFansAllTimeStats;
//     	$this->view->your_stat = $stat;
//     	$this->view->your_all_time_stat = $stat2;
    	
//     	$this->view->top_fans = $fanpage['topFans'];
//     	$this->view->most_popular = $fanpage['mostPopular'];
//     	$this->view->top_talker = $fanpage['topTalker'];
//     	$this->view->top_clicker = $fanpage['topClicker'];
//     	$this->view->top_followed = $fanpage['topFollowed'];
//     	$this->view->top_fans_all_time = $fanpage['topFansAllTime'];

//     	$this->view->topFanYou =  $userLeaderBoardData['topFans'];
//     	$this->view->popularYou = $userLeaderBoardData['mostPopular'];
//     	$this->view->talkerYou = $userLeaderBoardData['topTalker'];
//     	$this->view->clickerYou = $userLeaderBoardData['topClicker'];
//     	$this->view->followedYou = $userLeaderBoardData['topFollowed'];
//     	$this->view->topFansAllTimeYou = $userLeaderBoardData['topFansAllTime'];
		
//     	$this->view->topFanArray = $topArray ;
//     	$this->view->popularArray = $popularArray ;
//     	$this->view->talkerArray = $talkerArray ;
//     	$this->view->clickerArray = $clickerArray ;
//     	$this->view->followedArray = $followedArray ;
//     	$this->view->topFansAllTimeArray = $topFansAllTimeArray ;
    	
    	$this->render('leaderboard');
  	}

  	/* Action to show login user's wall post */
    public function newsfeedAction() 
    {	
    	$this->_helper->layout->disableLayout();

		$this->view->facebook_user = $this->_facebook_user;		    	
		$follow = new Model_Subscribes();
		$result = $this->feedFirstQuery();

		$latest = $result['posts']->data;
		
		//Zend_Debug::dump($latest);
// 		$post = new Model_Posts();
		
// 		$post = $post->getMyFeedPost($this->_fanpageId, $this->_userId , 10, null);
// 		$feed = array();
// 		$feed = array_merge($this->getPostLikesByBatch(array_slice($post, 0,5)),$this->getPostLikesByBatch(array_slice($post, 5,5)));
		
		//$feed = $result['feed']->data;
	
    	$likesModel = new Model_Likes();
    	$latestlike = array();
    	$latest_comment_relation = array();
    	$latest_comment_like = array();
    	//Zend_Debug::dump($latest);
    	$yourpointslatest = 0;
    	if ($latest != null ){
    		foreach ($latest as $l){
    			if (isset($l->story)){
    			/*
    			$cache = Zend_Registry::get('memcache');
    			$cache->setLifetime(1800);
    			try {
    				$adminPostId = $l->id;
    				
    				$cache->save($l, $adminPostId);
    				//$x = $cache->load($adminPostId);
    				//Zend_Debug::dump($x);
    			} catch (Exception $e) {
    				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    				//echo $e->getMessage();
    			}
    			*/
    			}else{
	    			if($this->_fanpageProfile -> fanpage_level > 2){
	    				$yourpointslatest = $this->postPointsCalculate($l);
	    			}
	    			$latestlike=0;
	    			//echo $top['facebook_user_id'];
	    			
	    			if(isset($l->likes)){
	    				foreach ($l->likes->data as $x){
	    					//echo $x->id;
	    					if($x->id == $this->_userId){
	    						$latestlike=1;
	    						//echo "$latestlike[$count] in the condensed list";
	    						
	    					}
	    					//Zend_Debug::dump( $likes[$count]);
	    					
	    				}
	    				if($latestlike==0){
	
	    					$latestlike = $likesModel->getLikes($this->_fanpageId, $l->id, $this->_userId );
	    					
	    				//Zend_Debug::dump($likes[$count]);
	    				}
	    				
	    			}
	    			if(isset($l->comments->data)){
		    			$count=0;
		    			foreach ( $l->comments->data as $x){
		    				$latest_comment_relation[$count] = $follow->getRelation($this->_userId, $x->from->id,$this->_fanpageId);
		    				
		    				$latest_comment_like[$count] = $likesModel->getLikes($this->_fanpageId, $x->id, $this->_userId );
		    				
		    				$count++;
		    			}
	    			}
	    			$latest = $l;
	    			break;
    			}
    		}
    		
    	}
    	//Zend_Debug::dump($latestrelation);
    	
    	$this->view->latest_comment_relation = $latest_comment_relation;
    	if($this->_fanpageProfile -> fanpage_level > 2){
    		$this->view->yourpointslatest = $yourpointslatest;
    	}
    	//Zend_Debug::dump($latestlike);
    	//Zend_Debug::dump($latest);
    	$this->view->latestlike = $latestlike;
    	$this->view->latest_comment_like = $latest_comment_like;
    	
    	//Zend_Debug::dump($latest_comment_like);
    	
    	$this->view->latest = $latest ;
    	

    	
//     	$likes = array();
//     	$relation = array();
//     	$comment_likes = array();
//     	$comment_relation = array();
//     	$count=0;
//     	$count2 = 0;
//     	if ($feed != null){
    			
//     			foreach ($feed as $posts){
    				
//     				$likes[$count]=0;
//     				//echo $top['facebook_user_id'];
//     				if(isset($posts->likes)){
//     					foreach ($posts->likes->data as $l){
//     						if($l->id == $this->_userId){
//     							$likes[$count]=1;
//     							//echo "$likes[$count] in the condensed list";
//     						}	
//     						//Zend_Debug::dump( $likes[$count]);
//     					}
//     					if($likes[$count]==0){
//     						$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
//     						//Zend_Debug::dump($likes[$count]);
//     					}
//     				}
    					
//     				$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
//     				//echo $likes[$count];
//     				$yourpoints[$count] = 0;
//     				//$points =  new Model_PointLog();
    				
//     				//$points  = $points->getPointsByPost($this->_fanpageId,  $this->_userId, $posts->id );
//     				//echo $posts->id;
//     			//	if ($points['point']!=null){
//     					//echo 'replacing point'. $points['point']. '<br/>';
//     				//	$yourpoints[$count] = $points['point'];
//     				//}
//     				//Zend_Debug::dump( $points);
    				
//     				if (isset($posts->comments->data)){
//     					foreach($posts->comments->data as $c){
    						
//     						$comment_likes[$count2] =  $likesModel->getLikes($this->_fanpageId, $c->id, $this->_userId );
//     						$comment_relation[$count2] = $follow->getRelation($this->_userId, $c->from->id,$this->_fanpageId);
//     						//Zend_Debug::dump($comment_relation[$count2]);
//     						$count2++;
//     					}
//     				}
    				

//     				$yourpoints[$count] = $this->postPointsCalculate($posts);
//     				$count++;
    				
//     			}
    		
//     	}
//     	//Zend_Debug::dump($relation);
//     	//Zend_Debug::dump($comment_relation);
    	
    	
//     	$this->view->yourpoints = $yourpoints;
//     	$this->view->relation = $relation;
    	
//     	$this->view->likes = $likes;
    	
//     	$this->view->comment_relation = $comment_relation;
    	 
//     	$this->view->comment_likes = $comment_likes;
    	
//     	$this->view->post = $feed;
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
    		$cache->setLifetime(600);
    		$topPostId = $this->_fanpageId .'_toppost';
    
    		//$topPosts = $model->getTopPosts($this->_fanpageId, 5);
    		$cache->remove( $topPostId);
    		try {
    			//$topPosts = $cache->remove($topPostId);
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($topPostId)){
    				
    				//echo 'db look up';
    				$topPosts = $model->getTopPosts($this->_fanpageId, 5);
    				$topPosts = $this->getPostLikesByBatch($topPosts);
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
    	
    	//Zend_Debug::dump($topPosts); exit();

    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$relation = array();
    	$likeslist = array();
    	$yourpoints = array();
    	$comment_relation = array();
    	$comment_likes = array();
    	$count=0;
    	$count2 = 0;

   
		//Zend_Debug::dump($topPosts);
    	foreach ($topPosts as $posts){
    		//echo $top['facebook_user_id'];
    
    		
    		$likes[$count]=0;
    		//echo $top['facebook_user_id'];
    		if(isset($posts->likes)){
    			foreach ($posts->likes->data as $l){
    				if($l->id == $this->_userId){
    					$likes[$count]=1;
    					//echo "$likes[$count] in the condensed list";
    				}
    				//Zend_Debug::dump( $likes[$count]);
    			}
    			if($likes[$count]==0){
    				$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    				//Zend_Debug::dump($likes[$count]);
    			}
    		}
    		if (isset($posts->comments->data)){
    		
    			foreach($posts->comments->data as $c){
    					
    				$comment_likes[$count2] =  $likesModel->getLikes($this->_fanpageId, $c->id, $this->_userId );
    				$comment_relation[$count2] = $follow->getRelation($this->_userId, $c->from->id,$this->_fanpageId);
    				//Zend_Debug::dump($comment_relation[$count2]);
    				$count2++;
    			}
    		}
    	
    		//echo $likes[$count];
    		$relation[$count] = $follow->getRelation($this->_userId,  $posts->from->id, $this->_fanpageId);
    		//$pic = $this->getPost($posts['post_id']);
    		//Zend_Debug::dump($pic);
    		//if (($pic->type == 'photo') ||($pic->type == 'video')  ){
    		//	$picture[$count] = $pic -> picture;
    		//}else{
    		//	$picture[$count] = null;
    		//}
    		if($this->_fanpageProfile -> fanpage_level > 2){
    			$yourpoints[$count] = 0;
    			$yourpoints[$count] = $this->postPointsCalculate($posts);
    		}
    		$count++;
    		
    	}

    	//$likeslist[$count] = $this->getPostLikes($posts['post_id']);
    	//Zend_Debug::dump($likeslist); exit();
    	
    	//Zend_Debug::dump($topPosts);
    //	$this->view->picture = $picture;
    	//$this->view->likeslist = $likeslist;
    	$this->view->comment_relation = $comment_relation;
    	 
    	$this->view->comment_likes = $likes;
    	$this->view->likes = $likes;
    	if($this->_fanpageProfile -> fanpage_level > 2){
    		$this->view->yourpointslatest = $yourpoints;
    	}
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
    public function pointlogAction(){
    	$this->_helper->layout->disableLayout();
    	if ($this->_fanpageProfile -> fanpage_level > 2){
    		
    	$pointlog = new Model_PointLog();
    	
    	$pointlog = $pointlog -> getPointsWithinDays($this->_fanpageId, $this->_userId, 3);
    	}else{
    		$pointlog = 'x';
    	}
    	//Zend_Debug::dump($pointlog);
    	$this->view->point_log = $pointlog;
    	$this->render("pointlog");
    }
    
    public function pointlogNotificationAction(){
    	$this->_helper->layout->disableLayout();
    	$time = $this->_request->_getParam('time');
    	$pointlog = new Model_PointLog();
    	 
    	$pointlog = $pointlog -> getPointsSinceTime($this->_fanpageId, $this->_userId, $time);
    	 
    	
    	//Zend_Debug::dump($pointlog);
    	$this->view->point_log = $pointlog;
    	$this->render("pointlog");
    }
    
    
    public function popuppostAction()
    {
    	$this->_helper->layout->disableLayout();

    	$postId = $this->_request->getParam('post_id');
    	
    	//$limit = $this->_request->getParam('limit');
    	//$total = $this->_request->getParam('total');
    	
    	//$originalPost = $this->getPost($postId);
    	$post= $this->getPost($postId);
    	//Zend_Debug::dump($post);
    	$result = array();
    	
    	$result = isset($post->comments->data)?$post->comments->data:null;
    	//$result = $this->getFeedComment($postId, $limit);
    	//$result = json_encode($result);
    	
    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();

    	$likes = array();
    	$relation = array();
    	$count=0;

    	
    	
    	$likesPost[$count]=0;
    	//echo $top['facebook_user_id'];
    	if(isset($post->likes)){
    		foreach ($post->likes->data as $l){
    			if($l->id == $this->_userId){
    				$likesPost[$count]=1;
    				//echo "$likes[$count] in the condensed list";
    			}
    			//Zend_Debug::dump( $likes[$count]);
    		}
    		if($likesPost==0){
    			$likesPost = $likesModel->getLikes($this->_fanpageId, $postId, $this->_userId );
    			//Zend_Debug::dump($likes[$count]);
    		}
    	}
    	
    	$relation[$count] =$follow->getRelation($this->_userId, $post->from->id,$this->_fanpageId);
    	
    	
    	$count=0;
    	if(!empty($result)) {
    		foreach ($result as $posts){
    			$likes[$count]=0;
    			//echo $top['facebook_user_id'];
    			if(isset($posts->user_likes)){
	    				
	    					if($posts->user_likes == true){
	    						$likes[$count]=1;
	    						//echo "$likes[$count] in the condensed list";
	    					}
	    					//Zend_Debug::dump( $likes[$count]);
	    				
	    				
	    				//Zend_Debug::dump( $likes[$count]);
	    		}
	    		if($likes[$count]==0){
	    				$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
	    				//Zend_Debug::dump($likes[$count]);
	    		}
	    	
    		$relation[$count+1] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
    			//echo $likes[$count];
    		$count++;
    		}
    	}

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
    	//$this->view->limit = $limit;
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
    	//$userBadges = new Model_BadgeEvents();
    	//$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $user->facebook_user_id);
    	
    	//Zend_Debug::dump($userBadgeCount);
    	//$allBadges = new Model_Badges();
    	//$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    //	$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	 
    	//$this->view->user_badge = $userBadgeCount[0]['count'];
    	//$this->view->all_badge = $allBadges[0]['count'];
    	//$this->view->overall_achievement = $overallAchievement;
    	
    	//$badges = new Model_Badges();
    	
    	$badges = $this->badgeArray($this->_fanpageId, $user->facebook_user_id);
    	//Zend_Debug::dump($badges);
    	
    	$this->view->badges = $badges;
    	
    	$this->render("awards");
    }
    
    public function redeemAction() {
    	$this->_helper->layout->disableLayout();
    	 
		$user = $this->_facebook_user;
		
    	if($user) {
    		$this->view->facebook_user = $user;
    	}else {
    		$this->view->facebook_user = null;
    	}

    	$rankingModel = new Model_Rankings;
    	$userLeaderBoardData = array();
    	
    	$cache = Zend_Registry::get('memcache');
    	//$cache->remove($this->_fanpageId .'_' .$user->facebook_user_id);
    	try {
    		//Check to see if the $fanpageId is cached and look it up if not
    		if(isset($cache) && !$cache->load($this->_fanpageId .'_' .$user->facebook_user_id)){
    			//Look up the $fanpageId
    			$userLeaderBoardData['topFans'] = $rankingModel->getUserTopFansRank($this->_fanpageId, $user->facebook_user_id);
    	
    		}else {
    			//echo 'memcache look up';
    			$userLeaderBoardData = $cache->load($this->_fanpageId, $user->facebook_user_id);
    		}
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    		//echo $e->getMessage();
    	}
    	
    	//enable top 5 fan restriction
//     	$badgesList = array();
//     	if(!empty($userLeaderBoardData['topFans']['my_rank']) && $userLeaderBoardData['topFans']['my_rank'] < 5) {
//     		$badgeModel = new Model_Badges();
//     		$badgesList = $badgeModel->findByFanpageLevel($this->_fanpageProfile->fanpage_level, $this->_fanpageId, $user->facebook_user_id);
//     	}

//     	$badgesList = array();
//     	$badgeModel = new Model_Badges();
//     	$badgesList = $badgeModel->findByFanpageLevel($this->_fanpageProfile->fanpage_level, $this->_fanpageId, $user->facebook_user_id);
//     	$this->view->badgesList = $badgesList;
    	 
    	$itemList = array();
    	$itemModel = new Model_Items();
    	$itemList = $itemModel->fetchAll();
    	
    	$this->view->itemList = $itemList;

    	$this->render("redeem");
    }
    
    public function myprofileAction() {

    	$this->_helper->layout->disableLayout();
    	//check for user authorization
    	$user = new Model_FacebookUsers();	
   		
    	//$user = $user->find($this->_userId)->current();
    	//Zend_Debug::dump($this->_userId);
    	
    	$fan = new Model_Fans($this->_userId, $this->_fanpageId);
    	$fan_exp = $fan->getCurrentEXP();
    	$fan_exp_required = $fan->getNextLevelRequiredXP();
    	/*
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    		// aplly memcache
			$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
    		$fan = null;
    		
    		try {
    			$fanProfileId = $this->_fanpageId .'_' .$user->facebook_user_id .'_fan';
    			//$cache->remove($fanProfileId);
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanProfileId)){
	    			//echo 'db look up';
	    			$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
	    			$fan = $fan->getFanProfile();
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
    	}*/
    	
    	$follow = new Model_Subscribes();
    	$follower = $follow->getFollowers($this->_userId, $this->_fanpageId);
    	$following = $follow->getFollowing($this->_userId, $this->_fanpageId);
    	//$friends = $follow->getFriends($user->facebook_user_id, $this->_fanpageId);
    	
    	//$userBadges = new Model_BadgeEvents();
    	//$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $this->_userId);

    	//Zend_Debug::dump($userBadgeCount);
    	//$allBadges = new Model_Badges();
    	//$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    	//$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	
    	
    	//$this->view->user_badge = $userBadgeCount[0]['count'];
    	//$this->view->all_badge = $allBadges[0]['count'];
    	//$this->view->overall_achievement = $overallAchievement;
    	
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $this->_userId);
    	
    	$stat_post = $stat[0]['total_posts'];
    	$stat_comment = $stat[0]['total_comments'];
    	$stat_like = $stat[0]['total_likes'];
    	$stat_get_comment = $stat[0]['total_get_comments'];
    	$stat_get_like = $stat[0]['total_get_likes'];
    	
    	
    	//Zend_Debug::dump($stat);
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 1) {
    		$fan->fan_point = '?';
    		$fan->fan_level = '?';
    		$fan_exp = '?';
    		$fan_exp_required ='?';
    		$fan_exp_percentage='?';
    	}
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 2) {
    		$fan->fan_point = '?';
    	}
    	
    	$badgesModel = new Model_BadgeEvents() ;
    	$cb = $fan->getChosenBadges(); 
    	$cb = str_replace("'", "", $cb);
    	$cb = explode(',', $cb);
    	//Zend_Debug::dump($cb);
    	//exit();
    	$chosenBadges = $badgesModel -> getChosenBadges($this->_fanpageId, $this->_userId, $cb);
		
    	if(empty($chosenBadges)){
    		$chosenBadges = $badgesModel -> getBadgesByFanpageIdAndFanID($this->_fanpageId, $this->_userId, 3);
    	}
    	
    	$badges = $badgesModel -> getBadgesByFanpageIdAndFanID($this->_fanpageId, $this->_userId, false);
    	for($count=0;$count < count($badges); $count++){
    		$badges[$count]['description'] = str_replace('[quantity]',$badges[$count]['quantity'] ,$badges[$count]['description']);
    	}
    	
    	$badges2 = $badgesModel -> getBadgesByFanpageIdAndFanIDUnread($this->_fanpageId, $this->_userId, false);
    	for($count=0;$count < count($badges2); $count++){
    		$badges2[$count]['description'] = str_replace('[quantity]',$badges2[$count]['quantity'] ,$badges2[$count]['description']);
    	}
    	//$badges = $this->badgeArray2D($this->_fanpageId, $this->_userId, 6);
    	for($count=0;$count < count($chosenBadges); $count++){
    		$chosenBadges[$count]['description'] = str_replace('[quantity]',$chosenBadges[$count]['quantity'] ,$chosenBadges[$count]['description']);
    	}

    	$this->view->badges = $badges;
    	$this->view->badges2 = $badges2;
    	$this->view->chosen_badges = $chosenBadges;
    	$this->view->fan_exp = $fan_exp;
    	$this->view->fan_exp_required = ($fan_exp == '?')?'?':$fan_exp_required - $fan_exp;
    	$this->view->fan_level_exp = $fan_exp_required;
    	$this->view->fan_exp_percentage = ($fan_exp == '?')?'?':$fan_exp/$fan_exp_required*100;
    	
    	//$this->view->fan = $fan;

    	$this->view->following = $following;
    	//Zend_Debug::dump($following);
    	$this->view->follower = $follower;
    	//$this->view->friends = $friends;
    	
    	
    	//Zend_Debug::dump($fan);
    	
    	$this->view->stat_post = $stat_post;
    	$this->view->stat_comment = $stat_comment;
    	$this->view->stat_like = $stat_like;
    	$this->view->stat_get_comment = $stat_get_comment;
    	$this->view->stat_get_like = $stat_get_like;
    	
    	//Zend_Debug::dump($this->_lastUpdateTime);
   		//$this->view->update_time = $this->_lastUpdateTime;
    	
    	$this->render('myprofile');
    }

    public function choosebadgesAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$badges = new Model_BadgeEvents();
    	$fan = new Model_Fans($this->_userId, $this->_fanpageId);
    	$cb = $fan->getChosenBadges();
    	$cb = str_replace("'", "", $cb);
    	$cb = explode(',', $cb);
    	
    	
    	$chosenBadges = $badges -> getChosenBadges($this->_fanpageId, $this->_userId, $cb);
    	
    	if(empty($chosenBadges)){
    		$chosenBadges = $badges -> getBadgesByFanpageIdAndFanID($this->_fanpageId, $this->_userId, 3);
    	}
    	$badges = $badges -> getBadgesByFanpageIdAndFanID($this->_fanpageId, $this->_userId, false);
    	
    	
    	for($count=0;$count < count($badges); $count++){
    		$badges[$count]['description'] = str_replace('[quantity]',$badges[$count]['quantity'] ,$badges[$count]['description']);
    	}
    	//$this->_fan->chosen_badges;
    	$cb = array();
    	foreach ($chosenBadges as $x){
    		if ($x != 'undefined'){
    			$cb[] = $x['badge_id'];
    		}
    	}
//     	$cb[0] = $chosenBadges[0]['badge_id'];
//     	$cb[1] = $chosenBadges[1]['badge_id'];
//     	$cb[2] = $chosenBadges[2]['badge_id'];
    	//Zend_Debug::dump($cb);
    	$this->view->selected = $cb;
    	$this->view->badges = $badges;
    	$this->render('choosebadges');
    }
    
    public function popoverprofileAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$user_id = $this->_request->getParam('facebook_user_id');
    	$user = new Model_FacebookUsers();
    	$user = $user->find($user_id)->current();// the target
    	//Zend_Debug::dump($user);
    	if($user) {
    		
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    	
    		$client = new Zend_Http_Client;
    		$client->setUri("https://graph.facebook.com/". $user_id );
    		$client->setMethod(Zend_Http_Client::GET);
    		//$client->setParameterGet('access_token', $this->_accessToken);
    		$response = $client->request(); 
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

    		if(!empty ($result)) {
    		
    			$addUser[] = $result;
    			
    			$FDBS = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
    			$FDBS->saveFans($addUser);
    		
    		}
    		$user = new Model_FacebookUsers();
    		$user = $user->find($user_id)->current();// the target

    		//Zend_Debug::dump($user);
    		
    		//$this->view->facebook_user = null;
    	} 
    	
    	$follow = new Model_Subscribes();
    	$relation = $follow->getRelation($this->_userId, $user->facebook_user_id, $this->_fanpageId);
    	$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    	$fan = $fan->getFanProfile();
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	
    	$this->view->facebook_user = $user;
    	$this->view->relation=$relation;
    	$this->view->stat= $stat;
    
    	if ($fan){
    		
    		
    	}else{
    		$fan['fan_point'] = 0;
    		$fan['level']=1;
    		$fan['created_time']=null;
    		$fan['fan_country']=null;
    		
    	}
    	
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 1) {
    		$fan['fan_point'] = '?';
    		$fan['fan_level'] = '?';
   
    	}
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 2) {
    		$fan['fan_point'] = '?';
    	}
    	

    	$this->view->fan = $fan;
    	$this->render('popoverprofile');
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
    		
    			$addUser[] = $result;
    			$FDBS = new Service_FancrankDBService($this->_fanpageId, null);
    			$FDBS->saveFans($addUser);
    			
    		}
    		$user = $user->find( $this->_request->getParam('target'))->current();// the target
    		//Zend_Debug::dump($user);
    		
    		$this->view->facebook_user = $user;
    		//$this->view->facebook_user = null;
    	}
    	

		
    	$follow = new Model_Subscribes();

    	$follower = $follow->getFollowers($user->facebook_user_id, $this->_fanpageId);
    	$following = $follow->getFollowing($user->facebook_user_id, $this->_fanpageId);

    	$relation = $follow->getRelation($this->_userId, $user->facebook_user_id, $this->_fanpageId);
    	$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    	$fan_exp = $fan->getCurrentEXP();
    	$fan_exp_required = $fan->getNextLevelRequiredXP();
    	
    	
    	
    	$cache = Zend_Registry::get('memcache');
    	$cache->setLifetime(1800);
    	$fan = null;
    	try {
    		$fanProfileId = $this->_fanpageId .'_' .$user->facebook_user_id .'_fan';
    		
    		//Check to see if the $fanpageId is cached and look it up if not
    		if(isset($cache) && !$cache->load($fanProfileId)){
    			//echo 'db look up';
    			$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    			//Save to the cache, so we don't have to look it up next time
    			$fan = $fan->getFanProfile();
    			$cache->save($fan, $fanProfileId);
    		}else {
    			//echo 'memcache look up';
    			$fan = $cache->load($fanProfileId);
    		}
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    		//echo $e->getMessage();
    	}
    	
    	
    	//Zend_Debug::dump($fan);
    	//$userBadges = new Model_BadgeEvents();
    	//$userBadgeCount = $userBadges->getNumBadgesByUser($this->_fanpageId, $user->facebook_user_id);

    	//Zend_Debug::dump($userBadgeCount);
    	//$allBadges = new Model_Badges();
    	//$allBadges = $allBadges -> getNumBadges();
    	//Zend_Debug::dump($allBadges);
    	//$overallAchievement = $userBadgeCount[0]['count']/$allBadges[0]['count']*100;
    	
    	
    	//$this->view->user_badge = $userBadgeCount[0]['count'];
    	//$this->view->all_badge = $allBadges[0]['count'];
    	//$this->view->overall_achievement = $overallAchievement;
    	
    
    	//Zend_Debug::dump($fan_level);

    	
    	
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($this->_fanpageId, $user->facebook_user_id);
    	
    	
	    $stat_post = $stat[0]['total_posts'];
	    $stat_comment = $stat[0]['total_comments'];
	    $stat_like = $stat[0]['total_likes'];
	    $stat_get_comment = $stat[0]['total_get_comments'];
	    $stat_get_like = $stat[0]['total_get_likes'];
    	
    	
    	$activitiesModel = new Model_FancrankActivities();
    	$activity = $activitiesModel->getRecentActivities($user->facebook_user_id, $this->_fanpageId, 20);//$activity->getUserActivity($this->_fanpageId, $user->facebook_user_id, 15);
    	
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 1) {
    		
    		$fan->fan_point = '?';
    		$fan->fan_level = '?';
    		$fan_exp = '?';
    		$fan_exp_required ='?';
    		$fan_exp_percentage='?';
    	}
    	if(isset($this->_fanpageProfile->fanpage_level) && $this->_fanpageProfile->fanpage_level == 2) {
    		

    		$fan->fan_point = '?';
    	} 
    	
    	$badges = new Model_BadgeEvents() ;
    	$badges = $badges -> getBadgesByFanpageIdAndFanID($this->_fanpageId, $user->facebook_user_id, 6);
    	for($count=0;$count < count($badges); $count++){
    		$badges[$count]['description'] = str_replace('[quantity]',$badges[$count]['quantity'] ,$badges[$count]['description']);

    	}

    	//$badges = $this->badgeArray2D($this->_fanpageId, $this->_userId, 6);
    	
    	$this->view->badges = $badges;
    	$this->view->fan_exp = $fan_exp;
    	$this->view->fan_exp_required = ($fan_exp === '?')?'?':$fan_exp_required - $fan_exp;
    	$this->view->fan_level_exp = $fan_exp_required;
    	$this->view->fan_exp_percentage = ($fan_exp === '?')?'?':$fan_exp/$fan_exp_required*100;
    	 
    	$this->view->fan = $fan;
    	
    	$this->view->relation = $relation;

    	$this->view->following = $following;
    	$this->view->follower = $follower;
    	//$this->view->friends = $friends;
    	
    	$this->view->post = $activity;
    	//Zend_Debug::dump($stat_post);
    	
    	$this->view->stat_post = $stat_post;
    	$this->view->stat_comment = $stat_comment;
    	$this->view->stat_like = $stat_like;
    	$this->view->stat_get_comment = $stat_get_comment;
    	$this->view->stat_get_like = $stat_get_like;
    	 
    	$this->view->fanpage_id = $this->_fanpageId ;
    	$this->render('userprofile');
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
    	//Zend_Debug::dump($this->_fanpageProfile);
    	
    	//Zend_Debug::dump($result);
    	//exit();
    	$likesModel = new Model_Likes();
		$follow = new Model_Subscribes();
    	$likes = array();
		$relation = array();
		$comment_likes = array();
		$comment_relation = array();
    	$count=0;
    	$count2=0;
		$totalpoints = array();
		$yourpoints = array();
		
	
		if ($result != null){
		
				//Zend_Debug::dump($result);
			
			/*
				foreach ($result as $posts){
					//Zend_Debug::dump($posts);
					//echo $top['facebook_user_id'];
					$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts['post_id'], $this->_userId );
					$relation[$count] = $follow->getRelation($this->_userId, $posts['facebook_user_id'],$this->_fanpageId);
	
					//echo $likes[$count];
					$count++;
				}	
			
			}else{*/
				foreach ($result as $posts){
					//echo $top['facebook_user_id'];
					if ($posts !=null ){
					$likes[$count]=0;
					if(isset($posts->likes->data)){
						foreach ($posts->likes->data as $l){
							//Zend_Debug::dump($posts->likes);
							if($l->id == $this->_userId){
								$likes[$count]=1;
								//echo "$likes[$count] in the condensed list";
							}
							//Zend_Debug::dump( $likes[$count]);
						}
						if($likes[$count]==0){
							$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId);
							//Zend_Debug::dump($likes[$count]);
						}
					}
					
					
					if (isset($posts->comments->data)){
				
						foreach($posts->comments->data as $c){
													
							$comment_likes[$count2] =  $likesModel->getLikes($this->_fanpageId, $c->id, $this->_userId );
							$comment_relation[$count2] = $follow->getRelation($this->_userId, $c->from->id,$this->_fanpageId);
							//Zend_Debug::dump($comment_relation[$count2]);
							$count2++;
						}
					}
					
					
					$relation[$count] = $follow->getRelation($this->_userId, $posts->from->id,$this->_fanpageId);
					//Zend_Debug::dump($posts);
					if($this->_fanpageProfile -> fanpage_level > 2){
						$yourpoints[$count] = 0;
						$yourpoints[$count] = $this->postPointsCalculate($posts);
					}
					//echo $likes[$count];
					}
					$count++;
					
				}
			//}
		}
		


		//Zend_Debug::dump($yourpoints);
		if($this->_fanpageProfile -> fanpage_level > 2){
			$this->view->yourpoints = $yourpoints;
		}
		$this->view->comment_relation = $comment_relation;
		
		$this->view->comment_likes = $likes;
		
		$this->view->relation = $relation;

    	$this->view->likes = $likes;
    	$this->view->post = $result;
    	/*if($viewAs == 'myfeed'){
    		$this->view->myfeedcount = $count;
    		$this->render("fancrankfeedm");
    	}else{*/
    	$this->render("fancrankfeed");
    	//}
    	
    }
    
    protected function postPointsCalculate($posts){
    	
    	$yourpoints = 0;
    	$c = isset($posts->comments->count)?$posts->comments->count:0;
    	$l = isset($posts->likes->count)?$posts->likes->count:0;
    	$y = ($this->_userId == $posts->from->id)?1:0;
    	//echo $c .'..'.$l.'..'.$y.'<br/>';
    	if ($c+$l+$y > 0){
	    	$points =  new Model_PointLog();
	    	//Zend_Debug::dump($posts->id);
	    	$points  = $points->getPointsByPost($this->_fanpageId,  $this->_userId, $posts->id );
	    	//echo $posts->id;
	    	if ($points['point']!=null){
	    	//echo 'replacing point'. $points['point']. '<br/>';
	    		$yourpoints = $points['point'];
	    	}
    	}
    	
    	
    	return $yourpoints;
    	/////////////////////////////////NOTHING BEYOND THIS LINE RUNS
    	/*
    	//Zend_Debug::dump( $points);
    	
    	
    	//if this is your post
    	if($this->_userId == $posts->from->id){
    		$yourpoints -= 5;
    		$uniquereply = array();
    		//if your posts has comments and don't need additional api call
    		if (isset($posts->comments->data)){
    			if (count($posts->comments->data) == $posts->comments->count){
    				foreach($posts->comments->data as $c){
    	
    					//echo $posts->id.'-'.$c->from->id;
    					// if comment not from you, add points
    					if ($c->from->id != $this->_userId){
    							
    						$yourpoints +=2;
    						$uniquereply[] = $c->from->id;
    					}else{
    						//if comment is users, check for likes
    						if (isset($c->like_count)){
    							if ($c->like_count != 0){
    								$ls = $this->getPostLikes($posts->id, $c->like_count);
    								foreach($ls as $l){
    									if($l->id != $this->_userId){
    										$yourpoints +=1;
    									}
    								}
    									
    							}
    						}
    						
    						
    					}
    				}
    			}else{//api called required to get other comments
    				$comm = $this->getFeedComment($posts->id, $posts->comments->count);
    				foreach($comm as $c){
    						
    					//echo $posts->id.'-'.$c->from->id;
    					if ($c->from->id != $this->_userId){
    	
    						$yourpoints +=2;
    						$uniquereply[] = $c->from->id;
    					}else{
    						if (isset($c->like_count)){
    							if ($c->like_count != 0){
    								$ls = $this->getPostLikes($posts->id, $c->like_count);
    								foreach($ls as $l){
    									if($l->id != $this->_userId){
    										$yourpoints +=1;
    									}
    								}
    									
    							}
    						}
    					}
    				}
    			}
    		}
    		//if your post has likes
    		if (isset($posts->likes->data )){ // if dont need api call for likes list
    			if (count($posts->likes->data) == $posts->likes->count){
    				foreach ($posts->likes->data as $l){
    					if($l->id != $this->_userId){
    						$yourpoints +=1;
    						$uniquereply[] = $l->id;
    					}
    				}
    			}else{ // if need api call for complete likes list
    				$ls = $this->getPostLikes($posts->id, $posts->likes->count);
    				foreach($ls as $l){
    					if($l->id != $this->_userId){
    						$yourpoints +=1;
    	
    					}
    				}
    					
    			}
    		}
    		
    		//apply unique count and virginity
    		$uniquereply = array_unique($uniquereply);
    		//Zend_Debug::dump($uniquereply);
    		$yourpoints += count($uniquereply);
    		if (count($uniquereply)>0){
    			$yourpoints += 4;
    		}
    			
    	//if post is by admin
    	}else if ($this->_fanpageId == $posts->from->id)	{
    		// does the admin post have comments
    		if (isset($posts->comments->data)){
    			$limit = 5; // limits of 5 for getting time bonus
    			$post_time = new Zend_Date($posts->created_time);
    			//do we need extra api call for admin comments
    			if (count($posts->comments->data) == $posts->comments->count){
    				foreach($posts->comments->data as $c){
    						
    					//echo $posts->id.'-'.$c->from->id;
    					if ($c->from->id == $this->_userId){
    	
    						$yourpoints +=2;
    	
    					
	    					$comment_time = new Zend_date($c->created_time);
	    	
	    					//Zend_Debug::dump($post_time);
	    					
	    					//give bonus points for before 1 hour difference
	    					$comment_time->sub('1', Zend_Date::HOUR);
	    					if ($comment_time->isEarlier($post_time)){
	    						$yourpoints += ($limit>0)?$limit:0;
	    						$limit--;
	    							
	    					}
	    					
	    					//did your comment get likes
	    					if (isset($c->like_count)){
	    						if ($c->like_count != 0){
	    							$ls = $this->getPostLikes($posts->id, $c->like_count);
	    							foreach($ls as $l){
	    								if($l->id != $this->_userId){
	    									$yourpoints +=1;
	    								}
	    							}
	    								
	    						}
	    					}
    					}	
    				}
    			}else{
    				$comm = $this->getFeedComment($posts->id, $posts->comments->count);
    				foreach($comm as $c){
    						
    					//echo $posts->id.'-'.$c->from->id;
    					if ($c->from->id == $this->_userId){
    							
    						$yourpoints +=2;
    							
    					
	    					$comment_time = new Zend_date($c->created_time);
	    	
	    					//Zend_Debug::dump($post_time);
	    					$comment_time->sub('1', Zend_Date::HOUR);
	    					if ($comment_time->isEarlier($post_time)){
	    						$yourpoints += ($limit>0)?$limit:0;
	    						$limit--;
	    	
	    					}
	    					
	    					//did comment get likes
	    					if (isset($c->like_count)){
	    						if ($c->like_count != 0){
	    							$ls = $this->getPostLikes($posts->id, $c->like_count);
	    							foreach($ls as $l){
	    								if($l->id != $this->_userId){
	    									$yourpoints +=1;
	    								}
	    							}
	    								
	    						}
	    					}
	    					
    					}
    				}
    			}
    		}
    			
    		//////LIKE TIME BONUS IS STILL MISSING YO.
    		if (isset($posts->likes->data )){
    			if (count($posts->likes->data) == $posts->likes->count){
    				foreach ($posts->likes->data as $l){
    					if($l->id == $this->_userId){
    						$yourpoints +=1;
    							
    					}
    				}
    			}else{
    				$ls = $this->getPostLikes($posts->id, $posts->likes->count);
    				foreach($ls as $l){
    					if($l->id == $this->_userId){
    						$yourpoints +=1;
    							
    					}
    				}
    	
    			}
    		}
    			
    			
    	}else{//for other user posts
    			
    		//did you like it?
    		if (isset($posts->likes->data )){
    			if (count($posts->likes->data) == $posts->likes->count){
    				foreach ($posts->likes->data as $l){
    					if($l->id == $this->_userId){
    						$yourpoints +=1;
    	
    					}
    				}
    			}else{
    				$ls = $this->getPostLikes($posts->id,$posts->likes->count );
    				foreach($ls as $l){
    					if($l->id == $this->_userId){
    						$yourpoints +=1;
    	
    					}
    				}
    					
    			}
    		}
    		
    		// did you comment on a user post and get likes on your comments
	    	if (isset($posts->comments->data)){
	    			if (count($posts->comments->data) == $posts->comments->count){
	    				foreach($posts->comments->data as $c){
	    					//echo $posts->id.'-'.$c->from->id;
	    					if ($c->from->id == $this->_userId){
	 							if (isset($c->like_count)){
		    						if ($c->like_count != 0){
		    							$ls = $this->getPostLikes($posts->id, $c->like_count);
		    							foreach($ls as $l){
		    								if($l->id != $this->_userId){
		    									$yourpoints +=1;
		    								}
		    							}
		    								
		    						}
		    					}
	    					}	
	    				}
	    			}else{
	    				$comm = $this->getFeedComment($posts->id, $posts->comments->count);
	    				foreach($comm as $c){
	    					//echo $posts->id.'-'.$c->from->id;
	    					if ($c->from->id == $this->_userId){
								if (isset($c->like_count)){
		    						if ($c->like_count != 0){
		    							$ls = $this->getPostLikes($posts->id, $c->like_count);
		    							foreach($ls as $l){
		    								if($l->id != $this->_userId){
		    									$yourpoints +=1;
		    								}
		    							}
		    								
		    						}
		    					}
		    					
	    					}
	    				}
	    			}
	    		}
	    			
    	}
    	//echo $posts->id.' - '.$yourpoints .'<br/>';
    	return $yourpoints;
    	*/
    }
    
    public function fancrankfeedcommentAction() {
    	$this->_helper->layout->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$postId = $this->_request->getParam('post_id');
		$postType = $this->_request->getParam('post_type');
    	$total = $this->_request->getParam('total');
    	$latest = $this->_request->getParam('latest');
    	$filter = $this->_request->getParam('filter');
    	$popup = $this->_request->getParam('popup');
    	//echo $filter;
    	
    	$result = array();
    	//Zend_Debug::dump($limit);
    	if ($filter == 'true'){
    		
    		$result = $this->filterComments($this->getFeedComment($postId, $total));
    	}else{
    		$result = $this->getFeedComment($postId, $total);
    	}
    	//$result = json_encode($result);
		
    	
    	$follow = new Model_Subscribes();
    	$likesModel = new Model_Likes();
    	$likes = array();
    	$relation = array();
    	$count=0;
		

    	if(!empty($result)) {
    		foreach ($result as $posts){
    			//echo $top['facebook_user_id'];
    			$likes[$count]=0;
    			if(isset($posts->user_likes)){
    				
    				if($posts->user_likes == true){
    					$likes[$count]=1;
    						//echo "$likes[$count] in the condensed list";
    				}
    					//Zend_Debug::dump( $likes[$count]);
    			}
    			
    			if (!isset($posts->user_likes) && ($likes[$count]==0)){
    					$likes[$count] = $likesModel->getLikes($this->_fanpageId, $posts->id, $this->_userId );
    					//Zend_Debug::dump($likes[$count]);
    			}    		

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
    	$this->view->comments = $result;
    	$this->view->postId = $postId;
    	$this->view->postType = $postType;
    	
    	if($latest){
    		$this->view->latest = $latest;
    	}
    	
    	$this->view->popup = $popup;
    	$this->render("fancrankfeedcomment");
    }
    
    public function recentactivitiesAction(){
    	$this->_helper->layout->disableLayout();
    	$source = $this->_request->getParam('source');
    	$userid = $this->_request->getParam('userid');
    	$target_fan = $this->_fan;
  
    	if ($userid != $this->_userId){
	    	$cache = Zend_Registry::get('memcache');
	    	$cache->setLifetime(1800);
	    	$target_fan = null;
	    	try {
	    		$fanProfileId = $this->_fanpageId .'_' .$userid .'_fan';
	    		//Check to see if the $fanpageId is cached and look it up if not
	    		if(isset($cache) && !$cache->load($fanProfileId)){
	    			//echo 'db look up';
	    			$target_fan = new Model_Fans($userid, $this->_fanpageId);
	    			//Save to the cache, so we don't have to look it up next time
	    			$target_fan = $target_fan->getFanProfile();
	    			$cache->save($target_fan, $fanProfileId);
	    		}else {
	    			//echo 'memcache look up';
	    			$target_fan = $cache->load($fanProfileId);
	    		}
	    	} catch (Exception $e) {
	    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
	    		//echo $e->getMessage();
	    	}
    	}
    	$activitiesModel = new Model_FancrankActivities();
  		
    	$limit = 20;
    	$activities = null;
    	if(!empty($this->_fanpageId ) && !empty($userid)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
    		
    		try {
    			$fanActivityId = $this->_fanpageId .'_' .$userid. '_fan_activity';
    			
    			//$cache->remove($fanActivityId);
    			//$cache->remove($fanActivityId);
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanActivityId)){
    				//echo 'db look up';
    				//$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    				$activities = $activitiesModel->getRecentActivities($userid, $this->_fanpageId, $limit);
    				
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($activities, $fanActivityId);
    			}else {
    				//echo 'memcache look up';
    				$activities = $cache->load($fanActivityId);

    				// merge new activity
    			    $newActivity = array();
				    if(!empty($activities[0]['created_time'])) {
				    	$newActivity = $activitiesModel->getRecentActivitiesSince($userid, $this->_fanpageId, $limit, $activities[0]['created_time']);
				    }

			    	
			    	if(count($newActivity) >= $limit) {
			    		//Zend_Debug::dump($newActivity);

			    		$activities = $newActivity;
			    		$cache->save($activities, $fanActivityId);
			    		
			    	}else if(count($newActivity) > 0){
			    		//echo"there are new activities";
			    		$activities = array_merge($newActivity, array_slice($activities, count($newActivity)));
			    		
			    	}
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	
    
  
    	
    	$this->view->activities = $activities;
    	$this->view->act_userid = $userid;
    	$this->view->target_fan = $target_fan ;

    	$this->view->source = $source ;
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
    
	public function upcomingbadgesAction(){
		$this->_helper->layout->disableLayout();
		
		$limit = $this->_request->getParam('limit');
		$badges = $this->badgeArray2D($this->_fanpageId, $this->_userId, $limit);
		
		//$badges = $this->badgeArray($this->_fanpageId, $this->_userId, $limit);
		//Zend_Debug::dump($badges);
		//exit();
		$this->view->upcoming = $badges;
		$this->render("upcomingbadges");
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
    
    protected function filterPosts($posts){
    	$following = new Model_Subscribes;
    	$following = $following->getFollowingList($this->_userId, $this->_fanpageId, false);
    	 
    	$f = array();
    	if ($following){
    		foreach($following as $fol){
    			$f[] = $fol['facebook_user_id_subscribe_to'];
    		}
    	}
    	$f[] = $this->_fanpageId;
    	$f[] = $this->_userId;
    	
    	$filteredPosts = array();
    	if($posts){
    		foreach($posts as $p){
    			Zend_Debug::dump($p);
    			if(in_array($p->from->id,$f)){
    				$filteredPosts[] = $p;
    			}else{
    				if ($p->likes->count > 0){
    					$filteredPosts[] = $p;
    				}
    			}
    		}
    	}
    	
    	return $filteredPosts;
    }
    
    
    
    protected function filterComments($comments){
    	$following = new Model_Subscribes;
    	$following = $following->getFollowingList($this->_userId, $this->_fanpageId, false);
    	
    	$f = array();
    	if ($following){
    		foreach($following as $fol){
    			$f[] = $fol['facebook_user_id_subscribe_to'];
    		}
    	}
    	$f[] = $this->_fanpageId;
    	$f[] = $this->_userId;
    	//Zend_Debug::dump($f);
    	$filteredComments = array();
    	
    	if($comments){
    		foreach($comments as $c){
    			//Zend_Debug::dump($c);
    			if(in_array($c->from->id,$f)){	
    				$filteredComments[] = $c;
    			}else{
    				if ($c->like_count > 0){
    					$filteredComments[] = $c;
    				}
    			}
    		}
    	}
    	

    	return $filteredComments;
    }
    
    protected function getPostLikes($postId, $limit) {
    
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $postId ."/likes");
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
    
    protected function getPostLikesByBatch($posts) {
    	$tmp = array();
    	$finalResult = array();
    	$allpost = array();
    	foreach ($posts as $post) {
    		$id = $post['post_id'];
    		$tmp[] = array('method'=>'GET', 'relative_url'=> "/$id/");
    	}
    	//Zend_Debug::dump($tmp);
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$this->_accessToken;
 
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/?". $batchQueries);
    	$client->setMethod(Zend_Http_Client::POST);
    	 
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	//Zend_Debug::dump($result); 
    	foreach ($result as $post) {
    		if(!empty($post->code) &&  $post->code === 200 && isset($post->body)) {
    			$all = json_decode($post->body);
    			if(isset($all)) {
    				$allpost[] = $all;
    			}else {
    				$allpost[] = array();
    			}
    		}else {
    			$allpost[] = array();
    		}
    	}
    	return $allpost;
    	
    	/*if ( !empty($allpost)){
	    	foreach ($allpost as $likes){
	    		
	    		$updatingPost = new Model_Posts();
	    		if (isset($likes->id)){
	    			$updatingPost = $updatingPost -> findPost($likes->id);
	    		
	    		
		    		if(!empty ($updatingPost)){
				    		if (isset($likes->likes->count) && ($updatingPost['post_likes_count'] != $likes->likes->count)){
				    			$updatingPost ->post_likes_count = $likes->likes->count;
				    		}
				    		if (isset($likes->comments->count) && ($updatingPost['post_comments_count'] != $likes->comments->count)){
				    			$updatingPost ->post_comments_count = $likes->comments->count;
				    		}
			    		$updatingPost->save();
		    		}
	    		}
	    		if (isset($likes->likes)){
	    			$likeslist[] = $likes->likes->data;
	    		}else{
	    			$likeslist[] = array();
	    		}
	    	}
	    	
    	}
    	return null;*/
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
		
		if ($view != 'myfeed'){
	    	$client = new Zend_Http_Client;
	    	$client->setMethod(Zend_Http_Client::GET);
	    	$client->setParameterGet('access_token', $this->_accessToken);
	    	$client->setParameterGet('limit', 10);
	    	//if($view != 'myfeed'){
		    if ($until != 'undefined'){
		    	$client->setParameterGet('until', $until);
		    	
		    }else{
		    	
		    }
		   
		}
		if ($until != 'undefined' && $until != null){ 
			$until = date("Y-m-d h:i:s", $until);
		}else{
			$until = 0;
		}
    	switch ($view){
    		case 'admin':
    				$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/posts");
     				$response = $client->request();
    				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    				//Zend_Debug::dump($result->data);
    				return $result->data;
    		case 'all':
    				$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/feed");
    				$response = $client->request(); 
    				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    				return $result->data;
    			/*case 'user':
    				return $this->feedFilterByUser($result->data, $this->_fanpageId);*/
    		case 'myfeed':   			
    				$post = new Model_Posts();
    				
    				$post = $post->getMyFeedPost($this->_fanpageId, $this->_userId , 10, $until);
    				$myfeed = array();
    				$myfeed = array_merge($this->getPostLikesByBatch(array_slice($post, 0,5)),$this->getPostLikesByBatch(array_slice($post, 5,5)));

    				return $myfeed;
    		default:
    				$client->setUri("https://graph.facebook.com/". $this->_fanpageId ."/feed");
    				$response = $client->request(); 
    				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    				return $result->data;
    		}
    	return $result->data;
    	
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
    	$relationTarget = $follow->getRelation($this->_userId, $target, $this->_fanpageId);
    	
    	//Zend_Debug::dump($result);
    	$this->view->relation= $relation;
    	$this->view->relationTarget= $relationTarget;
    	$this->view->result = $result;
    	
		$this->view->this_user_id = $this->_userId;
    	$this->view->title = 'Followers';
    	$this->view->user_name = $userName;
    	$this->view->user_id = $target;
    	
    	if ($mini){
    		$this->render("miniuserlist");
    	}else{
    		$this->render("userlist");
    	}
    }
    
	public function getlikeslistAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$postid = $this->_request->getParam('post_id');
		$limit = $this->_request->getParam('limit');
		$follow = new Model_Subscribes();
		$count = 0;
		
		$likes = $this->getPostLikes($postid, $limit);
		$result = array();
		foreach ($likes as $r){
			$result[$count]['facebook_user_id'] = $r->id;
			$result[$count]['facebook_user_name'] = $r->name;
			$relation[$count] = $follow->getRelation($this->_userId, $r->id, $this->_fanpageId);
			$count++;
		}
		
		
		$this->view->result = $result;
		$this->view->relation= $relation;
		$this->view->title = 'Likes';
		$this->view->this_user_id = $this->_userId;
		
		
		$this->render("userlist");
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
    	$relationTarget = $follow->getRelation($this->_userId, $target, $this->_fanpageId);
    	$this->view->relation= $relation;
    	$this->view->result = $result;
    	$this->view->title = 'Following';
    	$this->view->relationTarget = $relationTarget;
    	
    	
    	$this->view->user_name = $userName;
    	$this->view->user_id = $target;
    	$this->view->this_user_id = $this->_userId;
    	if ($mini){
    		$this->render("miniuserlist");
    	}else{
    		$this->render("userlist");
    	}

    }
    
    
    public function badgesnotificationAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	
    	$this->render("badgetest");
    }
    
    public function listnotificationAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	
    	$userBadges = new Model_BadgeEvents();
    	
    	$userBadges = $userBadges -> notify($this->_fanpageId, $this->_userId, $this->_fan->last_notification);
    	
    	$this->view->events= $userBadges;

    	$this->render("listnotifications");
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
    

    
    protected function badgeArray2D($fanpage_id, $facebook_user_id, $limit){
    	$badge = new Model_Badges();
    	$fan = new Model_FansObjectsStats();
    	$follow = new Model_Subscribes();
    	$fanRecord = $fan->findFanRecord($fanpage_id, $facebook_user_id);
    	$badge = $badge->getAllBadges($fanpage_id);
    	//Zend_Debug::dump($badge);
    	$array = array();
    	$timearray = array();
    	
    	$timestat = $fan->getStatsByTime($fanpage_id, $facebook_user_id);
    	$timearray['comments']['all']['no-time'] = 0;
    	$timearray['comments']['all']['1-minute'] = 0;
    	$timearray['comments']['all']['10-second'] = 0;
    	$timearray['comments']['status']['no-time'] = 0;
    	$timearray['comments']['status']['1-minute'] = 0;
    	$timearray['comments']['status']['10-second'] = 0;
    	$timearray['comments']['photo']['no-time'] = 0;
    	$timearray['comments']['photo']['1-minute'] = 0;
    	$timearray['comments']['photo']['10-second'] = 0;
    	$timearray['comments']['video']['no-time'] = 0;
    	$timearray['comments']['video']['1-minute'] = 0;
    	$timearray['comments']['video']['10-second'] = 0;
    	$timearray['comments']['link']['no-time'] = 0;
    	$timearray['comments']['link']['1-minute'] = 0;
    	$timearray['comments']['link']['10-second'] = 0;
    	$timearray['get-comments']['all']['no-time'] = 0;
    	$timearray['get-comments']['all']['1-minute'] = 0;
    	$timearray['get-comments']['all']['10-second'] = 0;
    	$timearray['get-comments']['status']['no-time'] = 0;
    	$timearray['get-comments']['status']['1-minute'] = 0;
    	$timearray['get-comments']['status']['10-second'] = 0;
    	$timearray['get-comments']['photo']['no-time'] = 0;
    	$timearray['get-comments']['photo']['1-minute'] = 0;
    	$timearray['get-comments']['photo']['10-second'] = 0;
    	$timearray['get-comments']['video']['no-time'] = 0;
    	$timearray['get-comments']['video']['1-minute'] = 0;
    	$timearray['get-comments']['video']['10-second'] = 0;
    	$timearray['get-comments']['link']['no-time'] = 0;
    	$timearray['get-comments']['link']['1-minute'] = 0;
    	$timearray['get-comments']['link']['10-second'] = 0;
    	$timearray['likes']['all']['no-time'] = 0;
    	$timearray['likes']['all']['1-minute'] = 0;
    	$timearray['likes']['all']['10-second'] = 0;
    	$timearray['likes']['status']['no-time'] = 0;
    	$timearray['likes']['status']['1-minute'] = 0;
    	$timearray['likes']['status']['10-second'] = 0;
    	$timearray['likes']['photo']['no-time'] = 0;
    	$timearray['likes']['photo']['1-minute'] = 0;
    	$timearray['likes']['photo']['10-second'] = 0;
    	$timearray['likes']['video']['no-time'] = 0;
    	$timearray['likes']['video']['1-minute'] = 0;
    	$timearray['likes']['video']['10-second'] = 0;
    	$timearray['likes']['link']['no-time'] = 0;
    	$timearray['likes']['link']['1-minute'] = 0;
    	$timearray['likes']['link']['10-second'] = 0;
    	$timearray['likes']['comment']['no-time'] = 0;
    	$timearray['likes']['comment']['1-minute'] = 0;
    	$timearray['likes']['comment']['10-second'] = 0;
    	$timearray['get-likes']['all']['no-time'] = 0;
    	$timearray['get-likes']['all']['1-minute'] = 0;
    	$timearray['get-likes']['all']['10-second'] = 0;
    	$timearray['get-likes']['status']['no-time'] = 0;
    	$timearray['get-likes']['status']['1-minute'] = 0;
    	$timearray['get-likes']['status']['10-second'] = 0;
    	$timearray['get-likes']['photo']['no-time'] = 0;
    	$timearray['get-likes']['photo']['1-minute'] = 0;
    	$timearray['get-likes']['photo']['10-second'] = 0;
    	$timearray['get-likes']['video']['no-time'] = 0;
    	$timearray['get-likes']['video']['1-minute'] = 0;
    	$timearray['get-likes']['video']['10-second'] = 0;
    	$timearray['get-likes']['link']['no-time'] = 0;
    	$timearray['get-likes']['link']['1-minute'] = 0;
    	$timearray['get-likes']['link']['10-second'] = 0;
    	$timearray['get-likes']['comment']['no-time'] = 0;
    	$timearray['get-likes']['comment']['1-minute'] = 0;
    	$timearray['get-likes']['comment']['10-second'] = 0;
   		
    	foreach ($timestat as $t ){
    		//Zend_Debug::dump($t);
    		$timearray[$t["check"]][$t["post_type"]]["1-minute"] = $t["1-minute"];
    		$timearray[$t["check"]][$t["post_type"]]["10-second"] = $t["10-second"];
    		$timearray[$t["check"]][$t["post_type"]]["no-time"] = $t["no-time"];
    	}
    	
    
    	$timearray['likes']['all']['no-time'] += $timearray['likes']['comment']['no-time'];
    	$timearray['likes']['all']['1-minute'] += $timearray['likes']['comment']['1-minute'];
    	$timearray['likes']['all']['10-second'] += $timearray['likes']['comment']['10-second'];
    	$timearray['get-likes']['all']['no-time'] += $timearray['get-likes']['comment']['no-time'];
    	$timearray['get-likes']['all']['1-minute'] += $timearray['get-likes']['comment']['1-minute'];
    	$timearray['get-likes']['all']['10-second'] += $timearray['get-likes']['comment']['10-second'];
    	/* 
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
    	*/
    	
    	$fw = $follow -> getFollowing($facebook_user_id, $fanpage_id);
    	$fwd = $follow -> getFollowers($facebook_user_id, $fanpage_id);
    	 
    	
    	//Zend_Debug::dump($badge);
    	$count = 0;
    	foreach ($badge as $b){
    		$array[$count]['name'] = $b['name'];
    		$array[$count]['stylename'] = $b['stylename'];
    		$array[$count]['quantity'] = $b['quantity'];
    		$array[$count]['description'] = str_replace('[quantity]',$b['quantity'] ,$b['description']);
    		$array[$count]['picture'] = $b['picture'];
    		$array[$count]['weight'] = $b['weight'];
    		$array[$count]['percentage'] = 'none';
			
    		switch ($b['name']){
    			case 'Comment-General':
    				//$temp =  $fan->getTotalComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['comments']['all']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-General-10sec':
    		
    				$temp =  $timearray['comments']['all']['10-second']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    					
    			case 'Comment-General-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['comments']['all']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Link':
    				//$temp =  $fan->getLinkComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['comments']['link']['no-time']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			case 'Comment-Link-10sec':
    					
    				$temp =  $timearray['comments']['link']['10-second']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Link-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['comments']['link']['1-minute']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Photo':
    				//$temp =  $fan->getPhotoComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['comments']['photo']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Photo-10sec':
    					
    				$temp =   $timearray['comments']['photo']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Photo-1min':
    		
    				//Zend_Debug::dump($temp);
    					
    				$temp =   $timearray['comments']['photo']['1-minute']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Status':
    				//$temp =  $fan->getStatusComments($fanpage_id, $facebook_user_id);
    				$temp =   $timearray['comments']['status']['no-time']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Status-10sec':
    					
    				$temp =   $timearray['comments']['status']['10-second']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Status-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['comments']['status']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Video':
    				//$temp =  $fan->getVideoComments($fanpage_id, $facebook_user_id);
    				$temp =   $timearray['comments']['video']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Video-10sec':
    					
    				$temp = $timearray['comments']['video']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-Video-1min':
    				//Zend_Debug::dump($temp);
    				$temp = $timearray['comments']['video']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			case 'Follow' :
    					
    				$temp =  $fw[0]['Following'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Followed':
    					
    				$temp =  $fwd[0]['Follower'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-General':
    				//$temp =  $fan->getTotalGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-comments']['all']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-General-10sec':
    					
    				$temp = $timearray['get-comments']['all']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-General-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-comments']['all']['1-minute']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Link':
    				//$temp =  $fan->getLinkGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-comments']['link']['no-time']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Link-10sec':
    					
    				$temp = $timearray['get-comments']['link']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Link-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-comments']['link']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Photo':
    				//$temp =  $fan->getPhotoGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-comments']['photo']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Photo-10sec':
    				$temp = $timearray['get-comments']['photo']['10-second']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Photo-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-comments']['photo']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Status':
    				//$temp =  $fan->getStatusGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-comments']['status']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Status-10sec':
    					
    				$temp =  $timearray['get-comments']['status']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Status-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-comments']['status']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Video':
    				//$temp =  $fan->getVideoGetComments($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-comments']['video']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Video-10sec':
    					
    				$temp =  $timearray['get-comments']['video']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Comment-Video-1min':
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-comments']['video']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-General':
    				//$temp =  $fan->getTotalLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['likes']['all']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-General-10sec':
    					
    		
    				$temp =  $timearray['likes']['all']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-General-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['likes']['all']['1-minute']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Link':
    				//$temp =  $fan->getLinkLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['likes']['link']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Link-10sec':
    					
    				$temp =  $timearray['likes']['link']['10-second']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Link-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['likes']['link']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Photo':
    				//$temp =  $fan->getPhotoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['likes']['photo']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Photo-10sec':
    					
    				$temp =  $timearray['likes']['photo']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Photo-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =   $timearray['likes']['photo']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Status':
    				//$temp =  $fan->getStatusLikes($fanpage_id, $facebook_user_id);
    				$temp =   $timearray['likes']['status']['no-time']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Status-10sec':
    					
    				$temp =  $timearray['likes']['status']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Status-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['likes']['status']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Video':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =   $timearray['likes']['video']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Video-10sec':
    					
    				$temp =  $timearray['likes']['video']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Video-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['likes']['video']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Comment':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['likes']['comment']['no-time']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Comment-10sec':
    					
    				$temp =  $timearray['likes']['comment']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-Comment-1min':
    		
    				//Zend_Debug::dump($temp);
    				$temp =   $timearray['likes']['comment']['1-minute']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    					
    		
    		
    			case 'Get-Like-General':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-likes']['all']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			case 'Get-Like-General-10sec':
    					
    				$temp =  $timearray['get-likes']['all']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-General-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-likes']['all']['1-minute']/ $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Link':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-likes']['link']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    					
    			case 'Get-Like-Link-10sec':
    		
    				//Zend_Debug::dump($temp);
    				$temp =   $timearray['get-likes']['link']['10-second']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Link-1min':
    					
    				$temp = $timearray['get-likes']['link']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Photo':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp = $timearray['get-likes']['photo']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Photo-10sec':
    					
    				$temp =  $timearray['get-likes']['photo']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Photo-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =  $timearray['get-likes']['photo']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Status':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-likes']['status']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Status-10sec':
    					
    				$temp =   $timearray['get-likes']['status']['10-second']  / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Status-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp = $timearray['get-likes']['status']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Video':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  $timearray['get-likes']['video']['no-time'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Video-10sec':
    					
    				$temp =   $timearray['get-likes']['video']['10-second'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Video-1min':
    					
    				//Zend_Debug::dump($temp);
    				$temp =   $timearray['get-likes']['video']['1-minute'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Like-Comment':
    					//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    					$temp =  $timearray['get-likes']['comment']['no-time'] / $b['quantity'];
    					$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    					$array[$count]['percentage'] = $temp;
    					break;
    			case 'Get-Like-Comment-10sec':
    						
    					$temp =   $timearray['get-likes']['comment']['10-second'] / $b['quantity'];
    					$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    					$array[$count]['percentage'] = $temp;
    					break;
    			case 'Get-Like-Comment-1min':
    						
    					//Zend_Debug::dump($temp);
    					$temp =   $timearray['get-likes']['comment']['1-minute'] / $b['quantity'];
    					$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    					$array[$count]['percentage'] = $temp;
    					break;
    			case 'Post-General':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				
    				$temp = (isset($fanRecord[0]['total_posts']))?$fanRecord[0]['total_posts'] / $b['quantity']:0;
    				
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Post-Link':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  (isset($fanRecord[0]['post_link']))?$fanRecord[0]['post_link'] / $b['quantity']:0;
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Post-Photo':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  (isset($fanRecord[0]['post_photo']))?$fanRecord[0]['post_photo'] / $b['quantity']:0;
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Post-Status':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($fanRecord);
    				$temp =  (isset($fanRecord[0]['post_status']))?$fanRecord[0]['post_status'] / $b['quantity']:0;
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Post-Video':
    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
    				$temp =  (isset($fanRecord[0]['post_video']))?$fanRecord[0]['post_video'] / $b['quantity']:0;
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-on-Post':
    				$temp =  $fan->getHighestLikeOnPostCount($fanpage_id, $facebook_user_id);
    				if ($temp == null){
    					$temp = 0;
    				}
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['post_likes_count'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Comment-on-Post':
    				$temp =  $fan->getHighestCommentOnPostCount($fanpage_id, $facebook_user_id);
    				//$temp = $temp->current();
    				if ($temp == null){
    					$temp = 0;
    				}
    				$temp =  $temp[0]['post_comments_count'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Like-on-Comment':
    				$temp =  $fan->getHighestLikeOnCommentCount($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				if ($temp == null){
    					$temp = 0;
    				}
    				$temp =  $temp[0]['comment_likes_count'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Admin-Comment':
    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    			case 'Get-Admin-Like':
    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
    				//Zend_Debug::dump($temp);
    				$temp =  $temp[0]['count'] / $b['quantity'];
    				$temp = ($temp>= 1)? 100 : round($temp*100, 2 ,PHP_ROUND_HALF_DOWN);
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			case 'Multiple-Comment':
    		
    				
    				
    				
    				
    				$temp =	( ($timearray['comments']['video']['no-time']/$b['quantity'] > 1)? 1 : $timearray['comments']['video']['no-time']/$b['quantity'] ) +
    						( ($timearray['comments']['status']['no-time']/$b['quantity'] > 1)? 1 : $timearray['comments']['status']['no-time']/$b['quantity'] ) +
    						( ($timearray['comments']['photo']['no-time']/$b['quantity'] > 1)? 1 : $timearray['comments']['photo']['no-time']/$b['quantity'] ) +
    						( ($timearray['comments']['link']['no-time']/$b['quantity'] > 1)? 1 : $timearray['comments']['link']['no-time']/$b['quantity'] ) +
    						( ($timearray['comments']['all']['no-time']/$b['quantity'] > 1)? 1 : $timearray['comments']['all']['no-time']/$b['quantity'] ) ;
    						
    				//Zend_Debug::dump($temp);
    				$temp =  round($temp/5, 2 ,PHP_ROUND_HALF_DOWN);  
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			case 'Multiple-Like':
    				
    				$temp = ( ($timearray['likes']['video']['no-time']/$b['quantity'] > 1)? 1 : $timearray['likes']['video']['no-time']/$b['quantity'] ) +
    						( ($timearray['likes']['status']['no-time']/$b['quantity'] > 1)? 1 : $timearray['likes']['status']['no-time']/$b['quantity'] ) +
    						( ($timearray['likes']['photo']['no-time']/$b['quantity'] > 1)? 1 : $timearray['likes']['photo']['no-time']/$b['quantity'] ) +
    						( ($timearray['likes']['link']['no-time']/$b['quantity'] > 1)? 1 : $timearray['likes']['link']['no-time']/$b['quantity'] ) +
    						( ($timearray['likes']['all']['no-time']/$b['quantity'] > 1)? 1 : $timearray['likes']['all']['no-time']/$b['quantity'] ) ;
    				//Zend_Debug::dump($temp);
    				$temp = round($temp/5, 2 ,PHP_ROUND_HALF_DOWN);  
    				$array[$count]['percentage'] = $temp;
    				break;
    		
    			/*
    			case 'Fan-Favorite-Month': $array[$count]['percentage'] = 'none';break;
    			case 'Fan-Favorite-Week': $array[$count]['percentage'] ='none';break;
    			case 'Fan-Favorite-Year':$array[$count]['percentage'] ='none'; break;
    			case 'Top-Clicker-Month': $array[$count]['percentage'] ='none'; break;
    			case 'Top-Clicker-Week':$array[$count]['percentage'] ='none';  break;
    			case 'Top-Clicker-Year': $array[$count]['percentage'] ='none'; break;
    			case 'Top-Fan-Month':$array[$count]['percentage'] ='none';  break;
    			case 'Top-Fan-Week':$array[$count]['percentage'] ='none';  break;
    			case 'Top-Fan-Year':$array[$count]['percentage'] ='none';  break;
    			case 'Top-Talker-Month':$array[$count]['percentage'] ='none';  break;
    			case 'Top-Talker-Week': $array[$count]['percentage'] ='none'; break;
    			case 'Top-Talker-Year':$array[$count]['percentage'] ='none';  break;
    			case 'Watched-Tutorial':$array[$count]['percentage'] ='none';  break;
    			*/
    			case 'default':
    				break;
    		
    		}
    		
    		
    		
    		$count++;
    	}
    	
    	$upcoming_badges = array();
    	
    	if(!function_exists('badge_cmp')){
	    	function badge_cmp($a, $b){
	    	if ($a['percentage'] == $b['percentage']){
	    		return 0;
	    	}
	    	if ($a['percentage'] > $b['percentage']){
	    		return -1;
	    	}
	    	return 1;
	    	}
    	}
    
    	 
    	usort($array, 'badge_cmp');
    	$j=0;
    	$i=0;
    	
		if (isset($limit)){
			
	    	while ($i < $limit){
	    		if($array[$j]['percentage']<100){
	    			$upcoming_badges[] = $array[$j];
	    			$i++;
	    		}
	    		$j++;
	    	}
		}else{
			$upcoming_badges = $array;
		}
    	return $upcoming_badges;
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
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    				break;
    				case 'Comment-General-10sec':
    				
	    				$temp =  $cg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    				break;
	    				
	    			case 'Comment-General-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Link':
	    				//$temp =  $fan->getLinkComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['link_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Comment-Link-10sec':
	    				
	    				$temp =  $cl10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Link-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cl1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo':
	    				//$temp =  $fan->getPhotoComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['photo_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo-10sec':
	    				
	    				$temp =  $cp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Photo-1min':
	    			
	    				//Zend_Debug::dump($temp);
	    				
	    				$temp =  $cp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status':
	    				//$temp =  $fan->getStatusComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['status_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status-10sec':
	    				
	    				$temp =  $cs10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Status-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cs1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video':
	    				//$temp =  $fan->getVideoComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['video_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video-10sec':
	    				
	    				$temp =  $cv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-Video-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $cv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Follow' :
	    				
	    				 $temp =  $fw[0]['Following'] / $b['quantity'];
	    				 $temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				 $array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Followed':
	    				
	    				$temp =  $fwd[0]['Follower'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General':
	    				//$temp =  $fan->getTotalGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_get_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General-10sec':
	    				
	    				$temp =  $gcg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-General-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break; 
	    			case 'Get-Comment-Link':
	    				//$temp =  $fan->getLinkGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_link_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Link-10sec':
	    				
	    				$temp =  $gcl10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Link-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo':
	    				//$temp =  $fan->getPhotoGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_photo_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo-10sec':
	    				$temp =  $gcp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Photo-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status':
	    				//$temp =  $fan->getStatusGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_status_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status-10sec':
	    				
	    				$temp =  $gcs10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Status-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcs1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video':
	    				//$temp =  $fan->getVideoGetComments($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_video_comments'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video-10sec':
	    				
	    				$temp =  $gcv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Comment-Video-1min':
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gcv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General':
	    				//$temp =  $fan->getTotalLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General-10sec':
	    				
	    			
	    				$temp =  $lg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-General-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link':
	    				//$temp =  $fan->getLinkLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['link_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link-10sec':
	    				
	    				$temp =  $ll10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Link-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $ll1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo':
	    				//$temp =  $fan->getPhotoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['photo_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo-10sec':
	    				
	    				$temp =  $lp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Photo-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status':
	    				//$temp =  $fan->getStatusLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['status_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status-10sec':
	    				
	    				$temp =  $ls10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Status-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $ls1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['video_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video-10sec':
	    				
	    				$temp =  $lv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Video-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['comment_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment-10sec':
	    				
	    				$temp =  $lc10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-Comment-1min':
	    			
	    				//Zend_Debug::dump($temp);
	    				$temp =  $lc1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    		
	 
	    			
	    			case 'Get-Like-General':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_get_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			
	    			case 'Get-Like-General-10sec':
	    				
	    				$temp =  $glg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-General-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glg1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Link':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_link_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			 
	    			case 'Get-Like-Link-10sec':
	   
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glg10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Link-1min':
	    				
	    				$temp =  $gll1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_photo_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo-10sec':
	    				
	    				$temp =  $glp10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Photo-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glp1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_status_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status-10sec':
	    				
	    				$temp =  $gls10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Status-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $gls1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['get_video_likes'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video-10sec':
	    				
	    				$temp =  $glv10s[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Like-Video-1min':
	    				
	    				//Zend_Debug::dump($temp);
	    				$temp =  $glv1m[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-General':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['total_posts'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Link':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_link'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Photo':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_photo'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Status':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_status'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Post-Video':
	    				//$temp =  $fan->getVideoLikes($fanpage_id, $facebook_user_id);
	    				$temp =  $fanRecord[0]['post_video'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-on-Post':
	    				$temp =  $fan->getHighestLikeOnPostCount($fanpage_id, $facebook_user_id);
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['post_likes_count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Comment-on-Post':
	    				$temp =  $fan->getHighestCommentOnPostCount($fanpage_id, $facebook_user_id);
	    				//$temp = $temp->current();
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				$temp =  $temp[0]['post_comments_count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Like-on-Comment':
	    				$temp =  $fan->getHighestLikeOnCommentCount($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				if ($temp == null){
	    					$temp = 0;
	    				}
	    				$temp =  $temp[0]['comment_likes_count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Admin-Comment':
	    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
	    				$array[$b['name']][(string)$b['quantity']]['percentage'] = $temp;
	    			break;
	    			case 'Get-Admin-Like':
	    				$temp =  $fan->getAdminComment($fanpage_id, $facebook_user_id);
	    				//Zend_Debug::dump($temp);
	    				$temp =  $temp[0]['count'] / $b['quantity'];
	    				$temp = ($temp>= 1)? 100 : round($temp*100, 0 ,PHP_ROUND_HALF_DOWN);
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
    
    			}
    		}
    		Zend_Debug::dump($array);
    		exit();
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
    	//$tmp[] = array('method'=>'GET', 'relative_url'=> "/$this->_fanpageId/feed?limit=10");
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$this->_fanpageId/posts?limit=10");
    
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$this->_accessToken;
    
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/?". $batchQueries);
    	$client->setMethod(Zend_Http_Client::POST);
    
    	$response = $client->request();
    
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	 
    	//$feed = array();
    	$posts = array();
//     	if(!empty($result[0]->body)) {
//     		$feed = json_decode($result[0]->body);
//     	}
    
    	if(!empty($result[0]->body)) {
    		$posts = json_decode($result[0]->body);
    	}
    	 
    	//$finalResult['feed'] = $feed;
    	$finalResult['posts'] = $posts;
    	//Zend_Debug::dump($finalResult);
    	return $finalResult;
    }
    
    
    
    
    public function fanrequestAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	
    	$fanRequestModel = new Model_FanRequests();
    	 
    	if ($this->_fanpageProfile->fanpage_level < 3 && ! $fanRequestModel->hasFanRequest($this->_fanpageId, $this->_facebook_user->facebook_user_id)) {
    		$data = array (
    				'facebook_user_id' => $this->_facebook_user->facebook_user_id,
    				'fanpage_id' => $this->_fanpageId,
    				'type' => 'enterprise'
    		);
    		$fanRequestModel->insert($data);
    	}
    }
    

}

