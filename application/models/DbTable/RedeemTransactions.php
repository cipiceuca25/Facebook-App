<?php

class Model_DbTable_RedeemTransactions extends Fancrank_Db_Table
{

    protected $_name = 'redeem_transactions';

    protected $_primary = 'id';

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
    	return $this->fetchAll($where, $order, $count, $offset);
    }
    
    public function findRow($key)
    {
    	return $this->find($key)->current();
    }
    
}

