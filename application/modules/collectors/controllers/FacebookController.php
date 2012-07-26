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

    public function extendAction() {
    	$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', 'production');
    	$this->config = $sources->get('facebook');
    	$token = 'AAAFWUgw4ZCZB8BAABe72sQZBMqLKI8uOGVf8akenwLjo7ZC0kjgIgQGS4ZCvj2spTKoOcSUSTTZBZBgwXxLljEZAqgDX7WalTYZAZCt7ZCMlW9BMQZDZD';
    	$token_url = "https://graph.facebook.com/oauth/access_token?client_id=".$this->config->client_id."&client_secret=".$this->config->client_secret."&grant_type=fb_exchange_token&fb_exchange_token=".$token;
    	
    	echo $token_url;
   	
    }
    
    public function test1Action() {
    	$access_token="AAAFHFbxmJmgBAJpg48MFFoOl6UNIWdqpAgHGDAyEc2oZC6zCFXP3LxjbCaIuP3fMasbIEGOyXgR3Sa6xr2pzyqWf5XuUZARBgOhTJ914iO57nzIlmm";
		$fb = new Service_FancrankFBService();
		$result = $fb->getExtendedAccessToken($access_token);
		echo 'new token =: ' .$result;
		$user_id = '100004098439774';
		

// 		$attachment =  array(
// 				'access_token' => $result,
// 				'message' => "$msg",
// 				'name' => "$name",
// 				'link' => "$link",
// 				'description' => "test post"
// 		);
		
// 		$fb->api('/'.$user_id.'/feed', 'POST', $attachment);
		
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
