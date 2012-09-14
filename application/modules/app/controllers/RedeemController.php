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
class App_RedeemController extends Fancrank_App_Controller_BaseController
{
	protected $_identity;
	
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//check for user authorization
		if($this->getFrontController()->getRequest()->getActionName() == 'track') {
			return;
		}
		
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		$this->_identity = $this->_auth->getIdentity();
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed'));
			//set the proper navbar
		}
	}
	
	public function indexAction() {

	}
	
	public function confirmAction() {
		$itemId = $this->_getParam('itemId');
		$itemModel = new Model_Items();
		
		$item = $itemModel->findRow($itemId);
		$redeemModel = new Model_RedeemTransactions();
		
		$rankingModel = new Model_Rankings;
		$userLeaderBoardData = array();
		
		$cache = Zend_Registry::get('memcache');
		//$cache->remove($this->_fanpageId .'_' .$user->facebook_user_id);
		try {
			//Check to see if the $fanpageId is cached and look it up if not
			if(isset($cache) && !$cache->load($this->_identity->fanpage_id .'_' .$this->_identity->facebook_user_id)){
				//Look up the $fanpageId
				$userLeaderBoardData['topFans'] = $rankingModel->getUserTopFansRank($this->_identity->fanpage_id, $this->_identity->facebook_user_id);
	
			}else {
				//echo 'memcache look up';
				$userLeaderBoardData = $cache->load($this->_identity->fanpage_id .'_' .$this->_identity->facebook_user_id);
			}
		} catch (Exception $e) {
			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
			//echo $e->getMessage();
		}
		
// 		if(empty($userLeaderBoardData['topFans']['my_rank']) || $userLeaderBoardData['topFans']['my_rank'] > 5) {
// 			return;
// 		}
		
		$date = Zend_Date::now();
		
		if(isset($item->id)) {
			$data = array(
						'fanpage_id' => $this->_identity->fanpage_id,
						'facebook_user_id' => $this->_identity->facebook_user_id,
						'item_id'	=> $item->id,
						'status'	=> 1,
						'created_time'	=> $date->toString( 'yyyy-MM-dd HH:mm:ss' ),
						'updated_time'	=> $date->toString( 'yyyy-MM-dd HH:mm:ss' )
					);
			try {
				$redeemId = $redeemModel->insert($data);
					
				$encryptData['redeem_id'] = $redeemId;
				$encryptData['code'] = 'fancrank';
				$link = $_SERVER['SERVER_NAME'] .'/app/redeem/track?data=' .Fancrank_Crypt::encrypt($encryptData);
				$mailModel = new Fancrank_Mail($this->_identity->facebook_user_email);
				$mailModel->sendMail($link);
				echo 'ok';				
			} catch (Exception $e) {
				echo 'fail';
			}
		}
	}
	
	protected function sendMail($link) {
			
	}
	
	private function encodeLink($data) {
		
	}
	
	public function trackAction() {
		$status = array('process', 'pending', 'shipping');
		try {
			$data = Fancrank_Crypt::decrypt($this->_getParam('data'));
			$redeemModel = new Model_RedeemTransactions();
			if(!empty($data['redeem_id'])) {
				$redeem = $redeemModel->findRow($data['redeem_id']);
				Zend_Debug::dump($status[$redeem->status]);
				
			}
		} catch (Exception $e) {
		}
	}
}

?>
