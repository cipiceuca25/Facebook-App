<?php
class Collectors_FacebookController extends Collectors_Library_Controller
{
    private $types = array(
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
            $timestamp = $this->fanpages->getLastDate($this->fanpage->fanpage_id);

            Collector::Run('facebook', 'fetch', array($this->source->source_id, $callback, 'since', $timestamp));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'facebook', 'update', array($this->source->source_id));
    }

    public function fetchAction()
    {
        $type       = $this->_getParam(1, false);
        $direction  = $this->_getParam(2, 'since');
        $timestamp  = $this->_getParam(3, 0);
        $extra      = $this->_getParam(4, false);

        $url = 'https://graph.facebook.com/fanpage_id/' . $type;

        switch ($type) {
            case 'statuses':
                $fields = array('id','message','updated_time');
                break;

            case 'events':
                $fields = array('id','name','description','location','start_time','end_time');
                break;

            case 'checkins':
                $fields = array('id','message','place','created_time');
                break;

            case 'notes':
                $fields = array('id','subject','message','created_time');
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

            case 'friends':
                $fields = array('id', 'name');
                break;

            default:
                return;
        }

        $this->fetchData($type, $url, $fields, $direction, $timestamp);
    }

    private function fetchData($type, $url, array $fields, $direction = 'since', $timestamp = 0)
    {
        Log::Info('Fetching %s from Facebook account: "%s" %s: "%d"', $type, $this->source->source_id, $direction, $timestamp);

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
            Collector::Queue('1 minute', 'facebook', 'fetch', array($this->source->source_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 1 minute [%s]', $e->getMessage());

            return;
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (property_exists($json, 'code')) {
            // try again
            Collector::Queue('5 minutes', 'facebook', 'fetch', array($this->source->source_id, $type, $direction, $timestamp));

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
                            Collector::Run('facebook', 'fetch', array($this->source->source_id, $type, 'until', $params['until']));
                        }
                    // go forward in history
                    } else {
                        $query = str_replace($url . '?', null, $json->paging->previous);

                        parse_str($query, $params);

                        if ($params['since'] != $timestamp) {
                            Collector::Run('facebook', 'fetch', array($this->source->source_id, $type, 'since', $params['since']));
                        }
                    }
                }
            } else {
                Log::Info('no new %s found', $type);
            }
        }
    }

    private function storeEvents($events)
    {
        $model = new Model_Events;

        foreach ($events as $event) {
            $start = new Zend_Date($event->start_time);
            $end = new Zend_Date($event->end_time);

            $row = array(
                'event_id'          => uuid_create(UUID_TYPE_RANDOM),
                'event_name'        => $event->name,
                'event_description' => isset($event->description) ?  $this->purifier->purify($event->description) : null,
                'event_start_time'  => $start->toString(Zend_Date::ISO_8601),
                'event_end_time'    => $end->toString(Zend_Date::ISO_8601),
                'event_address'     => isset($event->location) ? $event->location : null,
                'source_id'         => $this->source->source_id,
                'original_id'       => $event->id,
                'user_id'           => $this->source->user_id
            );

            $rows[] = $row;
        }

        $columns = array('event_id', 'event_name', 'event_description', 'event_start_time', 'event_end_time', 'event_address', 'source_id', 'original_id', 'user_id');
        $update = array('original_id');

        $count = $model->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new events', $count);
        } else {
            Log::Info('no new events found');
        }
    }

    private function storeStatuses($statuses)
    {
        $model = new Model_Statuses;

        foreach ($statuses as $status) {

            $date = new Zend_Date($status->updated_time);

            $row = array(
                'status_id'     => uuid_create(UUID_TYPE_RANDOM),
                'status_date'   => $date->toString(Zend_Date::ISO_8601),
                'status_text'   => $this->purifier->purify($status->message),
                'source_id'     => $this->source->source_id,
                'original_id'   => $status->id,
                'user_id'       => $this->source->user_id
            );

            $rows[] = $row;
        }

        $columns = array('status_id', 'status_date', 'status_text', 'source_id', 'original_id', 'user_id');
        $update = array('original_id');

        $count = $model->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new statuses', $count);
        } else {
            Log::Info('no new statuses found');
        }
    }

    private function storeCheckins($checkins)
    {
        $model = new Model_Places;

        foreach ($checkins as $checkin) {
            $date = new Zend_Date($checkin->created_time);

            if (isset($checkin->place->street)) {
                $address = sprintf('%s - %s, %s, %s, %s', $checkin->place->street, $checkin->place->city, $checkin->place->state, $checkin->place->zip, $checkin->place->country);
            } else {
                $address = null;
            }

            $row = array(
                'place_id'            => uuid_create(UUID_TYPE_RANDOM),
                'place_date'          => $date->toString(Zend_Date::ISO_8601),
                'place_location'      => new App_Db_Expr('POINT(%s, %s)', $checkin->place->location->latitude, $checkin->place->location->longitude),
                'place_text'          => isset($checkin->message) ? $this->purifier->purify($checkin->message) : null,
                'place_name'          => $checkin->place->name,
                'place_address'       => $address,
                'source_id'           => $this->source->source_id,
                'original_id'         => $checkin->id,
                'user_id'             => $this->source->user_id
            );

            $rows[] = $row;
        }

        $columns = array('place_id', 'place_date', 'place_location', 'place_text', 'place_name', 'place_address', 'source_id', 'original_id', 'user_id');
        $update = array('original_id');

        $count = $model->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new places', $count);
        } else {
            Log::Info('no new places found');
        }
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
        $model = new Model_Photos;

        foreach ($photos as $photo) {
            $date = new Zend_Date($photo->created_time);

            $row = array(
                'photo_id'          => uuid_create(UUID_TYPE_RANDOM),
                'photo_date'        => $date->toString(Zend_Date::ISO_8601),
                'photo_description' => isset($photo->name) ? $this->purifier->purify($photo->name) : null,
                'photo_source'      => $photo->source,
                'photo_hash'        => md5($photo->source),
                'source_id'         => $this->source->source_id,
                'original_id'       => $photo->id,
                'user_id'           => $this->source->user_id
            );

            $rows[] = $row;
        }

        $columns = array('photo_id', 'photo_date', 'photo_description', 'photo_source', 'photo_hash', 'source_id', 'original_id', 'user_id');
        $update = array('original_id');

        $count = $model->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new photos', $count);
        } else {
            Log::Info('no new photos found');
        }
    }

    private function storeNotes($notes)
    {
        $model = new Model_Notes;

        foreach ($notes as $note) {
            $date = new Zend_Date($note->created_time);

            $row = array(
                'note_id'       => uuid_create(UUID_TYPE_RANDOM),
                'note_date'     => $date->toString(Zend_Date::ISO_8601),
                'note_subject'  => $note->subject,
                'note_text'     => $this->purifier->purify($note->message),
                'source_id'     => $this->source->source_id,
                'original_id'   => $note->id,
                'user_id'       => $this->source->user_id
            );

            $rows[] = $row;
        }

        $columns = array('note_id', 'note_date', 'note_subject', 'note_text', 'source_id', 'original_id', 'user_id');
        $update = array('original_id');

        $count = $model->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new notes', $count);
        } else {
            Log::Info('no new notes found');
        }
    }

    private function storeFriends($friends)
    {
        $c = new Model_Contacts;

        $rows = array();
        foreach($friends as $friend) {
            $rows[] = array(
                'contact_id'   => uuid_create(UUID_TYPE_RANDOM),
                'source_id' => $this->source->source_id,
                'contact_user_id' => $friend->id,
                'contact_name' => $friend->name,
                'contact_avatar' => sprintf('https://graph.facebook.com/%s/picture', $friend->id),
                'user_id' => $this->source->user_id
            );
        }

        $where[] = $c->getAdapter()->quoteInto('source_id = ?', $this->source->source_id);
        $c->delete($where);

        $columns = array('contact_id', 'source_id', 'contact_user_id',  'contact_name', 'contact_avatar', 'user_id');
        //deleted anyway
        $update = null;

        $count = $c->insertMultiple($rows, $columns, $update);

        if ($count > 0) {
            Log::Info('stored %d new friends', $count);
        } else {
            Log::Info('no new friends found');
        }
    }
}
