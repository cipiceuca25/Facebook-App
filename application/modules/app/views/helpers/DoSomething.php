<?php
//Note: This is only a view helper. It should not have any connection to the database or do any domain logic;
//otherwise, it will violate the MVC desgin pattern
class Fancrank_View_Helper_DoSomething extends Zend_View_Helper_Abstract
{
	//declare class variable

	protected $_hasRelation = FALSE;

	//Note method name must be the same as class name without prefix
	public function doSomething($facebook_user_id)
	{
		if(! empty($facebook_user_id)) {
			//do something
			$this->_hasRelation = true;
			return '<a class="fancy-link">do something with' .$facebook_user_id .'</a>';
		}
		return 'do nothing';
	}

}

?>