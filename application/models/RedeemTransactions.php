<?php

class Model_RedeemTransactions extends Model_DbTable_RedeemTransactions
{
	public function getPendingOrdersListByFanpageId($fanpageId, $limit=100) {
		$query = $this->getDefaultAdapter()->select()
				->from(array('r'=>'redeem_transactions'), array('r.*'))
				->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name'))
				->join(array('f'=>'facebook_users'), 'f.facebook_user_id = r.facebook_user_id', array('f.facebook_user_name'))
				->where('r.fanpage_id = ?', $fanpageId)
				->where('r.status = 1')
				->limit($limit);
		return $this->getDefaultAdapter()->fetchAll($query);
	}
	
	public function getPendingOrdersCountByFanpageId($fanpageId) {
		return $this->findAll("fanpage_id = $fanpageId AND status = 1")->count();
	}
	
	public function getRedeemHistory($fanpageId, $limit=10000) {
		$query = $this->getDefaultAdapter()->select()
			->from(array('r'=>'redeem_transactions'), array('r.*'))
			->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name'))
			->join(array('f'=>'facebook_users'), 'f.facebook_user_id = r.facebook_user_id', array('f.facebook_user_name'))
			->where('r.fanpage_id = ?', $fanpageId)
			->where('r.status != 1')
			->limit($limit);
		return $this->getDefaultAdapter()->fetchAll($query);
	}
	
	public function getRedeemDetailById($redeemId) {
		$query = $this->getDefaultAdapter()->select()
			->from(array('r'=>'redeem_transactions'), array('r.*'))
			->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name', 'i.description', 'i.picture', 'i.points', 'i.enable'))
			->join(array('f'=>'fans'), 'f.facebook_user_id = r.facebook_user_id AND f.fanpage_id = r.fanpage_id', array('f.*'))
			->where('r.id = ?', $redeemId);
		return $this->getDefaultAdapter()->fetchRow($query);
	}
}

