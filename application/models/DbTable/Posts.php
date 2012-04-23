<?php

class Model_DbTable_Posts extends Fancrank_Db_Table
{

    protected $_name = 'Posts';

    protected $_primary = array('post_id');

    protected $_metadata = array(
        'post_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'post_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'facebook_user_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'bigint',
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
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'fanpage_id',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'bigint',
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
        'user_category' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'user_category',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'message' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'message',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'text',
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
        'privacy_descr' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'privacy_descr',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '25',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'privacy_value' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'privacy_value',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '25',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'type' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'type',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '25',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'created_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 9,
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
        'updated_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'updated_time',
            'COLUMN_POSITION' => 10,
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
        'application_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'application_name',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '25',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'application_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'application_id',
            'COLUMN_POSITION' => 12,
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
        'comments_count' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'comments_count',
            'COLUMN_POSITION' => 13,
            'DATA_TYPE' => 'float',
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
        'likes_count' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Posts',
            'COLUMN_NAME' => 'likes_count',
            'COLUMN_POSITION' => 14,
            'DATA_TYPE' => 'float',
            'DEFAULT' => null,
            'NULLABLE' => true,
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
        'post_id',
        'facebook_user_id',
        'fanpage_id',
        'user_category',
        'message',
        'privacy_descr',
        'privacy_value',
        'type',
        'created_time',
        'updated_time',
        'application_name',
        'application_id',
        'comments_count',
        'likes_count'
        );

    protected $_rowClass = 'Model_DbTable_Row_Posts';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Posts';

    protected $_referenceMap = array('POSTS_FANPAGES_ID_FK' => array(
            'columns' => 'fanpage_id',
            'refTableClass' => 'Model_Fanpages',
            'refColumns' => 'fanpage_id'
            ));

    protected $_dependentTables = array();

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($where, $order, $count, $offset);
    }

    public function findRow($key)
    {
        return $this->find($key)->current();
    }

    public function findByPostId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('post_id = ?', $value), $order, $count, $offset);
    }

    public function countByPostId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('post_id', 'num'=> 'COUNT(*)'))->where('post_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('post_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

