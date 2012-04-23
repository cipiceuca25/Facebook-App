<?php

class Model_Fanpages extends Model_DbTable_Fanpages
{
	public function getFeed($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);
		
		return $this->facebookRequest(null, 'feed', $fanpage->access_token);	
	}
	
	public function getAlbums($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);
		
		return $this->facebookRequest(null, 'albums', $fanpage->access_token);
	}
	
	public function getInsights($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);

		return $this->facebookRequest(null, 'insights', $fanpage->access_token);
	}
	
	public function getFans($fanpage_id)
	{
		$fanpage = $this-findRow($fanpage_id);
		
		//cycle through all posts, comments, likes to retrieve list of fans
	}

	public function getActiveFanpagesByUserId($user_id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));

		return $this->getAdapter()->fetchAll($select);
	}

	public function getActiveFanpageByFanpageId($fanpage_id, $user_id)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));
		$select->where('fanpages.active = TRUE');

		return $this->getAdapter()->fetchAll($select);
	}

	public function getFanpageByFanpageIdAndUserId($fanpage_id, $user_id) 
	{
		$select = $this->getAdapter()->select();
		$select->from(array('fanpages' => 'fanpages'));
		$select->join(array('admins' => 'fanpage_admins'), 'fanpages.fanpage_id = admins.fanpage_id');
		$select->where($this->getAdapter()->quoteInto('admins.facebook_user_id = ?', $user_id));
		$select->where($this->getAdapter()->quoteInto('fanpages.fanpage_id = ?', $fanpage_id));

		return $this->getAdapter()->fetchAll($select);
	}
}

