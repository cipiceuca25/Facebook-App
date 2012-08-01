<?php

class Model_FanpageThemes
{
	public static function getThemeList() {
		//Note we should query theme list from database if we grow big
		return array(1, 2, 3, 4, 5, 6, 7);
	}

	public static function isValidTheme($theme) {
		return is_numeric($theme) && in_array($theme, self::getThemeList()); 
	}
}