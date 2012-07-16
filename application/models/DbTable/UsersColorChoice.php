<?php
class Model_DbTable_UsersColorChoice extends Fancrank_Db_Table
{
	
	protected $_name = 'users_color_choice';
	
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