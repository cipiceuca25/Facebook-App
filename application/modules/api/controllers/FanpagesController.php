<?php
//could probably do security check in predispatch
class Api_FanpagesController extends Fancrank_API_Controller_BaseController
{
	public function activateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && !$fanpage->active) {
			$fanpage->active = TRUE;
			$fanpage->save();

			//init collection
			Collector::Run('facebook', 'init', array($this->_getParam('id')));
		} else {
			//send access deinied 403
		}
	}

	public function deactivateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && $fanpage->active) {
			$fanpage->active = FALSE;

			if ($fanpage->installed) {
				//uninstall
				$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
	        	$this->config = $sources->get('facebook');

				$response = $this->installTab($this->_getParam('id'), $fanpage->access_token, $this->config->client_id);

		        $body = $response->getBody();
		        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

		        if ($message == true) {
		        	$fanpage->installed = TRUE;
		        	$fanpage->tab_id = $this->_getParam('id'). '/tabs/app_' . $this->config->client_id;
		        } else {
		        	echo $message->error->message;
		        }
	    	}

			$fanpage->save();

		} else {
			//send access deinied 403
		}
	}

	public function installAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && $fanpage->active && !$fanpage->installed) {

			$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
        	$this->config = $sources->get('facebook');

	        //return success or not
	        $response = $this->installTab($this->_getParam('id'), $fanpage->access_token, $this->config->client_id);

	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

	        if ($message == true) {
	        	$fanpage->installed = TRUE;
	        	$fanpage->tab_id = $this->_getParam('id'). '/tabs/app_' . $this->config->client_id;
	            $fanpage->save();
	        } else {
	        	echo $message->error->message;
	        }
	       
		} else {
			//send access deinied 403
		}
	}

	public function uninstallAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && $fanpage->active && $fanpage->installed) {

			$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
        	$this->config = $sources->get('facebook');

	        //reuturn success or not
	        $response = $this->deleteTab($this->_getParam('id'), $fanpage->access_token, $fanpage->tab_id);

	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

	        if ($message == true) {
	        	$fanpage->installed = FALSE;
	        	$fanpage->tab_id = '';
	            $fanpage->save();
	        } else {
	        	echo $message->error->message;
	        }

		} else {
			//send access deinied 403
		}
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
        $client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs');
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
}
