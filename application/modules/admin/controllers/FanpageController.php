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
 * @copyright   Copyright (c) 2012 Fancrank
 * @license
 */
class Admin_FanpageController extends Fancrank_Admin_Controller_BaseController
{
	protected $_fanpageId;
	protected $_accessToken;
	
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
		$this-> _fanpageId = $this->_getParam('id');
		
		$fanpageAdminModel = new Model_FanpageAdmins;
		$admins = $fanpageAdminModel->find($this->_auth->getIdentity()->facebook_user_id, $this-> _fanpageId)->count();
		
		$fanpage = new Model_Fanpages();
		$fanpage = $fanpage->find($this->_fanpageId)->current();
		$this->_fanpageProfile = $fanpage;
		//Zend_Debug::dump($token);
		$this->_accessToken = $fanpage ->access_token;
		
		if(empty($admins) || $admins < 1) {
			$this->_helper->json(array('message'=>'authentication failed'));
		} 
	}
	
	public function indexAction() {
		
	}

	public function graphlikesAction(){
		$time = time();
		$range = 7776000;
		$since = $time - $range;
		$until = $time;

		$collector = new Service_FancrankCollectorService(null,  $this->_fanpageId, $this->_accessToken, 'insights');
		$result = $collector->collectFanpageInsight(5, 'likes');
		$likeStats = array();
		foreach ($result as $data) {
			foreach($data->values as $value) {
				$time = explode('T', $value->end_time);
				$newTime = str_replace('-', '/', $time[0]);
				$value->end_time = $newTime;
				$likeStats[] = $value;
			}
		}

		$this->_helper->json($likeStats);
	}
	
	
	public function graphpointsAction(){
		
		$points = new Model_PointLog();
		
		$pointStats = $points ->getFanpagePointLogByHour($this->_fanpageId, '2012-10-01');

		$this->_helper->json($pointStats);
	}
}

?>

