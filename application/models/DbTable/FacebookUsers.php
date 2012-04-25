<?php

class Model_DbTable_FacebookUsers extends Fancrank_Db_Table
{

    protected $_name = 'facebook_users';

    protected $_primary = array('facebook_user_id');

    protected $_metadata = array(
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'facebook_user_id',
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
        'facebook_user_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'facebook_user_name',
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
        'facebook_user_email' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'facebook_user_email',
            'COLUMN_POSITION' => 3,
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
        'facebook_user_gender' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'facebook_user_gender',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'enum(\'male\',\'female\')',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'updated_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'updated_time',
            'COLUMN_POSITION' => 5,
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
            ),
        'access_token' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'facebook_users',
            'COLUMN_NAME' => 'access_token',
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
            )
        );

    protected $_cols = array(
        'facebook_user_id',
        'facebook_user_name',
        'facebook_user_email',
        'facebook_user_gender',
        'updated_time',
        'access_token'
        );

    protected $_rowClass = 'Model_DbTable_Row_FacebookUsers';

    protected $_rowsetClass = 'Model_DbTable_Rowset_FacebookUsers';

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

    public function findByFacebookUserId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('facebook_user_id = ?', $value), $order, $count, $offset);
    }

    public function countByFacebookUserId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('facebook_user_id', 'num'=> 'COUNT(*)'))->where('facebook_user_id = ?', $value))->num;
    }


}

