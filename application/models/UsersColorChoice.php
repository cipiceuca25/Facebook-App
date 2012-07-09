<?php

class Model_UsersColorChoice extends Model_DbTable_UsersColorChoice
{


public function getColorChoice($user_id)
{
	//$select = "select users_color_choice.color_choice from users_color_choice where user_id ='".$user_id."'";
	
	return $this -> find($user_id)->current();

	//return $this->getAdapter()->fetchAll($select);
}


public function change($user_id, $color){
	//$user = new Model_UsersColorChoice();
	//$select = $user->find($user_id);
	
	
	$data = array('color_choice' => $color);
		//if have user
	$where = $this ->getAdapter() ->quoteInto('user_id =?', $user_id);
		//else user not exist
		// $select = "insert into user_color_choice values".$user_id.",".$color.")";
	
	$this->update($data, $where);
	
}


}