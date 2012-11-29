<?php

class Model_FanpageBadges extends Model_DbTable_FanpageBadges
{
	public function findByType($fanpage_id, $type) {
		$query = $this->getDefaultAdapter()->select()
					->from(array('f' => 'fanpage_badges'), array('f.fanpage_id'))
					->join(array('b' => 'badges'), 'f.badge_id = b.id', array('b.*'))
					->where('f.fanpage_id = ?', $fanpage_id)
					->where('b.type = ?', $type)
					->limit(10);
		return $this->getDefaultAdapter()->fetchAll($query);
	}
	
	public function findRemaindBadgeByUser($fanpage_id, $facebook_user_id) {
		$query = "select f.fanpage_id, b.* from badges b, fanpage_badges f where f.badge_id = b.id and f.fanpage_id = $fanpage_id 
			and b.id not in (select e.badge_id from badge_events e where e.facebook_user_id = $facebook_user_id 
			and e.fanpage_id = $fanpage_id) limit 10";
		
		return $this->getDefaultAdapter()->fetchAll($query);
	}
	
	public function getRedeemableBadges($fanpageId) {
		$query = $this->getDefaultAdapter()->select()
				->from(array('f' => 'fanpage_badges'), array('f.fanpage_id'))
				->join(array('b' => 'badges'), 'f.badge_id = b.id', array('b.*'))
				->where('f.fanpage_id = ?', $fanpageId)
				->where('f.redeemable = 1');
		return $this->getDefaultAdapter()->fetchAll($query);
	}
}

