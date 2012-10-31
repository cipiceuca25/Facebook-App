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
	
	
	public function findPost($post_id){

		$query = $this->select()
		->from($this)
		->where('post_id = ?', $post_id);
		//Zend_Debug::dump($query);
		return $this->fetchAll($query)->current();
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
	public function addLikeToPostReturn($id) {
		$found = $this->findPost($id);
	
	
		//Zend_Debug::dump($found);
		if (!empty ( $found )) {
			$found->post_likes_count ++;
			$found->save ();
		}
		return $found;
	}
	
	public function subtractLikeToPostReturn($id) {
		$found = $this->findPost($id);
	
	
		if (!empty ( $found )) {
			if ($found->post_likes_count >0){
				$found->post_likes_count --;
			}
			$found->save ();
		}
		return $found;
	}
	
	public function addLikeToPost($id) {
		$found = $this->findPost($id);
		
	
		//Zend_Debug::dump($found);
		if (!empty ( $found )) {
			$found->post_likes_count ++;
			$found->save ();
		}
	}
	
	public function subtractLikeToPost($id) {
		$found = $this->findPost($id);
	
	
		if (!empty ( $found )) {
			if ($found->post_likes_count >0){
				$found->post_likes_count --;
			}
			$found->save ();
		}
	}
	
	public function addCommentToPost($id) {
		$found = $this->findPost($id);
		
		$dateObject = new Zend_Date();
	
		if (!empty ( $found )) {
			$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->post_comments_count ++;
			$found->save ();
		}
	}
	public function addCommentToPostReturn($id) {
		$found = $this->findPost($id);
	
		$dateObject = new Zend_Date();
	
		if (!empty ( $found )) {
			$found->updated_time = $dateObject->toString ( 'yyyy-MM-dd HH:mm:ss' );
			$found->post_comments_count ++;
			$found->save ();
			return $found;
		}
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
				'likes_count'			=>	$post['likes_count'],
				'status_type'			=>	$post['status_type']
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

	
	public function getMyFeedPost($fanpage_id, $user_id, $limit, $myfeedoffset){
		
		$date = new Zend_Date();
		$today = new Zend_Date();
		$today->now();
		//echo $today->toString('yyyy-MM-dd HH:mm:ss');
		$date->sub('7', Zend_Date::DAY);
		
		$select="Select distinct h.* from(
				
				/*JUST POSTS FROM SELF, ADMIN, FOLLOW*/
			
				select p.post_id ,p.created_time
				from posts p
				where (p.facebook_user_id =$fanpage_id || p.facebook_user_id = $user_id) && p.fanpage_id = $fanpage_id
				
				union
			
				SELECT p.post_id ,p.created_time
				FROM subscribes s , posts p
				where s.facebook_user_id =  $user_id && s.fanpage_id = $fanpage_id && s.follow_enable = 1
				&& p.facebook_user_id = s.facebook_user_id_subscribe_to && s.fanpage_id = p.fanpage_id
				
				union
			
				/*posts with comments that are by admin, self or follow*/
				
				Select p.post_id ,p.created_time
				from posts p, comments c 
				where (c.facebook_user_id = $fanpage_id || c.facebook_user_id =  $user_id ) && c.comment_post_id = p.post_id && p.fanpage_id = $fanpage_id && c.fanpage_id = $fanpage_id
				
				union 
				SELECT p.post_id ,p.created_time
				FROM subscribes s , posts p, comments c
				where s.facebook_user_id =  $user_id && s.fanpage_id = $fanpage_id && s.follow_enable = 1
				&& c.facebook_user_id = s.facebook_user_id_subscribe_to && s.fanpage_id = p.fanpage_id && p.fanpage_id = c.fanpage_id && c.fanpage_id = $fanpage_id && c.comment_post_id = p.post_id
			
				union
				/*posts with likes that are by admin self or follow*/
				
				select p.post_id ,p.created_time
				from likes l, posts p
				where (l.facebook_user_id =$user_id || l.facebook_user_id = $fanpage_id) && p.post_id = l.post_id && l.fanpage_id = $fanpage_id
			
				union
				
				select p.post_id ,p.created_time
				from likes l, posts p, subscribes s
				where s.facebook_user_id = $user_id and l.facebook_user_id = s.facebook_user_id_subscribe_to and s.follow_enable = 1 and p.post_id = l.post_id
					and s.fanpage_id = l.fanpage_id and l.fanpage_id = p.fanpage_id and l.fanpage_id =$fanpage_id
				
				union
				/*post with comments that have likes by admin self or follow*/
			
				select p.post_id ,p.created_time
				from likes l , posts p, comments c
				where (l.facebook_user_id = $user_id || l.facebook_user_id = $fanpage_id) and l.post_id = c.comment_id and c.comment_post_id = p.post_id
					and l.fanpage_id = $fanpage_id and l.fanpage_id = c.fanpage_id and c.fanpage_id = p.fanpage_id 
			
				union
			
				select p.post_id ,p.created_time
				from likes l , posts p, comments c, subscribes s
				where l.facebook_user_id = s.facebook_user_id_subscribe_to and s.facebook_user_id = $user_id  and l.post_id = c.comment_id and c.comment_post_id = p.post_id
					and l.fanpage_id = $fanpage_id and l.fanpage_id = c.fanpage_id and c.fanpage_id = p.fanpage_id and s.fanpage_id = p.fanpage_id
			
				union
				
				select post_id ,created_time from
				(SELECT DISTINCT p.post_id ,p.created_time
								FROM posts p 
								WHERE  p.fanpage_id = $fanpage_id and p.facebook_user_id != $fanpage_id
								AND p.created_time < '".$today->toString('yyyy-MM-dd HH:mm:ss')."'	
								AND p.created_time > '".$date->toString('yyyy-MM-dd HH:mm:ss')."'
								ORDER BY (post_comments_count + post_likes_count)*1000000/TIMESTAMPDIFF(SECOND, created_time, NOW())  DESC
								limit 5) as q
				
				)as h ";
			
			if($myfeedoffset > 0)
				$select = $select . "where h.created_time < '$myfeedoffset' ";
		
			$select = $select ."order by h.created_time DESC";
						
		if($limit !== false)
			$select = $select . " LIMIT $limit";
	
		
		
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getUserActivity ($fanpage_id, $user_id, $limit){
		$select="select a.*, f.fan_name from 
				(
				select p.*, p.created_time as user_created_time, 'post' as act_type from posts p 
													where p.facebook_user_id = '".$user_id."'
													and p.fanpage_id= '".$fanpage_id."'
				union 
				SELECT p.*, c.created_time as user_created_time, 'comment' as act_type FROM comments c, posts p
													where c.facebook_user_id = '".$user_id."'
													and c.fanpage_id = '".$fanpage_id."'
													and p.post_id = c.comment_post_id
													and p.fanpage_id = c.fanpage_id
				union 
				SELECT p.*, l.created_time as user_created_time, 'like-post' as act_type FROM  posts p, likes l 
													where l.facebook_user_id = '".$user_id."'
													and l.likes = 1
													and l.fanpage_id = '".$fanpage_id."'
													and p.post_id = l.post_id
													and p.fanpage_id = l.fanpage_id
				union 
				SELECT p.*, l.created_time as user_created_time, 'unlike-post' as act_type FROM  posts p, likes l 
													where l.facebook_user_id = '".$user_id."'
													and l.likes = 0
													and l.fanpage_id = '".$fanpage_id."'
													and p.post_id = l.post_id
													and p.fanpage_id = l.fanpage_id									
				union
				SELECT  p.*, l.created_time as user_created_time, 'like-comment' as act_type FROM comments c, posts p, likes l 
													where l.facebook_user_id = '".$user_id."'
													and l.fanpage_id = '".$fanpage_id."'
													and l.likes = 1
													and p.post_id = c.comment_post_id
													and p.fanpage_id = c.fanpage_id
													and l.post_id = c.comment_id 
				union
				SELECT  p.*, l.created_time as user_created_time, 'unlike-comment' as act_type FROM comments c, posts p, likes l 
													where l.facebook_user_id = '".$user_id."'
													and l.fanpage_id = '".$fanpage_id."'
													and l.likes = 0
													and p.post_id = c.comment_post_id
													and p.fanpage_id = c.fanpage_id
													and l.post_id = c.comment_id 
				) as a, fans f 
				where f.facebook_user_id = a.facebook_user_id and f.fanpage_id = a.fanpage_id
				order by a.user_created_time DESC ";

		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
		
	
	}
	
	public function getUniqueComment($fanpage_id , $facebook_user_id, $post_id){
		
		$select = "";
		
		return $this->getAdapter()->fetchAll($select);
	
	}

	public function isVirginity($postId) {
		return $this->getUniqueInteractionCount($postId) > 0 ? false : true; 
	}
	
	public function getUniqueInteractionCount($postId, $filterSelf = true) {
		$select = "
				select count(*) as count
				from
				(select c.facebook_user_id from comments c left join posts p on (c.comment_post_id = p.post_id) where c.comment_post_id = '" .$postId ."' and p.facebook_user_id != c.facebook_user_id group by c.facebook_user_id
				union
				select l.facebook_user_id from likes l left join posts p on (l.post_id = p.post_id) where p.post_id = '" .$postId ."' and p.facebook_user_id != l.facebook_user_id) a
				";
		
		if ($filterSelf === false) {
			$select = "
					select count(*) as count
					from
					(select c.facebook_user_id from comments c left join posts p on (c.comment_post_id = p.post_id) where c.comment_post_id = '" .$postId ."' group by c.facebook_user_id
					union
					select l.facebook_user_id from likes l left join posts p on (l.post_id = p.post_id) where p.post_id = '" .$postId ."') a
					";
		}
		
		$result = $this->getAdapter()->fetchAll($select);
		
		if(!empty($result[0]['count'])) {
			return $result[0]['count'];
		}
		
		return 0;
	}
	
	public function isUniqueInPost($postId, $facebookUserId) {
		$select = "
				select count(*) as count
				from
				(select c.facebook_user_id from comments c left join posts p on (c.comment_post_id = p.post_id) where c.comment_post_id = '" .$postId ."' and p.facebook_user_id != c.facebook_user_id and c.facebook_user_id = $facebookUserId group by c.facebook_user_id
				union
				select l.facebook_user_id from likes l left join posts p on (l.post_id = p.post_id) where p.post_id = '" .$postId ."' and p.facebook_user_id != l.facebook_user_id and l.facebook_user_id = $facebookUserId) a
				";
		
		$result = $this->getAdapter()->fetchAll($select);

		return isset($result[0]['count']) && $result[0]['count'] == 0 ? true : false;
	}
	
}

