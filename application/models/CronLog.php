<?php

class Model_CronLog extends Model_DbTable_CronLog
{

	public function isValid() {
		
	}
	
	public function getLastUpdate($fanpage_id) {
		$select = "SELECT date(end_time)as end_time FROM cron_log where fanpage_id = $fanpage_id and status = 'success' order by end_time DESC limit 1";
	
		return $this->getAdapter()->fetchAll($select);
		
	}
	
	
	public function getFirstCron($fanpage_id){
		
		$select = "SELECT date(end_time)as end_time FROM cron_log where fanpage_id = $fanpage_id and status = 'success' order by end_time ASC limit 1";
		return $this->getAdapter()->fetchAll($select);
	}
}

