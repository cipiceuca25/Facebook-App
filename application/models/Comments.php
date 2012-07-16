<?php

class Model_Comments extends Model_DbTable_Comments
{
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
	
/*
	public function getCommentsByPostId($postId, $limit) {
		
		$select = "	SELECT * from facebook_users, comments where facebook_users.facebook_user_id = comments.facebook_user_id && comments.comment_post_id ='" . $postId ."'";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
				
    }
	*/

	
	public function getCommentsByPostId($postId, $limit) {
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false);
		$select->join(array('facebook_users'), 'facebook_users.facebook_user_id = comments.facebook_user_id');
		$select->where($this->quoteInto('comment_post_id = ?', $postId));
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		return $this->fetchAll($select); 
	}


}

