<?php

class App_UserController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		
	}

	public function followAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
}

