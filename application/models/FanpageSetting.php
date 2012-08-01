<?php

class Model_FanpageSetting extends Model_DbTable_FanpageSetting
{

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
	
	public function setTheme($fanpage_id, $color){
		//$user = new Model_UsersColorChoice();
		//$select = $user->find($user_id);
		
		
		$data = array('color_choice' => $color);
			//if have user
		$where = $this ->getAdapter() ->quoteInto('fanpage_id =?', $fanpage_id);
			//else user not exist
			// $select = "insert into user_color_choice values".$user_id.",".$color.")";
		
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

	public function isDataValid() {
		
	}
}