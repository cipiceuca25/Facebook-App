<?php
class Collector
{
	
    public static function run($url, $action, $params)
    {
		$result = $this->httpCurl($url .'/' .$action, $params, 'get');
		Zend_Debug::dump($result);
    	/*
        // ensure the proper environment is set
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);
		
        //under linux env
        $cmd = sprintf('php %s/index.php -m collectors -c %s -a %s %s > /dev/null 2>/dev/null &', PUBLIC_PATH, $controller, $action, join(' ', $params));
        //under windows env
        //$cmd = sprintf('php %s/index.php -m collectors -c %s -a %s %s >NUL 2>NUL &', PUBLIC_PATH, $controller, $action, join(' ', $params));
        //log all the execution commands
        //Log::Info('Execute Action: "%s"\n%s', $cmd);
        $output = shell_exec($cmd);
        */
    }

    public static function queue($timeout_str, $controller, $action, $params)
    {
        $timeout = strtotime($timeout_str);

        if ($timeout - time() == 0) {
            return $this->run($controller, $action, $params);
        }

        $message = array(
            'module' => 'collectors',
            'controller' => $controller,
            'action' => $action,
            'params' => $params
        );

        $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Config')->get('queue');
        $adapter = new Fancrank_Queue_Adapter($options);
        $queue = new Fancrank_Queue($adapter, $options);

        $queue->send($message, $timeout);

        Log::Info('new job sceduled after %s', $timeout_str);
    }
    
    private function httpCurl($url, $params, $method) {
    	$ch = curl_init();
    	switch (strtolower($method)) {
    		case 'get':
		    	curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
		    	curl_setopt($ch, CURLOPT_POST, false);
    			break;
    		case 'post':
    			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
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
