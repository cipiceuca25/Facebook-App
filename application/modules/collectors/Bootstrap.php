<?php
class Collectors_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __initSources()
    {
        return new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV, array('allowModifications' => true));
    }
    
    protected function _initCollectorLog()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
		$logOptions = $config->resources->log;
		$collectorsLogConfig = $config->resources->modules->collectors->log;
		
        $baseFilename = $logOptions->stream->writerParams->stream;
        if ($collectorsLogConfig->partitionStrategy == 'context'){
            $baseFilename = $collectorsLogConfig->path.'/'.APPLICATION_ENV;
        }

        $logFilename = '';
        switch(strtolower($collectorsLogConfig->partitionFrequency)) {
            case 'daily':
                $logFilename = $baseFilename.'_'.date('Y_m_d') .'.log';
                break;

            case 'weekly':
                $logFilename = $baseFilename.'_'.date('Y_W') .'.log';
                break;

            case 'monthly':
                $logFilename = $baseFilename.'_'.date('Y_m') .'.log';
                break;

            case 'yearly':
                $logFilename = $baseFilename.'_'.date('Y') .'.log';
                break;

            default:
                $logFilename = $baseFilename;

        }
		
        $newLogOption = $logOptions->toArray();
		$newLogOption['stream']['writerParams']['stream'] = $logFilename;

        $logger = Zend_Log::factory($newLogOption);
    	Zend_Registry::set('collectorLogger', $logger);
    
    	return $logger;
    }
}
