<?php

class Fancrank_Badge_Model_BadgeRules extends Fancrank_Db_Table
{

	protected $_DEFAULT_TABLE_NAME_LIST = array('posts', 'comments', 'likes', 'subscribes');
	protected $_DEFAULT_TABLE_FIELD_LIST = array('post_comments_count', 'post_likes_count', 'comment_likes_count');
	protected $_DEFAULT_OPERATOR_LIST = array('>', '<', '=', '>=', '<=', '!=');
	protected $_argument = null;
	protected $_ruleList = array();
	
    protected $_name = 'badge_rules';

    protected $_primary = 'id';
    
    public function __get($varName)
    {
    	if(method_exists($this,$MethodName='get'.$varName)) {
    		return $this->$MethodName();
    	}else {
    		trigger_error($varName.' is not avaliable .',E_USER_ERROR);
    	}
    }

    private function get_DEFAULT_TABLE_NAME_LIST() {
    	return $this->_DEFAULT_TABLE_NAME_LIST;
    }
    
    private function get_DEFAULT_TABLE_FIELD_LIST() {
    	return $this->_DEFAULT_TABLE_FIELD_LIST;
    }
    
    private function get_DEFAULT_OPERATOR_LIST() {
    	return $this->_DEFAULT_OPERATOR_LIST;
    }
    
    public function addRule($rule) {
    	if($this->isValidRule($rule)) {
    		$this->_ruleList[] = $rule;    		
    	}
    }
    
    public function getOperator($index=0) {
    	return is_numeric($index) && $index < 6 ? $this->_DEFAULT_OPERATOR_LIST[$index] : '>';
    }
    
    public function removeRule($ruleId) {
    	unset($this->_ruleList[$ruleId]);	
    }
    
    public function getJsonRules() {
    	return Zend_Json::encode($this->_ruleList);
    }
    
    public function isValidRule($data) {
    	$key = array('table_name', 'table_field', 'operator' ,'argument');
    	foreach ($key as $k) {
    		if(!isset($data[$k])) return false;
    	}
    	return true;
    }
}

