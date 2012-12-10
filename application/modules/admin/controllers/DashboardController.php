<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{
	
	public function preDispatch()
	{
		parent::preDispatch();
		$fp = new Model_Fanpages();
		$fanpageId = $this->_getParam('id');
		$uid = $this->_identity->facebook_user_id;
		$fanpage_admin_model = new Model_FanpageAdmins;
		
		if(!empty($fanpageId) && ! $fanpage_admin_model->findRow($uid, $fanpageId)) {
			$this->_helper->redirector('index', 'index');
		}
		
		if(!empty($fanpageId)) {
			$fanpage = $fp->find($this->_getParam('id'))->current();
			$this->view->page_id = $fanpageId;
			$this->view->fanpage_name = $fanpage->fanpage_name;
		}else {
			//$this->_redirect('http://www.fancrank.com');
		}
		
		$pages = $fp->getActiveFanpagesByUserId( $this->_identity->facebook_user_id);
		$this->view->pages = $pages;
		
		$activepages = array();
		
		foreach ($pages as $x){
			
			if ($x['ranking'] > 0){
				$activepages[] = $x;
			}
			
		}
		//Zend_Debug::dump($activepages);
		$this->view->active_pages = $activepages;
	}
	
    public function indexAction()
    {
    	
    }

    public function fanpagesAction() 
    {
    	$uid = $this->_identity->facebook_user_id;
    	$access_token= $this->_identity->facebook_user_access_token;
    	
    	if(empty($uid) || empty($access_token)) {
    		$this->view->pages = array();
    		return;
    	}
        $fanpages_model = new Model_Fanpages;
        $pages = $fanpages_model->getActiveFanpagesByUserId($uid);
        
        //Zend_Debug::dump($pages);
        
        //$pages = $this->getUserPagesList($uid, $access_token);
  
        $this->view->pages = $pages;
    }

    public function postAction(){
    	 
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$fanpageId = $this->_getParam('id');
    	$postId = $this->_getParam('post_id');

    	$post = $this->getPost($fanpageId,$postId);
    	
    	if (isset($post->comments->count)) {
    		$comments = $this->getComment($fanpageId, $postId, $post->comments->count);
    	}
    	
    	//Zend_Debug::dump($post);
    	//Zend_Debug::dump($comments);
    	$this->view->comment = $comments;
    	$this->view->post = $post;
    	$this->render("post");
    
    }
    
    public function userprofileAction(){
    	
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$fanpageId = $this->_getParam('id');
    	$userId = $this->_getParam('user_id');
    	
    	$fanModel = new Model_Fans($userId, $fanpageId);
    	$fan = $fanModel->getFanProfile();
    	//Zend_Debug::dump($fan);
    	
    	$this->view->fan = $fan ;
    	$this->view->userId = $userId;
    	$this->render("userprofile");
    }
    
    public function pointsAction() {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$fanpageId = $this->_getParam('id');
    	
    	
    	$points = new Model_PointLog();
    	$allpoints = $points ->getFanpagePoints($fanpageId);
    	$x = $points ->getPointsByType($fanpageId);
    	
    
    	foreach ($x as $y){
    		
    		switch ($y['object_type']){
    			case 'comments':
    				$pointsbytype['comments'] = $y['points'];
    				$pointsbytype['comments-bonus'] = $y['bonus'];
    			break;
    			case 'posts':
    				$pointsbytype['posts'] = $y['points'];
    			break;
    			case 'likes':
    				$pointsbytype['likes'] = $y['points'];
    				$pointsbytype['likes-bonus'] = $y['bonus'];
    				break;
    		}
    	}
    	
    	
    	$badges = new Model_BadgeEvents();
    	$badgesPoints = $badges -> getTotalPointsFromBadges($fanpageId);
    	
    	$this->view->badge_points = $badgesPoints;
    	$this->view->points = $allpoints;
    	
    	
    	
    	$this->view->points_by_type = $pointsbytype;

    	$this->view->page_id = $fanpageId;
    	
    	$this->render("points");
    }
    
    public function fanpagescopeAction() {
    	$fanpageId = $this->_getParam('id');
    	
    	$fanpageSettingModel = new Model_FanpageSetting();
    	$settingData = $fanpageSettingModel->findRow($fanpageId);
    	$dataLog = array();
    	$dataLog['activity_type'] = 'admin_change_facebook_scope';
    	$dataLog['event_object'] = '';
    	$dataLog['facebook_user_id'] = $this->_auth->getIdentity()->facebook_user_id;
    	$dataLog['facebook_user_name'] = $this->_auth->getIdentity()->facebook_user_name;
    	$dataLog['fanpage_id'] = $fanpageId;
    	$dataLog['target_user_id'] = $fanpageId;
    	$dataLog['target_user_name'] = '';
    	$dataLog['message'] = 'admin updated facebook scope setting';
    	
    	if($this->_getParam('confirm') === 'save') {

    		$adminActivityModel = new Model_AdminActivities();
    		if($settingData) {
    			$settingData->facebook_scope = $this->_getParam('facebook_scope');
    			//update fanpage setting data
    			$settingData->save();
    			//insert admin activity log
    		}else {
    			$data = $fanpageSettingModel->getDefaultSetting();
    			$data['facebook_scope'] = $this->_getParam('facebook_scope');
    			$data['fanpage_id'] = $fanpageId;
    			$fanpageSettingModel->insert($data);
    		}
    		$adminActivityModel->insert($dataLog);
    	}

    	$this->view->facebook_scope = !empty($settingData) ? $settingData->facebook_scope : Model_FanpageSetting::getDefaultFacebookScope();
    	$this->view->all_scope = Model_FanpageSetting::getAvailableScopeList();
    	$this->view->page_id = $fanpageId;    	
    }
    
    public function myaccountAction()
    {
    	//Zend_Debug::dump($this->_identity);
        $this->view->user_id = $this->_identity->facebook_user_id;
        $this->view->user_email = $this->_identity->facebook_user_email;
        $this->view->user_first_name  = $this->_identity->facebook_user_first_name;
        $this->view->user_last_name = $this->_identity->facebook_user_last_name;
    }

    public function logoutAction() 
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }

    public function previewAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
        $fanpages_model = new Model_Fanpages;
        $follow = new Model_Subscribes();
        $fanpage = $fanpages_model->findByFanpageId($this->_getParam('id'))->current();

        $this->view->installed = $fanpage->installed;
        $this->view->page_id = $this->_getParam('id');
        
        
        
        $colorChoice = new Model_UsersColorChoice;
        $choice = $colorChoice->getColorChoice($this->_getParam('id'))->color_choice;
        if(empty($choice)) {
        	$choice = 1;
        }
       
        $this->view->fanpageTheme = $choice;

        if ($fanpage->active) {
        	//maybe we should be asking for a relavant time from the api user and pass it as a parameter in the queries
        	$topfans = new Model_Rankings();
        	$this->view->top_fans = $topfans->getTopFans($this->_getParam('id'), 5);
        	//Zend_Debug::dump($this->view->top_fans); exit();
        	$this->view->most_popular = $topfans->getMostPopular($this->_getParam('id'), 5);
        	$this->view->top_talker = $topfans->getTopTalker($this->_getParam('id'), 5);
        	$this->view->top_clicker = $topfans->getTopClicker($this->_getParam('id'), 5);
        	$this->view->top_followed = $follow->getTopFollowed($this->_getParam('id'), 5);
        }else {
        	$this->view->top_fans = array();
        	$this->view->most_popular = array();
        	$this->view->top_talker = array();
        	$this->view->top_clicker = array();
        	$this->view->top_followed = array();
        }
        
      
        $this->render("preview");
    }

    /*
     * @return array return an array list of user's page
     */
    protected function getUserPagesList($uid, $userAccessToken) {
    	Service_FancrankFBService::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	
    	if(APPLICATION_ENV !== 'production') {
    		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', 'development');
    		$this->config = $sources->get('facebook');
    		$config = array(
    				'appId'  => $this->config->client_id,
    				'secret' => $this->config->client_secret,
    				'cookie' => true,
    		);
    	}

    	$facebook = new Service_FancrankFBService($config);
    	$facebook->setAccessToken($userAccessToken);
    	try {
    		$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id  FROM page_admin WHERE uid = '.$uid.''));
    		$arr = array();
    		foreach ($pages as $page) {
    			$arr[] = $page['page_id'];
    		}

    		$ids = implode(',', $arr);
    		if(empty($ids)) {
    			return array();
    		}
    		
			$pages = $facebook->api('/?ids=' .$ids);
    		//Zend_Debug::dump($pages); exit();
    		return $pages;
    	}catch(FacebookApiException $e) {
    		//Zend_Debug::dump($e->getResult());
    		error_log(json_encode($e->getResult()));
    		return array();
    	}
    }
    
    public function exportAction() {
    	$this->_helper->layout()->disableLayout();
     	$this->_helper->viewRenderer->setNoRender();
     	
     	$fanpageId = $this->_getParam('id');
     	
     	if(empty($fanpageId)) {
     		return;
     	}
     	
     	header('Content-Type: application/csv');
     	header("Content-Disposition: attachment;filename=$fanpageId" ."_export.csv");
    	
     	$exportType = $this->_getParam('queryType');

     	$fanpageModel = new Model_Fanpages;
     	$result = array();

     	switch ($exportType) {
     		case 'topfans' :      	
		     	$result = $fanpageModel->getTopFanList($fanpageId, 1000, 50);
		     	break;
     		case 'topposts' : 
     			$result = $fanpageModel->getTopPostsByNumberOfLikes($fanpageId, 1000, 50);
     			break;
     		default : break;
     	}
     	
//     	$filename = $fanpageId .'_export.csv';
//     	$this->_helper->contextSwitch()->addContext('csv',
//     			array('suffix' => 'csv',
//     					'headers' => array('Content-Type' => 'application/csv',
//     							'Content-Disposition:' => 'attachment; filename="'. $filename.'"')))->initContext('csv');
    	print_r($this->array_to_scv($result));
    }
    
    public function dashboardAction() {
    	$fanpageId = $this->_getParam('id');
    	
    	/*
    	$postDataByType = $fanpageModel->getPostsStatByFanpageId($fanpageId);

     	
     	
     	$newFans = $fanpageModel->getNewFansNumberSince($fanpageId, $date->toString('yyyy-MM-dd HH:mm:ss'), 5);
     	
    	
     	
     	$topPostByComment = $fanpageModel->getTopPostsByNumberOfComments($fanpageId, 50);
     	
     	//Zend_Debug::dump($topFanList); //exit();
     	$fansNumberBySex = $fanpageModel->getFansNumberBySex($fanpageId);
    	//Zend_Debug::dump($fansNumberBySex);
    	$this->view->fans = $fanpageModel->getFansNumber($fanpageId);
    	$this->view->new_fans = $newFans;
    	
    	//Zend_Debug::dump($this->_getParam('id')); exit();
    	$this->view->post_data = json_encode($postDataByType);
    	
    	
    	
    	
    	$this->view->fansNumberBySex = json_encode($fansNumberBySex);
    	
    	
    	$this->view->topPostByComment = $topPostByComment;
    	
    	$redeemTransactionModel = new Model_RedeemTransactions();
    	$this->view->pending_orders_count = $redeemTransactionModel->getPendingOrdersCountByFanpageId($fanpageId);
    	
    	$fanRequestModel = new Model_FanRequests();
    	$this->view->fan_requests_count = $fanRequestModel->getFanRequestCount();
    	$this->view->total_award_points = $fanpageModel->getTotalAwardPoints($fanpageId);
    	
    	$sideData = $this->getRealtimeInsightData($fanpageId);
    	$this->view->page_view = $sideData['page_view'];
    	$this->view->page_post = $sideData['page_post'];
    	$this->view->user_post = $sideData['user_post'];
    	
    	
    	*/
	}
    
	public function fantableAction(){
		$this->_helper->layout->disableLayout();

		$fanpageId = $this->_getParam('id');
		$tableType = $this->_getParam('type');
		$time = $this->_getParam('time');
		$fanpageModel = new Model_Fanpages;
		
		$fans_model = new Model_Fans;
		
		
		switch ($tableType){
		
			case 'topfan':
				//$topFanList = $fanpageModel->getTopFanList($fanpageId, 100, $time);
				$fanStatModel = new Model_FansObjectsStats();
				$topFanList = $fanStatModel->getTopFanListByFanpageId($fanpageId);
				
				//Zend_Debug::dump($topPostByComment);
				$follow = new Model_Subscribes();
				for ($count = 0 ;$count <count($topFanList); $count ++ ){
					 
					$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
					$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
					if ($fanpageModel->getFanpageLevel($fanpageId) < 3) {
						$topFanList[$count]['fan_exp'] = '?';
					}
				}
				break;
				
			case 'fanfavorite':
				
				$topFanList = $fanpageModel->getFanFavoriteList($fanpageId, 100, $time);
				//Zend_Debug::dump($topPostByComment);
				$follow = new Model_Subscribes();
				for ($count = 0 ;$count <count($topFanList); $count ++ ){
				
					$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
					$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
					if ($fanpageModel->getFanpageLevel($fanpageId) < 3 ) {
						$topFanList[$count]['fan_exp'] = '?';
					}
				}
				break;
 
			case 'toptalkers':
				$topFanList = $fanpageModel->getFanFavoriteList($fanpageId, 100, $time);
				//Zend_Debug::dump($topPostByComment);
				$follow = new Model_Subscribes();
				for ($count = 0 ;$count <count($topFanList); $count ++ ){
				
					$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
					$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
					if ($fanpageModel->getFanpageLevel($fanpageId) < 3 ) {
						$topFanList[$count]['fan_exp'] = '?';
					}
				}
				break;
			
			case 'topclickers':
				$topFanList = $fanpageModel->getFanFavoriteList($fanpageId, 100, $time);
				//Zend_Debug::dump($topPostByComment);
				$follow = new Model_Subscribes();
				for ($count = 0 ;$count <count($topFanList); $count ++ ){
				
					$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
					$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
					if ($fanpageModel->getFanpageLevel($fanpageId) < 3 ) {
						$topFanList[$count]['fan_exp'] = '?';
					}
				}
				break;
				
			case 'topfollowed':
				
				$topFanList = $fanpageModel->getFanFavoriteList($fanpageId, 100, $time);
				//Zend_Debug::dump($topPostByComment);
				$follow = new Model_Subscribes();
				for ($count = 0 ;$count <count($topFanList); $count ++ ){
				
					$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
					$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
					$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
					if ($fanpageModel->getFanpageLevel($fanpageId) < 3 ) {
						$topFanList[$count]['fan_exp'] = '?';
					}
				}
				break;
		}	
		
		//Zend_Debug::dump($topFanList);
		$this->view->tabletype = $tableType;
		$this->view->topFanList = $topFanList;
	}
	
	public function fanprofileAction() {
	
		$fanpageId = $this->_getParam('id');
		
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$userId = $this->_request->getParam('facebook_user_id');
    	
    	$fan = new Model_Fans($userId, $fanpageId);
    	
    	$fan = $fan->getFanProfile();
    	$stat = new Model_FansObjectsStats();
    	$stat = $stat->findFanRecord($fanpageId, $userId);
    	if ($fan){
    	
    	
    	}else{
    		$fan['currency'] = 0;
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
    	$this->view->stat= $stat;
    	$this->view->fan = $fan;
    	
    	$this->render("fanprofile");

	}
	
	public function badgewizardAction() {
		if($this->_getParam('confirm')) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$badgeRuleModel = new Fancrank_Badge_Model_BadgeRules();
			$rule1 = array(
						'table_name'=>$this->_getParam('target'),
						'table_field'=>$this->_getParam('target'),
						'operator'=>$badgeRuleModel->getOperator($this->_getParam('operator')),
						'argument'=>$this->_getParam('argument')
					);
			
			$badgeRuleModel->addRule($rule1);
			
			$stylename = $this->_getParam('name');
			$data = array(
						'name'=> $this->_getParam('name'),
						'stylename'=> empty($stylename) ? $this->_getParam('name') : $stylename,
						'description' => $this->_getParam('description'),
						'weight' => $this->_getParam('weight'),
						'quantity' => $this->_getParam('weight'),
						'picture' => $this->_getParam('picture'),
						'rules' => $badgeRuleModel->getJsonRules()
					);
			
			$badgeModel = Fancrank_BadgeFactory::factory('custom');
			
			try {
				if(!$badgeModel->isDataValid($data)) {
					throw new Exception('invalid badge input data');				
				}
				$badgeId = $badgeModel->insert($data);
				//$badge = $badgeModel->findrow($badgeId);

				$fanpageBadgeModel = new Model_FanpageBadges();
				$fanpageBadgeModel->insert(array('fanpage_id'=>$this->_getParam('id'), 'badge_id'=>$badgeId));
				
				$this->_helper->json(array('message'=>'ok'));
			} catch (Exception $e) {
				$this->_helper->json(array('message'=>'fail'));
				
			}
				
		}
		
		//Zend_Debug::dump($this->_getAllParams());
		$this->view->page_id = $this->_getParam('id');
	}
	
	
	public function facebookinsightsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
		
		$insights = $this->getRealtimeInsightData($fanpageId, false);
		
		Zend_Debug::dump($insights);
		$this->view->pageFanData = json_encode($this->insightPageFansByCountry($insights));
		
		Zend_Debug::dump($this->view->pageFanData);
		$this->render("facebookinsights");
	}
	
	public function homeAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
	
		$fanpageModel = new Model_Fanpages;
		$activityModel = new Model_FancrankActivities();
		$fans_model = new Model_Fans;
		$fanStatModel = new Model_FansObjectsStats();
		$cronModel = new Model_CronLog();
		$pointsModel = new Model_PointLog();
		
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$cache = Zend_Registry::get('memcache');
		$cache->setLifetime(1800);
		
		$ac= $fanpageModel->find($fanpageId)->current();
		//Zend_Debug::dump($token);
		$ac = $ac ->access_token;
		
		try {
			$adminLikesID = $fanpageId .'_admin_likes';
			//echo $adminLikesID;
			//$cache->remove($adminLikesID );
			if(isset($cache) && !$cache->load($adminLikesID )){
				
				$collector = new Service_FancrankCollectorService(null,  $fanpageId, $ac, 'insights');
				$result = $collector->collectFanpageInsight(5, 'likes');

				//Zend_Debug::dump($this->_fan);
				//Save to the cache, so we don't have to look it up next time
				$cache->save($result, $adminLikesID );
			}else {
				//echo 'memcache look up';
				$result = $cache->load($adminLikesID );
			}
		} catch (Exception $e) {
			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
			//echo $e->getMessage();
		}
		
		$date = new Zend_Date();
			
		//$date->subDay(1);
		//$likeStats = array();
		//$diffStats = array();
		$previous = 0;
		$week = 0;
		$month = 0; 
		$first = true;
		$t = 'all';
		foreach ($result as $data) {
			foreach($data->values as $value) {
					
				$time = explode('T', $value->end_time);
		
				//$newTime = str_replace('-', '/', $time[0]);
				$tempdate = new Zend_Date($time[0]);
				
				if ($first){
					$x = 0;
					$first=false;
				}else{
					$x = $value->value - $previous;
				}
				//$diffStats[] = array('value'=> $x,'end_time'=> $newTime);
				//$value->end_time = $newTime;
				//$likeStats[] = $value;
				//echo $date->toString('Y M');
				if ($tempdate->toString('Y w') == $date->toString('Y w')){
					
					$week += $x;
					//echo 'WEEK ' . $tempdate->toString() . ' ' . $week . '<br/>';
				}
				
				if ($tempdate->toString('Y M') == $date->toString('Y M')){
					
					$month += $x;
					//echo 'MONTH' . $tempdate->toString() . ' ' . $month . '<br/>';
				}
				
				$previous = $value->value;
			}
		}
		;
		//$a = array();
		//$a [] = $likeStats;
		//$a [] = $diffStats;
		
		//Zend_Debug::dump($a);
	
		
		//$date = new Zend_Date();
		//echo $date->toString(Zend_Date::WEEK);
		
		
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		//level
		$level = $fanpageModel->getFanpageLevel($fanpageId);
		$this->view->level = $level;
		
		//using fancrank since
		$firsttime = $cronModel -> getFirstCron($fanpageId);
		$this->view->first_time = $firsttime[0]['end_time'];
		
		//cron time
		$crontime = $cronModel-> getLastUpdate($fanpageId);
		$this->view->cron_time = $crontime[0]['end_time'];
		
		$topFanList = $fanStatModel->getTopFanListByFanpageId($fanpageId);
		 
		//Zend_Debug::dump($topPostByComment);
		//$follow = new Model_Subscribes();
	//	for ($count = 0 ;$count <count($topFanList); $count ++ ){
			 
	//		$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
	//		$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
	//		$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
	//		$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
	//		if ($fanpageModel->getFanpageLevel($fanpageId) < 3) {
	//			$topFanList[$count]['fan_exp'] = '?';
	//		}
	//	}
		 
	//	$date = new Zend_Date();
	//	$date->subDay(1);
	//	$newFans = $fanpageModel->getNewFansNumberSince($fanpageId, $date->toString('yyyy-MM-dd HH:mm:ss'));
		
		//$pages = $this->getUserPagesList($uid, $access_token);
	
		//$newInteractionsUsers = $fanpageModel -> getNumOfParticipatedUserWithinDays($fanpageId, 7);
		//$newInteractions = $fanpageModel ->getNumOfInteractionsWithinDays($fanpageId, 7);
		//$activitiesModel = new Model_FancrankActivities();
		//$fanCrankInteractionUsers = $activitiesModel -> getNumofUserInteractionsWithinDays($fanpageId, 7);
		//$fanCrankInteractions = $activitiesModel -> getNumofInteractionsWithinDays($fanpageId, 7);
	//	$newFanCrankUsers = $fanpageModel ->getNewFanCrankUsers($fanpageId);
	
		//chart interactions
		//$topPostByLike = $fanpageModel->getTopObjectsWithinTime($fanpageId, 24);
		//$this->view->topPostByLike = $topPostByLike;
		
		//$points = new Model_PointLog();
		//$points = $points ->getFanpagePoints($fanpageId);
		//CHARTS
		
	//	$this->view->topFanList = $topFanList;
		 
		//Page Likes
		//$likes = $fanpageModel->getFanpageLike($fanpageId);
		$likes = array('month' =>$month, 'week'=>$week, 'all'=>$previous);
		//Zend_Debug::dump($likes);
		
		$this->view->likes = $likes;
		
		//Fans
		$fans = $fanpageModel ->getFanFirstInteractionNumber($fanpageId);
		$this->view->fans = $fans;
		
		//Actived Fans
		$activefans = $fanpageModel ->getActiveFanNumber($fanpageId);
		$this->view->active_fans = $activefans;
		
		//facebook interactions
		$facebookinteractions = $fanpageModel ->getFacebookInteractionsNumber($fanpageId);
		$this->view->facebook_interactions = $facebookinteractions;
		
		$facebookinterationsuniqueusers = $fanpageModel ->getFacebookInteractionsUniqueUsersNumber($fanpageId);
		$this->view->facebook_interactions_unique_users = $facebookinterationsuniqueusers;
		//Zend_Debug::dump($facebookinterationsuniqueusers);

		$fancrankinteractions = $activityModel->getFancrankInteractionsNumber($fanpageId);
		$this->view->fancrank_interactions = $fancrankinteractions;
		
		$fancrankinteractionsuniqueusers = $activityModel->getFancrankInteractionsUniqueUsersNumber($fanpageId);
		$this->view->fancrank_interactions_unique_users = $fancrankinteractionsuniqueusers;
		
		$points = $pointsModel->getFanpagePointsNumber($fanpageId);
		
		//Zend_Debug::dump($points);
		
		$this->view->points = $points;
		//$this->view->new_fans = $newFans;
		//$this->view->new_interaction_users = $newInteractionsUsers;
		//$this->view->new_interaction = $newInteractions;
		//$this->view->new_fancrank_users = $newFanCrankUsers;
		//$this->view->fancrank_interaction_users = $fanCrankInteractionUsers;
		//$this->view->fancrank_interaction = $fanCrankInteractions;

		$this->render("home");
	
	}
	
	public function usersAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
		
		$fanStatModel = new Model_FansObjectsStats();
		$topFanList = $fanStatModel->getTopFanListByFanpageId($fanpageId);
		$fanpageModel = new Model_Fanpages;
		$model = new Model_Rankings;
		$cache = Zend_Registry::get('memcache');
		$cache->setLifetime(1800);
		$topfollowed = array();
		$topclicker = array();
		$fanfavorite = array();
		$toptalker = array();
		$topfanall = array();
		$topfan = array();

		try {		
    		if(isset($cache) && !$cache->load($fanpageId . '_topfollowed')){
    			$topfollowed = $model->getTopFollowedByWeek($fanpageId, 5);
    							
    			$cache->save($topfollowed, $fanpageId . '_topfollowed');
    		}else{
    			$topfollowed = $cache->load($fanpageId . '_topfollowed');
    		}
    	} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    	}
    	
    	try{		
    		if(isset($cache) && !$cache->load($fanpageId . '_topclicker')){
    			$topclicker = $model->getTopClickerByWeek($fanpageId, 5);
    			$cache->save($topclicker, $fanpageId . '_topclicker');
    		}else{
    			$topclicker = $cache->load($fanpageId . '_topclicker');
    		}
    	
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    	}
    	
       	try{			
		    if(isset($cache) && !$cache->load($fanpageId . '_fanfavorite')){
		    	$fanfavorite = $model->getMostPopularByWeek($fanpageId, 5);			
		    	$cache->save($fanfavorite, $fanpageId . '_fanfavorite');
		    }else{
		    	$fanfavorite = $cache->load($fanpageId . '_fanfavorite');
		    }
	    
	    } catch (Exception $e) {
	    	Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
	    	//echo $e->getMessage();
	    }
	    try{		
    		if(isset($cache) && !$cache->load($fanpageId . '_toptalker')){
    			$toptalker = $model->getTopTalkerByWeek($fanpageId, 5);
    				
    			$cache->save($toptalker, $fanpageId . '_topfan');
    		}else{
    			$toptalker = $cache->load($fanpageId . '_topfan');
    		}
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    		//echo $e->getMessage();
    	}
		try{
	    	if(isset($cache) && !$cache->load($fanpageId . '_topfanall')){
	    		$topfanall = $model->getTopFans($fanpageId, 5);
	    		$cache->save($topfanall, $fanpageId . '_topfanall');
	    	}else{
	    		$topfanall = $cache->load($fanpageId . '_topfanall');
	    	}
    	} catch (Exception $e) {
    		Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    		//echo $e->getMessage();
    	}			
    	try{
    		if(isset($cache) && !$cache->load($fanpageId . '_topfan')){
    			$topfan = $model->getTopFansByCurrentMonth($fanpageId, 5);
    			//$toplist = $model->getTopFansByWeek($this->_fanpageId, 5);
    			$cache->save($topfan, $fanpageId . '_topfan');
    		}else{
    			$topfan = $cache->load($fanpageId . '_topfan');
    		}    			
    	} catch (Exception $e) {
  			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
  				//echo $e->getMessage();
    	}
		
		//Zend_Debug::dump($topPostByComment);
		$follow = new Model_Subscribes();
		for ($count = 0 ;$count <count($topFanList); $count ++ ){
			$topFanList[$count]['follower'] = $follow->getFollowers($topFanList[$count]['facebook_user_id'], $fanpageId);
			$topFanList[$count]['follower'] = $topFanList[$count]['follower'][0]['Follower'];
			$topFanList[$count]['following'] = $follow->getFollowing($topFanList[$count]['facebook_user_id'], $fanpageId);
			$topFanList[$count]['following'] = $topFanList[$count]['following'][0]['Following'];
			if ($fanpageModel->getFanpageLevel($fanpageId) < 3) {
				$topFanList[$count]['fan_exp'] = '?';
			}
		}
		
		//Zend_Debug::dump($topFanList);

		$this->view->topFollowed =$topfollowed;
		$this->view->topClicker =$topclicker;
		$this->view->fanFavorite =$fanfavorite;
		$this->view->topTalker =$toptalker;
		$this->view->topFanAll =$topfanall;
		$this->view->topFan =$topfan;
		
		$this->view->topFanList = $topFanList;
		$this->render("users");
		
	}
	
	public function statsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
	
		$this->render("stats");
	
	}
	
	public function adminAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
	
		$postModel = new Model_Posts();
		$adminPost = $postModel->getAllAdminPosts($fanpageId);
		$commentModel = new Model_Comments();
		$adminComment = $commentModel->getAllAdminComments($fanpageId);

		
		$this->view->adminComment = $adminComment;
		$this->view->adminPost = $adminPost;
		
		$this->render("admin");
	
	}
	
	public function settingsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
		//Zend_Debug::dump($this->getRealtimeInsightData($fanpageId));
		
		//points settings
		try {
			$fanpageSettingModel = new Model_FanpageSetting();
			$settingData = $fanpageSettingModel->findRow($fanpageId);
		
			//update new setting
			if($this->_getParam('confirm') === 'save') {
				$data = array(
						'point_like_normal'=>$this->_getParam('point_like_normal'),
						'point_comment_normal'=>$this->_getParam('point_comment_normal'),
						'point_post_normal'=>$this->_getParam('point_post_normal'),
						'point_like_admin'=>$this->_getParam('point_like_admin'),
						'point_comment_admin'=>$this->_getParam('point_comment_admin'),
						'point_bonus_duration'=>$this->_getParam('point_bonus_duration'),
						'point_virginity'=>$this->_getParam('point_virginity'),
						'point_comment_limit'=>$this->_getParam('point_comment_limit')
				);
		
				$dataLog = array();
				$dataLog['activity_type'] = 'admin_change_point_setting';
				$dataLog['event_object'] = '';
				$dataLog['facebook_user_id'] = $this->_auth->getIdentity()->facebook_user_id;
				$dataLog['facebook_user_name'] = $this->_auth->getIdentity()->facebook_user_name;
				$dataLog['fanpage_id'] = $fanpageId;
				$dataLog['target_user_id'] = $fanpageId;
				$dataLog['target_user_name'] = '';
				$dataLog['message'] = 'admin updated point setting';
				$adminActivityModel = new Model_AdminActivities();
		
				if($settingData) {
					$hasChange = false;
					foreach ($data as $key=>$value) {
						if($key !== 'top_post_choice' && !is_numeric($value)) throw new Exception('invalid argument');
						if($value != $settingData->{$key}) {
							$settingData->{$key} = $value;
							$hasChange = true;
						}
					}
		
					if($hasChange) {
						//update fanpage setting data
						$settingData->save();
						//insert admin activity log
						$adminActivityModel->insert($dataLog);
					}else {
						echo 'no new change';
					}
				}else {
					//insert new paget setting
					$data['fanpage_id'] = $fanpageId;
					if(!$fanpageSettingModel->isDataValid($data)) throw new Exception('invalid argument');
					$fanpageSettingModel->insert($data);
					//insert admin activity log
					$adminActivityModel->insert($dataLog);
				}
				 
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		$this->view->setting = empty($settingData) ? $fanpageSettingModel->getDefaultSetting() : $settingData->toArray();
		
		
		
		
		
		//badge settings
		$b = new Model_Badges();

		$allBadges = $b-> getAllBadges($fanpageId);
	
		for($count=0;$count < count($allBadges); $count++){
			$allBadges[$count]['description'] = str_replace('[quantity]',$allBadges[$count]['quantity'] ,$allBadges[$count]['description']);
		}
		$this->view->allBadges = $allBadges;
		
		
		//color
		$colorChoice = new Model_UsersColorChoice;
		$choice = $colorChoice->getColorChoice($fanpageId)->color_choice;
		if(empty($choice)) {
			$choice = 1;
		}
		$this->view->color = $choice;
		
		$this->render("settings");
	
	}
	
	public function previewloginAction(){
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$fanpageId = $this->_request->getParam('id');
			
		$model = new Model_Rankings;
		$fanpage2 = array(
				'topFans'=>array(),
				'mostPopular'=>array(),
				'topTalker'=>array(),
				'topClicker'=>array(),
				'topFollowed'=>array(),
				'topFansAllTime'=>array()
		);
		
		$cache = Zend_Registry::get('memcache');
		$cache->setLifetime(1800);
			
		if(!empty($fanpageId)) {
		
			try {
		
				//$cache->remove($fanpageId);
				//Check to see if the $fanpageId is cached and look it up if not
				if(isset($cache) && !$cache->load($fanpageId)){
		
					//echo 'db look up';
					//Look up the $fanpageId
					$fanpage2['topFans'] = $model->getTopFansByWeek($fanpageId, 5);
					//Zend_Debug::dump($topFans);
		
					$fanpage2['mostPopular'] = $model->getMostPopularByWeek($fanpageId, 5);
					//Zend_Debug::dump($mostPopular);
		
					$fanpage2['topTalker'] = $model->getTopTalkerByWeek($fanpageId, 5);
					//Zend_Debug::dump($topTalker);
		
					$fanpage2['topClicker'] = $model->getTopClickerByWeek($fanpageId, 5);
					//Zend_Debug::dump($topClicker);
		
					//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
					$fanpage2['topFollowed'] = $model->getTopFollowedByWeek($fanpageId, 5);
					//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
					$fanpage2['topFansAllTime'] = $model->getTopFans($fanpageId, 5);
					//Save to the cache, so we don't have to look it up next time
					$cache->save($fanpage2, $fanpageId);
				}else {
					//echo 'memcache look up';
					$fanpage2 = $cache->load($fanpageId);
				}
			} catch (Exception $e) {
				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
				//echo $e->getMessage();
			}
		}
		$color = $this->_getParam('color');
	
		$this->view->color = $color;
		
		$this->view->top_fans = $fanpage2['topFans'];
		$this->view->most_popular = $fanpage2['mostPopular'];
		$this->view->top_talker = $fanpage2['topTalker'];
		$this->view->top_clicker = $fanpage2['topClicker'];
		$this->view->top_followed = $fanpage2['topFollowed'];
		$this->view->top_fans_all_time = $fanpage2['topFansAllTime'];
		$this->render("preview/login");
	}
	
	
	public function previewnewsfeedAction(){
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);	
		
		$fanpageId = $this->_request->getParam('id');
		
		$result = $this->feedFirstQuery($fanpageId);
		
		$latest = $result['posts']->data;
		$feed = $result['feed']->data;
		
		$follow = new Model_Subscribes();

		$relation = array();
		$count=0;
		 
		if ($feed != null){
		
			foreach ($feed as $posts){
				$relation[$count] = $follow->getRelation($fanpageId, $posts->from->id,$fanpageId);
				$count++;
			}
		
		}
		$this->view->latest = $latest ;
		$this->view->post = $feed;
		$this->view->relation = $relation;
		//Zend_Debug::dump($fanpage);
		$this->view->fanpage_id = $fanpageId;
		
		$this->render("preview/newsfeed");
	}
	
	
	public function badgeAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$fanpageId = $this->_request->getParam('id');
		
		$badges = new Model_BadgeEvents();
	
		$mostAwarded = $badges -> getMostAwardedBadges($fanpageId);
		for($count=0;$count < count($mostAwarded); $count++){
			$mostAwarded[$count]['description'] = str_replace('[quantity]',$mostAwarded[$count]['quantity'] ,$mostAwarded[$count]['description']);
		}
	
		$recentBadges = $badges -> recentAwardedBadges($fanpageId);
		for($count=0;$count < count($recentBadges); $count++){
			$recentBadges[$count]['description'] = str_replace('[quantity]',$recentBadges[$count]['quantity'] ,$recentBadges[$count]['description']);
		}	
		$userMostBadges = $badges -> getUsersWithMostBadge($fanpageId);
		
		$totalBadges = $badges -> getTotalAwardedBadges($fanpageId);
		$totalPoints = $badges -> getTotalPointsFromBadges($fanpageId);
		$badgesbytime = $badges -> badgesAwardedByTime($fanpageId);
		
		$this->view->mostAwarded = $mostAwarded;
		$this->view->recentBadges = $recentBadges;
		$this->view->userMostBadges = $userMostBadges;
		$this->view->totalBadges = $totalBadges;
		$this->view->totalPoints = $totalPoints;
		$this->view->badgesbytime = $badgesbytime ;
		
		
		
		$this->render("badge");
	}
	
	public function redeemAction() {
		$this->_helper->layout->disableLayout();
		$fanpageId = $this->_getParam('id');
		$leaderboardModel = new Model_LeaderboardLog();	
		$lastMonthTopFans = $leaderboardModel->getLastMonthTopFans($fanpageId);
		
		$redeemTransactionModel = new Model_RedeemTransactions();
		
		$this->view->requestRedeemItemList = $redeemTransactionModel->getPendingOrdersListByFanpageId($fanpageId);
		$this->view->redeemHistoryList = $redeemTransactionModel->getRedeemHistory($fanpageId);
		$this->view->lastMonthTopFans = $lastMonthTopFans;
	}
	
	public function showlogAction() {
		$activityModel = new Model_FancrankActivities();
		$logType = $this->_getParam('logType');
		$fanpageId = $this->_getParam('id');
		if(empty($fanpageId)) {
			return;
		}
		try {
			switch($logType) {
				case 'like' :
					$logList = $activityModel->getRecentFanpageLikeActivities($fanpageId);
					$result = array(
							'sEcho'=> 1,
							'aaData'=>$logList
					);						
					$this->_helper->json($result);
					//Zend_Debug::dump($result);
					break;
				case 'comment' :
					$logList = $activityModel->getRecentFanpageCommentActivities($fanpageId);
					$result = array(
							'sEcho'=> 1,
							'aaData'=>$logList
					);					
					$this->_helper->json($result);
					//Zend_Debug::dump($result);
					break;
				case 'post' :
					$logList = $activityModel->getRecentFanpagePostActivities($fanpageId);
					$result = array(
							'sEcho'=> 1,
							'iTotalRecords'=> '10',
							'iTotalDisplayRecords'=> '10',
							'aaData'=>$logList
					);
					$this->_helper->json($result);
					break;
				case 'subscribe' : break;
				case 'pointlog' :
					$pointLogModel = new Model_PointLog();
					$logList = $pointLogModel->getFanpagePointLog($fanpageId, 100);
					$result = array(
							'sEcho'=> 1,
							'iTotalRecords'=> '10',
							'iTotalDisplayRecords'=> '10',
							'aaData'=>$logList
					);
					$this->_helper->json($result);
					break;
				case 'overall' :
					$logList = $activityModel->getRecentFanpageActivities($fanpageId);
					$result = array(
							'sEcho'=> 1,
							'iTotalRecords'=> '10',
							'iTotalDisplayRecords'=> '10',
							'aaData'=>$logList
					);					
					$this->_helper->json($result);
					break;
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$this->view->page_id = $fanpageId;
	}
	
	private function array_2_csv($array) {
		$csv = array();
		foreach ($array as $item) {
			if (is_array($item)) {
				$csv[] = $this->array_2_csv($item);
			} else {
				$csv[] = $item;
			}
		}
		return implode(',', $csv);
	}
	
	private function array_to_scv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"')
	{
		$output = null;
		if (!is_array($array) or !is_array($array[0])) return false;
	
		//Header row.
		if ($header_row)
		{
			foreach ($array[0] as $key => $val)
			{
				//Escaping quotes.
				$key = str_replace($qut, "$qut$qut", $key);
				$output .= "$col_sep$qut$key$qut";
			}
			$output = substr($output, 1)."\n";
		}
		//Data rows.
		foreach ($array as $key => $val)
		{
			$tmp = '';
			foreach ($val as $cell_key => $cell_val)
			{
				//Escaping quotes.
				$cell_val = str_replace($qut, "$qut$qut", $cell_val);
				$tmp .= "$col_sep$qut$cell_val$qut";
			}
			$output .= substr($tmp, 1).$row_sep;
		}
	
		return $output;
	}
	
	private function getRealtimeInsightData($fanpageId, $parse = true) {
	
		$insightId = $fanpageId .'_insights';

		$insightData = null;
		try {
			$cache = Zend_Registry::get('memcache');
			//$cache->remove($insightId);	
			if(isset($cache) && !$cache->load($insightId)){
				//Look up the facebook graph api
				echo 'look up facebook graph api';
				
				$fanpageModel = new Model_Fanpages();
				$fanpage = $fanpageModel->findRow($fanpageId);
				$client = new Zend_Http_Client;
				$client->setUri("https://graph.facebook.com/$fanpageId/insights?access_token=". $fanpage->access_token);
				$client->setMethod(Zend_Http_Client::GET);
				
				$response = $client->request();
				
				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
				Zend_Debug::dump($result);
				if(!empty($result->data)) {
					$insightData = $result->data;
					//Save to the cache, so we don't have to look it up next time
					$cache->save($insightData, $insightId);
				}
			}else {
				//echo 'memcache look up';
				$insightData = $cache->load($insightId);
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
		}
		if ($parse) {
			return $this->insightDataParser($insightData);
		}
		
		return $insightData;
	}
	
	private function insightDataParser($insightData) {
		$result = array();
		$counter = 2;
		foreach ($insightData as $data) {
			if(preg_match('/\/day$/', $data->id)) {
				switch($data->name) {
					case 'page_views_login_unique' :
						if(!empty($data->values)) {
							$value = $data->values[sizeof($data->values)-1];
							$result['page_view'] = empty($value->value) ? 0 : $value->value;
						}
						$counter--;
						break;
					case 'page_story_adds_by_story_type_unique' :
						if(!empty($data->values)) {
							$value = $data->values[sizeof($data->values)-1];
							$result['page_post'] = empty($value->value->{'page post'}) ? 0 : $value->value->{'page post'};
							$result['new_fan'] =  empty($value->value->fan) ? 0 : $value->value->fan;
							$result['user_post'] = empty($value->value->{'user post'}) ? 0 : $value->value->{'user post'};
						}
						$counter++;
						break;
				}
			}
			
			//early terminate
			if($counter < 1) break;
		}
		return $result;
	}
	
	private function insightPageFansByLanguage($insightData) {
		$result = array();
		foreach ($insightData as $data) {
			if(preg_match('/\/page_fans_locale\/lifetime$/', $data->id)) {
				$value = $data->values[sizeof($data->values)-1];
				$result = empty($value->value) ? 0 : $value->value;
				break;
			}
		}
		
		$newResult = array();
		foreach ($result as $key => $value) {
			$parts = explode("_", $key);
			$newResult[$parts[1]] = $value;
		}
		return $newResult;
	}
	
	private function insightPageFansByCountry($insightData) {
		$result = array();
		foreach ($insightData as $data) {
			if(preg_match('/\/page_fans_country\/lifetime$/', $data->id)) {
				$value = $data->values[sizeof($data->values)-1];
				$result = empty($value->value) ? 0 : $value->value;
				break;
			}
		}
	
		return $result;
	}
	protected function getPost($fid,$postId){
		$fp = new Model_Fanpages();
		$fanpage = $fp->find($fid)->current();
		
		$client = new Zend_Http_Client;
		$client->setUri("https://graph.facebook.com/". $postId);
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('access_token', $fanpage->access_token);
		
		 
		$response = $client->request();
		 
		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		 
		//Zend_debug::dump($result);
		 
		if(!empty ($result)) {
			return $result;
		}
	}
	
	protected function getComment($fid, $postId, $limit) {
		$fp = new Model_Fanpages();
		$fanpage = $fp->find($fid)->current();
		$client = new Zend_Http_Client;
		$client->setUri("https://graph.facebook.com/". $postId ."/comments");
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('access_token', $fanpage->access_token);
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
	/*
	private function feedFirstQuery($fanpage_id) {
		
		$fanpage = new Model_Fanpages();
		$fanpage = $fanpage->find($fanpage_id)->current();
		$tmp[] = array('method'=>'GET', 'relative_url'=> "/$fanpage_id/feed?limit=10");
		$tmp[] = array('method'=>'GET', 'relative_url'=> "/$fanpage_id/posts?limit=10");
	
		$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$fanpage ->access_token;
	
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
	*/
}

