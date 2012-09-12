<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    app
 * @package     app
 * @copyright   Copyright (c) 2012 Francrank
 * @license
 */
class App_RedeemController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed'));
			//set the proper navbar
		}
	}
	
	public function indexAction() {

	}
	
	public function listbadgesAction() {

	}
	
	public function confirmAction() {
		
	}
	
}

?>
