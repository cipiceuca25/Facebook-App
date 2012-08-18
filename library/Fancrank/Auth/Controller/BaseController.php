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
       // $this->source = $this->getRequest()->getControllerName();

        // set the config property
        $this->config = $sources->get('facebook');

        // set callback url
        $this->callback = sprintf('%s://%s%s', $this->_request->getScheme(), $this->_request->getHttpHost(), $this->_request->getPathInfo());
	}

	public function loginAction()
    {
		$fanpageId = $this->_request->getParam ( 'id' );
		//Zend_Debug::dump($this->_getAllParams()); exit();
		$this->_helper->viewRenderer->setRender ( 'index/login', null, true );
		$user = $this->oauth2 ( true, false );
		
		if ($user) {
			// create user session
			//Zend_Debug::dump($user); exit();
			$this->_auth = Zend_Auth::getInstance ();
			$this->_auth->setStorage ( new Zend_Auth_Storage_Session ( 'Fancrank_Admin' ) );
			$this->_auth->getStorage ()->write ( $user );
			// $this->_auth->setExpirationSeconds(5259487);
		}
    }

    public function authorizeAction()
    {
    	//$this->_forward('index','index', 'app'); return;
    	$fanpageId = $this->_getParam('id');
    	if (empty($fanpageId)) {
			//$this->_helper->viewRenderer->setRender('index/failure', null, true);
			$this->_redirect('/app/app/index/' .$this->_getParam('id'));
			//return;
    	}
     	//Zend_Debug::dump($fanpageId); exit();
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        $this->_helper->viewRenderer->setRender('index/authorize', null, true);
        
        $user = $this->oauth2(false, false);
        
        if ($user) {
            //create user session
            $this->_auth->getStorage()->write($user);
			$this->view->current_fanpage_id = $fanpageId;
            //$this->_auth->setExpirationSeconds(5259487);
        }
        
    } 
    
    private function oauth2($authenticate = false, $user_id = false)
    {
    	$code = $this->_getParam('code', false);
    	
        if ($code !== false) {
        	//Zend_Debug::dump($code); exit();
            $client = new Zend_Http_Client();
            $client->setUri($this->config->access_token_url);
            $client->setMethod(Zend_Http_Client::POST);
            $client->setParameterPost(array(
                'client_id' => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'redirect_uri' => $this->callback,
                'code' => $code
            ));

            //Zend_Debug::dump($code);
            $response = $client->request();
			//Zend_Debug::dump($response);

            if ($response->getStatus() !== 200) {
                $this->_helper->viewRenderer->setRender('index/failure', null, true);
                $this->view->error = $this->getErrorInfo($response->getStatus(), $response->getBody());
            } else {
                // execute source specific method
                $source_data = $this->getSourceInfo($response->getBody());
    			//Zend_Debug::dump($source_data); exit();
				
                if ($authenticate) {
                    $source = $this->authenticateSource($source_data);
                	$this->view->adminSource = $source;
                    
                } else {
                    $source = $this->authenticateFan($source_data);
                    $this->view->source = $source;
                }
                //Zend_Debug::dump($source); exit();
                return $source;
			}
        } else {
            // redirect the user to facebook login
        	if ($authenticate) {
        		$extra_parameters = http_build_query($this->config->extra_parameters->redirect->toArray());
        	} else {
        		$extra_parameters = http_build_query($this->config->user_extra_parameters->redirect->toArray());
        	}
        	
            $this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
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
        $users = new Model_FacebookUsers;
        // Returns NULL if no records match selection criteria.

        $db = $users->getDefaultAdapter();
        $db->beginTransaction();
        
        try {
        	$users->saveAndUpdate($source_data);
        	$fanpages = $this->addFanpages($source_data);
        	$db->commit();
        	return (object)$source_data;
        } catch (Exception $e) {
        	//TOLOG
        	$db->rollBack();
         }
    }

    private function authenticateFan($source_data)
    {
        $users = new Model_FacebookUsers;

        //Zend_Debug::dump($source_data); exit();
        // check for matching records
        $select = $users->select();
        $select->where('facebook_user_id = ?', $source_data['facebook_user_id']);

        // Returns NULL if no records match selection criteria.
        $user = $users->fetchAll($select);
        $db = $users->getDefaultAdapter();
        $db->beginTransaction();

        $create = new Zend_Date();
        $new_fan_row = array(
        		'facebook_user_id'      => $source_data['facebook_user_id'],
        		'fanpage_id'            => $this->_getParam('id'),
        		'fan_first_name'		=> $source_data['facebook_user_first_name'],
        		'fan_last_name'			=> $source_data['facebook_user_last_name'],
        		'fan_gender'			=> $source_data['facebook_user_gender'],
        		'fan_name'				=> $source_data['facebook_user_name'],
        		'fan_user_avatar'		=> $source_data['facebook_user_avatar'],
        		'created_time'			=> $create->toString('yyyy-MM-dd HH:mm:ss'),
        		'last_login_time'		=> $create->toString('yyyy-MM-dd HH:mm:ss'),
        		'fan_level'				=> 1
        );
        
        switch (count($user)) {
        	//case for new facebook user
            case 0:
            	try {
            		$users->insert($source_data);
            	 
	                //add the fan if it doesnt exist
                	$fans_model = new Model_Fans;
                	$select = $fans_model->select();
                	$select->where($fans_model->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $source_data['facebook_user_id'], $this->_getParam('id')));

                	$fan = $fans_model->fetchRow($select);

                	if (empty($fan)) {
                		$fan->first_login_time = $create->toString('yyyy-MM-dd HH:mm:ss');
                        $fans_model->insert($new_fan_row);
                    }
                    
                    $db->commit();
				}catch (Exception $e) {
                    	$db->rollBack();
                        //die($e->getMessage());
                        //TO LOG
                        return;
                }
                //collect extra user data
                //Collector::Run('fancrank', 'init', array($source_data->user_id, 'likes'));

                //set up user subscription (https://developers.facebook.com/docs/reference/api/realtime/)
                break;
			//case for exist facebook user
            case 1:
            	try {
            		//add the fan if it doesnt exist
            		$fans_model = new Model_Fans;
            		$select = $fans_model->select();
            		$select->where($fans_model->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $source_data['facebook_user_id'], $this->_getParam('id')));
            	
            		$fan = $fans_model->fetchRow($select);
            	
            		if (empty($fan)) {
            			$fan->first_login_time = $create->toString('yyyy-MM-dd HH:mm:ss');
            			$fans_model->insert($new_fan_row);
            		}else {
            			$fan->fan_user_avatar = $source_data['facebook_user_avatar'];
            			$fan->last_login_time = $create->toString('yyyy-MM-dd HH:mm:ss');
            			$fan->save();
            		}

            		//update some user data
            		$user = $users->findByFacebookUserId($source_data['facebook_user_id'])->current();
            		$user->facebook_user_access_token = $source_data['facebook_user_access_token'];
            		$user->facebook_user_avatar = $source_data['facebook_user_avatar'];
            		$user->save();
            		
            		$db->commit();
            	}catch (Exception $e) {
            		$db->rollBack();
            		die($e->getMessage());
            		//TO LOG
            		return;
            	}

            	break;
            default:
                return false;
        }

        return (object)$source_data;
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
    
    protected function getSourceInfo($responseBody)
    {
    	parse_str($responseBody, $params);
    	//Zend_Debug::dump($access_token); exit();
    	$fb = new Service_FancrankFBService();
    	$access_token = $fb->getExtendedAccessToken($params['access_token']);

    	if(empty($access_token)) {
    		return null;
    	}
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/me');
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $access_token);
    	$client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday,gender,locale,languages,updated_time');
    
    	$response = $client->request();
    
    	$data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    
    	$email = null;
    
    	// reject stupid emails
    	if (!empty($data->email) || substr($data->email, -22) != 'proxymail.facebook.com') {
    		$email = $data->email;
    	}
    
    	if (isset($data->languages)) {
    		foreach($data->languages as $language) {
    			$lang[] = $language->name;
    		}
    	} else {
    		$lang = array();
    	}
    
    	if(empty($data->id)) {
    		return null;
    	}
    	
    	$birthday = new Zend_Date(!empty($data->birthday) ? $data->birthday : null);
    	$updated = new Zend_Date(!empty($data->updated_time) ? $data->updated_time : null);
    	
    	return array(
    			'facebook_user_id' 			=> $data->id,
    			'facebook_user_name' 		=> !empty($data->name) ? $data->name : '',
    			'facebook_user_first_name' 	=> !empty($data->first_name) ? $data->first_name : '',
    			'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
    			'facebook_user_email' 		=> !empty($data->email) ? $email : '',
    			'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
    			'facebook_user_avatar'    	=> sprintf('https://graph.facebook.com/%s/picture', $data->id),
    			'facebook_user_lang'        => implode(',', $lang),
    			'facebook_user_birthday'    => $birthday->toString('yyyy-MM-dd HH:mm:ss'),
    			'facebook_user_access_token'=> $access_token,
    			'updated_time' 				=> $updated->toString('yyyy-MM-dd HH:mm:ss'),
    			'facebook_user_locale' 		=> !empty($data->locale) ? $data->locale : ''
    	);
    }
    
    protected function addFanPages($source)
    {
    	$fanpages_model = new Model_Fanpages;
    	$fanpages = $fanpages_model->facebookRequest('me', $source['facebook_user_access_token'], array('accounts'));
    	
    	$rows = array();
    	if( empty($fanpages->accounts->data) ) {
    		return $rows;
    	}
    	
    	foreach ($fanpages->accounts->data as $fanpage) {
    		if($fanpage->category != 'Application') {
    			$rows[] = array(
    					'fanpage_id'        => $fanpage->id,
    					'fanpage_name'      => $fanpage->name,
    					'fanpage_category'  => $fanpage->category,
    					'access_token'      => $fanpage->access_token,
    			);
    
    			$admins[] = array(
    					'facebook_user_id'  => $source['facebook_user_id'],
    					'fanpage_id'        => $fanpage->id
    			);
    		}
    	}
    
    	try {
    		$cols = $update = array('fanpage_id', 'fanpage_name', 'fanpage_category', 'access_token');
    		$fanpages_model->insertMultiple($rows, $cols, $update);
    		
    		//die(print_r($admins));
    		$cols = array('facebook_user_id', 'fanpage_id');
    		$update = array('facebook_user_id', 'fanpage_id');
    		$fanpage_admins_model = new Model_FanpageAdmins;
    		$fanpage_admins_model->insertMultiple($admins, $cols, $update);    		
    	} catch (Exception $e) {
    		//TOLOG
    	}
    
    	return $rows;
    }

    
}