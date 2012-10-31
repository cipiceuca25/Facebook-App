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
		$select = "select sum, object_id, object_type, date(d.created_time) as created_time, note,d.facebook_user_id, fan_name, d.message from (
					select sum, object_id, object_type, created_time, note, 
					(case post_message when post_message<=> NULL then b.facebook_user_id else c.facebook_user_id end)as facebook_user_id, 
					(case post_message when post_message<=> NULL then post_message else comment_message end) as message from (
					
					SELECT sum(l.giving_points) as sum, object_id, object_type, created_time, note FROM fancrank.point_log l
					where ";
		if ($facebook_user_id !=null){
			$select = $select." l.facebook_user_id = $facebook_user_id && ";
		}
		
		$select =  $select." l.fanpage_id = $fanpageId && datediff(curdate(), l.created_time) < $day
					group by l.object_id
					order by l.object_id) as a 
					
					left join 
					
					(select facebook_user_id , post_message, post_id
					from posts
					where fanpage_id = $fanpageId) as b
					
					on a.object_id = b.post_id
					
					left join 
					
					(select facebook_user_id , comment_message, comment_id
					from comments
					where fanpage_id = $fanpageId) as c
					
					on a.object_id = c.comment_id
					
					) as d
					left join 
					
					fans f
					on d.facebook_user_id= f.facebook_user_id  
					order by created_time ASC";

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
	
	public function getFanpagePointLogByHour($fanpageId, $date) {
		$select = "SELECT sum(giving_points)as sum , date_format(created_time, '%Y-%m-%d %H:00:00')as hours FROM fancrank.point_log
					where fanpage_id = $fanpageId &&
						created_time > $date
					group by hours";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	
	public function getFanpagePoints($fanpage){
		$select= "
		SELECT 'Month' as time ,sum(giving_points) as points FROM fancrank.point_log where
		Month(curdate()) = Month(created_time)
		&& year(curdate()) = year(created_time)
		&& fanpage_id = $fanpage
		
		union
		
		SELECT 'Week' as time ,sum(giving_points) as points FROM fancrank.point_log where
		yearweek(curdate()) = yearweek(created_time)
		&& fanpage_id = $fanpage
		
		union
		
		SELECT 'Today' as time ,sum(giving_points) as points FROM fancrank.point_log where
		Date(curdate()) = date(created_time)
		&& fanpage_id = $fanpage
		
		union
		
		SELECT 'all' as time ,sum(giving_points) as points 
		FROM fancrank.point_log where fanpage_id = $fanpage
		";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	
	public function getPointsByType($fanpageId){
		
		$select= "SELECT object_type, sum(giving_points) as points, sum(bonus) as bonus FROM fancrank.point_log 
					where fanpage_id = $fanpageId
					group by object_type";
		
		return $this->getAdapter()->fetchAll($select);
		
	
	}
}

