<?php

class Model_Posts extends Model_DbTable_Posts
{
	public function checkPostExists($id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('posts' => 'posts'));
		$select->where($this->getAdapter()->quoteInto('posts.post_id = ?', $id));
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function checkPostUpdatedTime($id, $updated_time)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('posts' => 'posts'));
		$select->where($this->getAdapter()->quoteInto('posts.post_id = ?', $id) AND $this->getAdapter()->quoteInto('posts.updated_time < ?', $updated_time));
	
		$result = $this->getAdapter()->fetchAll($select);
		return count($result);
	}
	
	public function updateExistingPost($post)
	{
		$data = array(
			'updated_time' 		=>	$post['activityupdated_time'],
			'comments_count'	=>	$post['commentscount'],
			'likes_count'		=>	$post['likescount']		
		);
		
		$update = $this->getAdapter()->update(array('posts' => 'posts'), $data);
		$update->where($this->getAdapter()->quoteInto('posts.post_id = ?', $post[activityid]));

	}
	
	public function insertPost($fanpage_id, $post)
	{

		//$filter = new Zend_Filter_Input($filterRules, $validatorRules);
		$data = array(
				'post_id'			=>	$post['activityid'],
				'facebook_user_id'	=>	$post['fromid'],
				'fanpage_id'		=>	$fanpage_id,
				'user_cateogry'		=>	$post['activityuser_category'],
				'message'			=>	$post['activitymessage'],
				'privacy_descr'		=>	$post['privacydescription'],
				'privacy_value'		=>	$post['privacyvalue'],
				'type'				=>	$post['activitytype'],
				'created_time'		=>	$post['activitycreated_time'],
				'updated_time' 		=>	$post['activityupdated_time'],
				'application_name'	=>	$post['applicationname'],
				'application_id'	=>	$post['applicationid'],
				'comments_count'	=>	$post['commentscount'],
				'likes_count'		=>	$post['likescount']
		);
	
		$insert = $this->getAdapter()->insert(array('posts' => 'posts'), $data);
	
	}
	
	public function getUserPost($user_id, $limit=10) {
		$select = "SELECT p.*, f.facebook_user_name
				FROM posts p, facebook_users f
				WHERE (p.facebook_user_id = f.facebook_user_id)
				GROUP BY p.post_id ORDER BY p.updated_time DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	
	public function getLatestPost($page_id, $limit=5) {
		$select = "SELECT p.*, f.fan_first_name, f.fan_last_name 
				FROM posts p, fans f 
				WHERE (p.fanpage_id = '". $page_id ." ') AND (p.facebook_user_id = f.facebook_user_id)
				GROUP BY p.post_id ORDER BY p.updated_time DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}

	


}

