<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Service_FancrankFBService extends Facebook {
	protected $_facebookGraphAPIUrl;
	
	public function __construct($config = false) {
		$this->_facebookGraphAPIUrl = 'https://graph.facebook.com/';
		if($config) {
			if(is_array($config)) {
				parent::__construct($config);
				return;
			}
			$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
			$this->config = $sources->get('facebook');
			$config = array(
					'appId'  => $this->config->client_id,
					'secret' => $this->config->client_secret,
					'cookie' => true,
			);
			parent::__construct($config);
		}
	}

	/*
	 * Custom functions to get fan page related information
	 */
	function getSignedData()
	{
		if(isset($this->signedData) && !empty($this->signedData))
			return $this->signedData;
	
		if(!isset($_REQUEST["signed_request"]))
			return false;
	
		$signed_request = $_REQUEST["signed_request"];
	
		if(empty($signed_request))
			return false;
	
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);
		$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
	
		if(empty($data))
			return false;
	
		$this->signedData = $data;
	
		return $data;
	}
	
	function getFanPageId($signedRquest = false)
	{
		$data = null;
		if($signedRquest) {
			$data = $this->getSignedRequest($signedRquest);
		} else {
			$data = $this->getSignedData();
		}
		
		return isset($data['page']['id']) ? $data['page']['id'] : false;
	}
	
	function getFanPageUserId()
	{
		if(!$data = $this->getSignedData())	{
			return false;
		}
	
		return isset($data['user_id']) ? $data['user_id'] : false;		
	}
	
	function checkFanPageAdmin()
	{
		if(!$data = $this->getSignedData()) {
			return false;
		}
	
		if(isset($data['page']['admin']) && $data['page']['admin'] == 1) {
			return true;
		}
	
		return false;
	}
	
	function getFanpageIdByFQL() {
		return $this->api(array(
					'method' => 'fql.query',
					'query' => 'SELECT page_id FROM page_admin WHERE uid = ' .$this->getFanPageUserId().''
				));
	}
	
	function getSignedRequest($signed_request = false)
	{
	        if ($signed_request) {
            $sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
            $secret = $sources->get('facebook')->client_secret;

            list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

            // decode the data
            $sig = $this->base64_url_decode($encoded_sig);
            $data = json_decode($this->base64_url_decode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                return null;
            }

            // check sig
            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig) {
                return null;
            }
            
            return $data;

        } else {
            return null;
        }
	}
	
	private function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	public function collectFanpageInfo($fanpageId, $accessToken, $queryType = array('me' ,'feed', 'albums')) {
		$start = time();
		$collectorLogger = Zend_Registry::get('collectorLogger');
		//$collectorLogger->log('test log', Zend_Log::ERR);
		
		$batchQueries = $this->batchFanpageQueryBuilder($fanpageId, $queryType, $accessToken, array('limit'=>5));
		//$batchQueries = $this->batchQueryBuilder($queryType, $access_token);
		//Zend_Debug::dump($batchQueries); exit();
		try {
			$result = $this->requestFacebookAPI_POST($this->_facebookGraphAPIUrl, $batchQueries);
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
		
		
		if ($result === FALSE)
		{
			// Log or Redirect to error page
			$collectorLogger->log('Connection Error: ' .$fanpageId .' ' .$accessToken, Zend_Log::ERR);
			return;
		}
		else
		{
			try {
				$response = json_decode($result);
				//Zend_Debug::dump($response); exit();
				if(!empty($response->error) && !empty($response->error->message)) {
					$msg = sprintf("url: %s \n Error Message: %s \t Error Type: %s \t Erorr Code: %s", $fanpageId, $response->error->message, $response->error->type, $response->error->code);
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
				
			echo '<p>Who is ' .$fanpageId .' </p>';
			//Zend_Debug::dump(json_decode($data['me'])); exit();
		
			$fancrankDb = new Service_FancrankDBService();
				
			if($fancrankDb->saveFanpageProfile($this->_facebookGraphAPIUrl, $data, $accessToken) === false) {
				//$cLog->log('fail to save', Zend_Log::DEBUG);
				//throw new Exception('cant save');
		
			}
				
			$timeTaken = time() - $start;
		
			echo "Total execution time: " .$timeTaken;
			exit();
		}
	}
	
	public function getPostsRecursive($url, $fanpageId) {
		
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
		
		$extraDefaultParam = http_build_query($defaultOption);
		if($extraDefaultParam) {
			$extraDefaultParam = '?' .$extraDefaultParam;
		}
		
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
					$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId .$extraDefaultParam);
				}else {
					$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId .'/' .$value .$extraDefaultParam);
				}
			}
		}
	
		//$arrpost = 'batch=' .json_encode($result) .'&access_token=' .$access_token;
		return 'batch=' .json_encode($result) .'&access_token=' .$access_token;
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
}

?>