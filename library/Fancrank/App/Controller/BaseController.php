<?php

/*
signed request example
array(7) { 
    ["algorithm"]=> string(11) "HMAC-SHA256" ["expires"]=> int(1336150800) ["issued_at"]=> int(1336145562) ["oauth_token"]=> string(114) "AAAFWUgw4ZCZB8BAGWZC3VluHKonbHJwnGfVxZCSWsYNZAXOl1tpvpGrOu9P8Ai7WAHh75LXjBzLpeKAv0klqfxndKxStJMfZATXoPmYwZAa5gZDZD" ["page"]=> array(3) { ["id"]=> string(12) "265158018597" ["liked"]=> bool(true) ["admin"]=> bool(true) } 
    ["user"]=> array(3) { ["country"]=> string(2) "ca" ["locale"]=> string(5) "en_US" ["age"]=> array(1) { ["min"]=> int(21) } } ["user_id"]=> string(8) "48903527" }
*/

abstract class Fancrank_App_Controller_BaseController extends Fancrank_Controller_Action
{
    public function preDispatch()
    {
        //check for user authorization
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));

        if(!$this->_auth->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));  
            //set the proper navbar
            $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id',$this->_getParam('id')));
        } else {
            $this->_identity = $this->_auth->getIdentity();
            $this->view->user = $this->_identity;
            //set the proper navbar
            $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedin.phtml', array('fanpage_id',$this->_getParam('id')));
        }
    }

    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/app/'. $this->_request->getControllerName() . '.js');
        $this->view->headScript()->appendFile('/js/app/'. $this->_request->getControllerName() . '/' . $this->_request->getActionName() . '.js');

        $this->_helper->layout()->controller = $this->_request->getControllerName();
        $this->_helper->layout()->action = $this->_request->getActionName();
    }

    protected function getSignedRequest($signed_request = false)
    {


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

