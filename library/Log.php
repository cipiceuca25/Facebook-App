<?php
class Log
{
    public static function __callStatic($name, $arguments)
    {
        $text = urldecode(array_shift($arguments));
        $message = @vsprintf($text, $arguments);

        $logger = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Logger');

        call_user_func(array($logger, $name), $message);
    }
}
