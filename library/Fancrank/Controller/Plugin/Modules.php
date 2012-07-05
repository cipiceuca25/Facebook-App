<?php
/**
 * Active Module
 *
 * based on:
 * http://binarykitten.me.uk/dev/zend-framework/296-active-module-config-v2.html
 * http://offshootinc.com/blog/2011/02/11/modul-bootstrapping-in-zend-framework/
 **/
class Fancrank_Controller_Plugin_Modules extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $activeModuleName = $request->getModuleName();

        $appBootstrap = $this->getMainBootstrap();

        $activeModuleBootstrap = $this->getActiveBootstrap($appBootstrap, $activeModuleName);

        if ($activeModuleBootstrap instanceof Zend_Application_Module_Bootstrap) {
            $this->processBootstrap($activeModuleBootstrap);
        }
    }

    /**
     * Gets the Main Boostrap Object
     *
     * @return Zend_Application_Bootstrap_Bootstrap Main Bootstrap
     */
    private function getMainBootstrap()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $bootstrap =  $frontController->getParam('bootstrap');
        return $bootstrap;
    }

    /**
     * Gets the Current Active Module's Boostrap Object
     *
     * @param Zend_Application_Bootstrap_Bootstap $appBootstrap The Main Bootstrap
     * @param String $activeModuleName The name to find.
     * @return Zend_Application_Module_Bootstrap Active Module Bootstrap
     */
    private function getActiveBootstrap($appBootstrap, $activeModuleName)
    {
        $moduleList = $appBootstrap->modules;

        if (array_key_exists($activeModuleName, $moduleList)) {
            $activeModule = $moduleList[$activeModuleName];
        } else {
            $activeModule = $appBootstrap;
        }

        return $activeModule;
    }

    /**
     * Process the methods from within the main bootstrap
     *
     * @param Zend_Application_Module_Bootstrap $activeModuleBootstrap The "Active"  Modules's Bootstrap;
     */
    private function processBootstrap($activeModuleBootstrap)
    {
        $methodNames = get_class_methods($activeModuleBootstrap);

        foreach ($methodNames as $key => $method) {
            $runMethod = false;

            if ($this->isInitMethod($method)) {
                $resourceName = substr($method, 6);
                $runMethod = true;
            }

            if ($runMethod) {
                $resource = call_user_func(array($activeModuleBootstrap, $method));

                if (!is_null($resource)) {
                    $this->storeResource($resource, $resourceName, $activeModuleBootstrap);
                }
            }
        }
    }

    /**
     * @param string $method The method name to check
     */
    private function isInitMethod($method)
    {
        $methodNameLength = strlen($method);
        $methodNameLonger = ($methodNameLength > 6);
        $methodNameBeginMatch = '__init' === substr($method, 0, 6);

        return $methodNameLonger && $methodNameBeginMatch;
    }

    /**
     * Store the resource returned by the function so that it can be "bootstrapped"
     *
     * @param misc $resource The Resource to be stored
     * @param string $name the name of the resource
     * @param Zend_Application_Bootstrap_BootstrapAbstract $bootstrap The Bootstrap against which to store the resource
     */
    private function storeResource($resource, $name, $bootstrap)
    {
        // Store the resource.. not sure how to do this yet.. if you do let me know!
        if (null !== $resource) {
            $container = $bootstrap->getContainer();
            $container->{strtolower($name)} = $resource;
        }
    }
}
