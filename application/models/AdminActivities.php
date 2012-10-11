<?php

class Model_AdminActivities extends Model_DbTable_AdminActivities
{
	public function getAllActivitiesSince($since) {
		$query = $this->select()->where('created_time > ?', $since)->limit(1000);
		return $this->fetchAll($query);
	}
}

