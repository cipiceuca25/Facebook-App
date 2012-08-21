<?php

class Fancrank_Analytics_FancrankAnalytics
{
	protected $db;
	
	public function __construct($enabled = false)
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		$db = Zend_Db::factory($config->resources->db);
	}
	
}

