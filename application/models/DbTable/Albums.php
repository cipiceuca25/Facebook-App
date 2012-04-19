<?php

class Model_DbTable_Albums extends Zend_Db_Table
{

    protected $_name = 'Albums';

    protected $_primary = array('album_id');

    protected $_metadata = array(
        'album_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'album_id',
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
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
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
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'facebook_user_id',
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
            'TABLE_NAME' => 'Albums',
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
        'album_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'album_name',
            'COLUMN_POSITION' => 5,
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
        'description' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'description',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'text',
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
        'location' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'location',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'text',
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
        'link' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'link',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'text',
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
        'cover_photo_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'cover_photo_id',
            'COLUMN_POSITION' => 9,
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
        'count' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'count',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'int',
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
        'type' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'type',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'text',
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
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 12,
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
        'updated_time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Albums',
            'COLUMN_NAME' => 'updated_time',
            'COLUMN_POSITION' => 13,
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
            )
        );

    protected $_cols = array(
        'album_id',
        'fanpage_id',
        'facebook_user_id',
        'user_category',
        'album_name',
        'description',
        'location',
        'link',
        'cover_photo_id',
        'count',
        'type',
        'created_time',
        'updated_time'
        );

    protected $_rowClass = 'Model_DbTable_Row_Albums';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Albums';

    protected $_referenceMap = array('ALBUMS_FANPAGES_FK' => array(
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

    public function findByAlbumId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('album_id = ?', $value), $order, $count, $offset);
    }

    public function countByAlbumId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('album_id', 'num'=> 'COUNT(*)'))->where('album_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('album_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

