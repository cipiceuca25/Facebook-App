<?php

class Fancrank_Badge_Model_DefaultBadges extends Fancrank_Badge_Model_Badges
{

	public function insert(array $data) {
		$data['type'] = 'default';
		return parent::insert($data);
	}
}

