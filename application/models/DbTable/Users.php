<?php

class Model_DbTable_Users extends Fancrank_Db_Table
{

    protected $_name = 'users';

    protected $_primary = array('user_id');

    protected $_metadata = array(
        'user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'user_handle' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_handle',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '45',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_first_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_first_name',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '45',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_last_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_last_name',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '45',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_avatar' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_avatar',
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
        'user_email' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_email',
            'COLUMN_POSITION' => 6,
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
        'user_access_token' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_access_token',
            'COLUMN_POSITION' => 7,
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
        'user_gender' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_gender',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '45',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'user_locale' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'user_locale',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '45',
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
            'COLUMN_POSITION' => 10,
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
        'user_handle',
        'user_first_name',
        'user_last_name',
        'user_avatar',
        'user_email',
        'user_access_token',
        'user_gender',
        'user_locale',
        'user_created'
        );

    protected $_rowClass = 'Model_DbTable_Row_Users';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Users';

    protected $_referenceMap = array();

    protected $_dependentTables = array('Model_FanpageAdmins');

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

