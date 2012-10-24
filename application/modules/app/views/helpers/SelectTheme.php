<?php

class Fancrank_View_Helper_SelectTheme extends Zend_View_Helper_Abstract
{
	protected $_view;
	
	public function setView(Zend_View_Interface $view) {
		$this->_view = $view;
	}
	
	public function selectTheme($choice, $env) {
		/*
		switch($choice){
			case 1:
				echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/loadstyle1.css'));
				break;
			case 2:
				echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/loadstyle2.css'));
				break;
			case 3:
				echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/loadstyle3.css'));
				break;
			case 4:
				echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/loadstyle4.css'));
				break;
			default:
				echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/loadstyle1.css'));
				break;
		}
		*/
		if($env) {
			if(empty($choice)) {
				$choice = 1;
			}
			$cssFile = '/css/loadstyle' .$choice .'.less';
			echo $this->_view->headLink()->headLink(array('id'=>'stylesheet', 'rel'=>'stylesheet/less', 'href'=>"$cssFile"));
			$javascriptCode = sprintf("env = '%s'; less = {}; less.env = '%s';",$env, $env);
			echo $this->_view->headScript()->appendScript("$javascriptCode", 'text/javascript')
			->appendFile('/js/libs/less-1.3.0.min.js', 'text/javascript');
				
		}else {
			echo $this->_view->headLink()->headLink(array('type'=>'text/css', 'rel'=>'stylesheet', 'href'=>'/css/style.css'));
		}
	}
}

?>