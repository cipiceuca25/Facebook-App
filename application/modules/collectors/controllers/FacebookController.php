<?php
class Collectors_FacebookController extends Fancrank_Collectors_Controller_BaseController
{
    private $types = array(
        'fans'  => 'fans',
        'feed' => 'feed',
        'albums' => 'photo'
    );

    public function init()
    {
        parent::init();

        // get the fanpage object
        $this->fanpages = new Model_Fanpages;
        $fanpage = $this->fanpages->findRow($this->_getParam(0));
        
        if ($fanpage === null) {
            // TODO not exiting
            Log::Err('Invalid Fanpage ID: "%s"', $this->_getParam(0));
            exit;
        } else if (!$fanpage->active) {
            Log::Info('Inactive Fanpage ID: "%s". Exiting.', $this->_getParam(0));
            exit;
        } else {
            $this->fanpage = $fanpage;
        }

        Log::Info('Initializing Fanpage: "%s"', $this->fanpage->fanpage_id);

        // schedule the next auto update
        Collector::Queue('1 hour', 'facebook', 'update', array($this->fanpage->fanpage_id));
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
        
        //$token		= 'AAACgL1Ty5ggBAMH9LGdNEp9SSRWCC2CGIrgwjyuAL8jQlC3PtHksaWIoxAqTCv6qQ8FloIWVgA4T3hHusKErw7F2U88mogqMFf6A2QZDZD';
        
        $url = 'https://graph.facebook.com/' . $this->fanpage->fanpage_id . '/' . $type;// . '?access_token=' . $token;

        switch ($type) {
            case 'feed':
                $fields = array();
                break;
            case 'albums':
                $fields = array();
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

        $client->setParameterGet('access_token', $this->fanpage->access_token);

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
            Collector::Queue('1 minute', 'facebook', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 1 minute [%s]', $e->getMessage());

            return;
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		//die(print_r($json));
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

    private function storeFeed ($feed)
    {	
        $posts_model = new Model_Posts;
        $posts_media_model = new Model_PostsMedia;

    	foreach($feed as $post) {
            //die(print_r(split('_', $post->id)));

            $row = array(
                'post_id' => $post->id,
                'facebook_user_id' => $post->from->id,
                'fanpage_id' => $this->fanpage->fanpage_id,
                'message' => isset($post->message) ? $post->message : '',
                'type' => $post->type,
                'created_time' => $post->created_time,
                'updated_time' => $post->updated_time,
                'comments_count' => $post->comments->count
            );

            if (property_exists($post, 'application') && isset($post->application_id)) {
                $row['application_id'] = $post->application->application_id;
                $row['application_name'] = $post->application->application_name;
            } else {
                $row['application_id'] = '';
                $row['application_name'] = '';
            }

            $posts[] = $row;

            if ($post->type != 'page' && $post->type != 'status') {

                $medias[] = array(
                    'post_id' => $post->id,
                    'post_type' => $post->type,
                    'post_picture' => $post->picture,
                    'post_link' => $post->link,
                    'post_source' => isset($post->source) ? $post->source : '',
                    'post_name' => $post->name,
                    'post_caption'  => $post->caption,
                    'post_description' => isset($post->description) ? $post->description : '',
                    'post_icon' => $post->icon
                );
            }

            if (isset($post->comments) && $post->comments->count ) {
                
                foreach($post->comments->data as $comment) {
                    $comments[] = array(
                        'comment_id'        => $comment->id,
                        'fanpage_id'        => $this->fanpage->fanpage_id,
                        'post_id'           => $post->id,
                        'facebook_user_id'  => $comment->from->id,
                        'user_category'     => $comment->from->category,
                        'message'           => $comment->message,
                        'created_time'      => $comment->created_time,
                        'likes_count'             => isset($comment->likes) ? $comment->likes : 0
                    );
                }
            }
        }

        $cols = array('post_id', 'facebook_user_id', 'fanpage_id', 'message', 'type', 'created_time', 'updated_time', 'comments_count');
        $update = array('updated_time', 'comments_count');
        $posts_model->insertMultiple($posts, $cols, $update);

        if (isset($medias)) {
            $cols = array('post_id', 'post_type', 'post_picture', 'post_link', 'post_source', 'post_name', 'post_caption', 'post_description', 'post_icon');
            $update = array('post_description');
            $posts_media_model->insertMultiple($medias, $cols, $update);
        }

        if (isset($comments)) {
            $cols = array('comment_id', 'fanpage_id', 'post_id', 'facebook_user_id', 'user_category', 'message', 'created_time', 'likes_count');
            $update = array('message', 'likes_count');

            $comments_model = new Model_Comments;
            $comments_model->insertMultiple($comments, $cols, $update);
        }
    }

    private function storeAlbums($albums)
    {
        die(print_r($albums));
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
