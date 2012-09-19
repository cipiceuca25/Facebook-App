<?php

class Model_FanRequests extends Model_DbTable_FanRequests
{
	protected $_fanpageId;
	protected $_fanRequestList;
	
	public function __construct() {
		$args = func_get_args();
		$argsCount = func_num_args();
		if (method_exists($this, $constructor ='__construct' .$argsCount)) {
			call_user_func_array(array($this, $constructor), $args);
		}else {
			throw new Exception('NO CONSTRUCTOR: ' . get_class() . $constructor, NULL, NULL);
		}
	}
	
	public function __construct0() {
		parent::__construct();
	}
	
	public function __construct1($fanpageId) {
		parent::__construct();
		$this->_fanpageId = $fanpageId;
		if(!empty($fanpageId)) {
			$where = $this->quoteInto('fanpage_id = ?', $fanpageId);
			$this->_fanRequestList = $this->findAll($where);
		}else {
			throw new Fancrank_Exception_NotFoundException('fanpage not found');
		}
	}
	
	public function getFanRequestList() {
		return $this->_fanRequestList;
	}
	
	public function getFanRequestCount() {
		$where = $this->quoteInto('fanpage_id = ?', $this->_fanpageId);
		return $this->findAll($where)->count();
	}
	
	public function hasFanRequest($fanpageId, $facebookUserId) {
		$where = sprintf('fanpage_id = %s AND facebook_user_id = %s', $fanpageId, $facebookUserId);
		return $this->_fanRequestList = $this->findAll($where)->count() > 0 ? true : false;
	}
}

