<?php

class Model_DbTable_Users extends Application_Db_Table
{

    protected $_name = 'users';

    protected $_primary = array('user_id');

    protected $_metadata = array(
        'user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
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
        'user_password' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_password',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '32',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_name',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '30',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_level' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_level',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'enum(\'0\',\'1\',\'2\',\'3\')',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_email' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_email',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_created' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_created',
            'COLUMN_POSITION' => 6,
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
        'user_password',
        'user_name',
        'user_level',
        'user_email',
        'user_created'
        );

    protected $_rowClass = 'Model_DbTable_Row_Users';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Users';

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

