<?php
class Fc_AuthController extends Zend_Controller_Action
{
	protected $_auth; 
	
	public function preDispatch() {
		$this->_auth  = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session ( 'Internal_Admin' ));
		
		if ($this->_auth->hasIdentity() && $this->_request->getActionName() != 'logout') {
			$this->_redirect('/fc');
		}
		
		$this->view->layout()->disableLayout();
	}
	
	public function indexAction() {
		$this->render('index');	
	}
	
    public function loginAction() {
    	
    	try {
    		$username=$this->_getParam('username');
    		$password=$this->_getParam('password');

    		if (empty($username) || empty($password)) {
    			$this->view->message = 'invalid input';
    			return $this->render('index');
    		}
    		
    		$authAdapter = new Fancrank_Fc_Auth_Adapter($username, $password);
    		$result = $this->_auth->authenticate($authAdapter);
    		if($result->isValid()) {
    			$this->_auth->getStorage ()->write ( array('user'=>$username, 'login_time'=>Zend_Date::now()));
    			//Zend_Debug::dump($auth->getIdentity()); exit();
    			$this->_redirect('/fc');
    			return;
    		}
    	} catch (Exception $e) {
    	}
    	$this->render('index');
    }
    
    public function logoutAction()
    {
    	//$this->_helper->viewRenderer->setNoRender(true);
    	if ($this->_auth->hasIdentity()) {
    		$this->_auth->clearIdentity();
    	}
    	$this->_redirect('/fc/auth');
    }
    
}

?>