<?php

class Fancrank_Badge_Model_CustomBadges extends Fancrank_Badge_Model_Badges
{

	public function insert(array $data) {
		$data['type'] = 'custom';
		return parent::insert($data);
	}
}

