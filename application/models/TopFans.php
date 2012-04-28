<?php

class Model_TopFans extends Model_DbTable_TopFans
{
	public function getTopFans($page_id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fans' => 'fans'));
		$select->where($this->getAdapter()->quoteInto('posts.post_id = ?', $id) AND $this->getAdapter()->quoteInto('posts.updated_time < ?', $updated_time));
		$getTopFans = 	mysql_query(
			"SELECT
				Fans.fan_id, Fans.name, Likes.person_id, count(Likes.person_id) as num_likes
			FROM
 				Fans
 				INNER JOIN Likes ON (Fans.fan_id = Likes.person_id)
			WHERE
				Likes.person_id IN (SELECT fan_id FROM Fans)
			GROUP BY Likes.person_id
			ORDER BY num_likes DESC
			LIMIT 10"
		);
	}
}

