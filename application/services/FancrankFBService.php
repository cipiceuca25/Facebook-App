<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Service_FancrankFBService extends Facebook {
	protected $_facebookGraphAPIUrl;
	protected $_appId;
	protected $_appSecret;
	
	public function __construct($config = false) {
		$this->_facebookGraphAPIUrl = 'https://graph.facebook.com/';
		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
		$newConfig = $sources->get('facebook');
		$this->_appId = $newConfig->client_id;
		$this->_appSecret = $newConfig->client_secret;
		if($config) {
			if(is_array($config)) {
				parent::__construct($config);
				return;
			}
			$newConfig = array(
					'appId'  => $this->_appId,
					'secret' => $this->_appSecret,
					'cookie' => true,
			);
			parent::__construct($newConfig);
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
		$data = $this->getSignedData();
		return isset($data['user_id']) ? $data['user_id'] : false;		
	}
	
	function checkFanPageAdmin()
	{
		$data = $this->getSignedData();
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
	
	public function getExtendedAccessToken($access_token) {
		try {
			$params = array(
							'client_id' => $this->_appId,
							'client_secret' => $this->_appSecret,
							'grant_type'=>'fb_exchange_token',
							'fb_exchange_token'=>$access_token
						);

			$token_url = "https://graph.facebook.com/oauth/access_token?" .http_build_query($params);
			//echo $token_url;
						
			$client = new Zend_Http_Client;
			$client->setUri($token_url);
			$client->setMethod(Zend_Http_Client::GET);
			
			$response = $client->request();
			$responseBody = $response->getBody();
			//Zend_Debug::dump($responseBody);
			
			if(is_string($responseBody) && preg_match('/^access_token=/i', $responseBody)) {
				parse_str($responseBody, $result);
				
				return $result['access_token'];
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
			return;
		}
	}
	
	public function getAppAccessToken() {
		$token_url = "https://graph.facebook.com/oauth/access_token?" .
				"client_id=" . $this->_appId .
				"&client_secret=" . $this->_appSecret .
				"&grant_type=client_credentials";
	
		//$response = file_get_contents($token_url);
		$client = new Zend_Http_Client;
		$client->setUri($token_url);
		$client->setMethod(Zend_Http_Client::GET);
		try {
			$response = $client->request();
			$result = $response->getBody();
			if(is_string($result) && preg_match('/^access_token=/i', $result)) {
				$token = ltrim($result, 'access_token=');
				return $token;
			}
		}catch (Exception $e) {
			echo $e->getMessage();
			return null;
		}
	}
	
	public function getAppId() {
		return $this->_appId;
	}
	
	public function isUserInstalledApp($userId) {
		if(empty($userId)) {
			return false;
		}
		
		$app_access_token = $this->getAppAccessToken();
		
		$params = array(
				'fields'=>'installed',
				'access_token'=>$app_access_token
		);
		
		$url = "https://graph.facebook.com/$userId?" .http_build_query($params);
		
		$client = new Zend_Http_Client;
		$client->setUri($url);
		$client->setMethod(Zend_Http_Client::GET);
		try {
			$response = $client->request();
			$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
			if(!empty($result->id) && $userId === $result->id && !empty($result->installed)) {
				return true;
			}
		}catch (Exception $e) {
			echo $e->getMessage();
		}
		return false;
	}
}

?>