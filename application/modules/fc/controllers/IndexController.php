<?php
class Fc_IndexController extends Fancrank_Fc_Controller_BaseController
{

    public function preDispatch() {
    	parent::preDispatch();
        $this->view->layout()->disableLayout();
    }

    public function indexAction()
    {
        //get the amount of fanpages
        $fanpage_model = new Model_Fanpages;
        $this->view->fanpages = $fanpage_model->countAll();

        $fans_model = new Model_Fans;
        $this->view->fans = $fans_model->countAll();
        
        $insightData = $this->getInsightData();
        $appInfo = $this->insightDataParser($insightData);
        
        if (!($query = $this->_request->getParam('q'))) {
        }
        
        $this->view->appInfo = $appInfo;
        //$this->view->graphData = $graphData;
    }
    
    public function insightAction() {
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$fanpageId = $this->_request->getParam('id');
    	
    	Zend_Debug::dump($this->getInsightData());
    }
    
	private function query($param) {
		$result = array();
		switch ($param) {
			case 'basic' : break;
			case 'full' : break;
		}
		
		return $result;
	}
	
	private function getInsightData() {
		$fancrankFBService = new Service_FancrankFBService();
		 
		$appInsightId = 'app_insights';
		
		$insightData = array();
		try {
			$cache = Zend_Registry::get('memcache');
		
			if(isset($cache) && !$cache->load($appInsightId)){
				//Look up the facebook graph api
				//echo 'look up facebook graph api';
		
				$appId = $fancrankFBService->getAppId();
				$appAccessToken = $fancrankFBService->getAppAccessToken();
		
				// note: zend client setUri not accept the app access token format
				$result = $fancrankFBService->api("/$appId/insights", 'GET', array('access_token'=>$appAccessToken));
				if(!empty($result['data'])) {
					$insightData = $result['data'];
					//Save to the cache, so we don't have to look it up next time
					$cache->save($insightData, $appInsightId, array(), 72000);
				}
			}else {
				//echo 'memcache look up';
				$insightData = $cache->load($appInsightId);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return $insightData;
	}
	
	private function insightDataParser($insightData) {
		$result = array();
		$counter = 6;
		foreach ($insightData as $data) {
			if(preg_match('/\/day$/', $data['id'])) {
				switch($data['name']) {
					case 'application_installation_adds_unique' :
						if(!empty($data['values'])) {
							$value = $data['values'][sizeof($data['values'])-1];
							$result['application_installation_adds_unique'] = empty($value['value']) ? 0 : $value['value'];
						}
						$counter--;
						break;
					case 'application_installation_removes_unique' :
						if(!empty($data['values'])) {
							$value = $data['values'][sizeof($data['values'])-1];
							$result['application_installation_removes_unique'] = empty($value['value']) ? 0 : $value['value'];
						}
						$counter--;
						break;
					case 'application_active_users' :
						if(!empty($data['values'])) {
							$value = $data['values'][sizeof($data['values'])-1];
							$result['application_active_users'] = empty($value['value']) ? 0 : $value['value'];
						}
						$counter--;
						break;
					case 'application_api_calls' :
						if(!empty($data['values'])) {
							$value = $data['values'][sizeof($data['values'])-1];
							$result['application_api_calls'] = empty($value['value']) ? 0 : $value['value'];
						}
						$counter--;
						break;
					case 'application_api_errors' :
						if(!empty($data['values'])) {
							$value = $data['values'][sizeof($data['values'])-1];
							$result['application_api_errors'] = empty($value['value']) ? 0 : $value['value'];
						}
						$counter--;
						break;																		
				}
			}
				
			//early terminate
			if($counter < 1) break;
		}
		return $result;
	}
    
	public function testAction() {
		echo 'test';
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function appinfoAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		
		//get the amount of fanpages
		$fanpage_model = new Model_Fanpages;
		$appInfo['fanpages'] = $fanpage_model->countAll();
		
		$fans_model = new Model_Fans;
		$appInfo['fans'] = $fans_model->countAll();
		
		$insightData = $this->getInsightData();
		$appInfo['basicInsight'] = $this->insightDataParser($insightData);
		
		//Zend_Debug::dump($appInfo);
		$this->_helper->json($appInfo);
	}
	
	public function fanpageAction() {
		$fanpageModel = new Model_Fanpages();
		$fanpageList  = array();
		try {
			$fanpageList = $fanpageModel->findAll()->toArray();
		} catch (Exception $e) {
			// tolog
		}
		$this->_helper->json($fanpageList);
	}
	
	public function showpointlogAction() {
		$pointLogModel = new Model_PointLog();
		$pointLog = $pointLogModel->findAll(null, null, 100)->toArray();
		Zend_Debug::dump($pointLog);
		$this->_helper->json(array());
	}
	
	public function showcronlogAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		$cronLogModel = new Model_CronLog();
		$cronLog = $cronLogModel->fetchAll();
		
		$result = array();
		foreach ($cronLog->toArray() as $cron) {
			$cron['access_token'] = '*************************';
			$cron['facebook_user_access_token'] = '*************************';
			$result[] = $cron;
		}
		
		Zend_Debug::dump($result);
		$this->_helper->json(array());
	}
	
	public function showactivitiesAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		$adminLogModel = new Model_AdminActivities();
		$adminLog = $adminLogModel->getAllActivitiesSince(0);
		Zend_Debug::dump($adminLog);
		$this->_helper->json(array());
	}
	
	public function facebookuserAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		
		$facebookuserModel = new Model_FacebookUsers();
		
		$facebookuserList = $facebookuserModel->fetchAll('facebook_user_access_token != ""', null, 1000);
		
		$result = array();
		foreach ($facebookuserList->toArray() as $facebookuser) {
			
			$facebookuser['facebook_user_access_token'] = '*************************';
			$facebookuser['facebook_user_birthday'] = '****************';
			$result[] = $facebookuser;
		}
		
		Zend_Debug::dump($result);
		$this->_helper->json(array());
	}
}

?>