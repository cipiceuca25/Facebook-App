<?php
class Collector
{
    public static function run($url, $fanpageId, $facebookUserId, $accessToken, $type)
    {
    	$collector = new Service_FancrankCollectorService($url, $fanpageId, $accessToken, $type);
    	switch ($type) {
    		case 'init' :
    			$collector->collectFanpageInitInfo();
    			self::queue('5 second', $url, $fanpageId, $facebookUserId, $accessToken, 'full');
    			break;
    		case 'update' :
    			$collector->updateFanpage(null, null);
    			break;	
    		case 'full' :
    			$collector->fullScanFanpage(); 
    			//$collector->updateFanpageFeed('365+days+ago', 'now');
    			break;	
    		default:
    			break;		
    	}
    }

    public static function queue($timeout_str, $url, $fanpageId, $facebookUserId, $accessToken, $type)
    {
        $timeout = strtotime($timeout_str);

        if ($timeout - time() == 0) {
            return self::run($url, $fanpageId, $facebookUserId, $accessToken, $type);
        }

        $message = array(
            'url' => $url,
            'type' => $type,
            'fanpage_id' => $fanpageId,
        	'facebook_user_id' => $facebookUserId,	
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
