<?php

class Model_FancrankActivities
{
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

}

