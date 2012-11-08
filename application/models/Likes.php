<?php

class Model_Likes extends Model_DbTable_Likes
{
	public static function prepareCommentLikeData($like, $comment, $fanpageId = null) {
		if (empty($comment->id)) {
			return array();
		}
		
		if (count($commentId = explode('_', $comment->id)) > 1) {
			$fanpageId = $commentId[0];
		} else {
			return array();
		}
		
		$created = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : time(), Zend_Date::ISO_8601);
		$updated = new Zend_Date();
		
		$row = array(
				'fanpage_id'        => $fanpageId,
				'post_id'           => $comment->id,
				'facebook_user_id'  => $like->id,
				'created_time'		=> $created->toString( 'yyyy-MM-dd HH:mm:ss' ),
				'updated_time'		=> $updated->toString( 'yyyy-MM-dd HH:mm:ss' ),
				'post_type'         => $comment->comment_type .'_comment'
		);
		
		return $row;
	}
	
	public static function preparePostLikeData($like, $post, $fanpageId = null) {
		if (empty($post->id)) {
			return array();
		}
	
		if (count($commentId = explode('_', $post->id)) > 1) {
			$fanpageId = $commentId[0];
		} else {
			return array();
		}
	
		$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : time(), Zend_Date::ISO_8601);
		$updated = new Zend_Date();
	
		$row = array(
				'fanpage_id'        => $fanpageId,
				'post_id'           => $post->id,
				'facebook_user_id'  => $like->id,
				'created_time'		=> $created->toString( 'yyyy-MM-dd HH:mm:ss' ),
				'updated_time'		=> $updated->toString( 'yyyy-MM-dd HH:mm:ss' ),
				'post_type'         => $post->type
		);
	
		return $row;
	}
	
	public function insertNewLikes($fanpage_id, $post_id, $facebook_user_id, $post_type) {
		$found = $this->find ( $fanpage_id, $post_id, $facebook_user_id )->current ();
		// zend_debug::dump($found);
		$dateObject = new Zend_Date();
		
		if (empty ( $found )) {
			$data = array (
					'fanpage_id' => $fanpage_id,
					'post_id' => $post_id,
					'facebook_user_id' => $facebook_user_id,
					'post_type' => $post_type,
					'likes' => 1 ,
					'updated_time' => $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' )
			);

			$this->insert ( $data );
		} else {
			$found->likes = 1;
			$dateObject = new Zend_Date();
			$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
	public function insertNewLikesReturn($fanpage_id, $post_id, $facebook_user_id, $post_type) {
		$found = $this->find ( $fanpage_id, $post_id, $facebook_user_id )->current ();
		// zend_debug::dump($found);
		$dateObject = new Zend_Date();
	
		if (empty ( $found )) {
			$data = array (
					'fanpage_id' => $fanpage_id,
					'post_id' => $post_id,
					'facebook_user_id' => $facebook_user_id,
					'post_type' => $post_type,
					'likes' => 1 ,
					'updated_time' => $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' )
			);
	
			$this->insert ( $data );
			return 1;
		} else {
			if ($found->likes == 1){
				return 0;	
			}
			
			$found->likes = 1;
			$dateObject = new Zend_Date();
			$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
			return $found;
		}
	}
	public function unlike($fanpage_id, $post_id, $facebook_user_id, $post_type) {
		$found = $this->find ( $fanpage_id, $post_id, $facebook_user_id )->current ();
		
		if (! empty ( $found )) {
			if ($found->likes == 0){
				return 0;
			}
			$found->likes = 0;
			$dateObject = new Zend_Date();
			$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->save ();
		}
	}
       
       
    public function getLikes($fanpage_id, $post_id, $facebook_user_id){

    		$found = $this->find($fanpage_id, $post_id, $facebook_user_id)->current();
    		//echo $found['likes'];
               if ( empty($found) || $found['likes'] == 0) {
                      return 0;
               }else {
                      return 1;
               }
    
    }
	
	public function isDataValid($data) {
		if(empty($data)) {
			return false;
		}
		//echo 'data not empty';
		//Zend_Debug::dump($data);

		foreach ($data as $key => $field) {
			if(empty($field)) {
				return false;
			}
			if($key === 'post_type' && ! in_array($field, array('status', 'photo', 'comment', 'post', 'album', 'video', 'link'))) {
				return false;
			}
			
		}
		//echo 'data past';
		return $this->isIdFieldsValid($data);
	}
	
	private function isIdFieldsValid($data) {
		$idValidator = new Zend_Validate_Digits();
		$ids = array('facebook_user_id', 'fanpage_id');
		foreach ($ids as $key => $id) {
			if (! $idValidator->isValid($data[$id])) {
				return false;
			}
		}
		//echo 'key past';
		if($data['facebook_user_id'] === $data['fanpage_id']) {
			return false;
		}
		
		return true;
	}
}

