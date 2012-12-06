<?php

class Model_BadgeItems extends Model_DbTable_BadgeItems
{
	public function getItemsByBadges($fanpageId, $badge_id) {
		$query = $this->getDefaultAdapter()->select()
		->from(array('r'=>'badge_items'), array('r.id'))
		->where('r.badge_id = ?', $badge_id);
	
		return $this->getDefaultAdapter()->fetchAll($query);
	}
}

