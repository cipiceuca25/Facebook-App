<?php

class Model_Fanpages extends Model_DbTable_Fanpages
{
	public function getFeed($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);
		
		return $this->facebookRequest(null, 'feed', $fanpage->access_token);	
	}
	
	public function getAlbums($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);
		
		return $this->facebookRequest(null, 'albums', $fanpage->access_token);
	}
	
	public function getInsights($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);

		return $this->facebookRequest(null, 'insights', $fanpage->access_token);
	}
	
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
		$select = "select distinct f.facebook_user_id, f.fanpage_id, f.fan_name, f.fan_points, f.fan_level, 
			(s.fan_post_status_count+s.fan_post_photo_count+s.fan_post_video_count+s.fan_post_link_count) as post_count, 
			(s.fan_comment_status_count+s.fan_comment_photo_count+s.fan_comment_video_count+s.fan_comment_link_count) as comment_count,
			(s.fan_like_status_count+s.fan_like_photo_count+s.fan_like_video_count+s.fan_like_link_count) as like_count,
			(s.fan_get_like_status_count+s.fan_get_like_photo_count+s.fan_get_like_video_count+s.fan_get_like_link_count+s.fan_get_like_comment_count) as got_like_count,
			(s.fan_get_comment_status_count+s.fan_get_comment_photo_count+s.fan_get_comment_video_count+s.fan_get_comment_link_count) as got_comment_count
			from fans f, fans_objects_stats s where s.facebook_user_id = f.facebook_user_id and s.fanpage_id = f.fanpage_id and f.fanpage_id = $fanpage_id and datediff(s.updated_time, now()) < $days group by s.facebook_user_id order by fan_points desc";
		
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
}

