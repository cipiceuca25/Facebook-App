<?php

class Model_FansObjectsStats extends Model_DbTable_FansObjectsStats
{
	public function updatedFan($fanpage_id, $facebook_user_id) {
		$date = new Zend_Date();
		$fanStat = $this->findFan($fanpage_id, $facebook_user_id);

		$data = array(	'fanpage_id' => $fanpage_id,
				'facebook_user_id' => $facebook_user_id,
				'updated_time'=>$date->toString ( 'yyyy-MM-dd HH:mm:ss' ),
				'fan_post_status_count' => $this->getFanPostStatusCount($fanpage_id, $facebook_user_id),
				'fan_post_photo_count' => $this->getFanPostPhotoCount($fanpage_id, $facebook_user_id),
				'fan_post_video_count' => $this->getFanPostVideoCount($fanpage_id, $facebook_user_id),
				'fan_post_link_count' => $this->getFanPostLinkCount($fanpage_id, $facebook_user_id),
				'fan_comment_status_count' => $this->getFanCommentStatusCount($fanpage_id, $facebook_user_id),
				'fan_comment_photo_count' => $this->getFanCommentPhotoCount($fanpage_id, $facebook_user_id),
				'fan_comment_video_count' => $this->getFanCommentVideoCount($fanpage_id, $facebook_user_id),
				'fan_comment_link_count' => $this->getFanCommentLinkCount($fanpage_id, $facebook_user_id),
				'fan_like_status_count' => $this->getFanLikeStatusCount($fanpage_id, $facebook_user_id),
				'fan_like_photo_count' => $this->getFanLikePhotoCount($fanpage_id, $facebook_user_id),
				'fan_like_video_count' => $this->getFanLikeVideoCount($fanpage_id, $facebook_user_id),
				'fan_like_link_count' => $this->getFanLikeLinkCount($fanpage_id, $facebook_user_id),
				'fan_like_comment_count' => $this->getFanLikeCommentCount($fanpage_id, $facebook_user_id),
				'fan_get_like_status_count' => $this->getFanGotLikeFromStatus($fanpage_id, $facebook_user_id),
				'fan_get_like_photo_count' => $this->getFanGotLikeFromPhoto($fanpage_id, $facebook_user_id),
				'fan_get_like_video_count' => $this->getFanGotLikeFromVideo($fanpage_id, $facebook_user_id),
				'fan_get_like_link_count' => $this->getFanGotLikeFromLink($fanpage_id, $facebook_user_id),
				'fan_get_like_comment_count' => $this->getFanGotLikeFromComment($fanpage_id, $facebook_user_id),
				'fan_get_comment_status_count' => $this->getFanGotCommentCountFromStatus($fanpage_id, $facebook_user_id),
				'fan_get_comment_photo_count' => $this->getFanGotCommentCountFromPhoto($fanpage_id, $facebook_user_id),
				'fan_get_comment_video_count' => $this->getFanGotCommentCountFromVideo($fanpage_id, $facebook_user_id),
				'fan_get_comment_link_count' => $this->getFanGotCommentCountFromLink($fanpage_id, $facebook_user_id),
		);
		
		if($fanStat) {
			foreach ($data as $key => $value) {
				$fanStat->{$key} = $value;
			}
			$fanStat->save();
		}else {
			$this->insert($data);
		}
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
		if($type === 'all') {
			$commentModel = new Model_Comments();
			$select = $commentModel->select();
			$select->from($commentModel, array('count(*) as count'));
			$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id));
			$rows = $this->fetchAll($select);
			return ($rows[0]->count);
		}
		$commentModel = new Model_Comments();
		$select = $commentModel->select();
		$select->from($commentModel, array('count(*) as count'));
		$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
				->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id))
				->where($this->quoteInto('comment_type = ?', $type));
		$rows = $this->fetchAll($select);
		return ($rows[0]->count);
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
		if($type === 'all') {
			$likeModel = new Model_Likes();
			$select = $likeModel->select();
			$select->from($likeModel, array('count(*) as count'));
			$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
				->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id))
				->where($this->quoteInto('likes = ?', 1));
			$rows = $this->fetchAll($select);
			return ($rows[0]->count);
		}
		
		$likeModel = new Model_Likes();
		$select = $likeModel->select();
		$select->from($likeModel, array('count(*) as count'));
		$select->where($this->quoteInto('fanpage_id = ?', $fanpage_id))
			->where($this->quoteInto('facebook_user_id = ?', $facebook_user_id))
			->where($this->quoteInto('post_type = ?', $type))
			->where($this->quoteInto('likes = ?', 1));
		$rows = $this->fetchAll($select);
		return ($rows[0]->count);
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
				WHERE p.facebook_user_id = $facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'status' AND l.likes = 1";
				break;
			case 'link':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'link' AND l.likes = 1";
				break;
			case 'photo':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'photo' AND l.likes = 1";
				break;
			case 'video':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id)
				WHERE p.facebook_user_id = $facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type = 'video' AND l.likes = 1";
				break;
			case 'comment':
				$select = "SELECT count(*) AS count FROM likes l LEFT JOIN comments c ON(l.post_id = c.comment_id) WHERE c.facebook_user_id = 
							$facebook_user_id AND l.fanpage_id = $fanpage_id AND l.post_type LIKE '%_comment' AND l.likes = 1"; 
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
			$select = "SELECT sum(p.post_comments_count) AS count FROM posts p
				WHERE p.fanpage_id = $fanpage_id AND p.facebook_user_id = $facebook_user_id GROUP BY p.facebook_user_id";
		}else {
			$select = "SELECT sum(p.post_comments_count) AS count FROM posts p
				WHERE p.fanpage_id = $fanpage_id AND p.facebook_user_id = $facebook_user_id AND p.post_type = '". $type ."' GROUP BY p.facebook_user_id";
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
		echo 'making new entry';
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
	
	public function findFanRecord($fanpage_id, $facebook_user_id){
		
		$select = "	SELECT 
				(sum(f.fan_post_status_count) + sum(f.fan_post_photo_count) + sum(f.fan_post_video_count) + sum(f.fan_post_link_count)) 													as total_posts, 
				(sum(f.fan_comment_status_count) + sum(f.fan_comment_photo_count) + sum(f.fan_comment_video_count) + sum(f.fan_comment_link_count)) 										as total_comments,
				(sum(f.fan_like_status_count) + sum(f.fan_like_photo_count) + sum(f.fan_like_video_count) + sum(f.fan_like_link_count)) + sum(f.fan_like_comment_count)						as total_likes,
				(sum(f.fan_get_like_status_count) + sum(f.fan_get_like_photo_count) + sum(f.fan_get_like_video_count) + sum(f.fan_get_like_link_count)) + sum(f.fan_get_like_comment_count)	as total_get_likes	,
				(sum(f.fan_get_comment_status_count) + sum(f.fan_get_comment_photo_count) + sum(f.fan_get_comment_video_count) + sum(f.fan_get_comment_link_count)) 						as total_get_comments,
				
				sum(f.fan_comment_link_count) 							as link_comments,
				sum(f.fan_comment_video_count) 							as video_comments,
				sum(f.fan_comment_photo_count) 							as photo_comments,
				sum(f.fan_comment_status_count) 						as status_comments,
				
				sum(f.fan_like_link_count) 								as link_likes,
				sum(f.fan_like_video_count) 							as video_likes,
				sum(f.fan_like_photo_count) 							as photo_likes,
				sum(f.fan_like_status_count) 							as status_likes,
				sum(f.fan_like_comment_count)							as comment_likes,
				
				sum(f.fan_get_like_video_count) 						as get_video_likes,
				sum(f.fan_get_like_photo_count) 						as get_photo_likes,
				sum(f.fan_get_like_link_count) 							as get_link_likes,
				sum(f.fan_get_like_status_count) 						as get_status_likes,
				sum(f.fan_get_like_comment_count)						as get_comment_likes,
				
				sum(f.fan_get_comment_link_count) 						as get_link_comments,
				sum(f.fan_get_comment_video_count) 						as get_video_comments,
				sum(f.fan_get_comment_status_count) 					as get_status_comments,
				sum(f.fan_get_comment_photo_count) 						as get_photo_comments,
		
				sum(f.fan_post_status_count) 							as post_status,
				sum(f.fan_post_photo_count) 							as post_photo,
				sum(f.fan_post_video_count) 							as post_video,
				sum(f.fan_post_link_count) 								as post_link
				
				FROM fans_objects_stats f
				WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
		//Zend_Debug::dump($query);
		return $this->getAdapter()->fetchAll($select);
		
	
	}
	
	//increment status posts
	public function addPostStatus($fanpage_id, $facebook_user_id) {
		
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_post_status_count ++;
		$found->save ();
		
	}
	
	//increment photos post
	public function addPostPhoto($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
		$found->fan_post_photo_count ++;
		$found->save ();
	
	}
	
	//increment video post
	public function addPostVideo($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
		$found->fan_post_video_count ++;
		$found->save ();
	
	}
	
	//increment link post
	public function addPostLink($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
		$found->fan_post_link_count ++;
		$found->save ();
	
	}
	
	//increment status comments
	public function addCommentStatus($fanpage_id, $facebook_user_id) {
		
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
			$found->fan_comment_status_count ++;
			$found->save ();
		
	}
	
	//increment photo comments
	public function addCommentPhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_comment_photo_count ++;
		$found->save ();
	
	}
	
	//increment video comments
	public function addCommentVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_comment_video_count ++;
		$found->save ();
	
	}

	//increment link comments
	public function addCommentLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_comment_link_count ++;
		$found->save ();
	
	}

	
	//increment like status
	public function addLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_status_count ++;
		$found->save ();
	
	}
	
	//increment like photo
	public function addLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_photo_count ++;
		$found->save ();
	
	}
	
	//increment like video
	public function addLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_video_count ++;
		$found->save ();
	
	}
	
	//increment like status
	public function addLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_link_count ++;
		$found->save ();
	
	}
	
	//increment like comment
	public function addLikeComment($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_comment_count ++;
		$found->save ();
	
	}
	
	//increment get status comments
	public function addGetCommentStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_comment_status_count ++;
		$found->save ();
	
	}
	
	//increment get photo comments
	public function addGetCommentPhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_comment_photo_count ++;
		$found->save ();
	
	}
	
	//increment get video comments
	public function addGetCommentVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_comment_video_count ++;
		$found->save ();
	
	}
	
	//increment get link comments
	public function addGetCommentLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_comment_link_count ++;
		$found->save ();
	
	}
	
	//increment get like status
	public function addGetLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_status_count ++;
		$found->save ();
	
	}
	
	//increment get like photo
	public function addGetLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_photo_count ++;
		$found->save ();
	
	}
	
	//increment get like video
	public function addGetLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_video_count ++;
		$found->save ();
	
	}
	
	//increment get like status
	public function addGetLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_link_count ++;
		$found->save ();
	
	}
	
	
	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	
	//decrement like status
	public function subLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_status_count --;
		$found->save ();
	
	}
	
	//decrement like photo
	public function subLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_photo_count --;
		$found->save ();
	
	}
	
	//decrement like video
	public function subLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_video_count --;
		$found->save ();
	
	}
	
	//decrement like status
	public function subLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_link_count --;
		$found->save ();
	
	}
	
	//decrement like comment
	public function subLikeComment($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_like_comment_count --;
		$found->save ();
	
	}
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	
	//decrement get like status
	public function subGetLikeStatus($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_status_count --;
		$found->save ();
	
	}
	
	//decrement get like photo
	public function subGetLikePhoto($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_photo_count --;
		$found->save ();
	
	}
	
	//decrement get like video
	public function subGetLikeVideo($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_video_count --;
		$found->save ();
	
	}
	
	//decrement get like status
	public function subGetLikeLink($fanpage_id, $facebook_user_id) {
	
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_link_count --;
		$found->save ();
	
	}
	
	//decrement get like comment
	public function subGetLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_get_like_comment_count --;
		$found->save ();
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
	
}

