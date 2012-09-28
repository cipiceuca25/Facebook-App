<?php

class Fancrank_Badge_Model_CustomBadges extends Fancrank_Badge_Model_Badges
{

	public function insert(array $data, $extra=null) {
		$data['type'] = 'custom';
		if($extra) {
			$data['type'] .= '_' .$extra;
		}
		return parent::insert($data);
	}
}

