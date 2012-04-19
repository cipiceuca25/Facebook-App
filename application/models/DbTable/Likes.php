<?php

class Model_DbTable_Likes extends Zend_Db_Table
{

    protected $_name = 'Likes';

    protected $_primary = array('likes_id');

    protected $_metadata = array(
        'likes_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Likes',
            'COLUMN_NAME' => 'likes_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '200',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Likes',
            'COLUMN_NAME' => 'fanpage_id',
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
        'post_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Likes',
            'COLUMN_NAME' => 'post_id',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '100',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Likes',
            'COLUMN_NAME' => 'facebook_user_id',
            'COLUMN_POSITION' => 4,
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
        'post_type' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Likes',
            'COLUMN_NAME' => 'post_type',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => null,
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
        'likes_id',
        'fanpage_id',
        'post_id',
        'facebook_user_id',
        'post_type'
        );

    protected $_rowClass = 'Model_DbTable_Row_Likes';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Likes';

    protected $_referenceMap = array('LIKES_FANPAGES_FK' => array(
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

    public function findByLikesId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('likes_id = ?', $value), $order, $count, $offset);
    }

    public function countByLikesId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('likes_id', 'num'=> 'COUNT(*)'))->where('likes_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('likes_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

