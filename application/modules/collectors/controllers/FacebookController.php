<?php
class Collectors_FacebookController extends Collectors_Library_Controller
{
    private $types = array(
        'fans'  => 'fans',
        'feeds' => 'feeds',
        'albums' => 'photo'
    );

    public function initAction()
    {
        parent::init();

        // get the fanpage object
        $this->fanpages = new Model_Fanpages;
        $fanpage = $fanpages->findRow($this->_getParam(0));

        if ($fanpage === null) {
            // TODO not exiting
            Log::Err('Invalid Fanpage ID: "%s"', $this->_getParam(0));
            exit;
        } else {
            $this->fanpage = $fanpage;
        }

        Log::Info('Initializing Fanpage: "%s"', $this->fanpage->fanpage_id);

        foreach ($this->types as $callback => $type) {
            // fetch statuses
            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $callback, 'since', 0));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'facebook', 'update', array($this->source->source_id));
    }

    public function updateAction()
    {
        Log::Info('Updating Fanpage: "%s"', $this->fanpage->fanpage_id);

        foreach ($this->types as $callback => $type) {
            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $callback, 'since', $this->fanpage->latest_timestamp));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'facebook', 'update', array($this->fanpage->fanpage_id));
    }

    public function fetchAction()
    {
        $type       = $this->_getParam(1, false);
        $direction  = $this->_getParam(2, 'since');
        $timestamp  = $this->_getParam(3, 0);
        $extra      = $this->_getParam(4, false);

        $url = 'https://graph.facebook.com/' . $this->fanpage->fanpage_id . '/' . $type;

        switch ($type) {
            case: 'feeds':
                $fields = array();
                break;
            case 'albums':
                $fields = array('id');
                break;

            case 'photos':
                if ($extra) {
                    $url = 'https://graph.facebook.com/' . $extra . '/photos';
                    $fields = array('id','name','source','height','width','created_time');
                } else {
                    return;
                }
                break;

            case 'fans':
                $fields = array('id', 'name');
                break;

            default:
                return;
        }

        $this->fetchData($type, $url, $fields, $direction, $timestamp);
    }

    private function fetchData($type, $url, array $fields, $direction = 'since', $timestamp = 0)
    {
        Log::Info('Fetching %s from Fanpage: "%s" %s: "%d"', $type, $this->fanpage->fanpage_id, $direction, $timestamp);

        $client = new Zend_Http_Client;
        $client->setUri($url);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet($direction, $timestamp);
        $client->setParameterGet('access_token', $this->source->oauth_user_key);
        $client->setParameterGet(array(
            'format' => 'json',
            'limit' => 1000,
            'fields' => implode(',', $fields)
        ));

        if ($direction == 'since' and $timestamp != 0) {
            $client->setParameterGet('__previous', 1);
        }

        try {
            $response = $client->request();
        } catch (Exception $e) {
            // try again
            Collector::Queue('1 minute', 'facebook', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 1 minute [%s]', $e->getMessage());

            return;
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (property_exists($json, 'code')) {
            // try again
            Collector::Queue('5 minutes', 'facebook', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

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
                            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $type, 'until', $params['until']));
                        }
                    // go forward in history
                    } else {
                        $query = str_replace($url . '?', null, $json->paging->previous);

                        parse_str($query, $params);

                        if ($params['since'] != $timestamp) {
                            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $type, 'since', $params['since']));
                        }
                    }
                }
            } else {
                Log::Info('no new %s found', $type);
            }
        }
    }

    private function storeFeeds($feeds)
    {
       
    }

    private function storeAlbums($albums)
    {
        $direction  = $this->_getParam(2, 'since');
        $timestamp  = $this->_getParam(3, 0);

        foreach ($albums as $album) {
            Collector::Run('facebook', 'fetch', array($this->source->source_id, 'photos', $direction, $timestamp, $album->id));
        }
    }

    private function storePhotos($photos)
    {
        
    }

    private function storeFans($fans)
    {
        
    }
}
