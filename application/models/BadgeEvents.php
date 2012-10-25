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
		$select = "SELECT b.name, b.description, b.picture, b.quantity, b.id, e.created_time, e.badge_id
					from badges b, badge_events e 
					where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id &&
					 e.fanpage_id = $fanpage_id
					order by created_time DESC, quantity   DESC";
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);

	}

	public function getChosenBadges($fanpage_id, $facebook_user_id, $chosen){
		
		$select = "SELECT b.name, b.description, b.picture, b.quantity, b.id, e.created_time
		from badges b, badge_events e
		where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id &&
		e.fanpage_id = $fanpage_id ";
		switch(count($chosen)){
			case 3:
				$select = $select."&& (e.badge_id=$chosen[0] || e.badge_id = $chosen[1] || e.badge_id = $chosen[2])";
				break;
			case 2:
				$select = $select."&& (e.badge_id=$chosen[0] || e.badge_id = $chosen[1])";
				break;
			case 1:
				if ($chosen[0] !=null && !empty($chosen[0])){
					$select = $select."&& (e.badge_id=$chosen[0])";
				}else{
					$select = $select.' order by created_time DESC, quantity DESC limit 3';
				}
					break;
		}
					

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
		$select = "SELECT b.name, b.description, b.picture, b.quantity, b.id from badges b, badge_events e where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id && e.fanpage_id = $fanpage_id && e.notification_read=0";
		
		
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
	
	public function setViewBadgesByTime($fanpage_id, $facebook_id , $time){
		
		$data = array('notification_read' => 1);
		$where[] = $this->getAdapter()->quoteInto('created_time < ?', $time);
		$where[] = $this->getAdapter()->quoteInto('fanpage_id = ?', $fanpage_id);
		$where[] = $this->getAdapter()->quoteInto('facebook_user_id= ?', $facebook_id);
		$this->update($data, $where);		
	}
	
	public function notify($fanpage_id, $facebook_user_id, $time){
		
		if ($time){
			$select= "select * 
					from (
					
					SELECT e.created_time as created_time, 'badge' as activity_type, e.id as event_object, facebook_user_id, null as facebook_user_name, description as message, name, quantity, picture
					from badges b, badge_events e 
					where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id
					&& e.fanpage_id = $fanpage_id && e.notification_read=0
					
					union
					
					select created_time, 'points' as activity_type, null as event_object, null as facebook_user_id, null as facebook_user_name,
					null as message, null as name, sum(giving_points) as quantity, null as picture  from point_log p
					where facebook_user_id = $facebook_user_id && fanpage_id = $fanpage_id && created_time > '$time'
					group by date(created_time)

					union 
					
					SELECT created_time, activity_type, event_object, facebook_user_id, facebook_user_name, message, null as name, null as quantity, null as picture
					FROM fancrank.fancrank_activities 
					where target_user_id = $facebook_user_id && fanpage_id = $fanpage_id  && facebook_user_id != target_user_id && created_time > '$time'
					) as a
					order by created_time DESC
					";
		}else{
			$select= "select *
						from (
							
						SELECT e.created_time as created_time, 'badge' as activity_type, e.id as event_object, facebook_user_id, null as facebook_user_name, description as message, name, quantity, picture
						from badges b, badge_events e
						where e.badge_id = b.id && e.facebook_user_id = $facebook_user_id
						&& e.fanpage_id = $fanpage_id && e.notification_read=0
							
						union
							
						select created_time, 'points' as activity_type, object_type as event_object, null as facebook_user_id, null as facebook_user_name,
						note as message, null as name, sum(giving_points+bonus) as quantity, null as picture  from point_log p
						where facebook_user_id = $facebook_user_id && fanpage_id = $fanpage_id
						group by date(created_time)
						
						union
							
						SELECT created_time, activity_type, event_object, facebook_user_id, facebook_user_name, message, null as name, null as quantity, null as picture
						FROM fancrank.fancrank_activities
						where target_user_id = $facebook_user_id && fanpage_id = $fanpage_id  && facebook_user_id != target_user_id && activity_type != 'admin_add_point' && activity_type != 'admin_sub_point'
						) as a
						order by created_time DESC
						";
		}
		
		
		
		return $this->getAdapter()->fetchAll($select);
	}
	
}



