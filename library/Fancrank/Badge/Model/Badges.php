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
    	$query = $this->queryGenerate($fanpage_id, $facebook_user_id, $badgeId);
    	if(empty($query)) return false;
    	
    	$results = $this->getAdapter()->fetchAll($query);
    	//Zend_Debug::dump($results);
    	foreach($results as $result) {
    		if(empty($result['flag'])) return false;
    	}
    	return true;
    }
    
    private function queryGenerate($fanpage_id, $facebook_user_id, $badgeId) {
		$select = $this->getAdapter()->select();

    	$this->_badge = $this->findRow($badgeId);
    	
    	if(empty($this->_badge->rules)) return null;
    	
    	$ruleList = Zend_Json::decode($this->_badge->rules, Zend_Json::TYPE_ARRAY);
    	
    	$selects = array();
    	
    	if(isset($ruleList['from'])) {
    		$preSelect = 'SELECT count(*) > 0 AS flag';
    		foreach ($ruleList as $key=>$statement) {
    			switch ($key) {
    				case 'from' :
    					foreach ($statement as $key=>$v) {
    						$select->from(array($key=>$v), array());
    					}
    					break;
    				case 'join' :
    					//call_user_func_array(array($select, $key), $statement);
    					foreach ($statement as $v) {
    						$select->where($v);
    					}
    					break;
    				case 'where' :
    					foreach ($statement as $v) {
    						$select->where($v);
    					}
    					break;
    				case 'order' :
    					call_user_func_array(array($select, $key), $statement);
    					break;
    				case 'limit' :
    					call_user_func_array(array($select, $key), array($statement));
    					break;
    				default : break;
    			}
    		}
    		$selects[] = $preSelect .$select->assemble();    		
    	}else {
    		foreach ($ruleList as $rule) {
    			if($rule['table_name'] == $rule['table_field']) {
    				$selects[] = "select count(*) >= ". $rule['argument'] ." as flag from " .$rule['table_name']  ." where fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id";
    			}else {
    				$selects[] = "select count(*) > 0 as flag from " .$rule['table_name']  ." where " .$rule['table_field'] . $rule['operator'] .$rule['argument'] ." and fanpage_id = $fanpage_id and facebook_user_id = $facebook_user_id";
    			}
    		}
    	}

    	//Zend_Debug::dump($ruleList);

    	return implode(" union all ", $selects);
    }
}

