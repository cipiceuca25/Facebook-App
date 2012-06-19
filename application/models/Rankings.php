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
                        SELECT posts_count.facebook_user_id, fans.fan_name, COUNT(fans.fan_name) AS number_of_posts
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
								UNION ALL
                                                        		SELECT facebook_user_id
                                                        		FROM likes
                                                        		WHERE facebook_user_id != '". $page_id ."'
                                                        		AND fanpage_id = '". $page_id ."'
                                ) AS posts_count
	
                        INNER JOIN fans ON (fans.facebook_user_id = posts_count.facebook_user_id)
	
                        GROUP BY fans.fan_name
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
			SELECT posts_count.facebook_user_id, fans.fan_name, COUNT(fans.fan_name) AS number_of_posts
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
			ORDER BY number_of_likes DESC";
	
		if($limit !== false)
			$select = $select . " LIMIT $limit";
			
	
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getMostPopular($page_id, $limit = 5)
	{
	
		//$relevant_period = new Zend_Date(time() - 15552000);
		//$relevant_period = $relevant_period->toString(Zend_Date::ISO_8601);
	
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
						WHERE facebook_user_id != '". $page_id ."'
						AND fanpage_id = '". $page_id ."'
						GROUP BY facebook_user_id
	
						UNION ALL
	
						SELECT facebook_user_id, SUM(comment_likes_count) AS likes_count
						FROM comments
						WHERE facebook_user_id != '". $page_id ."'
						AND fanpage_id = '". $page_id ."'
						GROUP BY facebook_user_id
					) AS total_likes
	
					GROUP BY facebook_user_id
	
					UNION ALL
	
					SELECT facebook_user_id, SUM(post_comments_count) AS count
					FROM posts
					WHERE facebook_user_id != '". $page_id ."'
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

