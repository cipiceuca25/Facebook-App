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
	protected $_fanpageId;
	
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
		
		$this->_fanpageId = $this->_getParam('id');
	}
	
	public function indexAction() {
		$itemModel = new Model_Items();
		$itemList = $itemModel->getFanpageItems($this->_fanpageId);
		$this->view->itemList = $itemList;
		
		$shippingInfoModel = new Model_ShippingInfo();
		$shippingInfo = $shippingInfoModel->findByUserId($this->_identity->facebook_user_id);
		if ($shippingInfo) {
			$this->view->shippingInfo = $shippingInfo;
		} else {
			$shippingInfo['email'] = $this->_identity->facebook_user_email;
			$shippingInfo['name'] = $this->_identity->facebook_user_name;
			$shippingInfo['address'] = '';
			$shippingInfo['city'] = '';
			$shippingInfo['region'] = '';
			$shippingInfo['country'] = '';
			$shippingInfo['postcode'] = '';
			$this->view->shippingInfo = $shippingInfo;
		}
		
		$this->render('index');
	}
	
	public function confirmAction() {
		$itemId = $this->_getParam('redeemItemId');
		$itemModel = new Model_Items();
		
		$item = $itemModel->findRow($itemId);
		
		if (!$item) {
			echo 'item not found';
			return;
		}

		$redeemModel = new Model_RedeemTransactions();
		$badgeEventsModel = new Model_BadgeEvents();
		// check top fan last week, note: badge id 721 = top_fans 
		
		$fp = new Model_Fanpages();
		$fp = $fp->find($item['fanpage_id'])->current();
		
		if (!$badgeEventsModel->hasBadgeEvent($this->_fanpageId, $this->_identity->facebook_user_id, 1)) {
			echo 'redeemable badge not found';
			return;
		}
		
		try {
			$shippingInfo = $this->getShippingInfo();
			$shippingInfoModel = new Model_ShippingInfo();
			
			$shippingId = '';
			$foundShipping = $shippingInfoModel->findByUserId($this->_identity->facebook_user_id);
			if ($foundShipping) {
				foreach ($shippingInfo as $key => $field) {
					$foundShipping->{$key} = $field;
				}
				$foundShipping->save();
				$shippingId = $foundShipping->id;
			} else {
				$shippingId = $shippingInfoModel->insert($shippingInfo);
			}
			
			$date = Zend_Date::now();
			
			if(isset($item->id) && !empty($shippingId)) {
				$data = array(
						'fanpage_id' => $this->_fanpageId,
						'facebook_user_id' => $this->_identity->facebook_user_id,
						'item_id'	=> $item->id,
						'status'	=> 1,
						'shipping_info_id' => $shippingId,
						'created_time'	=> $date->toString( 'yyyy-MM-dd HH:mm:ss' ),
						'updated_time'	=> $date->toString( 'yyyy-MM-dd HH:mm:ss' )
				);
				
				$redeemId = $redeemModel->insert($data);
				
				//update activity log
				$activityData['activity_type'] = 'redeem_by_badge';
				$activityData['event_object'] = $redeemId;
				$activityData['facebook_user_id'] = $this->_identity->facebook_user_id;
				$activityData['facebook_user_name'] = $this->_identity->facebook_user_name;
				$activityData['fanpage_id'] = $this->_fanpageId;
				$activityData['target_user_id'] = $this->_fanpageId;
				$activityData['target_user_name'] = '';
				$activityData['message'] = 'redeem item';
				
				$activityModel = new Model_FancrankActivities();
				$activityModel->insert($activityData);
				
				$encryptData['redeem_id'] = $redeemId;
				$encryptData['code'] = 'fancrank';
				$link = $_SERVER['SERVER_NAME'] .'/app/redeem/track?data=' .Fancrank_Crypt::encrypt($encryptData);
				
				
				$html = new Zend_View();
				$html->setScriptPath(APPLICATION_PATH . '/modules/app/views/scripts/redeem/');
				$html->assign('link', $link);
				$html->assign('date', date("F j, Y"));
				$html->assign('shipping',$shippingInfo);
				$html->assign('item',$item);
				$html->assign('fanpage',$fp);
				
				$bodyText = $html->render('emailTemplate.phtml');
				Zend_Debug::dump($shippingInfo);
				$mailModel = new Fancrank_Mail($shippingInfo['email']);
			
				$mailModel->setSubject('FanCrank: Your Request has been Submitted');
				$mailModel->setFrom('redemption@fancrank.com', 'FanCrank Redemptions');

				
				$mailModel->sendMail($bodyText);
			}
			echo 'ok';	
		} catch (Fancrank_Exception_InvalidParameterException $f) {
			echo 'invalid shipping info';
			return;
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
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
		
		Zend_Debug::dump($redeem);
		
	}
	
	private function getShippingInfo() {
		$shippingInfo = array();
		$shippingInfo['name'] = $this->_getParam('contactName');
		$shippingInfo['address'] = $this->_getParam('address');
		$shippingInfo['city'] = $this->_getParam('city');
		$shippingInfo['region'] = $this->_getParam('region');
		$shippingInfo['country'] = $this->_getParam('country');
		$shippingInfo['postcode'] = $this->_getParam('postcode');
		$shippingInfo['email'] = $this->_getParam('trackingEmail');
		$shippingInfo['confirmEmail'] = $this->_getParam('confirmEmail');

		foreach ($shippingInfo as $field) {
			if (empty($field)) {
				throw new Fancrank_Exception_InvalidParameterException();
			}
		}
		
		if ($shippingInfo['email'] != $shippingInfo['confirmEmail']) {
			throw new Fancrank_Exception_InvalidParameterException();
		}

		$shippingInfo['facebook_user_id'] = $this->_identity->facebook_user_id;
		unset($shippingInfo['confirmEmail']);
		return $shippingInfo;
	}
	
	public function updateshippingAction() {
		$shippingInfoModel = new Model_ShippingInfo();
		$foundShipping = $shippingInfoModel->findByUserId($this->_identity->facebook_user_id);
		if ( !empty($_POST['confirmEmail']) ) {
			$shippingInfo = $this->getShippingInfo();
			$shippingId = '';
			if ($foundShipping) {
				foreach ($shippingInfo as $key => $field) {
					$foundShipping->{$key} = $field;
				}
				$foundShipping->save();
			} else {
				$shippingId = $shippingInfoModel->insert($shippingInfo);
			}
			echo 'ok';
		} else {
			if ($foundShipping) {
				$this->view->shippingInfo = $foundShipping->toArray();
			}
			$this->render('updateshipping');
		} 
	}
}

?>
