<?php

class Model_Likes extends Model_DbTable_Likes
{

	public function insertLikes($fanpage_id, $post_id, $likes)
	{
	
		$data = array(
	
		);
	
		$insert = $this->getAdapter()->insert(array('likes' => 'likes'), $data);
	
	}
}

