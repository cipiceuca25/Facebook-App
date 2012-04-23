<?php

class Model_DbTable_PostsMedia extends Fancrank_Db_Table
{

    protected $_name = 'posts_media';

    protected $_primary = array('post_id');

    protected $_metadata = array(
        'post_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
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
        'post_type' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_type',
            'COLUMN_POSITION' => 2,
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
        'post_picture' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_picture',
            'COLUMN_POSITION' => 3,
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
        'post_link' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_link',
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
        'post_source' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_source',
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
        'post_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_name',
            'COLUMN_POSITION' => 6,
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
        'post_caption' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_caption',
            'COLUMN_POSITION' => 7,
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
        'post_description' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_description',
            'COLUMN_POSITION' => 8,
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
        'post_icon' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'posts_media',
            'COLUMN_NAME' => 'post_icon',
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
            )
        );

    protected $_cols = array(
        'post_id',
        'post_type',
        'post_picture',
        'post_link',
        'post_source',
        'post_name',
        'post_caption',
        'post_description',
        'post_icon'
        );

    protected $_rowClass = 'Model_DbTable_Row_PostsMedia';

    protected $_rowsetClass = 'Model_DbTable_Rowset_PostsMedia';

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

    public function findByPostId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('post_id = ?', $value), $order, $count, $offset);
    }

    public function countByPostId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('post_id', 'num'=> 'COUNT(*)'))->where('post_id = ?', $value))->num;
    }


}

