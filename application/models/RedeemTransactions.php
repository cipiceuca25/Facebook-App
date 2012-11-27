<?php

class Model_RedeemTransactions extends Model_DbTable_RedeemTransactions
{
	public function getPendingOrdersListByFanpageId($fanpageId) {
		return $this->findAll("fanpage_id = $fanpageId AND status = 1");
	}
	
	public function getPendingOrdersCountByFanpageId($fanpageId) {
		return $this->findAll("fanpage_id = $fanpageId AND status = 1")->count();
	}
	
	public function getRedeemHistory($fanpageId) {
		return $this->findAll("fanpage_id = $fanpageId AND status != 1");
	}
}

