<?php
//could probably do security check in predispatch
class Api_UsersController extends Fancrank_API_Controller_BaseController
{

	public function init()
	{
		parent::init();
		
		$this->user = $this->model->findRow($this->_getParam('id'));
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
}
