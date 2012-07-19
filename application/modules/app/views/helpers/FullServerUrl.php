<?php
class Fancrank_View_Helper_FullServerUrl extends Zend_View_Helper_Abstract
{
	public function fullServerUrl()
	{
		$protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
		echo $protocol .$_SERVER["SERVER_NAME"];
	}

}

?>