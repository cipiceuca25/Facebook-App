<?php

class Model_Fans extends Model_DbTable_Fans
{

	public function getFanLevel($facebook_user_id, $fanpage_id) {
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
	
	public function getFanCurrency($facebook_user_id, $fanpage_id) {
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
	
	public function getFanSince($facebook_user_id, $fanpage_id){
		
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
	
	
	public function getFanPoints($facebook_user_id, $fanpage_id) {
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
	
	public function getFanFields($facebook_user_id, $fanpage_id, $fields) {
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

