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
        $client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday');

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
            'user_avatar'           => sprintf('https://graph.facebook.com/%s/picture', $data->id),
        );
    }
}