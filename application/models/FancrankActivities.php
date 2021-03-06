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

	public function getAllActivities($fanpage_id, $limit){
		$select = "select * from 
					
					(
					(Select c.fanpage_id, c.facebook_user_id, 
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name, 
							concat('comment-', c.comment_type)as activity_type, 
							p.post_id as event_object, 
							p.facebook_user_id as target_user_id,
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
							c.created_time, c.comment_message as message
							from 
							comments c, posts p 
							where c.fanpage_id =  $fanpage_id and  c.comment_post_id = p.post_id
							order by created_time DESC
							limit $limit)
					
					union 
					
					(SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
							FROM posts p
							where p.fanpage_id = $fanpage_id
					 		order by created_time DESC
							limit $limit)

					union 
					
					
					(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
							from
							(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and   l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
							union 
							select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and  l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
							) as liketable, posts p
							where liketable.post_id = p.post_id 
							order by created_time DESC
							limit $limit)
					
					union 
					
					(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message 
						from
						(select *, concat('like-comment') as activity_type from likes l where l.likes = 1  and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
						union 
						select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0  and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
						)as liketable , comments c
									
						where liketable.post_id = c.comment_id
						order by created_time DESC
						limit $limit)
					
					union

					(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message  
						from fancrank_activities 
						where fanpage_id = $fanpage_id
						order by created_time DESC
						limit $limit)	
						
					union
					
					(select fanpage_id, facebook_user_id, (select fanpage_name from fanpages where fanpage_id = $fanpage_id) as facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message
					from admin_activities
					where fanpage_id = $fanpage_id
					order by created_time DESC
					limit $limit)
					
					) as act
					group by fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, created_time, message
					
					order by created_time DESC
					limit $limit";
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
				
			$finalResult[] = $row;
		}
		
		if (count($finalResult) > $limit){
		
			$activities = array_slice($activities, 0, $limit);
		
		}
		
		return $finalResult;
	}
	
	
	public function getRecentActivities($facebook_user_id, $fanpage_id, $limit){
		$limit *=2;
		$select= "
					select * from 
					
					(
					(Select c.fanpage_id, c.facebook_user_id, 
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name, 
							concat('comment-', c.comment_type)as activity_type, 
							p.post_id as event_object, 
							p.facebook_user_id as target_user_id,
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
							c.created_time, c.comment_message as message
							from 
							comments c, posts p 
							where c.fanpage_id =  $fanpage_id  and c.facebook_user_id = $facebook_user_id and c.comment_post_id = p.post_id
							order by created_time DESC
							limit $limit)
					
					union 
					
					(SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
							FROM posts p
							where p.facebook_user_id = $facebook_user_id and p.fanpage_id = $fanpage_id
					 		order by created_time DESC
							limit $limit)

					union 
					
					
					(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
							from
							(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and  l.facebook_user_id = $facebook_user_id and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
							union 
							select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and l.facebook_user_id = $facebook_user_id and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
							) as liketable, posts p
							where liketable.post_id = p.post_id 
							order by created_time DESC
							limit $limit)
					
					union 
					
					(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message 
						from
						(select *, concat('like-comment') as activity_type from likes l where l.likes = 1 and  l.facebook_user_id = $facebook_user_id and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
						union 
						select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0 and l.facebook_user_id = $facebook_user_id and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
						)as liketable , comments c
									
						where liketable.post_id = c.comment_id
						order by created_time DESC
						limit $limit)
					
					union 
					
					(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message  
						from fancrank_activities 
						where fanpage_id = $fanpage_id && facebook_user_id = $facebook_user_id
						order by created_time DESC
						limit $limit)
					
					union

					/* activities done to target user*/
					(Select c.fanpage_id, c.facebook_user_id, 
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name, 
							concat('comment-', c.comment_type)as activity_type, 
							p.post_id as event_object, 
							p.facebook_user_id as target_user_id,
							(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
							c.created_time, c.comment_message as message
							from 
							comments c, posts p 
							where c.fanpage_id = $fanpage_id  and p.facebook_user_id = $facebook_user_id and c.comment_post_id = p.post_id
							order by created_time DESC
							limit $limit)

					union

					(select p.fanpage_id, 
						   l.facebook_user_id,
						   (select f.facebook_user_name from facebook_users f where f.facebook_user_id = l.facebook_user_id) as facebook_user_name,
						   (case when (l.likes = 1) then concat('like-', p.post_type) else concat('unlike-', p.post_type) end ) as activity_type,
						   p.post_id as event_object,
						   p.facebook_user_id as target_user_id,
						   (select f.facebook_user_name from facebook_users f where f.facebook_user_id = $facebook_user_id) as  target_user_name,
						   l.created_time as created_time,
						   p.post_message as message
					from posts p, likes l 
					where l.post_id = p.post_id and p.facebook_user_id = $facebook_user_id
							and l.fanpage_id = $fanpage_id and l.fanpage_id = p.fanpage_id
							
					order by l.created_time DESC
					limit $limit	)

					union 

					(select c.fanpage_id, 
						   l.facebook_user_id,
						   (select f.facebook_user_name from facebook_users f where f.facebook_user_id = l.facebook_user_id) as facebook_user_name,
						   (case when (l.likes = 1) then concat('like-', c.comment_type) else concat('unlike-', c.comment_type) end ) as activity_type,
						   c.comment_post_id as event_object,
						   c.facebook_user_id as target_user_id,
						   (select f.facebook_user_name from facebook_users f where f.facebook_user_id = $facebook_user_id) as  target_user_name,
						   l.created_time as created_time,
						   c.comment_message as message
					from comments c, likes l 
					where l.post_id = c.comment_id and c.facebook_user_id = $facebook_user_id and l.fanpage_id = $fanpage_id and l.fanpage_id = c.fanpage_id
					order by l.created_time DESC
					limit $limit)

					union

					(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message  
						from fancrank_activities 
						where fanpage_id = $fanpage_id && target_user_id = $facebook_user_id
						order by created_time DESC
						limit $limit)	
						
					union
					
					(select fanpage_id, facebook_user_id, (select fanpage_name from fanpages where fanpage_id = $fanpage_id) as facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message
					from admin_activities
					where fanpage_id = $fanpage_id && target_user_id = $facebook_user_id
					order by created_time DESC
					limit $limit)
								
					) as act
					group by fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, created_time, message
					
					order by created_time DESC
					limit $limit ";
		
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
			
			$finalResult[] = $row;
		}
		
		if (count($finalResult) > $limit){
		
			$activities = array_slice($activities, 0, $limit);
		
		}

		return $finalResult;
	}
	
	public function getRecentFanpageActivities($fanpage_id, $limit=50) {
		$oldLimit = $limit;
		$limit *=2;
		$select= "
			select * from
			(
			(Select c.fanpage_id, c.facebook_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name,
			concat('comment-', c.comment_type)as activity_type,
			p.post_id as event_object,
			p.facebook_user_id as target_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
			c.created_time, c.comment_message as message
			from
			comments c, posts p
			where c.fanpage_id =  $fanpage_id  and c.comment_post_id = p.post_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
			FROM posts p
			where p.fanpage_id = $fanpage_id
			order by created_time DESC
			limit $limit)
		
			union
				
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
			from
			(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			) as liketable, posts p
			where liketable.post_id = p.post_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message
			from
			(select *, concat('like-comment') as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			)as liketable , comments c
				
			where liketable.post_id = c.comment_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message
			from fancrank_activities
			where fanpage_id = $fanpage_id 
			order by created_time DESC
			limit $limit)
				
			) as act
			group by fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, created_time, message
				
			order by created_time DESC
			limit $limit ";
	
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
				
			$finalResult[] = $row;
		}
	
		if (count($finalResult) > $oldLimit){
			return array_slice($finalResult, 0, $oldLimit);
		}
	
		return $finalResult;
	}

	public function getRecentFanpageActivitiesSince($fanpage_id, $since=0, $limit=99999) {
		$date = new Zend_Date($since);
		$since = $date->toString('yyyy-MM-dd HH:mm:ss');
		$oldLimit = $limit;
		$limit *=2;

		$select= "
			select * from
			(
			(Select c.fanpage_id, c.facebook_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name,
			concat('comment-', c.comment_type)as activity_type,
			p.post_id as event_object,
			p.facebook_user_id as target_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
			c.created_time, c.comment_message as message
			from
			comments c, posts p
			where c.fanpage_id =  $fanpage_id  and c.comment_post_id = p.post_id
			order by created_time DESC
			limit $limit)
			
			union
			
			(SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
			FROM posts p
			where p.fanpage_id = $fanpage_id
			order by created_time DESC
			limit $limit)
			
			union
			
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
			from
			(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			) as liketable, posts p
			where liketable.post_id = p.post_id
			order by created_time DESC
			limit $limit)
			
			union
			
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message
			from
			(select *, concat('like-comment') as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			)as liketable , comments c
			
			where liketable.post_id = c.comment_id
			order by created_time DESC
			limit $limit)
			
			union
			
			(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message
			from fancrank_activities
			where fanpage_id = $fanpage_id
			order by created_time DESC
			limit $limit)
			
			) as act 
			where created_time > '" .$since ."'
			group by fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, created_time, message
			
			order by created_time DESC
			limit $limit ";
		
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}

			$finalResult[] = $row;
		}

		if (count($finalResult) > $oldLimit){
			return array_slice($finalResult, 0, $oldLimit);
		}

		return $finalResult;
	}
	
	public function getRecentFanpageLikeActivities($fanpage_id, $limit=50) {
		$select ="		
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
			from
			(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and not (l.post_type REGEXP '_comment[[:>:]]')
			) as liketable, posts p
			where liketable.post_id = p.post_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message
			from
			(select *, concat('like-comment') as activity_type from likes l where l.likes = 1 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0 and l.fanpage_id = $fanpage_id  and (l.post_type REGEXP '_comment[[:>:]]')
			)as liketable , comments c
				
			where liketable.post_id = c.comment_id
			order by created_time DESC
			limit $limit)";

		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
		
			$finalResult[] = $row;
		}
		
		return $finalResult;
	}
	
	public function getRecentFanpageCommentActivities($fanpage_id, $limit=50) {
		$select = "
				Select c.fanpage_id, c.facebook_user_id,
				(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name,
				concat('comment-', c.comment_type)as activity_type,
				p.post_id as event_object,
				p.facebook_user_id as target_user_id,
				(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
				c.created_time, c.comment_message as message
				from
				comments c, posts p
				where c.fanpage_id =  $fanpage_id  and c.comment_post_id = p.post_id
				order by created_time DESC
				limit $limit";
		
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
		
			$finalResult[] = $row;
		}
		
		return $finalResult;
	}
	
	public function getRecentFanpagePostActivities($fanpage_id, $limit=50) {
		$select = "
			SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
			FROM posts p
			where p.fanpage_id = $fanpage_id
			order by created_time DESC
			limit $limit
			";
		
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		//Zend_Debug::dump($result);
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		$finalResult = array();
		foreach ($result as $row) {
			if($row['target_user_id'] === $fanpage_id) {
				$row['target_user_name'] = $fanpageName;
			}
			if($row['facebook_user_id'] === $fanpage_id) {
				$row['facebook_user_name'] = $fanpageName;
			}
		
			$finalResult[] = $row;
		}
		
		return $finalResult;
	}
	
	public function getRecentActivitiesSince($facebook_user_id, $fanpage_id, $limit, $since) {
		$select = "select * from (

					(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message 
										from fancrank_activities 
										where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id and created_time > $since
										order by created_time DESC
										limit $limit)
					union 
					
					(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message  
											from fancrank_activities 
											where fanpage_id = $fanpage_id and target_user_id = $facebook_user_id and created_time > $since
											order by created_time DESC
											limit $limit)
					) as act
					
					order by created_time DESC
					limit $limit";
		
		$result = $this->getAdapter()->fetchAll($select);
		
		return $result;
	}
	
	public function getRecentActivitiesInRealTime($facebook_user_id, $fanpage_id, $limit) {
		$fanpageModel = new Model_Fanpages();
		$fanpageName = $fanpageModel->findRow($fanpage_id)->fanpage_name;
		
		$select = "select p.created_time, 'post' as activity_type, p.post_id as event_object, $fanpage_id as fanpage_id, p.facebook_user_id, f.facebook_user_name, $fanpage_id as target_user_id, '" .$fanpageName ."' as target_user_name   
					from posts p inner join facebook_users f on (p.facebook_user_id = f.facebook_user_id)
					where p.fanpage_id = $fanpage_id and p.facebook_user_id = $facebook_user_id
					union 
					select c.created_time, 'comment' as activity_type, c.comment_id as event_object, $fanpage_id as fanpage_id, c.facebook_user_id, f.facebook_user_name, c.comment_post_id as target_user_id, '' as target_user_name
					from comments c inner join facebook_users f on (c.facebook_user_id = f.facebook_user_id)
					where c.fanpage_id = $fanpage_id and c.facebook_user_id = $facebook_user_id
					union
					select l.created_time, 'likes' as activity_type, l.post_id as event_object, $fanpage_id as fanpage_id, l.facebook_user_id, f.facebook_user_name, l.post_id as target_user_id, l.post_type as target_user_name
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
					$row['activity_type'] = 'comment-'.$row["target_user_name"];
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
	
	public function getOverAllActivities($limit) {
		$select= "
			select * from	
			(
			(Select c.fanpage_id, c.facebook_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as facebook_user_name,
			concat('comment-', c.comment_type)as activity_type,
			p.post_id as event_object,
			p.facebook_user_id as target_user_id,
			(select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name,
			c.created_time, c.comment_message as message
			from
			comments c, posts p
			where c.comment_post_id = p.post_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(SELECT p.fanpage_id, p.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as facebook_user_name, concat('post-', p.post_type)as activity_type, post_id as event_object, p.fanpage_id as target_user_id, (select fp.fanpage_name from fanpages fp where fp.fanpage_id = p.fanpage_id) as target_user_name , p.created_time, p.post_message as message
			FROM posts p
			order by created_time DESC
			limit $limit)
			
			union
				
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, p.post_id as event_object, p.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = p.facebook_user_id) as target_user_name, liketable.updated_time as created_time, p.post_message as message
			from
			(select *, concat('like-', l.post_type) as activity_type from likes l where l.likes = 1 and  not (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-', l.post_type) as activity_type from likes l where l.likes = 0 and not (l.post_type REGEXP '_comment[[:>:]]')
			) as liketable, posts p
			where liketable.post_id = p.post_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(select liketable.fanpage_id, liketable.facebook_user_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = liketable.facebook_user_id) as facebook_user_name, activity_type, c.comment_post_id as event_object, c.facebook_user_id as target_id, (select f.facebook_user_name from facebook_users f where f.facebook_user_id = c.facebook_user_id) as target_user_name, liketable.updated_time as created_time, c.comment_message as message
			from
			(select *, concat('like-comment') as activity_type from likes l where l.likes = 1 and (l.post_type REGEXP '_comment[[:>:]]')
			union
			select *, concat('unlike-comment') as activity_type from likes l where l.likes = 0 and (l.post_type REGEXP '_comment[[:>:]]')
			)as liketable , comments c
				
			where liketable.post_id = c.comment_id
			order by created_time DESC
			limit $limit)
				
			union
				
			(select fanpage_id, facebook_user_id, facebook_user_name, activity_type, event_object, target_user_id, target_user_name, created_time, message
			from fancrank_activities
			order by created_time DESC
			limit $limit)
				
			) as act
				
			order by created_time DESC
			limit $limit ";
		
		$result = $this->getAdapter()->fetchAll($select);
		return $result;
		
	}
	
	public function getAllActivitiesSince($since) {
		$query = $this->select()->where('created_time > ?', $since)->limit(1000);
		return $this->fetchAll($query);
	}
	
	public function getAllNonAdminActivitiesSince($since) {
		$query = $this->select()
			->where('created_time > ?', $since)
			->where('fanpage_id != facebook_user_id')->limit(1000);
		return $this->fetchAll($query);
	}
	
	
	public function getNumofInteractionsWithinDays ( $fanpageId, $days){
		$select = "SELECT count(*) FROM fancrank.fancrank_activities 
					where
					fanpage_id = $fanpageId &&
					timestampdiff(DAY, created_time, curdate()) < 7";
		$result = $this->getAdapter()->fetchAll($select);
		if(empty($result['count'])) {
			return 0;
		}
		
		return $result['count'];
	}
	
	public function getFancrankInteractionsGraph($fanpageId, $time, $graph){
		
		if($graph){
			$select = "select * from (
					select count(*) as 'all',
					sum(case when activity_type like 'post-%' then 1 else 0 end ) as posts, 
					sum(case when activity_type like 'comment-%' then 1 else 0 end ) as comments, 
					sum(case when activity_type like 'like-%' then 1 else 0 end ) as likes, 
					sum(case when activity_type like 'unlike-%' then 1 else 0 end ) as unlike, 
					sum(case when activity_type = 'follow' then 1 else 0 end ) as follow, 
					sum(case when activity_type = 'unfollow' then 1 else 0 end ) as unfollow, 
					sum(case when activity_type = 'redeem%'  then 1 else 0 end ) as redeem, 
					created_time from fancrank_activities
					where fanpage_id = $fanpageId
					group by date(created_time)
					order by created_time ASC
					) as x ";
			
			switch($time){
				case 'month':
					$select= $select. " where month(x.created_time) = month(curdate()) && year(x.created_time) = year(curdate())";
					break;
				case 'week':
					$select= $select." where yearweek(x.created_time) = yearweek(curdate())";
					break;
				case 'today':
					$select= $select."  where date(x.created_time) = date(curdate())";
					break;
			}

			$result = $this->getAdapter()->fetchAll($select);
			
			if ($result){
				$result[0]['total_all'] = $result[0]['all'];
				$result[0]['total_posts'] =$result[0]['posts'];
				$result[0]['total_comments'] = $result[0]['comments'];
				$result[0]['total_likes'] = $result[0]['likes'];
				$result[0]['total_unlike'] = $result[0]['unlike'];
				$result[0]['total_follow'] = $result[0]['follow'];
				$result[0]['total_unfollow'] = $result[0]['unfollow'];
				$result[0]['total_redeem'] = $result[0]['redeem'];

				for($i = 1; $i < count($result); $i++ ){
			
					$result[$i]['total_all'] = $result[$i]['all'] +  $result[$i-1]['total_all'] ;
					$result[$i]['total_posts'] = $result[$i]['posts'] +  $result[$i-1]['total_posts'] ;
					$result[$i]['total_comments'] = $result[$i]['comments'] +  $result[$i-1]['total_comments'] ;
					$result[$i]['total_likes'] = $result[$i]['likes'] +  $result[$i-1]['total_likes'] ;
					$result[$i]['total_unlike'] = $result[$i]['unlike'] +  $result[$i-1]['total_unlike'] ;
					$result[$i]['total_follow'] = $result[$i]['follow'] +  $result[$i-1]['total_follow'] ;
					$result[$i]['total_unfollow'] = $result[$i]['unfollow'] +  $result[$i-1]['total_unfollow'] ;
					$result[$i]['total_redeem'] = $result[$i]['redeem'] +  $result[$i-1]['total_redeem'] ;
				}
			}
			
			return $result;
		}else{
			$select = "SELECT event_object, a.facebook_user_id, f.fan_name, f.fan_gender, activity_type, a.message , a.created_time 
						FROM fancrank_activities a 
							left join fans f
							on a.facebook_user_id = f.facebook_user_id && a.fanpage_id = f.fanpage_id 
						where a.fanpage_id = $fanpageId
						";
			
			switch($time){
				
				case 'month':
					$select = $select . " && year(a.created_time) = year(curdate()) && 
										month(a.created_time) = month(curdate())  order by created_time DESC";
					break;
				case 'week':
					$select = $select . " && yearweek(a.created_time) = yearweek(curdate())  order by created_time DESC";
					break;
				case 'today':
					$select = $select . " && date(a.created_time) = date(curdate()) order by created_time DESC";
					break;
				default:
					$select = $select . " order by created_time DESC";
					break;
				
				
			}
			
			
			$result = $this->getAdapter()->fetchAll($select);
			
			
			return $result;
			
			
			
		}
		
	}
	
	public function getFancrankInteractionsNumber($fanpageId){
		$select = "select count(*) as 'all',
				sum(case when date(created_time) = date(curdate()) then 1 else 0 end ) as today,
				sum(case when yearweek(created_time) = yearweek(curdate()) then 1 else 0 end ) as week,
				sum(case when (year(created_time) = year(curdate()) && month(created_time) = month(curdate())) then 1 else 0 end ) as month
				from fancrank_activities
				where fanpage_id = $fanpageId";
		
		$result = $this->getAdapter()->fetchAll($select);
		$result[0]['today'] = ($result[0]['today'] == null)?0:$result[0]['today'];
		$result[0]['month'] = ($result[0]['month'] == null)?0:$result[0]['month'];
		$result[0]['week'] = ($result[0]['week'] == null)?0:$result[0]['week'];
		return $result;
	}
	
	public function getFancrankInteractionsUniqueUsersNumber($fanpageId){
		$select = "select count(distinct facebook_user_id) as 'all',
					count(distinct case when date(created_time) = date(curdate()) then facebook_user_id end ) as today,
					count(distinct case when yearweek(created_time) = yearweek(curdate()) then facebook_user_id end ) as week,
					count(distinct case when (year(created_time) = year(curdate()) && month(created_time) = month(curdate())) then facebook_user_id end ) as month 
					from fancrank_activities
					where fanpage_id = $fanpageId";
	
		$result = $this->getAdapter()->fetchAll($select);
		$result[0]['today'] = ($result[0]['today'] == null)?0:$result[0]['today'];
		$result[0]['month'] = ($result[0]['month'] == null)?0:$result[0]['month'];
		$result[0]['week'] = ($result[0]['week'] == null)?0:$result[0]['week'];
		return $result;
	}
	
	public function getFancrankInteractionsUniqueUsersGraph($fanpageId, $time, $graph){
		
		
		if($graph){
			$select = "select count(distinct facebook_user_id) as 'all',
						count(distinct case when activity_type like 'post-%' then facebook_user_id end ) as posts, 
						count(distinct case when activity_type like 'comment-%' then facebook_user_id end ) as comments, 
						count(distinct case when activity_type like 'like-%' then facebook_user_id end ) as likes, 
						count(distinct case when activity_type like 'unlike-%' then facebook_user_id end ) as unlikes, 
						count(distinct case when activity_type = 'follow' then facebook_user_id end ) as follow, 
						count(distinct case when activity_type = 'unfollow' then facebook_user_id end ) as unfollow, 
						count(distinct case when activity_type like 'redeem%' then facebook_user_id end ) as redeem,
						created_time from fancrank_activities
						where fanpage_id = $fanpageId && fanpage_id != facebook_user_id
						";
			
			
			switch($time){
				case 'month':
					$select = $select . " && year(created_time) = year(curdate()) && month(created_time) = month(curdate())
										group by date(created_time)
										order by created_time ASC ";
					$select2 = "select count(distinct facebook_user_id) as 'all',
								count(distinct case when activity_type like 'post-%' then facebook_user_id end ) as posts, 
								count(distinct case when activity_type like 'comment-%' then facebook_user_id end ) as comments, 
								count(distinct case when activity_type like 'like-%' then facebook_user_id end ) as likes, 
								count(distinct case when activity_type like 'unlike-%' then facebook_user_id end ) as unlikes, 
								count(distinct case when activity_type = 'follow' then facebook_user_id end ) as follow, 
								count(distinct case when activity_type = 'unfollow' then facebook_user_id end ) as unfollow, 
								count(distinct case when activity_type like 'redeem%' then facebook_user_id end ) as redeem,
								y.created_time from fancrank_activities f, 
								
								(select distinct date(created_time) as created_time from fancrank_activities
								where fanpage_id != facebook_user_id
									&& fanpage_id = $fanpageId && year(created_time) = year(curdate()) && month(created_time) = month(curdate())
								order by created_time ASC) as y
								
								where fanpage_id = $fanpageId && fanpage_id != facebook_user_id && date(f.created_time)  <= date(y.created_time)
										&& year(f.created_time) = year(curdate()) && month(f.created_time) = month(curdate())
								group by date(y.created_time)
								order by y.created_time ASC";
					break;
				case 'week':
					$select = $select . " && yearweek(created_time) = yearweek(curdate())
										group by date(created_time)
										order by created_time ASC ";
					$select2 = "select count(distinct facebook_user_id) as 'all',
								count(distinct case when activity_type like 'post-%' then facebook_user_id end ) as posts, 
								count(distinct case when activity_type like 'comment-%' then facebook_user_id end ) as comments, 
								count(distinct case when activity_type like 'like-%' then facebook_user_id end ) as likes, 
								count(distinct case when activity_type like 'unlike-%' then facebook_user_id end ) as unlikes, 
								count(distinct case when activity_type = 'follow' then facebook_user_id end ) as follow, 
								count(distinct case when activity_type = 'unfollow' then facebook_user_id end ) as unfollow, 
								count(distinct case when activity_type like 'redeem%' then facebook_user_id end ) as redeem,
								y.created_time from fancrank_activities f, 
								
								(select distinct date(created_time) as created_time from fancrank_activities
								where fanpage_id != facebook_user_id
									&& fanpage_id = $fanpageId && yearweek(created_time) = yearweek(curdate())
								order by created_time ASC) as y
								
								where fanpage_id = $fanpageId && fanpage_id != facebook_user_id && date(f.created_time)  <= date(y.created_time)
										&& yearweek(f.created_time) = yearweek(curdate())
								group by date(y.created_time)
								order by y.created_time ASC";
					break;
				case 'today':
					$select= $select . "&& date(created_time) = date(curdate())
										group by hour(created_time)
										order by created_time ASC";
					$select2 = "select count(distinct facebook_user_id) as 'all',
								count(distinct case when activity_type like 'post-%' then facebook_user_id end ) as posts, 
								count(distinct case when activity_type like 'comment-%' then facebook_user_id end ) as comments, 
								count(distinct case when activity_type like 'like-%' then facebook_user_id end ) as likes, 
								count(distinct case when activity_type like 'unlike-%' then facebook_user_id end ) as unlikes, 
								count(distinct case when activity_type = 'follow' then facebook_user_id end ) as follow, 
								count(distinct case when activity_type = 'unfollow' then facebook_user_id end ) as unfollow, 
								count(distinct case when activity_type like 'redeem%' then facebook_user_id end ) as redeem,
								y.created_time from fancrank_activities f, 
								
								(select distinct date(created_time) as created_time from fancrank_activities
								where fanpage_id != facebook_user_id
									&& fanpage_id = $fanpageId && date(created_time) = date(curdate())
								order by created_time ASC) as y
								
								where fanpage_id = $fanpageId && fanpage_id != facebook_user_id && date(f.created_time)  <= date(y.created_time)
										&& date(f.created_time) = date(curdate())
								group by hour(y.created_time)
								order by y.created_time ASC";
					break;
				default:
					$select = $select.' group by date(created_time)
										order by created_time ASC';
					$select2 = "select count(distinct facebook_user_id) as 'all',
								count(distinct case when activity_type like 'post-%' then facebook_user_id end ) as posts,
								count(distinct case when activity_type like 'comment-%' then facebook_user_id end ) as comments,
								count(distinct case when activity_type like 'like-%' then facebook_user_id end ) as likes,
								count(distinct case when activity_type like 'unlike-%' then facebook_user_id end ) as unlikes,
								count(distinct case when activity_type = 'follow' then facebook_user_id end ) as follow,
								count(distinct case when activity_type = 'unfollow' then facebook_user_id end ) as unfollow,
								count(distinct case when activity_type like 'redeem%' then facebook_user_id end ) as redeem,
								y.created_time from fancrank_activities f,
									
								(select distinct date(created_time) as created_time from fancrank_activities
								where fanpage_id != facebook_user_id
								&& fanpage_id = $fanpageId
								order by created_time ASC) as y
									
								where fanpage_id = $fanpageId && fanpage_id != facebook_user_id && date(f.created_time)  <= date(y.created_time)
								group by date(y.created_time)
								order by y.created_time ASC";
					break;
			}
			
			$result = $this->getAdapter()->fetchAll($select);
			$result2 = $this->getAdapter()->fetchAll($select2);	
			for($i = 0; $i < count($result); $i++ ){
			
				$result[$i]['total_all'] =  $result2[$i]['all'] ;
				$result[$i]['total_posts'] = $result2[$i]['posts'] ;
				$result[$i]['total_comments'] =$result2[$i]['comments'] ;
				$result[$i]['total_likes'] =  $result2[$i]['likes'] ;
				$result[$i]['total_unlikes'] = $result2[$i]['unlikes'] ;
				$result[$i]['total_follow'] =$result2[$i]['follow'] ;
				$result[$i]['total_unfollow'] =  $result2[$i]['unfollow'] ;
				$result[$i]['total_unfollow'] =  $result2[$i]['redeem'] ;
				$result[$i]['created_time2'] = $result2[$i]['created_time'] ;
			
			}
			
			
			
			return $result;
		}else{
			$select = "select created_time, activity_type, facebook_user_id, facebook_user_name, count(facebook_user_id) as num_of_activities,
			sum(case when activity_type like 'post-%' then 1 else 0 end ) as posts,
			sum(case when activity_type like 'comment-%' then 1 else 0 end ) as comments,
			sum(case when activity_type like 'like-%' then 1 else 0 end ) as likes,
			sum(case when activity_type like 'unlike-%' then 1 else 0 end ) as unlike,
			sum(case when activity_type = 'follow' then 1 else 0 end ) as follow,
			sum(case when activity_type = 'unfollow' then 1 else 0 end ) as unfollow,
			sum(case when activity_type like 'redeem%'  then 1 else 0 end ) as redeem
			from
				
			(select * from
				
			fancrank.fancrank_activities
			where fanpage_id != facebook_user_id && $fanpageId
			order by created_time DESC ) as b
			";
				
			switch($time){
			
			case 'month':
				$select = $select . " where year(created_time) = year(curdate()) &&
										month(created_time) = month(curdate())  group by facebook_user_id
										order by created_time DESC ";
					break;
				case 'week':
				$select = $select . "where yearweek(created_time) = yearweek(curdate()) group by facebook_user_id
										order by created_time DESC ";
					break;
				case 'today':
				$select = $select . "where date(created_time) = date(curdate()) order by created_time DESC";
					break;
				default:
						$select = $select . "group by facebook_user_id
						order by created_time DESC ";
					break;
					
			}
			$result = $this->getAdapter()->fetchAll($select);
			return $result;
			
		}
	}
	
	
	public function getNumOfUserInteractionsWithinDays ($fanpageId, $days){
		$select = "SELECT count(distinct facebook_user_id) FROM fancrank.fancrank_activities 
					 where fanpage_id = $fanpageId &&
					timestampdiff(DAY, created_time, curdate()) < 7";
		$result = $this->getAdapter()->fetchAll($select);
		if(empty($result['count'])) {
			return 0;
		}
		
		return $result['count'];
	}
}

