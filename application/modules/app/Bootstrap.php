<?php
class App_Bootstrap extends Zend_Application_Module_Bootstrap
{
   
    protected function _initAppLog()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$logOptions = $config->resources->log;
		$logFilename = $logOptions->stream->writerParams->stream;
		
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFilename));
		Zend_Registry::set('appLogger', $logger);
		
    	return $logger;
    }
    
    /*
    //retrieve the fanpage Id from signed request and save it into zend registry with key 'fanpageId'.
    protected function _initFanpageID()
    {
    	try {
    		if(isset($_REQUEST['signed_request'])) {
    			$fb = new Service_FancrankFBService();
    			$fanpageId = $fb->getFanPageId();
    			//$frontController = Zend_Controller_Front::getInstance();
    			//$frontController->getRequest()->setParam('current_fanpage_id', $fanpageId);
    			$session = new Zend_Session_Namespace('currentFanpage');
    			$seesion->fanpageId = $fanpageId;
    			//Zend_Debug::dump($fb->getSignedData());
    		}
    	} catch (Exception $e) {
    		throw new ErrorException($e->getMessage());
    	}
    }
    */
    
    protected function _initFanpage() {
		$router     = new Zend_Controller_Router_Rewrite();
		$controller = Zend_Controller_Front::getInstance();
		$uri = $_SERVER['REQUEST_URI'];
		if (preg_match('/^\/page\//', trim($uri))) {
			$nameParts = explode('?', str_replace('/page/', '', $uri));
			$fanpageParam = preg_replace('/\/.*/', '', $nameParts[0]);

			if (is_string($fanpageParam) && ! empty($fanpageParam) && ! is_numeric($fanpageParam)) {
				try {
					// init default db adapter
					$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
					$adapter = Zend_Db::factory($config->resources->db);
					Zend_Db_Table::setDefaultAdapter($adapter);
					
					$fanpageModel = new Model_Fanpages();
					$fanpage = $fanpageModel->findByFanpageUsername($fanpageParam);
					
					// redirect to user app index controller if fanpage exists
					if ($fanpage) {
							$controller->getResponse()->setRedirect('/app/index/index/' .$fanpage->fanpage_id);
					}
				} catch (Exception $e) {
					$log = new Zend_Log();
					$log = Zend_Registry::get('appLogger');
					$log->log($e->getMessage(), Zend_Log::WARN, 'route problem');
				}
			}
		}
    }
    
    protected function _initViewHelper()
    {
    	$view = new Zend_View();
    	$view->addHelperPath(APPLICATION_PATH .'/modules/app/views/helpers', 'Fancrank_View_Helper');
    	$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
    	$viewRenderer->setView($view);
    	Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    }
    
    //log app traffic
    /*
    protected function _initAppLogTraffic() {
    	if (php_sapi_name() != 'cli') {
    		$log = new Zend_Log();
    		$log = Zend_Registry::get('appLogger');
    		$request = implode(",", $_REQUEST);
    		$info = $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] .' extra request params: ' .$request;
    		$log->log($info, Zend_Log::NOTICE, 'traffic info');    		
    	}
    }
    */
}
