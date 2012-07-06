<?php
class Model_DbTable_UsersColorChoice extends Zend_Db_Table
{
	
	protected $_color = 'color_choice';
	
	protected $_primary = array('user_id');
	
	public function findAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->fetchAll($where, $order, $count, $offset);
	}
	
	public function findRow($key)
	{
		return $this->find($key)->current();
	}
}