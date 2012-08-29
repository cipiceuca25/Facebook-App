<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Collectors_FacebookController extends Fancrank_Collectors_Controller_BaseController
{
    public function init()
    {
        parent::init();
        
        // get the fanpage object
        /*
        $this->fanpages = new Model_Fanpages;
        $fanpage = $this->fanpages->findRow($this->_getParam(0));

        if ($fanpage === null) {
            // TODO not exiting
            Log::Err('Invalid Fanpage ID: "%s"', $this->_getParam(0));
            exit;
        } else if (!$fanpage->active) {
            Log::Info('Inactive Fanpage ID: "%s". Exiting.', $this->_getParam(0));
            exit;
        } else {
            $this->fanpage = $fanpage;
        }
        */
    }
	
    public function testimagecreateAction(){
   
    	// Create image instances
    	$dest = imagecreatefrompng(APPLICATION_PATH.'\..\public\img\headermerge.png');
    	$src = imagecreatefrompng(APPLICATION_PATH.'\..\public\img\topfan-mini.png');
    	$src2 = imagecreatefrompng(APPLICATION_PATH.'\..\public\img\checkrank.png');
    	
    	
    	$model = new Model_Rankings;
    	$topFans = $model->getTopFans('216821905014540', 5);
    	
    	// imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
    	
    	
    	

    	$blue= imagecolorallocate($src, 23, 61, 113);
    	$white = imagecolorallocate($src, 255, 255, 255);
    	$gray = imagecolorallocate($src, 51, 51, 51);
    	
    	$font = APPLICATION_PATH.'\..\public\img\font\arialbd.ttf';
    	$font2 = APPLICATION_PATH.'\..\public\img\font\arial.ttf';
    	$yim = 47;
    
    	$count = 0;
    	foreach ($topFans as $top){
    		set_time_limit(60);
    		//echo $top['facebook_user_id'];
    		$text = $top['fan_first_name'];
    		$point = $top['number_of_posts'];
    		$file = 'https://graph.facebook.com/'.$top['facebook_user_id'].'/picture';
    		$size = getimagesize($file);
    		switch($size["mime"]){
    			case "image/jpeg":
    				$pic = imagecreatefromjpeg($file); //jpeg file
    				break;
    			case "image/gif":
    				$pic = imagecreatefromgif($file); //gif file
    				break;
    			case "image/png":
    				$pic = imagecreatefrompng($file); //png file
    				break;
    			default:
    				$pic=false;
    				break;
    		}
    		imagefilledrectangle ($src, 40, $yim , 75 , $yim+35 , $white );
    		imagecopyresized($src, $pic, 43, $yim+3, 0, 0, 30, 30, 50, 50);
    		//echo $topArray[$count];
    		imagettftext($src, 16, 0, 87, $yim+25, $blue, $font, $text);
    		imagettftext($src, 14, 0, 210, $yim+25, $gray, $font, $point);
    		imagettftext($src, 9, 0, 245, $yim+23, $gray, $font2, 'Points');
    		$count++;
    		$yim +=37;
    	}
    	
    	imagecopy($dest, $src, 500, 5, 0, 0, 810, 300);
    	imagecopy($dest, $src2, 500, 5, 0, 0, 810, 300);
    	
    	
    	
    	imagepng($dest,APPLICATION_PATH.'\..\public\img\test.png' );
    	

    
    }
    
    
    public function testfanobjectAction(){
    	$f = new Model_FansObjectsStats();
    	
    	$f->addPost('197221680326345','28117303');
    	$f->addComment('197221680326345','28117303');
    	$f->addPhotos('197221680326345','28117303');
    	$f->addLikeAlbum('197221680326345','28117303');
    	$f->addLikeComment('197221680326345','28117303');
    	$f->addLikePhoto('197221680326345','28117303');
    	$f->addLikePost('197221680326345','28117303');
    	
    	$f->addOtherLikeComments('197221680326345','28117303');
    	$f->addOtherLikePost('197221680326345','28117303');
    	$f->addOtherPostComment('197221680326345','28117303');
    	
    }
    
    public function testAction() {
		$yesterday = new Zend_Date(1344829222, zend_date::TIMESTAMP);
    	//$yesterday->sub(2, Zend_Date::DAY);
		Zend_Debug::dump($yesterday->toString(zend_date::ISO_8601));
		
		$time = time()-3600*24*30;
		
		echo $time .'<br/>';
		
		$yesterday = new Zend_Date($time, zend_date::TIMESTAMP);
		
		Zend_Debug::dump($yesterday->toString(zend_date::ISO_8601));
		
		//exit();
		
		//$yesterday->sub(2, Zend_Date::DAY);
		$fanpageId = '216821905014540';
		$accessToken = 'AAAFHFbxmJmgBAFMM8ULzy7NbBUE3AfQ0JlshZCtIoN6538nCcontTwQhgfqwXsYZCzf6uuWYvBuy6Hn1HO3qyQztlMr1TJkGcVvUygSS5fvuqFlXwH';
		$batchQueries = $this->postBatchQueryBuilder($fanpageId, 10, $accessToken);
		
		Zend_Debug::dump($batchQueries);
		$result = Fancrank_Util_Util::requestFacebookAPI_POST("https://graph.facebook.com/", $batchQueries);
		
		Zend_Debug::dump($result);
		
		$resultList = array();
		
		foreach(json_decode($result) as $key=>$values) {
			if(!empty($values->code) && $values->code === 200 && !empty($values->body)) {
				$values = json_decode($values->body);
				if(!empty ($values->data)) {
					foreach ($values->data as $value) {
						//echo $value->id .' ' .$queryType .'postId: '.$postIdsGroup[$groupKey][$key] .'<br />';
						$resultList[] = $value;
					}
				}
			}
		}
		
		Zend_Debug::dump($resultList);
		
    }
    
    private function postBatchQueryBuilder($fanpageId, $postNum, $access_token) {
    	$result = array();
    	$limit = 5;
    	for($i=0; $i<$postNum; $i++) {
    		$offset = $i * $limit;
    		if($offset > $postNum || $i >= 50) {
    			break;
    		}
    		$tmp = array('method'=>'GET', 'relative_url'=> "/$fanpageId/feed?since=yesterday&limit=$limit&offset=$offset");
    		$result[] = $tmp;
    	}
    	return 'batch=' .urlencode(json_encode($result)) .'&access_token=' .$access_token;
    }
    
    public function testmodelAction() {
    	$facebook_user_id = '578800322';
    	$fanpage_id = '178384541065';
    	$m = new Model_FanpageSetting();
    	$data = array(
    			'fanpage_id' => $fanpage_id,
    			'theme_choice' => 1,
    			'top_post_choice' => 'day',
    			'profile_image_enable' => 0
    			);
    	$result = $m->saveFanpageSetting($data);
    	
    	
    	Zend_Debug::dump($m->isProfileImageEnable(178384541065));
    }

    public function extendAction() {
    	$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', 'production');
    	$this->config = $sources->get('facebook');
    	$token = 'AAAFWUgw4ZCZB8BAABe72sQZBMqLKI8uOGVf8akenwLjo7ZC0kjgIgQGS4ZCvj2spTKoOcSUSTTZBZBgwXxLljEZAqgDX7WalTYZAZCt7ZCMlW9BMQZDZD';
    	$token_url = "https://graph.facebook.com/oauth/access_token?client_id=".$this->config->client_id."&client_secret=".$this->config->client_secret."&grant_type=fb_exchange_token&fb_exchange_token=".$token;
    	
    	echo $token_url;
   	
    }
    
    public function test1Action() {
		
    	$appToken = '359633657407080|8__URQrP98IM8YTmoUroI0OUvBc';
		$url = 'https://graph.facebook.com/359633657407080/subscriptions?access_token=' .$appToken;

		echo $url .'<br/>';
		
		$arr = array(1=>'test1', 2=>123, 3=>'test2');
		$result = Zend_Json::encode($arr);
		
		//echo $result; exit();
		$filePath = DATA_PATH .'/temp/last_update.data';
		
		if (file_exists($filePath)) {
			echo "The file $filePath exists";
		}
		
		$data = unserialize( file_get_contents( $filePath ) );
		
		echo $data['app_token'] .'<br/>';
		//echo $data['json'];
		
		$data['app_token'] = $appToken;
		$data['url']	= $url;
		$data['json'] = $result;
		
		file_put_contents( $filePath, serialize( $data ) );
		
		echo $data['json'];
		
		
		//$result = $this->httpCurl($url, null, null);
		
		//Zend_Debug::dump($result);
		
//     	$access_token="AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm";
// 		$fb = new Service_FancrankFBService();
// 		$result = $fb->getExtendedAccessToken($access_token);
// 		echo 'new token =: ' .$result;
// 		$user_id = '100004098439774';
		

// 		$attachment =  array(
// 				'access_token' => $result,
// 				'message' => "$msg",
// 				'name' => "$name",
// 				'link' => "$link",
// 				'description' => "test post"
// 		);
		
// 		$fb->api('/'.$user_id.'/feed', 'POST', $attachment);
		
    }
    
    public function test2Action() {
    	//$fanpageId = '178384541065';
    	//$accessToken = 'AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm';
    	
    	$fanpageId = '216821905014540';
    	$accessToken = 'AAAFHFbxmJmgBAPUVD7kjQIquRVpaDPJ8TKUPMXqUSD0BuP7F9KhsXtC1uEnWe0eaVTPebNprupHZC4fhNZA0ZAYTQoAjnNM0lG7ZBWQApc3Ttfphz7Dg';
    	   
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	$yesterday = new Zend_Date();
    	$yesterday->sub(2, Zend_Date::DAY);
    	//echo $yesterday->getTimestamp();
    	//echo $yesterday->toString('yyyy-MM-dd');
    	$since = new Zend_Date($yesterday->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
    	echo $since->toString('yyyy-MM-dd');    	
    	$until = $since->getTimestamp();
    	$since = $until-3600*24;
		$collector->updateFanpage('yesterday', 'now');
    }
    
    public function test3Action () {
    	$facebook_user_id = '578800322';
    	$fanpage_id = '178384541065';
    	try {
    		$fan = new Model_Fans($facebook_user_id, $fanpage_id);
    		
    		if(! $fan->isNewFan()) {
    			$fanProfile = $fan->getFanProfile();
    			$fanProfile->fan_name = 'Megan Hicks';
				$fan->updateFanPoints(-100);
				$fan->updateCurrency();
    			$fan->updateFanProfile();
    			Zend_Debug::dump($fanProfile);
    			echo 'current level: ' .$fan->getFanLevel() .PHP_EOL;
    			
    			echo 'fan_points: ' .$fan->getFanPoints() .PHP_EOL;
    			
    			echo 'next level: ' .$fan->getNextLevelRequiredXP();
    			
    			echo 'get new level: ' .$fan->updateLevel();
    		}
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}

    }
    
    public function testsubAction() {
    	$fb = new Service_FancrankFBService();
    	$token = $fb->getAppAccessToken();
    	echo $token .'<br/>';
    	 
    	$params = array(
    				'object'=>'page',
    				'fields'=>'feed',
    				'verify_token'=>'fancrank'
    			);
    	
    	$url = 'https://graph.facebook.com/359633657407080/subscriptions';
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/'
    			.$fb->getAppId().'/subscriptions?access_token='
    					.$token);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    	$res = curl_exec($ch);
    	
    	Zend_Debug::dump($res);
    	curl_close($ch);
    	//$result = $this->httpCurl($url, $param, 'post');
    	
    	//Zend_Debug::dump($result);
    	
    }
    
    public function testuploadAction() {
		
    	$access_token = 'AAAFHFbxmJmgBAHk0jZAn0PozdZCzjjBII3bosrvP9tG4c4PwnC5d7JSqkLuPimGAgHBj2ZAmPJBvi21SwvFhxDOVDbd8vMNVUFXNnYe7ZCHlskighIXt';
    	$fanapgeId = '197221680326345';
    	$facebook = new Service_FancrankFBService();
    	$facebook->setAccessToken($access_token); 
     	$facebook->setFileUploadSupport(true);
    	
     	$imageFileName = 'cover2.jpg';
     	
     	try {
			$file= DATA_PATH .'/images/fanpages/' .$imageFileName;
     		$args = array(
     			'no_story' => 1,	
     			'message' => 'update cover photo',
     		);

			$args[basename($file)] = '@' . realpath($file);
			
			$result = $facebook->api("/$fanapgeId/photos", 'post', $args);
			
			if($result && is_numeric($result['id'])) {
				$args = array('cover'=>$result['id'], 'no_feed_story'=>true);
				$result = $facebook->api("/$fanapgeId", 'post', $args);
			}
     		Zend_Debug::dump($result);
     		echo 'done';
     	} catch (FacebookApiException $e) {
     		print $e->getMessage();
     	}
    }
    
    public function test4Action() {
    	$imageFileName = 'cover2.jpg';
    	$file= DATA_PATH .'/images/fanpages/' .$imageFileName;
    	$image = imagecreatefromjpeg($file);
    	$this->getResponse()->setHeader('Content-Type', 'image/jpeg');
		//Zend_Debug::dump($image);
		//header('Content-Type: image/jpeg');
		imagejpeg($image, null, 100);
    }
    
    public function test5Action() {
    	$fanpageId = '178384541065';
    	$accessToken = 'AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm';
    	   
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	
    	$collector->fullScanFanpage();
    }
    
    public function test6Action() {
    	$fanStat = new Model_FansObjectsStats();
    	$fanpageId = '178384541065';
    	$userId = '664609767';

    	echo $fanStat->getFanPostStatusCount($fanpageId, $userId);
    	echo '<br/>';
    	echo $fanStat->getFanCommentCount($fanpageId, $userId);
    	echo '<br/>';
    	echo 'like: ' .$fanStat->getFanLikeCommentCount($fanpageId, $userId);
    	echo '<br/>';
    	echo 'got like: ' .$fanStat->getFanGotLikeFrom($fanpageId, $userId, 'comment');
    	echo '<br/>';
    	echo 'got comment: ' .$fanStat->getFanGotCommentCountFromStatus($fanpageId, $userId);

    	$result = $fanStat->updatedFan($fanpageId, $userId);
    	Zend_Debug::dump($result);
    }
    
    public function test7Action() {
    	$activity = new Model_FancrankActivities();
    	$fanpage_id = '178384541065';
    	$facebook_user_id = '664609767';
    	    	
    	$result = $activity->getRecentActivitiesInRealTime($facebook_user_id, $fanpage_id, 10);
    	
    	Zend_Debug::dump($result);
    	
    }

    public function test8Action() {
    	$fanpageId = '178384541065';
    	$accessToken = 'AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm';
    	
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	 
    	$collector->test();
    }
    
    public function test9Action() {
    	$post = new Model_Posts();
    	$row = array(
					'post_id'               => '153123704737554_10150137218225908',
					'post_message'          => 'hello',
			);
    	echo 'save post';
    	$result = $post->saveAndUpdateById($row, array('id_field_name'=>'post_id'));
    	Zend_Debug::dump($result);	
    }
    
    public function test10Action() {
    	$facebook = new Service_FancrankFBService();
    	$userId = '100001005159808';
    	//echo $facebook->getAppAccessToken();
    	echo $facebook->isUserInstalledApp($userId);
    }
    
    public function testmemcacheAction() {
    	$starttime = time();
    	echo $starttime;
    	$model = new Model_Rankings;

    	try {
			$cache = Zend_Registry::get('memcache');
			$cache->setLifetime(15); 
			$this->_fanpageId = '178384541065';
			$topPostsId = 'topFans_' .$this->_fanpageId;

    		//Check to see if the $topStories are cached and look them up if not
    		if(isset($cache) && !$cache->load($topPostsId)){
    			//Look up the $topStories
    			echo 'look up db';
    			$topFans = $model->getTopFans($this->_fanpageId , 100);
    			 
    			//Save to the cache, so we don't have to look it up next time
    			$cache->save($topFans, $topPostsId);
    		}else {
    			$topFans = $cache->load($topPostsId);
    		}    		
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	Zend_Debug::dump($topFans);
    	$stop = time();
    	$totalTime = $stop - $starttime;
    	echo '</br>Execution time ' . $totalTime;
    }
    
    public function testmemcache1Action() {
    	$activitiesModel = new Model_FancrankActivities();
    	 
    	$activities = null;
    	$limit = 20;
    	$this->_fanpageId = '197221680326345';
    	$this->_userId = '100001005159808';
    	
    	if(!empty($this->_fanpageId ) && !empty($this->_userId)) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(3600);
    		try {
    			$fanActivityId = $this->_fanpageId .'_' .$this->_userId. '_fan_activity';
    			$cache->remove($fanActivityId); exit();
    			 
    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanActivityId)){
    				echo 'db look up';
    				//$fan = new Model_Fans($user->facebook_user_id, $this->_fanpageId);
    				$activities = $activitiesModel->getRecentActivities($this->_userId, $this->_fanpageId, $limit);
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($activities, $fanActivityId);
    			}else {
    				echo 'memcache look up';
    				$activities = $cache->load($fanActivityId);
    				// merge new activity
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	Zend_Debug::dump($activities);
    	
    	$newActivity = array();
    	echo $activities[0]['created_time'];
    	if(!empty($activities[0]['created_time'])) {
    		$newActivity = $activitiesModel->getRecentActivitiesSince($this->_userId, $this->_fanpageId, $limit, $activities[1]['created_time']);
    	}
    	
    	if(count($newActivity) > 19) {
    		Zend_Debug::dump($newActivity);
    	}else {
    		echo '-------------';
    		$result = array_merge($newActivity, array_slice($activities, count($newActivity)));
    		Zend_Debug::dump($result);
    	}
    	
    }
    
    public function viewAction() {
    	$time = time();
    	$range = 7776000;
    	$since = $time - $range;
		$until = $time;
		 
    	$fanpageId = $this->_getParam('fanpage_id');
    	$type = $this->_getParam('type');
    	$accessToken = 'AAAFHFbxmJmgBAHnlQOjSf0ZBPd0fQjZBpI8b38i3ZBYeyObEKed0nV1iZBCkaIAQZC8Tt0wIVcKchYC7lRatkMQksZAQWantwiTvDdqqiYAQZDZD';
//      	$url = "https://graph.facebook.com/eslyonline/insights";
//      	$param = array('access_token'=>'AAAFHFbxmJmgBAP9PzJi7VDqsx1tP3CLbpoZABeFytBeEvFutkvLdZAVQgzdzyZCO3GxzjTYEZBWzHWy7T4Y3CImLEZBxZCa8Avi7lrNW6CCgZDZD',
//      					'since'=>$since,
//      					'until'=>$until);
//      	$result = $this->httpCurl($url, $param, 'get');
//      	Zend_Debug::dump($result);
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'insights');
    	$result = $collector->collectFanpageInsight(5, $type);
    	$likeStats = array();
    	foreach ($result as $data) {
    		foreach($data->values as $value) {
    			$time = explode('T', $value->end_time);
    			$newTime = str_replace('-', '/', $time[0]);
    			$value->end_time = $newTime;
    			$likeStats[] = $value;
    		}
    	}
    	
    	//Zend_Debug::dump($likeStats); exit();
    	//asort($likeStats);
    	//Zend_Debug::dump($likeStats);
    	$this->_helper->json($likeStats);
    }
    
    public function testmailAction() {
    	echo 'mail test'; exit();
		$fmail = new Service_FancrankMailService();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		
		// init default db adapter
		$adapter = Zend_Db::factory($config->resources->db);
		Zend_Db_Table::setDefaultAdapter($adapter);
		$jobCount = $adapter->query("select count(*) as count from message")->fetchAll();
		
		$adapter = new Fancrank_Queue_Adapter($config->queue);
		$queue = new Zend_Queue($adapter, $config->queue);
		
		$messages = $queue->receive((int) $jobCount[0]['count'], 0);
		
		foreach ($messages as $message) {
			$job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);
			break;
		}	
		
		$errMsg = sprintf('Error on job: %s <br/>fanpage_id: %s <br/>access_token: %s<br/> type: %s<br/>', $job->url, $job->fanpage_id, $job->access_token, $job->type);
		$fmail->sendErrorMail($errMsg .'End of Report');    	
    }
    
    private function httpCurl($url, $params=null, $method=null) {
    	$ch = curl_init();
    	switch (strtolower($method)) {
    		case 'get':
    			curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
    			curl_setopt($ch, CURLOPT_POST, false);
    			break;
    		case 'post':
    			curl_setopt($ch, CURLOPT_URL, $url);
    			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    			curl_setopt($ch, CURLOPT_POST, true);
    			break;
    		default:
    			curl_setopt($ch, CURLOPT_URL, $url);
    			curl_setopt($ch, CURLOPT_POST, false);
    			break;
    	}
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	$result = curl_exec($ch);
    	curl_close($ch);
    	return $result;
    }
}
