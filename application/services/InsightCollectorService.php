<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    service
 * @copyright   Copyright (c) 2012 Francrank
 * @license
 */
class Service_InsightCollectorService extends Service_FancrankCollectorService {

	public function getFullInsightData() {
		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/insights?access_token=' .$this->_accessToken .'&since=30+days+ago';
		$insights = array();
		$this->getFanpageInsights($url, 30, null, $insights);
		return $insights;
	}

	protected function getFanpageInsights($url, $level, $since = null, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		echo 'api call #' .$level;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n'; exit();
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			if(!empty($response->error)) throw new Exception($response->error->message);
			$url = !empty($response->paging->previous) ? $response->paging->previous : null;
			if (! empty($response->data)) {
				$result[] = $response->data;
				$this->getInsightsRecursive($url, $level, null, $result);
			} else {
				return array();
			}
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$msg = sprintf('Unable to fetch feed from fanpage %s. Error Message: %s ', $this->_fanpageId, $e->getMessage ());
			$collectorLogger->log($msg , Zend_log::ERR );
			throw new Exception($msg);
		}
	}
	
	public function logInsight($data, $overwrite=false) {
		$filePath = DATA_PATH .'/temp/' .$this->_fanpageId .'_last_insight.data';
	
		if (file_exists($filePath) && !$overwrite) {
			echo "The file $filePath exists";
			return unserialize( file_get_contents( $filePath ) );;
		} else {
			echo "The file $filePath not exists";
			file_put_contents( $filePath, serialize( $data ) );
		}
	}
}

?>