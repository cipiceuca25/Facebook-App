<?php

class Model_PointLog extends Model_DbTable_PointLog
{

	public function getAwardPointsByFanpgeIdAndTime($fanpageId, $since) {
		$time = '';
		if(is_string($since)) {
			$time = strtotime($since);
		}else {
			return;
		}
		$date = new Zend_Date($time);
		
		$query = $this->select()
			->from($this, array('sum(giving_points) as point'))
			->where('giving_points >= 0')
			->where('fanpage_id = ?', $fanpageId)
			->where('created_time >= ?', $date->toString('yyyy-MM-dd HH:mm:ss' ));
		
		$result = $this->fetchAll($query)->toArray();

		Zend_Debug::dump($result);
		return empty($result[0]['point']) ? 0 : $result[0]['point'];
	}
	
	public function getPointsByPost($fanpageId, $facebook_user_id, $post_id){
		
		$query = $this->select()
		->from($this, array('sum(giving_points) as point'))
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id = ?', $fanpageId)
		->where('object_id = ?', $post_id);
		
		$result = $this->fetchAll($query)->current();
		return $result;
	}
	
	public function getPointsWithinDays($fanpageId, $facebook_user_id, $day){
		$select = "select distinct sum(giving_points) as sum,  l.object_id, l.object_type, f.message, 
					l.giving_points, l.note, date(l.created_time) as created_time 
					
					from point_log l
					
					
					left join  fancrank.fancrank_activities f 
					on 	f.facebook_user_id = l.facebook_user_id && l.fanpage_id = f.fanpage_id 
						&& 	f.event_object = l.object_id
					where ";
		if ($facebook_user_id !=null){
			
			$select = $select." l.facebook_user_id = $facebook_user_id && ";
		}
				
		$select =  $select." l.fanpage_id = $fanpageId && datediff(curdate(), l.created_time) < $day
group by object_id
order by l.created_time ASC, l.object_id";
		return $this->getAdapter()->fetchAll($select);
	}
		
	public function getPointsGainSinceTimeByDay($fanpageId, $facebook_user_id, $time){
		
		$select = "select sum(giving_points) as sum, created_time from point_log p
					where facebook_user_id = $facebook_user_id && fanpage_id = $fanpageId
					&& created_time > '$time' 
					group by date(created_time)
					order by created_time ASC" ;

		return $this->getAdapter()->fetchAll($select);
	}	
	
	public function getPointsSinceTimeCount($fanpageId, $facebook_user_id, $time){
		
		$select = "select count(*) from point_log p
		where facebook_user_id = $facebook_user_id && fanpage_id = $fanpageId && created_time > '$time'
		order by created_time DESC";
		
		return $this->getAdapter()->fetchAll($select);
		
	}
	
	public function getFanpagePointLog($fanpageId, $limit=1000) {
		$query = $this->select()
			->where('fanpage_id = ?', $fanpageId)
			->order('created_time desc')
			->limit($limit);
		
		$result = $this->fetchAll($query);
		
		if(empty($result)) return null;
		return $result->toArray();
	}
	
	public function getFanpagePointLogByHour($fanpageId, $limit=1000) {
		$today = new Zend_Date();
		$query = $this->select()
			->from($this, array('sum(giving_points) as point, HOUR(created_time) as hours'))
			->where('fanpage_id = ?', $fanpageId)
			->where('created_time > >', $today->toString('yyyy-MM-dd 00:00:00'))
			->group('hours')
			->order('created_time desc')
			->limit($limit);
		
		$result = $this->fetchAll($query);
		
		if(empty($result)) return null;
		return $result->toArray();
	}
	
}

