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
    
    //retrieve the fanpage Id from signed request and save it into zend registry with key 'fanpageId'.
    protected function _initFanpageID()
    {
    	try {
    		if(isset($_REQUEST['signed_request'])) {
    			$fb = new Service_FancrankFBService();
    			$fanpageId = $fb->getFanPageId();
    			Zend_Registry::set('fanpageId', $fanpageId);
    			
    			//Zend_Debug::dump($fb->getSignedData());
    		}
    	} catch (Exception $e) {
    		throw new ErrorException($e->getMessage());
    	}
    }
}
