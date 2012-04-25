<?php
class Collector
{
    public static function run($controller, $action, $params)
    {
        // ensure the proper environment is set
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);

        $cmd = sprintf('php %s/index.php -m collectors -c %s -a %s %s > /dev/null 2>/dev/null &', PUBLIC_PATH, $controller, $action, join(' ', $params));

        $output = shell_exec($cmd);
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
}
