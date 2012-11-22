<?php
class Fc_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initFcRoute() {
		$router = Zend_Controller_Front::getInstance()->getRouter();
		$rssRoute = new Zend_Controller_Router_Route('rss', 
					array(
						'controller' => 'rss',
						'action' => 'index',
						'module' => 'fc'	
					)
				);
		$router->addRoute('rssRoute', $rssRoute);
	}
}