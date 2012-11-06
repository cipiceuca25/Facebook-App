<?php

class Model_Fanpages extends Model_DbTable_Fanpages
{
	public function getFans($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);
		
		//cycle through all posts, comments, likes to retrieve list of fans
	}

	public function getActiveFanpagesByUserId($user_id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));

		return $this->getAdapter()->fetchAll($select);
	}

	public function getPostsStatByFanpageId($fanpage_id) {
		$postModel = new Model_Posts();
		$select = $postModel->select();
		$select->from($postModel, array('post_type','count(post_type) as count'))
				->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
				->group('post_type');
		
		$rows = $postModel->fetchAll($select);
		
		//Zend_Debug::dump($rows->toArray());
		return $rows->toArray();	
	}
	public function getTopObjectsWithinTime($fanpage_id, $limit){
		
		$select = "select distinct x.*, fan_name from (

					select p.post_id, facebook_user_id, post_message as message, 
					post_type, post_description, picture, link, link_name, created_time,
					post_likes_count, post_comments_count, (case like_interactions when like_interactions > 0 then like_interactions else 0 end) as like_interactions ,
					post_comments_count + (case like_interactions when like_interactions > 0 then like_interactions else 0 end) as total_interactions
					from posts p
					
					left join 
					(select post_id,  count(*) as like_interactions
					from likes
					where fanpage_id = $fanpage_id && facebook_user_id != fanpage_id
					group by post_id) as a
					
					on a.post_id = p.post_id
					where p.fanpage_id = $fanpage_id && facebook_user_id != fanpage_id";
		if($limit !=null){
				$select=$select."&&
					timestampdiff(HOUR, created_time, curdate()) < $limit";
		}		
		$select=$select."			union 
					
					select 
					
					comment_id as post_id, facebook_user_id, comment_message as message, 
					comment_type as post_type, null as post_description, null as picture,null as link, null as link_name, created_time,
					comment_likes_count as post_likes_count, 0 as post_comments_count, (case like_interactions when like_interactions > 0 then like_interactions else 0 end) as like_interactions ,
					(case like_interactions when like_interactions > 0 then like_interactions else 0 end) as total_interactions
					 
					from comments c
					left join 
					(select post_id, count(*) as like_interactions
					from likes
					where fanpage_id = $fanpage_id && facebook_user_id != fanpage_id
					group by post_id) as a
					
					on a.post_id = c.comment_id
					where c.fanpage_id = $fanpage_id && facebook_user_id != fanpage_id 
					";
		if($limit !=null){
				$select=$select."&&
					timestampdiff(HOUR, created_time, curdate()) < $limit";
		}	
		$select=$select."			) as x
					left join fans f
					on f.facebook_user_id = x.facebook_user_id
					order by total_interactions DESC";
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getNumOfParticipatedUserWithinDays($fanpage_id, $day){
		
		$select ="select count(distinct facebook_user_id)as count from
					(select facebook_user_id, 'post' as type from posts
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
					
					timestampdiff(DAY, created_time, curdate()) < $day
					
					union
					
					select facebook_user_id, 'comments' as type from comments
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
					
					timestampdiff(DAY, created_time, curdate()) < $day
					
					union
					
					select facebook_user_id, 'likes' as type from likes
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
					
					timestampdiff(DAY, updated_time, curdate()) < $day
					
					union 
					
					select facebook_user_id, 'follow' as type from subscribes
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
					
					timestampdiff(DAY, update_time, curdate()) < $day
					
					) as a";
		
		$row = $this->getAdapter()->fetchAll($select);
		
		if(empty($row['count'])) {
			return 0;
		}
		
		return $row['count'];
	}
	public function getNumOfInteractionsWithinDays($fanpage_id, $day){
	
		$select ="select count(*) as count from
		(select facebook_user_id, 'post' as type,post_id, created_time from posts
		where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
			
		timestampdiff(DAY, created_time, curdate()) < $day
			
		union
			
		select facebook_user_id, 'comments' as type, comment_id, created_time from comments
		where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
			
		timestampdiff(DAY, created_time, curdate()) < $day
			
		union
			
		select facebook_user_id, 'likes' as type,post_id, updated_time from likes
		where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
			
		timestampdiff(DAY, updated_time, curdate()) < $day
			
		union
			
		select facebook_user_id, 'follow' as type, facebook_user_id_subscribe_to, created_time from subscribes
		where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id &&
			
		timestampdiff(DAY, update_time, curdate()) < $day
			
		) as a";
	
		$row = $this->getAdapter()->fetchAll($select);
	
		if(empty($row['count'])) {
			return 0;
		}
		return $row['count'];
	}
	
	public function getTotalInteractionGraph($fanpage_id){
		
		$select = "select count(*) as interaction, date(created_time) as date from
					(select facebook_user_id, 'post' as type, post_id, created_time from posts
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id 
						
					union
						
					select facebook_user_id, 'comments' as type,comment_id, created_time from comments
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id 
						
					union
						
					select facebook_user_id, 'likes' as type,post_id, created_time from likes
					where fanpage_id =  $fanpage_id && facebook_user_id != fanpage_id 
						
				
					) as a
					group by date(created_time)
					order by created_time DESC";
		return $this->getAdapter()->fetchAll($select);
		
	}
	
	
	
	
	public function getTopPostsByNumberOfLikes($fanpage_id, $limit) {
		$postModel = new Model_Posts();
		$select = $postModel->select();
		$select->from($postModel)
			->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('facebook_user_id != ?', $fanpage_id))
			->order('post_likes_count DESC')
			->limit($limit);
	
		$rows = $postModel->fetchAll($select);
	
		//Zend_Debug::dump($rows->toArray());
		return $rows->toArray();
	}
	
	public function getTopPostsByNumberOfComments($fanpage_id, $limit) {
		$postModel = new Model_Posts();
		$select = $postModel->select();
		$select->from($postModel)
			->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('facebook_user_id != ?', $fanpage_id))
			->order('post_comments_count DESC')
			->limit($limit);
	
		$rows = $postModel->fetchAll($select);
	
		//Zend_Debug::dump($rows->toArray());
		return $rows->toArray();
	}
	
	public function getTopFanList($fanpage_id, $limit, $days) {
		$select = "select distinct f.facebook_user_id, f.fanpage_id, f.fan_name, f.fan_exp, f.fan_level, 
			(s.fan_post_status_count+s.fan_post_photo_count+s.fan_post_video_count+s.fan_post_link_count) as post_count, 
			(s.fan_comment_status_count+s.fan_comment_photo_count+s.fan_comment_video_count+s.fan_comment_link_count) as comment_count,
			(s.fan_like_status_count+s.fan_like_photo_count+s.fan_like_video_count+s.fan_like_link_count) as like_count,
			(s.fan_get_like_status_count+s.fan_get_like_photo_count+s.fan_get_like_video_count+s.fan_get_like_link_count+s.fan_get_like_comment_count) as got_like_count,
			(s.fan_get_comment_status_count+s.fan_get_comment_photo_count+s.fan_get_comment_video_count+s.fan_get_comment_link_count) as got_comment_count
			from fans f, fans_objects_stats s where s.facebook_user_id = f.facebook_user_id and s.fanpage_id = f.fanpage_id and f.fanpage_id = $fanpage_id and datediff(s.updated_time, now()) < $days group by s.facebook_user_id order by f.fan_exp desc";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getFanFavoriteList($fanpage_id, $limit, $days){
		
		if ($days == 'all'){
			$days= '1900-01-01 00:00:00';
		}else{

			$days =Zend_Date::now()->subDay($days);
			$days = $days->get('YYYY-MM-dd HH:mm:ss');
		}
		$now = Zend_Date::now();
		$now = $now->get('YYYY-MM-dd HH:mm:ss');
		
		$select="select fans.*,
						sum((case when type = 'n_got_comment_status' then count else 0 end))+
						sum((case when type = 'n_got_comment_photo' then count else 0 end)) + 
						sum((case when type = 'n_got_comment_video' then count else 0 end)) +
						sum((case when type = 'n_got_comment_link' then count else 0 end)) +
						sum((case when type = 'n_got_like_status' then count else 0 end))+
						sum((case when type = 'n_got_like_photo' then count else 0 end)) + 
						sum((case when type = 'n_got_like_video' then count else 0 end)) +
						sum((case when type = 'n_got_like_link' then count else 0 end)) +
						sum((case when type = 'n_got_like_comment' then count else 0 end))  as props,
						sum((case when type = 'n_got_comment_status' then count else 0 end))+
						sum((case when type = 'n_got_comment_photo' then count else 0 end)) + 
						sum((case when type = 'n_got_comment_video' then count else 0 end)) +
						sum((case when type = 'n_got_comment_link' then count else 0 end)) as got_comment_others,
						sum((case when type = 'n_got_like_status' then count else 0 end))+
						sum((case when type = 'n_got_like_photo' then count else 0 end)) + 
						sum((case when type = 'n_got_like_video' then count else 0 end)) +
						sum((case when type = 'n_got_like_link' then count else 0 end)) +
						sum((case when type = 'n_got_like_status_comment' then count else 0 end)) +
						sum((case when type = 'n_got_like_photo_comment' then count else 0 end)) + 
						sum((case when type = 'n_got_like_video_comment' then count else 0 end)) +
						sum((case when type = 'n_got_like_link_comment' then count else 0 end)) as got_like_others,
						sum((case when type = 'post_status' then count else 0 end))  +
						sum((case when type = 'post_photo' then count else 0 end))  +
						sum((case when type = 'post_video' then count else 0 end)) +
						sum((case when type = 'post_link' then count else 0 end)) as post_count,
						sum((case when type = 'comment_status' then count else 0 end)) +
						sum((case when type = 'comment_photo' then count else 0 end))  + 
						sum((case when type = 'comment_video' then count else 0 end)) +
						sum((case when type = 'comment_link' then count else 0 end))  as comment_count,
						sum((case when type = 'like_status' then count else 0 end))  +
						sum((case when type = 'like_photo' then count else 0 end))  +
						sum((case when type = 'like_video' then count else 0 end))  +
						sum((case when type = 'like_link' then count else 0 end))  +
						sum((case when type = 'like_status_comment' then count else 0 end))  +
						sum((case when type = 'like_photo_comment' then count else 0 end))  +
						sum((case when type = 'like_video_comment' then count else 0 end))  +
						sum((case when type = 'like_link_comment' then count else 0 end))  as like_count,
						sum((case when type = 'got_comment_status' then count else 0 end))+
						sum((case when type = 'got_comment_photo' then count else 0 end)) + 
						sum((case when type = 'got_comment_video' then count else 0 end)) +
						sum((case when type = 'got_comment_link' then count else 0 end)) as got_comment_count,
						sum((case when type = 'got_like_status' then count else 0 end))+
						sum((case when type = 'got_like_photo' then count else 0 end)) + 
						sum((case when type = 'got_like_video' then count else 0 end)) +
						sum((case when type = 'got_like_link' then count else 0 end)) +
				
						sum((case when type = 'got_like_status_comment' then count else 0 end))+
						sum((case when type = 'got_like_photo_comment' then count else 0 end)) + 
						sum((case when type = 'got_like_video_comment' then count else 0 end)) +
						sum((case when type = 'got_like_link_comment' then count else 0 end)) as got_like_count
						
				from (
				select facebook_user_id, concat('post_', post_type) as type, count(*) as count from posts where fanpage_id = $fanpage_id and created_time >= '$days' and created_time < '$now' group by post_type , facebook_user_id
				union
				select facebook_user_id,concat('comment_', comment_type) as type, count(*) as count from comments where fanpage_id = $fanpage_id and created_time >= '$days' and created_time < '$now' group by comment_type,  facebook_user_id
				union
				select facebook_user_id,concat('like_', post_type) as type, count(*) as count from likes where fanpage_id = $fanpage_id  and likes = 1 and updated_time >= '$days' and updated_time < '$now' group  by post_type , facebook_user_id
				union
				select p.facebook_user_id, concat('got_like_', l.post_type) as type, count(*) as count from likes l, posts p where l.post_id = p.post_id and
				l.fanpage_id = $fanpage_id and l.likes = 1 and l.updated_time >='$days' and l.updated_time < '$now' group by l.post_type  , facebook_user_id
				union
				select c.facebook_user_id, 'got_like_comment_' as type, count(*) as count from likes l, comments c where l.post_id = c.comment_id and
				l.fanpage_id = $fanpage_id and l.likes = 1 and c.created_time >= '$days' and c.created_time < '$now' group by l.post_type ,  facebook_user_id
				union
				select p.facebook_user_id, concat('got_comment_', p.post_type) as type, count(*) as count from comments c, posts p where c.comment_post_id = p.post_id and p.fanpage_id = $fanpage_id and c.created_time >= '$days' and c.created_time < '$now' group by p.post_type , facebook_user_id
				union
				select p.facebook_user_id, concat('n_got_like_', l.post_type) as type, count(*) as count from likes l , posts p where l.post_id = p.post_id and
				l.fanpage_id = $fanpage_id and l.likes = 1 and l.facebook_user_id != p.facebook_user_id and l.updated_time >= '$days' and l.updated_time < '$now' group by l.post_type  , facebook_user_id
				union
				select c.facebook_user_id, 'n_got_like_comment_' as type, count(*) as count from likes l, comments c where l.post_id = c.comment_id and
				l.fanpage_id = $fanpage_id and l.likes = 1 and l.facebook_user_id != c.facebook_user_id and c.created_time >= '$days' and c.created_time < '$now' group by l.post_type ,  facebook_user_id
				union
				select p.facebook_user_id, concat('n_got_comment_', p.post_type) as type, count(*) as count from comments c, posts p where c.comment_post_id = p.post_id and p.fanpage_id = $fanpage_id and c.facebook_user_id != p.facebook_user_id and c.created_time >= '$days' and c.created_time < '$now' group by p.post_type , facebook_user_id
				
				)as ex
				
				inner join fans on (fans.facebook_user_id = ex.facebook_user_id && fans.fanpage_id = $fanpage_id)
				group by facebook_user_id
				order by props DESC";
		
		
		/*$select = "select distinct f.facebook_user_id, f.fanpage_id, fans.fan_name, sum(favorite.num) AS count, fans.fan_level, 
			(f.fan_post_status_count+f.fan_post_photo_count+f.fan_post_video_count+f.fan_post_link_count) as post_count, 
			(f.fan_comment_status_count+f.fan_comment_photo_count+f.fan_comment_video_count+f.fan_comment_link_count) as comment_count,
			(f.fan_like_status_count+f.fan_like_photo_count+f.fan_like_video_count+f.fan_like_link_count) as like_count,
			(f.fan_get_like_status_count+f.fan_get_like_photo_count+f.fan_get_like_video_count+f.fan_get_like_link_count+f.fan_get_like_comment_count) as got_like_count,
			(f.fan_get_comment_status_count+f.fan_get_comment_photo_count+f.fan_get_comment_video_count+f.fan_get_comment_link_count) as got_comment_count 
			FROM
			(
				SELECT p.facebook_user_id, sum(p.post_comments_count) AS num FROM posts p WHERE p.fanpage_id = $fanpage_id AND p.facebook_user_id != p.fanpage_id GROUP BY p.facebook_user_id
				UNION ALL
				SELECT p.facebook_user_id, count(*) AS num FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id) WHERE p.facebook_user_id != l.fanpage_id AND l.fanpage_id = $fanpage_id GROUP BY p.post_id, p.facebook_user_id
				UNION ALL
				SELECT c.facebook_user_id, count(*) AS num FROM likes l LEFT JOIN comments c ON(l.post_id = c.comment_id) WHERE c.facebook_user_id != l.fanpage_id AND l.fanpage_id = $fanpage_id GROUP BY c.comment_id, c.facebook_user_id
			) AS favorite
			INNER JOIN fans ON (fans.facebook_user_id = favorite.facebook_user_id && fans.fanpage_id = $fanpage_id)
			inner join fans_objects_stats f ON (f.facebook_user_id = favorite.facebook_user_id && f.fanpage_id = $fanpage_id)
			where datediff(f.updated_time, now()) < $days
			GROUP BY favorite.facebook_user_id
			
			ORDER BY count DESC";*/
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
	
		
		return $this->getAdapter()->fetchAll($select);
		
	}
	
	public function getFansNumberBySex($fanpage_id) {
		$select = "select count(*) as sex from fans f inner join facebook_users u on f.facebook_user_id = u.facebook_user_id where f.fanpage_id = $fanpage_id and u.facebook_user_gender = 'male'
					union 
					select count(*) as sex from fans f inner join facebook_users u on f.facebook_user_id = u.facebook_user_id where f.fanpage_id = $fanpage_id and u.facebook_user_gender = 'female'";

		$rows = $this->getAdapter()->fetchAll($select);
		
		$result = array('male'=>0, 'female'=>0);
		if(! empty($rows[0]['sex'])) {
			$result['male'] = $rows[0]['sex'];
		}
		if(! empty($rows[1]['sex'])) {
			$result['female'] = $rows[1]['sex'];
		}
		return $result;
	}
	
	public function getNewFansNumberSince($fanpage_id, $since) {
		
		$select = $this->getAdapter()->select();
		$select->from(array('fans' => 'fans'), 'count(*) as count')
			->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('created_time >= ?'), $since);

		$row = $this->getAdapter()->fetchRow($select);
		
		if(empty($row['count'])) {
			return 0;
		}
		
		return $row['count'];
	}
	
	public function getFansNumber($fanpage_id) {
	
		$select = $this->getAdapter()->select();
		$select->from(array('fans' => 'fans'), 'count(*) as count')
			->where($this->quoteInto('fanpage_id = ?', $fanpage_id));
	
		$row = $this->getAdapter()->fetchRow($select);
	
		if(empty($row['count'])) {
			return 0;
		}
	
		return $row['count'];
	}
	
	public function getFanpageLike($fanpage_id){
	
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages') )
		->where($this->quoteInto('fanpage_id = ?', $fanpage_id));
	
		$row = $this->getAdapter()->fetchRow($select);
	
		if(empty($row['fanpage_likes'])) {
			return 0;
		}
	
		return $row['fanpage_likes'];
	}
	
	public function getFanpageLevel($fanpage_id){
		
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages') )
		->where($this->quoteInto('fanpage_id = ?', $fanpage_id));
		
		$row = $this->getAdapter()->fetchRow($select);
		
		if(empty($row['fanpage_level'])) {
			return 0;
		}
		
		return $row['fanpage_level'];
	}
	
	public function getFanpageName($fanpage_id){
	
		$select = $this->select();
		$select->from($this, array('fanpage_name'))
				->where('fanpage_id = ?', $fanpage_id);
	
		$row = $this->fetchRow($select);

		if(empty($row['fanpage_name'])) {
			return;
		}
		
		return $row['fanpage_name'];
	}
	
	public function getActiveFanpageByFanpageId($fanpage_id, $user_id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));
		$select->where('fanpages.active = TRUE');

		return $this->getAdapter()->fetchAll($select);
	}

	public function getFanpageByFanpageIdAndUserId($fanpage_id, $user_id) 
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));
		$select->where($this->getAdapter()->quoteInto('fanpages.fanpage_id = ?', $fanpage_id));

		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getInstallFanpagesIdList() {
		$select = $this->select();
		$select->from($this, array('fanpage_id'))->where('fanpages.installed = TRUE');
		$result = array();
		foreach ($this->fetchAll($select)->toArray() as $id) {
			$result[] = $id['fanpage_id'];
		}
		return $result;
	}
	
	public function getInstallFanpages() {
		$select = $this->select();
		$select->where('fanpages.installed = TRUE');
		return $this->fetchAll($select);
	}
	
	public function getActiveFanpages() {
		$select = $this->select();
		$select->where('fanpages.active = TRUE');
		return $this->fetchAll($select);
	}
	
	public function getTotalAwardPoints($fanpageId) {
		$select = $this->getDefaultAdapter()->select()
						->from('fans', array('sum(fan_point) as total'));
		$result = $this->getDefaultAdapter()->fetchAll($select);
		
		if(empty($result[0]['total'])) return 0;
		
		return $result[0]['total'];
	}
	
	public function getNewFanCrankUsers($fanpageId){
		$select = "SELECT count(*) as count FROM fancrank.fans where fanpage_id = $fanpageId && yearweek(first_login_time) = yearweek(curdate())";
		
		$row= $this->getAdapter()->fetchAll($select);
		if(empty($row['count'])) {
			return;
		}
		
		return $row['count']; 
	}

	public function getFanFirstInteractionDateTable($fanpageId ,$time, $graph) {
		
		if ($graph){
		
				$select =  "select * from (
				select count(*) as 'like' ,
				if ((f.created_time < e.created_time) || (e.created_time <=> null), f.created_time, e.created_time ) as date
				from fans f
				left join
					
				(select * from (
				select *
				from (
				select created_time, facebook_user_id from posts
				where fanpage_id = $fanpageId
			
				group by facebook_user_id
				order by created_time asc
				) a
					
				union
				select *
				from (
				select created_time, facebook_user_id from comments
				where fanpage_id = $fanpageId
			
				group by facebook_user_id
				order by created_time asc
				) b
				) as c
			
				group by facebook_user_id
				order by created_time asc
				) as e
				on e.facebook_user_id = f.facebook_user_id
					
				where fanpage_id = $fanpageId
				group by if ((f.created_time < e.created_time) || (e.created_time <=> null), date(f.created_time), date(e.created_time) )
				order by date asc
				) as x";

			switch ($time){
				
				
				case 'month':
					$select= $select. " where month(x.date) = month(curdate()) && year(x.date) = year(curdate())";
					break;
				case 'week':
					$select= $select." where yearweek(x.date) = yearweek(curdate())";
					break;
				case 'today':
					$select= $select." where date(x.date) = date(curdate())";
			 				
					break;
						
			}
			
			$result= $this->getAdapter()->fetchAll($select);
			
			if ($result){
				$result[0]['total'] = $result[0]['like'];
				
				for($i = 1; $i < count($result); $i++ ){
					
					$result[$i]['total'] = $result[$i]['like'] +  $result[$i-1]['total'] ;
					
				}
			}
		
			return $result;
			
		}else{
			
			
			
		}
		
		
	}
	
	public function getFanFirstInteractionNumber($fanpageId){
		$select ="select * from (
					select f.facebook_user_id,
									if ((f.created_time < e.created_time) || (e.created_time <=> null), f.created_time,e.created_time ) as date
									from fans f
									
									left join
									
										(select * from (
									
									
									select * 
											from (
												select created_time, facebook_user_id from posts 
												where fanpage_id = $fanpageId
												
												group by facebook_user_id
												order by created_time asc 
												) a
									
											union
									
											select * 
											from (
												select created_time, facebook_user_id from comments
												where fanpage_id = $fanpageId
												
												
												group by facebook_user_id
												order by created_time asc 
												) b
									) as c
									
									
									group by facebook_user_id
									order by created_time asc
										 ) as e
									on e.facebook_user_id = f.facebook_user_id
									
									where fanpage_id = $fanpageId
					) as x  order by date ASC";
		$result= $this->getAdapter()->fetchAll($select);
		
		$total = 0;
		$month = 0;
		$week = 0;
		$today = 0;
		$date = new Zend_Date();
		foreach ( $result as $r){
			$total++;
			$tempdate = new Zend_Date($r['date']);

			if ($tempdate->toString('Y M') == $date->toString('Y M')){
		
				$month++;
			}
			if ($tempdate->toString('Y w') == $date->toString('Y w')){
				$week++;
			}
			if ($tempdate->toString('F') == $date->toString('F')){
				$today++;
			}
			
		}
		
		$array['all'] = $total;
		$array['month'] = $month;
		$array['week'] = $week;
		$array['today'] =$today;
		
		
		return $array;
		
		
	}

	public function getActiveFanTable($fanpageId, $time, $graph){
		
		if($graph){
			
			$select = "select * from (
						Select count(*) as active, first_login_time from fans 
						where !(first_login_time <=> null) && fanpage_id = $fanpageId
						group by date(first_login_time)
						order by first_login_time ASC) as x";
		
			switch($time){
				case 'month':
					$select= $select. " where month(x.first_login_time) = month(curdate()) && year(x.first_login_time) = year(curdate())";
					break;
				case 'week':
					$select= $select." where yearweek(x.first_login_time) = yearweek(curdate())";
					break;
				case 'today':
					$select= $select." where date(x.first_login_time) = date(curdate())";
					break;
			}
			
			$result= $this->getAdapter()->fetchAll($select);
			if ($result){
				$result[0]['total'] = $result[0]['active'];
			
				for($i = 1; $i < count($result); $i++ ){
						
					$result[$i]['total'] = $result[$i]['active'] +  $result[$i-1]['total'] ;
						
				}
			}
			
			return $result;
		}
		
		
	}

	public function getActiveFanNumber($fanpageId){
		$select = "Select count(*) as active, first_login_time from fans 
				where !(first_login_time <=> null) && fanpage_id = $fanpageId
					group by date(first_login_time)
					order by first_login_time DESC";
		$result= $this->getAdapter()->fetchAll($select);
		$total = 0;
		$month = 0;
		$week = 0;
		$today = 0;
		$date = new Zend_Date();
		foreach ( $result as $r){
			$total++;
			$tempdate = new Zend_Date($r['first_login_time']);
		
			if ($tempdate->toString('Y M') == $date->toString('Y M')){
				$month++;
			}
			if ($tempdate->toString('Y w') == $date->toString('Y w')){
				$week++;
			}
			if ($tempdate->toString('F') == $date->toString('F')){
				$today++;
			}
				
		}
		
		$array['all'] = $total;
		$array['month'] = $month;
		$array['week'] = $week;
		$array['today'] =$today;
		
		
		return $array;
		
	
	}

	public function getFacebookInteractions($fanpageId, $time, $graph){
		$select ="select * from (select count(*) as 'all', sum(if (x.type = 'post', 1, 0)) as posts ,sum(if (x.type = 'comment', 1, 0))as comments,sum(if (x.type = 'like', 1, 0)) as likes, created_time from
					(
					select post_id, facebook_user_id, created_time, 'post' as type from  posts where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					union all
					select comment_id, facebook_user_id, created_time, 'comment' as type from  comments where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					union all
					select post_id, facebook_user_id, updated_time ,'like' as type from  likes where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					
					) as x 
					group by date(created_time)
					order by created_time ASC) as y";
		
		switch($time){
			case 'month':
				$select= $select. " where month(y.created_time) = month(curdate()) && year(y.created_time) = year(curdate())";
				break;
			case 'week':
				$select= $select." where yearweek(y.created_time) = yearweek(curdate())";
				break;
			case 'today':
				$select= "select count(*) as 'all', sum(if (x.type = 'post', 1, 0)) as posts ,sum(if (x.type = 'comment', 1, 0))as comments,sum(if (x.type = 'like', 1, 0)) as likes, created_time from
					(
					select post_id, facebook_user_id, created_time, 'post' as type from  posts where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					union all
					select comment_id, facebook_user_id, created_time, 'comment' as type from  comments where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					union all
					select post_id, facebook_user_id, updated_time ,'like' as type from  likes where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					
					) as x 
					where date(x.created_time) = date(curdate())
					group by hour(created_time)
					order by created_time ASC";
				break;
		}
		$result= $this->getAdapter()->fetchAll($select);
		if ($result){
			$result[0]['total_all'] = $result[0]['all'];
			$result[0]['total_posts'] = $result[0]['posts'];
			$result[0]['total_comments'] = $result[0]['comments'];
			$result[0]['total_likes'] = $result[0]['likes'];
				
			for($i = 1; $i < count($result); $i++ ){
		
				$result[$i]['total_all'] = $result[$i]['all'] +  $result[$i-1]['total_all'] ;
				$result[$i]['total_posts'] = $result[$i]['posts'] +  $result[$i-1]['total_posts'] ;
				$result[$i]['total_comments'] = $result[$i]['comments'] +  $result[$i-1]['total_comments'] ;
				$result[$i]['total_likes'] = $result[$i]['likes'] +  $result[$i-1]['total_likes'] ;
		
			}
		}
		//Zend_Debug::$result;
		return $result;
	}

	public function getFacebookInteractionsNumber($fanpageId){
		
		$select = "select 'posts' as type, count(*) as 'all', 
					sum(case when date(created_time) = date(curdate()) then 1 else 0 end ) as today,
					sum(case when yearweek(created_time) = yearweek(curdate()) then 1 else 0 end ) as week,
					sum(case when (year(created_time) = year(curdate()) && month(created_time) = month(curdate())) then 1 else 0 end ) as month
					from  posts where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					
					union 
					
					select 'comments' as type, count(*) as 'all',
					sum(case when date(created_time) = date(curdate()) then 1 else 0 end ) as today,
					sum(case when yearweek(created_time) = yearweek(curdate()) then 1 else 0 end ) as week,
					sum(case when (year(created_time) = year(curdate()) && month(created_time) = month(curdate())) then 1 else 0 end ) as month
					 from comments where fanpage_id = $fanpageId && facebook_user_id != fanpage_id
					 
					union 
					
					select 'likes' as type, count(*) as 'all',
					sum(case when date(updated_time) = date(curdate()) then 1 else 0 end ) as today,
					sum(case when yearweek(updated_time) = yearweek(curdate()) then 1 else 0 end ) as week,
					sum(case when (year(updated_time) = year(curdate()) && month(updated_time) = month(curdate())) then 1 else 0 end ) as month
					from likes where fanpage_id = $fanpageId && facebook_user_id != fanpage_id ";
		
		$result= $this->getAdapter()->fetchAll($select);
		
		$result[3]['all'] = $result[0]['all'] + $result[1]['all'] + $result[2]['all'];
		$result[3]['today'] = $result[0]['today'] + $result[1]['today'] + $result[2]['today'];
		$result[3]['week'] = $result[0]['week'] + $result[1]['week'] + $result[2]['week'];
		$result[3]['month'] = $result[0]['month'] + $result[1]['month'] + $result[2]['month'];
		
		return $result;
	}
	
	public function getActiveFansSince($fanpageId, $since=0, $limit=99999) {
		$date = new Zend_Date($since);
		$since = $date->toString('yyyy-MM-dd HH:mm:ss');
	
		$select= "
			select distinct f.*
			from (
				select facebook_user_id, created_time from
				(
					(Select c.fanpage_id, c.facebook_user_id, p.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join posts p on (c.comment_post_id = p.post_id)
					where c.fanpage_id =  $fanpageId and p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(Select c.fanpage_id, c.facebook_user_id, p.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join photos p on (c.comment_post_id = p.photo_id)
					where c.fanpage_id =  $fanpageId and p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(Select c.fanpage_id, c.facebook_user_id, a.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join albums a on (c.comment_post_id = a.album_id)
					where c.fanpage_id =  $fanpageId and a.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(SELECT p.fanpage_id, p.facebook_user_id, p.fanpage_id as target_user_id, p.created_time
					FROM posts p
					where p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select l.fanpage_id, l.facebook_user_id, p.facebook_user_id as target_id, l.updated_time as created_time
					from
					likes l left join posts p on (l.post_id = p.post_id)
					where p.fanpage_id = $fanpageId and l.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select l.fanpage_id, l.facebook_user_id, c.facebook_user_id as target_id, l.updated_time as created_time
					from
					likes l left join comments c on (l.post_id = c.comment_id)
					where c.fanpage_id = $fanpageId and l.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select fanpage_id, facebook_user_id, target_user_id, created_time
					from fancrank_activities
					where fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
				) as t1 
				group by facebook_user_id
				
				union
				
				select target_user_id, created_time from
				(
					(Select c.fanpage_id, c.facebook_user_id, p.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join posts p on (c.comment_post_id = p.post_id)
					where c.fanpage_id =  $fanpageId and p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(Select c.fanpage_id, c.facebook_user_id, p.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join photos p on (c.comment_post_id = p.photo_id)
					where c.fanpage_id =  $fanpageId and p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(Select c.fanpage_id, c.facebook_user_id, a.facebook_user_id as target_user_id, c.created_time
					from
					comments c left join albums a on (c.comment_post_id = a.album_id)
					where c.fanpage_id =  $fanpageId and a.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
					union
					
					(SELECT p.fanpage_id, p.facebook_user_id, p.fanpage_id as target_user_id, p.created_time
					FROM posts p
					where p.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select l.fanpage_id, l.facebook_user_id, p.facebook_user_id as target_id, l.updated_time as created_time
					from
					likes l left join posts p on (l.post_id = p.post_id)
					where p.fanpage_id = $fanpageId and l.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select l.fanpage_id, l.facebook_user_id, c.facebook_user_id as target_id, l.updated_time as created_time
					from
					likes l left join comments c on (l.post_id = c.comment_id)
					where c.fanpage_id = $fanpageId and l.fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
						
					union
						
					(select fanpage_id, facebook_user_id, target_user_id, created_time
					from fancrank_activities
					where fanpage_id = $fanpageId
					order by created_time DESC
					limit $limit)
					
				) as t1 
				group by target_user_id 
			) as t2 left join fans f on (t2.facebook_user_id = f.facebook_user_id and f.fanpage_id = $fanpageId and f.facebook_user_id != $fanpageId)
			where t2.created_time > '" .$since ."'	
			order by t2.created_time DESC
			limit $limit			
		";
		
		$result = $this->getAdapter()->fetchAll($select);
		//return $result;
		return $result;
	}
	
	public function getUniqueUserInteractionsGraph($fanpage, $time, $graph) {
		$select = "select * from 
					(
					select * from (select count(distinct facebook_user_id) as 'all', 
					 created_time from
					(
					select post_id, facebook_user_id, created_time from  posts where fanpage_id = $fanpage && facebook_user_id != fanpage_id
					union all
					select comment_id, facebook_user_id, created_time from  comments where fanpage_id = $fanpage && facebook_user_id != fanpage_id
					union all
					select post_id, facebook_user_id, updated_time from  likes where fanpage_id =$fanpage && facebook_user_id != fanpage_id
					
					) as x 
					group by date(created_time)
					order by created_time ASC) as y) as z";
		switch($time){
			case 'month':
				$select= $select. " where month(z.created_time) = month(curdate()) && year(z.created_time) = year(curdate())";
				break;
			case 'week':
				$select= $select." where yearweek(z.created_time) = yearweek(curdate())";
				break;
			case 'today':
				$select= $select." where date(z.created_time) = date(curdate())";
				break;
		}
	}	
}	

