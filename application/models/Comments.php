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

	public function getPostOwnerCommentCount($postId, $ownerId) {
		$select = $this->select();
		$select->from($this, array('count(*) as count'));
		$select->where($this->quoteInto('comment_post_id = ?', $postId))
				->where($this->quoteInto('facebook_user_id = ?', $ownerId));
		$rows = $this->fetchAll($select);
		return ($rows[0]->count);
	}
}

