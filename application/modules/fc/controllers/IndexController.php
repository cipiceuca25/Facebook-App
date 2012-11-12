<?php

class Fc_IndexController extends Fancrank_Admin_Controller_BaseController
{

    public function preDispatch()
    {
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
        
        //Zend_Debug::dump($appInfo);
        
        $adminLogModel = new Model_AdminActivities();
        
        //echo 'page admin activities..............' . '<br/>';
        $adminLog = $adminLogModel->getAllActivitiesSince(0);
        //Zend_Debug::dump($adminLog->toArray());
        
		$pointLogModel = new Model_PointLog();
		$pointLog = $pointLogModel->findAll(null, null, 100)->toArray();
		//Zend_Debug::dump($pointLog);
		
        //echo 'cron log..............' . '<br/>'; 
        $cronLogModel = new Model_CronLog();
        $cronLog = $cronLogModel->fetchAll();
        //Zend_Debug::dump($cronLog->toArray());
        
        if (!($query = $this->_request->getParam('q'))) {
        }
        
        $this->view->insightData = $insightData;
        $this->view->appInfo = $appInfo;
        $this->view->adminLog = $adminLog;
        $this->view->cronLog = $cronLog;
        $this->view->pointLog = $pointLog;
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
		$counter = 3;
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
				}
			}
				
			//early terminate
			if($counter < 1) break;
		}
		return $result;
	}
    
}

