<?php

class Model_DbTable_FanpageAdmins extends Fancrank_Db_Table
{

    protected $_name = 'fanpage_admins';

    protected $_primary = array(
        'facebook_user_id',
        'fanpage_id'
        );

    protected $_metadata = array(
        'facebook_user_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpage_admins',
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
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpage_admins',
            'COLUMN_NAME' => 'fanpage_id',
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
            )
        );

    protected $_cols = array(
        'facebook_user_id',
        'fanpage_id'
        );

    protected $_rowClass = 'Model_DbTable_Row_FanpageAdmins';

    protected $_rowsetClass = 'Model_DbTable_Rowset_FanpageAdmins';

    protected $_referenceMap = array(
        'FANAPAGE_ADMIN_USER_FK' => array(
            'columns' => 'facebook_user_id',
            'refTableClass' => 'Model_Users',
            'refColumns' => 'user_id'
            ),
        'FANPAGE_ADMIN_PAGE_FK' => array(
            'columns' => 'fanpage_id',
            'refTableClass' => 'Model_Fanpages',
            'refColumns' => 'fanpage_id'
            )
        );

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
        return $this->fetchRow($this->select()->from($this->_name, array('facebook_user_id","fanpage_id', 'num'=> 'COUNT(*)'))->where('facebook_user_id = ?', $value))->num;
    }

    public function findByFanpageId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('fanpage_id = ?', $value), $order, $count, $offset);
    }

    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('facebook_user_id","fanpage_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    public function findUsers($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Users(), null, $select);
    }

    public function findFanpages($select = null)
    {
        return $this->findParentRow(new Model_DbTable_Fanpages(), null, $select);
    }


}

