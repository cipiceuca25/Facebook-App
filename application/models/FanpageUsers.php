<?php

class Model_FanpageUsers extends Model_DbTable_FanpageUsers
{
	public function fanpageUserSummary($fanpage) 
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fp' => 'fanages_users'));
		$select->join(array('fbu' => 'facebook_useres'), 'fp.facebook_user_id = fbu.facebook_user_id');
		$select->where($this->getAdapter()->quoteInto('fanpage_id', $fanpage));
		
		return $this->find($select);
	}
}

