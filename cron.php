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

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

// init default db adapter
$adapter = Zend_Db::factory($config->resources->db);
Zend_Db_Table::setDefaultAdapter($adapter);
$jobCount = $adapter->query("select count(*) as count from message")->fetchAll();

$adapter = new Fancrank_Queue_Adapter($config->queue);
$queue = new Zend_Queue($adapter, $config->queue);

//$messages = $queue->receive((int) $jobCount[0]['count'], 0);
$messages = $queue->receive(1);
//Zend_Debug::dump(count($messages));

$link = 'www.fancrank.local/admin';

if(APPLICATION_ENV === 'production') {
	$link = 'https://fancranapp.com/admin';
}

if (count($messages) > 0) {
	$logger = new Zend_Log();
	//$writer = new Zend_Log_Writer_Stream('php://output');
	$writer = new Zend_Log_Writer_Stream('./cron_error.log');
	$logger = new Zend_Log($writer);
	$dbLog = new Model_CronLog();
	
    foreach ($messages as $message) {
        $job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);
		Zend_Debug::dump($job);
		$data = array(
				'fanpage_id'	=> $job->fanpage_id,
				'facebook_user_id' => $job->facebook_user_id,
				'access_token'	=> $job->access_token,
				'url'			=> $job->url,
				'type'			=> $job->type,
				'start_time' 	=> (new Zend_Date(time(), Zend_Date::TIMESTAMP))->toString('YYYY-MM-dd HH:mm:ss')
		);
        try {
        	// We have processed the message; now we remove it from the queue.
        	$queue->deleteMessage($message);

        	Collector::run($job->url, $job->fanpage_id, $job->facebook_user_id, $job->access_token, $job->type);
        	$data['status'] = 'success';
        	$data['end_time'] =	(new Zend_Date(time(), Zend_Date::TIMESTAMP))->toString('YYYY-MM-dd HH:mm:ss');
        	$dbLog->insert($data);
        	
        	$userModel = new Model_FacebookUsers();
        	$adminUser = $userModel->findRow($job->facebook_user_id);
        	
        	$fmail = new Service_FancrankMailService($adminUser);
        	$okMsg = sprintf('Complete data collection on fanpage: %s <br/>
        					 please click following link to login and verify result <a target="_blank" href="%s">link</a>', $job->fanpage_id, $link);
        	
        	$fmail->sendOKMail($okMsg);
         }catch (Exception $e) {
        	try {
        		//remove error message from the queue
        		$queue->deleteMessage($message);
        		
        		//log err message into database
        		$data['status'] = 'fail';
        		$data['end_time'] =	(new Zend_Date(time(), Zend_Date::TIMESTAMP))->toString('YYYY-MM-dd HH:mm:ss');
        		$dbLog->insert($data);
        		
        		//send email notification
        		$fmail = new Service_FancrankMailService();
        		$errMsg = sprintf('Error on job: %s <br/>fanpage_id: %s <br/>access_token: %s<br/> type: %s<br/>', $job->url, $job->fanpage_id, $job->access_token, $job->type); 
        		$fmail->sendErrorMail($errMsg .'System message: ' .$e->getMessage());
        		
        		//log err into log file
        		$logger->log('Queue Failed: ' .$e->getMessage(), Zend_Log::INFO);
        	} catch (Exception $e) {
        		$logger->log('System Error: ' .$e->getMessage(), Zend_Log::INFO);
        		print $e->getMessage();
        		//return;
        	}
         }
    }
    
    echo 'job done'.PHP_EOL;
    exit();
}

