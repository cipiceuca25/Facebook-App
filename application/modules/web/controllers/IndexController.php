<?php

class Web_IndexController extends Zend_Controller_Action
{
   
	public function preDispatch() {
    	try {
    		$username = $this->_getParam('username');
    		
    		$userAgent = Fancrank_Http_UserAgent::getInstance();
    		$device = $userAgent->getDevice();
    		$deviceName = $userAgent->getDeviceName();
    	
    		$fancrankFBService = new Service_FancrankFBService();
    		$appId = $fancrankFBService->getAppId();
    		
    		$fanpageModel = new Model_Fanpages();
    		
    		$fanpage = null;
    		
    		if (is_numeric($username)) {
    			$fanpage = $fanpageModel->findRow($username);
    		} else if (is_string($username)) {
    			$fanpage = $fanpageModel->findByFanpageUsername($username);
    		}

    		if (empty($fanpage)) {
    			return;
    		}
    		
    		if ($device->getFeature('is_mobile')) {
    			$this->_redirect('/app/index/index/' .$fanpage->fanpage_id);
    		} else {
    			$this->_redirect("http://www.facebook.com/$fanpage->fanpage_id?sk=app_$appId");
    		}
    	} catch (Exception $e) {
    		//echo $e->getMessage();
    	}
    }

    public function indexAction() {
		throw new Zend_Controller_Action_Exception('This page does not exist', 404);
    }
}

