<?php

class Model_FansObjectsStats extends Model_DbTable_FansObjectsStats
{
	protected function addFan($fanpage_id, $facebook_user_id){
		$dateObject = new Zend_Date();
		$data = array('fanpage_id' => $fanpage_id, 'facebook_user_id' => $facebook_user_id, 'updated_time'=>$dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' ),
					  'fan_posts_count'
				
				
				);
		$this->insert ( $data );
	}
	
	//increment total posts
	public function addPost($fanpage_id, $facebook_user_id) {
		$dateObject = new Zend_Date();
		
		$found = $this->findAll ($fanpage_id, $facebook_user_id,$dateObject->toString ( 'yyyy-MM-dd' ))->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_posts_count ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd' );
			$found->save ();
		}
	}
	 
	//increment total comments
	public function addComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_comments_count ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	//increment total photos
	public function addPhotos($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_photos_count ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_posts_count ++;
			$found->fan_likes_other_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_comments_count ++;
			$found->fan_likes_other_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addLikePhoto($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_photos_count ++;
			$found->fan_likes_other_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addLikeAlbum($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_albums_count ++;
			$found->fan_likes_other_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addOtherLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_got_posts_likes_count ++;
			$found->fan_got_likes_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addOtherLikeComments($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_got_comments_likes_count ++;
			$found->fan_got_likes_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function addOtherPostComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			
			$found->fan_got_comment_total ++;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	//increment total posts
	public function subPost($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_posts_count --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	//increment total comments
	public function subComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_comments_count --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	//increment total photos
	public function subPhotos($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_photos_count --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_posts_count --;
			$found->fan_likes_other_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_comments_count --;
			$found->fan_likes_other_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subLikePhoto($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_photos_count --;
			$found->fan_likes_other_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subLikeAlbum($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_likes_other_albums_count --;
			$found->fan_likes_other_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subOtherLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_got_posts_likes_count --;
			$found->fan_got_likes_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subOtherLikeComments($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
	
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			$found->fan_got_comments_likes_count --;
			$found->fan_got_likes_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	
	public function subOtherPostComment($fanpage_id, $facebook_user_id) {
		$found = $this->find ($fanpage_id, $facebook_user_id)->current ();
		
		if (! empty ( $found )) {
			$dateObject = new Zend_Date();
			
			$found->fan_got_comment_total --;
			$found->updated_time= $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
}

