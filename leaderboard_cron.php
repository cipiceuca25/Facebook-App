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

saveLeaderboard();

function saveLeaderboard() {
	
	$fanpageModel = new Model_Fanpages();
	$fanpageList = $fanpageModel->getActiveFanpagesIdList();
	
	$rankingModel = new Model_Rankings();
	
	$writer = new Zend_Log_Writer_Stream('./cron_error.log');
	$logger = new Zend_Log($writer);
	
	try {
		foreach ($fanpageList as $fanpageId) {
			//save top fans
			$result = $rankingModel->getTopFansByWeek($fanpageId, 5);
			if(!empty($result)) {
				insertLog($fanpageId, $result, 'top_fans', 'number_of_posts');
				// apply badge for top fan
			}
			
			//save top clicker
			$result = $rankingModel->getTopClickerByWeek($fanpageId, 5);
			if(!empty($result)) {
				insertLog($fanpageId, $result, 'top_clicker', 'number_of_likes');
			}
			
			//save top talker
			$result = $rankingModel->getTopTalkerByWeek($fanpageId, 5);
			if(!empty($result)) {
				insertLog($fanpageId, $result, 'top_talker', 'number_of_posts');
			}
			
			//save most popular
			$result = $rankingModel->getMostPopularByWeek($fanpageId, 5);
			if(!empty($result)) {
				insertLog($fanpageId, $result, 'most_popular', 'count');
			}
			
			//save top follower
			$result = $rankingModel->getTopFollowedByWeek($fanpageId, 5);
			if(!empty($result)) {
				insertLog($fanpageId, $result, 'top_follow', 'count');
			}
		}
		//
	} catch (Exception $e) {
		$logger->log('leaderboard cron Error: ' .$e->getMessage(), Zend_Log::INFO);
		//echo $e->getMessage();
	}
}

function insertLog($fanpageId, $result, $type, $countType) {
	$date = new Zend_Date();
	$today = $date->toString('yyyy-MM-dd 00:00:00');
	$lastSunday = $date->sub(7, Zend_Date::DAY)->toString('yyyy-MM-dd 00:00:00');
	
	$leaderboardLogModel = new Model_LeaderboardLog();
	$badgeEventModel = new Model_BadgeEvents();
	
	$rank = 0;
	foreach ($result as $key=>$row) {
		$rank++;
		$data = array (
				'type'=>$type,
				'facebook_user_id'=>$row['facebook_user_id'],
				'fanpage_id'=>$fanpageId,
				'start_time'=>$lastSunday,
				'end_time'=>$today,
				'rank'=>$rank,
				'count'=> empty($row[$countType]) ? 0 : $row[$countType]
		);
		//$leaderboardLogModel->insert($data);
		
		// hard code badge id for now, need to change it later on
		if ($type == 'top_fans') {
			applyBadge($badgeEventModel, $fanpageId, $row['facebook_user_id'], 721);
		}
		
	}
}

function applyBadge(Model_BadgeEvents $badgeEventModel, $fanpageId, $facebookUserId, $badgeId) {
	// apply badge
	// fetch badge id
	if ($badgeEventModel->hasBadgeEvent($fanpageId, $facebookUserId, $badgeId)) {
		return;
	}
	
	$badgeData = array(
			'fanpage_id'		=> $fanpageId,
			'facebook_user_id'	=> $facebookUserId,
			'badge_id'		 	=> $badgeId
	);
	
	try {
		$badgeEventModel->insert($badgeData);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}