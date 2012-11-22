<?php
abstract class Fancrank_Db_Insight_Table extends Zend_Db_Table_Abstract
{
	public function getTableName() {
        return $this->_name;
    }

    public function getPrefix() {
        return str_replace('id', null, $this->getPrimaryKey());
    }

    public function getClassName() {
        return get_class($this);
    }

    public function getDependentTables() {
        return $this->_dependentTables;
    }

    public function getReferenceMap() {
        return $this->_referenceMap;
    }

    public function getPrimaryKey($first = false) {
        if (count($this->_primary) == 1) {
            return current($this->_primary);
        } else {
            if ($first) {
                return current($this->_primary);
            }

            return $this->_primary;
        }
    }

    public function getColumns($filterArray=null) {
    	return is_array($filterArray) ? array_diff($this->_getCols(),  $filterArray) : $this->_getCols();
    }

    public function getLastQuery() {
        return $this->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
    }

    public function countFoundRows() {
        return current($this->getAdapter()->query('SELECT FOUND_ROWS()')->fetch());
    }

}
