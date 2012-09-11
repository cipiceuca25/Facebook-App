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

$fanpageModel = new Model_Fanpages();
//$fanpageList = $fanpageModel->fetchAll();

$fanpageList = $fanpageModel->getActiveFanpages();

//Zend_Debug::dump(count($fanpageList)); exit();

if (count($fanpageList) > 0) {
	$logger = new Zend_Log();
	//$writer = new Zend_Log_Writer_Stream('php://output');
	$writer = new Zend_Log_Writer_Stream('./update_cron_error.log');
	$logger = new Zend_Log($writer);
	$dbLog = new Model_CronLog();
	
	$error = false;
	foreach ($fanpageList as $fanpage) {
		//if($fanpage->fanpage_id != '216821905014540') continue;
		$date = new Zend_Date(time(), Zend_Date::TIMESTAMP);
		$data = array(
				'fanpage_id'	=> $fanpage->fanpage_id,
				'access_token'	=> $fanpage->access_token,
				'url'			=> null,
				'type'			=> 'update',
				'start_time' 	=> $date->toString('YYYY-MM-dd HH:mm:ss')
		);
		
		//collect fanpage yesterday data
		try {
			$date = new Zend_Date(time(), Zend_Date::TIMESTAMP);
			if($fanpage->active) {
				echo $fanpage->fanpage_id .' ' .$fanpage->access_token .PHP_EOL;
				//update fanpage
				$collector = new Service_FancrankCollectorService(null, $fanpage->fanpage_id, $fanpage->access_token, 'update');
				$collector->updateFanpage('yesterday', 'now');
				
				$data['status'] = 'success';
				$data['end_time'] = $date->toString('YYYY-MM-dd HH:mm:ss');
				$dbLog->insert($data);
			}
		}catch(Exception $e) {
			$date = new Zend_Date(time(), Zend_Date::TIMESTAMP);
			$data['status'] = 'fail';
			$data['note'] = $e->getMessage();
			$data['end_time'] = $date->toString('YYYY-MM-dd HH:mm:ss');
			$dbLog->insert($data);
				
			$errMsg = sprintf('fanpage_id: %s <br/>access_token: %s<br/> type: update<br/>', $fanpage->fanpage_id, $fanpage->access_token);
			$logger->log('Update fanpage cron Failed: ' .$errMsg .'<br/>' .$e->getMessage(), Zend_Log::INFO);
			$error = true;
		}
		
		//update fanpage fans stat
		$fan = new Model_Fans();
		$fanList = $fan->fetchFansIdListByFanpageId($fanpage->fanpage_id);
		$fanStat = new Model_FansObjectsStats();
		
		foreach ($fanList as $fan) {
			try {
				//echo $fan->facebook_user_id .' ' .$fan->fanpage_id;
				$result = $fanStat->updatedFan($fanpage->fanpage_id, $fan['facebook_user_id']);
				//break;
			}catch(Exception $e) {
				$errMsg = sprintf('fan_id: %s %s <br/> type: update<br/>', $fan['facebook_user_id'], $fan['fanpage_id']);
				$logger->log('Update fanpage cron Failed: ' .$errMsg .' ' .$e->getMessage(), Zend_Log::INFO);
				$error = true;
			}
		}
		
	}
    
	if($error) {
		// send email with attachment
	}
    echo 'job done'.PHP_EOL;
    exit();
}

