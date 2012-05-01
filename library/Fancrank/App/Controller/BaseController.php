<?php

abstract class Fancrank_App_Controller_BaseController extends Fancrank_Controller_Action
{
	public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));

        //check for facebook signed request
        $data = $this->getSignedRequest();

        //do some checks with the data

        if(!$this->_auth->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));  
        } else {
            $this->_identity = $this->_auth->getIdentity();
            $this->view->user = $this->_identity;
        }
    }

    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/app/'. $this->_request->getControllerName() . '.js');

        $this->_helper->layout()->controller = $this->_request->getControllerName();
        $this->_helper->layout()->action = $this->_request->getActionName();
    }

    private function getSignedRequest()
    {
        $signed_request = $this->_getParam('signed_request', false);

        if ($signed_request) {
            $sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
            $secret = $sources->get('facebook')->client_secret;

            list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

            // decode the data
            $sig = $this->base64_url_decode($encoded_sig);
            $data = json_decode($this->base64_url_decode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                return null;
            }

            // check sig
            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig) {
                return null;
            }

            return $data;

        } else {
            return null;
        }
    }

    private function base64_url_decode($input) {
      return base64_decode(strtr($input, '-_', '+/'));
    }
}

