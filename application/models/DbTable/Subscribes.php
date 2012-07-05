<?php

class Model_DbTable_Subscribes extends Fancrank_Db_Table
{

    protected $_name = 'subscribes';

    protected $_primary = array(
    		'facebook_user_id',
    		'facebook_user_id_subscribe_to'
    );   

}

