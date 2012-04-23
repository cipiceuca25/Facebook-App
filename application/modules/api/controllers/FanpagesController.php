<?php

class Api_FanpagesController extends Fancrank_API_Controller_BaseController
{
	public function activateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && !$fanpage->active) {
			$fanpage->active = TRUE;
			$fanpage->save();
		} else {
			//send access deinied 403
		}
	}

	public function deactivateAction()
	{
		$fanpage = $this->model->findByFanpageId($this->_getParam('id'))->current();
		
		//security check
		$fanpage_admin_model = new Model_FanpageAdmins;
		$admin = $fanpage_admin_model->findByFanpageId($this->_getParam('id'))->current();
		
		if ($admin->facebook_user_id == $this->_identity->user_id && $fanpage->active) {
			$fanpage->active = FALSE;
			$fanpage->save();
		} else {
			//send access deinied 403
		}
	}
}
