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
            case 'comments':
            	$url = 'https://graph.facebook.com/' . $extra . '/comments'; //$extra here is the post id
            	$fields = array();
            	break;
            case 'likes':
            	$url = 'https://graph.facebook.com/' . $extra . '/likes'; //$extra here is the post id or the comment id
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
        }

        $cols = array('post_id', 'facebook_user_id', 'fanpage_id', 'message', 'type', 'created_time', 'updated_time', 'comments_count');
        $update = array('updated_time', 'comments_count');
        $posts_model->insertMultiple($posts, $cols, $update);

        if (isset($medias)) {
            $cols = array('post_id', 'post_type', 'post_picture', 'post_link', 'post_source', 'post_name', 'post_caption', 'post_description', 'post_icon');
            $update = array('post_description');
            $posts_media_model->insertMultiple($medias, $cols, $update);
        }

            /*static $activity;
    		//echo $key."<br/>";
			//print_r($value);
    		if(is_object($value)) { //[0], [1], [2], etc

    			foreach($value as $key2 => $value2){
    					 
    				if(is_object($value2)){ //[from], [comments], [likes], [privacy], [actions]
    	
    					if($key2 ==="from") {
    						
    						foreach($value2 as $fromkey => $fromvalue){
    	
    							if($fromkey === "name"){
    								
    								$activity["from".$fromkey]=$fromvalue;
    									
    							}else{
    								$activity["from".$fromkey]=$fromvalue;
    							}
    						}
    					}else if($key2==="likes"){
    	
    						$activity["likescount"]=$value2->count;
    							
    						if($activity["likescount"]>0){
    							
    							//run the fetch method for likes
    							//grab_inner("walllikes",$activity['activityid'], $db_name);
    							//Collector::Run('facebook', 'fetch', array($this->source->source_id, 'likes', $direction, $timestamp, $activity->id));
    	
    						}
    							
    					}else if($key2 === "comments"){
    							
    						$activity["commentscount"]=$value2->count;
    							
    						if($activity["commentscount"]>0){
    							
    							//run the fetch method for comments
    							//grab_inner("comments",$activity['activityid'], $db_name);
    							//Collector::Run('facebook', 'fetch', array($this->source->source_id, 'comments', $direction, $timestamp, $activity->id));
    	
    						}
    					}else if($key2==="privacy"){
    	
    						foreach($value2 as $privkey => $privvalue){
    	
    							$activity["privacy".$privkey]=$privvalue;
    								
    						}
    	
    					}else if($key2==="application"){
    	
    						foreach($value2 as $appkey => $appvalue){
    	
    							$activity["application".$appkey]=$appvalue;
    								
    						}
    	
    					}
    	
    				}else{//[id], [message], [type], etc
    						
    					if ($key2=== 'created_time' || $key2=== 'updated_time') {
    	
    						$activity["activity".$key2]=strtotime($value2);
    							
    					}else if($key2=== 'message'|| $key2==='name'|| $key2==='description'|| $key2==='caption'){
    	
    						$activity["activity".$key2]=$value2;
    						//echo "message: ".$value."<br/>";
    	
    						$activity["activity".$key2]=$value2;
    					}
    				}
    			}
    		}

    		//die(print_r($activity));
    		
    		$db_keys=array('activityid', 'fromname', 'fromid', 'fromcategory', 'activitymessage', 'privacydescription', 'privacyvalue', 'activitytype', 'activitycreated_time', 'activityupdated_time', 'applicationname', 'applicationid', 'commentscount', 'likescount', 'activitypicture', 'activitylink', 'activitysource', 'activityname', 'activitycaption', 'activityicon', 'activitydescription');
    		foreach($db_keys as $key => $value){
    			if(!array_key_exists($key, $activity)){

    				$activity[$key]=NULL;
    			}
    		}
    		
    		$posts_model = new Model_Posts;
    		$activity_id = $activity['activityid'];
    		$updated_time = $activity['activityupdated_time'];
    		$postExists = $posts_model->checkPostExists($activity_id);
    		
    		if($postExists){
    			
    			//this post exists in the database
    			//check if the updated time is old
    			$postTime = $posts_model->checkPostUpdatedTime($activity_id, $updated_time);
    			
    			if(!$postTime){
    				
    				//post time has changed since last fetch
    				//update the values in the db
    				$postUpdateDB = $posts_model->updateExistingPost($activity);
    			}
    			
    		}else{
    			
    			//this post does not exist - enter it into the db
    			
    			$dividePost = explode('_', $activity_id);
    			$fanpage_id = $dividePost[0];
    			
    			$postInsertDB = $posts_model->insertPost($fanpage_id, $activity);
    		}
			
    		//reset the activity array
    		$activity=array();
    	} //end inserting posts
    	*/
    }
    
    private function storeComments($comments)
    {
    	static $facebookComments;
    	
    	foreach($comments[data] as $key => $value){
    	
    		if(is_array($value)){
    				
    			foreach($value as $key2 => $value2){
    					
    				if(is_array($value2)){ //this is the FROM inner array
    					
    					foreach($value2 as $key3=>$value3){
    						
    						if($key3==='name'){
    							$facebookComments[$key2.$key3]=$value3;
    	
    						}else{
    							$facebookComments[$key2.$key3]=$value3;
    						}
    					}
    				}else{ //this is for fan comments i.e. no inner FROM Category
    					if ($key2=== 'created_time' || $key2=== 'updated_time') {
    							
    						$facebookComments[$key2]=strtotime($value2);
    	
    					}else if($key2=== 'message'|| $key2==='name'){
    							
    						$facebookComments[$key2]=$value2;
    	
    					}else{
    						$facebookComments[$key2]=$value2;
    						//$commentsinner[fromid]=NULL;
    						$facebookComments[fromcategory]=NULL;
    	
    					}
    				}
    			}
    		}
    		$divideCommentID=explode("_", $facebookComments['id']);
    		$fanpage_id = $divideCommentID[0];
    		$post_id = $divideCommentID[0] . '_' . $divideCommentID[1];
    		
    		//insert comments into DB
    		
    		//get likes for comment now
    		if($facebookComments['likes'] > 0){
    				
    			//grab_inner("walllikes", $commentsinner['id']);
    			//Collector::Run('facebook', 'fetch', array($this->source->source_id, 'likes', $direction, $timestamp, $activity->id));
    				
    		}
    		$facebookComments=array();
    	}
    }
    
    private function storeLikes($likes)
    {
    	static $facebookLikes;
    	//store likes
    	foreach($likes[data] as $key => $value){
    	
    		if(is_array($value)){
    				
    			foreach($value as $key2 => $value2){
    				if($key2==='name'){
    					$facebookLikes[$key2]=mysql_real_escape_string($value2);
    	
    				}
    				else{
    					$facebookLikes[$key2]=$value2;
    				}
    			}
    		}
    	
    		$primarykey= $post."_".$facebookLikes[id];
    		$post_type_temp=explode("_", $post);
    		
    		//reset our variable
    		$facebookLikes = array();
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
        
    }

    private function storeFans($fans)
    {
        
    }
    
}
