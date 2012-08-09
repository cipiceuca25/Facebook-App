<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    app
 * @package     app
 * @copyright   Copyright (c) 2012 Francrank
 * @license
 */
class App_UserController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed'));
			//set the proper navbar
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
		$data['facebook_user_id'] = $this->_getParam('facebook_user_id');
		
		//Zend_Debug::dump($data);
		$subscribe = $subscribe_Model->findById($data['facebook_user_id'], $data['facebook_user_id_subscribe_to'], $data['subscribe_ref_id']);

		if($subscribe) {
			$dateObject = new Zend_Date();
			$subscribe->update_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$subscribe->fanpage_id = $data['fanpage_id'];
			$subscribe->follow_enable = TRUE;
			try {
				$subscribe->save();				
			} catch (Exception $e) {
				//TO LOG
			}
		}else {
			if($subscribe_Model->isDataValid($data)) {
				try {
					$result = $subscribe_Model->insert($data);
					//Zend_Debug::dump($result);
				} catch (Exception $e) {
					//TO LOG
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
		$data['facebook_user_id'] = $this->_getParam('id');
		
		//Zend_Debug::dump($data);
		$subscribe = $subscribe_Model->findById($data['facebook_user_id'], $data['facebook_user_id_subscribe_to'], $data['subscribe_ref_id']);
		
		if($subscribe) {
			$dateObject = new Zend_Date();
			$subscribe->update_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$subscribe->fanpage_id = $data['fanpage_id'];
			$subscribe->follow_enable = (int)FALSE;
			try {
				$subscribe->save();				
			} catch (Exception $e) {
				//TO LOG
			}
		}
	}
	
	public function postAction() {
		//Note: data could initialize from preDispatch
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['access_token'] = $this->_getParam('access_token');
		$fancrankFB = new Service_FancrankFBService();
		$msg = "";
		$name = "";
		$link = "";
		$description = "";
		
		//save post to fancrank database
		//$post = new Model_Posts();
		//$post->insert($data);
		
		$params =  array(
				'access_token' => $data['access_token'],
				'message' => "$msg",
				'name' => "$name",
				'link' => "$link",
				'description' => "$description",
		);
		
		//push post to user's wall
		$fancrankFB->api('/' .$data['facebook_user_id'] .'/feed', 'POST', $params);
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
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['post_id'] = $this->_getParam('post_id');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['post_type'] = $this->_getParam('post_type');
		$data['likes'] = 1;

		$data['access_token'] = $this->_getParam('access_token');
		//echo ('function called ');

		//Zend_debug::dump($data);

		if(!$likeModel->isDataValid($data)) {
			$msg = array('response'=>'error','message'=>'invalid input');
			$this->_helper->json($msg);
		}


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
				$fancrankFB->api("/$postId/likes", 'POST', $params);

				//save object likes to fancrank database
				
				//******* WE HAVE YET TO UPDATE THE POSTS DATABASE THRU THIS FUNCTION
				
				echo 'inserting data to like.';
				//$likeModel->insert($data);		
				$likeModel->insertNewLikes($data['fanpage_id'], $data['post_id'], $data['facebook_user_id'], $data['post_type'] );
			} catch (Exception $e) {
				//TO LOG
				
			}

		}
	}
	

	public function unlikeAction() {
		
	$likeModel = new Model_Likes();
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['post_id'] = $this->_getParam('post_id');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['post_type'] = $this->_getParam('post_type');
		$data['likes'] = 0;
		
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
				
				echo "unliked";
			} catch (Exception $e) {
				//TO LOG
				echo "error".$e;
			}

		}
		
	}
	
	public function commentAction() {
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['access_token'] = $this->_getParam('access_token');
		$data['post_id'] = $this->_getParam('post_id');
		$postId = $data['post_id'];
		$fancrankFB = new Service_FancrankFBService();
		$msg = "";
		
		//save comment to fancrank database
		//$comment = new Model_Comments();
		//$comment->insert($data);
		
		//push comment to the post
		$params =  array(
				'access_token' => $data['access_token'],
				'message' => "$msg",
		);
		$fancrankFB->api("/$postId/comments", 'POST', $params);
	}
	
	public function commenttestAction(){
		$comment = new Model_Comments();
		$wootwoot = $comment -> getClosestCommentsByTimestamp($this->_getParam('comment'), $this->_getParam('limit'));
		Zend_Debug::dump($wootwoot);
	}
	
	public function relationAction(){
		$follow = new Model_Subscribes();
		$user= $this->_getParam('id');
		$target = $this->_getParam('target_id');
		echo $user.' '.$target;
		echo $follow->getRelation($user, $target);
	}
	
	public function addactivityAction(){
		
		$data['activity_type'] = $this -> _getParam('activity_type');
		$data['event_object'] = $this-> _getParam('event');
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['facebook_user_name'] = $this->_getParam('owner_name');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['target_user_id'] = $this->_getParam('target_id');
		$data['target_user_name'] = $this->_getParam('target_name');
		
		$act = new Model_FancrankActivities();
		
		$stat = new Model_FansObjectsStats();
		
		
		switch($data['activity_type']){
		
			case 'post-photo':
				$stat->addPostPhoto($data['fanpage_id'], $data['facebook_user_id']);
				break;
			case 'post-video':
				$stat->addPostVideo($data['fanpage_id'], $data['facebook_user_id']);
				break;
			case 'post-link':
				$stat->addPostLink($data['fanpage_id'], $data['facebook_user_id']);
				break;
			case 'post-status':
				$stat->addPostStatus($data['fanpage_id'], $data['facebook_user_id']);
				break;
			case 'comment-status':
				$stat->addCommentStatus($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetCommentStatus($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'comment-photo':
				$stat->addCommentPhoto($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetCommentPhoto($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'comment-video':
				$stat->addCommentVideo($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetCommentVideo($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'comment-link':
				$stat->addCommentLink($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetCommentLink($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'like-status':
				$stat->addLikeStatus($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetLikeStatus($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'like-photo':
				$stat->addLikePhoto($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetLikePhoto($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'like-video':
				$stat->addLikeVideo($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetLikeVideo($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'like-link':
				$stat->addLikeLink($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetLikeLink($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'like-comment':
				$stat->addLikeComment($data['fanpage_id'], $data['facebook_user_id']);
				$stat->addGetLikeComment($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'unlike-status':
				$stat->subLikeStatus($data['fanpage_id'], $data['facebook_user_id']);
				$stat->subGetLikeStatus($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'unlike-photo':
				$stat->subLikePhoto($data['fanpage_id'], $data['facebook_user_id']);
				$stat->subGetLikePhoto($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'unlike-video':
				$stat->subLikeVideo($data['fanpage_id'], $data['facebook_user_id']);
				$stat->subGetLikevideo($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'unlike-link':
				$stat->subLikeLink($data['fanpage_id'], $data['facebook_user_id']);
				$stat->subGetLikeLink($data['fanpage_id'], $data['target_user_id']);
				break;
			case 'unlike-comment':
				$stat->subLikeComment($data['fanpage_id'], $data['facebook_user_id']);
				$stat->subGetLikeComment($data['fanpage_id'], $data['target_user_id']);
				break;
			default:
				break;
		}
		
		
		$act -> addActivities($data);
		
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
	
}

?>
