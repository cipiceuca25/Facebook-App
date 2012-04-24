<?php

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
			$fanpage->save();

			//TODO: maybe remove from queue list?
			
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
		
		if ($admin->facebook_user_id == $this->_identity->user_id && $fanpage->active) {

			$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
        	$this->config = $sources->get('facebook');

			//install the app
			$client = new Zend_Http_Client;
	        $client->setUri('https://graph.facebook.com/' . $this->_getParam('id') . '/tabs');
	        $client->setMethod(Zend_Http_Client::POST);
	        $client->setParameterPost('access_token', $fanpage->access_token);
	        $client->setParameterPost('app_id', $this->config->client_id);
	    
	        $response = $client->request();

	        //reuturn success or not
	        $body = $response->getBody();
	        $message = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);
die(var_dump($message));
	        if ($message == true) {
	        	$fanpage->installed = TRUE;
	        	$fanpage->save();
	        	echo $body;
	        } else {
	        	echo $message->error->message;
	        }

	       
		} else {
			//send access deinied 403
		}
	}
}
