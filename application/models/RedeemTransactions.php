<?php

class Model_RedeemTransactions extends Model_DbTable_RedeemTransactions
{
	public function getPendingOrdersListByFanpageId($fanpageId) {
		
	}
	
	public function getPendingOrdersCountByFanpageId($fanpageId) {
		return $this->findAll("fanpage_id = $fanpageId AND status = 1")->count();
	}
}

