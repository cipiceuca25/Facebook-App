<?php
class Helper extends Zend_View_Helper_Abstract
{
	
	public function relation($model, $user, $target)
	{
		echo "hello";
		return $model->getRelation($user, $target);
	}
	
	public function r(){
		return "hello";
	}
	
}