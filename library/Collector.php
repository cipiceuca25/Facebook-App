<?php
class Collector
{
	
    public static function run($actionUrl, $method, $params)
    {
    	//Zend_Debug::dump($url .$action .'?' .http_build_query($params)); exit();
		$result = self::httpCurl($actionUrl, $method, $params);
		return $result;
    }

    public static function queue($timeout_str, $url, $type, $fanpageId)
    {
        $timeout = strtotime($timeout_str);

        if ($timeout - time() == 0) {
            return $this->run($url, 'get', array('limit'=>100));
        }

        $message = array(
            'url' => $url,
            'type' => $type,
            'fanpage_id' => $fanpageId,
        );

        $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Config')->get('queue');
        $adapter = new Fancrank_Queue_Adapter($options);
        $queue = new Fancrank_Queue($adapter, $options);

        $queue->send($message, $timeout);

        Log::Info('new job sceduled after %s', $timeout_str);
    }
    
    private static function httpCurl($url, $method, $params) {
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
