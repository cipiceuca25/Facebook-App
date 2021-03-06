<?php
require_once APPLICATION_PATH .'/../library/Collector.php';
class Api_FanpagesController extends Fancrank_API_Controller_BaseController
{
	protected $_config;
	protected $_fanpageId;
	protected $_accessToken;
	
	public function init() {
		//override existing enviroment configuration
		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
		$this->_config = $sources->get('facebook');
		
		parent::init();
	}
	
	//security check, Note: we could implement with zend_acl for better control
	public function preDispatch() {
		
		parent::preDispatch();

		$fanpage_admin_model = new Model_FanpageAdmins;
		$admins = $fanpage_admin_model->findByFanpageId($this->_getParam('id'));
		
		$adminUserFound = false;
		foreach($admins as $admin) {
			if ($this->_identity->facebook_user_id === $admin->facebook_user_id) {
				$adminUserFound = true;
				$this->_fanpageId = $this->_getParam('id');
				$this->_accessToken = $this->_identity->facebook_user_access_token;				
				break;
			}
		}

		if (!$adminUserFound) {
			$this->_response->setHttpResponseCode(403);
		}
	}
	
	public function activateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		try {
			if ( !$fanpage->active ) {
				$dataLog = array();
				$dataLog['activity_type'] = 'admin_activate_fanpage';
				$dataLog['event_object'] = '';
				$dataLog['facebook_user_id'] = $this->_identity->facebook_user_id;
				$dataLog['facebook_user_name'] = $this->_identity->facebook_user_name;
				$dataLog['fanpage_id'] = $fanpage->fanpage_id;
				$dataLog['target_user_id'] = $fanpage->fanpage_id;
				$dataLog['target_user_name'] = '';
				$dataLog['message'] = 'admin activated a fanpage';
				$adminActivityModel = new Model_AdminActivities();
				$adminActivityModel->insert($dataLog);
				
				$fanpage->active = (int) TRUE;
				$fanpage->save();
				Collector::run(null, $this->_getParam('id'), $this->_identity->facebook_user_id, $fanpage->access_token, 'init');
			}
			return;			
		} catch (Exception $e) {
			$this->_response->setHttpResponseCode(400);
		}
	}

	public function deactivateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($fanpage->active) {
			$fanpage->active = (int) FALSE;
			
			if ($fanpage->installed) {
				$fanpage->installed = (int) FALSE;
				//uninstall
				//$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
	        	//$this->config = $sources->get('facebook');
				$facebook = new Service_FancrankFBService();
				
				if($facebook->isFanpageInstalledApp($this->_getParam('id'))) {

					$response = $this->deleteTab($this->_getParam('id'), $fanpage->access_token, $this->_config->client_id);
					
					$body = $response->getBody();
					$message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);
					
					if (!isset($message->error)) {
						$fanpage->fanpage_tab_id = '';
					} else {
						$fanpage->active = (int) TRUE;
						$fanpage->installed = (int) TRUE;
						echo $message->error->message;
						$this->_response->setHttpResponseCode(400);
					}					
				}
	    	}

			$fanpage->save();
			
			$dataLog = array();
			$dataLog['activity_type'] = 'admin_deactivate_fanpage';
			$dataLog['event_object'] = '';
			$dataLog['facebook_user_id'] = $this->_identity->facebook_user_id;
			$dataLog['facebook_user_name'] = $this->_identity->facebook_user_name;
			$dataLog['fanpage_id'] = $fanpage->fanpage_id;
			$dataLog['target_user_id'] = $fanpage->fanpage_id;
			$dataLog['target_user_name'] = '';
			$dataLog['message'] = 'admin deactivated a fanpage';
			$adminActivityModel = new Model_AdminActivities();
			$adminActivityModel->insert($dataLog);
		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
		}
	}

	public function installAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($fanpage->active && !$fanpage->installed) {

			$facebook = new Service_FancrankFBService();
			if($facebook->isFanpageInstalledApp($this->_getParam('id'))) {
				echo 'App already installed';
				$fanpage->installed = 1;
				$fanpage->save();
				$this->_response->setHttpResponseCode(400);
				return;
			}
			
	        $response = $this->installTab($this->_getParam('id'), $fanpage->access_token, $this->_config->client_id);

	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

	        if (!isset($message->error)) {
	        	$fanpage->installed = (int) TRUE;
	        	$fanpage->fanpage_tab_id = $this->_getParam('id'). '/tabs/app_' . $this->_config->client_id;
	            $fanpage->save();
	            
	            $dataLog = array();
	            $dataLog['activity_type'] = 'admin_install_fanpage';
	            $dataLog['event_object'] = '';
	            $dataLog['facebook_user_id'] = $this->_identity->facebook_user_id;
	            $dataLog['facebook_user_name'] = $this->_identity->facebook_user_name;
	            $dataLog['fanpage_id'] = $fanpage->fanpage_id;
	            $dataLog['target_user_id'] = $fanpage->fanpage_id;
	            $dataLog['target_user_name'] = '';
	            $dataLog['message'] = 'admin installed a fanpage';
	            $adminActivityModel = new Model_AdminActivities();
	            $adminActivityModel->insert($dataLog);
	        } else {
	        	echo $message->error->message;
	        	$this->_response->setHttpResponseCode(400);
	        }
	       
		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
		}
	}

	public function uninstallAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($fanpage->active && $fanpage->installed) {
	        //reuturn success or not
	        $response = $this->deleteTab($this->_getParam('id'), $fanpage->access_token, $fanpage->fanpage_tab_id);

	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

	        if (!isset($message->error)) {
	        	$fanpage->installed = FALSE;
	        	$fanpage->fanpage_tab_id = '';
	            $fanpage->save();
	        } else {
	        	echo $message->error->message;
	        	$this->_response->setHttpResponseCode(400);
	        }

		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
		}
	}
	
	public function upgradeAction() {
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		$level = $this->_getParam('level');
		
		if (isset($fanpage->fanpage_level) && $fanpage->fanpage_level != $level) {
			//reuturn success or not
			try {
				$oldLevel = $fanpage->fanpage_level;
				switch($level)	{
					case 1: $fanpage->fanpage_level = 1; break;
					case 2: $fanpage->fanpage_level = 2; break;
					case 3: $fanpage->fanpage_level = 3; break;
					case 4: $fanpage->fanpage_level = 4; break;
					default: throw new Fancrank_Exception_InvalidParameterException('invalid parameter'); break;
				}
				$fanpage->save();
				
				$dataLog = array();
				$dataLog['activity_type'] = 'admin_upgrade_fanpage';
				$dataLog['event_object'] = '';
				$dataLog['facebook_user_id'] = $this->_identity->facebook_user_id;
				$dataLog['facebook_user_name'] = $this->_identity->facebook_user_name;
				$dataLog['fanpage_id'] = $fanpage->fanpage_id;
				$dataLog['target_user_id'] = $fanpage->fanpage_id;
				$dataLog['target_user_name'] = '';
				$dataLog['message'] = "admin upgraded fanpage level from $oldLevel to $level";
				$adminActivityModel = new Model_AdminActivities();
				$adminActivityModel->insert($dataLog);
			} catch (Fancrank_Exception_InvalidParameterException $e) {
				echo $e->getMessage();
				$this->_response->setHttpResponseCode(400);
			} catch (Exception $e) {
				echo 'unable to upgrade';
				$this->_response->setHttpResponseCode(400);
			}
		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
			return;
		}
	}

	public function previewAction()
	{
		//$model = new Model_TopFans;
		$model = new Model_Rankings;
  		$this->view->top_fans = $model->getTopFans($this->_getParam('id'));
  		$this->view->most_popular = $model->getMostPopular($this->_getParam('id'));
  		$this->view->top_talker = $model->getTopTalker($this->_getParam('id'));
      	$this->view->top_clicker = $model->getTopClicker($this->_getParam('id'));
	}

	private function installTab($fanpage_id, $access_token, $app_id) 
	{
		//install the tab
		$client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs');
        $client->setMethod(Zend_Http_Client::POST);
        $client->setParameterPost('access_token', $access_token);
        $client->setParameterPost('app_id', $app_id);
    
        return $client->request();
	}

	private function deleteTab($fanpage_id, $access_token, $tab_id) 
	{
		//delete the tab
		$client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs/app_' .$this->_config->client_id);
        $client->setMethod(Zend_Http_Client::DELETE);
        $client->setParameterPost('access_token', $access_token);
        $client->setParameterPost('tab_id', $tab_id);
    
        return $client->request();
	}

	private function getTabs($fanpage_id, $access_token)
	{
		$client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet('access_token', $access_token);

        $response =  $client->request();
        return Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
	}
	
	public function setthemeAction() {
		try {
			$fanpageTheme = $this->_request->getParam('fanpageTheme');
			if(Model_FanpageThemes::isValidTheme($fanpageTheme)) {
				$colorChoice = new Model_UsersColorChoice;
				$colorChoice ->change($this->_fanpageId, $fanpageTheme);
				$this->_helper->json(array('code'=>'200', 'message'=>'ok'));				
			}else {
				$this->_helper->json(array('code'=>'400', 'message'=>'invalid theme input'));
			}
		} catch (Exception $e) {
			$this->_response->setHttpResponseCode(400);
		}
	}
	
	public function putAction() {
		$imageDestination = DATA_PATH .'/images/fanpages';
		try {
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload//->addValidator('Count', false, 1)     // ensure only 1 file
					->addValidator('Size', false, 1000000) // limit to 100K
					->addValidator('Extension' ,false, 'jpg,png,gif');
			$imageFileName = $this->_fanpageId .'_picture' .strrchr($upload->getFileName(), '.');
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
			}
		} catch (Exception $e) {
			//TO LOG
			$this->view->message = $e->getMessage();
			$this->_response->setHttpResponseCode(400);
		}
	}
	
	public function analysticAction() {
		$time = time();
		$range = 7776000;
		$since = $time - $range;
		$until = $time;
			
		$fanpageId = $this->_fanpageId;
		$type = $this->_getParam('type');

		$collector = new Service_FancrankCollectorService(null, $fanpageId, $this->_accessToken, 'insights');
		$result = $collector->collectFanpageInsight(5, $type);
		$likeStats = array();
		foreach ($result as $data) {
			foreach($data->values as $value) {
				$time = explode('T', $value->end_time);
				$newTime = str_replace('-', '/', $time[0]);
				$value->end_time = $newTime;
				$likeStats[] = $value;
			}
		}
		 
		//Zend_Debug::dump($likeStats); exit();
		//asort($likeStats);
		//Zend_Debug::dump($likeStats);
		$this->_helper->json($likeStats);
	}
	
	public function changeAction() {
		$data['profile_image_enable'] = $this->_getParam('profile_image_enable');
		$this->_helper->json(array('code'=>'200', 'message'=>'ok', 'profile_image_enable'=>$data['profile_image_enable']));
	}
	
	public function exportAction() {
		$fanpageId = $this->_getParam('id');
		$filename = $fanpageId .'_' .time() . '.csv';

		$result = array();
		print_r($result);
		
		$this->_helper->contextSwitch()->addContext('csv',
				array('suffix' => 'csv',
						'headers' => array('Content-Type' => 'application/csv',
						'Content-Disposition:' => 'attachment; filename="'. $filename.'"')))->initContext('csv');
	}
	
	/*
	public function pictureAction() {
		$imageDestination = DATA_PATH .'/images/fanpages';
		$imageFileName = $this->_fanpageId .'_picture';
		$fullFilePath = $imageDestination .DIRECTORY_SEPARATOR .$imageFileName;
		
		if(!file_exists($fullFilePath))
		{
			return false;
		}
		
		$image = readfile($fullFilePath);
		
		header('Content-Type: image/jpeg');
		
		$this->getResponse()
		->setHeader('Content-Type', 'image/jpeg/png/gif');
		
		$this->view->profileImage = $image;
	}
	*/
}