<?php
/**
 * This is a dummy-router that shouldn't do anything on routing
 */
class Fancrank_Controller_Router_Cli extends Zend_Controller_Router_Abstract implements Zend_Controller_Router_Interface
{
    public function route(Zend_Controller_Request_Abstract $request)
    {
        set_time_limit(0);

        try {
            $opts = new Zend_Console_Getopt(array(
                'env|e-s'           => 'Application environment switch (optional)',
                'module|m=s'        => 'Module name (optional)',
                'controller|c=s'    => 'Controller name (required)',
                'action|a=s'        => 'Action name (required)',
                'verbose|v'         => 'Print verbose output',
                'help'              => 'help option'
            ));

            $opts->setOption('ignoreCase', true);
            $opts->parse();
        } catch (Zend_Console_Getopt_Exception $e) {
            exit($e->getMessage() . PHP_EOL . PHP_EOL . $e->getUsageMessage());
        }

        if (isset($opts->help) or !isset($opts->controller, $opts->action)) {
            exit($opts->getUsageMessage());
        }

        if (!isset($opts->module)) {
            $opts->module = 'default';
        }

        $request->setModuleName($opts->module);
        $request->setControllerName($opts->controller);
        $request->setActionName($opts->action);
        $request->setParams($opts->getRemainingArgs());
    }

    public function assemble($userParams, $name = null, $reset = false, $encode = true)
    {
    }

    public function addConfig()
    {
    }
}
