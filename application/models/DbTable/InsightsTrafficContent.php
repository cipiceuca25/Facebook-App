<?php

class Model_DbTable_InsightsTrafficContent extends Zend_Db_Table
{

    protected $_name = 'Insights_Traffic_Content';

    protected $_primary = array('insights_id');

    protected $_metadata = array(
        'insights_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Content',
            'COLUMN_NAME' => 'insights_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '100',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => false
            ),
        'name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Content',
            'COLUMN_NAME' => 'name',
            'COLUMN_POSITION' => 2,
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
        'period' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Content',
            'COLUMN_NAME' => 'period',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '25',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'value' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Content',
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
            ),
        'time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Content',
            'COLUMN_NAME' => 'time',
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
        'insights_id',
        'name',
        'period',
        'value',
        'time'
        );

    protected $_rowClass = 'Model_DbTable_Row_InsightsTrafficContent';

    protected $_rowsetClass = 'Model_DbTable_Rowset_InsightsTrafficContent';

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

