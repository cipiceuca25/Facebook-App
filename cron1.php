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
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PS, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
		APPLICATION_ENV,
		APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();

require_once 'Zend/Loader/Autoloader.php';

$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Fancrank_');

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

// init default db adapter
$adapter = Zend_Db::factory($config->resources->db);
Zend_Db_Table::setDefaultAdapter($adapter);
$jobCount = $adapter->query("select count(*) as count from message")->fetchAll();

$adapter = new Fancrank_Queue_Adapter($config->queue);
$queue = new Zend_Queue($adapter, $config->queue);

$messages = $queue->receive((int) $jobCount[0]['count'], 0);

if (count($messages) > 0) {
	$logger = new Zend_Log();
	//$writer = new Zend_Log_Writer_Stream('php://output');
	$writer = new Zend_Log_Writer_Stream('./cron_error.log');
	$logger = new Zend_Log($writer);    
    foreach ($messages as $message) {
        $job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);
        // ensure the proper environment is set
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);

        //linux env
        //$cmd = sprintf('php %s/index.php -m %s -c %s -a %s %s > /dev/null 2>/dev/null &', PUBLIC_PATH, $job->module, $job->controller, $job->action, implode(' ', $job->params));
        //windows env
        //$cmd = sprintf('php %s/index.php -m %s -c %s -a %s %s >NUL 2>NUL &', PUBLIC_PATH, $job->module, $job->controller, $job->action, implode(' ', $job->params));
        try {
        	Zend_Debug::dump($job);
        	//$queue->deleteMessage($message);
        }catch (Exception $e) {
        	try {
        		//remove error message from the queue
        		$queue->deleteMessage($message);
        		$logger->log('Queue Failed: ' .$e->getMessage(), Zend_Log::INFO);
        	} catch (Exception $e) {
        		//return;
        	}
         }
        
        // We have processed the message; now we remove it from the queue.
        //Zend_Debug::dump($message);
        //$queue->deleteMessage($message);
    }
}

