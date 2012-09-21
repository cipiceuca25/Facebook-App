<?php

class Fancrank_Badge_Model_FreeBadges extends Fancrank_Badge_Model_Badges
{
	
	public function insert(array $data) {
		$data['type'] = 'free';
		parent::insert($data);
	}
}

