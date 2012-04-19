<?php

class Model_DbTable_Comments extends Zend_Db_Table
{

    protected $_name = 'Comments';

    protected $_primary = array('comment_id');

    protected $_metadata = array(
        'comment_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'comment_id',
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
            'TABLE_NAME' => 'Comments',
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
            'TABLE_NAME' => 'Comments',
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
            'TABLE_NAME' => 'Comments',
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
        'user_category' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'user_category',
            'COLUMN_POSITION' => 5,
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
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'message',
            'COLUMN_POSITION' => 6,
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
        'created_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 7,
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
        'likes_count' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'likes_count',
            'COLUMN_POSITION' => 8,
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
        'type' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Comments',
            'COLUMN_NAME' => 'type',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'tinyint',
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
        'comment_id',
        'fanpage_id',
        'post_id',
        'facebook_user_id',
        'user_category',
        'message',
        'created_time',
        'likes_count',
        'type'
        );

    protected $_rowClass = 'Model_DbTable_Row_Comments';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Comments';

    protected $_referenceMap = array('COMMENTS_FANPAGES_ID_FK' => array(
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

    public function findByCommentId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('comment_id = ?', $value), $order, $count, $offset);
    }

    public function countByCommentId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('comment_id', 'num'=> 'COUNT(*)'))->where('comment_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('comment_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

