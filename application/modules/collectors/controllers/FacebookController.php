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
    	//$src2 = imagecreatefrompng(APPLICATION_PATH.'\..\public\img\checkrank.png');
    	
    	
    	$model = new Model_Rankings;
    	$topFans = $model->getTopFans('216821905014540', 5);
    	
    	// imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
    	
    	
    	

    	$blue= imagecolorallocate($src, 23, 61, 113);
    	$white = imagecolorallocate($src, 255, 255, 255);
    	$gray = imagecolorallocate($src, 51, 51, 51);
    	
    	$font = APPLICATION_PATH.'\..\public\img\font\arialbd.ttf';
    	$font2 = APPLICATION_PATH.'\..\public\img\font\arial.ttf';
    	$yim = 88;
    
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
    		//imagefilledrectangle ($src, 45, $yim , 75 , $yim+35 , $white );
    		imagecopyresized($src, $pic, 48, $yim+3, 0, 0, 30, 30, 50, 50);
    		//echo $topArray[$count];
    		imagettftext($src, 16, 0, 92, $yim+25, $blue, $font, $text);
    		imagettftext($src, 14, 0, 215, $yim+25, $gray, $font, $point);
    		imagettftext($src, 9, 0, 250, $yim+23, $gray, $font2, 'Points');
    		$count++;
    		$yim +=37;
    	}
    	
    	imagecopy($dest, $src, 500, 5, 0, 0, 810, 300);
    	//imagecopy($dest, $src2, 500, 5, 0, 0, 810, 300);

    	imagepng($dest,APPLICATION_PATH.'\..\public\img\autogencover.png' );

    }
    
    
    public function testimagecreatetwoAction(){
    	 
    	// Create image instances
    	$dest = imagecreatefromjpeg(APPLICATION_PATH.'\..\public\img\beach.jpg');
    	$src = imagecreatefrompng(APPLICATION_PATH.'\..\public\img\topfanoftheweek.png');
    	
    	$fanpage_id = '216821905014540';
    	$model = new Model_Rankings;
    	$topFans = $model->getTopFans($fanpage_id, 1);
    	 
    	// imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
    	 
		$blue= imagecolorallocate($src, 89, 116, 154);
    	$white = imagecolorallocate($src, 255, 255, 255);
    	$gray = imagecolorallocate($src, 102, 102, 102);
    	 
    	$font = APPLICATION_PATH.'\..\public\img\font\arialbd.ttf';
    	$font2 = APPLICATION_PATH.'\..\public\img\font\arial.ttf';


    	foreach ($topFans as $top){
    		set_time_limit(60);
    		//echo $top['facebook_user_id'];
    		$text = $top['fan_first_name'].' '.$top['fan_last_name'];
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
    		
    		$fan = new Model_Fans($top['facebook_user_id'],$fanpage_id );
    		
    		$stat = new Model_FansObjectsStats();
    		$stat = $stat->findFanRecord($fanpage_id, $top['facebook_user_id']);
    		
    	
    		
    		$level = $fan->getFanLevel();
    		
    		
    		//imagefilledrectangle ($src, 40, $yim , 75 , $yim+35 , $white );
    		imagecopyresized($src, $pic, 30, 67, 0, 0, 40, 40, 50, 50);//add the facebook pic
    		//echo $topArray[$count];
    		imagettftext($src, 14, 0, 75, 107, $blue, $font, $text);//file, font, , x, y, color, type , text
    		
    		if (strlen($level)==1){
    			imagettftext($src, 16, 0, 302, 93, $blue, $font, $level);
    		}else{
    			imagettftext($src, 16, 0, 297, 93, $blue, $font, $level);
    		}
    		imagettftext($src, 10, 0, 85, 140, $gray, $font2, $stat[0]['total_posts']);
    		imagettftext($src, 10, 0, 83, 157, $gray, $font2, $stat[0]['total_likes']);
    		imagettftext($src, 10, 0, 115, 175, $gray, $font2, $stat[0]['total_comments']);
    		imagettftext($src, 10, 0, 169, 193, $gray, $font2, $stat[0]['total_get_comments']);
    		imagettftext($src, 10, 0, 137, 211, $gray, $font2, $stat[0]['total_get_likes']);
    						
    		
    		
    		if (strlen($point)== 1){
    			imagettftext($src, 25, 8, 260, 193, $blue, $font, $point);
    		}else if (strlen($point)== 2){
    			imagettftext($src, 25, 8, 253, 194, $blue, $font, $point);
    		}else if (strlen($point) == 3){
    			imagettftext($src, 25, 8, 244, 196, $blue, $font, $point);
    		}else if (strlen($point) == 4){
    			imagettftext($src, 25, 8, 237, 198, $blue, $font,$point);
    		}
    	}
    	 
    	imagecopy($dest, $src, 439, 5, 0, 0, 810, 300);
    	//imagecopy($dest, $src2, 500, 5, 0, 0, 810, 300);
    
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
		exit();
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
    
    private function feedBatchQuery($fanpageId, $accessToken, $limit, $since) {
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$fanpageId/feed?since=$since&limit=$limit");
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$fanpageId/posts?since=$since&limit=$limit");
    	
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$accessToken;
    	
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
    	
    	Zend_Debug::dump($finalResult);
    	
    	return $finalResult;
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
    	$accessToken = 'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno';
    	   
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	$yesterday = new Zend_Date();
    	$yesterday->sub(2, Zend_Date::DAY);
    	//echo $yesterday->getTimestamp();
    	//echo $yesterday->toString('yyyy-MM-dd');
    	$since = new Zend_Date($yesterday->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
    	echo $since->toString('yyyy-MM-dd');    	
    	$until = $since->getTimestamp();
    	$since = $until-3600*24;
		$collector->updateFanpage('5+days+ago', 'now');
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

    	$since = new Zend_Date(time());
    	$since->subDay(30);
    	$result = $activity->getAllActivitiesSince($since->toString('yyyy-MM-dd HH:mm:ss'));
    	
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
    	//echo $facebook->isUserInstalledApp($userId);
    	echo $facebook->isFanpageInstalledApp('197221680326345');
    }
    
    public function test11Action() {
    	$fanpage_id = '178384541065';
    	$facebook_user_id = '664609767';
    	$activitiesModel = new Model_FancrankActivities();
    	$activities = $activitiesModel->getRecentActivities($facebook_user_id, $fanpage_id, 5);
    	Zend_Debug::dump($activities);
    	$date = new Zend_Date('1344639277');
    	echo $date->toString(Zend_Date::ISO_8601);
    	echo '</br>';
    	$date1 = new Zend_Date('2012-08-10T22:54:38+0000');
    	echo $date1->toString(Zend_Date::TIMESTAMP);
    }
    
    public function test12Action() {
    	$fanpageId = '197221680326345';
    	$accessToken = 'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno';
    	$since = 'yesterday';
    	$limit = 5; 
    	$this->feedBatchQuery($fanpageId, $accessToken, $limit, $since);
    }
    
    public function test13Action() {
    	$fanpage_id = '216821905014540';
    	$facebook_user_id = '612158099';
		$fanStatModel = new Model_FansObjectsStats();
		Zend_Debug::dump($fanStatModel->getFanStatById($fanpage_id, $facebook_user_id));
    }
    
    public function test14Action() {
    	$postIds = array('216821905014540_505034479526613', '216821905014540_504975742865820', '216821905014540_504170349613026', '216821905014540_256560081113468');
    	$access_token = 'AAAFHFbxmJmgBAPUVD7kjQIquRVpaDPJ8TKUPMXqUSD0BuP7F9KhsXtC1uEnWe0eaVTPebNprupHZC4fhNZA0ZAYTQoAjnNM0lG7ZBWQApc3Ttfphz7Dg';
    	
    	$tmp = array();
    	$limit = 10;
    	foreach ($postIds as $id) {
    		$tmp[] = array('method'=>'GET', 'relative_url'=> "/$id/likes?limit=$limit");
    	}
    	
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$access_token;

    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/?". $batchQueries);
    	$client->setMethod(Zend_Http_Client::POST);
    	
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	Zend_Debug::dump($result); exit();
    	$finalResult = array();
    	foreach ($result as $key=>$post) {
    		if(!empty($post->code) &&  $post->code === 200 && isset($post->body)) {
    			$likes = json_decode($post->body);
    			if(isset($likes->data)) {
    				$finalResult[] = $likes->data;
    			}else {
    				$finalResult[] = array();
    			}
    		}else {
    			$finalResult[] = array();
    		}
    	}
    	Zend_Debug::dump($finalResult);
    	
    }
    
    public function test15Action() {
    	$fanpageId = '216821905014540';
    	 
    	$fanpageModel = new Model_Fanpages;
    	
    	$date = new Zend_Date();
    	$date->subDay(2);
    	
    	$newFans = $fanpageModel->getNewFansNumberSince($fanpageId, $date->toString('yyyy-MM-dd HH:mm:ss'), 5);
    	
    	Zend_Debug::dump($newFans);
    }
    
    public function test16Action() {
    	$badgeModel = Fancrank_BadgeFactory::factory('custom');
    	$badgeRuleModel = new Fancrank_Badge_Model_BadgeRules();
    	
    	$rule1 = array('table_name'=>'posts', 'table_field'=>'posts', 'operator'=>'>' ,'argument'=>'10');
    	$badgeRuleModel->addRule($rule1);
    	//$badgeRuleModel->addRule($rule2);
    	//$badgeRuleModel->addRule($rule3);
    	
    	$rules = $badgeRuleModel->getJsonRules();

    	if(empty($rules)) exit();
    	
    	$data = array ('name'=>'my post and comments', 'description' => '100 posts and comments', 'weight'=>'100', 'picture'=>'/use/abb', 'rules'=>$rules);
    	
    	$badgeId = $badgeModel->insert($data);
    	echo $badgeId;
    	
//     	Zend_Debug::dump($badgeRuleModel->_DEFAULT_TABLE_NAME_LIST);
//     	Zend_Debug::dump($badgeRuleModel->_DEFAULT_TABLE_FIELD_LIST);
//     	Zend_Debug::dump($badgeRuleModel->_DEFAULT_OPERATOR_LIST);

    	$badge = $badgeModel->findrow($badgeId);
    	
    	$fanpageId = '216821905014540';
    	$facebookUserId = '216821905014540';
    	echo $badgeModel->isFanEligible($fanpageId, $facebookUserId, $badgeId);
    	//Zend_Debug::dump($badge);
    }
    
    public function test17Action() {
    	$fanpageId = '197221680326345';
    	$message = 'this is a test comment';
    	$accessToken = 'AAAFHFbxmJmgBAM6lldcZAZCggKej98EhT9n4hAcuDfpIQVB7nHKIuGNOZAQ2rprCeFEBk3tDZApEb8KUJZBuFR8lIUTvKE4yW3amNYmJMWgZDZD';
    	
    	$client = new Zend_Http_Client;
		$client->setUri("https://graph.facebook.com/197221680326345_419358148112696/likes");
		$client->setMethod(Zend_Http_Client::GET);
		
		//$client->setParameterGet('message', $message);
		$client->setParameterGet('access_token', $accessToken);
		$client->setParameterGet('method', 'post');
		
		$response = $client->request();
		
		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		Zend_Debug::dump($result);
		
    }
    
    public function test18Action() {
    	$badgeDefaultModel = Fancrank_BadgeFactory::factory('default');
    	$badgeModel = new Model_Badges();
    	$badgeEventModel = new Model_BadgeEvents();
    	
    	$fanpageId = '216821905014540';
    	$facebookUserId = '1826347152';
    	
    	$badgeList = $badgeEventModel->getRemaindDefaultBadgeByUser($fanpageId, $facebookUserId);
    	//Zend_Debug::dump($badgeList); exit();
    	$tmpName = $badgeList[0]['name'];
    	$flag = true;

    	foreach ($badgeList as $badge) {
    		if($badge['name'] == $tmpName) {
    			if($flag) {
    				$flag = $badgeDefaultModel->isFanEligible($fanpageId, $facebookUserId, $badge['id']);
    			}else {
					//
    			}
    		}else {
    			$tmpName = $badge['name'];
    			$flag = $badgeDefaultModel->isFanEligible($fanpageId, $facebookUserId, $badge['id']);
    		}
    		if($flag) {
    			echo $badge['id'] .': '.$flag .'<br/>';
				$data = array(
							'fanpage_id'		=> $fanpageId,
							'facebook_user_id'	=> $facebookUserId,
							'badge_id'		 	=> $badge['id']
						);
				try {
					$badgeEventModel = new Model_BadgeEvents();
					$badgeEventModel->insert($data);
				} catch (Exception $e) {
					echo $e->getMessage();
				}
    		}
    	}
    	//echo $badgeModel->isFanEligible($fanpageId, $facebookUserId, $badgeId);
    }
    
    public function test19Action() {
    	$badgeModel = new Model_Badges();

    	$select = $badgeModel->getAdapter()->select();
    	
    	$arr = array(
    				'from'=>array('p'=>'posts', 'c'=>'comments'),
    				'join'=>array('p.post_id = c.comment_post_id'),
    				'where'=>array('p.facebook_user_id = 65558608937', 'p.fanpage_id = 65558608937'),
    				'order'=>array('p.post_id desc'),
    				'limit'=> 10
    			);
		
    	$arr1 = Zend_Json::encode($arr);
    	$arr2 = Zend_Json::decode($arr1);
    	//call_user_func_array(array($select, 'from'), array(array('p' => 'posts')));
    	//echo $select->assemble(); exit();
    	//Zend_Debug::dump(implode(',', array(array('c' => 'comments'), 'p.post_id = c.comment', array()))); exit();
    	$preSelect = 'SELECT count(*) > 0 AS flag ';
    	foreach ($arr2 as $key=>$statement) {
    		//$select->{$key}($key == 'from' ? $statement : implode(',' ,$statement));
    		switch ($key) {
    			case 'from' : 
    			    foreach ($statement as $key=>$v) {
	    				$select->from(array($key=>$v), array());
	    			}
    				break;
    			case 'join' :
    				//call_user_func_array(array($select, $key), $statement);
    				foreach ($statement as $v) {
    					$select->where($v);
    				}
    				break;
    			case 'where' :
	    			foreach ($statement as $v) {
	    					$select->where($v);
	    			}
    				break;
    			case 'order' :
    				call_user_func_array(array($select, $key), $statement);
    				break;
    			case 'limit' :
    				call_user_func_array(array($select, $key), array($statement));
    				break;		
    			default : break;			
    		}
    	}
    	echo $preSelect .$select->assemble();
    }
    
    public function test20Action() {
    	$date = new Zend_Date();
		echo $date->get(Zend_Date::WEEKDAY_DIGIT);
		$pointLogModel = new Model_PointLog();
    }
    
    public function testmemcacheAction() {
    	$starttime = time();
    	echo $starttime;
    	$model = new Model_Rankings;
		$newResult = array();
		$this->_fanpageId = '197221680326345';
		$newResult['code'] = 110;
    	try {
			$cache = Zend_Registry::get('memcache');
			
    		//Check to see if the $topStories are cached and look them up if not
    		if(isset($cache) && !$cache->load('test')){
    			//Look up the $topStories
    			echo 'look up db';
    			$newResult['topFans'] = $model->getTopFans($this->_fanpageId , 100);
    			 
    			//Save to the cache, so we don't have to look it up next time
    			$cache->save($newResult, 'test');
    		}else {
    			echo 'memcache look up';
    			$newResult = $cache->load('test');
    			//$newResult['code'] = 110;
    			unset($newResult['msg']);
    			$cache->save($newResult, 'test');
    			$newResult = $cache->load('test');
    		}    		
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	Zend_Debug::dump($newResult);
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
    
    public function testsecurityAction() {
    	print $_GET['a'] .'</br>';
    	//$data = array('username'=>'stephen', 'points'=>123, 'email'=>'stephen@gmail.com', 'access_token'=>'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno', 'created_time'=>(new Zend_Date())->toString());
    	$encryptData = Fancrank_Crypt::encrypt($data, 'hello');
    	print $encryptData;
    	$decryptData = Fancrank_Crypt::decrypt($encryptData, 'hello');
    	Zend_Debug::dump($decryptData);
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
    
    public function testmail1Action() {
    	$mailModel = new Fancrank_Mail('stephen@fancrank.com');
    	$date = Zend_Date::now();
    	$mailModel->setSubject('Redeem Tracking: ' .$date->toString(Zend_date::ISO_8601).PHP_EOL);
    	//$data = array('username'=>'stephen', 'points'=>123, 'email'=>'stephen@gmail.com', 'access_token'=>'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno', 'created_time'=>(new Zend_Date())->toString());
    	$link = 'www.fancrank.local/app/redeem/track?data=' .Fancrank_Crypt::encrypt($data);
    	$mailModel->sendMail($link);
    	
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
