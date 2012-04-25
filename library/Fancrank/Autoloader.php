<?php
class Fancrank_Autoloader implements Zend_Loader_Autoloader_Interface
{
    public function autoload($class)
    {
        if (!in_array($class, array('Log', 'Email', 'Collector'))) {
            return false;
        }

        require_once $class . '.php';
        return $class;
    }
}
