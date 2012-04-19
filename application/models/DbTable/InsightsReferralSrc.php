<?php

class Model_DbTable_InsightsReferralSrc extends Zend_Db_Table
{

    protected $_name = 'Insights_Referral_Src';

    protected $_primary = array('insights_id');

    protected $_metadata = array(
        'insights_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
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
            'TABLE_NAME' => 'Insights_Referral_Src',
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
            'TABLE_NAME' => 'Insights_Referral_Src',
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
        'suggestions' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'suggestions',
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
        'other' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'other',
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
        'profile' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'profile',
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
        'like_widget' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'like_widget',
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
        'mobile' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'mobile',
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
        'composer' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'composer',
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
        'search' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'search',
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
        'profile_connect' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'profile_connect',
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
        'network_ego' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'network_ego',
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
        'wap' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'wap',
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
        'time' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'Insights_Referral_Src',
            'COLUMN_NAME' => 'time',
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
            )
        );

    protected $_cols = array(
        'insights_id',
        'name',
        'period',
        'suggestions',
        'other',
        'profile',
        'like_widget',
        'mobile',
        'composer',
        'search',
        'profile_connect',
        'network_ego',
        'wap',
        'time'
        );

    protected $_rowClass = 'Model_DbTable_Row_InsightsReferralSrc';

    protected $_rowsetClass = 'Model_DbTable_Rowset_InsightsReferralSrc';

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

