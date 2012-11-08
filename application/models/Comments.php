<?php

class Model_Comments extends Model_DbTable_Comments
{
	
	public static function prepareCommentData($comment, $postId = null, $fanpageId = null) {
		if (empty($comment->id)) {
			return array();
		}
		
		if (count($commentId = explode('_', $comment->id)) > 0) {
			$fanpageId = $commentId[0];
			$postId = count($commentId) > 2 ? $commentId[0] .'_' .$commentId[1] : $commentId[0];
		} else {
			return array(); 
		}
		
		if (!$postId || !$fanpageId) {
			return array();
		}
		
		$created = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : null, Zend_Date::ISO_8601);
		$row = array (
				'comment_id' => $comment->id,
				'fanpage_id' => $fanpageId,
				'comment_post_id' => $postId,
				'facebook_user_id' => $comment->from->id,
				'comment_message' => Zend_Db_Table::getDefaultAdapter()->quote($comment->message),
				'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
				'comment_likes_count' => isset ( $comment->like_count ) ? $comment->like_count : 0,
				'comment_type' => $comment->comment_type
		);
		
		return $row;
	}
	
	public function insertComment($fanpage_id, $post_id, $comments)
	{
	
		$data = array(

		);
	
		$insert = $this->getAdapter()->insert(array('comments' => 'comments'), $data);
	
	}
	
	public function getCommentAboveByTimestamp($commentId, $limit) {
		$comment = $this->find($commentId)->current();
		if(! empty($comment->comment_post_id) && ! empty($comment->created_time)) {
			$select = $this->select()->where('comment_post_id = ?', $comment->comment_post_id)
							->where('created_time >= ?', $comment->created_time)
							->order('created_time desc')
							->limit($limit);
			return $this->fetchAll($select);
		}
	}
	
	public function addLikeToComment($comment_id){
		
		$found = $this->findComment($comment_id);

		if (!empty ( $found )) {
			$found->comment_likes_count ++;
			$found->save ();
		}
	}
	
	public function addLikeToCommentReturn($comment_id){
	
		$found = $this->findComment($comment_id);
	
		if (!empty ( $found )) {
			$found->comment_likes_count ++;
			$found->save ();
		}
		
		return $found;
	}
	
	public function subtractLikeToCommentReturn($id) {
		$found = $this->findComment($id);
		if (!empty ( $found )) {
			if ($found->comment_likes_count >0){
				$found->comment_likes_count --;
			}
			$found->save ();
		}
		return $found;
	}
	
	public function findComment($comment_id){
		$query = $this->select()
		->from($this)
		->where('comment_id = ?', $comment_id);
		return $this->fetchAll($query)->current();
	}
	
	public function getCommentBelowByTimestamp($commentId, $limit) {
		$comment = $this->find($commentId)->current();
		if(! empty($comment->comment_post_id) && ! empty($comment->created_time)) {
			$select = $this->select()->where('comment_post_id = ?', $comment->comment_post_id)
										->where('created_time < ? ', $comment->created_time)
										->order('created_time desc')
										->limit($limit);
			return $this->fetchAll($select);
		}
	}
	
	public function getClosestCommentsByTimestamp($commentId, $limit) {
		$comment = $this->find($commentId)->current();
		$limit2 = $limit++;
		if(! empty($comment->comment_post_id) && ! empty($comment->created_time)) {

			$select = "(SELECT * from comments where comment_post_id = '" .$comment->comment_post_id ."' and created_time >= '" .$comment->created_time ."' order by created_time limit $limit)
						union all
						(SELECT * from comments where comment_post_id = '" .$comment->comment_post_id ."' and created_time < '" .$comment->created_time ."' order by created_time limit $limit2)
						ORDER BY created_time";	
			return $this->getAdapter()->fetchAll($select);
		}
	}
	
	public function getCommentsByPostId($postId, $limit) {
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false);
		$select->join(array('facebook_users'), 'facebook_users.facebook_user_id = comments.facebook_user_id');
		$select->where($this->quoteInto('comment_post_id = ?', $postId));
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		return $this->fetchAll($select); 
	}

	public function getUserCommentCountByPost($postId, $facebook_user_id) {
		$select = $this->select();
		$select->from($this, array('count(*) as count'));
		$select->where('comment_post_id = ?', $postId)
			->where('facebook_user_id = ?', $facebook_user_id);
		$rows = $this->fetchAll($select);
		
		if(empty($rows[0]->count)) {
			return 0;
		}
		return $rows[0]->count;
	}
}

