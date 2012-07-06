<?php

class Model_Rankings extends Model_DbTable_Rankings
{

	public function getRanking($page_id, $type, $user_id = false, $limit = 5)
	{
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false);
		$select->join(array('fans'), 'fans.facebook_user_id = rankings.facebook_user_id');
		$select->where($this->quoteInto('rankings.fanpage_id = ?', $page_id));
		$select->where($this->quoteInto('type = ?', $type));
		$select->order('rank ASC');
		
		/*
		if($user_id) 
			$select->where($this->quoteInto('rankings.facebook_user_id = ?', $user_id));
		*/
		if($limit)
			$select->limit($limit);

		return $this->fetchAll($select);
	}
	
	/*
	 * This following method will return a single user ranking object
	 * 
	 * @param $page_id the first argument
	 * @param $type  the second argument
	 * @param $user_id the third argument
	 * @return ranking object or void 
	 */
	public function getUserRanking($page_id, $type, $user_id) {
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false);
		$select->join(array('fans'), 'fans.facebook_user_id = rankings.facebook_user_id')
				->where($this->quoteInto('rankings.facebook_user_id = ?', $user_id))
				->where($this->quoteInto('type = ?', $type));
		
		return $this->fetchRow($select);
	}

	public function getTopFans($page_id, $limit = 5)
	{
		$select = "
					SELECT fans.facebook_user_id, fans.fan_first_name, COUNT(fans.facebook_user_id) AS number_of_posts
					FROM
                    (SELECT l.facebook_user_id FROM posts p INNER JOIN likes l ON(p.post_id = l.post_id) WHERE p.fanpage_id = '". $page_id ."' AND p.facebook_user_id = p.fanpage_id
					UNION ALL
                    SELECT l.facebook_user_id FROM comments c INNER JOIN likes l ON (c.comment_id = l.post_id) WHERE l.fanpage_id = '". $page_id ."' AND c.facebook_user_id = c.fanpage_id
                    UNION ALL
                    SELECT l.facebook_user_id FROM likes l WHERE l.post_type = 'photo' AND l.fanpage_id = '". $page_id ."'		
                   	) AS topfans
					INNER JOIN fans ON (fans.facebook_user_id = topfans.facebook_user_id)
					GROUP BY fans.facebook_user_id
					HAVING fans.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $page_id ."')                    		
					ORDER BY number_of_posts DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getTopTalker($page_id, $limit = 5)
	{
		//$relevant_period = new Zend_Date(time() - 15552000);
		//$relevant_period = $relevant_period->toString(Zend_Date::ISO_8601);
	
		$select = "
			SELECT posts_count.facebook_user_id, fans.fan_first_name, COUNT(fans.facebook_user_id) AS number_of_posts
			FROM
			(SELECT facebook_user_id
				FROM posts
				WHERE facebook_user_id != '". $page_id ."'
				AND fanpage_id = '". $page_id ."'
			UNION ALL
			SELECT facebook_user_id
				FROM comments
				WHERE facebook_user_id != '". $page_id ."'
				AND fanpage_id = '". $page_id ."'
			) AS posts_count
			INNER JOIN fans ON (fans.facebook_user_id = posts_count.facebook_user_id)
			GROUP BY fans.facebook_user_id
			HAVING fans.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $page_id ."')			
			ORDER BY number_of_posts DESC";
			
		if($limit !== false)
			$select = $select . " LIMIT $limit";
			
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getTopClicker($page_id, $limit = 5)
	{
		$select = "
			SELECT likes_count.facebook_user_id, fans.fan_first_name, COUNT(fans.facebook_user_id) AS number_of_likes
			FROM (
				SELECT facebook_user_id FROM likes
				WHERE facebook_user_id != '". $page_id ."'
				AND fanpage_id = '". $page_id ."'
			) AS likes_count
			INNER JOIN fans ON (fans.facebook_user_id = likes_count.facebook_user_id)
			GROUP BY fans.facebook_user_id
			HAVING fans.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $page_id ."')						
			ORDER BY number_of_likes DESC";
	
		if($limit !== false)
			$select = $select . " LIMIT $limit";
			
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getMostPopular($page_id, $limit = 5)
	{
		$select = "SELECT fans.facebook_user_id, fans.fan_first_name, sum(favorite.num) AS count
					FROM
					(
						SELECT p.facebook_user_id, sum(p.post_comments_count) AS num FROM posts p WHERE p.fanpage_id = '". $page_id ."' AND p.facebook_user_id != p.fanpage_id GROUP BY p.facebook_user_id
						UNION ALL
						SELECT p.facebook_user_id, count(*) AS num FROM likes l LEFT JOIN posts p ON(l.post_id = p.post_id) WHERE p.facebook_user_id != l.fanpage_id AND l.fanpage_id = '". $page_id ."' GROUP BY p.post_id, p.facebook_user_id
						UNION ALL
						SELECT c.facebook_user_id, count(*) AS num FROM likes l LEFT JOIN comments c ON(l.post_id = c.comment_id) WHERE c.facebook_user_id != l.fanpage_id AND l.fanpage_id = '". $page_id ."' AND l.post_type ='comment' GROUP BY c.comment_id, c.facebook_user_id
					) AS favorite
					INNER JOIN fans ON (fans.facebook_user_id = favorite.facebook_user_id)
					GROUP BY favorite.facebook_user_id
					HAVING fans.facebook_user_id NOT IN(SELECT facebook_user_id FROM fanpage_admins WHERE fanpage_id = '". $page_id ."')
					ORDER BY count DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
	
		return $this->getAdapter()->fetchAll($select);
	
	}
	
	public function getTopPosts($page_id, $limit=5) {
		$select = "SELECT p.*, count(*) AS count 
				FROM likes l
				 INNER JOIN 
				posts p ON(l.post_id = p.post_id) 

				WHERE p.facebook_user_id != l.fanpage_id AND l.fanpage_id = '". $page_id ."' GROUP BY p.post_id ORDER BY count DESC";
		
		if($limit !== false)
			$select = $select . " LIMIT $limit";
		
		return $this->getAdapter()->fetchAll($select);
	}
	
}

