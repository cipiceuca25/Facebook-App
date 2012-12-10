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
$date->subHour(72);

$option = array('name'=>'postqueue');
//$queue = new Zend_Queue('Array', $option);

$adapter = new Fancrank_Queue_Adapter($option);
$queue = new Fancrank_Queue($adapter, $option);

$logger = new Zend_Log();
//$writer = new Zend_Log_Writer_Stream('php://output');
$writer = new Zend_Log_Writer_Stream('./monitor_cron_error.log');
$logger = new Zend_Log($writer);

foreach($fanpageList as $fanpage) {

	if ($fanpage->installed) {
		$url = 'https://graph.facebook.com/' .$fanpage->fanpage_id .'/posts?access_token=' .$fanpage->access_token .'&since=' .$date->getTimestamp();// .$since;
		$client = new Zend_Http_Client;
		$client->setUri($url);
		$client->setMethod(Zend_Http_Client::GET);
		
		$response = $client->request();
		$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		
		if(!empty($result->error)) {
			$errMsg = sprintf('fan_id: %s', $fanpage->fanpage_id);
			$logger->log('Post Monitor Fetch Cron Failed: ' .$errMsg .' error msg: ' .$result->error->message, Zend_Log::INFO);
			continue;
		}
		
		if (empty($result->data)) continue;
		
		$postModel = new Model_Posts();
		foreach ($result->data as $post) {
			// save posts
			if( empty($post->id) || isset($post->story)) continue;
			$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
			$updated = new Zend_Date(!empty($post->updated_time) ? $post->updated_time : null, Zend_Date::ISO_8601);
			
			$row = array(
					'post_id'               => $post->id,
					'facebook_user_id'      => $post->from->id,
					'fanpage_id'            => $fanpage->fanpage_id,
					'post_message'          => isset($post->message) ? $postModel->getDefaultAdapter()->quote($post->message) : '',
					'picture'				=> !empty($post->picture) ? $post->picture : '',
					'link'					=> !empty($post->link) ? $post->link : '',
					'post_type'             => !empty($post->type) ? $post->type : '',
					'status_type'           => !empty($post->status_type) ? $post->status_type : '',
					'post_description'		=> !empty($post->description) ?  $postModel->getDefaultAdapter()->quote($post->description) : '',
					'post_caption'			=> !empty($post->caption) ? $this->quoteInto($post->caption) : '',
					'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
					'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
					'post_comments_count'   => !empty($post->comments->count) ? $post->comments->count : 0,
					'post_likes_count'      => isset($post->likes) && isset($post->likes->count) ? $post->likes->count : 0
			);
			
			if (property_exists($post, 'application') && isset($post->application->id)) {
				$row['post_application_id'] = $post->application->id;
				$row['post_application_name'] = empty($post->application->name) ? null : $post->application->name;
			} else {
				$row['post_application_id'] = null;
				$row['post_application_name'] = null;
			}

			try {
				//save fanpage's post's relative information into post table
				//Zend_Debug::dump($row);
				$postModel->saveAndUpdateById($row, array('id_field_name'=>'post_id'));
				
				// add post id into queue
				$message = array(
						'fanpage_id' => $fanpage->fanpage_id,
						'access_token' => $fanpage->access_token,
						'id' =>	$post->id,
						'type' => $post->type,
						'created_time' => $post->created_time
				);
				//Zend_Debug::dump($list);
				$queue->send($message, strtotime('55 minutes'));
				//$queue->send($message, strtotime('5 seconds'));
				
			} catch (Exception $e) {
				print $e->getMessage();
				$logger->log(sprintf('Post monitor unable to save post %s from fanpage %s to database. Error Message: %s ', $post->id, $fanpage->fanpage_id, $e->getMessage()), Zend_log::ERR);
			}
		}
	}
}

$stop = time() - $start;
echo '<br />total execution time: ' .$stop;
echo 'job done'.PHP_EOL;
exit();

