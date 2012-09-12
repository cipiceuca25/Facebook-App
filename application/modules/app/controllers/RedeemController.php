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
		$badgeId = $this->_getParam('badgeId');
		$badgeModel = new Model_Badges();
		
		$badge = $badgeModel->findRow($badgeId);
		$badgeEventsModel = new Model_BadgeEvents();

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
		
		if(isset($badge->id) && !$badgeEventsModel->hasBadgeEvent($this->_identity->fanpage_id, $this->_identity->facebook_user_id, $badgeId)) {
			$data = array(
						'fanpage_id' => $this->_identity->fanpage_id,
						'facebook_user_id' => $this->_identity->facebook_user_id,
						'badge_id'	=> $badgeId
					);
			$badgeEventsModel->insert($data);
			echo 'ok';
		}
	}
	
}

?>
