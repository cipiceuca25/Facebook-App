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
			Zend_Debug::dump($result);
			try {
				$response = json_decode($result);
				if(!empty($response->error) && !empty($response->error->message)) {
					$msg = sprintf("url: %s \n Error Message: %s \t Error Type: %s \t Erorr Code: %s", $requestUrl, $response->error->message, $response->error->type, $response->error->code);
					$collectorLogger->log($msg, Zend_Log::ERR);
					return;
				}
			} catch (Exception $e) {
				$collectorLogger->log($e->getMessage(), Zend_Log::ERR);
				return;
			}
				
			exit();
			$response = json_decode($result,true);

			//Zend_Debug::dump($response); exit();
			//$typeName = array('me', 'fanpage', 'albums', 'friends');
			$data = $this->responseParser($response, $queryType);
			//Zend_Debug::dump($data);
			//Zend_Debug::dump($this->idCollector($data));
			
			echo '<p>Who is ' .$this->getRequest()->getParam('fanpage_id') .' </p>';
			//Zend_Debug::dump(json_decode($data['me']));

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
    	$tempKey = array();
    	foreach($queryType as $k => $value) {
    		if(is_array($value) && !empty($value['paramName'])) {
    			$tempKey[$k] = $value['paramName'];
    		}else {
    			$tempKey[$k] = $value;
    		}
    	}
    	
    	if(count($response) != count($tempKey)) {
    		throw new Exception('unmatch query type');
    	}
    	
    	$arr = array();
    	foreach ($response as $key => $res){
			if($res['code'] === 200 && !empty($res['body'])) {
				$arr["$tempKey[$key]"] = $res['body'];
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
    	$app_id = "359633657407080";
    	$app_secret = "438dd417f5a3f67f27dd97606d01e83c";
    	$facebook = new Facebook(array(
		      'appId'  => $app_id,
		      'secret' => $app_secret,
		      'cookie' => true,
    	));
    	$user = $facebook->getUser();
    	Zend_Debug::dump($user);
    	
    	if ($user) {
    		try {
    			// Proceed knowing you have a logged in user who's authenticated.
    			$user_profile = $facebook->api('/me/likes');
    			Zend_Debug::dump($user_profile);
    			$access_token = $facebook->getAccessToken();
    			Zend_Debug::dump($access_token);
    		} catch (FacebookApiException $e) {
    			//you should use error_log($e); instead of printing the info on browser
    			d($e);  // d is a debug function defined at the end of this file
    			$user = null;
    		}
    	}
    }
    
}
