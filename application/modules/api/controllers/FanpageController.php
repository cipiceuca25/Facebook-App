<?php

class Api_FanpageController extends Fancrank_API_Controller_BaseController
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
