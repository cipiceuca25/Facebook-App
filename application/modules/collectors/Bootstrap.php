<?php
class Collectors_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __initSources()
    {
        return new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV, array('allowModifications' => true));
    }
    
    protected function _initMemcache()
    {
    	$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini',APPLICATION_ENV);
    	$options = $config->memcache->toArray();
    	if (isset($options)) {
    		try {
    			$cache = Zend_Cache::factory(
    					$options['frontend']['type'],
    					$options['backend']['type'],
    					$options['frontend']['options'],
    					$options['backend']['options']
    			);
    			//Zend_Debug::dump($cache);
    			Zend_Registry::set('memcache', $cache);
    			return $cache;
    		} catch (Exception $e) {
    			echo $e->getMessage();
    		}
    	}
    	return;
    }
}
