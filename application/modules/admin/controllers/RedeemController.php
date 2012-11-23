<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    app
 * @package     admin
 * @copyright   Copyright (c) 2012 Francrank
 * @license
 */
class Admin_RedeemController extends Fancrank_Admin_Controller_BaseController
{
	protected $_fanpageId;
	
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));
		//
		if(!$this->_auth->hasIdentity()) {
			$this->_helper->json(array('message'=>'authentication failed'));
			//set the proper navbar
		}
		$this->_fanpageId = $this->_getParam('fanpage_id');
	}
	
	public function indexAction() {
		echo 'admin redeem';
	}
}

?>
