<?php
class Fancrank_BadgeFactory
{
	public static function factory($type=null, $action=null) {
		$badge = "Fancrank_Badge_Model_" . ucfirst($type) .'Badges';
		if (class_exists($badge)) {
			return new $badge();
		}
		else {
			throw new Fancrank_Badge_Exception("Invalid badge type given.");
		}
	}

}

