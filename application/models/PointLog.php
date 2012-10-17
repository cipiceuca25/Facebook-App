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
		$query = $this->select();	
	}
		
		
	

	public function getFanpagePointLog($fanpageId, $limit=1000) {
		$query = $this->select()
			->where('fanpage_id = ?', $fanpageId)
			->order('created_time desc')
			->limit($limit);
		return $this->fetchAll($query)->toArray();

	}
}

