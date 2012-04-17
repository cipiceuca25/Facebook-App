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
    	 $this->oauth2(true, false);
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
                    $source = $this->addSource($source_data, $user_id);
                }

                $this->view->source = $source;
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

    private function addSource($source_data, $user_id)
    {
        if (is_array($source_data)) {
            foreach ($source_data as $value) {
                $sources[] = $this->addSource($value, $user_id);
            }

            return $sources;
        }

        // initiate the model
        $sources = new Model_Sources;

        // check for matching records
        $select = $sources->select();
        $select->where('source_provider = ?', $source_data->source_provider);
        $select->where('source_user_id = ?', $source_data->source_user_id);
        $select->where('user_id = ?', $user_id);

        // Returns NULL if no records match selection criteria.
        $source = $sources->fetchRow($select);

        if ($source) {
            // Update the source with returned access tokens.
            $source->setFromArray((array) $source_data);
            $source->active = true;
            $source->save();

            // trigger update
            $this->postSourceUpdate($source);
        } else {
            // associate with the logged in user
            $source_data->user_id = $user_id;

            // create new source
            $source = $sources->createRow((array) $source_data);
            $source->save();

            // trigger initialization
            $this->postSourceCreation($source);
        }

        return $source;
    }

    private function authenticateSource($source_data)
    {
        $users = new Model_FacebookUsers;

        // check for matching records
        $select = $sources->select();
        $select->where('facebook_user_id = ?', $source_data->source_user_id);
        $select->where('source_user_id = ?', $source_data->source_user_id);

        // Returns NULL if no records match selection criteria.
        $source = $sources->fetchAll($select);

        switch (count($source)) {
            case 0:
                // create new user
                $row = $this->getUserInfo($source_data);

                $filter = new Zend_Filter_Alnum();
                $row['user_handle'] = $return = $filter->filter($row['user_handle']);

                // check for duplicate user handle
                if ($users->countByUserHandle($row['user_handle']) > 0) {
                    $row['user_handle'] = $row['user_handle'] . substr(time(), -5);
                }

                // check for duplicate user handle
                if (!empty($row['user_email']) and $users->countByUserEmail($row['user_email']) > 0) {
                    $row['user_email'] = null;
                }

                $user = $users->createRow($row);
                $user->save();

                // associate with the new user
                $source_data->user_id = $user->user_id;

                // create new source
                $source = $sources->createRow((array) $source_data);
                $source->save();

                // trigger initialization
                $this->postSourceCreation($source);
                break;

            case 1:
                $source = $source[0];
                // Update the source with returned access tokens.
                $source->setFromArray((array) $source_data);
                $source->active = true;
                $source->save();

                // get the user object
                $user = $users->findByUserId($source->user_id)->current();

                // trigger update
                $this->postSourceUpdate($source);
                break;

            default:
                return false;
        }

        if ($user) {
            $user = $user->toArray();
            $token = $tokens->getToken('00000000-0000-0000-0000-000000000000', $user['user_id'])->toArray();

            // web token
            // TODO: What about mobile?
            $this->view->user = $this->cleanUser($user + $token);
        }

        return $source;
    }
}