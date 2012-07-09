<?php

class Model_Subscribes extends Model_DbTable_Subscribes
{
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
	
		$booleanValidator = new Zend_Validate_InArray(array(true, false));
		$valid = $booleanValidator->isValid($data['follow_enable']);
	
		return $valid && $this->isIdFieldsValid($data);
	}
	
	private function isIdFieldsValid($data) {
		$idValidator = new Zend_Validate_Digits();
		$ids = array('facebook_user_id', 'facebook_user_id_subscribe_to', 'fanpage_id');
		foreach ($ids as $key => $id) {
			if (! $idValidator->isValid($data[$id])) {
				return false;
			}
		}

		if($data['facebook_user_id'] === $data['facebook_user_id_subscribe_to'] 
				|| $data['facebook_user_id'] === $data['fanpage_id']
				|| $data['facebook_user_id_subscribe_to'] === $data['fanpage_id']) {
			return false;
		}
		
		return true;
	}
}
