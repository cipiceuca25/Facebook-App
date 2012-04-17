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
        $client->setParameterGet('fields', 'id,username,link,first_name,last_name');

        $response = $client->request();

        $data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (empty($data->username)) {
            $filter = new Zend_Filter_Alnum();
            $data->username = $filter->filter($data->first_name . $data->last_name);
        }

        return (object) array(
            'source_provider'       => 'FACEBOOK',
            'source_user_id'        => $data->id,
            'source_user_handle'    => $data->username,
            'source_profile_url'    => $data->link,
            'oauth_user_key'        => $access_token,
            'source_avatar'         => sprintf('https://graph.facebook.com/%s/picture', $data->id),
        );
    }

    protected function getUserInfo($source_data)
    {
        $client = new Zend_Http_Client;
        $client->setUri('https://graph.facebook.com/me');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet('access_token', $source_data->oauth_user_key);
        $client->setParameterGet('fields', 'name,username,birthday,email');

        $response = $client->request();

        $data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        $date = new Zend_Date($data->birthday);

        $email = $data->email;

        // reject stupid emails
        if (substr($email, -22) == 'proxymail.facebook.com') {
            $email = null;
        }

        return array(
            'user_name'         => $data->name,
            'user_handle'       => $data->username,
            'user_email'        => $data->email,
            'user_birthdate'    => $date->toString(Zend_Date::ISO_8601)
        );
    }
}