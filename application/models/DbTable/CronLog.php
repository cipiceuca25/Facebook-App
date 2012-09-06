<?php

class Model_DbTable_CronLog extends Fancrank_Db_Table
{

    protected $_name = 'cron_log';

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

