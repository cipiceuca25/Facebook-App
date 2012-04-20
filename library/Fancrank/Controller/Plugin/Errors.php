<?php
class Fancrank_Controller_Plugin_Errors extends Zend_Controller_Plugin_Abstract
{
    // module specific errors
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $frontController = Zend_Controller_Front::getInstance();

        $error = $frontController->getPlugin('Zend_Controller_Plugin_ErrorHandler');

        $error->setErrorHandlerModule($request->getModuleName());
    }
}
