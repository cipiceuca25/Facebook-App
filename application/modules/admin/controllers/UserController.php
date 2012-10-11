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
class Admin_UserController extends Fancrank_Admin_Controller_BaseController
{
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed'));
			//set the proper navbar
		}
		//check admin permission
<<<<<<< Updated upstream
		$fanpageId = $this->_getParam('fanpage_id');
		
		$fanpageAdminModel = new Model_FanpageAdmins;
		$admins = $fanpageAdminModel->find($this->_auth->getIdentity()->facebook_user_id, $fanpageId)->count();
=======
		$userId = $this->_getParam('user_id');		
		$fanpageId = $this->_getParam('fanpage_id');
		
		$fanpageAdminModel = new Model_FanpageAdmins;
		$admins = $fanpageAdminModel->find($userId, $fanpageId)->count();
>>>>>>> Stashed changes
		
		if(empty($admins) || $admins < 1) {
			$this->_helper->json(array('message'=>'authentication failed'));
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
	
	public function addpointAction() {
		//Zend_Debug::dump($_POST);
		if(empty($_POST)) {
			$this->_helper->json(array('message'=>'requires post method'));
			return;
		}
		$fanpageId = $this->_getParam('fanpage_id');
		$userId = $this->_getParam('user_id');
		$point = abs($this->_getParam('point'));
		$msg = $this->_getParam('pointMsg');

		if(empty($fanpageId) || empty($userId) || empty($point)) {
			$this->_helper->json(array('message'=>'fields cant be empty'));
			return;
		}
		//update user point
		$fanModel = new Model_Fans($userId,  $fanpageId);
		if($fanModel->isNewFan()) {
			$this->_helper->json(array('message'=>'user not exist'));
			return;
		}
		
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$db->beginTransaction();
				
		try {

			//add points
			$result = $fanModel->addPoint($point);
			
			$fanpageModel = new Model_Fanpages();
			$fanpageName = $fanpageModel->getFanpageName($fanpageId);
			
			//update fancrank activity log
			$data = array();
			$data['activity_type'] = 'admin_add_point';
			$data['event_object'] = $fanpageId;
			$data['facebook_user_id'] = $fanpageId;
			$data['facebook_user_name'] = $fanpageName;
			$data['fanpage_id'] = $fanpageId;
			$data['target_user_id'] = $userId;
			$data['target_user_name'] = $fanModel->getFanProfile()->fan_name;
			$data['message'] = $msg;
			
			$fancrankActivityModel = new Model_FancrankActivities();
			$result = $fancrankActivityModel->insert($data);
			
			//udpate point log
			$pointLog = array();
			$pointLog['fanpage_id'] = $fanpageId;
			$pointLog['facebook_user_id'] = $userId;
			$pointLog['object_id'] = $fanpageId;
			$pointLog['object_type'] = 'admin_add_point';
			$pointLog['giving_points'] = $point;
			$pointLog['note'] = $msg;
			
			$pointLogModel = new Model_PointLog();
			$result = $pointLogModel->insert($pointLog);
			//Zend_Debug::dump($result);
			
			$db->commit();
			$this->_helper->json(array('message'=>'ok'));
		} catch (Exception $e) {
			$db->rollBack();
		}
		$this->_helper->json(array('message'=>'unable to add point'));
	}
	
	public function subtractpointAction() {
		if(empty($_POST)) {
			$this->_helper->json(array('message'=>'requires post method'));
			return;
		}
		
		$fanpageId = $this->_getParam('fanpage_id');
		$userId = $this->_getParam('user_id');
		$point = abs($this->_getParam('point'));
		$msg = $this->_getParam('pointMsg');

		if(empty($fanpageId) || empty($userId) || empty($point)) {
			$this->_helper->json(array('message'=>'fields cant be empty'));
			return;
		}
		
		//update user point
		$fanModel = new Model_Fans($userId,  $fanpageId);
		if($fanModel->isNewFan()) {
			$this->_helper->json(array('message'=>'user not exist'));
			return;
		}
		
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$db->beginTransaction();
				
		try {
			//subtract points
			if($point > $fanModel->getFanCurrency()) {
				$this->_helper->json(array('message'=>'over limit'));
				return;
			}
			$fanModel->subtractPoint($point);
			
			$fanpageModel = new Model_Fanpages();
			$fanpageName = $fanpageModel->getFanpageName($fanpageId);
			
			//update fancrank activity log
			$data = array();
			$data['activity_type'] = 'admin_subtract_point';
			$data['event_object'] = $fanpageId;
			$data['facebook_user_id'] = $fanpageId;
			$data['facebook_user_name'] = $fanpageName;
			$data['fanpage_id'] = $fanpageId;
			$data['target_user_id'] = $userId;
			$data['target_user_name'] = $fanModel->getFanProfile()->fan_name;
			$data['message'] = $msg;
			
			$fancrankActivityModel = new Model_FancrankActivities();
			$fancrankActivityModel->insert($data);
			
			//udpate point log
			$pointLog = array();
			$pointLog['fanpage_id'] = $fanpageId;
			$pointLog['facebook_user_id'] = $userId;
			$pointLog['object_id'] = $fanpageId;
			$pointLog['object_type'] = 'admin_subtract_point';
			$pointLog['giving_points'] = -$point;
			$pointLog['note'] = $msg;
				
			$pointLogModel = new Model_PointLog();
			$result = $pointLogModel->insert($pointLog);
			//Zend_Debug::dump($result);
			$db->commit();
			$this->_helper->json(array('message'=>'ok'));
		} catch (Exception $e) {
			$db->rollBack();
		}
		$this->_helper->json(array('message'=>'unable to add point'));
	}
}

?>
