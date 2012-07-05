<?php
//could probably do security check in predispatch
class Api_UsersController extends Fancrank_API_Controller_BaseController
{
	public function preDispatch() 
	{
		parent::preDispatch();

		//lets validate the data ^_^ (maybe make validation in an ini file with inheritence structure that will validate in a predispatch at a lower level?)
	}

	
	public function init()
	{
		parent::init();
		
		//$this->user = $this->model->findRow($this->_getParam('id'));
	}
	
	public function indexAction() {
			
	}
	
	public function updateAction()
	{	
		if ($this->user->user_id == $this->_getParam('id')) {

			$params = $this->getAllParams();
			unset($params->id);

			foreach($this->getAllParams() as $key => $param)
			{	
				$this->user->{$key} = $this->_identity->{$key} =  $param;
			}
			
			$this->user->save();
		} else {
			//send access deinied 403
			$this->_response->setHttpResponseCode(403);
		}
	}
	
	public function followAction() {
		$subcriber = new Model_Subscribes();
		$result = $subcriber->fetchAll();
		Zend_Debug::dump($result);
		echo 'follow';
	}
	
}
