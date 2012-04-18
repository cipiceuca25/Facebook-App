<?php

class Model_Users extends Model_DbTable_Users
{
    public function countByUserHandle($handle)
    {
        return $this->fetchRow($this->select()->from($this->_name, array('user_handle', 'num'=> 'COUNT(*)'))->where('user_handle = ?', $handle))->num;
    }


}

