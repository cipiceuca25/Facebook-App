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
    	$this->_forward('index','index', 'app'); return;
    	$fanpageId = $this->_request->getParam('id');
    	if (empty($fanpageId)) {
			$this->_helper->viewRenderer->setRender('index/failure', null, true);
			return;
    	}
    	
     	//Zend_Debug::dump($fanpageId); exit();
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
                //$fb = new Service_FancrankFBService();
    			//Zend_Debug::dump($source_data); exit();
				
                //check user type: owner or fans
                try {
                	$fanpageAdmin = new Model_FanpageAdmins();
                	if($this->_getParam('id') && $source_data->facebook_user_id) {
                		$fanpageAdminUser = $fanpageAdmin->findRow($source_data->facebook_user_id, $this->_getParam('id'));
                	}
                } catch (Exception $e) {
                	//log $e
                	//Zend_Debug::dump($source_data->facebook_user_id .' '.$this->_getParam('id') .' '.$e->getMessage()); exit();
                	throw new Exception($e->getMessage());
                }
                
                //Zend_Debug::dump($fanpageAdminUser); exit();
                if(! empty($fanpageAdminUser)) {
                	$authenticate = true;
                }
                
                if ($authenticate) {
                    $source = $this->authenticateSource($source_data);
                } else {
                    $source = $this->authenticateFan($source_data);
                }

                $this->view->source = $source;

                return $source;
            }
        } else {
            // redirect the user to facebook login
            $extra_parameters = http_build_query($this->config->extra_parameters->redirect->toArray());
            $this->redirect(0, sprintf('%s?client_id=%s&redirect_uri=%s&%s', $this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
            //$this->redirect(0, sprintf('%s?signed_request=%s&client_id=%s&redirect_uri=%s&%s',isset($_REQUEST['signed_request'])?$_REQUEST['signed_request']:'',$this->config->authorize_url, $this->config->client_id, $this->callback, $extra_parameters));
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
        
        // check for matching records
        try {
            $select = $users->select();
            $select->where('facebook_user_id = ?', $source_data->facebook_user_id);

            // Returns NULL if no records match selection criteria.
            $user = $users->fetchAll($select);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        switch (count($user)) {
            case 0:
            	/*
                // check for duplicate user handle
                if ($users->countByFacebookUserId($source_data->facebook_user_id) > 0) {
                    $source_data->facebook_user_name = $source_data->facebook_user_name . substr(time(), -5);
                }
				*/
                $user = $users->createRow((array)$source_data);
                $user->save();

                //Collector::Run($this->source, 'init', array($source->source_id));
                break;

            case 1:
                //update some user data
                $user = $users->findByFacebookUserId($source_data->facebook_user_id)->current();
                $user->facebook_user_access_token = $source_data->facebook_user_access_token;
                $user->facebook_user_avatar = $source_data->facebook_user_avatar;
                $user->save();

                //Collector::Run($this->source, 'update', array($source->source_id));
                break;

            default:
                return false;
        }

        try {
        	$fanpages = $this->addFanpages($source_data);
        	$db->commit();
        	return $source_data;
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
        $select->where('facebook_user_id = ?', $source_data->facebook_user_id);

        // Returns NULL if no records match selection criteria.
        $user = $users->fetchAll($select);
        $db = $users->getDefaultAdapter();
        $db->beginTransaction();
        
        switch (count($user)) {
        	//case for new facebook user
            case 0:
            	try {
            		$users->insert((array)$source_data);
            	 
	                //add the fan if it doesnt exist
                	$fans_model = new Model_Fans;
                	$select = $fans_model->select();
                	$select->where($fans_model->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $source_data->facebook_user_id, $this->_getParam('id')));

                	$fan = $fans_model->fetchRow($select);

                	if (empty($fan)) {
                    	$new_fan_row = array(
                        	'facebook_user_id'      => $source_data->facebook_user_id,
                        	'fanpage_id'            => $this->_getParam('id')
                    	);  

                        $fans_model->insert($new_fan_row);
                    }
                    
                    $db->commit();
				}catch (Exception $e) {
                    	$db->rollBack();
                        die($e->getMessage());
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
            		$select->where($fans_model->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $source_data->facebook_user_id, $this->_getParam('id')));
            	
            		$fan = $fans_model->fetchRow($select);
            	
            		if (empty($fan)) {
            			$new_fan_row = array(
            					'facebook_user_id'      => $source_data->facebook_user_id,
            					'fanpage_id'            => $this->_getParam('id')
            			);
            	
            			$fans_model->insert($new_fan_row);
            		}

            		//update some user data
            		$user = $users->findByFacebookUserId($source_data->facebook_user_id)->current();
            		$user->facebook_user_access_token = $source_data->facebook_user_access_token;
            		$user->save();
            		//collect extra user data
            		//Collector::Run('fancrank', 'update', array($source_data->user_id, 'likes'));
            		
            		$db->commit();
            	}catch (Exception $e) {
            		$db->rollBack();
            		die($e->getMessage());
            	}

            	break;
            default:
                return false;
        }

        return $source_data;
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
    	parse_str($responseBody);
    	//Zend_Debug::dump($access_token); exit();
    
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/me');
    	$client->setMethod(Zend_Http_Client::GET);
    	$client->setParameterGet('access_token', $access_token);
    	$client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday,gender,locale,languages');
    
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
    	
    	return(object) array(
    			'facebook_user_id' 			=> $data->id,
    			'facebook_user_name' 		=> !empty($data->username) ? $data->username : '',
    			'facebook_user_first_name' 	=> !empty($data->user_first_name) ? $data->user_first_name : '',
    			'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
    			'facebook_user_email' 		=> !empty($data->email) ? $email : '',
    			'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
    			'facebook_user_avatar'    	=> sprintf('https://graph.facebook.com/%s/picture', $data->id),
    			'facebook_user_lang'        => implode(',', $lang),
    			'facebook_user_birthday'    => Fancrank_Util_Util::dateToStringForMysql(!empty($data->birthday)),
    			'facebook_user_access_token'=> $access_token,
    			'updated_time' 				=> Fancrank_Util_Util::dateToStringForMysql(!empty($data->updated_time)),
    			'facebook_user_locale' 		=> !empty($data->facebook_user_locale) ? $data->locale : '',
    			'hometown' 					=> !empty($data->hometown) ? $data->hometown : '',
    			'current_location' 			=> !empty($data->current_location) ? $data->current_location : '',
    			'bio' 						=> !empty($data->bio) ? $data->bio : ''
    	);
    }
    
    protected function addFanPages($source)
    {
    	$fanpages_model = new Model_Fanpages;
    	$fanpages = $fanpages_model->facebookRequest('me', $source->facebook_user_access_token, array('accounts'));
    	
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
    					'facebook_user_id'  => $source->facebook_user_id,
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