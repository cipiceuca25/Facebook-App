<?php

class Fancrank_Controller_Action extends Zend_Controller_Action
{
	
    public function getModuleBootstrap()
    {
        // get the request object 
        $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        return $this->getInvokeArg('bootstrap')->modules->{$module};
    }

    public function getParams()
    {
        if (is_array(func_get_arg(0))) {
            $filter = array_flip(func_get_arg(0));
        } else {
            $filter = array_flip(func_get_args());
        }

        $params = $this->getAllParams();

        return array_intersect_key($params, $filter);
    }

    public function getConfig($key = null) {
        return Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Config')->get($key);
    }

    public function getAllParams()
    {
        $params = $this->_request->getParams();

        // remove zend_application params
        unset($params['module'], $params['controller'], $params['action'], $params['format'], $params['id']);

        return $params;
    }
}
