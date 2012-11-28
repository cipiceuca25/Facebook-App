<?php

class Model_DbTable_ShippingInfo extends Fancrank_Db_Table
{

    protected $_name = 'shipping_info';

    protected $_primary = 'id';

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($where, $order, $count, $offset);
    }

    public function findRow($key)
    {
        return $this->find($key)->current();
    }

    public function findByUserId($facebookUserId) {
        return $this->fetchrow($this->getAdapter()->quoteInto('facebook_user_id = ?', $facebookUserId));
    }
}

