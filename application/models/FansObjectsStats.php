<?php

class Model_FansObjectsStats extends Model_DbTable_FansObjectsStats
{
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
	
	public function getTotalPosts($fanpage_id, $facebook_user_id){
		$select = "	SELECT (sum(f.fan_post_status_count) + sum(f.fan_post_photo_count) + sum(f.fan_post_video_count) + sum(f.fan_post_link_count)) as total_posts
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
		
		return $this->getAdapter()->fetchAll($select);
		
	}

	public function getTotalComments($fanpage_id, $facebook_user_id){
		$select = "	SELECT (sum(f.fan_comment_status_count) + sum(f.fan_comment_photo_count) + sum(f.fan_comment_video_count) + sum(f.fan_comment_link_count)) as total_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalLikes($fanpage_id, $facebook_user_id){
		$select = "	SELECT (sum(f.fan_like_status_count) + sum(f.fan_like_photo_count) + sum(f.fan_like_video_count) + sum(f.fan_like_link_count)) as total_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalGetLikes($fanpage_id, $facebook_user_id){
		$select = "	SELECT (sum(f.fan_get_like_status_count) + sum(f.fan_get_like_photo_count) + sum(f.fan_get_like_video_count) + sum(f.fan_get_like_link_count)) as total_get_likes
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTotalGetComments($fanpage_id, $facebook_user_id){
		$select = "	SELECT (sum(f.fan_get_comment_status_count) + sum(f.fan_get_comment_photo_count) + sum(f.fan_get_comment_video_count) + sum(f.fan_get_comment_link_count)) as total_get_comments
					FROM fans_objects_stats f
					WHERE f.fanpage_id = '".$fanpage_id."' AND f.facebook_user_id = '".$facebook_user_id."'";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
}

