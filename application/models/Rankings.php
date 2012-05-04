<?php

class Model_Rankings extends Model_DbTable_Rankings
{

	public function getRanking($page_id, $type, $user_id = false, $limit = 5)
	{
		$select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false);
		$select->join(array('fans'), 'fans.facebook_user_id = rankings.facebook_user_id');
		$select->where($this->quoteInto('rankings.fanpage_id = ?', $page_id));
		$select->where($this->quoteInto('type = ?', $type));
		$select->order('rank DESC');

		if($user_id) 
			$select->where($this->quoteInto('rankings.facebook_user_id = ?', $user_id));

		if($limit)
			$select->limit($limit);

		return $this->fetchAll($select);
	}

}

