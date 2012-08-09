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
    	$green = imagecolorallocate($src, 193, 204, 178);
    	$gray = imagecolorallocate($src, 30, 30, 30);
    	
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
    		imagefilledrectangle ($src, 40, $yim , 75 , $yim+35 , $green );
    		imagecopyresized($src, $pic, 43, $yim+3, 0, 0, 30, 30, 50, 50);
    		//echo $topArray[$count];
    		imagettftext($src, 16, 0, 87, $yim+25, $blue, $font, $text);
    		imagettftext($src, 14, 0, 180, $yim+25, $gray, $font, $point);
    		imagettftext($src, 9, 0, 215, $yim+23, $gray, $font2, 'Points');
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
    	$facebook_user_id = '578800322';
    	$fanpage_id = '178384541065'; 
    	
		$badge = new Model_Badges();
		//$result = $badge->fetchAll();
		
		$top = new Model_Rankings();
		$result = $top->getUserTopFansRank($fanpage_id, $facebook_user_id);
		Zend_Debug::dump($result);
		
		$result = $top->getUserTopTalkerRank($fanpage_id, $facebook_user_id);
		Zend_Debug::dump($result);
		
		$result = $top->getUserTopClickerRank($fanpage_id, $facebook_user_id);
		Zend_Debug::dump($result);
		
		$result = $top->getUserMostPopularRank($fanpage_id, $facebook_user_id);
		Zend_Debug::dump($result);
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

    	$postTime = new Zend_Date('2012-08-07T11:52:50+0000', Zend_Date::ISO_8601);
    	$commentTime = new Zend_Date('2012-08-07T11:58:40+0000', zend_date::ISO_8601);
		
		echo '<br/>' .floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
		
		$arr = array();
		$arr['1234567890000'] = 1;
		$arr[23] = 2;
		Zend_Debug::dump($arr);
    	
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
    	$accessToken = 'AAAFHFbxmJmgBAFMM8ULzy7NbBUE3AfQ0JlshZCtIoN6538nCcontTwQhgfqwXsYZCzf6uuWYvBuy6Hn1HO3qyQztlMr1TJkGcVvUygSS5fvuqFlXwH';
    	   
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	$yesterday = new Zend_Date();
    	$yesterday->sub(2, Zend_Date::DAY);
    	//echo $yesterday->getTimestamp();
    	//echo $yesterday->toString('yyyy-MM-dd');
    	$since = new Zend_Date($yesterday->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
    	echo $since->toString('yyyy-MM-dd');    	
    	$until = $since->getTimestamp();
    	$since = $until-3600*24;
		$collector->updateFanpage($since, $until);
    }
    
    public function test3Action () {
    	$facebook_user_id = '578800322';
    	$fanpage_id = '178384541065';
    	try {
    		$fan = new Model_Fans($facebook_user_id, $fanpage_id);
    		
    		if(! $fan->isNewFan()) {
    			$fanProfile = $fan->getFanProfile();
    			$fanProfile->fan_name = 'Megan Hicks';
				$fan->updateFanPoints(1000);
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
				$args = array('cover'=>$result['id'], 'no_story'=>1);
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
    		if(isset($cache) && !$topStories = $cache->load($topPostsId)){
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
