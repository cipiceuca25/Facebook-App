<?php
class Model_DbTable_FancrankActivities extends Fancrank_Db_Table
{

	protected $_name = 'fancrank_activities';

	protected $_primary = array('id');
	
	public function findAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->fetchAll($where, $order, $count, $offset);
	}
	
	public function findRow($key)
	{
		return $this->find($key)->current();
	}
	
}