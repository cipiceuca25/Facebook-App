<?php

class Model_DbTable_BannedUsers extends Application_Db_Table
{

    protected $_name = 'banned_users';

    protected $_primary = array('user_id');

    protected $_metadata = array(
        'user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'banned_users',
            'COLUMN_NAME' => 'user_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '32',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'timestamp' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'banned_users',
            'COLUMN_NAME' => 'timestamp',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            )
        );

    protected $_cols = array(
        'user_id',
        'timestamp'
        );

    protected $_rowClass = 'Model_DbTable_Row_BannedUsers';

    protected $_rowsetClass = 'Model_DbTable_Rowset_BannedUsers';

    protected $_referenceMap = array();

    protected $_dependentTables = array();

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($where, $order, $count, $offset);
    }

    public function findRow($key)
    {
        return $this->find($key)->current();
    }

    public function findByUserId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('user_id = ?', $value), $order, $count, $offset);
    }

    public function countByUserId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('user_id', 'num'=> 'COUNT(*)'))->where('user_id = ?', $value))->num;
    }


}

