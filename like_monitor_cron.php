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

$fanpageList = $fanpageModel->getActiveFanpages();

//Zend_Debug::dump($fanpageList->toArray()); exit();

$date = new Zend_Date();
$date->subHour(1);

$option = array('name'=>'postqueue');
//$queue = new Zend_Queue('Array', $option);

$adapter = new Fancrank_Queue_Adapter($option);
$queue = new Fancrank_Queue($adapter, $option);

$messages = $queue->receive(100);

$logger = new Zend_Log();
//$writer = new Zend_Log_Writer_Stream('php://output');
$writer = new Zend_Log_Writer_Stream('./monitor_cron_error.log');
$logger = new Zend_Log($writer);

foreach ($messages as $i => $message) {
	$queue->deleteMessage($message);
	$likesList = array();
	$job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);
	$url = 'https://graph.facebook.com/' .$job->id .'/likes?limit=500&access_token=' .$job->access_token;
	echo $url;

	$client = new Zend_Http_Client;
	$client->setUri($url);
	$client->setMethod(Zend_Http_Client::GET);

	$response = $client->request();
	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
	
	if(!empty($result->error) || empty($result->data)) continue;

	$time = new Zend_Date();
	$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
	
	$fansIdsList = array();
	$pointResult = array();
	foreach ($result->data as $data) {
		$time = new Zend_Date($job->created_time, Zend_Date::ISO_8601);
		$created_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		$like = array(
				'fanpage_id'        => $job->fanpage_id,
				'post_id'           => $job->id,
				'facebook_user_id'  => $data->id,
				'created_time'		=> $created_time,
				'updated_time'		=> $update_time,
				'post_type'			=> $job->type
		);
		$likeModel = new Model_Likes();
		$found = $likeModel->find($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])->current();
		try {
			if (empty($found)) {
				//$like['likes'] = 2;
				$likeModel->insert($like);
				
				// like point for admin post 
				if(isset($pointResult[$like['facebook_user_id']])) {
					$pointResult[$like['facebook_user_id']]['total_points'] = $pointResult[$like['facebook_user_id']]['total_points'] + 5;
				}else {
					$pointResult[$like['facebook_user_id']]['total_points'] = 5;
				}
				$pointResult[$like['facebook_user_id']]['xp'] = $pointResult[$like['facebook_user_id']]['total_points'];
				$pointResult[$like['facebook_user_id']]['point_log'][] = array(
						'object_id'=> $like['post_id'],
						'object_type'=> 'likes',
						'giving_points'=> 5,
						'bonus'=> 4,
						'note'=> 'likes on admin object'
				);
			}
			//
			$fansIdsList[] = $like['facebook_user_id'];
		} catch (Exception $e) {
			$logger->log ( sprintf ( 'Unable to save likes in post monitor cron: %s %s',  $e->getMessage (), implode(' ', $like)), Zend_log::ERR);
		}
		
	}
	
	$collector = new Service_FancrankCollectorService(null, $job->fanpage_id, $job->access_token, 'update_post');
	
	$fansIdsList = array_unique($fansIdsList);
	$facebookUsers = $collector->getFansList($fansIdsList, $job->access_token);

	$fdb = new Service_FancrankDBService($job->fanpage_id, $job->access_token);
	
	$db = $fdb->getDefaultAdapter();
	$db->beginTransaction();
	try {
		//Zend_Debug::dump($facebookUsers);
		$result = $fdb->saveAndUpdateFans($facebookUsers, $pointResult, true);
		echo 'finish....';	
		$db->commit();
	
	} catch (Exception $e) {
		$logger->log ( sprintf ( 'Post like Scan fail: %s',  $e->getMessage ()), Zend_log::ERR);
		$db->rollBack();
	}
	// We have processed the message; now we remove it from the queue.
}

$stop = time() - $start;
echo '<br />total execution time: ' .$stop .'seconds ';
echo 'job done'.PHP_EOL;
exit();

