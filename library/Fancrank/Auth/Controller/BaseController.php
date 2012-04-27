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

    public function authorizeAction()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        $this->_helper->viewRenderer->setRender('index/authorize', null, true);
            
        $user = $this->oauth2(false, false);
        
        if ($user) {
            //create user session
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
                    $source = $this->authenticateFan($source_data);
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
        try {
            $select = $users->select();
            $select->where('user_id = ?', $source_data->user_id);

            // Returns NULL if no records match selection criteria.
            $user = $users->fetchAll($select);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        switch (count($user)) {
            case 0:
                // check for duplicate user handle
                if ($users->countByUserHandle($source_data->user_handle) > 0) {
                    $source_data->user_handle = $source_data->user_handle . substr(time(), -5);
                }

                $user = $users->createRow((array)$source_data);
                $user->save();

                Collector::Run($this->source, 'init', array($source->source_id));

                break;

            case 1:
                //update some user data
                $user = $users->findByUserId($source_data->user_id)->current();
                $user->user_access_token = $source_data->user_access_token;
                $user->user_avatar = $source_data->user_avatar;
                $user->save();

                Collector::Run($this->source, 'update', array($source->source_id));

                break;

            default:
                return false;
        }

        $fanpages = $this->addFanpages($source_data);
        
        return $user;
    }

    private function authenticateFan($source_data)
    {
        $fancrank_users_model = new Model_FancrankUsers;

        // check for matching records
        $select = $fancrank_users_model->select();
        $select->where('facebook_user_id = ?', $source_data->user_id);

        // Returns NULL if no records match selection criteria.
        $user = $fancrank_users_model->fetchAll($select);

        switch (count($user)) {
            case 0:
                //add the fan if it doesnt exist
                $fans_model = new Model_Fans;
                $select = $fans_model->select();
                $select->where($fans_model->getAdapter()->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $source_data->user_id, $this->_getParam('id')));
                $fan = $fans_model->fetchAll($select);

                if (!count($fan)) {
                    $new_fan_row = array(
                        'facebook_user_id'      => $source_data->user_id,
                        'name'                  => isset($source_data->username) ? $source_data->username : $source_data->user_first_name . ' ' . $source_data->user_last_name,
                        'first_name'            => $source_data->user_first_name,
                        'last_name'             => $source_data->user_last_name,
                        'user_avatar'           => sprintf('https://graph.facebook.com/%s/picture', $source_data->user_id),
                        'gender'                => $source_data->user_gender,
                        'locale'                => $source_data->user_locale,
                        'lang'                  => $source_data->user_lang,
                        'fanpage_id'            => $this->_getParam('id')
                    );  

                    $new_fan = $fans_model->createRow($new_fan_row);
                    try {
                        $new_fan->save();

                    } catch (Exception $e) {
                        die($e->getMessage());
                    }
                }

                $row = array(
                    'facebook_user_id' => $source_data->user_id,
                    'fancrank_user_email'   => $source_data->user_email,
                    'access_token' => $source_data->user_access_token,
                );

                //will only insert if fan is present in fan table so must collect fans
                $user = $fancrank_users_model->createRow($row);
                $user->save();

                Collector::Run('fancrank', 'init', array($source_data->user_id, 'likes'));

                break;

            case 1:
                //update some user data
                $user = $fancrank_users_model->findByFacebookUserId($source_data->user_id)->current();
                $user->access_token = $source_data->user_access_token;
                $user->save();

                Collector::Run('fancrank', 'update', array($source_data->user_id, 'likes'));

                break;

            default:
                return false;
        }

        return $user;
    }

    
}