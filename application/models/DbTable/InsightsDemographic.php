<?php

class Model_DbTable_InsightsDemographic extends Fancrank_Db_Table
{

    protected $_name = 'Insights_Demographic';

    protected $_primary = array('insights_id');
/*
    protected $_metadata = array(
        'insights_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
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
            'TABLE_NAME' => 'Insights_Demographic',
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
            'TABLE_NAME' => 'Insights_Demographic',
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
        'time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'time',
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
        'F' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F',
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
        'M' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M',
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
        'U' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U',
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
        '13_17' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '13_17',
            'COLUMN_POSITION' => 8,
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
        '18_24' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '18_24',
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
        '25_34' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '25_34',
            'COLUMN_POSITION' => 10,
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
        '35_44' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '35_44',
            'COLUMN_POSITION' => 11,
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
        '45_54' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '45_54',
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
        '55_64' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '55_64',
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
        '65_' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => '65_',
            'COLUMN_POSITION' => 14,
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
        'F_13_17' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_13_17',
            'COLUMN_POSITION' => 15,
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
        'F_18_24' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_18_24',
            'COLUMN_POSITION' => 16,
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
        'F_25_34' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_25_34',
            'COLUMN_POSITION' => 17,
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
        'F_35_44' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_35_44',
            'COLUMN_POSITION' => 18,
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
        'F_45_54' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_45_54',
            'COLUMN_POSITION' => 19,
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
        'F_55_64' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_55_64',
            'COLUMN_POSITION' => 20,
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
        'F_65_' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'F_65_',
            'COLUMN_POSITION' => 21,
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
        'M_13_17' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_13_17',
            'COLUMN_POSITION' => 22,
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
        'M_18_24' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_18_24',
            'COLUMN_POSITION' => 23,
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
        'M_25_34' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_25_34',
            'COLUMN_POSITION' => 24,
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
        'M_35_44' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_35_44',
            'COLUMN_POSITION' => 25,
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
        'M_45_54' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_45_54',
            'COLUMN_POSITION' => 26,
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
        'M_55_64' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_55_64',
            'COLUMN_POSITION' => 27,
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
        'M_65_' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'M_65_',
            'COLUMN_POSITION' => 28,
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
        'U_13_17' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_13_17',
            'COLUMN_POSITION' => 29,
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
        'U_18_24' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_18_24',
            'COLUMN_POSITION' => 30,
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
        'U_25_34' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_25_34',
            'COLUMN_POSITION' => 31,
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
        'U_35_44' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_35_44',
            'COLUMN_POSITION' => 32,
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
        'U_45_54' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_45_54',
            'COLUMN_POSITION' => 33,
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
        'U_55_64' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_55_64',
            'COLUMN_POSITION' => 34,
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
        'U_65_' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_65_',
            'COLUMN_POSITION' => 35,
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
        'U_UNKNOWN' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Demographic',
            'COLUMN_NAME' => 'U_UNKNOWN',
            'COLUMN_POSITION' => 36,
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
        'time',
        'F',
        'M',
        'U',
        '13_17',
        '18_24',
        '25_34',
        '35_44',
        '45_54',
        '55_64',
        '65_',
        'F_13_17',
        'F_18_24',
        'F_25_34',
        'F_35_44',
        'F_45_54',
        'F_55_64',
        'F_65_',
        'M_13_17',
        'M_18_24',
        'M_25_34',
        'M_35_44',
        'M_45_54',
        'M_55_64',
        'M_65_',
        'U_13_17',
        'U_18_24',
        'U_25_34',
        'U_35_44',
        'U_45_54',
        'U_55_64',
        'U_65_',
        'U_UNKNOWN'
        );

    protected $_rowClass = 'Model_DbTable_Row_InsightsDemographic';

    protected $_rowsetClass = 'Model_DbTable_Rowset_InsightsDemographic';
*/
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

