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
	
	public function getBadgeNameList() {
		$query = $this->select()
					->from($this, array('id','name'))
					->where('type = ?', 'default')
					->group('name')
					->order('id');
		
		$result = $this->fetchAll($query);
		
		$finalList = array();
		foreach ($result as $key => $list) {
			$finalList[$list['id']] = $list['name'];
		}
		return $finalList;
	}
	
	public function getBadgesByType($type) {
		$query = $this->select()
			->where('type = ?', $type)
			->order('id');
		
		return $this->fetchAll($query);
	}
	
	public function getAllBadges($fanpageId){
		$select = "select * from
				
				(SELECT b.id, b.name, b.description, b.quantity, if (f.weight <=> null, b.weight, f.weight) as weight,  if (f.style_name <=> null, b.stylename, f.style_name) as stylename, 
		if (f.active <=> null, 1, f.active) as active,
		b.picture
		FROM badges b
		left join fancrank.fanpage_badges f
		on f.badge_id = b.id && fanpage_id = $fanpageId) as x
		where x.active = 1";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
}

