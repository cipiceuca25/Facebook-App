<?php

class App_UserController extends Fancrank_App_Controller_BaseController
{
	public function preDispatch() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function indexAction() {
		$postModel = new Model_Posts();
		$fanpageId = $this->_getParam('fanpage_id');
		$userId = $this->_getParam('id');
		//echo $fanpageId .' ' .$userId; exit();
		$result = $postModel->findByUserIdAndFanpageId($userId, $fanpageId);
		foreach ($result as $post) {
			Zend_Debug::dump($post);			
		}
		
	}

	public function followAction() {

	}
	
	public function profileAction() {
		$user = new Model_FacebookUsers();
		$user = $user->find($this->_getParam('id'))->current();
		if($user) {
			$this->_helper->json($user->toArray());
		}
	}
	
	public function postAction() {
		//TODO
		Zend_Debug::dump($this->_request->getParams());
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

?>
