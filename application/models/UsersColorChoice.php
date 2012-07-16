<?php

class Model_UsersColorChoice extends Model_DbTable_UsersColorChoice
{


public function getColorChoice($fanpage_id)
{
	//$select = "select users_color_choice.color_choice from users_color_choice where user_id ='".$user_id."'";
	
	if ($this -> find($fanpage_id)->current() ==false){
		
		$data = array('fanpage_id' => $fanpage_id, 'color_choice' => 3);
		
		$insert = $this->getAdapter()->insert(array('users_color_choice' => 'users_color_choice'), $data);
	}
	
	return $this -> find($fanpage_id)->current();
	//return $this->getAdapter()->fetchAll($select);
}


public function change($fanpage_id, $color){
	//$user = new Model_UsersColorChoice();
	//$select = $user->find($user_id);
	
	
	$data = array('color_choice' => $color);
		//if have user
	$where = $this ->getAdapter() ->quoteInto('fanpage_id =?', $fanpage_id);
		//else user not exist
		// $select = "insert into user_color_choice values".$user_id.",".$color.")";
	
	$this->update($data, $where);
	
}


}