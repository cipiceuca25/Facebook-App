<?php

class Model_Likes extends Model_DbTable_Likes
{
    public function insertNewLikes($fanpage_id, $post_id, $facebook_user_id, $post_type)
	{
           $found = $this->find($fanpage_id, $post_id, $facebook_user_id)->current();
               if (empty($found)) {
                       $data = array( 'fanpage_id'=> $fanpage_id, 'post_id'=>$post_id, 'facebook_user_id'=>$facebook_user_id, 'post_type'=>$post_type, 'likes'=>1);

                       $this->insert($data);
               }else {
              	 	$found->likes = 1;
               		$found->save();
               }
       }

    public function unlike($fanpage_id, $post_id, $facebook_user_id, $post_type)
       {
       	$found = $this->find($fanpage_id, $post_id, $facebook_user_id)->current();
       	
       	if (!empty($found)) {

       		$found->likes = 0;
       		$found->save();
       	}
       }
       
       
    public function getLikes($fanpage_id, $post_id, $facebook_user_id){

    		$found = $this->find($fanpage_id, $post_id, $facebook_user_id)->current();
    		//echo $found['likes'];
               if ( empty($found) || $found['likes'] == 0) {
                      return 0;
               }else {
                      return 1;
               }
    
    }
	
	public function isDataValid($data) {
		if(empty($data)) {
			return false;
		}
		//echo 'data not empty';
		foreach ($data as $key => $field) {
			if(empty($field)) {
				return false;
			}
			if($key === 'post_type' && ! in_array($field, array('status', 'photo', 'comment', 'post', 'album', 'video'))) {
				return false;
			}
			
		}
		//echo 'data past';
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
		//echo 'key past';
		if($data['facebook_user_id'] === $data['fanpage_id']) {
			return false;
		}
		
		return true;
	}
}

