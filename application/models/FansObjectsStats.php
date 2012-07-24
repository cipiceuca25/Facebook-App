<?php

class Model_FansObjectsStats extends Model_DbTable_FansObjectsStats
{
	protected function addFan($fanpage_id, $facebook_user_id){
		$dateObject = new Zend_Date();
		$data = array(	'fanpage_id' => $fanpage_id, 
						'facebook_user_id' => $facebook_user_id, 
						'updated_time'=>$dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' ),
					  	'fan_posts_count' => 0, 
						'fan_comments_count' =>0, 
						'fan_photos_count' => 0, 
						'fan_got_posts_likes_count'=> 0,
						'fan_got_comments_likes_count'=>0,
					  	'fan_likes_other_posts_count' => 0,
						'fan_likes_other_comments_count'=> 0, 
						'fan_likes_other_albums_count'=> 0, 
						'fan_likes_other_photos_count'=>0,
					  	'fan_got_likes_total'=> 0, 
						'fan_likes_other_total'=> 0,
						'fan_got_comment_total' => 0);
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
	
	//increment total posts
	public function addPost($fanpage_id, $facebook_user_id) {
		
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		//Zend_Debug::dump($found);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		$found->fan_posts_count ++;
		$found->save ();
		
	}
	 
	//increment total comments
	public function addComment($fanpage_id, $facebook_user_id) {
		
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
			$found->fan_comments_count ++;
			$found->save ();
		
	}
	
	//increment total photos
	public function addPhotos($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
		$found->fan_photos_count ++;
		$found->save ();
		
	}
	
	public function addLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
			$found->fan_likes_other_posts_count ++;
			$found->fan_likes_other_total ++;
			
			$found->save ();
		
	}
	
	public function addLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
			
			$found->fan_likes_other_comments_count ++;
			$found->fan_likes_other_total ++;
	
			$found->save ();
		
	}
	
	public function addLikePhoto($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		
			$found->fan_likes_other_photos_count ++;
			$found->fan_likes_other_total ++;
		
			$found->save ();
		
	}
	
	public function addLikeAlbum($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}

			$found->fan_likes_other_albums_count ++;
			$found->fan_likes_other_total ++;
			
			$found->save ();
		
	}
	
	public function addOtherLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
	
			$found->fan_got_posts_likes_count ++;
			$found->fan_got_likes_total ++;
			
			$found->save ();
		
	}
	
	public function addOtherLikeComments($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
			
			$found->fan_got_comments_likes_count ++;
			$found->fan_got_likes_total ++;
			
			$found->save ();
		
	}
	
	public function addOtherPostComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
			
			
			$found->fan_got_comment_total ++;
			
			$found->save ();
		
	}
	
	//increment total posts
	public function subPost($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		
		try{
			$found->fan_posts_count --;
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	//increment total comments
	public function subComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		
		try{	
			$found->fan_comments_count --;
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	//increment total photos
	public function subPhotos($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		
		try{
			$found->fan_photos_count --;
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	public function subLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{
			$found->fan_likes_other_posts_count --;
			$found->fan_likes_other_total --;

			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	public function subLikeComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{
			$found->fan_likes_other_comments_count --;
			$found->fan_likes_other_total --;
			
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	public function subLikePhoto($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		
		try{	
			$found->fan_likes_other_photos_count --;
			$found->fan_likes_other_total --;
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
		
		
	}
	
	public function subLikeAlbum($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{	
			$found->fan_likes_other_albums_count --;
			$found->fan_likes_other_total --;
			
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	public function subOtherLikePost($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{	
			$found->fan_got_posts_likes_count --;
			$found->fan_got_likes_total --;
			
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	public function subOtherLikeComments($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
	
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{	
			$found->fan_got_comments_likes_count --;
			$found->fan_got_likes_total --;
	
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
	
	public function subOtherPostComment($fanpage_id, $facebook_user_id) {
		$found = $this->findFan($fanpage_id, $facebook_user_id);
		
		if (empty ( $found )) {
			$found = $this->addFan($fanpage_id, $facebook_user_id);
			//echo($found);
			$found  =$this->find($found)->current();
		}
		try{
			$found->fan_got_comment_total --;
			$found->save ();
		}catch (Exception $e){
			echo $e->getMessage();
		}
	
	}
}

