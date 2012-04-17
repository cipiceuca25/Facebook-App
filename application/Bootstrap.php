<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initREST()
    {
        $frontController = Zend_Controller_Front::getInstance();

        // set custom request object
        $frontController->setRequest(new REST_Controller_Request_Http);
        $frontController->setResponse(new REST_Response);

        // add the REST route for the API module only
        $restRoute = new Zend_Rest_Route($frontController, array(), array('api'));
        $frontController->getRouter()->addRoute('rest', $restRoute);
    }

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
                )
            )
        ));
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

    protected function _initLibraryAutoloader()
    {
        return $this->getResourceLoader()->addResourceType('library', 'library', 'library');
    }
}

