<?php

class Model_Fans extends Model_DbTable_Fans
{
	const BASE_XP = 200;
	const MAX_LEVEL = 60;
	const LEVEL_FACTOR = 2;
	
	protected $_fanProfile;
	protected $_isNew;
	protected $_newBalance;
	
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
		$this->_isNew = true;
		$this->_fanProfile = null;
		$this->_newBalance = 200;
	}
	
	public function __construct2($facebook_user_id, $fanpage_id) {
		parent::__construct();
		if(! is_numeric($facebook_user_id) || ! is_numeric($fanpage_id)) {
			throw new Exception('invalid argument ' . get_class(), null, null);
		}
		
		$result = $this->find($facebook_user_id, $fanpage_id)->current();
		
		if($result) {
			Zend_Debug::dump($result->toArray());
			$this->_isNew = false;
			$this->_fanProfile = $result;		
		}else {
			$this->_isNew = true;
			$this->_fanProfile = null;
			$this->_newBalance = 200;
		}
	}
	
	public function insertNewFan($data) {
		$data['fan_currency'] = $this->_newBalance;
		if(empty($data['fan_points'])) {
			$data['fan_level'] = 1;
		}else {
			$data['fan_level'] = $this->calculateLevel($data['fan_points']);
		}
		$this->insert($data);
	}
	
	public function updateFanProfile() {
		$this->updateLevel();
		$this->_fanProfile->save();
	}
	
	public function getFanProfile() {
		return $this->_fanProfile;
	}
	
	public function getFanLevel() {
		return $this->_fanProfile->fan_level;
	}
	
	public function getLastLoginTime() {
		return $this->_fanProfile->last_login_time;	
	}
	
	public function getFanPoints() {
		return $this->_fanProfile->fan_points;
	}
	
	public function getFanCountry() {
		return $this->_fanProfile->fan_country;
	}
	
	public function getFanGender() {
		return $this->_fanProfile->fan_gender;
	}
	
	public function getFanSince() {
		return $this->_fanProfile->created_time;
	}
	
	public function updateLevel() {
		if($this->_fanProfile->fan_points <= 0) {
			$this->_fanProfile->fan_level =  1;
			return;
		}
		//$newLevel = floor(pow($this->_fanProfile->fan_points / self::BASE_XP, 1 / self::LEVEL_FACTOR));
		//$this->_fanProfile->fan_level = $newLevel < self::MAX_LEVEL ? $newLevel : self::MAX_LEVEL;
		$this->_fanProfile->fan_level = $this->calculateLevel($this->_fanProfile->fan_points);
	}
	
	protected function calculateLevel($points) {
		$newLevel = floor(pow($points / self::BASE_XP, 1 / self::LEVEL_FACTOR));
		return $newLevel < self::MAX_LEVEL ? $newLevel : self::MAX_LEVEL;
	}
	
	public function updateCurrency($newBalance = null) {
		if(empty($newBalance)) {
			$this->_fanProfile->fan_currency = $this->_newBalance;
		}else {
			$this->_fanProfile->fan_currency = $newBalance;
		}
	}
	
	public function updateFanPoints($newFanPoints) {
		$this->_newBalance = $newFanPoints;
		$this->_fanProfile->fan_points = $newFanPoints;
	}
	
	public function getNextLevelRequiredXP() {
		return self::BASE_XP * pow($this->_fanProfile->fan_level + 1, self::LEVEL_FACTOR);
	}
	
	public function getFanLevelByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		$query = $this->select()
						->from($this, array('fan_level'))
						->where('facebook_user_id = ?', $facebook_user_id)
						->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
		
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['fan_level'];
		}
		
        return;
	}
	
	public function isNewFan() {
		return $this->_isNew;	
	}
	
	public function getFanCurrencyByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		$query = $this->select()
		->from($this, array('fan_currency'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
	
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['fan_currency'];
		}
	
		return;
	}
	
	public function getFanSinceByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		
		$query = $this->select()
		->from($this, array('created_time'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
		
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['created_time'];
		}
		
		return;
	}
	
	
	public function getFanPointsByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		$query = $this->select()
		->from($this, array('fan_points'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
	
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['fan_points'];
		}
	
		return;
	}
	
	public function getFanFieldsByFanIdAndFanpageId($facebook_user_id, $fanpage_id, $fields) {
		$query = $this->select()
						->from($this, $fields)
						->where('facebook_user_id = ?', $facebook_user_id)
						->where('fanpage_id =?', $fanpage_id);
		
		return $this->fetchAll($query);
	}
	
	public function isDataValid($data) {
		if(empty($data)) {
			return false;
		}
	
		$valid = true;
	
		$validator = new Zend_Validate_Sitemap_Lastmod();
	
		if(!empty($data['created_time'])) {
			$valid = $validator->isValid($data['created_time']);
		}
	
		if(!empty($data['updated_time'])) {
			$valid = $validator->isValid($data['created_time']);
		}
	
		return $valid && $this->isIdFieldsValid($data);
	}
	
	private function isIdFieldsValid($data) {
		$idValidator = new Zend_Validate_Digits();
		$ids = array('facebook_user_id', 'fanpage_id');
		foreach ($ids as $key => $id) {
			if (! $idValidator->isValid($data[$id])) {
				return false;
			}
		}
	
		if($data['facebook_user_id'] === $data['fanpage_id']) {
			return false;
		}
	
		return true;
	}
	
}

