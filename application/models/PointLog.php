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
}

