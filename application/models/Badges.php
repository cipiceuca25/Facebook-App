<?php

class Model_Badges extends Model_DbTable_Badges
{

	public function getNumBadges() {
		$select = "	SELECT count(*) as count from badges  
					";
		return $this->getAdapter()->fetchAll($select);
	}
	
	
}

