<?php

class Model_DbTable_Tags extends Fancrank_Db_Table
{

    protected $_name = 'tags';

    protected $_primary = array(
        'fanpage_id',
        'facebook_user_id',
        'facebook_user_name',
        'photo_id'
        );

    protected $_metadata = array(
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'fanpage_id',
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
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'facebook_user_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 2,
            'IDENTITY' => false
            ),
        'facebook_user_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'facebook_user_name',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 3,
            'IDENTITY' => false
            ),
        'photo_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'photo_id',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 4,
            'IDENTITY' => false
            ),
        'tag_position_x' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'tag_position_x',
            'COLUMN_POSITION' => 5,
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
        'tag_position_y' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'tag_position_y',
            'COLUMN_POSITION' => 6,
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
            'TABLE_NAME' => 'tags',
            'COLUMN_NAME' => 'created_time',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'timestamp',
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
        'fanpage_id',
        'facebook_user_id',
        'facebook_user_name',
        'photo_id',
        'tag_position_x',
        'tag_position_y',
        'created_time'
        );

    protected $_rowClass = 'Model_DbTable_Row_Tags';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Tags';

    protected $_referenceMap = array('TAGS_PHOTOS_FK' => array(
            'columns' => 'photo_id',
            'refTableClass' => 'Model_Photos',
            'refColumns' => 'photo_id'
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

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('fanpage_id","facebook_user_id","facebook_user_name","photo_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findByFacebookUserId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('facebook_user_id = ?', $value), $order, $count, $offset);
    }

    public function countByFacebookUserId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('fanpage_id","facebook_user_id","facebook_user_name","photo_id', 'num'=> 'COUNT(*)'))->where('facebook_user_id = ?', $value))->num;
    }

    public function findByFacebookUserName($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('facebook_user_name = ?', $value), $order, $count, $offset);
    }

    public function countByFacebookUserName($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('fanpage_id","facebook_user_id","facebook_user_name","photo_id', 'num'=> 'COUNT(*)'))->where('facebook_user_name = ?', $value))->num;
    }

    public function findByPhotoId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('photo_id = ?', $value), $order, $count, $offset);
    }

    public function countByPhotoId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('fanpage_id","facebook_user_id","facebook_user_name","photo_id', 'num'=> 'COUNT(*)'))->where('photo_id = ?', $value))->num;
    }

    public function findPhotos($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Photos(), null, $select);
    }


}

