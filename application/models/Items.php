<?php

class Model_Items extends Model_DbTable_Items
{
	public function getFanpageItems($fanpageId) {
		$query = $this->select()
				->where('fanpage_id = ?', $fanpageId);
		return $this->fetchAll($query);
	}

}

