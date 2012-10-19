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
		$select = "select object_id, object_type, giving_points, note, date(created_time) as created_time from point_log p
					where";
		if ($facebook_user_id !=null){
			
			$select = $select." facebook_user_id = $facebook_user_id && ";
		}
				
		$select =  $select." fanpage_id = $fanpageId && datediff(curdate(), created_time) < $day 
					order by created_time ASC, object_id";
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
		return $this->fetchAll($query)->toArray();

	}
	
	
	
}

