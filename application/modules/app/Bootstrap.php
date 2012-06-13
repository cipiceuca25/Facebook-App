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
}
