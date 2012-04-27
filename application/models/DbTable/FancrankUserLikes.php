<?php

class Model_DbTable_FancrankUserLikes extends Fancrank_Db_Table
{

    protected $_name = 'fancrank_user_likes';

    protected $_primary = array(
        'facebook_user_id',
        'like_id'
        );

    protected $_metadata = array(
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_user_likes',
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
        'like_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_user_likes',
            'COLUMN_NAME' => 'like_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 2,
            'IDENTITY' => false
            ),
        'like_category' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_user_likes',
            'COLUMN_NAME' => 'like_category',
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
        'like_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_user_likes',
            'COLUMN_NAME' => 'like_name',
            'COLUMN_POSITION' => 4,
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
        'created_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fancrank_user_likes',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 5,
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
            )
        );

    protected $_cols = array(
        'facebook_user_id',
        'like_id',
        'like_category',
        'like_name',
        'created_time'
        );

    protected $_rowClass = 'Model_DbTable_Row_FancrankUserLikes';

    protected $_rowsetClass = 'Model_DbTable_Rowset_FancrankUserLikes';

    protected $_referenceMap = array('fk_fancrank_user_likes_facebook_user_id' => array(
            'columns' => 'facebook_user_id',
            'refTableClass' => 'Model_FancrankUsers',
            'refColumns' => 'facebook_user_id'
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

    public function findByFacebookUserId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('facebook_user_id = ?', $value), $order, $count, $offset);
    }

    public function countByFacebookUserId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('facebook_user_id","like_id', 'num'=> 'COUNT(*)'))->where('facebook_user_id = ?', $value))->num;
    }

    public function findByLikeId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('like_id = ?', $value), $order, $count, $offset);
    }

    public function countByLikeId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('facebook_user_id","like_id', 'num'=> 'COUNT(*)'))->where('like_id = ?', $value))->num;
    }

    public function findFancrankUsers($select = null)
    {
        return $this->findParentRow(new Model_DbTable_FancrankUsers(), null, $select);
    }


}

