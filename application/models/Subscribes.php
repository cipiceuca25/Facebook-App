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
	
	public function getFollowersList($user, $fanpage, $limit){
		$select = "select s.*,f.facebook_user_id, f.facebook_user_name, f.facebook_user_last_name, f.facebook_user_first_name from subscribes s, facebook_users f where s.follow_enable=1 AND f.facebook_user_id=s.facebook_user_id AND s.facebook_user_id_subscribe_to =".$user." AND s.fanpage_id=".$fanpage ;
		
		if(($limit !== false) && ($limit !=0))
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getFollowingList($user, $fanpage, $limit){
		$select = "select s.*,f.facebook_user_id, f.facebook_user_name, f.facebook_user_last_name, f.facebook_user_first_name from subscribes s, facebook_users f where s.follow_enable=1  AND f.facebook_user_id =s.facebook_user_id_subscribe_to AND s.facebook_user_id =".$user." AND s.fanpage_id=".$fanpage ;
		
		if(($limit !== false) && ($limit !=0))
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}
	/*
	public function getFriendsList($user, $fanpage, $limit){
		$select = "select f.facebook_user_id, f.facebook_user_name, f.facebook_user_last_name, f.facebook_user_first_name,b.* 
					from subscribes a, subscribes b, facebook_users f
					where a.follow_enable=1 AND b.follow_enable=1 AND  a.facebook_user_id =".$user." AND
					b.facebook_user_id_subscribe_to =" .$user." AND a.fanpage_id=".$fanpage." AND b.fanpage_id=".$fanpage. " AND
					a.facebook_user_id_subscribe_to = b.facebook_user_id AND f.facebook_user_id = a.facebook_user_id_subscribe_to";
		
		if(($limit !== false) && ($limit !=0))
			$select = $select . " LIMIT $limit";
		//echo $select;
		
		return $this->getAdapter()->fetchAll($select);
	}
	*/
	
	public function getTopFollowed($fanpage, $limit){
		$select = "SELECT distinct a.facebook_user_id_subscribe_to, b.*, count(*) as count
					FROM fancrank.subscribes a, fancrank.fans b 
					where a.fanpage_id = ".$fanpage." && 
						  b.fanpage_id = ".$fanpage." &&
				          a.facebook_user_id_subscribe_to = b.facebook_user_id &&
				          a.follow_enable = 1 &&
						  b.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $fanpage."')	
					group by a.facebook_user_id_subscribe_to 
					order by count DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTopFollowedRank($fanpage, $facebook_user_id){
		
		$select = "select *
				from
				(
				    select rank.*, @rownum:=@rownum+1 as my_rank
				    FROM
					    (
						SELECT distinct a.facebook_user_id_subscribe_to ,b.*, count(*) as count
							FROM fancrank.subscribes a, fancrank.fans b 
							where a.fanpage_id = ".$fanpage." && 
							  b.fanpage_id = ".$fanpage." &&
					          a.facebook_user_id_subscribe_to = b.facebook_user_id &&
					          a.follow_enable = 1 &&
							  b.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $fanpage."')	
							group by a.facebook_user_id_subscribe_to 
							order by count DESC
					) as rank, (SELECT @rownum:=0) r
				) as topfollow_rank
				WHERE facebook_user_id = '". $facebook_user_id ."'
		";
		
		$result = $this->getAdapter()->fetchAll($select);
		if(!empty($result[0])) {
			return $result[0];
		}
		
		
		
		
	}
	
	
	public function getFollowers($user, $fanpage){
		 
		$select = "select count(s.facebook_user_id) as Follower from subscribes s where s.follow_enable=1 AND s.facebook_user_id_subscribe_to =".$user." AND s.fanpage_id=".$fanpage ;
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getFollowing($user, $fanpage){
		 
		$select = "select count(s.facebook_user_id_subscribe_to) as Following from subscribes s where s.follow_enable=1 AND s.facebook_user_id =".$user." AND s.fanpage_id=".$fanpage ;

		return $this->getAdapter()->fetchAll($select);
	}
	/*
	public function getFriends($user, $fanpage){

		$select = "select count(a.facebook_user_id) as friends 
					from subscribes a, subscribes b
					where a.follow_enable=1 AND b.follow_enable=1 AND  a.facebook_user_id = ".$user." AND 
					b.facebook_user_id_subscribe_to =" .$user." AND a.fanpage_id=".$fanpage." AND b.fanpage_id=".$fanpage. " AND 
					a.facebook_user_id_subscribe_to = b.facebook_user_id";
		
		return $this->getAdapter()->fetchAll($select);
		
	}
	*/
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
	
	public function isFanpage($user, $target){
		$select = "select f.facebook_user_id from fans f where f.facebook_user_id=$user AND f.fanpage_id=$target";
		if ($this->getAdapter()->fetchAll($select)){
		
			return true;
		}
		return false;
	}
	
	public function getRelation($user, $target){
		if($user == $target){
			return "You";
		}
		$isFanpage = $this->isFanpage($user, $target);
		if($isFanpage){	
			return "Fanpage";
		}
		
		$isfollowing = $this->isFollowing($user, $target);
		$isfollower =  $this->isFollower($user, $target);
		
		if ($isfollowing && $isfollower){
			//return "Friends";
			return "Following";
		}else if ($isfollowing){
			return "Following";
		}else if ($isfollower){
			return "Follower";
		}else{
			return "Follow";
		}
	}
}
