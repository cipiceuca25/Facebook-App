<?php
require_once APPLICATION_PATH .'/../library/Collector.php';
class Api_FanpagesController extends Fancrank_API_Controller_BaseController
{
	protected $_config;
	
	public function init() {
		//override existing enviroment configuration
		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', 'production');
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
				$fanpage->active = (int) TRUE;
				$fanpage->save();
				Collector::run(null, $this->_getParam('id'), $fanpage->access_token, 'init');
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
				//uninstall
				//$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
	        	//$this->config = $sources->get('facebook');

				$response = $this->deleteTab($this->_getParam('id'), $fanpage->access_token, $this->_config->client_id);

		        $body = $response->getBody();
		        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

		        if (!isset($message->error)) {
		        	$fanpage->installed = (int) FALSE;
		        	$fanpage->fanpage_tab_id = '';
		        } else {
		        	$fanpage->active = (int) TRUE;
		        	echo $message->error->message;
		        	$this->_response->setHttpResponseCode(400);
		        }
	    	}

			$fanpage->save();

		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
		}
	}

	public function installAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($fanpage->active && !$fanpage->installed) {

	        $response = $this->installTab($this->_getParam('id'), $fanpage->access_token, $this->_config->client_id);

	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

	        if (!isset($message->error)) {
	        	$fanpage->installed = (int) TRUE;
	        	$fanpage->fanpage_tab_id = $this->_getParam('id'). '/tabs/app_' . $this->_config->client_id;
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
		
	}
}
