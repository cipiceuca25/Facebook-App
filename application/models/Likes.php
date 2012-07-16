<?php

class Model_Likes extends Model_DbTable_Likes
{

	
	//this isnt even used.
	public function insertLikes($fanpage_id, $post_id, $facebook_user_id, $post_type)
	{
	
		$data = array( 'fanpage_id'=> $fanpage_id, 'post_id'=>$post_id, 'post_type'=>$post_id
	
		);
		$insert = $this->getAdapter()->insert(array('likes' => 'likes'), $data);
	
	}
	
	public function isDataValid($data) {
		if(empty($data)) {
			return false;
		}
		
		foreach ($data as $key => $field) {
			if(empty($field)) {
				return false;
			}
			if($key === 'post_type' && ! in_array($field, array('photo', 'comment', 'post', 'album', 'video'))) {
				return false;
			}
		}
		
		return $this->isIdFieldsValid($data);
	}
	
	private function isIdFieldsValid($data) {
		$idValidator = new Zend_Validate_Digits();
		$ids = array('facebook_user_id', 'fanpage_id');
		foreach ($ids as $key => $id) {
			if (! $idValidator->isValid($data[$id])) {
				return false;
			}
		}
	
		if($data['facebook_user_id'] === $data['fanpage_id']) {
			return false;
		}
	
		return true;
	}
}

