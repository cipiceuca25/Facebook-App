<?php
class Model_DbTable_FanpageSetting extends Fancrank_Db_Table
{
	
	protected $_name = 'fanpage_setting';
	
	protected $_primary = array('fanpage_id');
	
	public function findAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->fetchAll($where, $order, $count, $offset);
	}
	
	public function findRow($key)
	{
		return $this->find($key)->current();
	}
}