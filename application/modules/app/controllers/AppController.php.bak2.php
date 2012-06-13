<?php

class App_AppController extends Fancrank_App_Controller_BaseController
{
	/*
	public function preDispatch() {
		//check for user authorization
		$this->_auth = Zend_Auth::getInstance();
		
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		$this->data = $this->getSignedRequest($this->_getParam('signed_request'));
		
		if (APPLICATION_ENV != 'production') {
			$this->data['page']['id'] = $this->_getParam('id');
			//$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
			$this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
		}
		
		if($this->_auth->hasIdentity()) {
			//bring the user into the app if he is already logged in
			$this->_identity = $this->_auth->getIdentity();
			$this->_helper->redirector('topfans', 'app', 'app', array($this->data['page']['id'] => ''));
		}
		
		//set the proper navbar
		$this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
	}
	*/
    public function indexAction()
    {

    }

  	public function topfansAction()
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

  		$this->view->user_top_fans = $model->getUserRanking($this->data['page']['id'], 'FAN', $this->view->fan_id);
  		$this->view->user_most_popular = $model->getUserRanking($this->data['page']['id'], 'POPULAR', $this->view->fan_id);
  		$this->view->user_top_talker = $model->getUserRanking($this->data['page']['id'], 'TALKER', $this->view->fan_id);
  		$this->view->user_top_clicker = $model->getUserRanking($this->data['page']['id'], 'CLICKER', $this->view->fan_id);
  	}

    public function logoutAction()
    {
    	
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index', 'app', array($this->_getParam('id') => ''));   
    }
    
    public function newsfeedAction() 
    {
    	
    	//$this->_helper->layout()->disableLayout();
    	//$this->_helper->viewRenderer->setNoRender(true);
    	//$sources = $this->getModuleBootstrap()->getResource('Sources');
    	$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
		
    	$this->config = $sources->get('facebook');
    	// get the source name
    	//$client = new Zend_Http_Client();
    	//$client->setUri($this->config->access_token_url);
    	
    	// set callback url
    	$this->callback = sprintf('%s://%s%s', $this->_request->getScheme(), $this->_request->getHttpHost(), $this->_request->getPathInfo());
    	//Zend_Debug::dump($this->config->client_id);
    	//Zend_Debug::dump($this->config->client_secret);
    	
    	//Zend_Debug::dump( $this->callback );
    	
    	//echo 'start to login facebook: ';
    	$user = $this->oauth2(true, false);
    	
    	$extra_parameters = http_build_query($this->config->extra_parameters->redirect->toArray());
    	//echo sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters);
    	Zend_Debug::dump( $user );
    	
    	$this->view->user_first_name = $user->user_first_name;
    	//$feed = $this->getFeed($user->user_access_token);
    	$this->view->myImage = $user->user_avatar;
    	Zend_Debug::dump($this->getFeed($user->user_access_token));
    	$this->render("newsfeed");
    }
    
    public function awardsAction() {
    	$this->_auth = Zend_Auth::getInstance();
    	$user = $this->_auth->getIdentity();
    	$this->view->user = $user;
    	Zend_Debug::dump($user);
    	$this->render("awards");
    }
    
    public function myprofileAction() {
    	//check for user authorization
    	$this->_auth = Zend_Auth::getInstance();
    	Zend_Debug::dump($this->_auth->getIdentity());
    }
    
    private function oauth2($authenticate = false, $user_id = false)
    {
    	$code = $this->_getParam('code', false);
    
    	if ($code !== false) {
    		$client = new Zend_Http_Client();
    		$client->setUri($this->config->access_token_url);
    		$client->setMethod(Zend_Http_Client::POST);
    		$client->setParameterPost(array(
    				'client_id' => $this->config->client_id,
    				'client_secret' => $this->config->client_secret,
    				'redirect_uri' => $this->callback,
    				'code' => $code
    		));
    
    		if ($this->config->extra_parameters->get('token', false) !== false) {
    			$client->setParameterPost($this->config->extra_parameters->token->toArray($this->config->extra_parameters->token->toArray()));
    			//Zend_Debug::dump($this->config->extra_parameters->token->toArray());
    		}
    
    		$response = $client->request();
    
    		if ($response->getStatus() !== 200) {
    			//Zend_Debug::dump($response->getStatus());
    			//Zend_Debug::dump($response->getBody());
    			//$this->_helper->viewRenderer->setRender('index/failure', null, true);
    			$this->view->error = $this->getErrorInfo($response->getStatus(), $response->getBody());
    		} else {
    			// execute source specific method
    			$source_data = $this->getSourceInfo($response->getBody());
    
    			$this->view->source = $source_data;
    
    			return $source_data;
    		}
    	} else {
    		// redirect the user
    		//echo 'good'; return;
    		$extra_parameters = http_build_query($this->config->extra_parameters->redirect->toArray());
    
    		if ($authenticate) {
    			$this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
    		} else {
    			$this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
    		}
    	}
    }
    
    protected function getSourceInfo($responseBody)
    {
    	parse_str($responseBody);
    
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/me');
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $access_token);
    	$client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday,gender,locale,languages');
    
    	$response = $client->request();
    
    	$data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    
    	$date = new Zend_Date($data->birthday);
    
    	$email = $data->email;
    
    	// reject stupid emails
    	if (substr($email, -22) == 'proxymail.facebook.com') {
    		$email = null;
    	}
    
    	if (isset($data->languages)) {
    		foreach($data->languages as $language) {
    			$lang[] = $language->name;
    		}
    	} else {
    		$lang = array();
    	}
    
    	return (object) array(
    			'user_id'               => $data->id,
    			'user_handle'           => isset($data->username) ? $data->username : $data->first_name . ' ' . $data->last_name,
    			'user_first_name'       => $data->first_name,
    			'user_last_name'        => $data->last_name,
    			'user_access_token'     => $access_token,
    			'user_email'            => $email,
    			'user_locale'           => $data->locale,
    			'user_gender'           => $data->gender,
    			'user_avatar'           => sprintf('https://graph.facebook.com/%s/picture', $data->id),
    			'user_lang'             => implode(',', $lang)
    	);
    }
    
    protected function getFeed($access_token) {
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/me/feed');
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $access_token);
    	
    	$response = $client->request();
    	
    	$data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	return $data;
    }
    
    private function redirect($delay, $url)
    {
    	if ($delay > 0) {
    		$target = sprintf('%s; url=%s', $delay, $url);
    
    		$this->getResponse()->setHeader('Refresh', $target);
    
    		$this->view->url = $url;
    		$this->view->delay = $delay;
    
    		$this->view->headMeta()->appendHttpEquiv('refresh', $target);
    
    		$this->_helper->viewRenderer->setRender('index/redirect', null, true);
    	} else {
    		$this->getResponse()->setRedirect($url, 302);
    	}
    }
    
    protected function getErrorInfo($code, $responseBody)
    {
    	$body = Zend_Json::decode($responseBody, Zend_Json::TYPE_OBJECT);
    
    	switch ($code) {
    		case 400:
    			return 'Bad Request: ' . $body->error->message;
    			break;
    
    
    		default:
    			return 'Oops! Something went wrong! ' . $body->error->message;
    	}
    }
}

