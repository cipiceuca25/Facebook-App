<?php

class Model_FanpageSetting extends Model_DbTable_FanpageSetting
{
	const THEME_CHOICE = 3;
	const TOP_POST_CHOICE = 'week';
	const POINT_LIKE_NORMAL = 1;
	const POINT_COMMENT_NORMAL = 0;
	const POINT_POST_NORMAL = -5;
	const POINT_POST_COMMENT = 2;
	const POINT_LIKE_ADMIN = 1;
	const POINT_COMMENT_ADMIN = 2;
	const POINT_BONUS_DURATION = 60;
	const POINT_VIRGINITY = 4;
	const POINT_COMMENT_LIMIT = 5;
	const POINT_LIKE_BONUS = 1;
	
	public function getThemeChoice($fanpage_id)
	{
		//$select = "select users_color_choice.color_choice from users_color_choice where user_id ='".$user_id."'";
		$result = $this -> find($fanpage_id)->current();
		
		if($result) {
			return $result['theme_choice'];
		}		
		return;
		//return $this->getAdapter()->fetchAll($select);
	}
	
	public function getTopPostChoice($fanpage_id) {
		$result = $this -> find($fanpage_id)->current();
		
		if($result) {
			return $result['top_post_choice'];
		}
		return;
	}
	
	public function isProfileImageEnable($fanpage_id) {
		$result = $this -> find($fanpage_id)->current();
		if($result && isset($result->profile_image_enable)) {
			return $result->profile_image_enable;
		}
		return false;
	}
	
	public function profileImageUrl($fanpage_id) {
		$result = $this -> find($fanpage_id)->current();
		if($result && isset($result->profile_image_url)) {
			return $result->profile_image_url;
		}
		return false;
	}
	
	public function setTheme($fanpage_id, $color){
		$data = array('color_choice' => $color);
		$where = $this ->getAdapter() ->quoteInto('fanpage_id =?', $fanpage_id);
		$this->update($data, $where);
	}
	
	public function saveFanpageSetting($data) {
		if(empty($data['fanpage_id'])) {
			return;
		}
		
		$result = $this ->find($data['fanpage_id'])->current();
		//Zend_Debug::dump($result);

		if($result) {
			foreach ($data as $key=> $value) {
				$result->{$key} = $value;
			}
			//Zend_Debug::dump($result); exit();
			return $result->save();
		}
		return;
	}

	public function isDataValid($data) {
		if(empty($data)) return false;
		
		foreach ($data as $key=>$value) {
			if($key !== 'top_post_choice' && !is_numeric($value)) return false; 
		}
		return true;
	}
	
	public function getDefaultSetting() {
		$data = array(
				'theme_choice'=>self::THEME_CHOICE,
				'top_post_choice'=>self::TOP_POST_CHOICE,
				'point_like_normal'=>self::POINT_LIKE_NORMAL,
				'point_comment_normal'=>self::POINT_COMMENT_NORMAL,
				'point_post_normal'=>self::POINT_POST_NORMAL,
				'point_post_comment'=>self::POINT_POST_COMMENT,
				'point_like_admin'=>self::POINT_LIKE_ADMIN,
				'point_comment_admin'=>self::POINT_COMMENT_ADMIN,
				'point_virginity'=>self::POINT_VIRGINITY,
				'point_comment_limit'=>self::POINT_COMMENT_LIMIT,
				'point_bonus_duration'=>self::POINT_BONUS_DURATION,
				'point_like_bonus'=>self::POINT_LIKE_BONUS
				);
		return $data;
	}
	
	public function getFanpageSetting($fanpageId) {
		$result = $this->findRow($fanpageId);
		return empty($result) ? $this->getDefaultSetting() : $result->toArray();
	}
	
	public static function getDefaultFacebookScope() {
		$data = 'publish_stream,email,user_about_me,user_birthday,user_likes,user_status';
		return $data;
	}
	
	public static function getAvailableScopeList() {
		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
		$scope = $sources->get('facebook');
		return $scope->permission_scope;
	}
	
	public function getFacebookScope($fanpageId) {
		$settingData = $this->findRow($fanpageId);
		return $settingData->facebook_scope;
	}
}