<?php

//MAYBE THIS CAN RUN ONCE AFTER WE HAVE REAL TIME SETUP
class Collectors_FancrankController extends Fancrank_Collectors_Controller_BaseController
{
    private $types = array(
        'likes'  => 'likes'
    );

    public function init()
    {
        parent::init();

        // get the fanpage object
        $this->fancrank_users = new Model_FancrankUsers;
        $fancrank_user = $this->fancrank_users->findRow($this->_getParam(0));
        
        if ($fancrank_user === null) {
            // TODO not exiting
            Log::Err('Invalid Fancrank Facebook ID ID: "%s"', $this->_getParam(0));
            exit;
        } else {
            $this->fancrank_user = $fancrank_user;
        }
    }

    public function initAction()
    {
        Log::Info('Initializing Fancrank Facebook: "%s"', $this->fancrank_user->facebook_user_id);

        foreach ($this->types as $callback => $type) {
            Collector::Run('fancrank', 'fetch', array($this->fancrank_user->facebook_user_id, $callback, 'since'));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'fancrank', 'update', array($this->fancrank_user->facebook_user_id));
    }

    public function updateAction()
    {
        Log::Info('Updating Fanpage: "%s"', $this->fancrank_user->facebook_user_id);

        foreach ($this->types as $callback => $type) {
            Collector::Run('fancrank', 'fetch', array($this->fancrank_user->facebook_user_id, $callback, 'since'));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'fancrank', 'update', array($this->fancrank_user->facebook_user_id));
    }

    public function fetchAction()
    {
        $type       = $this->_getParam(1, false);
        $direction  = $this->_getParam(2, 'since');
        $timestamp  = $this->_getParam(3, 0);
        $extra      = $this->_getParam(4, false);
        
        $url = 'https://graph.facebook.com/' . $this->fancrank_user->facebook_user_id . '/' . $type;// . '?access_token=' . $token;

        switch ($type) {
            case 'likes':
                $fields = array();
                $url = 'https://graph.facebook.com/' . $this->fancrank_user->facebook_user_id . '/likes';
                break;
            default:
                return;
        }

        $this->fetchData($type, $url, $fields, $direction, $timestamp);
    }

    private function fetchData($type, $url, array $fields, $direction = 'since', $timestamp = 0)
    {
        Log::Info('Fetching %s from Fanpage: "%s" %s: "%d"', $type, $this->fancrank_user->facebook_user_id, $direction, $timestamp);

        $client = new Zend_Http_Client;
        $client->setUri($url);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet($direction, $timestamp);

        $client->setParameterGet('access_token', $this->fancrank_user->access_token);

        $client->setParameterGet(array(
            'format' => 'json',
            'limit' => 50,
            'fields' => implode(',', $fields)
        ));

        if ($direction == 'since' and $timestamp != 0) {
            $client->setParameterGet('__previous', 1);
        }

        try {
            $response = $client->request();
        } catch (Exception $e) {
            // try again
            Collector::Queue('1 minute', 'fancrank', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 1 minute [%s]', $e->getMessage());

            return;
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (property_exists($json, 'code')) {
            // try again
            Collector::Queue('5 minutes', 'fancrank', 'fetch', array($this->fancrank_user->facebook_user_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 5 minutes [%s]', $json->error_code);

            return;
        } else {
            $data = $json->data;

            if (count($data) > 0) {
                // call specific store method
                call_user_func(array($this, 'store' . $type), $data);

                // should we keep going?
                if (isset($json->paging)) {
                    // go back in history
                    if ($timestamp == 0 or $direction == 'until' ) {
                        $query = str_replace($url . '?', null, $json->paging->next);
                        parse_str($query, $params);

                        if (isset($params['until']) && $params['until'] != $timestamp) {
                            Collector::Run('fancrank', 'fetch', array($this->fancrank_user->facebook_user_id, $type, 'until', $params['until']));
                        }
                    // go forward in history
                    } else {
                        $query = str_replace($url . '?', null, $json->paging->previous);

                        parse_str($query, $params);

                        if ($params['since'] != $timestamp) {
                            Collector::Run('fancrank', 'fetch', array($this->fancrank_user->facebook_user_id, $type, 'since', $params['since']));
                        }
                    }
                }
            } else {
                Log::Info('no new %s found', $type);
            }
        }
    }

    private function storeLikes($likes)
    {
        foreach($likes as $like)
        {
            $rows[] = array(
                'facebook_user_id'  => $this->fancrank_user->facebook_user_id,
                'like_id'           => $like->id,
                'like_category'     => $like->category,
                'like_name'         => $like->name,
                'created_time'      => $like->created_time
            );
        }

        $fancrank_user_likes_model = new Model_FancrankUserLikes;
        $cols = array('facebook_user_id', 'like_id', 'like_category', 'like_name', 'created_time');
        $update = array('like_category');

        $fancrank_user_likes_model->insertMultiple($rows, $cols, $update);
    }
}

