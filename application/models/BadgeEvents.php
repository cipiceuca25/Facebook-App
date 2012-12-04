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
		$select = "select x.id as badge_id, x.name, x.description, x.quantity, x.weight, x.stylename, e.created_time, x.picture, x.active, x.redeemable, r.*
					from badge_events e left join fancrank.redeem_transactions r
					on r.badge_event_id = e.id && r.fanpage_id = $fanpage_id,
					(
					SELECT b.id, b.name, b.description, b.quantity, 
					if (f.weight <=> null, b.weight, f.weight) as weight,  
					if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
					if (f.active <=> null, 1, f.active) as active,
					if (f.redeemable <=> null, 0, f.redeemable) as redeemable,
					b.picture
					
					FROM badges b
					left join fancrank.fanpage_badges f
					on f.badge_id = b.id && fanpage_id = $fanpage_id
					) as x
					where e.fanpage_id = $fanpage_id && e.facebook_user_id = $facebook_user_id && e.badge_id = x.id &&
					x.active = 1 
					order by e.created_time DESC , quantity DESC";
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);

	}

	public function getRedeemableBadges($fanpage_id, $facebook_user_id, $limit){
		$select = "select x.id as badge_id, x.name, x.description, x.quantity, x.weight, x.stylename, e.created_time, x.picture,x.redeemable, x.active, r.*

		from badge_events e left join fancrank.redeem_transactions r
		on r.badge_event_id = e.id && r.fanpage_id = $fanpage_id,
		(
		SELECT b.id, b.name, b.description, b.quantity,
		if (f.weight <=> null, b.weight, f.weight) as weight,
		if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
		if (f.active <=> null, 1, f.active) as active,
		if (f.redeemable <=> null, 0, f.redeemable) as redeemable,
		b.picture
			
		FROM badges b
		left join fancrank.fanpage_badges f
		on f.badge_id = b.id && fanpage_id = $fanpage_id
		) as x
		where e.fanpage_id = $fanpage_id && e.facebook_user_id = $facebook_user_id && e.badge_id = x.id &&
		x.active = 1 && x.redeemable = 1
		order by e.created_time DESC , quantity DESC";
		if($limit !== false)
		$select = $select . " LIMIT $limit";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	/*
	// note we hard code the top fan badge id in the query
	public function getRedeemableBadges($fanpage_id, $facebook_user_id, $limit) {
		// check fanpage level
		$select = "select x.id as badge_id, x.name, x.description, x.quantity, x.weight, x.stylename, e.created_time, x.picture, x.active
		from badge_events e,
		(
		SELECT b.id, b.name, b.description, b.quantity,
		if (f.weight <=> null, b.weight, f.weight) as weight,
		if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
		if (f.active <=> null, 1, f.active) as active,
		b.picture
			
		FROM badges b
		left join fancrank.fanpage_badges f
		on f.badge_id = b.id && fanpage_id = $fanpage_id
		) as x
		where e.fanpage_id = $fanpage_id && e.facebook_user_id = $facebook_user_id && e.badge_id = x.id &&
		x.active = 1 and e.badge_id = 721
		order by created_time DESC , quantity DESC";
		if($limit !== false)
		$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}*/
 	
	public function getChosenBadges($fanpage_id, $facebook_user_id, $chosen){
		
		$a = array();
		
		foreach ($chosen as $x){
			if ($x != 'undefined'){
				$a [] = $x;
			}
		}
		
		//Zend_Debug::dump($a);
		
		$select = "select x.id as badge_id, x.name, x.description, x.quantity, x.weight, x.stylename, e.created_time, x.picture, x.active
					from badge_events e,
					(
						SELECT b.id, b.name, b.description, b.quantity, 
						if (f.weight <=> null, b.weight, f.weight) as weight,  
						if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
						if (f.active <=> null, 1, f.active) as active,
						b.picture
						FROM badges b
						left join fancrank.fanpage_badges f
						on f.badge_id = b.id && fanpage_id = $fanpage_id
					) as x
					where e.fanpage_id = $fanpage_id && e.facebook_user_id = $facebook_user_id && e.badge_id = x.id &&
					x.active = 1";
		
		switch(count($a)){
			case 3:
				$select = $select."&& (e.badge_id=$a[0] || e.badge_id = $a[1] || e.badge_id = $a[2])";
				break;
			case 2:
				$select = $select."&& (e.badge_id=$a[0] || e.badge_id = $a[1])";
				break;
			case 1:
				if ($a[0] !=null && !empty($a[0])){
					$select = $select."&& (e.badge_id=$a[0])";
				}else{
					$select = $select.' order by created_time DESC, quantity DESC limit 3';
				}
				break;
			case 0:
				return $chosen;
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
		
		if ($result) {
			return true;
		}
		return false;
	}
	
	public function hasRedeemableBadgeEvent($fanpage_id, $facebook_user_id, $badge_id) {
		$query = $this->select()
			->from(array('e'=>'badge_events'), array('id'))
			->join(array('f'=>'fanpage_badges'), 'f.badge_id = e.badge_id AND f.fanpage_id = e.fanpage_id', array())
			->where('e.fanpage_id = ?', $fanpage_id)
			->where('e.facebook_user_id = ?', $facebook_user_id)
			->where('e.badge_id = ?', $badge_id)
			->where('f.redeemable = 1')
			->limit(1);
		
		$result = $this->fetchRow($query);

		if ($result) {
			return $result['id'];
		}
		return false;
	}
	
	public function getRedeemableBadgeDetail($fanpage_id, $facebook_user_id, $badge_event_id) {
		$query = $this->getDefaultAdapter()->select()
			->from(array('e'=>'badge_events'), array('e.id'))
			->join(array('b'=>'badges'), 'b.id = e.badge_id', array('b.name', 'b.picture'))
			->join(array('f'=>'fanpage_badges'), 'f.badge_id = e.badge_id AND f.fanpage_id = e.fanpage_id', array('f.*'))
			->where('e.id = ?', $badge_event_id)
			->where('f.redeemable = 1')
			->limit(1);
		return $this->getDefaultAdapter()->fetchrow($query);
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
	public function getMostAwardedBadges($fanpageId){
	
		$select="select count(x.id) as count ,  x.id, x.name, x.description, x.quantity, x.weight, x.stylename, x.picture
		from badge_events e,
		(
		SELECT b.id, b.name, b.description, b.quantity,
		if (f.weight <=> null, b.weight, f.weight) as weight,
		if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
		if (f.active <=> null, 1, f.active) as active,
		b.picture
			
		FROM badges b
		left join fancrank.fanpage_badges f
		on f.badge_id = b.id && fanpage_id = $fanpageId
		) as x
		where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1 
		group by id
		order by count(x.id) DESC
		";
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function recentAwardedBadges($fanpageId){
	
	$select="select a.*, f.fan_name
	from
	(select  e.fanpage_id, e.facebook_user_id, x.id, x.name, x.description, x.quantity, x.weight, x.stylename, e.created_time, x.picture
	from badge_events e,
	(
	SELECT b.id, b.name, b.description, b.quantity,
	if (f.weight <=> null, b.weight, f.weight) as weight,
	if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
	if (f.active <=> null, 1, f.active) as active,
	b.picture
	
	FROM badges b
	left join fancrank.fanpage_badges f
	on f.badge_id = b.id && fanpage_id = $fanpageId
	) as x
	where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1
	order by created_time desc
	limit 100
	) as a
	left join fans f
	on a.facebook_user_id = f.facebook_user_id && a.fanpage_id = f.fanpage_id
	";
	return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getUsersWithMostBadge($fanpageId){
	$select= "select a.count, a.fanpage_id, a.facebook_user_id, f.fan_name from
	(
			select count(e.facebook_user_id) as count, e.fanpage_id, e.facebook_user_id
			from badge_events e,
			(
			SELECT b.id, b.name, b.description, b.quantity,
			if (f.weight <=> null, b.weight, f.weight) as weight,
			if (f.stylename <=> null, b.stylename, f.stylename) as stylename,
			if (f.active <=> null, 1, f.active) as active,
			b.picture
				
			FROM badges b
			left join fancrank.fanpage_badges f
			on f.badge_id = b.id && fanpage_id = $fanpageId
	) as x
	where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1
	group by e.facebook_user_id
	order by count DESC) as a
	left join fans f
	on a.facebook_user_id = f.facebook_user_id && a.fanpage_id = f.fanpage_id 
	order by count DESC
	limit 100
	";
	return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalAwardedBadges($fanpageId){
		$select="select count(*) as count
				from badge_events e,
				(
				SELECT b.id,
				if (f.active <=> null, 1, f.active) as active,
				b.picture
				
				FROM badges b
				left join fancrank.fanpage_badges f
				on f.badge_id = b.id && fanpage_id = $fanpageId
				) as x
				where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1";
		
		$r = $this->getAdapter()->fetchAll($select);
		return $r[0]['count'];
	}
	
	public function getTotalPointsFromBadges($fanpageId){
		$select="select sum(weight) as points
				from badge_events e,
				(
				SELECT b.id, 
				if (f.weight <=> null, b.weight, f.weight) as weight,  
		
				if (f.active <=> null, 1, f.active) as active
			
				FROM badges b
				left join fancrank.fanpage_badges f
				on f.badge_id = b.id && fanpage_id = $fanpageId
				) as x
				where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1";
	
		$r = $this->getAdapter()->fetchAll($select);
		return $r[0]['points'];
	}
	
	public function badgesAwardedByTime($fanpageId){
		$select="select 'month' as time, count(*) as badges 
					from badge_events e,
					(
					SELECT b.id, 
					if (f.active <=> null, 1, f.active) as active
					
					FROM badges b
					left join fancrank.fanpage_badges f
					on f.badge_id = b.id && fanpage_id = $fanpageId
					) as x
					where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1
					&& Month(curdate()) = Month(created_time) 
					&& year(curdate()) = year(created_time)
				
				union 
				
				select 'week' as time, count(*) as badges 
					from badge_events e,
					(
					SELECT b.id, 
					if (f.active <=> null, 1, f.active) as active
					
					FROM badges b
					left join fancrank.fanpage_badges f
					on f.badge_id = b.id && fanpage_id = $fanpageId
					) as x
					where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1
					&& yearweek(curdate()) = yearweek(created_time)
				
				union 
				
				select 'today' as time, count(*) as badges 
					from badge_events e,
					(
					SELECT b.id, 
					if (f.active <=> null, 1, f.active) as active
					
					FROM badges b
					left join fancrank.fanpage_badges f
					on f.badge_id = b.id && fanpage_id = $fanpageId
					) as x
					where e.fanpage_id = $fanpageId  && e.badge_id = x.id && active = 1
					&& Date(curdate()) = date(created_time)";
		
		return $this->getAdapter()->fetchAll($select);
		
	}
	
}



