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
    }

    public function initAction()
    {
        Log::Info('Initializing Fanpage: "%s"', $this->fanpage->fanpage_id);

        foreach ($this->types as $callback => $type) {
            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $callback, 'since'));
        }

        // schedule the next auto update
        Collector::Queue('1 hour', 'facebook', 'update', array($this->fanpage->fanpage_id));
    }

    public function updateAction()
    {
        Log::Info('Updating Fanpage: "%s"', $this->fanpage->fanpage_id);

        foreach ($this->types as $callback => $type) {
            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, $callback, 'since'));
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

        $url = 'https://graph.facebook.com/' . $this->fanpage->fanpage_id . '/' . $type;// . '?access_token=' . $token;

        switch ($type) {
            case 'feed':
                $fields = array();
                break;
            case 'albums':
                $fields = array();
                break;
            case 'comments':
            	if($extra) {
            		$this->post_id = $extra;
            		$url = 'https://graph.facebook.com/' . $extra . '/comments';
            		$fields = array();
            	} else {
                    return;
                }
            	break;
            case 'likes':
        		if($extra) {
            		$this->post_id = $extra;
            		$url = 'https://graph.facebook.com/' . $extra . '/likes';
            		$fields = array();
            	} else {
                    return;
                }
            	break;
            case 'photos':
                if ($extra) {
                    $this->album_id = $extra;
                    $url = 'https://graph.facebook.com/' . $extra . '/photos';
                    $fields = array('id','name','source','height','width', 'name', 'tags', 'place', 'updated_time', 'created_time');
                } else {
                    return;
                }
                break;

            default:
                return;
        }

        $this->fetchData($type, $url, $fields, $direction, $timestamp);
    }

    private function fetchData($type, $url, array $fields, $direction = 'since', $timestamp = 0)
    {
        Log::Info('Fetching %s from Fanpage: "%s" %s: "%d" with URL: "%s"', $type, $this->fanpage->fanpage_id, $direction, $timestamp, $url);

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

        try {
            $response = $client->request();
        } catch (Exception $e) {
            // try again
            Collector::Queue('1 minute', 'facebook', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 1 minute [%s]', $e->getMessage());

            return;
        }

        $json = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

        if (property_exists($json, 'error')) {
            // try again
            Collector::Queue('5 minutes', 'facebook', 'fetch', array($this->fanpage->fanpage_id, $type, $direction, $timestamp));

            Log::Info('Facebook request failed, re-trying after 5 minutes [%s]', $json->error->message);

            return;
        } else {
            $data = $json->data;

            if (count($data) > 0) {
                // call specific store method
                call_user_func(array($this, 'store' . $type), $data);

                // should we keep going?
                if (isset($json->paging)) {
                    // go back in history
                    if ($timestamp == 0 or $direction == 'since' ) {
                        $query = str_replace($url . '?', null, $json->paging->next);
                        parse_str($query, $params);

                        if (isset($params['since']) && $params['since'] != $timestamp) {
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
            $created = new Zend_Date($post->created_time);
            $updated = new Zend_Date($post->updated_time);
            
            $direction  = $this->_getParam(2, 'since');
            $timestamp  = $this->_getParam(3, 0);

            $row = array(
                'post_id'               => $post->id,
                'facebook_user_id'      => $post->from->id,
                'fanpage_id'            => $this->fanpage->fanpage_id,
                'post_message'          => isset($post->message) ? $post->message : '',
                'post_type'             => $post->type,
                'created_time'          => $created->toString(Zend_Date::ISO_8601),
                'updated_time'          => $updated->toString(Zend_Date::ISO_8601),
                'post_comments_count'   => $post->comments->count,
                'post_likes_count'      => isset($post->likes) && isset($post->likes->count) ? $post->likes->count : 0
            );

            if (property_exists($post, 'application') && isset($post->application_id)) {
                $row['post_application_id'] = $post->application->application_id;
                $row['post_application_name'] = $post->application->application_name;
            } else {
                $row['post_application_id'] = '';
                $row['post_application_name'] = '';
            }

            $posts[] = $row;

            if (isset($post->likes->data) && isset($post->likes->count)) {
            	//die(print_r($post->likes->count));
            	if($post->likes->count > 1){
            	
            		Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'likes', $direction, $timestamp, $post->id));
            		
            	}else{
            		
            		foreach($post->likes->data as $like) {
            			//die(print_r($like));
            			$fans[] = $like->id;
            		
            			$likes[] = array(
            					'fanpage_id'        => $this->fanpage->fanpage_id,
            					'post_id'           => $post->id,
            					'facebook_user_id'  => $like->id,
            					'post_type'         => $post->type
            			);
            		
            		}
            	}
            }

            $fans[] = $post->from->id;

            if ($post->type != 'page' && $post->type != 'status') {

                $medias[] = array(
                    'post_id'           => $post->id,
                    'post_type'         => $post->type,
                    'post_picture'      => isset($post->picture) ? $post->picture : '',
                    'post_link'         => isset($post->link) ? $post->link : '',
                    'post_source'       => isset($post->source) ? $post->source : '',
                    'post_name'         => isset($post->name) ? $post->name : '',
                    'post_caption'      => isset($post->caption) ? $post->caption : '',
                    'post_description'  => isset($post->description) ? $post->description : '',
                    'post_icon'         => $post->icon
                );
            }

            if (isset($post->comments) && $post->comments->count ) {
                
                foreach($post->comments->data as $comment) {

                    $created = new Zend_Date($comment->created_time);

                    $comments[] = array(
                        'comment_id'            => $comment->id,
                        'fanpage_id'            => $this->fanpage->fanpage_id,
                        'comment_post_id'       => $post->id,
                        'facebook_user_id'      => $comment->from->id,
                        'comment_message'       => $comment->message,
                        'created_time'          => $created->toString(Zend_Date::ISO_8601),
                        'comment_likes_count'   => isset($comment->likes) ? $comment->likes : 0
                    );

                    $fans[] = $comment->from->id;
                    /* DOESN'T WORK
                    if($post->comments->count > 2){
                    	
                    	Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'comments', $direction, $timestamp, $post->id));
                    }
                    */
                    
                }
            }
        }

        $cols = array('post_id', 'facebook_user_id', 'fanpage_id', 'post_message', 'post_type', 'created_time', 'updated_time', 'post_comments_count', 'post_likes_count');
        $update = array('updated_time', 'post_comments_count', 'post_likes_count');
        $posts_model->insertMultiple($posts, $cols, $update);

        if (isset($medias)) {
            $cols = array('post_id', 'post_type', 'post_picture', 'post_link', 'post_source', 'post_name', 'post_caption', 'post_description', 'post_icon');
            $update = array('post_description');
            $posts_media_model->insertMultiple($medias, $cols, $update);
        }

        if(isset($comments)) {
            $cols = array('comment_id', 'fanpage_id', 'comment_post_id', 'facebook_user_id', 'comment_message', 'created_time', 'comment_likes_count');
            $update = array('comment_message', 'comment_likes_count');

            $comments_model = new Model_Comments;
            $comments_model->insertMultiple($comments, $cols, $update);   
        }

        if (isset($likes)) {
            $cols = array('fanpage_id','post_id', 'facebook_user_id', 'post_type');
            $update = array('post_type');

            $likes_model = new Model_Likes;
            $likes_model->insertMultiple($likes, $cols, $update);
        }

        $unique_fans = array_unique($fans);

        $this->storeFans($unique_fans);
    }

    private function storeComments($comments)
    {
    	$comments_model = new Model_Comments;
    	
    	$direction  = $this->_getParam(2, 'since');
    	$timestamp  = $this->_getParam(3, 0);

    	foreach($comments as $comment) {
    		//die(print_r(split('_', $post->id)));
    		$created	= new Zend_Date($post->created_time);

    		$comments[] = array(
    				'comment_id'           	=> $comment->id,
    				'facebook_user_id'      => $comment->from->id,
    				'fanpage_id'            => $this->fanpage->fanpage_id,
    				'comment_post_id'		=> $this->post_id,
    				'comment_message'       => isset($comment->message) ? $comment->message : '',
    				'created_time'          => $created->toString(Zend_Date::ISO_8601),
    				'comment_likes_count'   => isset($comment->likes) ? $comment->likes : 0
    		);
    		
    		if($comment->likes > 1){
    			Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'likes', $direction, $timestamp, $comment->id));
    		}
    	}
    	
    	$cols = array('comment_id', 'fanpage_id', 'comment_post_id', 'facebook_user_id', 'comment_message', 'created_time', 'comment_likes_count');
    	$update = array('comment_message', 'comment_likes_count');
    	$comments_model->insertMultiple($comments, $cols, $update);
    }
    
    private function storeLikes($likes)
    {
    	$likes_model = new Model_Likes;
    	 //die(print_r($likes));
    	foreach($likes as $like) {
    		$temp		= explode('_',$this->post_id);
    		$type 		= count($temp); // 1-album or photo, 2-post, 3-comment
    	
    		$likes[] = array(
    				'facebook_user_id'      => $like->id,
    				'fanpage_id'            => $this->fanpage->fanpage_id,
    				'post_id'				=> $this->post_id,
    				'post_type'				=> $type
    		);
    
    	}
    	
    	$cols = array('fanpage_id', 'post_id', 'facebook_user_id', 'post_type');
    	$update = array('post_type');
    	$likes_model->insertMultiple($likes, $cols, $update);
    }
    
    private function storeAlbums($albums)
    {	
        foreach ($albums as $album) {

        	$created = new Zend_Date($album->created_time);
        	$updated = new Zend_Date($album->updated_time);
        	
        	$rows[] = array(
        			'album_id'				=> $album->id,
        			'fanpage_id'			=> $this->fanpage->fanpage_id,
        			'facebook_user_id'		=> $album->from->id,
        			'album_name'			=> $album->name,
        			'album_desription'		=> isset($album->description) ? $album->description : '',
        			'album_location'		=> isset($album->location) ? $album->location : '',
        			'album_link'			=> $album->link,
        			'album_cover_photo_id'	=> isset($album->cover_photo) ? $album->cover_photo : '',
        			'album_photo_count'		=> isset($album->count) ? $album->count : 0,
        			'album_type'			=> $album->type,
        			'updated_time'			=> $updated->toString(Zend_Date::ISO_8601),
        			'created_time'			=> $created->toString(Zend_Date::ISO_8601)		
        	);

            Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'photos', 'since', 0, $album->id));
            /* DOESN'T WORK
            if(isset($album->likes->data)){
            
            	Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'likes', 'since' , 0, $album->id));
            }

            if(isset($album->comments->data)){
            	
            	Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'comments', 'since'r, 0, $album->id));
            }
            */
        }

        $cols = array('album_id', 'fanpage_id', 'facebook_user_id', 'album_name', 'album_description', 'album_location', 'album_link', 'album_cover_photo_id', 'album_photo_count', 'album_type', 'updated_time', 'created_time');
        $update = array('album_name', 'album_description', 'album_cover_photo_id', 'album_photo_count', 'updated_time');
        
        $albums_model = new Model_Albums;
        $albums_model->insertMultiple($rows, $cols, $update);
    }

    private function storePhotos($photos)
    {
        //currenty not storing place, tags

        foreach($photos as $photo) {
        	
        	$created = new Zend_Date($photo->created_time);
        	$updated = new Zend_Date($photo->updated_time);
        	
            $rows[] = array(
                'photo_id'          => $photo->id,
                'fanpage_id'        => $this->fanpage->fanpage_id,
            	'facebook_user_id'	=> $photo->from->id,
                'photo_album_id'    => $this->album_id,
                'photo_source'      => $photo->source,
                'photo_caption'     => isset($photo->name) ? $photo->name : '',
            	'photo_picture'		=> $photo->picture,
            	'photo_position'	=> $photo->position,
                'photo_width'       => $photo->width,
                'photo_height'      => $photo->height,
                'updated_time'      => $created->toString(Zend_Date::ISO_8601),
                'created_time'      => $updated->toString(Zend_Date::ISO_8601)
            );
            
            if(isset($photo->tags->data)){
            	
            	foreach($photo->tags->data as $tag) {
            		//$fans[] = $tag->id;
            		$created = new Zend_Date($tag->created_time);
            		
            		$tags[] = array(
            				'fanpage_id'        => $this->fanpage->fanpage_id,
            				'facebook_user_id'	=> isset($tag->id) ? $tag->id : '',
            				'facebook_user_name'	=> $tag->name,
            				'photo_id'				=> $photo->id,
            				'tag_position_x'		=> $tag->x,
            				'tag_position_y'		=> $tag->y,
            				'created_time'			=> $created->toString(Zend_Date::ISO_8601)
            		);
            	}
            }
           /* DOESN'T WORK 
         	
         	if(isset($photo->likes->data)){
            	//die("here");
            	Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'likes', $direction, $timestamp, $photo->id));
            }
            if(isset($photo->comments->data)){
            	
            	Collector::Run('facebook', 'fetch', array($this->fanpage->fanpage_id, 'comments', $direction, $timestamp, $photo->id));
            }*/
            
        }  

        $cols = array('photo_id', 'fanpage_id', 'photo_album_id', 'photo_source', 'photo_caption', 'photo_width', 'photo_height', 'updated_time', 'created_time');
        $update = array('photo_source', 'photo_caption', 'updated_time');

        $photos_model = new Model_Photos;
        $photos_model->insertMultiple($rows, $cols, $update);
        
        if (isset($tags)) {
        	$cols = array('fanpage_id','facebook_user_id', 'facebook_user_name', 'photo_id', 'tag_position_x', 'tag_position_y', 'created_time');
        	$update = array('tag_position_x', 'tag_position_y');
        
        	$tags_model = new Model_Tags;
        	$tags_model->insertMultiple($tags, $cols, $update);
        }
    }

    private function storeFans($fans)
    {

        foreach($fans as $fan) {
            $client = new Zend_Http_Client;
            $client->setUri('https://graph.facebook.com/' . $fan);
            $client->setMethod(Zend_Http_Client::GET);
            $client->setParameterGet('access_token', $this->fanpage->access_token);
            //$client->setParameterGet('fields', 'id,username,link,first_name,last_name,email,birthday,gender,locale,languages');
            
            $response = $client->request();
            $data = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);

            if (isset($data->languages)) {
                foreach($data->languages as $language) {
                    $lang[] = $language->name;
                }
            } else {
                $lang = array();
            }

            $rows[] = array(
                'facebook_user_id'      => $data->id,
                'fanpage_id'            => $this->fanpage->fanpage_id,
                'fan_name'              => $data->name,
                'fan_user_avatar'       => isset($data->picture) ? $data->picture : sprintf('https://graph.facebook.com/%s/picture', $data->id),
                'fan_location'          => isset($data->location) ? $data->location->name : '',
                'fan_gender'            => isset($data->gender) ? $data->gender : '',
                'fan_locale'            => isset($data->locale) ? $data->locale : '',
                'fan_lang'              => implode(',', $lang)
            );
        }

        $cols = array('facebook_user_id', 'fanpage_id', 'fan_name', 'fan_user_avatar', 'fan_location', 'fan_gender', 'fan_locale', 'fan_lang');
        $update = array('fan_user_avatar', 'fan_location', 'fan_gender', 'fan_locale', 'fan_lang');

        $fans_model = new Model_Fans;
        $fans_model->insertMultiple($rows, $cols, $update);
    }
}