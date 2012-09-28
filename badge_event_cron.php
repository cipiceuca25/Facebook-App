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

	// init default db adapter
	$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
	$adapter = Zend_Db::factory($config->resources->db);
	Zend_Db_Table::setDefaultAdapter($adapter);
	
	try {
		$eventConfiguration = new Zend_Config_Xml(APPLICATION_PATH.'/configs/badge_events.xml', null, array('allowModifications' => true, 'ignoreExtends' => true));
		
// 		Zend_Debug::dump($eventConfiguration);
// 		$eventConfiguration->interval = 30;
// 		$writer = new Zend_Config_Writer_Xml();
// 		$writer->write(APPLICATION_PATH.'/configs/badge_events.xml', $eventConfiguration);
		
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
	$since = new Zend_Date();

	$activityModel = new Model_FancrankActivities();
	
	$currentTime = time();
	$interval = strtotime($eventConfiguration->interval .$eventConfiguration->time_unit) - $currentTime;
	$newTime = $currentTime - $interval;
	$since = new Zend_Date($newTime, Zend_Date::TIMESTAMP);
	$activityList = $activityModel->getAllActivitiesSince($since->toString('yyyy-MM-dd HH:mm:ss'));
	 
// 	$badgeEventModel = new Model_BadgeEvents();
// 	$badgeModel = new Model_Badges();
// 	$badgeList = $badgeModel->findAll()->toArray(); 
// 	Zend_Debug::dump($badgeList); exit();
	
	if(!empty($activityList)) {
		
		$logger = new Zend_Log();
		$writer = new Zend_Log_Writer_Stream('./achievement_cron_error.log');
		$logger = new Zend_Log($writer);
		
		$badgeEventModel = new Model_BadgeEvents();
		
		try {
			foreach ($activityList as $activity) {
				Zend_Debug::dump($activity->toArray());
				//default badge check
				
				
				// custom badge check
				$fanpageBadgeModel = new Model_FanpageBadges();
				$fanpageBadgeList = $fanpageBadgeModel->findRemaindBadgeByUser($activity->fanpage_id, $activity->facebook_user_id);
				
				foreach ($fanpageBadgeList as $fanpageBadge) {
					Zend_Debug::dump($fanpageBadge);
					$badgeModel = Fancrank_BadgeFactory::factory('custom');
					if(! $badgeEventModel->hasBadgeEvent($activity->fanpage_id, $activity->facebook_user_id, $fanpageBadge['id']) && 
						$badgeModel->isFanEligible($activity->fanpage_id, $activity->facebook_user_id, $fanpageBadge['id'])) {
						$data = array(
									'fanpage_id'		=> $activity->fanpage_id,
									'facebook_user_id'	=> $activity->facebook_user_id,
									'badge_id'		 	=> $fanpageBadge['id']
								);
						$badgeEventModel->insert($data);
					}
				}			
			}			
		} catch (Exception $e) {
			
		}
	}
}
