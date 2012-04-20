<?php

class Fancrank_Auth_Controller_BaseController extends Fancrank_Controller_Action 
{
	protected $config = null;
    protected $source = null;
    protected $session = null;
    protected $callback = null;

	public function preDispatch() 
	{
		//$sources = $this->getModuleBootstrap()->getResource('Sources');
		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV);
		
		// get the source name
        $this->source = $this->getRequest()->getControllerName();

        // set the config property
        $this->config = $sources->get($this->source);

        // initiate session
        $this->session = new Zend_Session_Namespace('AUTH_' . $this->source);

        // expire in five minute
        $this->session->setExpirationSeconds(60 * 5);

        // set callback url
        $this->callback = sprintf('%s://%s%s', $this->_request->getScheme(), $this->_request->getHttpHost(), $this->_request->getPathInfo());
	}

	public function loginAction()
    {
    	 $this->_helper->viewRenderer->setRender('index/login', null, true);
    	 $user = $this->oauth2(true, false);

         if ($user) {
            //create user session
            $this->_auth = Zend_Auth::getInstance();
            $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_Admin'));
            $this->_auth->getStorage()->write($user);
            //$this->_auth->setExpirationSeconds(5259487);
        }
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
                $client->setParameterPost($this->config->extra_parameters->token->toArray());
            }

            $response = $client->request();

            if ($response->getStatus() !== 200) {
                $this->_helper->viewRenderer->setRender('index/failure', null, true);
                $this->view->error = $this->getErrorInfo($response->getStatus(), $response->getBody());
            } else {
                // execute source specific method
                $source_data = $this->getSourceInfo($response->getBody());

                if ($authenticate) {
                    $source = $this->authenticateSource($source_data);
                } else {
                    //$source = $this->addSource($source_data, $user_id);
                }

                $this->view->source = $source;

                return $source;
            }
        } else {
            // redirect the user
            $extra_parameters = http_build_query($this->config->extra_parameters->redirect->toArray());

            if ($authenticate) {
                $this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
            } else {
                $this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
            }
        }
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

    private function authenticateSource($source_data)
    {
        $users = new Model_Users;

        // check for matching records
        $select = $users->select();
        $select->where('user_id = ?', $source_data->user_id);

        // Returns NULL if no records match selection criteria.
        $user = $users->fetchAll($select);

        switch (count($user)) {
            case 0:
                // check for duplicate user handle
                if ($users->countByUserHandle($source_data->user_handle) > 0) {
                    $source_data->user_handle = $source_data->user_handle . substr(time(), -5);
                }

                $user = $users->createRow((array)$source_data);
                $user->save();


                break;

            case 1:
                //update some user data
                $user = $users->findByUserId($source_data->user_id)->current();
                $user->user_access_token = $source_data->user_access_token;
                $user->user_avatar = $source_data->user_avatar;
                $user->save();
                break;

            default:
                return false;
        }

        $this->addFanpages($source_data);
        
        return $user;
    }
}