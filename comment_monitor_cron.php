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

$option = array('name'=>'commentqueue');
//$queue = new Zend_Queue('Array', $option);

$adapter = new Fancrank_Queue_Adapter($option);
$queue = new Fancrank_Queue($adapter, $option);

$messages = $queue->receive(100);

$logger = new Zend_Log();
//$writer = new Zend_Log_Writer_Stream('php://output');
$writer = new Zend_Log_Writer_Stream('./monitor_cron_error.log');
$logger = new Zend_Log($writer);


$postModel = new Model_Posts();
$commentModel = new Model_Comments();
$db = Zend_Db_Table::getDefaultAdapter();

foreach ($messages as $i => $message) {
	//$queue->deleteMessage($message);
	$db->beginTransaction();
	try {
		$job = Zend_Json::decode($message->body, Zend_Json::TYPE_OBJECT);
		Zend_Debug::dump($job);

		// if comment exists in database, do next comment
		$comment = $commentModel->findRow($job->id);
		if ($comment) {	continue; }
		
		// search database post
		// retrieve fanpage setting
		$fanpageSettingModel = new Model_FanpageSetting();
		$fanpageSetting = $fanpageSettingModel->findRow($job->fanpage_id);
		if (!$fanpageSetting) {
			$fanpageSetting = $fanpageSettingModel->getDefaultSetting();
		} else {
			$fanpageSetting = $fanpageSetting->toArray();
		}
		
		if (count($postId = explode('_', $job->id)) > 2) {
			Zend_Debug::dump(count($postId));
			$postId = $postId[0] .'_' .$postId[1];
			$post = $postModel->findRow($postId);
			
			if ($post) {
				$client = new Zend_Http_Client;
				$client->setUri("https://graph.facebook.com/". $job->id);
				$client->setMethod(Zend_Http_Client::GET);
				$client->setParameterGet('access_token', $job->access_token);
				$response = $client->request();
				$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
	
				if (!empty($result->error)) {
					$type = isset($result->error->type) ? $result->error->type : '';
					$code = isset($result->error->code) ? $result->error->code : '';
					$message = isset($result->error->message) ? $result->error->message : '';
					$msg = sprintf('type: %s, $code: %s, message: %s', $type, $code, $message);
					throw new Exception($msg);
				}
					
				if ($job->facebook_user_id != $result->from->id) throw new Exception('wrong user');
				
				$created = new Zend_Date(!empty($result->created_time) ? $result->created_time : null, Zend_Date::ISO_8601);
				
				$row = array (
						'comment_id' => $result->id,
						'fanpage_id' => $job->fanpage_id,
						'comment_post_id' => $postId,
						'facebook_user_id' => $result->from->id,
						'comment_message' => $db->quote($result->message),
						'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
						'comment_likes_count' => isset ( $result->like_count ) ? $result->like_count : 0,
						'comment_type' => $post->post_type
				);
				
				$pointLogModel = new Model_PointLog();
				$bonus = 0;
				
				// if admin post, apply bonus to comment owner
				if ($post->facebook_user_id == $job->fanpage_id) {
					// update fan stat
					$fanstat = new Model_FansObjectsStats();
					switch ($post->post_type) {
						case 'status':
							$fanstat->addCommentStatus($job->fanpage_id, $job->facebook_user_id);
							break;
						case 'photo':
							$fanstat->addCommentPhoto($job->fanpage_id, $job->facebook_user_id);
							break;
						case 'video':
							$fanstat->addCommentVideo($job->fanpage_id, $job->facebook_user_id);
							break;
						case 'link':
							$fanstat->addCommentLink($job->fanpage_id, $job->facebook_user_id);
							break;
					}
					
					// apply point if less than giving limit comment number
					if ($commentModel->getUserCommentCountByPost($postId, $result->from->id) < $fanpageSetting['point_comment_limit']) {
						
						// update fan profile
						$fan = new Model_Fans($job->facebook_user_id, $job->fanpage_id);
					
						// check bonus
						$time = new Zend_Date($result->created_time, Zend_Date::ISO_8601);
						$now = new Zend_Date();
						$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);
							
						if ($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
							$bonus = $fanpageSetting['point_comment_admin'];
						}
					
						$fan->updateFanPoints($fanpageSetting['point_comment_admin']+$bonus);
						$fan->updateFanProfile();
					
						// update pointlog
						$pointLog = array();
						$pointLog['fanpage_id'] = $job->fanpage_id;
						$pointLog['facebook_user_id'] =  $job->facebook_user_id;
						$pointLog['object_id'] = $result->id;
						$pointLog['object_type'] = 'comment';
						$pointLog['giving_points'] = $fanpageSetting['point_comment_admin'] + $bonus;
						$pointLog['note'] = 'comment on admin object , bonus : ' .$bonus;
						
						$result = $pointLogModel->insert($pointLog);
					}	
				}else {
					// update fan stat
					$fanstat = new Model_FansObjectsStats();
					switch ($post->post_type) {
						case 'status':
							$fanstat->addCommentStatus($job->fanpage_id, $job->facebook_user_id);
							if($post->facebook_user_id != $job->facebook_user_id) {
								$fanstat->addGetCommentStatus($job->fanpage_id, $post->facebook_user_id);
							}
							break;
						case 'photo':
							$fanstat->addCommentPhoto($job->fanpage_id, $job->facebook_user_id);
							if(facebook_user_id != $job->facebook_user_id){
								$fanstat->addGetCommentPhoto($job->fanpage_id, $post->facebook_user_id);
							}
							break;
						case 'video':
							$fanstat->addCommentVideo($job->fanpage_id, $job->facebook_user_id);
							if(facebook_user_id != $job->facebook_user_id){
								$fanstat->addGetCommentVideo($job->fanpage_id, $post->facebook_user_id);
							}							
							break;
						case 'link':
							$fanstat->addCommentLink($job->fanpage_id, $job->facebook_user_id);
							if(facebook_user_id != $job->facebook_user_id){
								$fanstat->addGetCommentLink($job->fanpage_id, $post->facebook_user_id);
							}							
							break;
					}
					
					$givingPoint = 0;
					
					// if non admin post, apply virginity
					if ($postModel->isVirginity($postId)) {
						$givingPoint = $fanpageSetting['point_virginity']+1;
						// update virginity break pointlog
						$pointLog = array();
						$pointLog['fanpage_id'] = $job->fanpage_id;
						$pointLog['facebook_user_id'] =  $post->facebook_user_id;
						$pointLog['object_id'] = $result->id;
						$pointLog['object_type'] = 'get_comment';
						$pointLog['giving_points'] = $givingPoint;
						$pointLog['note'] = 'get comment from user, break post virginity';
						$result = $pointLogModel->insert($pointLog);

						// update unique gain pointlog
						$pointLog = array();
						$pointLog['fanpage_id'] = $job->fanpage_id;
						$pointLog['facebook_user_id'] =  $post->facebook_user_id;
						$pointLog['object_id'] = $result->id;
						$pointLog['object_type'] = 'get_comment';
						$pointLog['giving_points'] = 1;
						$pointLog['note'] = 'get comment from user, unique point';
												
						$result = $pointLogModel->insert($pointLog);
					} else if ($postModel->isUniqueInPost($postId, $job->facebook_user_id)) {
						$givingPoint = 1;
						// update unique gain pointlog
						$pointLog = array();
						$pointLog['fanpage_id'] = $job->fanpage_id;
						$pointLog['facebook_user_id'] =  $post->facebook_user_id;
						$pointLog['object_id'] = $result->id;
						$pointLog['object_type'] = 'get_comment';
						$pointLog['giving_points'] = $givingPoint;
						$pointLog['note'] = 'get comment from user, unique point';
						
						$result = $pointLogModel->insert($pointLog);
					}
					
					// update post owner profile
					if ($givingPoint > 0) {
						$fan = new Model_Fans($post->facebook_user_id, $job->fanpage_id);
						$pointLogModel = new Model_PointLog();
							
						$fan->updateFanPoints($givingPoint);
						$fan->updateFanProfile();						
					}
				}
				
				// insert new comment into database
				$commentModel->insert($row);
				
				$db->commit();
			}else {
				// if new post, retrieve post from graph api
				echo 'hello';
				$fanpageModel = new Model_Fanpages();
				$fanpage = $fanpageModel->findRow($job->fanpage_id);
				if ($fanpage) {
					$newPost = getPost($postId, $fanpage->fanpage_id, $fanpage->access_token);
					proccessPost($newPost);
				}
			}
		}else {
			//$queue->deleteMessage($message);
		}
		
	} catch (Exception $e) {
		$db->rollBack();
		echo $e->getMessage();
	}
	$db->closeConnection();
}


// get post data via facebook graph api
function getPost($postId, $fanpageId, $pageAccessToken) {
	$collector = new Service_FancrankCollectorService(null, $fanpageId, $pageAccessToken, 'fetch');
	$result = $collector->getFullPost($postId);
	$fansList = $collector->getNewFanListFromPost($result);
	$fdb = new Service_FancrankDBService($fanpageId, $pageAccessToken);
	
	try {
		//Zend_Debug::dump($facebookUsers);
		$result = $fdb->saveAndUpdateFans($fansList, null, true);
	} catch (Exception $e) {
		echo $e->getMessage();
		//$logger->log ( sprintf ( 'Post like Scan fail: %s',  $e->getMessage ()), Zend_Log::ERR);
	}
	return $result;
}

function proccessPost($result = null) {
	// search database post
	// retrieve fanpage setting
	if (!result) { return; }
	
	$fanpageSettingModel = new Model_FanpageSetting();
	$fanpageSetting = $fanpageSettingModel->findRow($result['fanpage_id']);
	if (!$fanpageSetting) {
		$fanpageSetting = $fanpageSettingModel->getDefaultSetting();
	} else {
		$fanpageSetting = $fanpageSetting->toArray();
	}
	
	//$db = Zend_Db_Table::getDefaultAdapter();
	$db = Zend_Db_Table::getDefaultAdapter();
	$commentModel = new Model_Comments();
	$likeModel = new Model_Likes();
	$pointLogModel = new Model_PointLog();
	$fanstat = new Model_FansObjectsStats();
	
	// handle admin post
	if ($result['facebook_user_id'] == $result['fanpage_id']) {

		// giving point to comment user
		foreach ($result['comment_list'] as $comment) {
			$db->beginTransaction();
			try {
				$bonus = 0;
				if ($comment->from->id != $result['fanpage_id'] &&
						!$commentModel->findRow($comment->id) &&
						($commentCount = $commentModel->getUserCommentCountByPost($result['post_id'], $comment->from->id)) < $fanpageSetting['point_comment_limit']) {
					Zend_Debug::dump($comment->from->id .' ' .$commentCount);

					// insert new comment into database
					$created = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : null, Zend_Date::ISO_8601);
					$row = array (
							'comment_id' => $comment->id,
							'fanpage_id' => $result['fanpage_id'],
							'comment_post_id' => $result['post_id'],
							'facebook_user_id' => $comment->from->id,
							'comment_message' => $db->quote($comment->message),
							'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
							'comment_likes_count' => isset ( $comment->like_count ) ? $comment->like_count : 0,
							'comment_type' => $comment->comment_type
					);
					Zend_Debug::dump($row);
					echo 'insert ' .$commentModel->insert($row) .'<br/>';

					// check bonus
					$time = new Zend_Date($comment->created_time, Zend_Date::ISO_8601);
					$now = new Zend_Date();
					$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);

					if ($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
						$bonus = $fanpageSetting['point_comment_limit'] - $commentCount;
						echo 'has bonus';
					}

					// update fan profile
					$fan = new Model_Fans($comment->from->id, $result['fanpage_id']);
					if(!$fan->isNewFan()) {
						$fan->updateFanPoints($fanpageSetting['point_comment_admin']+$bonus);
						$fan->updateFanProfile();
					}

					// update fan stat
					switch ($comment->comment_type) {
						case 'status':
							$fanstat->addCommentStatus($result['fanpage_id'], $comment->from->id);
							break;
						case 'photo':
							$fanstat->addCommentPhoto($result['fanpage_id'], $comment->from->id);
							break;
						case 'video':
							$fanstat->addCommentVideo($result['fanpage_id'], $comment->from->id);
							break;
						case 'link':
							$fanstat->addCommentLink($result['fanpage_id'], $comment->from->id);
							break;
					}

					// update pointlog
					$pointLog = array();
					$pointLog['fanpage_id'] = $result['fanpage_id'];
					$pointLog['facebook_user_id'] =  $comment->from->id;
					$pointLog['object_id'] = $comment->id;
					$pointLog['object_type'] = $comment->comment_type;
					$pointLog['giving_points'] = $fanpageSetting['point_comment_admin'] + $bonus;
					$pointLog['bonus'] = $bonus;
					$pointLog['note'] = 'comment on admin object , bonus : ' .$bonus;
					$pointLogModel->insert($pointLog);
					$db->commit();
				}
			} catch (Exception $e) {
				echo $e->getMessage() .$e->getCode();
				$db->rollBack();
			}
			$db->closeConnection();
		}

		echo 'like----------------';
		// giving point to like user
		foreach ($result['like_list'] as $like) {
			$db->beginTransaction();
			try {
				if ($like['facebook_user_id'] != $result['fanpage_id']) {
					Zend_Debug::dump($like);

					$found = $likeModel->find($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])->current();

					if (empty($found)) {
						// insert new like into database
						if (isset($like['target'])) {
							unset($like['target']);
						}
						$likeModel->insert($like);

						$bonus = 0;
						// apply double point bonus
						$time = new Zend_Date($result['created_time'], Zend_Date::ISO_8601);
						$now = new Zend_Date();
						$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);
							
						if($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
							$bonus = $fanpageSetting['point_like_bonus'];
						}

						// update fan profile
						$fan = new Model_Fans($like['facebook_user_id'], $like['fanpage_id']);
						if (!$fan->isNewFan()) {
							$fan->updateFanPoints($fanpageSetting['point_like_admin']+$bonus);
							$fan->updateFanProfile();
						}

						// update fan stat
						switch ($result['post_type']) {
							case 'status':
								$fanstat->addLikeStatus($like['fanpage_id'], $like['facebook_user_id']);
								break;
							case 'photo':
								$fanstat->addLikePhoto($like['fanpage_id'], $like['facebook_user_id']);
								break;
							case 'video':
								$fanstat->addLikeVideo($like['fanpage_id'], $like['facebook_user_id']);
								break;
							case 'link':
								$fanstat->addLikeLink($like['fanpage_id'], $like['facebook_user_id']);
								break;
						}

						//update like point log
						$pointLog = array();
						$pointLog['fanpage_id'] = $like['fanpage_id'];
						$pointLog['facebook_user_id'] =  $like['facebook_user_id'];
						$pointLog['object_id'] = $like['post_id'];
						$pointLog['object_type'] = 'likes';
						$pointLog['giving_points'] = $fanpageSetting['point_like_admin'] + $bonus;
						$pointLog['bonus']= $bonus;
						$pointLog['note'] = empty($bonus) ? 'likes on admin post' : sprintf('likes on admin post, %s bonus for likes within %s minutes', $bonus, $fanpageSetting['point_bonus_duration']);
						$pointLogModel->insert($pointLog);
					} else {
						if ($found->likes == 0) {
							$found->likes = 1;
							$found->save();
						}
					}
				}
				$db->commit();
			} catch (Exception $e) {
				echo $e->getMessage();
				$db->rollBack();
			}
			$db->closeConnection();
		}
	} else {
		// handle user post

	}
}