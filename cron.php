#!/usr/bin/php -q
<?php
// Definitions
define('PS', PATH_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

// Define path to application public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)) . DS . 'public');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define path to data directory
defined('DATA_PATH')
    || define('DATA_PATH', APPLICATION_PATH . DS . 'data');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PS, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';

$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Fancrank_');

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

// init default db adapter
$adapter = Zend_Db::factory($config->resources->db);
Zend_Db_Table::setDefaultAdapter($adapter);

// init queue
$adapter = new Fancrank_Queue_Adapter($config->queue);
$queue = new Zend_Queue($adapter, $config->queue);

// Get up to 10 messages from a queue
$messages = $queue->receive(10, 0);

if (count($messages) > 0) {
    foreach ($messages as $message) {
        $job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);

        // ensure the proper environment is set
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);

        $cmd = sprintf('php %s/index.php -m %s -c %s -a %s %s > /dev/null 2>/dev/null &', PUBLIC_PATH, $job->module, $job->controller, $job->action, implode(' ', $job->params));

        shell_exec($cmd);

        // We have processed the message; now we remove it from the queue.
        $queue->deleteMessage($message);
    }
}
