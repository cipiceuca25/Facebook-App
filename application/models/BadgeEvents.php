<?php

class Model_BadgeEvents extends Model_DbTable_BadgeEvents
{
	public function getRecentBadgesByFanpageIdAndFanID($fanpage_id, $facebook_user_id, $since) {
		$query = $this->select()
			->from($this)
			->where('facebook_user_id = ?', $facebook_user_id)
			->where('fanpage_id = ?', $fanpage_id)
			->where('created_time > ?', $since);
		
		return $this->fetchAll($query); 
	}
	
	public function getBadgesByFanpageIdAndFanID($fanpage_id, $facebook_user_id, $limit){
		$select = "SELECT b.name, b.description, b.picture, b.quantity, b.id, e.created_time
					from badges b, badge_events e 
					where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id &&
					 e.fanpage_id = $fanpage_id
					order by created_time DESC, quantity   DESC";
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
		
		
	}
		
	public function getAllBadgesEvents($since) {
		$query = $this->select()
					->from($this)
					->where('created_time > ?', $since)
					->order('facebook_user_id');
		
		return $this->fetchAll($query);
	}
	
	public function getNumBadgesByUser($fanpage_id, $facebook_user_id) {
		$select = "	SELECT count(*) as count 
					from badge_events 
					where facebook_user_id=".$facebook_user_id." && fanpage_id=".$fanpage_id;

		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getNonNotifiedBadgesCountByUser($fanpage_id, $facebook_user_id) {
		$query = $this->select()
			->from($this)
			->where('facebook_user_id = ?', $facebook_user_id)
			->where('fanpage_id = ?', $fanpage_id)
			->where('notification_read = ?', 0);
		
		return $this->fetchAll($query)->count();
	}
	
	public function getNonNotifiedBadgesByUser($fanpage_id, $facebook_user_id) {
		/*$query = $this->select()
		->from($this)
	
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id = ?', $fanpage_id)
		->where('notification_read = ?', 0);
	
		return $this->fetchAll($query);*/
		$select = "SELECT b.name, b.description, b.picture, b.quantity, b.id from badges b, badge_events e where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id && e.fanpage_id = $fanpage_id";
		
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function hasBadgeEvent($fanpage_id, $facebook_user_id, $badge_id) {
		$query = $this->select()
			->from($this, array('id'))
			->where('fanpage_id = ?', $fanpage_id)
			->where('facebook_user_id = ?', $facebook_user_id)
			->where('badge_id = ?', $badge_id)
			->limit(1);
		
		$result = $this->fetchAll($query)->count();
		
		if($result) {
			return true;
		}
		return false;
	}
	
	public function getRemaindDefaultBadgeByUser($fanpage_id, $facebook_user_id) {
		$query = $this->getDefaultAdapter()->select()
			->from(array('b' => 'badges'))
			->where('b.type = ?', 'default')
			->where("b.id not in (select e.badge_id from badge_events e where e.fanpage_id = $fanpage_id and e.facebook_user_id = $facebook_user_id)");
		
		return $this->getDefaultAdapter()->fetchAll($query);
	}	
}



