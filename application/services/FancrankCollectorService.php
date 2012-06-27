<?php
class Service_FancrankCollectorService {
	protected $_facebookGraphAPIUrl;
	protected $_fanpageId;
	protected $_accessToken;
	protected $_type;
	
	public function __construct() {
		$this->_facebookGraphAPIUrl = 'https://graph.facebook.com/';
		$args = func_get_args();
		$argsCount = func_num_args();
		if (method_exists($this, $constructor ='__construct' .$argsCount)) {
			call_user_func_array(array($this, $constructor), $args);
		}else {
			throw new Exception('NO CONSTRUCTOR FOUND: ' . get_class() . $constructor, NULL, NULL);
		}
	}
	
	function __construct4($url, $fanpage_id, $access_token, $type)
	{
		if($url === null) {
			$this->_facebookGraphAPIUrl = 'https://graph.facebook.com/';
		}else {
			$this->_facebookGraphAPIUrl = $url;
		}
		
		if(empty($fanpage_id) || empty($access_token) || empty($type)) {
			throw new Exception('Invalid Arguments: ' . get_class(), NULL, NULL);
		}
		
		$this->_fanpageId = $fanpage_id;
		$this->_accessToken = $access_token;
		$this->_type = $type;
	}
	
	public function collectFanpageInitInfo() {
		$meUrl = $this->_facebookGraphAPIUrl . $this->_fanpageId;
		$pageProfile = $this->httpCurl($meUrl, array('access_token'=>$this->_accessToken), 'get');
		$fdb = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		$result = $fdb->saveFanpage(json_decode($pageProfile));
		//Zend_Debug::dump($result);
	}
	
	public function fetchFanpageInfo($since=0) {
		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/feed?access_token=' .$this->_accessToken;
		$posts = array();

		$this->getPostsRecursive($url, 3, 200, $posts);
		//Zend_Debug::dump($posts);
		
		if(empty($posts)) {
			return;
		}

		$postLikeList = array();
		$this->getLikesFromMyPostRecursive($posts, $this->_fanpageId, $this->_accessToken, 0, $postLikeList);

		$commentsList = array();
		$this->getQueryRecursive($posts, 'comments', $this->_fanpageId, $this->_accessToken, 0, $commentsList);
		//get all the likes from all comments
		//Zend_Debug::dump($commentsList);
		
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$this->_fanpageId .'/albums?access_token=' .$this->_accessToken;
		$albumsList = array();
		$this->getFromUrlRecursive($url, 3, 200, $albumsList);
		//Zend_Debug::dump($albumsList);
		
		//get all photos recursively
		$photoList = array();
		$this->getQueryRecursive($albumsList, 'photos', $this->_fanpageId, $this->_accessToken, 0, $photoList);
		//Zend_Debug::dump($photoList);
		
		$likeList = array();
		$albumLikesList = $this->getLikesFromMyPhotos($albumsList, $this->_fanpageId);
		$photoLikesList = $this->getLikesFromMyPhotos($photoList, $this->_fanpageId);
		
		$commentsList = array_merge($commentsList, $this->getCommentsFromPhotos($albumsList, $this->_fanpageId), $this->getCommentsFromPhotos($photoList, $this->_fanpageId));
		//get all the likes from all comments
		$commentLikeList = array();
		$this->getLikesFromCommentsRecursive($commentsList, $this->_fanpageId, $this->_accessToken, 0, $commentLikeList);
		
		$allLikesList = array_merge($postLikeList, $commentLikeList, $albumLikesList, $photoLikesList);
		
		$fdb = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		
		$db = $fdb->getDefaultAdapter();
		//$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		//$db = Zend_Db::factory($config->resources->db);
		$db->beginTransaction();
		
		try {
			$fdb->savePosts($posts);
			
			$fdb->saveAlbums($albumsList);
			
			$fdb->savePhotos($photoList);
			
			$fdb->saveComments($commentsList);
			
			$fdb->saveLikes($allLikesList);
			
			$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
			//Zend_Debug::dump($fansIdsList);
			
			$facebookUsers = $this->getFansList($fansIdsList, $this->_accessToken);
			$result = $fdb->saveFans($facebookUsers);
			//Zend_Debug::dump($result);
			
			$db->commit();			
		} catch (Exception $e) {
			$db->rollBack();
		}

	}
	
	/*
	 * This method will retrieve all posts from giving url recursively
	*
	* @param string $url a next page url
	* @param int numbers of recursive call
	* @param int numbers of limit posts per recursion level
	* @param mixed $result a callback result
	* 
	*/
	private function getPostsRecursive($url, $level=2, $limit, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		$params['limit'] = $limit;
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n'; exit();
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			$url = !empty($response->paging->next) ? $response->paging->next : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}
			$this->getPostsRecursive($url, $level, $limit, $result);
		} catch (Exception $e) {
			return array();
		}
	}

	private function getLikesFromCommentsRecursive($posts, $fanpageId, $access_token, $offset=0, &$likesList) {
		if(empty($posts) || $offset > 100) {
			return null;
		}
		$postIds = array();
		$extraPosts = array();
		$extraPostIds = array();
		foreach ($posts as $post) {
			//if(!empty($post->likes->count) && $post->likes->count >= 1 && preg_match("/{$post->from->id}_/", $post->id)) {
			if(!empty($post->like_count) && $post->like_count >= 1) {
				$postIds[] = $post->id;
				if($post->like_count > 25 + $offset) {
					$extraPostIds[] = $post->id;
					$extraPosts [] = $post;
				}
			}
		}
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//Zend_Debug::dump($postIdsGroup);
		try {
			$results = array();
			//note: we could implement this loop with multi thread for optimization later on.
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>'likes', 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
		//$posts = json_decode($result);
		//Zend_Debug::dump($results); exit();
		foreach ($results as $groupKey => $result) {
			foreach(json_decode($result) as $key=>$likes) {
				if($likes->code === 200 && !empty($likes->body)) {
					$likes = json_decode($likes->body);
					foreach ($likes->data as $like) {
						//echo $like->id .' ' .$like->name . 'likes ' .$postIdsGroup[$groupKey][$key] .'<br />';
						$post_type = 'post';
						if(substr_count($postIdsGroup[$groupKey][$key], '_') === 2) {
							$post_type = 'comments';
						}
						$likesList[] = array(
								'fanpage_id'        => $fanpageId,
								'post_id'           => $postIdsGroup[$groupKey][$key],
								'facebook_user_id'  => $like->id,
								'post_type'         => 'comment'
						);
					}
				}
			}
		}

		$this->getLikesFromCommentsRecursive($extraPosts, $fanpageId, $access_token, $offset+25, $likesList);
	}
	
	private function getLikesFromMyPostRecursive($posts, $fanpageId, $access_token, $offset=0, &$likesList) {
		if(empty($posts) || $offset > 100) {
			return null;
		}
		$postIds = array();
		$extraLikesPosts = array();
		$extraLikesOnPostIds = array();
		foreach ($posts as $post) {
			if(!empty($post->likes->count) && $post->likes->count >= 1) {
				$postIds[] = $post->id;
				if( $post->likes->count > 25 + $offset ) {
					$extraLikesOnPostIds[] = $post->id;
					$extraLikesPosts [] = $post;
				}
			}
		}
	
		//Zend_Debug::dump($extraLikesOnPostIds);
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//Zend_Debug::dump($postIdsGroup);
	
		try {
			$results = array();
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>'likes', 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
	
		foreach ($results as $groupKey => $result) {
			foreach(json_decode($result) as $key=>$likes) {
				if($likes->code === 200 && !empty($likes->body)) {
					$likes = json_decode($likes->body);
					foreach ($likes->data as $like) {
						//echo $like->id .' ' .$like->name . 'likes ' .$postIdsGroup[$groupKey][$key] .'<br />';
						$likesList[] = array(
								'fanpage_id'        => $fanpageId,
								'post_id'           => $postIdsGroup[$groupKey][$key],
								'facebook_user_id'  => $like->id,
								'post_type'         => 'post'
						);
					}
				}
			}
		}
	
		$this->getLikesFromMyPostRecursive($extraLikesPosts, $fanpageId, $access_token, $offset+25, $likesList);
	}
	
	private function getQueryRecursive($posts, $queryType, $fanpageId, $access_token, $offset=0, &$resultList) {
		if(empty($posts) || $offset > 100) {
			return null;
		}
		$postIds = array();
		$extraPosts = array();
		$extraPostIds = array();
		foreach ($posts as $post) {
			//if(!empty($post->likes->count) && $post->likes->count >= 1 && preg_match("/{$post->from->id}_/", $post->id)) {
			if(!empty($post->$queryType->count) && $post->$queryType->count >= 1 ||
					$queryType === 'photos' && !empty($post->count) && $post->count >= 1) {
				$postIds[] = $post->id;
				if($queryType === 'photos' && $post->count > 25 + $offset || $queryType !== 'photos' && $post->$queryType->count > 25 + $offset) {
					$extraPostIds[] = $post->id;
					$extraPosts [] = $post;
				}
			}
		}
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//Zend_Debug::dump($postIdsGroup);
	
		try {
			$results = array();
			//note: we could implement this loop with multi thread for optimization later on.
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>$queryType, 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
	
		//$posts = json_decode($result);
		//Zend_Debug::dump($results); exit();
		foreach ($results as $groupKey => $result) {
			foreach(json_decode($result) as $key=>$values) {
				if($values->code === 200 && !empty($values->body)) {
					$values = json_decode($values->body);
					if(!empty ($values->data)) {
						foreach ($values->data as $value) {
							//echo $value->id .' ' .$queryType .'postId: '.$postIdsGroup[$groupKey][$key] .'<br />';
							$resultList[] = $value;
						}
					}
				}
			}
		}

		$this->getQueryRecursive($extraPosts, $queryType, $fanpageId, $access_token, $offset+25, $resultList);
	}
	
	private function getFromUrlRecursive($url, $level = 5, $limit=5, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		$params['limit'] = $limit;
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n';
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			$url = !empty($response->paging->next) ? $response->paging->next : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}
			$this->getPostsRecursive($url, $level, $limit, $result);
		} catch (Exception $e) {
			return array();
		}
	}
	
	private function getCommentsFromPhotos($photos, $fanpageId) {
		$commentsList = array();
		foreach ($photos as $photo) {
			if(! empty($photo->comments) ) {
				foreach ($photo->comments->data as $comment) {
					$commentsList[] = $comment;
				}
			}
		}
	
		return $commentsList;
	}
	
	private function getLikesFromMyPhotos($photos, $fanpageId) {
		$likesList = array();
		foreach ($photos as $photo) {
			if(! empty($photo->likes->data) ) {
				foreach ($photo->likes->data as $like) {
					$likesList[] = array(
							'fanpage_id'        => $fanpageId,
							'post_id'           => $photo->id,
							'facebook_user_id'  => $like->id,
							'post_type'         => 'photo'
					);
				}
			}
		}
	
		return $likesList;
	}
	
	private function arrayToGroups($source, $pergroup) {
		$grouped = array ();
		$groupCount = ceil ( count ( $source ) / $pergroup );
		$queue = $source;
		for($r = 0; $r < $groupCount; $r++) {
			array_push ( $grouped, array_splice ( $queue, 0, $pergroup ) );
		}
		return $grouped;
	}
	
	//$data is array of json objects
	private function fansIdCollector() {
		$fansIdList = array();
		foreach(func_get_args() as $data) {
			foreach($data as $k => $v){
				if(!empty($v->from->id)) {
					$fansIdList[] = $v->from->id;
				}else if (!empty($v->facebook_user_id)) {
					$fansIdList[] = $v->facebook_user_id;
				}
			}
		}
		 
		return array_unique($fansIdList);
	}
	
	private function getFansList($fansIdsList, $access_token) {
		$results = array();
		$fansIdsGroup = $this->arrayToGroups($fansIdsList, 500);
		//Zend_Debug::dump($fansIdsGroup);
		try {
			$results = array();
			foreach ($fansIdsGroup as $fansIds) {
				$batchQuery = $this->batchQueryBuilder($fansIds, $access_token);
				foreach (json_decode($this->httpCurl($batchQuery)) as $fans) {
					if(empty($fans->first_name) || empty($fans->id)) continue;
					$results[] = $fans;
				}
			}
			return $results;
		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
	}
	
	private function batchQueryBuilder($ids, $access_token) {
		$params = 'ids=' .implode(',', $ids);
		return 'https://graph.facebook.com/?' .$params .'&access_token=' .$access_token;
	}
	
	private function httpCurl($url, $params=null, $method=null) {
		$ch = curl_init();
		switch (strtolower($method)) {
			case 'get':
				curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
				curl_setopt($ch, CURLOPT_POST, false);
				break;
			case 'post':
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			default:
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, false);
				break;
		}
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}

?>