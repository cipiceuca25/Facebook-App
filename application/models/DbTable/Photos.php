<?php

class Model_DbTable_Photos extends Zend_Db_Table
{

    protected $_name = 'Photos';

    protected $_primary = array('photo_id');

    protected $_metadata = array(
        'photo_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'photo_id',
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
            'TABLE_NAME' => 'Photos',
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
        'album_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'album_id',
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
        'faceboook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'faceboook_user_id',
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
            'TABLE_NAME' => 'Photos',
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
        'caption' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'caption',
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
        'picture' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'picture',
            'COLUMN_POSITION' => 7,
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
        'source' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'source',
            'COLUMN_POSITION' => 8,
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
        'height' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'height',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'int',
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
        'width' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'width',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'int',
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
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'link',
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
        'position' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'position',
            'COLUMN_POSITION' => 12,
            'DATA_TYPE' => 'int',
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
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 13,
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
            'TABLE_NAME' => 'Photos',
            'COLUMN_NAME' => 'updated_time',
            'COLUMN_POSITION' => 14,
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
        'photo_id',
        'fanpage_id',
        'album_id',
        'faceboook_user_id',
        'user_category',
        'caption',
        'picture',
        'source',
        'height',
        'width',
        'link',
        'position',
        'created_time',
        'updated_time'
        );

    protected $_rowClass = 'Model_DbTable_Row_Photos';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Photos';

    protected $_referenceMap = array('PHOTOS_FANPAGES_FK' => array(
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

    public function findByPhotoId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('photo_id = ?', $value), $order, $count, $offset);
    }

    public function countByPhotoId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('photo_id', 'num'=> 'COUNT(*)'))->where('photo_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('photo_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

