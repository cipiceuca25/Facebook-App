<?php

class Model_LeaderboardLog extends Zend_Db_Table_Abstract
{
	protected $_name = 'leaderboard_log';
	protected $_primary = 'id';
	
	public function getLastWeekTopFans($fanpageId) {
		$query = $this->getDefaultAdapter()
					->select()
					->from(array('l'=>'leaderboard_log'), array('facebook_user_id', 'rank', 'count'))
					->join(array('f'=>'fans'), 'f.facebook_user_id = l.facebook_user_id AND f.fanpage_id = l.fanpage_id', array('fan_first_name','fan_last_name' ))
					->where('l.type = "top_fans"')
					->where('l.fanpage_id = ?', $fanpageId)
					->where('DATEDIFF(now(), l.end_time) <= 7')
					->limit(5);
		
		$result =  $this->getDefaultAdapter()->fetchAll($query);
		return empty($result) ? array() : $result;		
	}
	
	public function getLastMonthTopFans($fanpageId) {
		$query = $this->getDefaultAdapter()
				->select()
				->from(array('l'=>'leaderboard_log'), array('facebook_user_id', 'rank', 'count'))
				->join(array('f'=>'fans'), 'f.facebook_user_id = l.facebook_user_id AND f.fanpage_id = l.fanpage_id', array('fan_first_name','fan_last_name' ))
				->where('l.type = "Top-Fan-Month"')
				->where('l.fanpage_id = ?', $fanpageId)
				->where('PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM NOW()), EXTRACT(YEAR_MONTH FROM l.end_time)) <= 1')
				->limit(5);
		
		$result =  $this->getDefaultAdapter()->fetchAll($query);
		return empty($result) ? array() : $result;
	}
}

