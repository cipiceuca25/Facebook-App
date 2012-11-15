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
    
    private function postBatchQuery($postId, $accessToken, $limit, $since) {
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$postId");
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$postId/comments?since=$since&limit=$limit");
    	$tmp[] = array('method'=>'GET', 'relative_url'=> "/$postId/likes?since=$since&limit=$limit");
    	 
    	$batchQueries =  'batch=' .urlencode(json_encode($tmp)) .'&access_token=' .$accessToken;
    	 
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/?". $batchQueries);
    	$client->setMethod(Zend_Http_Client::POST);
    	 
    	$response = $client->request();
    	 
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    
    	$post = array();
    	$comments = array();
    	$likes = array();
    	
    	if(!empty($result[0]->body)) {
    		$post = json_decode($result[0]->body);
    	}
    	 
    	if(!empty($result[1]->body)) {
    		$comments = json_decode($result[1]->body);
    	}
    
    	if(!empty($result[2]->body)) {
    		$likes = json_decode($result[1]->body);
    	}
    	
    	$finalResult['post'] = $post;
    	$finalResult['likes'] = $likes;
    	$finalResult['comments'] = $comments;
    	 
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
		$collector->updateFanpageFeed('10+days+ago', 'now');
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
    			
    			echo 'fan_exp: ' .$fan->getFanPoints() .PHP_EOL;
    			
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
    	$fanpage_id = '216821905014540';
    	$facebook_user_id = '100000027416934';
    	
    	$sub1 = array(
	    			'select'=>'SELECT count(*) > 0 AS flag',
	    			'from'=>array('p'=>'posts', 'c'=>'comments'),
	    			'join'=>array('p.post_id = c.comment_post_id'),
	    			'where'=>array('p.facebook_user_id = 100000027416934', 'p.fanpage_id = :fanpage_id'),
	    			'order'=>array('p.post_id desc')
    			);

    	$sub2 = array(
    			'select'=>'SELECT count(*) > 0 AS flag',
    			'from'=>array('p'=>'posts', 'c'=>'comments'),
    			'join'=>array('p.post_id = c.comment_post_id'),
    			'where'=>array('p.facebook_user_id = 100000027416934', 'p.fanpage_id = :fanpage_id'),
    			'order'=>array('p.post_id desc')
    	);
    	
    	$arr = array('sql'=>' select (case when ((a.a + b.b) >= 34) then 1 else 0 end ) as flag from
							(select count(*) as a
							from posts p , likes l 
							where l.facebook_user_id != p.facebook_user_id &&
							         l.post_id = p.post_id &&
							         l.fanpage_id = p.fanpage_id &&
							         l.facebook_user_id = :facebook_user_id &&
							         l.fanpage_id = :fanpage_id
							) as a,
							
							(
							select count(*) as b
							from likes l , comments c
							where l.facebook_user_id != c.facebook_user_id &&
							         l.post_id = c.comment_id &&
							         l.fanpage_id = c.fanpage_id &&
							         l.facebook_user_id = :facebook_user_id &&
							         l.fanpage_id = :fanpage_id
							) as b');
    	$arr1 = Zend_Json::encode($arr);
    	
    	echo $arr1;
    	$arr2 = Zend_Json::decode($arr1);
    	echo $arr2;
    	$badgeModel = Fancrank_BadgeFactory::factory('default');
    	//call_user_func_array(array($select, 'from'), array(array('p' => 'posts')));
    	//echo $select->assemble(); exit();
    	//Zend_Debug::dump(implode(',', array(array('c' => 'comments'), 'p.post_id = c.comment', array()))); exit();
    	
		if($arr2['sql']) {
			$v = str_replace(':facebook_user_id', $facebook_user_id, $arr2['sql']);
			$v = str_replace(':fanpage_id', $fanpage_id, $v);
			echo $v;
			exit();
		}
		
    	$preSelect = 'SELECT count(*) > 0 AS flag ';
    	foreach ($arr2 as $key=>$statement) {
    		//$select->{$key}($key == 'from' ? $statement : implode(',' ,$statement));
    		switch ($key) {
    			case 'select' :
    				$preSelect = $statement .' ';
    				break;
    			case 'from' : 
    			    foreach ($statement as $key=>$v) {
    			    	$v = str_replace(':facebook_user_id', $facebook_user_id, $v);
    			    	$v = str_replace(':fanpage_id', $fanpage_id, $v);
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
	    				$v = str_replace(':facebook_user_id', $facebook_user_id, $v);
	    				$v = str_replace(':fanpage_id', $fanpage_id, $v);
	    				$select->where($v);
	    			}
    				break;
    			case 'orWhere' :
    				foreach ($statement as $v) {
    					$v = str_replace(':facebook_user_id', $facebook_user_id, $v);
    					$v = str_replace(':fanpage_id', $fanpage_id, $v);
    					$select->orWhere($v);
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
    	$logger = new Zend_Log();
    	//$writer = new Zend_Log_Writer_Stream('php://output');
    	$writer = new Zend_Log_Writer_Stream('./monitor_cron_error.log');
    	$logger = new Zend_Log($writer);
    	 
    	$fanpageId = '216821905014540';
    	$accessToken = 'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno';
    	$postId = '216821905014540_528230683873659';
    	
		$result = null;
		$testPostId = 'test_post';
		try {
			$cache = Zend_Registry::get('memcache');
			//$cache->remove($testPostId);
			
			if(isset($cache) && !$cache->load($testPostId)){
				//Look up the facebook graph api
				echo 'look up facebook graph api';
				
				$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'fetch');
				$result = $collector->getFullPost($postId);
				
				if($result) {
					//Save to the cache, so we don't have to look it up next time
					$cache->save($result, $testPostId);
				}
			}else {
				echo 'memcache look up';
				$result = $cache->load($testPostId);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		//$fansList = $collector->getNewFanListFromPost($result);
		//$fdb = new Service_FancrankDBService($fanpageId, $accessToken);
		
		Zend_Debug::dump($result);
		echo 'fans list.........';
		
		// search database post
		// retrieve fanpage setting
		$fanpageSettingModel = new Model_FanpageSetting();
		$fanpageSetting = $fanpageSettingModel->findRow($fanpageId);
		if (!$fanpageSetting) {
			$fanpageSetting = $fanpageSettingModel->getDefaultSetting();
		} else {
			$fanpageSetting = $fanpageSetting->toArray();
		}
		
		//$db = Zend_Db_Table::getDefaultAdapter();
		$db = Zend_Db_Table::getDefaultAdapter();
		$commentModel = new Model_Comments();
		$likeModel = new Model_Likes();
		$pointLogModel = new Model_PointLog();
		$fanstat = new Model_FansObjectsStats();
		
		if ($result) {
			// handle admin post
			if ($result['facebook_user_id'] == $result['fanpage_id']) {
				
				// giving point to comment user
				foreach ($result['comment_list'] as $comment) {
					$db->beginTransaction();
					try {
						$bonus = 0;
						if ($comment->from->id != $result['fanpage_id'] &&
								!$commentModel->findRow($comment->id) &&
								($commentCount = $commentModel->getUserCommentCountByPost($result['post_id'], $comment->from->id)) < $fanpageSetting['point_comment_limit']) {
							Zend_Debug::dump($comment->from->id .' ' .$commentCount);

							// insert new comment into database
							$created = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : null, Zend_Date::ISO_8601);
							$row = array (
									'comment_id' => $comment->id,
									'fanpage_id' => $result['fanpage_id'],
									'comment_post_id' => $result['post_id'],
									'facebook_user_id' => $comment->from->id,
									'comment_message' => $db->quote($comment->message),
									'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
									'comment_likes_count' => isset ( $comment->like_count ) ? $comment->like_count : 0,
									'comment_type' => $comment->comment_type
							);
							Zend_Debug::dump($row);
							echo 'insert ' .$commentModel->insert($row) .'<br/>';
						
							// check bonus
							$commentTime = new Zend_Date($commentTime->created_time, Zend_Date::ISO_8601);
							$postTime = new Zend_Date($result['created_time'], Zend_Date::ISO_8601);
							$timeDifferentInMinute =floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
								
							if ($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
								$bonus = $fanpageSetting['point_comment_limit'] - $commentCount;
								echo 'has bonus';
							}
						
							// update fan profile
							$fan = new Model_Fans($comment->from->id, $result['fanpage_id']);
							if(!$fan->isNewFan()) {
								$fan->updateFanPoints($fanpageSetting['point_comment_admin']+$bonus);
								$fan->updateFanProfile();
							}
						
							// update fan stat
							switch ($comment->comment_type) {
								case 'status':
									$fanstat->addCommentStatus($result['fanpage_id'], $comment->from->id);
									break;
								case 'photo':
									$fanstat->addCommentPhoto($result['fanpage_id'], $comment->from->id);
									break;
								case 'video':
									$fanstat->addCommentVideo($result['fanpage_id'], $comment->from->id);
									break;
								case 'link':
									$fanstat->addCommentLink($result['fanpage_id'], $comment->from->id);
									break;
							}
						
							// update pointlog
							$pointLog = array();
							$pointLog['fanpage_id'] = $result['fanpage_id'];
							$pointLog['facebook_user_id'] =  $comment->from->id;
							$pointLog['object_id'] = $comment->id;
							$pointLog['object_type'] = $comment->comment_type;
							$pointLog['giving_points'] = $fanpageSetting['point_comment_admin'] + $bonus;
							$pointLog['bonus'] = $bonus;
							$pointLog['note'] = 'comment on admin object , bonus : ' .$bonus;
							$pointLogModel->insert($pointLog);
							$db->commit();
						}
					} catch (Exception $e) {
						echo $e->getMessage() .$e->getCode();
						$db->rollBack();
					}
					$db->closeConnection();
				}
				
				echo 'like----------------';
				// giving point to like user
				foreach ($result['like_list'] as $like) {
					$db->beginTransaction();
					try {
						if ($like['facebook_user_id'] != $result['fanpage_id']) {
							Zend_Debug::dump($like);
								
							$found = $likeModel->find($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])->current();
						
							if (empty($found)) {
								// insert new like into database
								if (isset($like['target'])) {
									unset($like['target']);
								}
								$likeModel->insert($like);
								
								$bonus = 0;
								// apply double point bonus
								$time = new Zend_Date($result['created_time'], Zend_Date::ISO_8601);
								$now = new Zend_Date();
								$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);
									
								if($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
									$bonus = $fanpageSetting['point_like_bonus'];
								}
								
								// update fan profile
								$fan = new Model_Fans($like['facebook_user_id'], $like['fanpage_id']);
								if (!$fan->isNewFan()) {
									$fan->updateFanPoints($fanpageSetting['point_like_admin']+$bonus);
									$fan->updateFanProfile();
								}
								
								// update fan stat
								switch ($result['post_type']) {
									case 'status':
										$fanstat->addLikeStatus($like['fanpage_id'], $like['facebook_user_id']);
										break;
									case 'photo':
										$fanstat->addLikePhoto($like['fanpage_id'], $like['facebook_user_id']);
										break;
									case 'video':
										$fanstat->addLikeVideo($like['fanpage_id'], $like['facebook_user_id']);
										break;
									case 'link':
										$fanstat->addLikeLink($like['fanpage_id'], $like['facebook_user_id']);
										break;
								}
								
								//update like point log
								$pointLog = array();
								$pointLog['fanpage_id'] = $like['fanpage_id'];
								$pointLog['facebook_user_id'] =  $like['facebook_user_id'];
								$pointLog['object_id'] = $like['post_id'];
								$pointLog['object_type'] = 'likes';
								$pointLog['giving_points'] = $fanpageSetting['point_like_admin'] + $bonus;
								$pointLog['bonus']= $bonus;
								$pointLog['note'] = empty($bonus) ? 'likes on admin post' : sprintf('likes on admin post, %s bonus for likes within %s minutes', $bonus, $fanpageSetting['point_bonus_duration']);
								$pointLogModel->insert($pointLog);
							} else {
								if ($found->likes == 0) {
									$found->likes = 1;
									$found->save();
								}
							}
						}
						$db->commit();
					} catch (Exception $e) {
						echo $e->getMessage();
						$db->rollBack();
					}
					$db->closeConnection();
				}
			} else {
				echo 'user post -----------------------';
				$db->beginTransaction();
				Zend_Debug::dump($result);
				$postModel = new Model_Posts();
				// handle new user post
				if (!$postModel->findRow($result['post_id'])) {
				    try {
				    	
				    	$row = $result;
						if(isset($row['comment_list'])) { unset($row['comment_list']); }
						if(isset($row['like_list'])) { unset($row['like_list']); }				    	
	    				// insert new post into database
	    				$postModel->insert($row);
    						
    					// update fan profile
    					$fan = new Model_Fans($result['facebook_user_id'], $result['fanpage_id']);
    					$fan->updateFanPoints($fanpageSetting['point_post_normal']);
    					$fan->updateFanProfile();
    						
    					// update fan stat
    					$fanstat = new Model_FansObjectsStats();
    					switch ($result['post_type']) {
    						case 'status':
    							$fanstat ->addPostStatus($result['fanpage_id'], $result['facebook_user_id']);
    							break;
    						case 'photo':
    							$fanstat->addPostPhoto($result['fanpage_id'], $result['facebook_user_id']);
    							break;
    						case 'video':
    							$fanstat->addPostVideo($result['fanpage_id'], $result['facebook_user_id']);
    							break;
    						case 'link':
    							$fanstat->addPostLink($result['fanpage_id'], $result['facebook_user_id']);
    							break;
    					}
    						
    					// update point log data
    					$pointLog = array();
    					$pointLog['fanpage_id'] = $result['fanpage_id'];
    					$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
    					$pointLog['object_id'] = $result['post_id'];
    					$pointLog['object_type'] = 'posts';
    					$pointLog['giving_points'] = $fanpageSetting['point_post_normal'];
    					$pointLog['note'] = 'post on fanpage';
    					echo $pointLogModel->insert($pointLog);
	    				
	    				// commit all update
	    				$db->commit();
					} catch (Exception $e) {
						$db->rollBack();
						echo $e->getMessage();
						$appLogger = Zend_Registry::get('appLog');
						$appLogger->log(sprintf('Unable to save post %s Error %s', $result['post_id'], $e->getMessage()), Zend_log::ERR);
					};
				}
				
				if ($db->isConnected()) {
					$db->closeConnection();
				}
				
				echo 'user post comment and like --------------';
				$db->beginTransaction();
				
				// get the list of unique user id that have interaction with the post
				$postUniqueList = $postModel->getUniqueInteractionList($result['post_id']);
				$isVirginity = false;
				$givingPoint = 0;
				// handle comment on user post
				try {
					foreach ($result['comment_list'] as $comment) {
						$commentFound = $commentModel->findRow($comment->id);
						if (!$commentFound) {
							$created = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : null, Zend_Date::ISO_8601);
							$row = array (
									'comment_id' => $comment->id,
									'fanpage_id' => $result['fanpage_id'],
									'comment_post_id' => $result['post_id'],
									'facebook_user_id' => $comment->from->id,
									'comment_message' => $db->quote($comment->message),
									'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
									'comment_likes_count' => isset ( $comment->like_count ) ? $comment->like_count : 0,
									'comment_type' => $comment->comment_type
							);
					
							// insert new comment into database
							$commentModel->insert($row);
					
							if ($comment->from->id !== $result['post_id'] && !in_array($comment->from->id, $postUniqueList)) {
									
								// check virginity break
								$pointLog = array();
								if (count($postUniqueList) === 0) {
									// update point log data
									$givingPoint += $fanpageSetting['point_virginity'] + 1;
									$pointLog['fanpage_id'] = $result['fanpage_id'];
									$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
									$pointLog['object_id'] = $result['post_id'];
									$pointLog['object_type'] = 'get_virginity_comment';
									$pointLog['giving_points'] = $fanpageSetting['point_virginity'] + 1;
									$pointLog['bonus'] = $fanpageSetting['point_virginity'] ;
									$pointLog['note'] = 'receive unique from comment' .$comment->id .'and ' .$fanpageSetting['point_virginity'] .'bonus on breaking post virginity';
									Zend_Debug::dump($pointLog);
									echo 'break virgin' .$pointLogModel->insert($pointLog);
								} else {
									// update point log data
									$givingPoint += 1;
									$pointLog = array();
									$pointLog['fanpage_id'] = $result['fanpage_id'];
									$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
									$pointLog['object_id'] = $result['post_id'];
									$pointLog['object_type'] = 'get_unique_comment';
									$pointLog['giving_points'] = 1;
									$pointLog['note'] = 'receive unique from comment ' .$comment->id;
									Zend_Debug::dump($pointLog);
									echo $pointLogModel->insert($pointLog);
								}
									
								// add the id into the array
								$postUniqueList[] = $comment->from->id;
							}
						}
					}
					
					// handle like on user post
					foreach ($result['like_list'] as $like) {
						$likeFound = $likeModel->find($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])->current();
							
						if (!$likeFound) {
							// insert new like into database
							if (isset($like['target'])) {
								unset($like['target']);
							}
							$likeModel->insert($like);
					
							// check virginity break
							if ($result['facebook_user_id'] !== $like['facebook_user_id'] && ($isUnique = !in_array($like['facebook_user_id'], $postUniqueList))) {
									
								// check virginity break
								if (count($postUniqueList) === 0) {
									// update get like point log
									$pointLog = array();
									$givingPoint += $fanpageSetting['point_like_normal'] + $fanpageSetting['point_virginity'];
									$pointLog['fanpage_id'] = $result['fanpage_id'];
									$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
									$pointLog['object_id'] = $result['post_id'];
									$pointLog['object_type'] = 'get_virginity_like';
									$pointLog['giving_points'] = $fanpageSetting['point_like_normal'] + $fanpageSetting['point_virginity'] + 1;
									$pointLog['bonus']= $fanpageSetting['point_virginity'] + 1;
									$pointLog['note'] = 'receive like and ' .($fanpageSetting['point_virginity'] + 1) .' bonus on breaking virginity and unique';
									$pointLogModel->insert($pointLog);
									Zend_Debug::dump($pointLog);
					
									// update like point log
									$pointLog = array();
									$pointLog['fanpage_id'] = $like['fanpage_id'];
									$pointLog['facebook_user_id'] =  $like['facebook_user_id'];
									$pointLog['object_id'] = $like['post_id'];
									$pointLog['object_type'] = 'likes';
									$pointLog['giving_points'] = $fanpageSetting['point_like_normal'];
									$pointLog['note'] = 'likes on user post';
									$pointLogModel->insert($pointLog);
									Zend_Debug::dump($pointLog);
					
									$postUniqueList[] = $like['facebook_user_id'];
								} else {
									// update get like point log
									$pointLog = array();
									if ($isUnique) {
										$givingPoint += $fanpageSetting['point_like_normal'] + 1;
										// update get like point log
										$pointLog['fanpage_id'] = $result['fanpage_id'];
										$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
										$pointLog['object_id'] = $result['post_id'];
										$pointLog['object_type'] = 'get_unique_likes';
										$pointLog['giving_points'] = $fanpageSetting['point_like_normal'] + 1;
										$pointLog['bonus']= 1;
										$pointLog['note'] = 'receive likes on post and 1 bonus on unique' ;
										$pointLogModel->insert($pointLog);
										Zend_Debug::dump($pointLog);
											
										$postUniqueList[] = $like['facebook_user_id'];
									} else {
										$givingPoint += $fanpageSetting['point_like_normal'];
										$pointLog['fanpage_id'] = $result['fanpage_id'];
										$pointLog['facebook_user_id'] =  $result['facebook_user_id'];
										$pointLog['object_id'] = $result['post_id'];
										$pointLog['object_type'] = 'get_likes';
										$pointLog['giving_points'] = $fanpageSetting['point_like_normal'];
										$pointLog['note'] = 'receive likes on post';
										$pointLogModel->insert($pointLog);
										Zend_Debug::dump($pointLog);
									}
									
									// update liker fan profile
									$fan = new Model_Fans($like['facebook_user_id'],  $like['fanpage_id']);
										
									if (!$fan->isNewFan()) {
										$fan->updateFanPoints($fanpageSetting['point_like_normal']);
										$fan->updateFanProfile();
									}
										
									// update like point log
									$pointLog = array();
									$pointLog['fanpage_id'] = $like['fanpage_id'];
									$pointLog['facebook_user_id'] =  $like['facebook_user_id'];
									$pointLog['object_id'] = $like['post_id'];
									$pointLog['object_type'] = 'likes';
									$pointLog['giving_points'] = $fanpageSetting['point_like_normal'];
									$pointLog['note'] = 'likes on user post';
									$pointLogModel->insert($pointLog);
									Zend_Debug::dump($pointLog);
								}
							}
						} else {
							$likeFound->likes = 1;
							$likeFound->save();
						}
					}
					
					// update post owner profile
					$fan = new Model_Fans($result['facebook_user_id'], $result['fanpage_id']);
					if (!$fan->isNewFan()) {
						$fan->updateFanPoints($givingPoint);
						$fan->updateFanProfile();
						// update post owner fan stat
						$fanstat->updatedFanWithPoint($result['fanpage_id'], $result['facebook_user_id'], $fan->getFanExp(), $fan->getFanPoint());
					}
						
					$db->commit();
					echo $givingPoint;
						
				} catch (Exception $e) {
					$db->rollBack();
				}
				
				// update fan stat
				foreach ($postUniqueList as $facebookUserId) {
					if ($facebookUserId !== $result['fanpage_id']) {
						$fan = new Model_Fans($facebookUserId, $result['fanpage_id']);
						$fanstat->updatedFanWithPoint($result['fanpage_id'], $facebookUserId, $fan->getFanExp(), $fan->getFanPoint());
					}
				}
			} 
		}

    }
    
    public function test21Action() {
    
    	$fanpage_id = '197221680326345';
    	$facebook_user_id = '48911310';
    	for ($i =711 ;$i< 717; $i++){
	    	$badgeId = $i;
	    	
	    	$badgeEventModel = new Model_BadgeEvents();
	    	
	    	$badgeModel = new Model_Badges();
	    	$badge = $badgeModel->findRow($badgeId)->toArray();
	    	Zend_Debug::dump($badge);
	    	
	    	$badgeModel = Fancrank_BadgeFactory::factory('default');
	    	if($badgeModel->isFanEligible($fanpage_id, $facebook_user_id, $badgeId)) {
	    		echo 'yes';
	    	}else {
	    		echo 'no';
	    	}
    	}
    }
    
    public function test22Action() {
		$pointLog = new Model_PointLog();
		$fanpageId = '216821905014540';
		echo $pointLog->getAwardPointsByFanpgeIdAndTime($fanpageId, 'yesterday');
		
		$insightId = $fanpageId .'_insights';

		$insightData = null;
		try {
			$cache = Zend_Registry::get('memcache');
				
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
				
				if(!empty($result->data)) {
					$insightData = $result->data;
					//Save to the cache, so we don't have to look it up next time
					$cache->save($insightData, $insightId);
				}
			}else {
				echo 'memcache look up';
				$insightData = $cache->load($insightId);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		Zend_Debug::dump($insightData);
		Zend_Debug::dump($this->insightDataParser($insightData));
    }
    
    public function test24Action() {
    	$fanpageId = '197221680326345_428051090576735';
    	$accessToken = 'AAAFHFbxmJmgBAM6lldcZAZCggKej98EhT9n4hAcuDfpIQVB7nHKIuGNOZAQ2rprCeFEBk3tDZApEb8KUJZBuFR8lIUTvKE4yW3amNYmJMWgZDZD';
    	$since = 'yesterday';
    	$limit = 500;
    	$this->postBatchQuery($fanpageId, $accessToken, $limit, $since);
    }
    
    public function test25Action() {
    	$fanpageId = '216821905014540';
    	$facebook_user_id = '508061175';
		$rank = new Model_Rankings();
		Zend_Debug::dump($rank->getTopFansByWeek($fanpageId));
		
		echo '--------------top talker';
		Zend_Debug::dump($rank->getTopTalkerByWeek($fanpageId));
		
		echo '--------------top clicker';
		Zend_Debug::dump($rank->getTopClickerByWeek($fanpageId));
		
		echo '--------------top popular';
		Zend_Debug::dump($rank->getMostPopularByWeek($fanpageId));
		
		echo '--------------top post';
		Zend_Debug::dump($rank->getTopPosts($fanpageId));
		
		echo '--------------user top fan';
		Zend_Debug::dump($rank->getUserTopFansRankByWeek($fanpageId, $facebook_user_id));
		
		echo '--------------top talker';
		Zend_Debug::dump($rank->getUserTopTalkerRankByWeek($fanpageId, $facebook_user_id));
		
		echo '----------------------------fan get like stat';
		$fanpage_id = '216821905014540';
		$stat = new Model_FansObjectsStats();
		Zend_Debug::dump($stat->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'status'));		
    }
    
    public function test26Action() {
    	$data['fanpage_id'] = '197221680326345';
    	$settingModel = new Model_FanpageSetting();
    	$fanpageSetting = $settingModel->getFanpageSetting($data['fanpage_id']);
    	 
    	$time = new Zend_Date('2012-10-25T17:43:39+00:00', Zend_Date::ISO_8601);
    	$now = new Zend_Date();

    	$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);
    	
    	if($timeDifferentInMinute > $fanpageSetting['point_bonus_duration']) {
    		$bonus = 2;
    	}

    }
    
    public function test27Action() {
		//$fanpageActivitiesModel = new Model_FancrankActivities();
		//$result = $fanpageActivitiesModel->getRecentFanpageActivitiesSince('216821905014540', (time()-3600*24*7));

		$fanpageModel = new Model_Fanpages();
		$result = $fanpageModel->getActiveFansSince('216821905014540', (time()-3600*24*2));
		Zend_Debug::dump($result);    	
    }
    
    public function testpostAction() {
    
    	$starttime = time();
    	$postId = '197221680326345_428051090576735';
    	$data['facebook_user_id'] = '100004566963982';
    	$data['fanpage_id'] = '197221680326345';
    	$data['fanpage_name'] = 'dan club';
    	$data['access_token'] = 'AAAFHFbxmJmgBAMSA4ZAdwftxnE4X9BRWOtZBehbIe4F923gH8PreivIqkF6JnZChO8idLwSTX4HqXRpfyaH18JKrjMldKHdq14wEMJnUAZDZD';
    	//$data['post_id'] = $this->_getParam('post_id');
    	$data['message'] = 'I can get in';
    	try{
    		$fancrankFB = new Service_FancrankFBService();
    		$params =  array(
    				'message' => $data['message'],
    				'access_token' => $data['access_token']
    		);
    
    		$ret_obj = $fancrankFB->api('/'.$data['fanpage_id'].'/feed', 'POST',
    				$params);
    			
    		Zend_Debug::dump($ret_obj);

    		
    		$data['post_id'] = $ret_obj['id'];
    		
    		$ret_obj = $fancrankFB->api('/'.$data['post_id'], 'GET',
    				$params);
    		exit();
    		//$data['post_id'] = $postId;
    		Zend_Debug::dump($data['post_id']);	
    		$client = new Zend_Http_Client;
    		$client->setUri("https://graph.facebook.com/". $data['post_id']);
    		$client->setMethod(Zend_Http_Client::GET);
    		$client->setParameterGet('access_token', $data['access_token']);
    
    		$response = $client->request();
    
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
			Zend_Debug::dump($result); exit();
    		if(!empty ($result)) {
    			$db = Zend_Db_Table::getDefaultAdapter();
    			$db->beginTransaction();
    			
    			// check response error from facebook graph api
				$result = $this->facebookResponseCheck($result);
				
    			$postModel = new Model_Posts();
    			$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
    			$updated = new Zend_Date(!empty($post->updated_time) ? $post->updated_time : null, Zend_Date::ISO_8601);
    
    			$row = array(
    					'post_id'               => $result->id,
    					'facebook_user_id'      => $result->from->id,
    					'fanpage_id'            => $data['fanpage_id'],
    					'post_message'          => isset($result->message) ? $postModel->quoteInto($result->message) : '',
    					'picture'				=> !empty($result->picture) ? $result->picture : '',
    					'link'					=> !empty($result->link) ? $result->link : '',
    					'post_type'             => !empty($result->type) ? $result->type : '',
    					'status_type'           => !empty($result->status_type) ? $result->status_type : '',
    					'post_description'		=> !empty($result->description) ? $postModel->quoteInto($result->description) : '',
    					'post_caption'			=> !empty($result->caption) ? $postModel->quoteInto($result->caption) : '',
    					'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
    					'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
    					'post_comments_count'   => !empty($result->comments->count) ? $result->comments->count : 0,
    					'post_likes_count'      => isset($result->likes) && isset($result->likes->count) ? $result->likes->count : 0
    			);
    
    			if (property_exists($result, 'application') && isset($result->application->id)) {
    				$row['post_application_id'] = $result->application->id;
    				$row['post_application_name'] = $result->application->name;
    			} else {
    				$row['post_application_id'] = null;
    				$row['post_application_name'] = null;
    			}
    
    			try {
    				// retrieve fanpage setting
    				$fanpageSettingModel = new Model_FanpageSetting();
    				$settingData = $fanpageSettingModel->findRow($data['fanpage_id']);
    				if(!$settingData) {
    					$settingData = $fanpageSettingModel->getDefaultSetting();
    				}else {
    					$settingData = $settingData->toArray();
    				}
    					
    				// insert new post into database
    				$postModel->insert($row);
    					
    				// add activity into database
    				$this->addactivity('post-'.$row['post_type'], $data['post_id'],
    						$data['fanpage_id'],$data['fanpage_id'], $data['fanpage_name'],$row['post_message'] );
    					
    				// update fan data
    				$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
    				$fan->updateFanPoints($settingData['point_post_normal']);
    				$fan->updateFanProfile();
    					
    				// update fan stat
    				$fanstat = new Model_FansObjectsStats();
    				switch($row['post_type']){
    					case 'status':
    						$fanstat ->addPostStatus($data['fanpage_id'], $data['facebook_user_id']);
    						break;
    					case 'photo':
    						$fanstat->addPostPhoto($data['fanpage_id'], $data['facebook_user_id']);
    						break;
    					case 'video':
    						$fanstat->addPostVideo($data['fanpage_id'], $data['facebook_user_id']);
    						break;
    					case 'link':
    						$fanstat->addPostLink($data['fanpage_id'], $data['facebook_user_id']);
    						break;
    				}
    					
    				// update point data
    				$pointLog = array();
    				$pointLog['fanpage_id'] = $data['fanpage_id'];
    				$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
    				$pointLog['object_id'] = $data['post_id'];
    				$pointLog['object_type'] = 'posts';
    				$pointLog['giving_points'] = $settingData['point_post_normal'];
    				$pointLog['note'] = 'post on fanpage';
    				$pointLogModel = new Model_PointLog();
    				$result = $pointLogModel->insert($pointLog);
    				
    				// commit all update
    				$db->commit();
    			} catch (Exception $e) {
    				$db->rollBack();
    				print $e->getMessage();
    				$appLogger = Zend_Registry::get('appLog');
    				$appLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $data['post_id'], $data['fanpage_id'], $e->getMessage()), Zend_log::ERR);
    			}
    		}
    	}	catch (Exception $e){
    		echo $e->getMessage();
    		$appLogger = Zend_Registry::get('appLog');
    		$appLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $data['post_id'], $data['fanpage_id'], $e->getMessage()), Zend_log::ERR);
    	}
    	echo '<br/>' .(time() - $starttime) . 'sec';

    }

    public function testcommentAction() {
    	$data['facebook_user_id'] = '100001005159808';
    	$data['fanpage_id'] = '197221680326345';
    	$data['fanpage_name'] = 'dan club';
    	$data['access_token'] = 'AAAFHFbxmJmgBAM6lldcZAZCggKej98EhT9n4hAcuDfpIQVB7nHKIuGNOZAQ2rprCeFEBk3tDZApEb8KUJZBuFR8lIUTvKE4yW3amNYmJMWgZDZD';
    	$data['post_id'] = '197221680326345_428051090576735';
    	$data['post_type'] = 'status';
    	$data['message'] = 'test message random number: ' .rand(0, 10);

    	//$data['target_id'] = $this->_getParam('target_id');
    	//$data['target_name'] = $this->_getParam('target_name');
    
    	$bonus = 0;
    	$virgin=false;
    
    	try{
			// save comment to facebook original page
    		$client = new Zend_Http_Client;
    		$client->setUri("https://graph.facebook.com/" .$data['post_id'] ."/comments");
    		$client->setMethod(Zend_Http_Client::POST);
    		$client->setParameterGet('access_token', $data['access_token']);
    		$client->setParameterGet('message', $data['message']);
    		$response = $client->request();
    		
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    		$result = $this->facebookResponseCheck($result);
    		
    		if(empty($result->id)) {
    			return;
    		}
    		
    		$data['comment_id'] = $result->id;

    		// retrieve comment information through facebook graph api
    		$client->setUri("https://graph.facebook.com/". $data['comment_id']);
    		$client->setMethod(Zend_Http_Client::GET);
    		$client->setParameterGet('access_token', $data['access_token']);
    		$response = $client->request();
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

    		$result = $this->facebookResponseCheck($result);
    		
    		if(empty($result->id)) {
    			return;
    		}
    		
    		// get default database adapter
    		$db = Zend_Db_Table::getDefaultAdapter();
    		$db->beginTransaction();
    		
    		$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
    		$commentModel = new Model_Comments ();
    		$row = array (
    				'comment_id' => $result->id,
    				'fanpage_id' => $data['fanpage_id'],
    				'comment_post_id' => $data['post_id'],
    				'facebook_user_id' => $result->from->id,
    				'comment_message' => $commentModel->quoteInto($result->message),
    				'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
    				'comment_likes_count' => isset ( $result->like_count ) ? $result->like_count : 0,
    				'comment_type' => $data['post_type']
    		);
    			
    		try {
    			// retrieve fanpage setting
    			$fanpageSettingModel = new Model_FanpageSetting();
    			$settingData = $fanpageSettingModel->findRow($data['fanpage_id']);
    			if(!$settingData) {
    				$settingData = $fanpageSettingModel->getDefaultSetting();
    			}else {
    				$settingData = $settingData->toArray();
    			}
    			
    			// insert new comment into database
    			$commentModel->insert($row);
    			
    			// add activity into database
    			$this->addactivity('comment-'.$data['post_type'], $data['post_id'],
    					$data['fanpage_id'], $data['fanpage_id'], 'dan club', $row['comment_message']);
    			
    			$db->commit();
    		} catch ( Exception $e ) {
    			$db->rollBack();
    			print $e->getMessage ();
    			$appLogger = Zend_Registry::get('appLog');
    			$appLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $comment->id, $data['fanpage_id'], $e->getMessage () ), Zend_log::ERR );
    			return;
    		}
    			
    	}catch(Exception $e){
    		echo $e->getMessage();
    		$appLogger = Zend_Registry::get('appLog');
    		$appLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $data['message'], $data['fanpage_id'], $e->getMessage () ), Zend_log::ERR );
    	}
    }
    
    public function commentAction() {
    	$data['facebook_user_id'] = $this->_user->facebook_user_id;
    	$data['access_token'] = $this->_user->facebook_user_access_token;
    	$data['post_id'] = $this->_getParam('post_id');
    	$data['post_type'] = $this->_getParam('post_type');
    	$data['message'] = $this->_getParam('message');
    	$data['fanpage_id'] = $this->_getParam('fanpage_id');
    	$data['fanpage_name'] = $this->_getParam('fanpage_name');
    	//$data['target_id'] = $this->_getParam('target_id');
    	//$data['target_name'] = $this->_getParam('target_name');
    
    	$fancrankFB = new Service_FancrankFBService();
    
    	$params =  array(
    			'access_token' => $data['access_token'],
    			'message' => $data['message'],
    	);
    
    	$bonus = 0;
    	$virgin=false;
    
    	try{
    		$ret_obj = $fancrankFB->api("/".$data['post_id']."/comments", 'POST', $params);
    			
    		$data['comment_id'] = $ret_obj['id'];
    		//Zend_Debug::dump($data['comment_id']);
    			
    		$client = new Zend_Http_Client;
    		$client->setUri("https://graph.facebook.com/". $data['comment_id']);
    		$client->setMethod(Zend_Http_Client::GET);
    		$client->setParameterGet('access_token', $data['access_token']);
    		$response = $client->request();
    		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    			
    		$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
    		$commentModel = new Model_Comments ();
    		$row = array (
    				'comment_id' => $result->id,
    				'fanpage_id' => $data['fanpage_id'],
    				'comment_post_id' => $data['post_id'],
    				'facebook_user_id' => $result->from->id,
    				'comment_message' => $commentModel->quoteInto($result->message),
    				'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
    				'comment_likes_count' => isset ( $result->like_count ) ? $result->like_count : 0,
    				'comment_type' => $data['post_type']
    		);
    			
    		// $fansId[] = $comment->from->id;
    		try {
    			// save fanpage's post's relative information into post table
    			// Zend_Debug::dump($row);
    			$commentModel->saveAndUpdateById ($row, array('id_field_name' =>'comment_id'));
    		} catch ( Exception $e ) {
    			print $e->getMessage ();
    			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
    			$collectorLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $comment->id, $this->_fanpageId, $e->getMessage () ), Zend_log::ERR );
    			return;
    		}
    			
    			
    		$post= new Model_Posts();
    		$post = $post -> addCommentToPostReturn($data['post_id']);
    		//Zend_Debug::dump($post);
    			
    		if ($post==null){
    				
    			$post['facebook_user_id'] = $this->_getParam('target_id');
    
    			//Zend_Debug::dump($post);
    		}
    		$fanstat = new Model_FansObjectsStats();
    		switch($data['post_type']){
    				
    			case 'status':
    				$fanstat->addCommentStatus($data['fanpage_id'], $data['facebook_user_id']);
    				if($post['facebook_user_id'] != $data['fanpage_id']){
    					$fanstat -> addGetCommentStatus($data['fanpage_id'], $post['facebook_user_id']);
    				}
    				break;
    			case 'photo':
    				$fanstat->addCommentPhoto($data['fanpage_id'], $data['facebook_user_id']);
    				if($post['facebook_user_id'] != $data['fanpage_id']){
    					$fanstat -> addGetCommentPhoto($data['fanpage_id'], $post['facebook_user_id']);
    				}
    				break;
    			case 'video':
    				$fanstat->addCommentVideo($data['fanpage_id'], $data['facebook_user_id']);
    				if($post['facebook_user_id'] != $data['fanpage_id']){
    					$fanstat -> addGetCommentVideo($data['fanpage_id'], $post['facebook_user_id']);
    				}
    				break;
    			case 'link':
    				$fanstat->addCommentLink($data['fanpage_id'], $data['facebook_user_id']);
    				if($post['facebook_user_id'] != $data['fanpage_id']){
    					$fanstat -> addGetCommentLink($data['fanpage_id'], $post['facebook_user_id']);
    				}
    				break;
    					
    		}
    		if (isset($post['post_comments_count'])){
    			if ($post['post_comments_count'] + $post['post_likes_count'] == 1){
    				$virgin=true;
    				echo 'virginity is true';
    			}
    
    		}
    			
    		//preparing for points addition
    		//if user is not coming on this own post
    		if ($post['facebook_user_id'] != $data['facebook_user_id']){
    
    			//checking how many times this user has comment on this post
    			$client->setUri("https://graph.facebook.com/". $data['post_id']."/comments");
    			$client->setMethod(Zend_Http_Client::GET);
    			$client->setParameterGet('access_token', $data['access_token']);
    			$response = $client->request();
    			$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    
    			//Zend_Debug::dump($result);
    			$comment_counter = 0;
    			foreach ($result->data as $c){
    					
    				if ($c->from->id == $data['facebook_user_id']){
    					$comment_counter ++;
    				}
    					
    			}
    			//echo $comment_counter;
    			//if post owner is not a fanpage
    
    			if($comment_counter < 6){
    
    				if ($post['facebook_user_id'] != $data['fanpage_id']){
    						
    					if(isset($post['post_comments_count'])){
    						//GIVE 2 POINTS FOR RECIEVING A COMMENT
    						$fan = new Model_Fans($post['facebook_user_id'], $data['fanpage_id']);
    						$fan->updateFanPoints(2 + (($virgin)?4:0));
    						$fan->updateFanProfile();
    							
    						$pointLog = array();
    						$pointLog['fanpage_id'] = $data['fanpage_id'];
    						$pointLog['facebook_user_id'] =  $post['facebook_user_id'];
    						$pointLog['object_id'] = $data['post_id'];
    						$pointLog['object_type'] = 'recieve a comment';
    						$pointLog['giving_points'] = 2 + (($virgin)?4:0);
    						$pointLog['note'] = 'likes on object ,'.(($virgin)?'virginity broken':'');
    
    						$pointLogModel = new Model_PointLog();
    						$result = $pointLogModel->insert($pointLog);
    					}
    						
    						
    				}else{
    					if(!isset($post['post_comments_count'])){
    						$cache = Zend_Registry::get('memcache');
    						try {
    							//Zend_Debug::dump($cache);
    							//Check to see if the $fanpageId is cached and look it up if not
    							if(isset($cache) && !$cache->load($data['post_id'])){
    								//Look up the $fanpageId
    								//$post = $cache->load($data['post_id']);
    							}else {
    								//echo 'memcache look up';
    								$post = $cache->load($data['post_id']);
    							}
    								
    							$a = new Zend_Date($post['created_time']);
    							//if it is a fanpage, check if bonus occured
    							echo 'poster is a fan page, checking bonus';
    							if ($a->compare(1, Zend_Date::HOUR)== -1) {
    								$bonus = $comment_counter;
    								//echo ' within 1 hour, bonus is true';
    							}
    							//echo $bonus;
    								
    								
    						} catch (Exception $e) {
    							Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    							//echo $e->getMessage();
    						}
    						echo 'this post is from an admin.';
    					}
    						
    						
    
    				}
    
    				$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
    				$fan->updateFanPoints(2 + $bonus);
    				$fan->updateFanProfile();
    
    				$pointLog = array();
    				$pointLog['fanpage_id'] = $data['fanpage_id'];
    				$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
    				$pointLog['object_id'] = $data['post_id'];
    				$pointLog['object_type'] = 'comments';
    				$pointLog['giving_points'] = 2 + $bonus;
    				$pointLog['bonus'] = $bonus;
    				$pointLog['note'] = 'comments on object ,'.(($virgin)?'virginity broken':'');
    					
    				$pointLogModel = new Model_PointLog();
    				$result = $pointLogModel->insert($pointLog);
    
    			}
    
    		}
    
    		$this->addactivity('comment-'.$data['post_type'], $data['post_id'],
    				$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $row['comment_message']);
    			
    		echo 'adding activities';
    			
    			
    			
    	}catch(Exception $e){
    		echo $e;
    	}
    }
    
    public function testlikesAction() {
    	//$objectId = $this->_getParam('object_id');
    	$likeModel = new Model_Likes();
    	$data['facebook_user_id'] = $this->_user->facebook_user_id;
    	$data['post_id'] = $this->_getParam('post_id');
    	$data['fanpage_id'] = $this->_getParam('fanpage_id');
    	$data['post_type'] = $this->_getParam('post_type');
    	$data['access_token'] = $this->_user->facebook_user_access_token;
    
    	$isComment = strpos($this->_getParam('post_type'),'comment')?true:false;
    
    	try {
    		//$data['post_id'] = $this->_getParam('post_id');
    		$postId = $data['post_id'];
    		$fancrankFB = new Service_FancrankFBService();
    		$params =  array(
    				'access_token' => $data['access_token']
    		);
    		$fancrankFB->api("/$postId/likes", 'POST', $params);
    
    		//save object likes to fancrank database
    		//******* WE HAVE YET TO UPDATE THE POSTS DATABASE THRU THIS FUNCTION
    
    		echo 'attempting insert data to like.';
    		//$likeModel->insert($data);
    		$likesModel = $likeModel->insertNewLikesReturn($data['fanpage_id'], $data['post_id'], $data['facebook_user_id'], $data['post_type'] );
    		//Zend_Debug::dump($likesModel);
    		//ADDING POINTS NOW
    		//$activity_type, $event_object, $facebook_user_id, $facebook_user_name, $fanpage_id, $target_user_id, $target_name, $message
    
    		$bonus = false;
    		$virgin = false;
    		//echo $bonus;
    		if($likesModel !== 0){
    			echo 'like is not redundant';
    				
    			$fanstat = new Model_FansObjectsStats();
    			if ($isComment) {
    				$comment = new Model_Comments();
    				$post = $comment -> addLikeToCommentReturn($data['post_id']);
    					
    				if ($post == null){
    						
    					$post['facebook_user_id'] = $this->_getParam('target_id');
    						
    				}
    				$fanstat -> addLikeCommentCount($data['fanpage_id'], $data['facebook_user_id']);
    				echo 'increasing comment likes count';
    
    				if($post['facebook_user_id'] != $data['fanpage_id']){
    					$fanstat -> addGetLikeComment($data['fanpage_id'], $post['facebook_user_id']);
    				}
    
    				// you still need to compare the created times and check if bonus should be applied
    			}else{
    				// if liked a post
    				$post= new Model_Posts();
    				$post = $post -> addLikeToPostReturn($data['post_id']);
    				//Zend_Debug::dump($post);
    
    				if ($post==null){
    						
    					$post['facebook_user_id'] = $this->_getParam('target_id');
    
    					//Zend_Debug::dump($post);
    				}
    
    				switch($data['post_type']){
    						
    					case 'status':
    						$fanstat->addLikeStatus($data['fanpage_id'], $data['facebook_user_id']);
    						if($post['facebook_user_id'] != $data['fanpage_id']){
    							$fanstat -> addGetLikeStatus($data['fanpage_id'], $post['facebook_user_id']);
    						}
    						break;
    					case 'photo':
    						$fanstat->addLikePhoto($data['fanpage_id'], $data['facebook_user_id']);
    						if($post['facebook_user_id'] != $data['fanpage_id']){
    							$fanstat -> addGetLikePhoto($data['fanpage_id'], $post['facebook_user_id']);
    						}
    						break;
    					case 'video':
    						$fanstat->addLikeVideo($data['fanpage_id'], $data['facebook_user_id']);
    						if($post['facebook_user_id'] != $data['fanpage_id']){
    							$fanstat -> addGetLikeVideo($data['fanpage_id'], $post['facebook_user_id']);
    						}
    						break;
    					case 'link':
    						$fanstat->addLikeLink($data['fanpage_id'], $data['facebook_user_id']);
    						if($post['facebook_user_id'] != $data['fanpage_id']){
    							$fanstat -> addGetLikeLink($data['fanpage_id'], $post['facebook_user_id']);
    						}
    						break;
    
    				}
    				if (isset($post['post_comments_count'])){
    					if ($post['post_comments_count'] + $post['post_likes_count'] == 1){
    						$virgin=true;
    						echo 'virginity is true';
    					}
    				}
    
    			}
    				
    			$this->addactivity('like-'.$this->_getParam('post_type'), $data['post_id'],
    					$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $this->_getParam('mes'));
    			echo 'adding activities';
    				
    			//Zend_Debug::dump($likesModel);
    			//if likes model didn't return anything
    			//ie if its not a "new" like, there doesn't need to be points
    			if ($likesModel == 1){
    
    				echo ' Like is new, points need to be allocated';
    
    				if ($data['facebook_user_id'] != $post['facebook_user_id']){
    					echo 'User did not like his/her own post';
    					//if not fanpage , meaning some other user needs points
    					if ($post['facebook_user_id'] != $data['fanpage_id']){
    						echo 'poster is not a fanpage';
    						//update fan
    						$fan = new Model_Fans($post['facebook_user_id'], $data['fanpage_id']);
    						$fan->updateFanPoints(1);
    						$fan->updateFanProfile();
    							
    						//update point log
    						$pointLog = array();
    						$pointLog['fanpage_id'] = $data['fanpage_id'];
    						$pointLog['facebook_user_id'] =  $post['facebook_user_id'];
    						$pointLog['object_id'] =  $data['post_id'];
    						$pointLog['object_type'] = 'get likes';
    						$pointLog['giving_points'] = 1 +(($virgin)?4:0);
    						$pointLog['note'] = 'get like on object'.(($virgin)?', viriginity broken':'');
    							
    
    						$pointLogModel = new Model_PointLog();
    						$result = $pointLogModel->insert($pointLog);
    
    
    					}else{
    						echo 'trying memcache';
    						$cache = Zend_Registry::get('memcache');
    
    						//$cache->remove($this->_fanpageId .'_' .$this->_userId);
    						// Zend_Debug::dump($data['post_id']);
    						try {
    							//Zend_Debug::dump($cache);
    							//Check to see if the $fanpageId is cached and look it up if not
    							if(isset($cache) && !$cache->load($data['post_id'])){
    								//Look up the $fanpageId
    								//$post = $cache->load($data['post_id']);
    							}else {
    
    								//echo 'memcache look up';
    								$post = $cache->load($data['post_id']);
    							}
    						} catch (Exception $e) {
    							Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    							//echo $e->getMessage();
    						}
    
    						//Zend_Debug::dump($post);
    
    						/* DONT HAVE TO DO THIS BECAUSE BONUS CRON WOULD DO IT*/
    						/*
    						 if(isset($post['created_time'])){
    
    						$a = new Zend_Date($post['created_time']);
    						//if it is a fanpage, check if bonus occured
    						echo 'poster is a fan page, checking bonus';
    						if ($a->compare(1, Zend_Date::HOUR)== -1) {
    						$bonus = true;
    						echo ' within 1 hour, bonus is true';
    						}
    						echo $bonus;
    						}*/
    					}
    						
    					echo 'giving points to liker';
    					$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
    					$fan->updateFanPoints(1);
    					$fan->updateFanProfile();
    						
    					$pointLog = array();
    					$pointLog['fanpage_id'] = $data['fanpage_id'];
    					$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
    					$pointLog['object_id'] = $data['post_id'];
    					$pointLog['object_type'] = 'likes';
    					$pointLog['giving_points'] = 1 * (($bonus)?2:1);
    					$pointLog['bonus']= ($bonus)?1:null;
    					$pointLog['note'] = 'likes on object ,'.(($bonus)?'by admin within 1 hour':'');
    
    					$pointLogModel = new Model_PointLog();
    					$result = $pointLogModel->insert($pointLog);
    						
    				}
    			}
    			//ADD TO POINT LOG
    
    		}
    
    	} catch (Exception $e) {
    		//TO LOG
    		echo $e;
    	}
    
    	//}
    }
    
    private function facebookResponseCheck($result) {
    	// check response error from facebook graph api
    	if(!empty($result->error)) {
    		$type = isset($result->error->type) ? $result->error->type : '';
    		$code = isset($result->error->code) ? $result->error->code : '';
    		$message = isset($result->error->message) ? $result->error->message : '';
    		$msg = sprintf('type: %s, $code: %s, message: %s', $type, $code, $message);
    		throw new Exception($msg);
    	}
    	return $result;
    }
    
    protected function  addactivity($activity_type, $event_object, $fanpage_id, $target_user_id, $target_name, $message ){

    	$data['activity_type'] = $activity_type;
    	$data['event_object'] = $event_object;
    	$data['facebook_user_id'] = $fanpage_id;
    	$data['facebook_user_name'] = 'dan club';
    	$data['fanpage_id'] = $fanpage_id;
    	$data['target_user_id'] = $target_user_id;
    	$data['target_user_name'] = $target_name;
    	$data['message'] = $message;
    	$act = new Model_FancrankActivities();
    	$post = new Model_Posts();
    	/*
    		if ($data['activity_type'] == "like-status" || $data['activity_type'] == "like-photo" ||
    			 $data['activity_type'] == "like-video" || $data['activity_type'] == "like-link"){
    	$post->addLikeToPost($data['event_object']);
    	}else if ($data['activity_type'] == "unlike-status" || $data['activity_type'] == "unlike-photo" ||
    			$data['activity_type'] == "unlike-video" || $data['activity_type'] == "unlike-link"){
    	$post->subtractLikeToPost($data['event_object']);
    	}else if ($data['activity_type'] == "comment-status" || $data['activity_type'] == "comment-photo" ||
    			$data['activity_type'] == "comment-video" || $data['activity_type'] == "comment-link"){
    	$post->addCommentToPost($data['event_object']);
    	}
    	*/
    	$act -> addActivities($data);
    }
    
    // test multi table insert
    public function test23Action() {
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		// init default db adapter
		$db = Zend_Db::factory($config->resources->db);
		$db->beginTransaction();
		try {

		//read fanpage setting
			
		//prepare insert like
		$sql = "select * from posts limit 1;";			
		
		//prepare insert activity
		
		//prepare insert pointlog
		
		//prepare update fan
		
		//prepare update fan stat
		
		//update memcache 
		
		//execute
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		Zend_Debug::dump($result);
		$db->commit();		
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollBack();
		}
	}
    
    public function insightDataParser($insightData) {
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
							Zend_Debug::dump($value->value->{'page post'});
							$result['page_post'] = empty($value->value->{'page post'}) ? 0 : $value->value->{'page post'};
							$result['new_fan'] =  empty($value->value->fan) ? 0 : $value->value->fan;
							$result['user_post'] = empty($value->value->{'user post'}) ? 0 : $value->value->{'user post'};
						}
						$counter++;
						break;
				}
			}
			
			if($counter < 1) break;
		}
		return $result;
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
    
    public function test28Action() {
		//$fanpageId = '178384541065';
    	//$accessToken = 'AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm';

    	$fanpageId = '216821905014540';
    	$accessToken = 'AAAFHFbxmJmgBAIC75ZAo1l3zZB0e7ZAJM1CuZAPZA8jZAegeabToX13hDhje3czBe3LYFXvNQxcByREt6RwrposGq6J8mOoYDT935pDevkalt2bZCRK5Qno';
    	 
    	$feed = array();
    	$testFeedId = 'test_feed';
    	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
    	try {
    		$cache = Zend_Registry::get('memcache');
    		$cache->remove($testFeedId);

    		if(isset($cache) && !$cache->load($testFeedId)){
    			//Look up the facebook graph api
    			echo 'look up facebook graph api';

    			$feed = $collector->getFanpageFeed('2+days+ago', 'now');
    			if($feed) {
    				//Save to the cache, so we don't have to look it up next time
    				//$cache->save($feed, $testFeedId);
    				$cache->save($feed, $testFeedId, array(), 7200);
    			}
    		}else {
    			echo 'memcache look up';
    			$feed = $cache->load($testFeedId);
    		}
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	
		Zend_Debug::dump($feed);
		Zend_Debug::dump($collector->getActiveFansFromFeed($feed)); exit();
		
		$fancrankDB = new Service_FancrankDBService($fanpageId, $accessToken);
		foreach ($feed as $post) {
			// save post
    		if (!$fancrankDB->savePost($post)) {
    			echo 'error to save post';
	    		continue;
    		}
				
			// handle post comments
			if (! empty ( $post->comments->data )) {
				foreach ( $post->comments->data as $comment ) {
					// save comment
					$comment->comment_type = $post->type;
					if (! $fancrankDB->saveComment ( $comment, $post )) {
						continue;
					}
					
					// save comment like
					if (! empty ( $comment->like_list )) {
						foreach ( $comment->like_list as $like ) {
							$fancrankDB->saveCommentLike ( $like, $comment );
						}
					}
				}
			}
			
			// handle post likes
			if (! empty ( $post->likes->data )) {
				foreach ( $post->likes->data as $like ) {
					$fancrankDB->savePostLike ( $like, $post );
				}
			}
    	}
    }
    
    public function test29Action() {
        $fanpageId = '216821905014540';
    	$accessToken = 'AAAFHFbxmJmgBAPUVD7kjQIquRVpaDPJ8TKUPMXqUSD0BuP7F9KhsXtC1uEnWe0eaVTPebNprupHZC4fhNZA0ZAYTQoAjnNM0lG7ZBWQApc3Ttfphz7Dg';
    	 
    	$insightData = array();
    	$testInsighdId = 'full_insight';
    	$insightCollector = new Service_InsightCollectorService(null, $fanpageId, $accessToken, 'insight');
    	
    	
    	try {
    		$cache = Zend_Registry::get('memcache');
    		//$insightData = $insightCollector->getFullInsightData();
    		if(isset($cache) && !$cache->load($testInsighdId)){
    			//Look up the facebook graph api
    			echo 'look up facebook graph api';

    			if($insightData) {
    				//Save to the cache, so we don't have to look it up next time
    				//$cache->save($insightData, $testInsighdId, array(), 7200);
    			}
    		}else {
    			echo 'memcache look up';
    			$insightData = $cache->load($testInsighdId);
    		}
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	$insightData = $insightCollector->logInsight($insightData);
    	Zend_Debug::dump($insightData);
    }
    
    public function test30Action() {
    	$fanpageId = '197221680326345';
    	$rss = new Service_FancrankRssService();
    	$rss->readPageRssFeed($fanpageId);
    	$rss->formatRssFeed('array');
    }
}