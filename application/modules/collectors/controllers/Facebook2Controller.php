<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Collectors_Facebook1Controller extends Fancrank_Collectors_Controller_BaseController
{
    private $types = array(
        'fans'  => 'fans',
        'feed' => 'feed',
	'comments' => 'comments',
	'likes' => 'likes',
        'albums' => 'photo',
        'rankings'  => 'rankings'
    );

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

    public function batchfetchAction() {
    	$start = time();
    	$collectorLogger = Zend_Registry::get('collectorLogger');
    	//$collectorLogger->log('test log', Zend_Log::ERR);
    	   
   		echo 'This request will fetch client profile, 50 likes, 50 posts and 50 comments within each post';
    	$url = 'https://graph.facebook.com/';
    	$requestUrl = $this->getRequest()->getHttpHost() .$this->getRequest()->getRequestUri();
    	//$friends = array('paramName'=>'friends', 'method'=>'GET');
    	//$fanpage = array('paramName'=>'fanpage', 'method'=>'GET', 'fanpage_id'=> $this->getRequest()->getParam('fanpage_id'));
    	//$queryType = array('me', 'albums', 'friends', 'likes', 'notes', 'photos', 'posts', 'videos');
    	//$queryType = array('me', 'albums', $friends, 'likes', 'notes', 'photos', 'posts', 'videos');
    	$queryType = array('me', 'albums', 'feed', 'posts', 'photos');
    	$access_token = $this->getRequest()->getParam('access_token');

    	$batchQueries = $this->batchFanpageQueryBuilder($this->getRequest()->getParam('fanpage_id'), $queryType, $access_token);
    	//$batchQueries = $this->batchQueryBuilder($queryType, $access_token);
    	//Zend_Debug::dump($batchQueries); exit();
		try {
				$result = $this->requestFacebookAPI_POST($url, $batchQueries);
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
  
		
		if ($result === FALSE)
		{
			// Log or Redirect to error page
			$collectorLogger->log('Connection Error: ' .$requestUrl, Zend_Log::ERR);
			return;
		}
		else
		{
			try {
				$response = json_decode($result);
				//Zend_Debug::dump($response); exit();
				if(!empty($response->error) && !empty($response->error->message)) {
					$msg = sprintf("url: %s \n Error Message: %s \t Error Type: %s \t Erorr Code: %s", $requestUrl, $response->error->message, $response->error->type, $response->error->code);
					$collectorLogger->log($msg, Zend_Log::ERR);
					return;
				}
			} catch (Exception $e) {
				$collectorLogger->log($e->getMessage(), Zend_Log::ERR);
				return;
			}
				
			//exit();
			$response = json_decode($result,true);

			//Zend_Debug::dump($response); exit();
			//$typeName = array('me', 'fanpage', 'albums', 'friends');
			$data = $this->responseParser($response, $queryType);
			//Zend_Debug::dump($data); exit();
			//terminate the collector if error found					
			if(empty($data)) {
				return;
			}
			
			echo '<p>Who is ' .$this->getRequest()->getParam('fanpage_id') .' </p>';
			//Zend_Debug::dump(json_decode($data['me'])); exit();

			$fancrankDb = new Service_FancrankDBService();
			
			if($fancrankDb->saveFanpageProfile($url, $data, $access_token) === false) {
				//$cLog->log('fail to save', Zend_Log::DEBUG);
				//throw new Exception('cant save');
				
			}
			
			$timeTaken = time() - $start;
				
			echo "Total execution time: " .$timeTaken;
			exit();
			//echo facebook user Albums

			$posts = json_decode($data['posts'], true);
			//Zend_Debug::dump($posts);
			//exit();
			$commentArray = array();
			if(!empty($posts['daya'])) {
				foreach($posts['data'] as $post) {
					$commentArray[] = array('method' => 'GET', 'relative_url' => $post['id'] .'?limit='.$commentLimit);
				}
			}
			
			//Zend_Debug::dump(json_encode($commentArray));
			$commentQueries = json_encode($commentArray);
			
			$arrpost = 'batch=' .$commentQueries .'&access_token=' .$access_token;
			//Zend_Debug::dump($arrpost);
			//exit();
			
			$result = $this->requestFacebookAPI_POST($url, $arrpost);
			
			if ($result === FALSE)
			{
				// Redirect to error page
			}else {
				//Zend_Debug::dump(json_decode($result, true));
				$posts = json_decode($result, true);
				foreach ($posts as $data) {
					$post = json_decode($data['body'], true);
					echo '<p>' .$post['id'] .' post: </p>';
					
					Zend_Debug::dump($post);
				}
				$timeTaken = time() - $start;
				
				echo "Total execution time: " .$timeTaken;
			}
				
		}
    }
    

    private function requestFacebookAPI_GET($url, $arpost) {

    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url . "?" . $arpost);
    	curl_setopt($ch, CURLOPT_POST, false);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    	$result = curl_exec($ch);
    	curl_close($ch);
    
    	return $result;
    
    }
    

    private function requestFacebookAPI_POST($url, $arpost) {
    
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $arpost);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    	$result = curl_exec($ch);
    
    	curl_close($ch);
    
    	return $result;
    
    }
    
    /*
     * @return retrun an array of json objects 
     */
    private function responseParser($response, $queryType = array()) {
    	$arr = array();
    	foreach ($response as $key => $res){
			if($res['code'] === 200 && !empty($res['body'])) {
				$arr["$queryType[$key]"] = $res['body'];
			}
    	}
    	
    	return $arr;
    }
    
    //$data is array of json objects
    private function idCollector($data) {
    	$idArray = array();
    	foreach($data as $k => $v){
    		$result = $this->searchByKey('id', json_decode($v, true));
    		$idArray = array_merge($idArray, $result);
    	}
    	
    	return $idArray;
    }

    private function search_key_r($array, $key, &$results)
    {
    	if (!is_array($array))
    		return;
 
    	if (array_key_exists($key, $array))
    		$results[] = $array[$key];
    
    	foreach ($array as $subarray)
    		$this->search_key_r($subarray, $key, $results);
    }
    
    private function searchByKey($key, $array)
    {
    	$results = array();
    
    	$this->search_key_r($array, $key, $results);
    
    	return $results;
    }
    
    /*
     * A function to encode a batch query in a single url
    *
    * @param $fanpageId fanpage id
    * @param $arr params after usesr id in url. Example: https://graph.facebook.com/userId/param
    * @param $access_token an access token
    * @param $defaultOption an array of additional default params
    */
    private function batchFanpageQueryBuilder($fanageId, $arr, $access_token, $defaultOption=array()) {
    	if(empty($access_token)) {
    		return array();
    	}
    	$method = 'GET';
    	$limit = 0;
    	$since = null;
    	$until = null;
    	$result = array();
    	foreach ($arr as $key => $value) {
    		if(is_array($value) && !empty($value)) {
    			$tmp = array('method'=>'GET', 'relative_url'=> '/' .$fanageId. '?');
    			foreach ($value as $k => $v) {
    				switch ($k){
    					case 'paramName':
    						if( $v === 'me' ) {
    							$tmp['relative_url'] = '/me?';
    						}else {
    							$tmp['relative_url'] = '/' .$fanageId .'/' .$v .'?';
    						}
    						break;
    					case 'method': $tmp['method'] = $v; break;
    					case 'relative_url': $tmp['relative_url'] = $v; break;
    					case 'limit': $tmp['relative_url'] .= '&' .'limit=' .$v; break;
    					case 'since': $tmp['relative_url'] .= '&' .'since=' .$v; break;
    					case 'until': $tmp['relative_url'] .= '&' .'until=' .$v; break;
    					default: break;
    				}
    			}
    			$result[] = $tmp;
    		}else if(is_string($value)) {
    			if( $value === 'me') {
    				$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId);
    			}else {
    				$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId .'/' .$value);
    			}
    		}
    	}
    	 
    	//$arrpost = 'batch=' .json_encode($result) .'&access_token=' .$access_token;
    	return 'batch=' .json_encode($result) .'&access_token=' .$access_token;
    }
    
    public function appinfoAction() {
    	echo 'app info start: ';
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$config = array(
    			'appId'  => '376385369079791',
    			'secret' => '21f20fe69b53f7aebd13fee7488da3a7',
    			'cookie' => true,
    	);
    	$facebook = new Service_FancrankFBService($config);
    	$facebook->setAccessToken($this->_getParam('access_token'));
    	$user_profile = $facebook->api('/me');

    	Zend_Debug::dump($user_profile);
    		try {
    			/*
   				$facebook->api('/me/feed','POST',
   							array(
   								'message' => 'This is a test message from' .$user,
   								'link' => 'http://www.fancrank.local/app/app/post'
   							)S
   						);
   				*/
    			$uid = $user_profile['id'];

    			$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id FROM page_admin WHERE uid = '.$uid.''));
				Zend_Debug::dump($pages);
				echo 'end';
       			}catch(FacebookApiException $e) {
    				$result = $e->getResult();
    				Zend_Debug::dump($result);
    				error_log(json_encode($result));
    				$user = null;
    			}
    }
    
    public function getpagesAction() {
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$uid = $this->_getParam('id');
    	$facebook = new Service_FancrankFBService();
    	$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id FROM page_admin WHERE uid = '.$uid.''));
    	foreach($pages as $k=>$v) {
    		echo 'page id#:'.$v['page_id'].'<br/>';
    	}
    }
    
    public function testrunAction() {
    	$params=array('fanpage_id'=>'178384541065', 'access_token'=>'AAAFWUgw4ZCZB8BAO8ZCgMOINWwydm4xmEdqrN0ukBW2zJWi6JrNtG1d8iyADBEEBz6TZA36K4QTbaIAHQPZANFIQYbgAce88RwZATuV1M4swZDZD', 'limit'=>5);
    	//$result = $this->requestFacebookAPI_GET('http://www.fancrank.local/collectors/facebook1/batchfetch', http_build_query($params));
    	//Zend_Debug::dump($result);
    	//$this->httpCurl('http://www.fancrank.local/collectors/facebook1/batchfetch', $params, 'post');
		$result = Collector::run('http://www.fancrank.local/collectors/facebook1/batchfetch', $params, 'get');
		Zend_Debug::dump($result);
    }
    
    public function addtabAction() {
    	//install the tab
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$facebook = new Service_FancrankFBService();
    	$fanpage_id = '283104988451477';
    	$access_token = 'AAAFHFbxmJmgBACY4evXMsLuuKyilosy0ZArHAQXJZA27VnBVR2uDX6RXLnYuZAOsUn7hUwrVrB4l2QLSJMlAzwLhOjUyOklRfCg0TDfQwBssk8VqzQU';
    	/*
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs');
    	$client->setMethod(Zend_Http_Client::DELETE);
    	$client->setParameterPost('access_token', $access_token);
    	$client->setParameterPost('app_id', '359633657407080');
    	
    	$response = $client->request();
    	*/
    	try {
    		$result = $facebook->api("/283104988451477/tabs/app_376385369079791","delete", array('custom_name' => 'fancrank', 'access_token' => $access_token,  'app_id' => '376385369079791') );
    		Zend_Debug::dump($result);
    	} catch (Exception $e) {
    		$result = $e->getResult();
    		Zend_Debug::dump($result);
    	}
    }
    
    private function httpCurl($url, $params, $method) {
    	$ch = curl_init();
    	switch (strtolower($method)) {
    		case 'get':
    			curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
    			curl_setopt($ch, CURLOPT_POST, false);
    			break;
    		case 'post':
    			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    			curl_setopt($ch, CURLOPT_POST, true);
    			break;
    		default:
    			return;
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
