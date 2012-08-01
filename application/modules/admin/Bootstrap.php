<?php
class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
   
    protected function _initViewHelper()
    {
    	$view = new Zend_View();
		$view->addHelperPath(APPLICATION_PATH .'/modules/admin/views/helpers', 'Admin_View_Helper');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    }
    
}
