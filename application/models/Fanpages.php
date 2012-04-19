<?php

class Model_Fanpages extends Model_DbTable_Fanpages
{
	public function getLatestTimestamp($fanpage_id)
	{
		$fanpage = $this->findRow($fanpage_id);

		return $fanpage->lastest_timestamp;
	}
}

