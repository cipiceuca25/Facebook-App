<?php

class Model_DbTable_FanpageBadges extends Fancrank_Db_Table
{

    protected $_name = 'fanpage_badges';

    protected $_primary = array(
	        'fanpage_id',
	        'badge_id'
        );

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
    	return $this->fetchAll($where, $order, $count, $offset);
    }
    
    public function findRow($key)
    {
    	return $this->find($key)->current();
    }
    
    public function getFanpageBadges($fanpageId, $param=null) {
    	$select = null;
    	if(empty($param)) {
    		$select = "Select b.id, b.name, b.description, b.weight, b.picture from badges b inner join fanpage_badges f on (b.id = f.badge_id) where f.fanpage_id =  $fanpageId";
    	}else {
    		$paramString = '';
    		foreach ($param as $k=>$v) {
    			$paramString .= 'b.' .$v .',';
    		}
    		$paramString= rtrim($paramString, ',');
    		$select = "Select $paramString from badges b inner join fanpage_badges f on (b.id = f.badge_id) where f.fanpage_id =  $fanpageId";
    	}
    	return $this->getAdapter()->fetchAll($select);
    }
}

