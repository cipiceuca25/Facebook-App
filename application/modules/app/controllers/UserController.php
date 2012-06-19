<?php

class App_UserController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		
	}

	public function followAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function postAction() {
		//TODO
	}
	
	public function updateProfile() {
		//TODO
	}
	
	public function likesAction($objectId) {
		//TODO
	}
	
	public function dislikeAction($objectId) {
		//TODO
	}
	
	public function commentAction($objectId, $type) {
		//TODO
	}
	
	public function add_aLbumAction() {
		//TODO
	}
	
	public function delete_albumAction($albumId) {
		//TODO
	}
	
	public function update_albumAction($albumId) {
		//TODO
	}
	
	
}

