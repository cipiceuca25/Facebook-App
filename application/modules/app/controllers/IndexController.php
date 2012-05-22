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
            $this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
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
        $this->view->fan_id = $this->data['user_id'];

        $model = new Model_Rankings;
        $this->view->top_fans = $model->getRanking($this->data['page']['id'], 'FAN', false, 5);
        $this->view->most_popular = $model->getRanking($this->data['page']['id'], 'POPULAR', false, 5);
        $this->view->top_talker = $model->getRanking($this->data['page']['id'], 'TALKER', false, 5);
        $this->view->top_clicker = $model->getRanking($this->data['page']['id'], 'CLICKER', false, 5);

        /*
        $client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/' . $this->view->fan_id;
        $client->setMethod(Zend_Http_Client::GET);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (property_exists($json, 'error')) {
            // try again
    
        } else {
            $this->view->me = $json->data;
        }
        */
        
        //get user ranking
        $this->view->user_top_fans = $model->getRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
        $this->view->user_most_popular = $model->getRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
        $this->view->user_top_talker = $model->getRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
        $this->view->user_top_clicker = $model->getRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
    }

    public function loginAction()
    {
    }
}

