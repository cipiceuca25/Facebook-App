<?php
/**
 * An utility class holds some common help functions
 * 
 */
class Fancrank_Util_Util
{
	/**
	 * Executes a program and capture the result into array
	 */
	public static function execute($cmd,$stdin=null){
		$proc=proc_open($cmd,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		fwrite($pipes[0],$stdin);
		fclose($pipes[0]);
		$stdout=stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$stderr=stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$return=proc_close($proc);
		return array( 'stdout'=>$stdout, 'stderr'=>$stderr, 'return'=>$return );
	}
	
	/*
	 * A function to encode a batch query in a single url
	*
	* @param $objectId fanpage id
	* @param $arr params after usesr id in url. Example: https://graph.facebook.com/userId/param
	* @param $access_token an access token
	* @param $defaultOption an array of additional default params
	*/
	public static function batchQueryBuilder($objectId, $arr, $access_token, $defaultOption=array()) {
		if(empty($access_token)) {
			return array();
		}
		$method = 'GET';
		$limit = 0;
		$since = null;
		$until = null;
		$result = array();
		if(is_string($objectId)) {
			foreach ($arr as $key => $value) {
				if(is_array($value) && !empty($value)) {
					$tmp = array('method'=>'GET', 'relative_url'=> '/' .$objectId. '?');
					foreach ($value as $k => $v) {
						switch ($k){
							case 'paramName':
								if( $v === 'me' ) {
									$tmp['relative_url'] = '/me?';
								}else {
									$tmp['relative_url'] = '/' .$objectId .'/' .$v .'?';
								}
								break;
							case 'method': $tmp['method'] = $v; break;
							case 'relative_url': $tmp['relative_url'] = $v; break;
							case 'limit': $tmp['relative_url'] .= '&' .'limit=' .$v; break;
							case 'offset': $tmp['relative_url'] .= '&' .'offset=' .$v; break;
							case 'since': $tmp['relative_url'] .= '&' .'since=' .$v; break;
							case 'until': $tmp['relative_url'] .= '&' .'until=' .$v; break;
							default: break;
						}
					}
					$result[] = $tmp;
				}else if(is_string($value)) {
					if( $value === 'me') {
						$result[] = array('method' => 'GET', 'relative_url' => '/' .$objectId);
					}else {
						$result[] = array('method' => 'GET', 'relative_url' => '/' .$objectId .'/' .$value);
					}
				}
			}
		}else {
			foreach ($objectId as $k => $objectId) {
				foreach ($arr as $key => $value) {
					if(is_array($value) && !empty($value)) {
						$tmp = array('method'=>'GET', 'relative_url'=> '/' .$objectId. '?');
						foreach ($value as $k => $v) {
							switch ($k){
								case 'paramName':
									if( $v === 'me' ) {
										$tmp['relative_url'] = '/me?';
									}else {
										$tmp['relative_url'] = '/' .$objectId .'/' .$v .'?';
									}
									break;
								case 'method': $tmp['method'] = $v; break;
								case 'relative_url': $tmp['relative_url'] = $v; break;
								case 'limit': $tmp['relative_url'] .= '&' .'limit=' .$v; break;
								case 'offset': $tmp['relative_url'] .= '&' .'offset=' .$v; break;
								case 'since': $tmp['relative_url'] .= '&' .'since=' .$v; break;
								case 'until': $tmp['relative_url'] .= '&' .'until=' .$v; break;
								default: break;
							}
						}
						$result[] = $tmp;
					}else if(is_string($value)) {
						if( $value === 'me') {
							$result[] = array('method' => 'GET', 'relative_url' => '/' .$objectId);
						}else {
							$result[] = array('method' => 'GET', 'relative_url' => '/' .$objectId .'/' .$value);
						}
					}
				}
			}
		}
	
		//$arrpost = 'batch=' .json_encode($result) .'&access_token=' .$access_token;
		return 'batch=' .json_encode($result) .'&access_token=' .$access_token;
	}
	
	public static function requestFacebookAPI_GET($url, $arpost) {
	
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
	
	
	public static function requestFacebookAPI_POST($url, $arpost) {
	
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
	public static function responseParser($response, $queryType = array()) {
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
	
	/*
	 * @param $date a date string
	 * @return a string representation of a giving date in following format: yyyy-MM-dd HH:mm:ss 
	 */
	public static function dateToStringForMysql($date) {
		if(!empty ($date)) {
			$date = new Zend_Date($date);
			return $date->toString('yyyy-MM-dd HH:mm:ss');
		}
		return null;
	}
	
}
?>