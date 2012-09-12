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
}



