<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();

        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));

        $this->data = $this->getSignedRequest($this->_getParam('signed_request'));

        if (APPLICATION_ENV != 'production') {
            $this->data['page']['id'] = $this->_getParam('id');
            $this->data['user']['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
        }

        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
            $this->_identity = $this->_auth->getIdentity();
            $this->_helper->redirector('topfans', 'app', 'app', array($this->data['page']['id'] => ''));   
        }

        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));   
    }

    public function indexAction()
    {
        $this->view->fanpage_id = $this->data['page']['id'];
        $this->view->fan_id = $this->data['user']['user_id'];

        $model = new Model_Rankings;
        $this->view->top_fans = $model->getRanking($this->_getParam('id'), 'FAN');
        $this->view->most_popular = $model->getRanking($this->_getParam('id'), 'POPULAR');
        $this->view->top_talker = $model->getRanking($this->_getParam('id'), 'TALKER');
        $this->view->top_clicker = $model->getRanking($this->_getParam('id'), 'CLICKER');
    }

    public function loginAction()
    {
    }
}

