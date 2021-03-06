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
		$t = $this->_getParam('time');
		$cache = Zend_Registry::get('memcache');
		$cache->setLifetime(1800);
		
		try {
			$adminLikesID = $this->_fanpageId .'_admin_likes';
			//echo $adminLikesID;
			//$cache->remove($adminLikesID );
			if(isset($cache) && !$cache->load($adminLikesID )){
				
				
		
				$collector = new Service_FancrankCollectorService(null,  $this->_fanpageId, $this->_accessToken, 'insights');
				$result = $collector->collectFanpageInsight(5, 'likes');
				
				
				
				//Zend_Debug::dump($this->_fan);
				//Save to the cache, so we don't have to look it up next time
				$cache->save($result, $adminLikesID );
			}else {
					//echo 'memcache look up';
					$result = $cache->load($adminLikesID );
				}
		} catch (Exception $e) {
				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
				//echo $e->getMessage();
		}	
			$date = new Zend_Date();
			
			$date->subDay(1);
			$likeStats = array();
			$diffStats = array();
			$previous = 0;
			$first = true;
			foreach ($result as $data) {
				foreach($data->values as $value) {
						
					$time = explode('T', $value->end_time);
			
					$newTime = str_replace('-', '/', $time[0]);
					$tempdate = new Zend_Date($time[0]);
					switch ($t){
						case 'month':
							if ($tempdate->toString('Y M') == $date->toString('Y M')){
								if ($first){
									$x = 0;
									$first=false;
								}else{
									$x = $value->value - $previous;
								}
								$diffStats[] = array('value'=> $x,'end_time'=> $newTime);
								$value->end_time = $newTime;
								$likeStats[] = $value;
							}
							break;
						case 'week':
							if ($tempdate->toString('Y w') == $date->toString('Y w')){
								if ($first){
									$x = 0;
									$first=false;
								}else{
									$x = $value->value - $previous;
								}
								$diffStats[] = array('value'=> $x,'end_time'=> $newTime);
								$value->end_time = $newTime;
								$likeStats[] = $value;
							}
							break;
						default:
							if ($first){
								$x = 0;
								$first=false;
							}else{
								$x = $value->value - $previous;
							}
							$diffStats[] = array('value'=> $x,'end_time'=> $newTime);
							$value->end_time = $newTime;
							$likeStats[] = $value;
							break;
					}
					$previous = $value->value;
				}
			}
			$a = array();
			$a [] = $likeStats;
			$a [] = $diffStats;
				
			$this->_helper->json($a);
	}
	
	public function graphhomefansAction(){
		$time = $this->_getParam('time');
		$fp = new Model_Fanpages();
		
		$x = $fp -> getFanFirstInteractionGraph( $this->_fanpageId,$time, true);
		$y = $fp -> getFanFirstInteractionGraph( $this->_fanpageId,$time, false);

		$a = array() ;
		$a []=$x;
		$a []=$y ;
		$this->_helper->json($a);
	}
	
	public function graphhomeactivefansAction(){
		
		$time = $this->_getParam('time');
		$fp = new Model_Fanpages();
		
		$x = $fp ->getActiveFanGraph( $this->_fanpageId, $time,  true);
		$y = $fp ->getActiveFanGraph( $this->_fanpageId, $time,  false);
		
		$a = array();
		$a []=$x;
		$a []=$y;
		
		$this->_helper->json($a);
		
	}
	
	public function graphhomefacebookinteractionsAction(){
		
		$time = $this->_getParam('time');
		$fp = new Model_Fanpages();
		
		$x = $fp ->getFacebookInteractionsGraph( $this->_fanpageId,$time,  true);
		$y = $fp ->getFacebookInteractionsGraph( $this->_fanpageId,$time,  false);
		$a = array();
		$a []=$x;
		$a []=$y;
		
		$this->_helper->json($a);
	
	}
	
	public function graphhomefancrankinteractionsAction(){
	
		$time = $this->_getParam('time');
		$fp = new Model_FancrankActivities();
	
		$x = $fp ->getFancrankInteractionsGraph( $this->_fanpageId,$time,  true);
		$y = $fp ->getFancrankInteractionsGraph( $this->_fanpageId,$time,  false);
		
		$a = array();
		$a []=$x;
		$a []=$y;
		
		$this->_helper->json($a);
	
	
	}
	
	public function graphhomefancrankinteractionsuniqueusersAction(){
	
		$time = $this->_getParam('time');
		$fp = new Model_FancrankActivities();
	
		$x = $fp ->getFancrankInteractionsUniqueUsersGraph( $this->_fanpageId,$time,  true);
		$y = $fp ->getFancrankInteractionsUniqueUsersGraph( $this->_fanpageId,$time,  false);
		$a = array();
		$a []=$x;
		$a []=$y;
		
		$this->_helper->json($a);
	
	}
	
	public function graphhomefacebookinteractionsuniqueusersAction(){
	
		$time = $this->_getParam('time');
		$fp = new Model_Fanpages();
	
		$x = $fp ->getFacebookInteractionsUniqueUsersGraph( $this->_fanpageId,$time,  true);
		$y = $fp ->getFacebookInteractionsUniqueUsersGraph( $this->_fanpageId,$time,  false);
		$a = array();
		$a []=$x;
		$a []=$y;
		$this->_helper->json($a);
	
	}

	public function graphhomepointsAction(){
		$time = $this->_getParam('time');
		
		$p = new Model_PointLog();
		$x = $p ->getFanpagePointsGraph($this->_fanpageId, $time, true);
		
		$this->_helper->json($x);
	}
	
	public function newFansTableAction(){
		
		return 'table of new fancrank fans';
	}

	public function newFansGraphAction(){
		
		$fanpage = new Model_Fanpages();
		
		$result = $fanpage -> getFanFirstInteractionDateTable($this->_fanpageId, true);
		
		//Zend_Debug::dump($result);
		
		return 'graph of new fancrank fans';
	}
	
	public function fancrankinteractionsgraphAction(){

		
		$actModel = new Model_FancrankActivities();
		$act = $actModel -> getFancrankInteractionsGraph($this->_fanpageId, null);
		
		$act[0]['total'] = $act[0]['interactions'];
			
		for($i = 1; $i < count($act); $i++ ){
		
			$act[$i]['total'] = $act[$i]['interactions'] +  $act[$i-1]['total'] ;
		
		}
		$this->_helper->json($act);
	}
	
	public function landingpageAction() {

		$this->_helper->layout()->disableLayout();

		$fanpageId = $this->_getParam('id');

		$landingPageImageEnable = $this->_getParam('landingPageImageEnable');
		$landingPageTopfanPeriod = $this->_getParam('landingPageTopfanPeriod');

		$fanpageSettingModel = new Model_FanpageSetting();
		$fanpageSetting = $fanpageSettingModel->findRow($fanpageId);
		//Zend_Debug::dump($fanpageSetting); exit();
		if (!empty($_POST['landing-confirm']) && $fanpageSetting) {
			$coverExtension = substr($this->_getParam('landingCoverFile'), strripos($this->_getParam('landingCoverFile'), '.'));
			$logoExtension = substr($this->_getParam('landingLogoFile'), strripos($this->_getParam('landingLogoFile'), '.'));
			$fanpageSetting->landingpage_image_url = empty($coverExtension) ? '' : 'landing_image' .$coverExtension;
			$fanpageSetting->landingpage_logo_url = empty($logoExtension) ? '' : 'landing_logo' .$logoExtension;
			$fanpageSetting->landingpage_image_enable = empty($landingPageImageEnable) ? 0 : $landingPageImageEnable;
			$fanpageSetting->landingpage_topfan_period = empty($landingPageTopfanPeriod) ? 'week' : $landingPageTopfanPeriod;
			$fanpageSetting->save();
			
			$adminActivityModel = new Model_AdminActivities();
			$dataLog = array();
			$dataLog['activity_type'] = 'admin_change_landingpage_setting';
			$dataLog['event_object'] = '';
			$dataLog['facebook_user_id'] = $this->_auth->getIdentity()->facebook_user_id;
			$dataLog['facebook_user_name'] = $this->_auth->getIdentity()->facebook_user_name;
			$dataLog['fanpage_id'] = $fanpageId;
			$dataLog['target_user_id'] = $fanpageId;
			$dataLog['target_user_name'] = '';
			$dataLog['message'] = 'admin updated landingpage setting';
			$adminActivityModel->insert($dataLog);
		} else {
			echo 'no';
		}
		$this->view->landingPageImageUrl = '';
		$this->view->fanpage_id = $fanpageId;
		$this->render('landingpage');
	}
	
	public function imageuploadAction() {
		try {
			$imageDestination = $this->getImageLocation();
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload//->addValidator('Count', false, 1)     // ensure only 1 file
				->addValidator('Size', false, 3000000) // limit to 100K
				->addValidator('Extension' ,false, 'png,jpg,gif');
			
			$imageFileName = 'landingpage_cover' .strrchr($upload->getFileName(), '.');
			$fullFilePath = $imageDestination .DIRECTORY_SEPARATOR .$imageFileName;
		
			if ($upload->isValid()) {
				$upload->setDestination($imageDestination);
				$upload->addFilter('Rename', array('target' => $fullFilePath, 'overwrite' => true));
				//check upload file
				if ($upload->receive()) {
					echo 'ok';
				}else {
					//TO LOG
					throw new Exception('unable to save');
				}
			}else {
				//TO LOG
				throw new Exception(implode(PHP_EOL, $upload->getErrors()));
			}
		} catch (Exception $e) {
			//TO LOG
			echo $e->getMessage();
		}
		return;
	}
	
	public function logouploadAction() {
		$imageDestination = DATA_PATH .DIRECTORY_SEPARATOR .'images' .DIRECTORY_SEPARATOR .'fanpages'
				.DIRECTORY_SEPARATOR .$this->_fanpageId .DIRECTORY_SEPARATOR .'landingpage';
	
		try {
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload//->addValidator('Count', false, 1)     // ensure only 1 file
			->addValidator('Size', false, 1000000) // limit to 100K
			->addValidator('Extension' ,false, 'jpg,png,gif');
			$imageFileName = 'landing_l' .strrchr($upload->getFileName(), '.');
			$fullFilePath = $imageDestination .DIRECTORY_SEPARATOR .$imageFileName;
	
			if ($upload->isValid()) {
				$upload->setDestination($imageDestination);
				$upload->addFilter('Rename', array('target' => $fullFilePath, 'overwrite' => true));
				//check upload file
				if ($upload->receive()) {
					echo 'ok';
				}else {
					//TO LOG
					throw new Exception('unable to save');
				}
			}else {
				//TO LOG
				throw new Exception(implode(PHP_EOL, $upload->getErrors()));
			}
		} catch (Exception $e) {
			//TO LOG
			echo $e->getMessage();
		}
		return;
	}
	
	public function listitemsAction() {
		$itemModel = new Model_Items();
		$itemList = $itemModel->getFanpageItems($this->_fanpageId);

		$result = array(
				'sEcho'=> 1,
				'iTotalRecords'=> count($itemList),
				'aaData'=> empty($itemList) ? array() : $itemList->toArray()
		);
		
		$this->_helper->json($result);
	}
	
	public function additemAction() {
		$itemName = $this->_getParam('itemName');
		$itemDescription = $this->_getParam('itemDescription');
		$itemPictureUrl = $this->_getParam('itemPictureUrl');
		$itemPoint = $this->_getParam('itemPrice');
	
		$item = array(
				'fanpage_id' => $this->_fanpageId,
				'name' => $itemName,
				'description' => $itemDescription,
				'picture' => $itemPictureUrl,
				'points' => $itemPoint
		);
	
		$itemModel = new Model_Items();
		$itemModel->insert($item);
		echo 'ok';
	}
	
	public function updateitemAction() {
		$itemId = $this->_getParam('itemId');
		$itemModel = new Model_Items();
		$foundItem = $itemModel->findRow($itemId);
		$updateParam = array('name', 'description', 'picture', 'points');
		try {
			if ($foundItem) {
				foreach ($updateParam as $fieldName) {
					$newValue = $this->_getParam($fieldName);
					if (isset($newValue)) {
						if ($newValue != $foundItem->{$fieldName}) {
							$foundItem->{$fieldName} = $newValue;
							$foundItem->save();
						}
						// early terminate, update 1 field at a time
						break;
					}
				}
			}
			echo 'ok';
		} catch (Exception $e) {
			echo 'fail';
		}
	}
	
	public function edititemAction() {
		$itemId = $this->_getParam('itemId');
		$itemName = $this->_getParam('itemName');
		$itemDescription = $this->_getParam('itemDescription');
		$itemPictureUrl = $this->_getParam('itemPictureUrl');
		$itemPoint = $this->_getParam('itemPrice');
		
		$itemModel = new Model_Items();
		$foundItem = $itemModel->findRow($itemId);
		
		try {
			if ($foundItem) {
				$foundItem->name = $itemName;
				$foundItem->description = $itemDescription;
				$foundItem->picture = $itemPictureUrl;
				$foundItem->points = $itemPoint;
				$foundItem->save();
			}
			echo 'ok';
		} catch (Exception $e) {
			echo 'fail';
		}
	}
	
	public function deleteitemAction() {
		$itemModel = new Model_Items();
		$where = $itemModel->quoteInto('fanpage_id = ? and id = ?',$this->_fanpageId, $this->_getParam('itemId'));
		echo $itemModel->delete($where);
	}
	
	public function iteminfoAction() {
		$itemId = $this->_getParam('itemId');
		$itemModel = new Model_Items();
		$foundItem = $itemModel->findRow($itemId);
		$item = array(
				'fanpage_id' => $this->_fanpageId,
				'name' => '',
				'description' => '',
				'picture' => '',
				'points' => 0
		);
		
		if ($foundItem) {
			$item = $foundItem->toArray();
		}
		
		$this->_helper->json($item);
	}
	
	private function getImageLocation() {
		return $imageDestination = DATA_PATH .DIRECTORY_SEPARATOR .'images' .DIRECTORY_SEPARATOR .'fanpages'
				.DIRECTORY_SEPARATOR .$this->_fanpageId .DIRECTORY_SEPARATOR .'landingpage';
	}
	
}

?>

