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
$start = time();
$fanpageModel = new Model_Fanpages();
$fanpageIdList = $fanpageModel->getInstallFanpagesIdList();

//Zend_Debug::dump($fanpageIdList); exit();

foreach($fanpageIdList as $id) {


	$fan = new Model_Fans();
	$fanList = $fan->fetchFansIdListByFanpageId($id);
	Zend_Debug::dump(count($fanList));
	
	if (count($fanList) > 0) {
		$logger = new Zend_Log();
		//$writer = new Zend_Log_Writer_Stream('php://output');
		$writer = new Zend_Log_Writer_Stream('./update_cron_error.log');
		$logger = new Zend_Log($writer);
	
		$error = false;
		$fanStat = new Model_FansObjectsStats();
	
		foreach ($fanList as $fan) {
			try {
				//echo $fan->facebook_user_id .' ' .$fan->fanpage_id;
					$result = $fanStat->updatedFan($fan['fanpage_id'], $fan['facebook_user_id']);
				//break;
			}catch(Exception $e) {
				$errMsg = sprintf('fan_id: %s %s <br/> type: update<br/>', $fan['facebook_user_id'], $fan['fanpage_id']);
				$logger->log('Update fanpage cron Failed: ' .$errMsg .' ' .$e->getMessage(), Zend_Log::INFO);
				$error = true;
			}
		}
	
		if($error) {
			// send email with attachment
			echo 'there is error';
			$logger->log('Update fanpage fans stats failed on: ' .$fan['fanpage_id'], Zend_Log::INFO);

		}
	}
}

$stop = time() - $start;
echo '<br />total execution time: ' .$stop;
echo 'job done'.PHP_EOL;
exit();

