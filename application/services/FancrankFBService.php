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
	
	public function getExtendedAccessToken() {
		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$access_token_response =
			$this->_oauthRequest(
					$this->getUrl('graph', '/oauth/access_token'), array(
							'client_id' => $this->getAppId(),
							'client_secret' => $this->getAppSecret(),
							'grant_type'=>'fb_exchange_token',
							'fb_exchange_token'=>$this->getAccessToken()
					)
			);
		} catch (FacebookApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
	
		if (empty($access_token_response)) {
			return false;
		}
	
		$response_params = array();
		parse_str($access_token_response, $response_params);
		if (!isset($response_params['access_token'])) {
			return false;
		}
	
		return $response_params['access_token'];
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
			
		}
	}
}

?>