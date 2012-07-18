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
	
	public function getFollowers($user){
		 
		$select = "select count(s.facebook_user_id) as Follower from subscribes s where s.follow_enable=1 AND s.facebook_user_id_subscribe_to =".$user ;
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getFollowing($user){
		 
		$select = "select count(s.facebook_user_id_subscribe_to) as Following from subscribes s where s.follow_enable=1 AND s.facebook_user_id =".$user ;

		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getFriends($user){

		$select = "select count(a.facebook_user_id) as friends 
					from subscribes as a, subscribes as b
					where a.follow_enable=1 AND b.follow_enable=1 AND  a.facebook_user_id = ".$user." AND 
					b.facebook_user_id_subscribe_to =" .$user. " AND 
					a.facebook_user_id_subscribe_to = b.facebook_user_id";
		
		return $this->getAdapter()->fetchAll($select);
		
	}
	public function isFollowing($user, $target){
		$select = "select s.facebook_user_id from subscribes s where s.follow_enable=1 AND s.facebook_user_id=$user AND s.facebook_user_id_subscribe_to = $target";
		if ($this->getAdapter()->fetchAll($select))
			return true;
	
		return false;
	}
	public function isFollower($user, $target){
		$select = "select s.facebook_user_id from subscribes s where s.follow_enable=1  AND s.facebook_user_id=$target AND s.facebook_user_id_subscribe_to = $user";
		if ($this->getAdapter()->fetchAll($select))
			return true;
	
		return false;
	}
	
	public function getRelation($user, $target){
		if($user == $target){
			return "self";
		}
		
		$isfollowing = $this->isFollowing($user, $target);
		$isfollower =  $this->isFollower($user, $target);
		if ($isfollowing && $isfollower){
			return "Friends";
		}else if ($isfollowing){
			return "Following";
		}else if ($isfollower){
			return "Follower";
		}else{
			return "Follow";
		}
	}
}
