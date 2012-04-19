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
}

