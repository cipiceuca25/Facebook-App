<?php
class Fancrank_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
    protected function _insert()
    {
        $primary = array_keys($this->_getPrimaryKey());

        if (empty($this->{$primary[0]})) {
            $this->{$primary[0]} = uuid_create(UUID_TYPE_RANDOM);
        }
    }

    public function findDependentIds($dependentTable, $ruleKey = null, Zend_Db_Table_Select $select = null)
    {
        if (is_string($dependentTable)) {
            $dependentTable = $this->_getTableFromString($dependentTable);
        }

        if ($select === null) {
            $select = $dependentTable->select();
        } else {
            $select->setTable($dependentTable);
        }

        $select->from($dependentTable, $dependentTable->getPrimaryKey());

        $rowset = $this->findDependentRowset($dependentTable, null, $select);

        // start with an empty result
        $results = array();

        foreach ($rowset as $row) {
            $results[] = $row->{$dependentTable->getPrimaryKey()};
        }

        return $results;
    }

    protected function _transformColumn($columnName)
    {
        $columns = $this->_table->getColumns();
        $prefix = $this->_table->info(Zend_Db_Table::NAME);

        $prefixedColumnName = $prefix . '_' . parent::_transformColumn($columnName);

        if (in_array($prefixedColumnName, $columns)) {
            return $prefixedColumnName;
        } else {
            return $columnName;
        }
    }

    public function toObject() {
        return (object) $this->toArray();
    }
}
