<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initResourceLoader()
    {
        return new Zend_Loader_Autoloader_Resource(array(
            'namespace' => '',
            'basePath'  => APPLICATION_PATH,
            'resourceTypes' => array(
                'model'   => array(
                    'namespace' => 'Model',
                    'path'      => 'models',
                ),

                'table' => array(
                    'namespace' => 'Model_DbTable',
                    'path'      => 'models/DbTable',
                ),

            	'service' => array(
            			'namespace' => 'Service',
            			'path'      => 'services',
            	),            		
            )
        ));
    }

    protected function _initAutoloading()
    {
        Zend_Loader_Autoloader::getInstance()->pushAutoloader(new Fancrank_Autoloader());
    }

    public function _initRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();

        if (php_sapi_name() == 'cli') {
            // special conditions for command line important for collectors
            $frontController->setRouter(new Fancrank_Controller_Router_Cli);
            $frontController->setRequest(new Zend_Controller_Request_Http);
            $frontController->setResponse(new Zend_Controller_Response_Cli);
        } else {
            // set custom request object
            $frontController->setRequest(new REST_Controller_Request_Http);
            $frontController->setResponse(new REST_Response);

            // add the REST route for the API module only
            $restRoute = new Fancrank_Rest_Route($frontController, array(), array('api'));
            $frontController->getRouter()->addRoute('rest', $restRoute);
        }
    }

    protected function _initTimesZone()
    {
        date_default_timezone_set('UTC');
    }

    /**
     * keep the config object handy
     **/
    protected function _initConfig()
    {
        return new Zend_Config($this->getOptions());
    }

    protected function _initLogger()
    {
        $formatter = new Zend_Log_Formatter_Simple('%timestamp% [%path%] %priorityName% (%priority%): %message%' . PHP_EOL);

        $file = new Zend_Log_Writer_Stream(DATA_PATH . '/logs/app.log');
        $file->setFormatter($formatter);

        $logger = new Zend_Log();
        $logger->addWriter($file);

        if (php_sapi_name() == 'cli') {
            // TODO: make into a real path
            $logger->setEventItem('path', 'CLI');
        } else {
            $logger->setEventItem('path', Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        }

        return $logger;
    }
}

