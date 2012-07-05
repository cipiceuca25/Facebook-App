<?php

class Model_DbTable_FancrankUsers extends Fancrank_Db_Table
{

    protected $_name = 'fancrank_users';

    protected $_primary = array('facebook_user_id');

    /*
    protected $_metadata = array(
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_users',
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
        'fancrank_user_email' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_users',
            'COLUMN_NAME' => 'fancrank_user_email',
            'COLUMN_POSITION' => 2,
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
        'access_token' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_users',
            'COLUMN_NAME' => 'access_token',
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
        'updated_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_users',
            'COLUMN_NAME' => 'updated_time',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'created_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_users',
            'COLUMN_NAME' => 'created_time',
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
            )
        );

    protected $_cols = array(
        'facebook_user_id',
        'fancrank_user_email',
        'access_token',
        'updated_time',
        'created_time'
        );

    protected $_rowClass = 'Model_DbTable_Row_FancrankUsers';

    protected $_rowsetClass = 'Model_DbTable_Rowset_FancrankUsers';

	*/
    protected $_referenceMap = array('FANCRANK_USER_FAN_FK' => array(
            'columns' => 'facebook_user_id',
            'refTableClass' => 'Model_Fans',
            'refColumns' => 'facebook_user_id'
            ));

    protected $_dependentTables = array('Model_FancrankUserLikes');

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

    public function findFans($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fans(), null, $select);
    }


}

