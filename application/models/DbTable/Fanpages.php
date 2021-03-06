<?php

class Model_DbTable_Fanpages extends Fancrank_Db_Table
{

    protected $_name = 'fanpages';

    protected $_primary = array('fanpage_id');
/*
    protected $_metadata = array(
        'fanpage_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
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
        'fanpage_name' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'fanpage_name',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'fanpage_category' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'fanpage_category',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '45',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'fanpage_tab_id' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'fanpage_tab_id',
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
        'latest_timestamp' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'latest_timestamp',
            'COLUMN_POSITION' => 5,
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
            ),
        'access_token' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'access_token',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'active' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'active',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false
            ),
        'installed' => array(
            'SCHEMA_NAME' => null,
            'TABLE_NAME' => 'fanpages',
            'COLUMN_NAME' => 'installed',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '0',
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
        'fanpage_id',
        'fanpage_name',
        'fanpage_category',
        'fanpage_tab_id',
        'latest_timestamp',
        'access_token',
        'active',
        'installed'
        );

    protected $_rowClass = 'Model_DbTable_Row_Fanpages';

    protected $_rowsetClass = 'Model_DbTable_Rowset_Fanpages';

    protected $_referenceMap = array();

    protected $_dependentTables = array(
        'Model_Albums',
        'Model_Comments',
        'Model_Fans',
        'Model_Likes',
        'Model_Photos',
        'Model_Posts',
        'Model_Rankings'
        );
*/
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

    public function findByFanpageUsername($username) 
    {
    	$query = $this->select()
    				->from($this->_name, array('*'))
    				->where('fanpage_username = ?', $username);
    	return $this->fetchRow($query);
    }
    
    public function countByFanpageId($value)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('fanpage_id', 'num'=> 'COUNT(*)'))->where('fanpage_id = ?', $value))->num;
    }

    // save or update the fanpages
    public function saveAndUpdate($fanpageData) {
    	if($this->findRow($fanpageData['fanpage_id'])){
    		$this->update($fanpageData,  $this->getAdapter()->quoteInto('fanpage_id = ?', $fanpageData['fanpage_id']));
    	}else {
    		$this->insert($fanpageData);
    	}
    }

}

