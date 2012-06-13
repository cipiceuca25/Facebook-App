<?php
class Auth_FacebookController extends Fancrank_Auth_Controller_BaseController
{
	public function preDispatch()
	{
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
		//$fanpageId = $this->_request->getParam('id');
		
		if($this->_auth->hasIdentity()) {
			$this->_helper->redirector('index', 'app', 'app', array($this->data['page']['id'] => null));
		}
		
		parent::preDispatch();
	}
	
	/*
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

        $client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/me');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet('access_token', $access_token);
        $client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday,gender,locale,languages');

        $response = $client->request();

        $data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        $date = new Zend_Date($data->birthday);

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
        		'facebook_user_access_token'=> $access_token,
        		'updated_time' 				=> Fancrank_Util_Util::dateToStringForMysql($data->updated_time),
        		'facebook_user_locale' 		=> !empty($data->facebook_user_locale) ? $data->locale : '',
        		'hometown' 					=> !empty($data->hometown) ? $data->hometown : '',
        		'current_location' 			=> !empty($data->current_location) ? $data->current_location : '',
        		'bio' 						=> !empty($data->bio) ? $data->bio : ''
        );
        
    }

    protected function addFanPages($source) 
    {
        $fanpages_model = new Model_Fanpages;
        $fanpages = $fanpages_model->facebookRequest('me', $source->user_access_token, array('accounts'));

        foreach ($fanpages->accounts->data as $fanpage) {
            if($fanpage->category != 'Application') {
                $rows[] = array(
                    'fanpage_id'        => $fanpage->id,
                    'fanpage_name'      => $fanpage->name,
                    'fanpage_category'  => $fanpage->category,
                    'access_token'      => $fanpage->access_token,
                );

                $admins[] = array(
                    'facebook_user_id'  => $source->user_id,
                    'fanpage_id'        => $fanpage->id
                );
            }
        }

        $cols = $update = array('fanpage_id', 'fanpage_name', 'fanpage_category', 'access_token');
        $fanpages_model->insertMultiple($rows, $cols, $update);

//die(print_r($admins));
        $cols = array('facebook_user_id', 'fanpage_id');
        $update = array('facebook_user_id', 'fanpage_id');
        $fanpage_admins_model = new Model_FanpageAdmins;
        $fanpage_admins_model->insertMultiple($admins, $cols, $update);

        return $rows;
    }
    */
}