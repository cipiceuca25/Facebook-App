<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{
	public function preDispatch()
	{
		parent::preDispatch();
		$fanpageId = $this->_getParam('id');
		$uid = $this->_identity->facebook_user_id;
		$fanpage_admin_model = new Model_FanpageAdmins;
		if(!empty($fanpageId) && ! $fanpage_admin_model->findRow($uid, $fanpageId)) {
			$this->_helper->redirector('index', 'index');
		}
		if(!empty($fanpageId)) {
			$fanpage = new Model_Fanpages();
			$fanpage = $fanpage->find($this->_getParam('id'))->current();
			
			$this->view->fanpage_name = $fanpage->fanpage_name;
		}else {
			//$this->_redirect('http://www.fancrank.com');
		}
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
        //$pages = $this->getUserPagesList($uid, $access_token);
  
        $this->view->pages = $pages;
    }

    public function pointsettingAction() {
    	$fanpageId = $this->_getParam('id');
    	
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
    	$this->view->page_id = $fanpageId;
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
        $fanpages_model = new Model_Fanpages;
        $follow = new Model_Subscribes();
        $fanpage = $fanpages_model->findByFanpageId($this->_getParam('id'))->current();

        $this->view->installed = $fanpage->installed;
        $this->view->page_id = $this->_getParam('id');
        $colorChoice = new Model_UsersColorChoice;
        $choice = $colorChoice->getColorChoice($this->_getParam('id'))->color_choice;
        if(empty($choice)) {
        	$choice = 3;
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
    	
    	$fanpageModel = new Model_Fanpages;
    	 
    	$fans_model = new Model_Fans;
    	
     	$postDataByType = $fanpageModel->getPostsStatByFanpageId($fanpageId);

     	$date = new Zend_Date();
     	$date->subDay(2);
     	
     	$newFans = $fanpageModel->getNewFansNumberSince($fanpageId, $date->toString('yyyy-MM-dd HH:mm:ss'), 5);
     	
    	
     	$topPostByLike = $fanpageModel->getTopPostsByNumberOfLikes($fanpageId, 50);
     	$topPostByComment = $fanpageModel->getTopPostsByNumberOfComments($fanpageId, 50);
     	
     	//Zend_Debug::dump($topFanList); //exit();
     	$fansNumberBySex = $fanpageModel->getFansNumberBySex($fanpageId);
    	//Zend_Debug::dump($fansNumberBySex);
    	$this->view->fans = $fanpageModel->getFansNumber($fanpageId);
    	$this->view->new_fans = $newFans;
    	$this->view->page_id = $fanpageId;
    	//Zend_Debug::dump($this->_getParam('id')); exit();
    	$this->view->post_data = json_encode($postDataByType);
    	$this->view->fansNumberBySex = json_encode($fansNumberBySex);
    	
    	$this->view->topPostByLike = $topPostByLike;
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
	
	private function getRealtimeInsightData($fanpageId) {
	
		$insightId = $fanpageId .'_insights';

		$insightData = null;
		try {
			$cache = Zend_Registry::get('memcache');
				
			if(isset($cache) && !$cache->load($insightId)){
				//Look up the facebook graph api
				//echo 'look up facebook graph api';
				
				$fanpageModel = new Model_Fanpages();
				$fanpage = $fanpageModel->findRow($fanpageId);
				$client = new Zend_Http_Client;
				$client->setUri("https://graph.facebook.com/$fanpageId/insights?access_token=". $fanpage->access_token);
				$client->setMethod(Zend_Http_Client::GET);
				
				$response = $client->request();
				
				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
				
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
		
		return $this->insightDataParser($insightData);
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
}

