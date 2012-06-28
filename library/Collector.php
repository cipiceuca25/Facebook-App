<?php
class Collector
{
    public static function run($url, $fanpageId, $accessToken, $type)
    {
    	$collector = new Service_FancrankCollectorService($url, $fanpageId, $accessToken, $type);
    	switch ($type) {
    		case 'init' :
    			$collector->collectFanpageInitInfo();
    			self::queue('5 second', $url, $fanpageId, $accessToken, 'full');
    			break;
    		case 'fetch' :
    			$collector->fetchFanpageInfo();
    			break;	
    		case 'update' :
    			break;
    		case 'full' : 
    			$collector->fullScanFanpage();
    			break;	
    		default:
    			break;		
    	}
    }

    public static function queue($timeout_str, $url, $fanpageId, $accessToken, $type)
    {
        $timeout = strtotime($timeout_str);

        if ($timeout - time() == 0) {
            return self::run($url, $fanpageId, $accessToken, $type);
        }

        $message = array(
            'url' => $url,
            'type' => $type,
            'fanpage_id' => $fanpageId,
        	'access_token' => $accessToken,
        );

        $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Config')->get('queue');
        $adapter = new Fancrank_Queue_Adapter($options);
        $queue = new Fancrank_Queue($adapter, $options);

        $queue->send($message, $timeout);

        Log::Info('new job sceduled after %s', $timeout_str);
    }
    
    public static function removeQueue() {
    	
    }
}
