<?php

class Model_DbTable_InsightsTrafficViews extends Zend_Db_Table
{

    protected $_name = 'Insights_Traffic_Views';

    protected $_primary = array('insights_id');

    protected $_metadata = array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'Insights_Traffic_Views',
        'COLUMN_NAME' => 'value',
        'COLUMN_POSITION' => 4,
        'DATA_TYPE' => 'mediumint',
        'DEFAULT' => null,
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
        );

    protected $_cols = array(
        'insights_id',
        'name',
        'period',
        'value',
        'time',
        'type'
        );

    protected $_rowClass = 'Model_DbTable_Row_InsightsTrafficViews';

    protected $_rowsetClass = 'Model_DbTable_Rowset_InsightsTrafficViews';

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

    public function findByInsightsId($value, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($this->getAdapter()->quoteInto('insights_id = ?', $value), $order, $count, $offset);
    }

    public function countByInsightsId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('insights_id', 'num'=> 'COUNT(*)'))->where('insights_id = ?', $value))->num;
    }


}

