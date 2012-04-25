<?php
class Auth_FacebookController extends Fancrank_Auth_Controller_BaseController
{
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

        $email = $data->email;

        // reject stupid emails
        if (substr($email, -22) == 'proxymail.facebook.com') {
            $email = null;
        }

        return (object) array(
            'user_id'               => $data->id,
            'user_handle'           => isset($data->username) ? $data->username : $data->first_name . ' ' . $data->last_name,
            'user_first_name'       => $data->first_name,
            'user_last_name'        => $data->last_name,
            'user_access_token'     => $access_token,
            'user_email'            => $email,
            'locale'                => $data->locale,
            'gender'                => $data->gender,
            'user_avatar'           => sprintf('https://graph.facebook.com/%s/picture', $data->id),
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
}