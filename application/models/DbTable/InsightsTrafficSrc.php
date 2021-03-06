<?php

class Model_DbTable_InsightsTrafficSrc extends Fancrank_Db_Table
{

    protected $_name = 'Insights_Traffic_Src';

    protected $_primary = array('insights_id');

    protected $_metadata = array(
        'insights_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
            'COLUMN_NAME' => 'insights_id',
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
            'IDENTITY' => true
            ),
        'name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
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
            'TABLE_NAME' => 'Insights_Traffic_Src',
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
        'location' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
            'COLUMN_NAME' => 'location',
            'COLUMN_POSITION' => 4,
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
        'value' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
            'COLUMN_NAME' => 'value',
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
            ),
        'time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
            'COLUMN_NAME' => 'time',
            'COLUMN_POSITION' => 6,
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
        'flag' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Traffic_Src',
            'COLUMN_NAME' => 'flag',
            'COLUMN_POSITION' => 7,
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
            )
        );

    protected $_cols = array(
        'insights_id',
        'name',
        'period',
        'location',
        'value',
        'time',
        'flag'
        );

    protected $_rowClass = 'Model_DbTable_Row_InsightsTrafficSrc';

    protected $_rowsetClass = 'Model_DbTable_Rowset_InsightsTrafficSrc';

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

