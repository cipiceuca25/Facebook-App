<?php

class Model_RedeemTransactions extends Model_DbTable_RedeemTransactions
{
	public function getPendingOrdersListByFanpageId($fanpageId, $limit=100) {
		$query = $this->getDefaultAdapter()->select()
				->from(array('r'=>'redeem_transactions'), array('r.*'))
				->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name','i.picture'))
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
			->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name','i.picture'))
			->join(array('f'=>'facebook_users'), 'f.facebook_user_id = r.facebook_user_id', array('f.facebook_user_name'))
			->where('r.fanpage_id = ?', $fanpageId)
			->where('r.status != 1 AND r.status != 2')
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
	
	public function getRedeemDetailByBadgeIdAndUser($redeemId, $facebook_user_id) {
		$query = $this->getDefaultAdapter()->select()
		->from(array('r'=>'redeem_transactions'), array('r.*'))
		->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('i.name', 'i.description', 'i.picture', 'i.points', 'i.enable'))
		->where('r.badge_event_id = ?', $redeemId)
		->where('r.facebook_user_id = ?', $facebook_user_id);
	
		return $this->getDefaultAdapter()->fetchRow($query);
	}
	
	
	public function getShippingList($fanpageId, $limit=100) {
		$query = $this->getDefaultAdapter()->select()
			->from(array('r'=>'redeem_transactions'), array('r.id', 'r.item_id'))
			->join(array('i'=>'items'), 'r.item_id = i.id AND r.fanpage_id = i.fanpage_id', array('item_name' =>'i.name'))
			->join(array('s'=>'shipping_info'), 'r.shipping_info_id = s.id', 
					array('shipping_info_id'=>'s.id', 's.facebook_user_id','s.name', 's.email','s.address', 's.city', 's.region', 's.region', 's.country', 's.postcode'))
			->where('r.fanpage_id = ?', $fanpageId)
			->where('r.status = 2')
			->limit($limit);

		return $this->getDefaultAdapter()->fetchAll($query);
	}
}

