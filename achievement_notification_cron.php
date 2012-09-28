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

sendNotification();

function sendNotification() {
	//get fanapp access_token
	$fb = new Service_FancrankFBService();
	$appAccessToken = $fb->getAppAccessToken();
	if(empty($appAccessToken)) {
		exit(0);
	}
	
	// init default db adapter
	$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
	$adapter = Zend_Db::factory($config->resources->db);
	Zend_Db_Table::setDefaultAdapter($adapter);
	$since = new Zend_Date();
	$sql = "SELECT e.id as event_id, e.fanpage_id, e.facebook_user_id, e.badge_id, e.created_time, b.name, b.description, b.weight, b.picture  FROM badge_events e inner join badges b on(b.id=e.badge_id) where e.created_time >= '" .$since->toString ( 'yyyy-MM-dd' ) ."' order by facebook_user_id";
	$badgeEventsList = $adapter->query($sql)->fetchAll();
	Zend_Debug::dump($badgeEventsList); exit();
	
// 	$badgeEventModel = new Model_BadgeEvents();
// 	$badgeModel = new Model_Badges();
// 	$badgeList = $badgeModel->findAll()->toArray(); 
// 	Zend_Debug::dump($badgeList); exit();
	
	if(!empty($badgeEventsList)) {
		
		$logger = new Zend_Log();
		$writer = new Zend_Log_Writer_Stream('./achievement_cron_error.log');
		$logger = new Zend_Log($writer);
		try {
			foreach ($badgeEventsList as $badgeEvent) {
				$facebook_user_id = $badgeEvent['facebook_user_id'];
				Zend_Debug::dump($badgeEvent);
				sendMessage($facebook_user_id, $badgeEvent['name'] .' ' .$badgeEvent['created_time'], $appAccessToken);
			}			
		} catch (Exception $e) {
			
		}
	}
}

function sendMessage($facebook_user_id, $message, $appAccessToken) {
	$client = new Zend_Http_Client;
	$client->setUri("https://graph.facebook.com/". $facebook_user_id . "/feed");
	$client->setMethod(Zend_Http_Client::GET);
	
	$client->setParameterGet('access_token', $appAccessToken);
	$client->setParameterGet('message', $message);
	$client->setParameterGet('method', 'post');
	 
	
	$response = $client->request();
	 
	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
	 
	if(!empty ($result)) {
		Zend_Debug::dump($result);		 
	}
}
