<?php

class Model_TopFans extends Model_DbTable_TopFans
{
	public function getTopFans($page_id, $limit = 5)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fans' => 'fans'),
			array(
				'fans.facebook_user_id', 
				'fans.fan_name',
				'likes.facebook_user_id',
				'num_likes' => new Zend_Db_Expr('COUNT(likes.facebook_user_id)') 
			)
		);
		$select->join(array('likes' => 'likes'), 'fans.facebook_user_id = likes.facebook_user_id');
		//$select->where('likes.facebook_user_id IN (SELECT facebook_user_id FROM fans)');
		$select->where($this->getAdapter()->quoteInto('fans.fanpage_id = ?', $page_id));
		$select->group('likes.facebook_user_id');
		$select->order('num_likes');

		if($limit !== false)
			$select->limit($limit);

		//die(print_r($select->__toString()));
		
		return $this->getAdapter()->fetchAll($select);
	}

	public function getTopTalker($page_id, $limit = 5)
	{
		$relevant_period = new Zend_Date(time() - 15552000);
		$relevant_period = $relevant_period->toString(Zend_Date::ISO_8601);

		$select = "
			SELECT posts_count.facebook_user_id, fans.fan_name, COUNT(fans.fan_name) AS number_of_posts 
				FROM 
				(SELECT facebook_user_id 
					FROM posts
					WHERE created_time > '".$relevant_period."' 
					AND facebook_user_id != '". $page_id ."'
					AND fanpage_id = '". $page_id ."'
						UNION ALL 
							SELECT facebook_user_id 
							FROM comments
							WHERE created_time > '".$relevant_period."' 
							AND facebook_user_id != '". $page_id ."'
							AND fanpage_id = '". $page_id ."'
				) AS posts_count 
			
			INNER JOIN fans ON (fans.facebook_user_id = posts_count.facebook_user_id)		
	
			GROUP BY fans.fan_name 
			ORDER BY number_of_posts DESC"; 
			
			if($limit !== false)
				$select = $select . " LIMIT $limit";
			

		return $this->getAdapter()->fetchAll($select);
	}

	public function getTopClicker($page_id, $limit = 10)
	{
		$select = "
			SELECT likes_count.facebook_user_id, fans.fan_name, COUNT(fans.fan_name) AS number_of_likes 
				FROM (
					SELECT facebook_user_id FROM likes
					WHERE facebook_user_id != '". $page_id ."'
					AND fanpage_id = '". $page_id ."' 
				) AS likes_count
				
			INNER JOIN fans ON (fans.facebook_user_id = likes_count.facebook_user_id)	

			GROUP BY fans.fan_name 
			ORDER BY number_of_likes";

			if($limit !== false)
				$select = $select . " DESC LIMIT $limit";
			

		return $this->getAdapter()->fetchAll($select);
	}

	public function getMostPopular($page_id, $limit = 5)
	{

		$relevant_period = new Zend_Date(time() - 15552000);
		$relevant_period = $relevant_period->toString(Zend_Date::ISO_8601);

//CONVERT this to zend notation so conditionals can be done simply instead of having to create duplicates
		$select = "
			SELECT total_count.facebook_user_id, fans.fan_name, SUM(count) AS count 
				FROM
				(
					SELECT facebook_user_id, SUM(likes_count) AS count 
					FROM 
					(
						SELECT facebook_user_id, SUM(post_likes_count) AS likes_count 
						FROM posts
						WHERE created_time > '".$relevant_period."'  
						AND facebook_user_id != '". $page_id ."'
						AND fanpage_id = '". $page_id ."'
						GROUP BY facebook_user_id 

						UNION ALL 

						SELECT facebook_user_id, SUM(comment_likes_count) AS likes_count 
						FROM comments 
						WHERE created_time > '".$relevant_period."' 
						AND facebook_user_id != '". $page_id ."'
						AND fanpage_id = '". $page_id ."'
						GROUP BY facebook_user_id
					) AS total_likes

					GROUP BY facebook_user_id 

					UNION ALL

					SELECT facebook_user_id, SUM(post_comments_count) AS count 
					FROM posts 
					WHERE created_time > '".$relevant_period."'  
					AND facebook_user_id != '". $page_id ."'
					AND fanpage_id = '". $page_id ."'
					GROUP BY facebook_user_id
				) AS total_count
				
				INNER JOIN fans ON (fans.facebook_user_id = total_count.facebook_user_id)

				GROUP BY facebook_user_id 
				ORDER BY count DESC";

		if($limit !== false)
			$select = $select . " LIMIT $limit";

		return $this->getAdapter()->fetchAll($select);

	}
}