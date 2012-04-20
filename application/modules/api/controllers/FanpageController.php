<?php

class Api_FanpageController extends Fancrank_Controller_API_BaseController
{
	public function activateAction()
	{
		$fanpage = $this->model->getActiveFanpageByFanpageId($this->_getParam('id'));

		if (!$fanpage->active) {
			$fanpage->active = TRUE;
			$fanpage->save();
		}
	}
}
