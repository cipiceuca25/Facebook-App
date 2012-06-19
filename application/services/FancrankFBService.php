<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Service_FancrankFBService extends Facebook {
	
	public function __construct($config = false) {
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
					'query' => 'SELECT page_id FROM page_admin WHERE uid = ' .getFanPageUserId().''
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
	
}

?>