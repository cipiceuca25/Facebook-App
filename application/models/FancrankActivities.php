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
		/*
		$where = array('facebook_user_id = ?' => $facebook_user_id, 'fanpage_id =?'=> $fanpage_id);
		$order = 'created_time DESC';
		$count = $limit;
		$offset= 0;
		$found = $this -> findAll($where,$order, $count, $offset);
		*/
		$query = $this->select()
		->from($this)
		->where('fanpage_id = ? ', $fanpage_id)
		->where('facebook_user_id = ?', $facebook_user_id)
		//->orWhere('target_user_id = ?', $facebook_user_id)
		->order('created_time DESC')
		->limit($limit);
		//where (a and b) or (b and c)
		return $this->fetchAll($query);
	}
	
	public function getRecentActivitiesInRealTime($facebook_user_id, $fanpage_id, $limit) {
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		
		$select = "select p.created_time, concat('post-',p.post_type) as activity_type, p.post_id as event_object, $fanpage_id as fanpage_id, p.facebook_user_id, f.facebook_user_name, $fanpage_id as target_user_id, '" .$fanpageName ."' as target_user_name   
					from posts p inner join facebook_users f on (p.facebook_user_id = f.facebook_user_id)
					where p.fanpage_id = $fanpage_id and p.facebook_user_id = $facebook_user_id
					union 
					select c.created_time, concat('comment-',c.comment_type) as activity_type, c.comment_id as event_object, $fanpage_id as fanpage_id, c.facebook_user_id, f.facebook_user_name, c.comment_post_id as target_user_id, '' as target_user_name
					from comments c inner join facebook_users f on (c.facebook_user_id = f.facebook_user_id)
					where c.fanpage_id = $fanpage_id and c.facebook_user_id = $facebook_user_id
					union
					select l.created_time, concat('like-',l.post_type) as activity_type, l.post_id as event_object, $fanpage_id as fanpage_id, l.facebook_user_id, f.facebook_user_name, l.post_id as target_user_id, l.post_type as target_user_name
					from likes l inner join facebook_users f on (l.facebook_user_id = f.facebook_user_id)
					where l.fanpage_id = $fanpage_id and l.facebook_user_id = $facebook_user_id
					union
					select fc.created_time, fc.activity_type, fc.event_object, fc.fanpage_id, fc.facebook_user_id, fc.facebook_user_name, fc.target_user_id, fc.target_user_name
					from fancrank_activities fc
					where fc.fanpage_id = $fanpage_id and (fc.activity_type = 'follow' or fc.activity_type = 'unfollow') and fc.facebook_user_id = $facebook_user_id
					order by created_time DESC
				";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		$result = $this->getAdapter()->fetchAll($select);
		
		//Zend_Debug::dump($result);
		$commentModel = new Model_Comments();
		$postModel = new Model_Posts();
		$photo = new Model_Photos();
		
		$finalResult = array();
		foreach ($result as $row) {
			switch($row['activity_type']) {
				case 'comment':
					if(is_numeric($row['event_object'])) {
						$row['target_user_id'] = $fanpage_id;
						$row['target_user_name'] = $fanpageName;
					}else {
						$select = "select p.facebook_user_id, f.facebook_user_name
								from posts p inner join facebook_users f on (p.facebook_user_id = f.facebook_user_id)
								where p.post_id = '" .$row['target_user_id'] ."'";
						$post = $this->getAdapter()->fetchRow($select);
						//Zend_Debug::dump($post);
						$row['target_user_id'] = $post['facebook_user_id'];
						$row['target_user_name'] = $post['facebook_user_name'];
					}
					break;
				case 'like':
					switch($row['target_user_name']) {
						case 'album': 					
							$row['target_user_id'] = $fanpage_id;
							$row['target_user_name'] = $fanpageName;
							break;
						case 'photo': 
							if($photo->findRow($row['target_user_id'])) {
								$row['target_user_id'] = $fanpage_id;
								$row['target_user_name'] = $fanpageName;
								
							}else {
								$select = "select p.facebook_user_id, f.facebook_user_name
								from posts p inner join facebook_users f on (p.facebook_user_id = f.facebook_user_id)
								where p.post_id = '" .$row['target_user_id'] ."'";
								$post = $this->getAdapter()->fetchRow($select);
								$row['target_user_id'] = $post['facebook_user_id'];
								$row['target_user_name'] = $post['facebook_user_name'];
						
							}
							
							break;
						default:
							if(substr_count($row['target_user_name'], '_comment') > 0) {
								$select = "select c.facebook_user_id, f.facebook_user_name
								from comments c inner join facebook_users f on (c.facebook_user_id = f.facebook_user_id)
								where c.comment_id = '" .$row['target_user_id'] ."'";
								$post = $this->getAdapter()->fetchRow($select);
							}else {
								$select = "select p.facebook_user_id, f.facebook_user_name
										from posts p inner join facebook_users f on (p.facebook_user_id = f.facebook_user_id)
										where p.post_id = '" .$row['target_user_id'] ."'";
								$post = $this->getAdapter()->fetchRow($select);
							} 
							if(empty($post)) {
								$row['target_user_id'] = $fanpage_id;
								$row['target_user_name'] = $fanpageName;
							}else {
								Zend_Debug::dump($post);
								$row['target_user_id'] = $post['facebook_user_id'];
								$row['target_user_name'] = $post['facebook_user_name'];
							}
						break;
					}
				default: break;
			}
			$finalResult[] = $row;
		}
		
		return $finalResult;
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

