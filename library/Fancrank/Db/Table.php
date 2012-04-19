<?php
class Fancrank_Db_Table extends Zend_Db_Table_Abstract
{
    public function getTableName()
    {
        return $this->_name;
    }

    public function getPrefix()
    {
        return str_replace('id', null, $this->getPrimaryKey());
    }

    public function getClassName()
    {
        return get_class($this);
    }

    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    public function getReferenceMap()
    {
        return $this->_referenceMap;
    }

    public function getPrimaryKey($first = false)
    {
        if (count($this->_primary) == 1) {
            return current($this->_primary);
        } else {
            if ($first) {
                return current($this->_primary);
            }

            return $this->_primary;
        }
    }

    public function getColumns()
    {
        return $this->_getCols();
    }

    public function getLastQuery()
    {
        return $this->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
    }

    public function countFoundRows()
    {
        return current($this->getAdapter()->query('SELECT FOUND_ROWS()')->fetch());
    }

    public function getDependentRows($row)
    {
        $primary_key = $this->getPrimaryKey();

        $results = array();
        $id = $row->{$primary_key};

        // get all dependant table mapping
        if (property_exists($this, 'childTables')) {
            $childTables = $this->childTables;
        } else {
            $childTables = $this->getDependentTables();
        }

        foreach ($childTables as $name => $table) {
            $model = new $table;
            $dependant_key = $model->getPrimaryKey();

            if (is_numeric($name)) {
                $name = $this->getPrefix() . $model->getTableName();
            }

            // reset values outside the loop
            $key = false;
            $columns = false;

            foreach ($model->getReferenceMap() as $key => $info) {
                // only the one matching this table
                if ($info['refTableClass'] == $this->getClassName()) {
                    $key = strtolower($key);
                    $columns = $info['refColumns'];
                }
            }

            if (is_array($dependant_key)) {
                $dependant_key = array_flip($dependant_key);
                unset($dependant_key[$primary_key]);
                $dependant_key = array_shift(array_flip($dependant_key));
            }

            // start with an empty result
            $results[$name] = array();

            $select = $model->select();
            $select->from($model->getTableName(), array($dependant_key, $primary_key));
            $select->where($primary_key . ' IN (?)', $id);

            $dependant_results = $model->fetchAll($select)->toArray();

            foreach ($dependant_results as $dependant) {
                $results[$name][] = $dependant[$dependant_key];
            }
        }

        return $results;
    }

    /**
     * Quote values and place them into a piece of text with placeholders
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.
     *
     * Accepts unlimited number of parameters, one for every question mark.
     *
     * @param string $text Text containing replacements
     * @return string
     */
    public function quoteInto($text)
    {
        // get function arguments
        $args = func_get_args();

        // remove $text from the array
        array_shift($args);

        // check if the first parameter is an array and loop through that instead
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        // replace each question mark with the respective value
        foreach ($args as $arg) {
            $text = preg_replace('/\\?{1}/', $this->_db->quote($arg), $text, 1);
        }

        // return processed text
        return $text;
    }

    /**
     * Inserts multiple rows into the table
     */
    public function insertMultiple(array $rows, array $columns = null, array $update = null)
    {
        $data = array();
        $bind = array();
        $adapter = $this->getAdapter();

        if ($columns == null) {
            $columns = $this->getColumns();
        } else {
            foreach ($columns as &$column) {
                $column = $adapter->quoteIdentifier($column, true);
            }
        }

        // a little gotcha with references
        unset($column);

        if ($update !== null) {
            foreach ($update as &$column) {
                $column = sprintf('%s = VALUES(%s)', $column, $column);
            }
        }

        // a little gotcha with references
        unset($column);

        $column_count = count($columns);

        // Loop through data to extract values for binding
        foreach ($rows as $row) {
            // ensure we got the right number of fields:
            $row = array_slice($row, 0, $column_count);

            // reset
            $values = array();

            foreach ($row as $value) {
                if ($value instanceof Zend_Db_Expr) {
                    $values[] = $value->__toString();
                } else {
                    $values[] = '?';
                    $bind[] = $value;
                }
            }

            $data[] = sprintf('(%s)', implode(', ', $values));
        }


        if ($update !== null) {
            // build statement
            $sql = sprintf('INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s', $this->getTableName(), implode(', ', $columns), implode(', '. "\n", $data), implode(', ', $update));
        } else {
            // build statement
            $sql = sprintf('INSERT INTO %s (%s) VALUES %s', $this->getTableName(), implode(', ', $columns), implode(', ', $data));
        }

        // Execute the statement and return the number of affected rows
        $stmt = $adapter->query($sql, $bind);
        $result = $stmt->rowCount();

        return $result;
    }
}
