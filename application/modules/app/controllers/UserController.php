<?php
/**
 * FanCrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the FanCrank OEM license
 *
 * @category    app
 * @package     app
 * @copyright   Copyright (c) 2012 FanCrank
 * @license
 */
class App_UserController extends Fancrank_App_Controller_BaseController
{
	protected $_user;
	
	public function preDispatch() {
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed','code'=>400));
			//set the proper navbar
		}
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->_user = $this->_auth->getIdentity();
		
		if ($this->_getParam('id') != $this->_user->facebook_user_id){
			echo "IDENTITY AND PARAMETER ID DOES NOT MATCH"	;
			exit();
		}
	}
	
	public function indexAction() {
		$postModel = new Model_Posts();
		$fanpageId = $this->_getParam('fanpage_id');
		$userId = $this->_getParam('id');
		//echo $fanpageId .' ' .$userId; exit();
		$result = $postModel->findByUserIdAndFanpageId($userId, $fanpageId);
		foreach ($result as $post) {
			Zend_Debug::dump($post);			
		}
	}

	public function colorAction() {
		$colorChoice = new Model_UsersColorChoice;
		$c = $this->_request->getParam('choice');
		$f = $this->_request->getParam('fanpage_id');
		if(!is_null($c)) {
			$colorChoice ->change($f, $c);
		}
	}
	
	public function followAction() {
		$subscribe_Model = new Model_Subscribes;
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['subscribe_ref_id'] = $this->_getParam('subscribe_ref_id');
		$data['facebook_user_id_subscribe_to'] = $this->_getParam('subscribe_to');
		$data['follow_enable'] = TRUE;
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		
		//Zend_Debug::dump($data);
		$subscribe = $subscribe_Model->findById($data['facebook_user_id'], $data['facebook_user_id_subscribe_to'], $data['subscribe_ref_id']);

		if($subscribe) {
			$dateObject = new Zend_Date();
			$subscribe->update_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$subscribe->fanpage_id = $data['fanpage_id'];
			$subscribe->follow_enable = $data['follow_enable'];
			try {
				$subscribe->save();		
				//addactivity($activity_type, $event_object, $facebook_user_id, $facebook_user_name, $fanpage_id, $target_user_id, $target_name, $message )
				$this->addactivity('follow', $data['facebook_user_id_subscribe_to'],
						$data['fanpage_id'],$data['facebook_user_id_subscribe_to'], $this->_getParam('target_name'),null);
				
				
				echo "followed";
			} catch (Exception $e) {
				//TO LOG
			}
		}else {
			
			if($subscribe_Model->isDataValid($data)) {
				echo "making new follow data";
				try {
					//Zend_Debug::dump($subscribe);
					$result = $subscribe_Model->insert($data);
					$this->addactivity('follow', $data['facebook_user_id_subscribe_to'],
							$data['fanpage_id'],$data['facebook_user_id_subscribe_to'], $this->_getParam('target_name'),null);
					//Zend_Debug::dump($result);
					
				} catch (Exception $e) {
					//TO LOG
					echo $e;
				}
			}else {
				//echo 'nothing to save';
			}
			//Zend_Debug::dump($color->getColorChoice(1));
		}
		
	}
	
	public function profileAction() {
		$user = new Model_FacebookUsers();
		$user = $user->find($this->_getParam('id'))->current();
		if($user) {
			$this->_helper->json($user->toArray());
		}
	}
	
	public function unfollowAction() {
		$subscribe_Model = new Model_Subscribes;
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['subscribe_ref_id'] = $this->_getParam('subscribe_ref_id');
		$data['facebook_user_id_subscribe_to'] = $this->_getParam('subscribe_to');
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		
		//Zend_Debug::dump($data);
		$subscribe = $subscribe_Model->findById($data['facebook_user_id'], $data['facebook_user_id_subscribe_to'], $data['subscribe_ref_id']);
		
		if($subscribe) {
			$dateObject = new Zend_Date();
			$subscribe->update_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$subscribe->fanpage_id = $data['fanpage_id'];
			$subscribe->follow_enable = (int)FALSE;
			try {
				$subscribe->save();		
				$this->addactivity('unfollow', $data['facebook_user_id_subscribe_to'], 
						$data['fanpage_id'],$data['facebook_user_id_subscribe_to'], $this->_getParam('target_name'),null);
			} catch (Exception $e) {
				//TO LOG
			}
		}
	}
	
	public function postAction() {
		//Note: data could initialize from preDispatch
		//197221680326345_425781560803688
		$starttime = time();
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['fanpage_name'] = $this->_getParam('fanpage_name');
		$data['access_token'] = $this->_getParam('access_token');
		//$data['post_id'] = $this->_getParam('post_id');
		$data['message'] = $this->_getParam('message');

		try{
			$fancrankFB = new Service_FancrankFBService();
			$params =  array(
					'message' => $data['message'],
					'access_token' => $this->_user->facebook_user_access_token
			);
	
			$ret_obj = $fancrankFB->api('/'.$data['fanpage_id'].'/feed', 'POST',
					$params);
			
			Zend_Debug::dump($ret_obj);
			
			$data['post_id'] = $ret_obj['id'];
			
			$fanpageModel = new Model_Fanpages();
			$fanpageAccessToken = $fanpageModel->getFanpageAccessToken($data['fanpage_id']);
			
			$client = new Zend_Http_Client;
			$client->setUri("https://graph.facebook.com/". $data['post_id']);
			$client->setMethod(Zend_Http_Client::GET);
			$client->setParameterGet('access_token', $fanpageAccessToken);
			 
			$response = $client->request();
			 
			$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
			 
			Zend_debug::dump($result);
			 
			if(!empty ($result)) {
    			$db = Zend_Db_Table::getDefaultAdapter();
   			
    			// check response error from facebook graph api
				$result = $this->facebookResponseCheck($result);
				
				$db->beginTransaction();
    			$postModel = new Model_Posts();
    			$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
    			$updated = new Zend_Date(!empty($post->updated_time) ? $post->updated_time : null, Zend_Date::ISO_8601);
    
    			$row = array(
    					'post_id'               => $result->id,
    					'facebook_user_id'      => $result->from->id,
    					'fanpage_id'            => $data['fanpage_id'],
    					'post_message'          => isset($result->message) ? $postModel->quoteInto($result->message) : '',
    					'picture'				=> !empty($result->picture) ? $result->picture : '',
    					'link'					=> !empty($result->link) ? $result->link : '',
    					'post_type'             => !empty($result->type) ? $result->type : '',
    					'status_type'           => !empty($result->status_type) ? $result->status_type : '',
    					'post_description'		=> !empty($result->description) ? $postModel->quoteInto($result->description) : '',
    					'post_caption'			=> !empty($result->caption) ? $postModel->quoteInto($result->caption) : '',
    					'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
    					'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
    					'post_comments_count'   => !empty($result->comments->count) ? $result->comments->count : 0,
    					'post_likes_count'      => isset($result->likes) && isset($result->likes->count) ? $result->likes->count : 0
    			);
    
    			if (property_exists($result, 'application') && isset($result->application->id)) {
    				$row['post_application_id'] = $result->application->id;
    				$row['post_application_name'] = empty($result->application->name) ? null : $result->application->name;
    			} else {
    				$row['post_application_id'] = null;
    				$row['post_application_name'] = null;
    			}
    
    			try {
    				// retrieve fanpage setting
    				$fanpageSettingModel = new Model_FanpageSetting();
    				$settingData = $fanpageSettingModel->findRow($data['fanpage_id']);
    				if(!$settingData) {
    					$settingData = $fanpageSettingModel->getDefaultSetting();
    				}else {
    					$settingData = $settingData->toArray();
    				}
    					
    				// insert new post into database
    				$postModel->insert($row);

    				// if none admin post, apply point rule to post
    				if($data['fanpage_id'] != $result->from->id) {
    					// add activity into database
    					$this->addactivity('post-'.$row['post_type'], $data['post_id'],
    							$data['fanpage_id'],$data['fanpage_id'], $data['fanpage_name'],$row['post_message'] );
    						
    					// update fan data
    					$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
    					$fan->updateFanPoints($settingData['point_post_normal']);
    					$fan->updateFanProfile();
    						
    					// update fan stat
    					$fanstat = new Model_FansObjectsStats();
    					switch($row['post_type']){
    						case 'status':
    							$fanstat ->addPostStatus($data['fanpage_id'], $data['facebook_user_id']);
    							break;
    						case 'photo':
    							$fanstat->addPostPhoto($data['fanpage_id'], $data['facebook_user_id']);
    							break;
    						case 'video':
    							$fanstat->addPostVideo($data['fanpage_id'], $data['facebook_user_id']);
    							break;
    						case 'link':
    							$fanstat->addPostLink($data['fanpage_id'], $data['facebook_user_id']);
    							break;
    					}
    						
    					// update point data
    					$pointLog = array();
    					$pointLog['fanpage_id'] = $data['fanpage_id'];
    					$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
    					$pointLog['object_id'] = $data['post_id'];
    					$pointLog['object_type'] = 'posts';
    					$pointLog['giving_points'] = $settingData['point_post_normal'];
    					$pointLog['note'] = 'post on fanpage';
    					$pointLogModel = new Model_PointLog();
    					$result = $pointLogModel->insert($pointLog);
    				}else {
    					$this->addactivity('post-'.$row['post_type'], $data['post_id'],
    							$data['fanpage_id'],$data['fanpage_id'], $data['fanpage_name'],$row['post_message'] );
    				}
    				
    				// commit all update
    				$db->commit();
    				$db->closeConnection();
				} catch (Exception $e) {
					$db->rollBack();
					$db->closeConnection();
					print $e->getMessage();
					$appLogger = Zend_Registry::get('appLog');
					$appLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $post->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
					return;
				}
			}
			//return $result;

		} catch (Exception $e){
			echo $e;
			$appLogger = Zend_Registry::get('appLog');
			$appLogger->log(sprintf('Unable to save post from fanpage %s to database. Error Message: %s ', $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
		}
		echo '<br/>' .time() - $starttime . 'sec';
	}
	
	public function feedAction() {
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['access_token'] = $this->_getParam('access_token');
		
		$fancrankFB = new Service_FancrankFBService();
		$params =  array(
				'access_token' => $data['access_token'],
				'limit' => 2,
		);
		$result = $fancrankFB->api('/' .$data['facebook_user_id'] .'/feed', 'GET', $params);
		//Zend_Debug::dump($result);
		if(empty($result['data'])) {
			$msg = array('response'=>'error');
			$this->_helper->json($msg);
		}

		$this->_helper->json($result['data']);
	}

	public function updateprofileAction() {
		$fancrankDB = new Service_FancrankDBService();
		$fancrankDB->saveFacebookUser($this->user);
	}

	public function likesAction() {
		//$objectId = $this->_getParam('object_id');
		$likeModel = new Model_Likes();
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		$data['post_id'] = $this->_getParam('post_id');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['post_type'] = $this->_getParam('post_type');
		$data['access_token'] = $this->_getParam('access_token');
		
		$isComment = strpos($this->_getParam('post_type'),'comment')?true:false;
		$db = Zend_Db_Table::getDefaultAdapter();
		try {
			//$data['post_id'] = $this->_getParam('post_id');
			$postId = $data['post_id'];
			$fancrankFB = new Service_FancrankFBService();
			$params =  array(
				'access_token' => $data['access_token']
			);
			
			$likeResponse = $fancrankFB->api("/$postId/likes", 'POST', $params);
			
			// check connection problem
			if(!is_bool($likeResponse)) {
				echo 'relog';
			}else {
				// do nothing if false response 
				if(!$likeResponse) return;
			}
			
			//save object likes to fancrank database
			//******* WE HAVE YET TO UPDATE THE POSTS DATABASE THRU THIS FUNCTION
			
			$db->beginTransaction();
			//echo 'attempting insert data to like.';
			//$likeModel->insert($data);

			//save like into database, return 0 if like exists in database and like flag is 0, return 1 if new like
			$likesModel = $likeModel->insertNewLikesReturn($data['fanpage_id'], $data['post_id'], $data['facebook_user_id'], $data['post_type'] );
			//Zend_Debug::dump($likesModel);
			//ADDING POINTS NOW
			//$activity_type, $event_object, $facebook_user_id, $facebook_user_name, $fanpage_id, $target_user_id, $target_name, $message

			$bonus = 0;
			$virgin = false;
			$fanPoint = 0;
			//echo $bonus; 
			if ($likesModel !== 0) {
				//echo 'like is not redundant';
				
				$fanstat = new Model_FansObjectsStats();
				if ($isComment) {
					$comment = new Model_Comments();
					$post = $comment -> addLikeToCommentReturn($data['post_id']);
						
					if ($post == null){
						$post['facebook_user_id'] = $this->_getParam('target_id');
					}
					//echo 'increasing comment likes count';
					
					//update fan stat table
					if($post['facebook_user_id'] != $data['fanpage_id']){
						$fanstat -> addLikeComment($data['fanpage_id'], $data['facebook_user_id']);
						$fanstat -> addGetLikeComment($data['fanpage_id'], $post['facebook_user_id']);
					}
					
					// you still need to compare the created times and check if bonus should be applied
				}else{
					// if liked a post
					$post= new Model_Posts();
					$post = $post -> addLikeToPostReturn($data['post_id']);
					//Zend_Debug::dump($post);
					
					if ($post==null){
						$post['facebook_user_id'] = $this->_getParam('target_id');
					}
					
					switch($data['post_type']){
						
						case 'status':
							if($post['facebook_user_id'] != $data['fanpage_id']){
								$fanstat->addLikeStatus($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> addGetLikeStatus($data['fanpage_id'], $post['facebook_user_id']);
							}
							break;
						case 'photo':
							if($post['facebook_user_id'] != $data['fanpage_id']){
								$fanstat->addLikePhoto($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> addGetLikePhoto($data['fanpage_id'], $post['facebook_user_id']);
							}
							break;
						case 'video':
							if($post['facebook_user_id'] != $data['fanpage_id']){
								$fanstat->addLikeVideo($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> addGetLikeVideo($data['fanpage_id'], $post['facebook_user_id']);
							}
							break;
						case 'link':
							if($post['facebook_user_id'] != $data['fanpage_id']){
								$fanstat->addLikeLink($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> addGetLikeLink($data['fanpage_id'], $post['facebook_user_id']);
							}
							break;
					}
					
					if (isset($post['post_comments_count'])){
						if ($post['post_comments_count'] + $post['post_likes_count'] == 1){
							$virgin=true;
							//echo 'virginity is true';
						}
					}

				}
				
				if ($isComment){
					$x = explode("_",$data['post_id']);
					$this->addactivity('like-comment', $x[0].'_'.$x[1],
						$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $this->_getParam('mes'));
				}else{
					$this->addactivity('like-'.$this->_getParam('post_type'), $data['post_id'],
					$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $this->_getParam('mes'));
				}
				
				//echo 'adding activities';
				
				//Zend_Debug::dump($likesModel);
				//if likes model didn't return anything 
				//ie if its not a "new" like, there doesn't need to be points 
				if (is_int($likesModel) && $likesModel == 1){
					$settingModel = new Model_FanpageSetting();
					$fanpageSetting = $settingModel->getFanpageSetting($data['fanpage_id']);
					$pointLogModel = new Model_PointLog();
					//echo ' Like is new, points need to be allocated';
					
					// check target object is not liker's own post
					if ($data['facebook_user_id'] != $post['facebook_user_id']) {
						//echo 'User did not like his/her own post';
						//if not admin object , apply normal user like point rule
						if ($post['facebook_user_id'] != $data['fanpage_id']) {
							//echo 'poster is not a fanpage';
							//update get_like fan
							$fan = new Model_Fans($post['facebook_user_id'], $data['fanpage_id']);
							$fan->updateFanPoints($fanpageSetting['point_like_normal']);
							$fan->updateFanProfile();
							$this->updateProfileMemcache($fan->getFanProfile());
								
							//update get_like point log
							$pointLog = array();
							$pointLog['fanpage_id'] = $data['fanpage_id'];
							$pointLog['facebook_user_id'] =  $post['facebook_user_id'];
							$pointLog['object_id'] =  $data['post_id'];
							$pointLog['object_type'] = 'get_likes';
							$pointLog['giving_points'] = $fanpageSetting['point_like_normal'];
							$pointLog['note'] = 'get like on object';

							$result = $pointLogModel->insert($pointLog);
							
							// update like fan if not admin
							if($data['facebook_user_id'] != $data['fanpage_id']) {
								//update like fan
								$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
								$fan->updateFanPoints($fanpageSetting['point_like_normal']);
								$fan->updateFanProfile();
								$fanPoint = $fan->getFanPoint();
								$this->updateProfileMemcache($fan->getFanProfile());
								
								//update like point log
								$pointLog = array();
								$pointLog['fanpage_id'] = $data['fanpage_id'];
								$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
								$pointLog['object_id'] = $data['post_id'];
								$pointLog['object_type'] = 'likes';
								$pointLog['giving_points'] = $fanpageSetting['point_like_normal'];
								$pointLog['bonus']= 0;
								$pointLog['note'] = 'likes on user object';
								$result = $pointLogModel->insert($pointLog);
							}
						}else{
							// admin object, apply admin point rule
							//echo 'trying memcache';
							$cache = Zend_Registry::get('memcache');
							
							//$cache->remove($this->_fanpageId .'_' .$this->_userId);
							// Zend_Debug::dump($data['post_id']);
							try {
								//Zend_Debug::dump($cache);
								//Check to see if the $fanpageId is cached and look it up if not
								if(isset($cache) && !$cache->load($data['post_id'])){
									//Look up the $fanpageId
									//$post = $cache->load($data['post_id']);
								}else {
									
									//echo 'memcache look up';
									$post = $cache->load($data['post_id']);
								}
							} catch (Exception $e) {
								Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
								//echo $e->getMessage();
							}
							
							// apply double point bonus
							if(isset($post->created_time) && !$isComment){
								$time = new Zend_Date($post->created_time, Zend_Date::ISO_8601);
								$now = new Zend_Date();
								$timeDifferentInMinute = floor(($now->getTimestamp() - $time->getTimestamp()) / 60);
								 
								if($timeDifferentInMinute < $fanpageSetting['point_bonus_duration']) {
									$bonus = $fanpageSetting['point_like_bonus'];
								}
							}
							
							// update like fan if not admin
							if($data['facebook_user_id'] != $data['fanpage_id']) {
								//update like fan
								//echo 'giving points to liker';
								$fan = new Model_Fans($data['facebook_user_id'], $data['fanpage_id']);
								$fan->updateFanPoints($fanpageSetting['point_like_admin']+$bonus);
								$fan->updateFanProfile();
								$fanPoint = $fan->getFanPoint();
								$this->updateProfileMemcache($fan->getFanProfile());
								
								//update like point log
								$pointLog = array();
								$pointLog['fanpage_id'] = $data['fanpage_id'];
								$pointLog['facebook_user_id'] =  $data['facebook_user_id'];
								$pointLog['object_id'] = $data['post_id'];
								$pointLog['object_type'] = 'likes';
								$pointLog['giving_points'] = $fanpageSetting['point_like_admin'] + $bonus;
								$pointLog['bonus']= $bonus;
								$pointLog['note'] = empty($bonus) ? 'likes on admin object' : sprintf('likes on admin post, %s bonus for likes within %s minutes', $bonus, $fanpageSetting['point_bonus_duration']);
								$result = $pointLogModel->insert($pointLog);
							}
						}
					}
				}
			}

			$db->commit();
			$this->_helper->json(array('fan_point'=>$fanPoint));
			// update profile memcache
					
		} catch (Exception $e) {
			//TO LOG
			$db->rollBack();
			//echo $e;
		}
	}

	public function unlikeAction() {
		
		$likeModel = new Model_Likes();
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['post_id'] = $this->_getParam('post_id');
		
		$data['post_type'] = $this->_getParam('post_type');
		$data['likes'] = 0;
		$isComment = strpos($this->_getParam('post_type'),'comment')?true:false;
		$data['access_token'] = $this->_getParam('access_token');
		//echo ('function called ');
		
		//Zend_debug::dump($data);

		//echo ('getting rdy to like');
		$found = $likeModel->find($data['facebook_user_id'], $this->_getParam('post_id'), $data['fanpage_id'])->current();
		if (empty($found)) {
			//call facebook api and publish likes object to facebook
			try {
				//$data['post_id'] = $this->_getParam('post_id');
				$postId = $data['post_id'];
				$fancrankFB = new Service_FancrankFBService();
				$params =  array(
						'access_token' => $data['access_token']
				);
				$fancrankFB->api("/$postId/likes", 'DELETE', $params);
				
				//save object likes to fancrank database
				echo " trying to unlike";
				//******* WE HAVE YET TO UPDATE THE POSTS DATABASE THRU THIS FUNCTION
				
				//echo 'inserting data to like.';
				//$likeModel->insert($data);		
				$likeModel->unlike($data['fanpage_id'], $data['post_id'], $data['facebook_user_id'], $data['post_type'] );
				
				
				$fanstat = new Model_FansObjectsStats();
				if ($data['post_type'] == 'comment') {
					$comment = new Model_Comments();
					$post = $comment -> subtractLikeToCommentReturn($data['post_id']);
					if ($post==null){
							
						$post['facebook_user_id'] = $this->_getParam('target_id');
					
						//Zend_Debug::dump($post);
					}	
					$fanstat -> subLikeComment($data['fanpage_id'], $data['facebook_user_id']);
					if($post['facebook_user_id'] != $data['fanpage_id']){
						$fanstat -> subGetLikeComment($data['fanpage_id'], $post['facebook_user_id']);
					}
						
					// you still need to compare the created times and check if bonus should be applied
				}else{
					// if liked a post
					$post= new Model_Posts();
					$post = $post -> subtractLikeToPostReturn($data['post_id']);					
					if ($post==null){
							
						$post['facebook_user_id'] = $this->_getParam('target_id');
							
						//Zend_Debug::dump($post);
					}	
					switch($data['post_type']){
				
						case 'status':
							if($this->_getParam('target_id') != $data['fanpage_id']){
								$fanstat->subLikeStatus($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> subGetLikeStatus($data['fanpage_id'], $data['target_id']);
							}
							break;
						case 'photo':
							if($this->_getParam('target_id') != $data['fanpage_id']){
								$fanstat->subLikePhoto($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> subGetLikePhoto($data['fanpage_id'], $data['target_id']);
							}
							break;
						case 'video':
							if($this->_getParam('target_id') != $data['fanpage_id']){
								$fanstat->subLikeVideo($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> subGetLikeVideo($data['fanpage_id'], $data['target_id']);
							}
							break;
						case 'link':
							if($this->_getParam('target_id') != $data['fanpage_id']){
								$fanstat->subLikeLink($data['fanpage_id'], $data['facebook_user_id']);
								$fanstat -> subGetLikeLink($data['fanpage_id'], $data['target_id']);
							}
							break;
				
					}
						
				}
			
				if ($isComment){
					$x = explode("_",$data['post_id']);
					$this->addactivity('unlike-comment', $x[0].'_'.$x[1],
							$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $this->_getParam('mes'));
				}else{
					$this->addactivity('unlike-'.$this->_getParam('post_type'), $data['post_id'],
							$data['fanpage_id'],$post['facebook_user_id'], $this->_getParam('target_name'), $this->_getParam('mes'));
				}
				
				echo "unliked";
			} catch (Exception $e) {
				//TO LOG
				echo "error".$e;
			}

		}
	}
	
	public function commentAction() {
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		$data['access_token'] = $this->_getParam('access_token');
		$data['post_id'] = $this->_getParam('post_id');
		$data['post_type'] = $this->_getParam('post_type');
		$data['message'] = $this->_getParam('message');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['fanpage_name'] = $this->_getParam('fanpage_name');
		//$data['target_id'] = $this->_getParam('target_id');
		//$data['target_name'] = $this->_getParam('target_name');

		try{
			// save comment to facebook original page
			$client = new Zend_Http_Client;
			$client->setUri("https://graph.facebook.com/" .$data['post_id'] ."/comments");
			$client->setMethod(Zend_Http_Client::POST);
			$client->setParameterGet('access_token', $data['access_token']);
			$client->setParameterGet('message', $data['message']);
			$response = $client->request();
		
			$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
			$result = $this->facebookResponseCheck($result);
		
			if(empty($result->id)) {
				return;
			}
		
			$data['comment_id'] = $result->id;
		
			// get default database adapter
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
		
			try {
				// comment out following code will disable comment monitor
				// add to comment que, apply point later
// 				$option = array('name'=>'commentqueue');
// 				$adapter = new Fancrank_Queue_Adapter($option);
// 				$queue = new Fancrank_Queue($adapter, $option);
				
// 				// add comment message queue
// 				$message = array(
// 						'fanpage_id' => $data['fanpage_id'],
// 						'access_token' => $data['access_token'],
// 						'facebook_user_id'=> $data['facebook_user_id'],
// 						'id' =>	$data['comment_id'],
// 						'type' => $data['post_type']
// 				);
// 				//Zend_Debug::dump($list);
// 				$queue->send($message, strtotime('1 minutes'));
				
				// add activity into database
				$this->addactivity('comment-'.$data['post_type'], $data['post_id'],
						$data['fanpage_id'], $data['fanpage_id'], $data['fanpage_name'], $data['message']);
				 
				$db->commit();
			} catch ( Exception $e ) {
				$db->rollBack();
				print $e->getMessage ();
				$appLogger = Zend_Registry::get('appLog');
				$appLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $comment->id, $data['fanpage_id'], $e->getMessage () ), Zend_log::ERR );
				return;
			}
			 
		}catch(Exception $e){
			echo $e->getMessage();
			$appLogger = Zend_Registry::get('appLog');
			$appLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $data['message'], $data['fanpage_id'], $e->getMessage () ), Zend_log::ERR );
		}
		
	}
	
	
	
	public function relationAction(){
		$follow = new Model_Subscribes();
		$user= $this->_getParam('id');
		$target = $this->_getParam('target_id');
		$fanpage_id = $this->_getParam('fanpage_id');
		//echo $user.' '.$target;
		echo $follow->getRelation($user, $target, $fanpage_id);
	}
	
	protected function  addactivity($activity_type, $event_object, $fanpage_id, $target_user_id, $target_name, $message ){
		/*
		$data['activity_type'] = $this -> _getParam('activity_type');
		$data['event_object'] = $this-> _getParam('event');
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['facebook_user_name'] = $this->_getParam('owner_name');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['target_user_id'] = $this->_getParam('target_id');
		$data['target_user_name'] = $this->_getParam('target_name');
		$data['message'] = $this->_getParam('message');
		*/
		$data['activity_type'] = $activity_type;
		$data['event_object'] = $event_object;
		$data['facebook_user_id'] = $this->_user->facebook_user_id;
		$data['facebook_user_name'] = $this->_user->facebook_user_name;
		$data['fanpage_id'] = $fanpage_id;
		$data['target_user_id'] = $target_user_id;
		$data['target_user_name'] = $target_name;
		$data['message'] = $message;
		$act = new Model_FancrankActivities();
		$post = new Model_Posts();
		/*
		if ($data['activity_type'] == "like-status" || $data['activity_type'] == "like-photo" || 
			 $data['activity_type'] == "like-video" || $data['activity_type'] == "like-link"){
			$post->addLikeToPost($data['event_object']);
		}else if ($data['activity_type'] == "unlike-status" || $data['activity_type'] == "unlike-photo" || 
			 $data['activity_type'] == "unlike-video" || $data['activity_type'] == "unlike-link"){
			$post->subtractLikeToPost($data['event_object']);
		}else if ($data['activity_type'] == "comment-status" || $data['activity_type'] == "comment-photo" || 
			 $data['activity_type'] == "comment-video" || $data['activity_type'] == "comment-link"){
			$post->addCommentToPost($data['event_object']);
		}
		*/
		$act -> addActivities($data);
	}
	
	/*
	public function addactivityAction(){
		
		$data['activity_type'] = $this -> _getParam('activity_type');
		$data['event_object'] = $this-> _getParam('event');
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['facebook_user_name'] = $this->_getParam('owner_name');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['target_user_id'] = $this->_getParam('target_id');
		$data['target_user_name'] = $this->_getParam('target_name');
		$data['message'] = $this->_getParam('message');
		
		$act = new Model_FancrankActivities();
		$post = new Model_Posts();
		
		if ($data['activity_type'] == "like-status" || $data['activity_type'] == "like-photo" || 
			 $data['activity_type'] == "like-video" || $data['activity_type'] == "like-link"){
			$post->addLikeToPost($data['event_object']);
		}else if ($data['activity_type'] == "unlike-status" || $data['activity_type'] == "unlike-photo" || 
			 $data['activity_type'] == "unlike-video" || $data['activity_type'] == "unlike-link"){
			$post->subtractLikeToPost($data['event_object']);
		}else if ($data['activity_type'] == "comment-status" || $data['activity_type'] == "comment-photo" || 
			 $data['activity_type'] == "comment-video" || $data['activity_type'] == "comment-link"){
			$post->addCommentToPost($data['event_object']);
		}

		$act -> addActivities($data);
	}
	*/
	public function saveuserdescriptionAction(){
		
		$fanpageId = $this->_getParam('fanpage_id');
		$userId =  $this->_getParam('id');
		$message = $this->_getParam('message');
		$fan = new Model_Fans($userId, $fanpageId);
		$fan->updateDescription(substr( trim($message), 0, 160));
		
		$cache = Zend_Registry::get('memcache');
		$fanProfileId = $fanpageId.'_' .$userId.'_fan';
		$cache->remove($fanProfileId);
		
		echo 'ok description saved';
	}
	

	
	public function uploadAction() {
		$fc = $this->_getParam('fanpage_id');
		
		try {
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload//->addValidator('Count', false, 1)     // ensure only 1 file
			->addValidator('Size', false, 10000000) // limit to 100K
			->addValidator('Extension' ,false, 'jpg,png,gif');
			//echo $upload ->getFileName();	
			$facebook = new Service_FancrankFBService();
			$facebook->setAccessToken($this->_getParam('access_token'));
			$facebook->setFileUploadSupport(true);
			$upload->receive();
			
			$imageFileName = $upload->getFileName();
				
			try {
				$file= $imageFileName;
			
				$args = array(
						'message' => $this->_getParam('message'),
						'picture' => 'http://xiless.com/public/www/back.jpg' ,//$file,
						
				);
				echo $file;
				$args[basename($file)] = '@' . realpath($file);
				
				$result = $facebook->api("/".$fc."/feed", 'post', $args);
					
				//Zend_Debug::dump($result);
				echo 'done';
			} catch (FacebookApiException $e) {
				print $e->getMessage();
				
			}
				
				
			/*$imageFileName = $this->_getParam('fanpage_id') .'_picture' .strrchr($upload->getFileName(), '.');
				$fullFilePath = $imageDestination .DIRECTORY_SEPARATOR .$imageFileName;
			if ($upload->isValid()) {
			$upload->setDestination($imageDestination);
			$upload->addFilter('Rename', array('target' => $fullFilePath, 'overwrite' => true));
			//check upload file
			if ($upload->receive()) {
			$this->view->message = 'ok';
			$this->_response->setHttpResponseCode(200);
			}else {
			//TO LOG
			throw new Exception('unable to save');
			}
			}else {
			//TO LOG
			throw new Exception(implode(PHP_EOL, $upload->getErrors()));
			}*/
		} catch (Exception $e) {
			//TO LOG
			
			$this->view->message = $e->getMessage();
			$this->_response->setHttpResponseCode(400);
		}
	}
	
	public function pointlognotificationAction(){
	
		$fanpage_id = $this->_getParam('fanpage_id');
		
		$time = $this->_request->getParam('time');
		$pointlog = new Model_PointLog();
		
		if ($time=='undefined'){
			
			$fan = new Model_Fans($this->_user->facebook_user_id, $fanpage_id);
			
			$time = $fan->getLastLoginTime();
		}
		
		$pointlog = $pointlog -> getPointsGainSinceTimeByDay($fanpage_id, $this->_user->facebook_user_id, $time);
		$this->_helper->json($pointlog);
	}
	
	public function notificationAction() {
		
		//$fp = $this->_getParam('fanpage_id');
		$userBadges = new Model_BadgeEvents();
		/*
		$userBadgeCount = $userBadges->getNonNotifiedBadgesByUser($fp, $this->_user->facebook_user_id);
		//Zend_Debug::dump($userBadgeCount);
		if (!empty($userBadgeCount)){
			$this->_helper->json(array('message'=>'ok', 'notification'=>array('newBadgeCount'=>$userBadgeCount), 'count'=> count($userBadgeCount)));
		}else{
			$this->_helper->json(array('message'=>'none'));
		}*/
		$userBadges = $userBadges -> notify($this->_user->fanpage_id, $this->_user->facebook_user_id);
		
		//$this->_helper->json($userBadges);
	}
	
	public function setviewedbadgesAction() {
		$fp = $this->_getParam('fanpage_id');

		$userBadges = new Model_BadgeEvents();
		$fan = new Model_Fans($this->_user->facebook_user_id, $this->_user->fanpage_id);
		$userBadges -> setViewBadgesByTime($fp, $this->_user->facebook_user_id , $fan->getLastNotification());
	}
	
	private function facebookResponseCheck($result) {
		// check response error from facebook graph api
		if(!empty($result->error)) {
			$type = isset($result->error->type) ? $result->error->type : '';
			$code = isset($result->error->code) ? $result->error->code : '';
			$message = isset($result->error->message) ? $result->error->message : '';
			$msg = sprintf('type: %s, $code: %s, message: %s', $type, $code, $message);
			throw new Exception($msg);
		}
		return $result;
	}

	public function savechosenbadgesAction(){
		$fp = $this->_getParam('fanpage');
		$c1 = $this->_getParam('c1');
		$c2 = $this->_getParam('c2');
		$c3 = $this->_getParam('c3');
		
		$fan = new Model_Fans($this->_user->facebook_user_id, $fp);
		
		// need to find why to parse commas
	
		$fan->updateBadgeChoices($c1.','.$c2.','.$c3);
		echo 'badges save';
	}
	
	public function savelastnotificationAction(){
		$fp = $this->_getParam('fanpage');
		$fan = new Model_Fans($this->_user->facebook_user_id, $fp);
		$fan->updateLastNotification();
	
	}
	
	private function updateProfileMemcache($fanProfile) {
		$cache = Zend_Registry::get('memcache');
			
		try {
			$fanProfileId = $fanProfile->fanpage_id .'_' .$fanProfile->facebook_user_id .'_fan';
			//$cache->remove($fanProfileId);
			if(isset($cache)){
				//echo 'db look up';
				$fanModel = new Model_Fans($fanProfile->facebook_user_id, $fanProfile->fanpage_id);
				$cache->save($fanProfile, $fanProfileId);
			}
		} catch (Exception $e) {
			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
			//echo $e->getMessage();
		}
	}
}
