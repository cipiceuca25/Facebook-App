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
				'post_id'				=>	$post['post_id'],
				'facebook_user_id'		=>	$post['fromid'],
				'fanpage_id'			=>	$fanpage_id,
				'post_user_cateogry'	=>	$post['post_user_cateogry'],
				'post_message'			=>	$post['post_message'],
				'post_privacy_descr'	=>	$post['post_privacy_descr'],
				'post_privacy_value'	=>	$post['post_privacy_value'],
				'post_type'				=>	$post['post_type'],
				'created_time'			=>	$post['created_time'],
				'updated_time' 			=>	$post['updated_time'],
				'post_application_name'	=>	$post['post_application_name'],
				'post_application_id'	=>	$post['post_application_id'],
				'comments_count'		=>	$post['comments_count'],
				'likes_count'			=>	$post['likes_count']
		);
	
		
		$insert = $this->getAdapter()->insert(array('posts' => 'posts'), $data);
	
	}
	
	public function isDataValid($data) {
		if(empty($data)) {
			return false;
		}
		
		$valid = true;
		
		$validator = new Zend_Validate_Sitemap_Lastmod();
		
		if(!empty($data['created_time'])) {
			$valid = $validator->isValid($data['created_time']);
		}
		
		if(!empty($data['updated_time'])) {
			$valid = $validator->isValid($data['created_time']);
		}
		
		return $valid && $this->isIdFieldsValid($data);
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

