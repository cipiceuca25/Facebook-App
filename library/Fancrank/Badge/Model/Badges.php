<?php

class Fancrank_Badge_Model_Badges extends Fancrank_Db_Table
{

    protected $_name = 'badges';

    protected $_primary = 'id';

    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
    	return $this->fetchAll($where, $order, $count, $offset);
    }
    
    public function findRow($key)
    {
    	return $this->find($key)->current();
    }
    
    public function findByBadgeName($badgeName) {
    	$where = $this->quoteInto('name = ?', $badgeName);
    	return $this->findAll($where);
    }

    public function isFanEligible($fanpage_id, $facebook_user_id, $badgeId) {
    	
    	//Zend_Debug::dump($this->queryGenerate($fanpage_id, $facebook_user_id, $badgeId));
    	$results = $this->getAdapter()->fetchAll($this->queryGenerate($fanpage_id, $facebook_user_id, $badgeId));
    	//Zend_Debug::dump($results);
    	foreach($results as $result) {
    		if(empty($result['flag'])) return false;
    	}
    	return true;
    }
    
    private function queryGenerate($fanpage_id, $facebook_user_id, $badgeId) {
    	$selects = array();

    	$this->_badge = $this->findRow($badgeId);
    	$ruleList = Zend_Json::decode($this->_badge->rules, Zend_Json::TYPE_ARRAY);
    	
    	Zend_Debug::dump($ruleList);
    	foreach ($ruleList as $rule) {
    		if($rule['table_name'] == $rule['table_field']) {
    			$selects[] = "select count(*) > 0 as flag from " .$rule['table_name']  ." where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id";
    		}else {
    			$selects[] = "select count(*) > 0 as flag from " .$rule['table_name']  ." where " .$rule['table_field'] . $rule['operator'] .$rule['argument'] ." and fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id";
    		}
    	}
    	return implode(" union all ", $selects);
    }
}

