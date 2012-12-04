<?php

class Model_FansObjectsStats extends Model_DbTable_FansObjectsStats
{
	public function updatedFan($fanpage_id, $facebook_user_id) {
		$date = new Zend_Date();
		$fanStat = $this->findFan($fanpage_id, $facebook_user_id);

		$data = $this->getFanStatById($fanpage_id, $facebook_user_id);
		
		if($fanStat) {
			foreach ($data as $key => $value) {
				$fanStat->{$key} = $value;
			}
			$fanStat->save();
			return $fanStat->toArray();
		}else {
			if($this->insert($data)) {
				return $data;
			}
		}
	}
	
	public function updatedFanWithPoint($fanpage_id, $facebook_user_id, $xp=0, $points=0) {
		$date = new Zend_Date();
		$fanStat = $this->findFan($fanpage_id, $facebook_user_id);
	
		$data = $this->getFanStatById($fanpage_id, $facebook_user_id);
		$data['fan_exp'] = $xp;
		$data['fan_point'] = $points;
	
		if($fanStat) {
			foreach ($data as $key => $value) {
				$fanStat->{$key} = $value;
			}
			$fanStat->save();
			return $fanStat->toArray();
		}else {
			if($this->insert($data)) {
				return $data;
			}
		}
	}
	
	public function getLatestFanStat($fanpage_id, $facebook_user_id) {
		$query = $this->select()
		->from($this)
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id = ?', $fanpage_id)
		->order('updated_time DESC')
		->limit(1);
		//Zend_Debug::dump($query);
		return $this->fetchAll($query)->current();
		
	//	$select = "select * from fans_objects_stats where fanpage_id = $fanpage_id && facebook_user_id = $facebook_user_id order by updated_time DESC limit 1";
	//	$result = $this->getDefaultAdapter()->fetchAll($select);
	//	return $result;
	}
	
	public function getFanStatById($fanpage_id, $facebook_user_id) {
		$select = "select concat('post_', post_type) as type, count(*) as count from posts where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id group by post_type
					union
					select concat('comment_', comment_type) as type, count(*) as count from comments where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id group by comment_type
					union
					select concat('like_', post_type) as type, count(*) as count from likes where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id and likes = 1 group by post_type
					union
					select concat('got_like_', l.post_type) as type, count(*) as count from likes l left join posts p on(l.post_id = p.post_id) where
					l.fanpage_id = $fanpage_id and p.facebook_user_id = $facebook_user_id and l.likes = 1 group by l.post_type 
					union
					select 'got_like_comment' as type, count(*) as count from likes l left join comments c on(l.post_id = c.comment_id) where
					l.fanpage_id = $fanpage_id and c.facebook_user_id = $facebook_user_id and l.likes = 1
					union
					select concat('got_comment_', p.post_type) as type, sum(p.post_comments_count) as count from posts p where p.fanpage_id = $fanpage_id and p.facebook_user_id = $facebook_user_id group by p.post_type
				";
		$result = $this->getDefaultAdapter()->fetchAll($select);
		
		$date = new Zend_Date();
		
		$data = array(	
				'fanpage_id' => $fanpage_id,
				'facebook_user_id' => $facebook_user_id,
				'updated_time'=>$date->toString ( 'yyyy-MM-dd HH:mm:ss' ),
				'fan_post_status_count' => 0,
				'fan_post_photo_count' => 0,
				'fan_post_video_count' => 0,
				'fan_post_link_count' => 0,
				'fan_comment_status_count' => 0,
				'fan_comment_photo_count' => 0,
				'fan_comment_video_count' => 0,
				'fan_comment_link_count' => 0,
				'fan_like_status_count' => 0,
				'fan_like_photo_count' => 0,
				'fan_like_video_count' => 0,
				'fan_like_link_count' => 0,
				'fan_like_comment_count' => 0,
				'fan_get_like_status_count' => 0,
				'fan_get_like_photo_count' => 0,
				'fan_get_like_video_count' => 0,
				'fan_get_like_link_count' => 0,
				'fan_get_like_comment_count' => 0,
				'fan_get_comment_status_count' => 0,
				'fan_get_comment_photo_count' => 0,
				'fan_get_comment_video_count' => 0,
				'fan_get_comment_link_count' => 0
		);
		
		foreach($result as $key => $v) {
			//$data[$v['type']] = $v['count'];
			switch($v['type']) {
				case 'post_status' : $data['fan_post_status_count'] = $v['count']; break;
				case 'post_photo' : $data['fan_post_photo_count'] = $v['count']; break;
				case 'post_video' : $data['fan_post_video_count'] = $v['count']; break;
				case 'post_link' : $data['fan_post_link_count'] = $v['count']; break;
				
				case 'comment_status' : $data['fan_comment_status_count'] = $v['count']; break;
				case 'comment_photo' : $data['fan_comment_photo_count'] = $v['count']; break;
				case 'comment_video' : $data['fan_comment_video_count'] = $v['count']; break;
				case 'comment_link' : $data['fan_comment_link_count'] = $v['count']; break;

				case 'like_status' : $data['fan_like_status_count'] = $v['count']; break;
				case 'like_photo' : $data['fan_like_photo_count'] = $v['count']; break;
				case 'like_video' : $data['fan_like_video_count'] = $v['count']; break;
				case 'like_link' : $data['fan_like_link_count'] = $v['count']; break;
				case 'like_status_comment' : $data['fan_like_comment_count'] += $v['count']; break;
				case 'like_photo_comment' : $data['fan_like_comment_count'] += $v['count']; break;
				case 'like_video_comment' : $data['fan_like_comment_count'] += $v['count']; break;
				case 'like_link_comment' : $data['fan_like_comment_count'] += $v['count']; break;
				
				case 'got_like_status' : $data['fan_get_like_status_count'] = $v['count']; break;
				case 'got_like_photo' : $data['fan_get_like_photo_count'] = $v['count']; break;
				case 'got_like_video' : $data['fan_get_like_video_count'] = $v['count']; break;
				case 'got_like_link' : $data['fan_get_like_link_count'] = $v['count']; break;
				case 'got_like_comment' : $data['fan_get_like_comment_count'] = $v['count']; break;
				
				case 'got_comment_status' : $data['fan_get_comment_status_count'] = $v['count']; break;
				case 'got_comment_photo' : $data['fan_get_comment_photo_count'] = $v['count']; break;
				case 'got_comment_video' : $data['fan_get_comment_video_count'] = $v['count']; break;
				case 'got_comment_link' : $data['fan_get_comment_link_count'] = $v['count']; break;
				default : break;	
			}
		}
		
		return $data;
	}
	
	public function getFanPostCountByType($fanpage_id, $facebook_user_id, $type) {
		if($type === 'all') {
			$postModel = new Model_Posts();
			$select = $postModel->select();
			$select->from($postModel, array('count(*) as count'));
			$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id));

			$rows = $this->fetchAll($select);
			
			if(empty($rows[0]->count)) {
				return 0;
			}
			
			return ($rows[0]->count);
		}
		
		$postModel = new Model_Posts();
		$select = $postModel->select();
		$select->from($postModel, array('count(*) as count'));
		$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
		->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id))
		->where($this->quoteInto('post_type = ?', $type));
		$rows = $this->fetchAll($select);
		
		if(empty($rows[0]->count)) {
			return 0;
		}
		return ($rows[0]->count);
	}
	
	public function getFanPostStatusCount($fanpage_id, $facebook_user_id) {
		return $this->getFanPostCountByType($fanpage_id, $facebook_user_id, 'status');
	}
	
	public function getFanPostVideoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanPostCountByType($fanpage_id, $facebook_user_id, 'video');
	}
	
	public function getFanPostPhotoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanPostCountByType($fanpage_id, $facebook_user_id, 'photo');
	}
	
	public function getFanPostLinkCount($fanpage_id, $facebook_user_id) {
		return $this->getFanPostCountByType($fanpage_id, $facebook_user_id, 'link');
	}
	
	public function getFanPostCount($fanpage_id, $facebook_user_id) {
		return $this->getFanPostCountByType($fanpage_id, $facebook_user_id, 'all');
	}
	
	public function getFanCommentCountByType($fanpage_id, $facebook_user_id, $type) {
		$select = null;
		
		if($type === 'all') {
			$select = "SELECT count(*) AS count FROM comments c left join posts p on (p.post_id = c.comment_post_id)
			WHERE p.fanpage_id = $fanpage_id AND c.facebook_user_id = $facebook_user_id AND p.facebook_user_id != c.facebook_user_id GROUP BY p.facebook_user_id";
		}else {
			$select = "SELECT count(*) AS count FROM comments c left join posts p on (p.post_id = c.comment_post_id)
			WHERE p.fanpage_id = $fanpage_id AND c.facebook_user_id = $facebook_user_id AND p.facebook_user_id != c.facebook_user_id AND p.post_type = '". $type ."' GROUP BY p.facebook_user_id";
		}
		
		$rows = $this->getAdapter()->fetchAll($select);
		
		if(empty($rows[0]['count'])) {
			return 0;
		}
		return ($rows[0]['count']);
	}
	
	public function getFanCommentStatusCount($fanpage_id, $facebook_user_id) {
		return $this->getFanCommentCountByType($fanpage_id, $facebook_user_id, 'status');
	}
	
	public function getFanCommentPhotoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanCommentCountByType($fanpage_id, $facebook_user_id, 'photo');
	}
	
	public function getFanCommentVideoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanCommentCountByType($fanpage_id, $facebook_user_id, 'video');
	}
	
	public function getFanCommentLinkCount($fanpage_id, $facebook_user_id) {
		return $this->getFanCommentCountByType($fanpage_id, $facebook_user_id, 'link');
	}
	
	public function getFanCommentCount($fanpage_id, $facebook_user_id) {
		return $this->getFanCommentCountByType($fanpage_id, $facebook_user_id, 'all');
	}
	
	public function getFanLikeCountByType($fanpage_id, $facebook_user_id, $type) {
		
		$select = null;
		
		switch($type) {
			case 'all':
				$select = "SELECT count(*) AS count FROM likes l
				WHERE l.facebook_user_id = $facebook_user_id AND l.fanpage_id = $fanpage_id AND l.likes = 1";
				break;			
			case 'status':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE l.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'status' AND l.likes = 1";
				break;
			case 'link':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE l.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'link' AND l.likes = 1";
				break;
			case 'photo':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE l.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'photo' AND l.likes = 1";
				break;
			case 'video':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE l.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'video' AND l.likes = 1";
				break;
			case 'comment':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN comments c ON(l.post_id = c.comment_id) WHERE l.facebook_user_id =
				$facebook_user_id AND l.facebook_user_id != c.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type LIKE '%_comment' AND l.likes = 1";
				break;
				default: break;
		}
		
		if(empty($select)) {
			return 0;
		}
		
		$rows = $this->getAdapter()->fetchAll($select);
		
		if(empty($rows[0]['count'])) {
			return 0;
		}
		return ($rows[0]['count']);
	}
	
	
	public function getFanLikeStatusCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'status');
	}
	
	public function getFanLikeCommentCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'comment');
	}
	
	public function getFanLikePhotoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'photo');
	}
	
	public function getFanLikeVideoCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'video');
	}
	
	public function getFanLikeLinkCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'link');
	}
	
	public function getFanLikeCount($fanpage_id, $facebook_user_id) {
		return $this->getFanLikeCountByType($fanpage_id, $facebook_user_id, 'all');
	}
	
	public function getFanGotLikeFrom($fanpage_id, $facebook_user_id, $type) {
		$select = null;
		
		switch($type) {
			case 'status':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'status' AND l.likes = 1";
				break;
			case 'link':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'link' AND l.likes = 1";
				break;
			case 'photo':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'photo' AND l.likes = 1";
				break;
			case 'video':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != l.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'video' AND l.likes = 1";
				break;
			case 'comment':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN comments c ON(l.post_id = c.comment_id) WHERE c.facebook_user_id = 
							$facebook_user_id AND l.facebook_user_id != c.facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type LIKE '%_comment' AND l.likes = 1"; 
				break;
			default: break;
		}
		
		if(empty($select)) {
			return 0;
		}
		
		$rows = $this->getAdapter()->fetchAll($select);
		
		if(empty($rows[0]['count'])) {
			return 0;
		}
		return ($rows[0]['count']);
	}
	
	public function getFanGotLikeFromStatus($fanpage_id, $facebook_user_id) {
		return $this->getFanGotLikeFrom($fanpage_id, $facebook_user_id, 'status');
	}
	
	public function getFanGotLikeFromPhoto($fanpage_id, $facebook_user_id) {
		return $this->getFanGotLikeFrom($fanpage_id, $facebook_user_id, 'photo');
	}
	
	public function getFanGotLikeFromVideo($fanpage_id, $facebook_user_id) {
		return $this->getFanGotLikeFrom($fanpage_id, $facebook_user_id, 'video');
	}
	
	public function getFanGotLikeFromComment($fanpage_id, $facebook_user_id) {
		return $this->getFanGotLikeFrom($fanpage_id, $facebook_user_id, 'comment');
	}
	
	public function getFanGotLikeFromLink($fanpage_id, $facebook_user_id) {
		return $this->getFanGotLikeFrom($fanpage_id, $facebook_user_id, 'link');
	}
	
	public function getFanGotCommentFrom($fanpage_id, $facebook_user_id, $type) {
		$select = null;
		
		if($type === 'all') {
			$select = "SELECT count(*) AS count FROM comments c left join posts p on (p.post_id = c.comment_post_id)
				WHERE p.fanpage_id = $fanpage_id AND p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != c.facebook_user_id GROUP BY p.facebook_user_id";
		}else {
			$select = "SELECT count(*) AS count FROM comments c left join posts p on (p.post_id = c.comment_post_id)
				WHERE p.fanpage_id = $fanpage_id AND p.facebook_user_id = $facebook_user_id AND p.facebook_user_id != c.facebook_user_id AND p.post_type = '". $type ."' GROUP BY p.facebook_user_id";
		}
		
		if(empty($select)) {
			return 0;
		}
		
		$rows = $this->getAdapter()->fetchAll($select);
		
		if(empty($rows[0]['count'])) {
			return 0;
		}
		return ($rows[0]['count']);
	}
	
	public function getFanGotCommentCountFromStatus($fanpage_id, $facebook_user_id) {
		return $this->getFanGotCommentFrom($fanpage_id, $facebook_user_id, 'status');
	}

	public function getFanGotCommentCountFromVideo($fanpage_id, $facebook_user_id) {
		return $this->getFanGotCommentFrom($fanpage_id, $facebook_user_id, 'video');
	}
	
	public function getFanGotCommentCountFromLink($fanpage_id, $facebook_user_id) {
		return $this->getFanGotCommentFrom($fanpage_id, $facebook_user_id, 'link');
	}
	
	public function getFanGotCommentCountFromPhoto($fanpage_id, $facebook_user_id) {
		return $this->getFanGotCommentFrom($fanpage_id, $facebook_user_id, 'photo');
	}
	
	public function getTopFanListByFanpageId($fanpage_id, $limit=100, $fields=null) {
    	$select = "select ? from fans_objects_stats s,
			(
			select max(id) as id from fans_objects_stats where fanpage_id = $fanpage_id group by facebook_user_id
			) s1, fans f
			where s.id = s1.id and s.facebook_user_id = f.facebook_user_id and s.fanpage_id = f.fanpage_id
			order by f.fan_exp desc";
		$extra = '';
		
		if(empty($fields)) {
			$extra = "f.facebook_user_id, f.fanpage_id, f.fan_name, f.fan_exp, f.fan_level,
				(s.fan_post_status_count+s.fan_post_photo_count+s.fan_post_video_count+s.fan_post_link_count) as post_count, 
				(s.fan_comment_status_count+s.fan_comment_photo_count+s.fan_comment_video_count+s.fan_comment_link_count) as comment_count,
				(s.fan_like_status_count+s.fan_like_photo_count+s.fan_like_video_count+s.fan_like_link_count) as like_count,
				(s.fan_get_like_status_count+s.fan_get_like_photo_count+s.fan_get_like_video_count+s.fan_get_like_link_count+s.fan_get_like_comment_count) as got_like_count,
				(s.fan_get_comment_status_count+s.fan_get_comment_photo_count+s.fan_get_comment_video_count+s.fan_get_comment_link_count) as got_comment_count
				";
		}else {
			$extra = '';
			foreach ($fields as $field) {
				$extra .= 's1.' .$field .',';
			}
			$extra = rtrim($extra, ',');
		}
		
		$select = str_replace('?', $extra, $select);
		
		if($limit !== false) {
			$select = $select . " LIMIT $limit";			
		}
		
		return $this->getDefaultAdapter()->fetchAll($select);
	}
	
	//////////////////////////////////////////////////////////////
	protected function addFan($fanpage_id, $facebook_user_id){
		$dateObject = new Zend_Date();
		$data = array(	'fanpage_id' => $fanpage_id, 
						'facebook_user_id' => $facebook_user_id, 
						'updated_time'=>$dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' ),
					  	'fan_post_status_count' => 0, 
						'fan_post_photo_count' => 0,
						'fan_post_video_count' => 0,
						'fan_post_link_count' => 0,
						'fan_comment_status_count' =>0, 
						'fan_comment_photo_count' => 0,
						'fan_comment_video_count' => 0,
						'fan_comment_link_count' => 0,
						'fan_like_status_count' => 0,
						'fan_like_photo_count' => 0,
						'fan_like_video_count' => 0,
						'fan_like_link_count' => 0,
						'fan_like_comment_count' => 0,
						'fan_get_like_status_count' => 0,
						'fan_get_like_photo_count' => 0,
						'fan_get_like_video_count' => 0,
						'fan_get_like_link_count' => 0,
						'fan_get_like_comment_count' => 0,
						'fan_get_comment_status_count' => 0,
						'fan_get_comment_photo_count' => 0,
						'fan_get_comment_video_count' => 0,
						'fan_get_comment_link_count' => 0,
						);
		//echo 'making new entry';
		return $this->insert ( $data );
	}
	
	public function findFan($fanpage_id, $facebook_user_id){
		$dateObject = new Zend_Date();
		
		$query = $this->select()
		->from($this)
		->where('facebook_user_id = ?', $facebook_user_id)
		->where('fanpage_id = ?', $fanpage_id)
		->where('DATEDIFF(updated_time,CURDATE())  >= ?', 0 );
		//Zend_Debug::dump($query);
		return $this->fetchAll($query)->current();
	}
	
	public function findFanRecord($fanpage_id, $facebook_user_id, $limit=false){
		
		$select = "	SELECT 
				f.fan_post_status_count + 
				f.fan_post_photo_count + 
				f.fan_post_video_count + 
				f.fan_post_link_count	as total_posts, 
				
				f.fan_comment_status_count + 
				f.fan_comment_photo_count + 
				f.fan_comment_video_count + 
				f.fan_comment_link_count	as total_comments,
				
				f.fan_like_status_count + 
				f.fan_like_photo_count + 
				f.fan_like_video_count + 
				f.fan_like_link_count + 
				f.fan_like_comment_count	as total_likes,
				
				f.fan_get_like_status_count + 
				f.fan_get_like_photo_count + 
				f.fan_get_like_video_count + 
				f.fan_get_like_link_count + 
				f.fan_get_like_comment_count as total_get_likes	,
				
				f.fan_get_comment_status_count + 
				f.fan_get_comment_photo_count + 
				f.fan_get_comment_video_count + 
				f.fan_get_comment_link_count as total_get_comments,
				
				f.fan_comment_link_count 						as link_comments,
				f.fan_comment_video_count						as video_comments,
				f.fan_comment_photo_count 						as photo_comments,
				f.fan_comment_status_count						as status_comments,
				
				f.fan_like_link_count 							as link_likes,
				f.fan_like_video_count 							as video_likes,
				f.fan_like_photo_count 							as photo_likes,
				f.fan_like_status_count 						as status_likes,
				f.fan_like_comment_count						as comment_likes,
				
				f.fan_get_like_video_count						as get_video_likes,
				f.fan_get_like_photo_count 						as get_photo_likes,
				f.fan_get_like_link_count 						as get_link_likes,
				f.fan_get_like_status_count 					as get_status_likes,
				f.fan_get_like_comment_count					as get_comment_likes,
				
				f.fan_get_comment_link_count						as get_link_comments,
				f.fan_get_comment_video_count 						as get_video_comments,
				f.fan_get_comment_status_count 					as get_status_comments,
				f.fan_get_comment_photo_count					as get_photo_comments,
		
				f.fan_post_status_count							as post_status,
				f.fan_post_photo_count 							as post_photo,
				f.fan_post_video_count 							as post_video,
				f.fan_post_link_count 								as post_link
				
				FROM fans_objects_stats f
				WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'
				
				order by f.updated_time DESC
				limit 1";
		//Zend_Debug::dump($query);
		if($limit !== false) {
			$select = "select a.total_posts - b.total_posts as total_posts, 
		a.total_comments - b.total_comments as total_comments, 
		a.total_likes - b.total_likes as total_likes,
		a.total_get_likes - b.total_get_likes as total_get_likes,
		a.total_get_comments - b.total_get_comments as total_get_comments,
		a.link_comments - b.link_comments as link_comments,
		a.video_comments - b.video_comments as video_comments, 
		a.photo_comments - b.photo_comments as photo_comments,
		a.status_comments - b.status_comments as status_comments, 
		a.link_likes - b.link_likes as link_likes,
		a.video_likes - b.video_likes as video_likes, 
		a.photo_likes - b.photo_likes as photo_likes, 
		a.status_likes - b.status_likes as status_likes,
		a.comment_likes - b.comment_likes as comment_likes,
		a.get_video_likes - b.get_video_likes as get_video_likes, 
		a.get_photo_likes - b.get_photo_likes as get_photo_likes, 
		a.get_link_likes - b.get_link_likes as get_link_likes, 
		a.get_status_likes - b.get_status_likes as get_status_likes,
		a.get_comment_likes - b.get_comment_likes as get_comment_likes, 

		a.get_video_comments - b.get_video_comments as get_video_comments, 
		a.get_status_comments - b.get_status_comments as get_status_comments, 
		a.get_photo_comments - b.get_photo_comments as get_photo_comments, 
		a.post_status - b.post_status as post_status,
		a.post_photo - b.post_photo as post_photo, 
		a.post_video - b.post_video as post_video,
		a.post_link - b.post_link as post_link

 from 
(SELECT f.fan_post_status_count + 
				f.fan_post_photo_count + 
				f.fan_post_video_count + 
				f.fan_post_link_count	as total_posts, 
				
				f.fan_comment_status_count + 
				f.fan_comment_photo_count + 
				f.fan_comment_video_count + 
				f.fan_comment_link_count	as total_comments,
				
				f.fan_like_status_count + 
				f.fan_like_photo_count + 
				f.fan_like_video_count + 
				f.fan_like_link_count + 
				f.fan_like_comment_count	as total_likes,
				
				f.fan_get_like_status_count + 
				f.fan_get_like_photo_count + 
				f.fan_get_like_video_count + 
				f.fan_get_like_link_count + 
				f.fan_get_like_comment_count as total_get_likes	,
				
				f.fan_get_comment_status_count + 
				f.fan_get_comment_photo_count + 
				f.fan_get_comment_video_count + 
				f.fan_get_comment_link_count as total_get_comments,
				
				f.fan_comment_link_count 						as link_comments,
				f.fan_comment_video_count						as video_comments,
				f.fan_comment_photo_count 						as photo_comments,
				f.fan_comment_status_count						as status_comments,
				
				f.fan_like_link_count 							as link_likes,
				f.fan_like_video_count 							as video_likes,
				f.fan_like_photo_count 							as photo_likes,
				f.fan_like_status_count 						as status_likes,
				f.fan_like_comment_count						as comment_likes,
				
				f.fan_get_like_video_count						as get_video_likes,
				f.fan_get_like_photo_count 						as get_photo_likes,
				f.fan_get_like_link_count 						as get_link_likes,
				f.fan_get_like_status_count 					as get_status_likes,
				f.fan_get_like_comment_count					as get_comment_likes,
				
				f.fan_get_comment_link_count						as get_link_comments,
				f.fan_get_comment_video_count 						as get_video_comments,
				f.fan_get_comment_status_count 					as get_status_comments,
				f.fan_get_comment_photo_count					as get_photo_comments,
		
				f.fan_post_status_count							as post_status,
				f.fan_post_photo_count 							as post_photo,
				f.fan_post_video_count 							as post_video,
				f.fan_post_link_count 								as post_link, 
				f.updated_time,
				DATEDIFF(f.updated_time, '$limit') as d
				
				FROM fans_objects_stats f
				WHERE f.fanpage_id = $fanpage_id AND f.facebook_user_id = $facebook_user_id
		
				order by  f.updated_time DESC
				limit 1) as a,

				(SELECT f.fan_post_status_count + 
				f.fan_post_photo_count + 
				f.fan_post_video_count + 
				f.fan_post_link_count	as total_posts, 
				
				f.fan_comment_status_count + 
				f.fan_comment_photo_count + 
				f.fan_comment_video_count + 
				f.fan_comment_link_count	as total_comments,
				
				f.fan_like_status_count + 
				f.fan_like_photo_count + 
				f.fan_like_video_count + 
				f.fan_like_link_count + 
				f.fan_like_comment_count	as total_likes,
				
				f.fan_get_like_status_count + 
				f.fan_get_like_photo_count + 
				f.fan_get_like_video_count + 
				f.fan_get_like_link_count + 
				f.fan_get_like_comment_count as total_get_likes	,
				
				f.fan_get_comment_status_count + 
				f.fan_get_comment_photo_count + 
				f.fan_get_comment_video_count + 
				f.fan_get_comment_link_count as total_get_comments,
				
				f.fan_comment_link_count 						as link_comments,
				f.fan_comment_video_count						as video_comments,
				f.fan_comment_photo_count 						as photo_comments,
				f.fan_comment_status_count						as status_comments,
				
				f.fan_like_link_count 							as link_likes,
				f.fan_like_video_count 							as video_likes,
				f.fan_like_photo_count 							as photo_likes,
				f.fan_like_status_count 						as status_likes,
				f.fan_like_comment_count						as comment_likes,
				
				f.fan_get_like_video_count						as get_video_likes,
				f.fan_get_like_photo_count 						as get_photo_likes,
				f.fan_get_like_link_count 						as get_link_likes,
				f.fan_get_like_status_count 					as get_status_likes,
				f.fan_get_like_comment_count					as get_comment_likes,
				
				f.fan_get_comment_link_count						as get_link_comments,
				f.fan_get_comment_video_count 						as get_video_comments,
				f.fan_get_comment_status_count 					as get_status_comments,
				f.fan_get_comment_photo_count					as get_photo_comments,
		
				f.fan_post_status_count							as post_status,
				f.fan_post_photo_count 							as post_photo,
				f.fan_post_video_count 							as post_video,
				f.fan_post_link_count 								as post_link, 
				f.updated_time,
				DATEDIFF(f.updated_time, '$limit') as d
				
				FROM fans_objects_stats f
				WHERE f.fanpage_id = $fanpage_id AND f.facebook_user_id = $facebook_user_id
				&& f.updated_time <= '$limit'
				order by  abs(DATEDIFF(f.updated_time, '$limit')) ASC
				limit 1) as b
			";
		}
		
		
		
		
		
		$result=$this->getAdapter()->fetchAll($select);
		
		if(empty($result)){
			
			$result[0]['total_posts'] = 0;
			$result[0]['total_comments'] = 0;
			$result[0]['total_likes'] = 0;
			$result[0]['total_get_likes'] = 0;
			$result[0]['total_get_comments'] = 0;
			
		}
		return $result;
	
	}
	
	//increment status posts
	public function addPostStatus($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_status_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_status_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}

	}
	
	//increment photos post
	public function addPostPhoto($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_photo_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_photo_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	}
	
	//increment video post
	public function addPostVideo($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_video_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_video_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//increment link post
	public function addPostLink($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_link_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_post_link_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}		
	
	
	}
	
	//increment status comments
	public function addCommentStatus($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_status_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_status_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//increment photo comments
	public function addCommentPhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_photo_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_photo_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	}
	
	//increment video comments
	public function addCommentVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_video_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_video_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}

	//increment link comments
	public function addCommentLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_link_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_comment_link_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	}

	
	//increment like status
	public function addLikeStatus($fanpage_id, $facebook_user_id) {
		//$found = $this->updatedFan($fanpage_id, $facebook_user_id);
		
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_status_count ++;
				$found->save ();
				
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_status_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
				
				$this->insert($found->toArray());
			}
		}
		
		
	}
	
	//increment like photo
	public function addLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_photo_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_photo_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	
	}
	
	//increment like video
	public function addLikeVideo($fanpage_id, $facebook_user_id) {
	
	$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_video_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_video_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//increment like status
	public function addLikeLink($fanpage_id, $facebook_user_id) {
	
	$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_link_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_link_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//increment like comment
	public function addLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_comment_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_like_comment_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}

	
	}
	
	//increment get status comments
	public function addGetCommentStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_status_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_status_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
			
	}
	
	//increment get photo comments
	public function addGetCommentPhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_photo_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_photo_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	
	}
	
	//increment get video comments
	public function addGetCommentVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_video_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_video_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
		
	
	}
	
	//increment get link comments
	public function addGetCommentLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_link_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_comment_link_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//increment get like status
	public function addGetLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_status_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_status_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//increment get like photo
	public function addGetLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_photo_count ++;
				$found->save ();
		
			}else{
				$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_photo_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//increment get like video
	public function addGetLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_video_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_video_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	}
	
	//increment get like status
	public function addGetLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_link_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_link_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
		

	
	}
	
	public function addGetLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_comment_count ++;
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->fan_get_like_comment_count ++;
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	
	//decrement like status
	public function subLikeStatus($fanpage_id, $facebook_user_id) {
		
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			
			
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_status_count > 0 ){
					
					$found->fan_like_status_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
	
				if ($found->fan_like_status_count > 0 ){
					$found->fan_like_status_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}

	
	}
	
	//decrement like photo
	public function subLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_photo_count > 0 ){
					
					$found->fan_like_photo_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				//$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_photo_count > 0 ){
					
					$found->fan_like_photo_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	}
	
	//decrement like video
	public function subLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				if ($found->fan_like_video_count > 0 ){
					$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
					$found->fan_like_video_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_video_count > 0 ){
					$found->fan_like_video_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//decrement like status
	public function subLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				//echo 'found data from today';
				if ($found->fan_like_link_count > 0 ){
					$found->fan_like_link_count --;
				}
				
				$found->save ();
		
			}else{
				$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_link_count > 0 ){
					$found->fan_like_link_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//decrement like comment
	public function subLikeComment($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_comment_count > 0 ){
					$found->fan_like_comment_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_like_comment_count > 0 ){
					$found->fan_like_comment_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	
	//decrement get like status
	public function subGetLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_status_count > 0 ){
					$found->fan_get_like_status_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_status_count > 0 ){
					$found->fan_get_like_status_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		
	
	}
	
	//decrement get like photo
	public function subGetLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_photo_count > 0 ){
					$found->fan_get_like_photo_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_photo_count > 0 ){
					$found->fan_get_like_photo_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
	
	}
	
	//decrement get like video
	public function subGetLikeVideo($fanpage_id, $facebook_user_id) {
		
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_video_count > 0 ){
					$found->fan_get_like_video_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_video_count> 0 ){
					$found->fan_get_like_video_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//decrement get like status
	public function subGetLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_link_count > 0 ){
					$found->fan_get_like_link_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_link_count> 0 ){
					$found->fan_get_like_link_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}
		

	
	}
	
	//decrement get like comment
	public function subGetLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->getLatestFanStat($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->updatedFan($fanpage_id, $facebook_user_id);
			//echo($found);
			//echo 'did not find previous data, creating new data';
		}else{
			//Zend_Debug::dump($found->updated_time);
			$dateObject = new Zend_Date($found->updated_time);
			$date = new Zend_Date();
			if($dateObject->compareDay($date )=== 0){
				//echo 'found data from today';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_comment_count > 0 ){
					$found->fan_get_like_comment_count --;
				}
				
				$found->save ();
		
			}else{
				//$dateObject = new Zend_Date();
				//echo 'found older stats data, using';
				$found->updated_time = $date->toString ( 'yyyy-MM-dd HH:mm:ss' );
				if ($found->fan_get_like_comment_count > 0 ){
					$found->fan_get_like_comment_count --;
				}
				$found->id = null;
				//Zend_Debug::dump($found->toArray());
		
				$this->insert($found->toArray());
			}
		}

	}
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
/*	
	public function getTotalPosts($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT (sum(f.fan_post_status_count) + sum(f.fan_post_photo_count) + sum(f.fan_post_video_count) + sum(f.fan_post_link_count)) as total_posts
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
		
		return $this->getAdapter()->fetchAll($select);
		
	}

	public function getTotalComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT (sum(f.fan_comment_status_count) + sum(f.fan_comment_photo_count) + sum(f.fan_comment_video_count) + sum(f.fan_comment_link_count)) as total_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getLinkComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_comment_link_count) as link_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getLinkLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_like_link_count) as link_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getLinkGetComments($fanpage_id, $facebook_user_id){
	$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
	if (empty ( $found )) {
	$found = $this->addFan($fanpage_id, $facebook_user_id);
	//echo($found);
	}
	$select = "	SELECT sum(f.fan_get_comment_link_count) as get_link_comments
	FROM fans_objects_stats f
	WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";

		return $this->getAdapter()->fetchAll($select);
	
	}
	
	
	
	public function getPhotoComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_comment_photo_count) as photo_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getPhotoLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_like_photo_count) as photo_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getPhotoGetComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_get_comment_photo_count) as get_photo_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getStatusComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_comment_status_count) as status_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getStatusLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_like_status_count) as status_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getStatusGetComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_get_comment_status_count) as get_status_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getVideoComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_comment_video_count) as video_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getVideoLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_like_video_count) as video_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getVideoGetComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT sum(f.fan_get_comment_video_count) as get_video_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	public function getTotalLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT (sum(f.fan_like_status_count) + sum(f.fan_like_photo_count) + sum(f.fan_like_video_count) + sum(f.fan_like_link_count)) as total_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalGetLikes($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT (sum(f.fan_get_like_status_count) + sum(f.fan_get_like_photo_count) + sum(f.fan_get_like_video_count) + sum(f.fan_get_like_link_count)) as total_get_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalGetComments($fanpage_id, $facebook_user_id){
		$found = $this->findFanRecord($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
		}
		$select = "	SELECT (sum(f.fan_get_comment_status_count) + sum(f.fan_get_comment_photo_count) + sum(f.fan_get_comment_video_count) + sum(f.fan_get_comment_link_count)) as total_get_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	*/
	public function getNumberOfCommentOnPostInTimeByUser($fanpage_id, $facebook_user_id, $time, $type){
		$select = "SELECT count(*) as count from posts p, comments c 
					where p.post_id = c.comment_post_id && p.fanpage_id = ".$fanpage_id." && c.facebook_user_id =".$facebook_user_id."
					&& c.facebook_user_id != p.facebook_user_id
					&& p.fanpage_id= c.fanpage_id && (c.created_time - ".$time.") < p.created_time && p.created_time < c.created_time ";
		if($type != 'all'){
			$select = $select.'&& p.post_type ="'.$type.'"';
		}
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getNumberOfLikesOnPostInTimeByUser($fanpage_id, $facebook_user_id, $time, $type){
		
	if($type == 'all'){
			$select =	"select a.count + b.count as count from
						(SELECT count(*) as count from likes l, comments c
							where  c.comment_id = l.post_id
								&& c.fanpage_id = ".$fanpage_id."
								&& l.facebook_user_id = ".$facebook_user_id."
								&& l.facebook_user_id != c.facebook_user_id
								&& c.fanpage_id = l.fanpage_id
								&& c.fanpage_id= l.fanpage_id 
								&& (l.created_time - ".$time.") < c.created_time 
								&& c.created_time < l.created_time		
							)as a, 
						(SELECT count(*) as count from posts p, likes l
							where  p.post_id = l.post_id 
								&& p.fanpage_id = ".$fanpage_id." 
								&& l.facebook_user_id = ".$facebook_user_id."
								&& l.facebook_user_id != p.facebook_user_id
								&& p.fanpage_id= l.fanpage_id 
								&& (l.created_time - ".$time.") < p.created_time 
								&& p.created_time < l.created_time 
						 ) as b";
		}elseif($type =='comment')	{
			$select ="SELECT count(*) as count from likes l, comments c
					where  c.comment_id = l.post_id
						&& c.fanpage_id = ".$fanpage_id."
						&& l.facebook_user_id = ".$facebook_user_id."
						&& l.facebook_user_id != c.facebook_user_id
						&& c.fanpage_id = l.fanpage_id
						&& c.fanpage_id= l.fanpage_id 
						&& (l.created_time - ".$time.") < c.created_time 
						&& c.created_time < l.created_time";				
		}else{
			$select = "SELECT count(*) as count from posts p, likes l
					where p.post_id = l.post_id && p.fanpage_id = ".$fanpage_id." && l.facebook_user_id =".$facebook_user_id."
					&& l.facebook_user_id != p.facebook_user_id
					&& p.fanpage_id= l.fanpage_id && (l.created_time - ".$time.") < p.created_time && p.created_time < l.created_time ";
			$select = $select.'&& p.post_type ="'.$type.'"';
		}
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getNumberOfLikesOnPostInTimeRecieved($fanpage_id, $facebook_user_id, $time, $type){
		if($type == 'all'){
			$select =	"select a.count + b.count as count from
						(SELECT count(*) as count from likes l, comments c
							where  c.comment_id = l.post_id
								&& c.fanpage_id = ".$fanpage_id."
								&& c.facebook_user_id = ".$facebook_user_id."
								&& l.facebook_user_id != c.facebook_user_id
								&& c.fanpage_id = l.fanpage_id
								&& c.fanpage_id= l.fanpage_id 
								&& (l.created_time - ".$time.") < c.created_time 
								&& c.created_time < l.created_time		
							)as a, 
						(SELECT count(*) as count from posts p, likes l
							where  p.post_id = l.post_id 
								&& p.fanpage_id = ".$fanpage_id." 
								&& p.facebook_user_id = ".$facebook_user_id."
								&& l.facebook_user_id != p.facebook_user_id
								&& p.fanpage_id= l.fanpage_id 
								&& (l.created_time - ".$time.") < p.created_time 
								&& p.created_time < l.created_time 
						 ) as b";
		}elseif($type =='comment')	{
			$select ="SELECT count(*) as count from likes l, comments c
					where  c.comment_id = l.post_id
						&& c.fanpage_id = ".$fanpage_id."
						&& c.facebook_user_id = ".$facebook_user_id."
						&& l.facebook_user_id != c.facebook_user_id
						&& c.fanpage_id = l.fanpage_id
						&& c.fanpage_id= l.fanpage_id 
						&& (l.created_time - ".$time.") < c.created_time 
						&& c.created_time < l.created_time";				
		}else{
			$select = "SELECT count(*) as count from posts p, likes l
					where p.post_id = l.post_id && p.fanpage_id = ".$fanpage_id." && p.facebook_user_id =".$facebook_user_id."
					&& l.facebook_user_id != p.facebook_user_id
					&& p.fanpage_id= l.fanpage_id && (l.created_time - ".$time.") < p.created_time && p.created_time < l.created_time ";
			$select = $select.'&& p.post_type ="'.$type.'"';
		}
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getNumberOfCommentOnPostInTimeRecieved($fanpage_id, $facebook_user_id, $time, $type){
		$select = "SELECT count(*) as count from posts p, comments c
					where p.post_id = c.comment_post_id && p.fanpage_id = ".$fanpage_id." && p.facebook_user_id =".$facebook_user_id."
					&& c.facebook_user_id != p.facebook_user_id
					&& p.fanpage_id= c.fanpage_id && (c.created_time - ".$time.") < p.created_time && p.created_time < c.created_time ";
		if($type != 'all'){
			$select = $select.'&& p.post_type ="'.$type.'"';
		}
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getHighestLikeOnPostCount($fanpage_id, $facebook_user_id){
		$select = "Select * from posts p where p.fanpage_id ='".$fanpage_id."' && p.facebook_user_id='".$facebook_user_id."' order by p.post_likes_count DESC limit 1";
		return $this->getAdapter()->fetchAll($select);
	}
	public function getHighestLikeOnCommentCount($fanpage_id, $facebook_user_id){
		$select = "Select * from comments c where c.fanpage_id ='".$fanpage_id."' && c.facebook_user_id='".$facebook_user_id."' order by c.comment_likes_count DESC limit 1";
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getHighestCommentOnPostCount($fanpage_id, $facebook_user_id){
		$select = "Select * from posts p where p.fanpage_id ='".$fanpage_id."' && p.facebook_user_id='".$facebook_user_id."' order by p.post_comments_count DESC limit 1";
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getAdminLikes($fanpage_id, $facebook_user_id){
		$select = "select com+pos as count
					from
					(SELECT count(*) as com from likes l, comments c
										where 
										c.comment_id = l.post_id
										&& c.fanpage_id = '".$fanpage_id ."'
										&& c.facebook_user_id = '".$facebook_user_id ."'
										&& c.fanpage_id = l.fanpage_id
										&& c.facebook_user_id != l.facebook_user_id
										&& l.facebook_user_id = l.fanpage_id
					) as a
					,
					
					(SELECT count(*) as pos from likes l, posts p
										where 
										p.post_id = l.post_id
										&& p.fanpage_id = '".$fanpage_id ."'
										&& p.facebook_user_id = 594232528
										&& p.fanpage_id = l.fanpage_id
										&& p.facebook_user_id != l.facebook_user_id
										&& l.facebook_user_id = l.fanpage_id
					)
					as b	"	;
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getAdminComment($fanpage_id, $facebook_user_id){
		$select = "SELECT count(*) as count from posts p , comments c
					where
					c.comment_id = p.post_id
					&& c.fanpage_id = '".$fanpage_id ."'
					&& c.facebook_user_id = '".$facebook_user_id ."'
					&& c.fanpage_id = p.fanpage_id
					&& c.facebook_user_id != p.facebook_user_id
					&& p.facebook_user_id = p.fanpage_id
					"	;
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getStatsByTime( $fanpage_id, $facebook_user_id){
		$select = "			select 'comments' as 'check', 'all' as post_type,
		count(*) as 'no-time',
		count( case when ((comment_created_time - interval 1 minute) < created_time && created_time < comment_created_time) then 1 end) as '1-minute',
		count(case when ((comment_created_time - interval 10 second) < created_time && created_time < comment_created_time ) then 1 end) as '10-second' from
			
		(SELECT p.*, c.created_time as comment_created_time
		from posts p, comments c
		where p.post_id = c.comment_post_id && p.fanpage_id = $fanpage_id && c.facebook_user_id = $facebook_user_id
		&& c.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= c.fanpage_id ) as temp
			
		union all
			
		select 'comments' as 'check', post_type,
			
		count(*) as 'no-time',
		count( case when ((comment_created_time - interval 1 minute) < created_time && created_time < comment_created_time) then 1 end) as '1-minute',
		count(case when ((comment_created_time - interval 10 second) < created_time && created_time < comment_created_time ) then 1 end) as '10-second'
		from
			
		(SELECT p.*, c.created_time as comment_created_time
		from posts p, comments c
		where p.post_id = c.comment_post_id && p.fanpage_id = $fanpage_id && c.facebook_user_id = $facebook_user_id
		&& c.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= c.fanpage_id  ) as temp
		group by post_type
			
		union all
			
		select 'get-comments' as 'check', 'all' as post_type,
		count(*) as 'no-time',
		count( case when ((comment_created_time - interval 1 minute) < created_time && created_time < comment_created_time) then 1 end) as '1-minute',
		count(case when ((comment_created_time - interval 10 second) < created_time && created_time < comment_created_time ) then 1 end) as '10-second'
		from
			
		(SELECT p.*, c.created_time as comment_created_time
		from posts p, comments c
		where p.post_id = c.comment_post_id && p.fanpage_id = $fanpage_id && p.facebook_user_id =  $facebook_user_id
		&& c.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= c.fanpage_id  ) as temp
			
		union all
			
		select 'get-comments' as 'check', post_type,
		count(*) as 'no-time',
		count( case when ((comment_created_time - interval 1 minute) < created_time && created_time < comment_created_time) then 1 end) as '1-minute',
		count(case when ((comment_created_time - interval 10 second) < created_time && created_time < comment_created_time ) then 1 end) as '10-second'
			
		from
			
		(SELECT p.*, c.created_time as comment_created_time
		from posts p, comments c
		where p.post_id = c.comment_post_id && p.fanpage_id = $fanpage_id && p.facebook_user_id =  $facebook_user_id
		&& c.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= c.fanpage_id ) as temp
		group by post_type
			
		union all
			
		select 'likes' as 'check', 'all'  as post_type,
		count(*) as 'no-time',
		count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
		count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
			
		from
			
		(SELECT p.*, l.created_time as like_created_time
		from posts p, likes l
		where p.post_id = l.post_id && p.fanpage_id = $fanpage_id && l.facebook_user_id =  $facebook_user_id
		&& l.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= l.fanpage_id  ) as temp
	
			
		union all
			
		select 'likes' as 'check', post_type,
		count(*) as 'no-time',
		count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
		count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
		from
			
		(SELECT p.*, l.created_time as like_created_time
		from posts p, likes l
		where p.post_id = l.post_id && p.fanpage_id = $fanpage_id && l.facebook_user_id =  $facebook_user_id
		&& l.facebook_user_id != p.facebook_user_id
		&& p.fanpage_id= l.fanpage_id  ) as temp
		group by post_type
			
		union all
			
		select 'likes' as 'check', 'comment' as post_type,
		count(*) as 'no-time',
		count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
		count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
		from
			
		(SELECT c.*,l.post_type, l.created_time as like_created_time
				from comments c, likes l
				where c.comment_id = l.post_id && c.fanpage_id = $fanpage_id && l.facebook_user_id =  $facebook_user_id
				&& l.facebook_user_id != c.facebook_user_id
				&& c.fanpage_id= l.fanpage_id  ) as temp
					
				union all
					
				select 'get-likes' as 'check', 'all' as post_type,
				count(*) as 'no-time',
				count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
				count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
				from
					
				(SELECT p.*, l.created_time as like_created_time
				from posts p, likes l
				where p.post_id = l.post_id && p.fanpage_id = $fanpage_id && p.facebook_user_id =  $facebook_user_id
				&& l.facebook_user_id != p.facebook_user_id
				&& p.fanpage_id= l.fanpage_id  ) as temp
					
				union all
					
				select 'get-likes' as 'check', post_type,
				count(*) as 'no-time',
				count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
				count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
					
				from
					
				(SELECT p.*, l.created_time as like_created_time
				from posts p, likes l
				where p.post_id = l.post_id && p.fanpage_id = $fanpage_id && p.facebook_user_id =  $facebook_user_id
				&& l.facebook_user_id != p.facebook_user_id
				&& p.fanpage_id= l.fanpage_id ) as temp
				group by post_type
					
				union all
					
				select 'get-likes' as 'check', 'comment' as post_type,
				count(*) as 'no-time',
				count( case when ((like_created_time - interval 1 minute) < created_time && created_time < like_created_time) then 1 end) as '1-minute',
				count(case when ((like_created_time - interval 10 second) < created_time && created_time < like_created_time ) then 1 end) as '10-second'
				from
					
				(SELECT c.*,l.post_type, l.created_time as like_created_time
				from comments c, likes l
				where c.comment_id = l.post_id && c.fanpage_id = $fanpage_id && c.facebook_user_id =  $facebook_user_id
				&& l.facebook_user_id != c.facebook_user_id
				&& c.fanpage_id= l.fanpage_id  ) as temp";
					
	
				return $this->getAdapter()->fetchAll($select);
	}
}