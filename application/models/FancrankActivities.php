<?php

class Model_FancrankActivities extends Model_DbTable_FancrankActivities
{
	public function addActivities($data){
		
		//$found = $this -> find($created_time, $activity_type, $event_object, )->current();
	
		//if (empty($found)) {
		
			$insert = $this->insert($data);
		//}
		
	
	}
	
	
	//we shouldn't need to use this. since it would be close to impossible to pass in the created_time
	public function getActivities($created_time, $activity_type, $event_object){
		
		$found = $this -> find($created_time, $activity_type, $event_object)->current();
		
		if (!empty($found)) {
		
			return $found;
		}
		
		echo 'not found';
		
	}
	
	public function getRecentActivities($facebook_user_id, $fanpage_id, $limit){
		$where = array('facebook_user_id' => $facebook_user_id, 'fanpage_id'=> $fanpage_id);
		$order = 'created_time DESC';
		$count = $limit;
		$offset= 0;
		$found = $this -> findAll($where,$order, $count, $offset);
		return $found;
	}
	/*
	public function getFeed($limit) {
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		$db = Zend_Db::factory($config->resources->db);
		
		$select = "select feed.*, f.facebook_user_name
					from
					(
					select p.facebook_user_id, CONCAT(p.post_id, ',', p.fanpage_id, ',', p.post_message) as body, 'post' as type, updated_time as timestamp from posts p
					union all
					select c.facebook_user_id, CONCAT(c.comment_id, ',', c.fanpage_id, ',', c.comment_message) as body,  'comment' as type, created_time as timestamp from comments c
					union all
					select s.facebook_user_id, s.facebook_user_id_subscribe_to as body, 'follow' as type, created_time as timestamp from subscribes s
					) as feed inner join facebook_users f on (feed.facebook_user_id = f.facebook_user_id)
					order by feed.timestamp desc
				";
	
		if($limit !== false)
			$select = $select . " LIMIT $limit";
	
		return $db->fetchAll($select);
	}
	
	public function feedJsonEncode() {
		
	}
	*/
	
	
}

