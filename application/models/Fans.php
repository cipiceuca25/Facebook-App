<?php

class Model_Fans extends Model_DbTable_Fans
{
	const BASE_EXP = 100;
	const BASE_POINT = 0;
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
		$this->_newBalance = self::BASE_POINT;
	}
	
	public function __construct2($facebook_user_id, $fanpage_id) {
		parent::__construct();
		if(! is_numeric($facebook_user_id) || ! is_numeric($fanpage_id)) {
			throw new Exception('invalid argument ' . get_class(), null, null);
		}
		
		$result = $this->find($facebook_user_id, $fanpage_id)->current();
		
		if($result) {
			//Zend_Debug::dump($result->toArray());
			$this->_isNew = false;
			$this->_fanProfile = $result;
		}else {
			$this->_isNew = true;
			$this->_fanProfile = null;
			$this->_newBalance = self::BASE_POINT;
		}
	}
	
	public function insertNewFan($data) {
		if(empty($data['fan_exp'])) {
			$data['fan_point'] = $this->_newBalance;
			$data['fan_exp']	= $this->_newBalance;
			$data['fan_level'] = 1;
		}else {
			$data['fan_point'] = $this->_newBalance;
			$data['fan_exp']	+= $this->_newBalance;
			$data['fan_level'] = $this->calculateLevel($data['fan_exp']);
		}

		//Zend_Debug::dump($data);
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
	
	public function getFanCurrency() {
		return $this->_fanProfile->fan_point;
	}

	public function getFanPoint() {
		return $this->_isNew ? self::BASE_POINT : $this->_fanProfile->fan_point;
	}
	
	public function getFanExp() {
		return $this->_isNew ? self::BASE_EXP : $this->_fanProfile->fan_exp;	
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
		$newLevel = $this->calculateLevel($this->_fanProfile->fan_exp);
		if($this->_fanProfile->fan_level > $newLevel) {
			return;
		}
		
		//$newLevel = floor(pow($this->_fanProfile->fan_exp / self::BASE_EXP, 1 / self::LEVEL_FACTOR));
		//$this->_fanProfile->fan_level = $newLevel < self::MAX_LEVEL ? $newLevel : self::MAX_LEVEL;
		$this->_fanProfile->fan_level = $newLevel;
	}
	
	public function getLastNotification(){
		
		return $this->_fanProfile->last_notification;
	}
	public function getCurrentEXP(){
		return $this->_fanProfile->fan_exp;
	}
	
	public function getChosenBadges(){
		return $this->_fanProfile->chosen_badges;
	}
	
	protected function calculateLevel($points) {
		if($points < self::BASE_EXP) return 1;
		$newLevel = floor(pow($points / self::BASE_EXP, 1 / self::LEVEL_FACTOR));
		return $newLevel < self::MAX_LEVEL ? $newLevel : self::MAX_LEVEL;
	}
	
	public function updateCurrency($newBalance = null) {
		if(empty($newBalance)) {
			$this->_fanProfile->fan_point = $this->_newBalance;
		}else {
			$this->_fanProfile->fan_point = $newBalance;
		}
	}
	
	public function addPoint($point) {
		if($this->_isNew || empty($point)) return;
		$this->_fanProfile->fan_point += $point;
		return $this->_fanProfile->save();
	}
	
	public function subtractPoint($point) {
		if($this->_isNew || empty($point)) return;
		$this->_fanProfile->fan_point = $this->_fanProfile->fan_point - $point;
		return $this->_fanProfile->save();
	}
	
	public function updateLastNotification(){
		$date = new Zend_Date();
		$this->_fanProfile->last_notification = $date->toString('YYYY-MM-dd HH:mm:ss');
		$this->_fanProfile->save();
	}
	
	public function updateBadgeChoices($badges){
		
		$this->_fanProfile->chosen_badges = $this->getAdapter()->quote($badges);
		$this->_fanProfile->save();
	}
	
	public function updateDescription($message){
		
		$this->_fanProfile->message = $this->getAdapter()->quote($message);
		$this->_fanProfile->save();
	}
	
	public function updateFanPoints($newFanPoints) {
		$this->_fanProfile->fan_point += $newFanPoints;
		$this->_fanProfile->fan_exp += $newFanPoints > 0 ? $newFanPoints : 0;
	}
	
	public function getNextLevelRequiredXP() {
		return self::BASE_EXP * pow($this->_fanProfile->fan_level + 1, self::LEVEL_FACTOR);
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
	
	public function getFanPointByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		$query = $this->select()
		->from($this, array('fan_point'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
	
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['fan_point'];
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
	
	
	public function getFanExpByFanIdAndFanpageId($facebook_user_id, $fanpage_id) {
		$query = $this->select()
		->from($this, array('fan_exp'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id =?', $fanpage_id);
		$result = $this->fetchAll($query)->toArray();
	
		//Zend_Debug::dump($result); exit();
		if(!empty($result[0])) {
			return $result[0]['fan_exp'];
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
	
	public function fetchFanFieldsByFanpageId($fanpage_id, $fields) {
		$select = $this->select();
		$select->from($this, $fields)->where('fanpage_id = ?', $fanpage_id);
		return $this->fetchAll($select)->toArray();
	}
	
	public function fetchFansIdListByFanpageId($fanpage_id) {
		$select = $this->select();
		$select->from($this, array('facebook_user_id'))->where('fanpage_id = ?', $fanpage_id);
		return $this->fetchAll($select)->toArray();
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

