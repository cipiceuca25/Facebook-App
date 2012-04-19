<?php

class  Fancrank_Collectors_Controller_BaseController extends Fancrank_Controller_Action
{
    protected $config = null;
    protected $source = null;
    protected $source_name = null;

    public function init()
    {
        $sources = $sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);

        // get the source name
        $this->source_name = $this->getRequest()->getControllerName();

        // set the config property
        $this->config = $sources->get($this->source_name);

        // disable html
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    protected function sendOutput()
    {
        $args = func_get_args();
        $message = array_shift($args);

        if (count($args) > 0) {
            $this->getResponse()->appendBody(vsprintf($message . PHP_EOL, $args));
        } else {
            $this->getResponse()->appendBody($message . PHP_EOL);
        }
    }
}
