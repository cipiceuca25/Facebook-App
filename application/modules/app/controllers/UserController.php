<?php

class App_UserController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
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

	public function followAction() {
		$subscribe_Model = new Model_Subscribes;
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['subscribe_ref_id'] = $this->_getParam('subscribe_ref_id');
		$data['facebook_user_id_subscribe_to'] = $this->_getParam('subscribe_to');
		$data['follow_enable'] = TRUE;
		$data['facebook_user_id'] = $this->_getParam('id');
		
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
			}
			
		}else {
			if($subscribe->isDataValid($data)) {
				try {
					$result = $subscribe->insert($data);
					//Zend_Debug::dump($result);
				} catch (Exception $e) {
					//TO LOG
				}
			}else {
				//echo 'nothing to save';
			}
			
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
	
	public function profileAction() {
		$user = new Model_FacebookUsers();
		$user = $user->find($this->_getParam('id'))->current();
		if($user) {
			$this->_helper->json($user->toArray());
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
		$fancrankFB->setAccessToken($data['access_token']);
		$result = $fancrankFB->api('/' .$data['facebook_user_id'] .'/feed&limit=2');
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
		//TODO
		$objectId = $this->_getParam('object_id');
		$likeModel = new Model_Likes();
		$data['facebook_user_id'] = $this->_getParam('id');
		$data['post_id'] = $this->_getParam('post_id');
		$data['fanpage_id'] = $this->_getParam('fanpage_id');
		$data['post_type'] = $this->_getParam('post_type');
		
		if(!$likeModel->isDataValid($data)) {
			$msg = array('response'=>'error','message'=>'invalid input');
			$this->_helper->json($msg);
		}
		
		$found = $likeModel->find($data['facebook_user_id'], $this->_getParam('post_id'), $data['fanpage_id'])->current();
		if (empty($found)) {
			//call facebook api and publish likes object to facebook
			try {
				$data['post_id'] = $this->_getParam('post_id');
				$postId = $data['post_id'];
				$fancrankFB = new Service_FancrankFBService();
				$params =  array(
						'access_token' => $data['access_token']
				);
				$fancrankFB->api("/$postId/likes", 'POST', $params);
				
				//save object likes to fancrank database
				$likeModel->insert($data);					
			} catch (Exception $e) {
				//TO LOG
			}

		}
	}
	
	public function dislikeAction() {
		try {
			$data['post_id'] = $this->_getParam('post_id');
			$postId = $data['post_id'];
			$fancrankFB = new Service_FancrankFBService();
			$params =  array(
					'access_token' => $data['access_token']
			);
			$fancrankFB->api("/$postId/likes", 'DELETE', $params);
		
			//delete object likes from fancrank database or turn object likes flag to zero
			//TODO
		} catch (Exception $e) {
			//TO LOG
		}
	}
	
	public function commentAction() {
		//TODO
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
	
}

?>
