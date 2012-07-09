<?php

class Model_DbTable_Subscribes extends Fancrank_Db_Table
{

    protected $_name = 'subscribes';

    protected $_primary = array(
    		'facebook_user_id',
    		'facebook_user_id_subscribe_to',
    		'subscribe_ref_id'
    );   

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
    	return $this->fetchAll($where, $order, $count, $offset);
    }
    
    public function findRow($key)
    {
    	return $this->find($key)->current();
    }
    
    public function findById($facebook_user_id, $subscribe_to, $subscribe_ref_id) {
    
    	return $this->find($facebook_user_id, $subscribe_to, $subscribe_ref_id)->current();
    }
    
}

