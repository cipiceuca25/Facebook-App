<?php

class Model_Users extends Model_DbTable_Users
{
    public function findByUsernameAndPassword($username, $password)
    {
        return $this->fetchRow($this->select()->where('user_name = ?', $username)->where('user_password = ?', $password));
    }


}

