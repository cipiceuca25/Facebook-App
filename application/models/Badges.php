<?php

class Model_Badges extends Model_DbTable_Badges
{
	public function getNumBadges() {
		$select = "	SELECT count(*) as count from badges";
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function findByFanpageLevel($level, $fanpage_id, $facebook_user_id) {
		$where = null;
		
		switch($level) {
			case 1: $where = "b.name = 'top-fan' or b.name = 'top-post'"; break;
			case 2: $where = "b.name = 'top-fan' or b.name = 'top-post' or b.name = 'someone-likes-you'"; break;
			case 3: $where = "b.name = 'top-fan' or b.name = 'top-post' or b.name = 'someone-likes-you'"; break;
			default: break; 
		}
		
		if($where) {
			try {
				$select = "select b.* from badges b left join badge_events e on (b.id = e.badge_id and e.fanpage_id = $fanpage_id and e.facebook_user_id = $facebook_user_id) 
				where ($where) and e.id is null";
				
				$result = $this->getDefaultAdapter()->fetchAll($select);

				return $result;
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
		
		return;
	}
}

