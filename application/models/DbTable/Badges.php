<?php

class Model_DbTable_Badges extends Fancrank_Db_Table
{

    protected $_name = 'badges';

    protected $_primary = 'id';

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
    	return $this->fetchAll($where, $order, $count, $offset);
    }
    
    public function findRow($key)
    {
    	return $this->find($key)->current();
    }
    
    public function findByBadgeName($badgeName) {
    	$where = $this->quoteInto('name = ?', $badgeName);
    	return $this->findAll($where);
    }
    
    public function insertBadgeByObjectType($objectType) {
    	
    }
}

