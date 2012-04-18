<?php

class Model_DbTable_ActiveGuests extends Zend_Db_Table
{

    protected $_name = 'active_guests';

    protected $_primary = array('ip');

    protected $_metadata = array(
        'ip' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'active_guests',
            'COLUMN_NAME' => 'ip',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '15',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'timestamp' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'active_guests',
            'COLUMN_NAME' => 'timestamp',
            'COLUMN_POSITION' => 2,
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
        'ip',
        'timestamp'
        );

    protected $_rowClass = 'Model_DbTable_Row_ActiveGuests';

    protected $_rowsetClass = 'Model_DbTable_Rowset_ActiveGuests';

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

    public function findByIp($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('ip = ?', $value), $order, $count, $offset);
    }

    public function countByIp($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('ip', 'num'=> 'COUNT(*)'))->where('ip = ?', $value))->num;
    }


}

