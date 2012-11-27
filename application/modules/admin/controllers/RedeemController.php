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
		$this->_fanpageId = $this->_getParam('id');
		
		if (empty($this->_fanpageId)) {
			return;
		}
	}
	
	public function indexAction() {
		echo 'admin redeem';
	}

	public function approveAction() {
		$redeemId = $this->_getParam('redeemId');
		$redeemTransactionModel = new Model_RedeemTransactions();
		$redeemTransaction = $redeemTransactionModel->findRow($redeemId);
		
		if ($redeemTransaction) {
			$redeemTransaction->status = 2;
			$redeemTransaction->save();
			echo 'ok';
		}
	}
	
	public function disapproveAction() {
		$redeemId = $this->_getParam('redeemId');
		$redeemTransactionModel = new Model_RedeemTransactions();
		$redeemTransaction = $redeemTransactionModel->findRow($redeemId);
	
		if ($redeemTransaction) {
			$redeemTransaction->status = 0;
			$redeemTransaction->save();
			echo 'ok';
		}
	}
	
	public function historyAction() {
		$redeemTransactionModel = new Model_RedeemTransactions();
		$historyList = $redeemTransactionModel->getRedeemHistory($this->_fanpageId);
		
		$result = array(
				'sEcho'=> 1,
				'iTotalRecords'=> 10,
				'iTotalDisplayRecords'=> 10,
				'aaData'=> empty($historyList) ? array() : $historyList->toArray()
		);
		
		$this->_helper->json($result);
	}
	
	public function requestAction() {
		$redeemTransactionModel = new Model_RedeemTransactions();
		$requestList = $redeemTransactionModel->getPendingOrdersListByFanpageId($this->_fanpageId);
		
		$result = array(
				'sEcho'=> 1,
				'iTotalRecords'=> 10,
				'iTotalDisplayRecords'=> 10,
				'aaData'=> empty($requestList) ? array() : $requestList->toArray()
		);
		
		$this->_helper->json($result);
	}
}

?>
