<?php
/**
 * Francrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fancrank OEM license
 *
 * @category    service
 * @copyright   Copyright (c) 2012 Francrank
 * @license
 */
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
	
	public function fullScanFanpage() {
		$start = time();
		$this->collectFanpageInitInfo();
		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/feed?access_token=' .$this->_accessToken .'&since=0';
		$posts = array();
		
		$this->getPostsRecursive($url, 5, 1000, $posts);
		//Zend_Debug::dump($posts);
		
		if(empty($posts)) {
			return;
		}
		
 		$postLikeList = $this->getLikesFromMyPost($posts, 2, 1000);
 		//Zend_Debug::dump($postLikeList);
		
 		$commentsList = $this->getCommentsFromPost($posts, 5, 1000);
 		//Zend_Debug::dump($commentsList);
		
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$this->_fanpageId .'/albums?access_token=' .$this->_accessToken;
		$albumsList = array();
		$this->getFromUrlRecursive($url, 2, 1000, $albumsList);
		//Zend_Debug::dump($albumsList);
		
		$photoList = array();
		$photoList = $this->getPhotosFromAlbum($albumsList, 2, 1000);
		//Zend_Debug::dump($photoList);
		$albumLikesList = $this->getLikesFromMyAlbums($albumsList);
		//Zend_Debug::dump($albumLikesList);
		$photoLikesList = $this->getLikesFromMyAlbums($photoList);
		//Zend_Debug::dump($photoLikesList);
		$albumCommentList = $this->getCommentsFromMyAlbum($albumsList);
		//Zend_Debug::dump($albumCommentList);
		$photoCommentList = $this->getCommentsFromMyAlbum($photoList);
		//Zend_Debug::dump($photoCommentList);
		$commentsList = array_merge($commentsList, $albumCommentList, $photoCommentList);
		//Zend_Debug::dump($commentsList);
		$commentLikeList = $this->getLikesFromMyComment($commentsList);

		$allLikesList = array_merge($postLikeList, $commentLikeList, $albumLikesList, $photoLikesList);
		
		$fdb = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		
		$db = $fdb->getDefaultAdapter();
		
		echo '<br/>total likes for ' .count($posts) . ' posts: ' .count($postLikeList);
		echo '<br/>total likes for ' .count($commentsList) . ' comments: ' .count($commentLikeList);
		echo '<br/>total likes for ' .count($albumsList) . ' albums ' .count($albumLikesList);
		echo '<br/>total likes for ' .count($photoList) . ' photos ' .count($photoLikesList);
		echo '<br/>Total likes : ' .count($allLikesList);
		
		$db->beginTransaction();
		
		try {
			$fdb->savePosts($posts);
			
			$fdb->saveAlbums($albumsList);
			
			$fdb->savePhotos($photoList);
			
			$fdb->saveComments($commentsList);
			
			$fdb->saveLikes($allLikesList);
			
			$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
			
			$facebookUsers = $this->getFansList($fansIdsList, $this->_accessToken);
			$result = $fdb->saveFans($facebookUsers);
			
			$db->commit();
			$stop = time() - $start;
			echo '<br />total execution time: ' .$stop;
	
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$collectorLogger->log ( sprintf ( 'Full Scan fail: %s',  $e->getMessage ()), Zend_log::ERR);
			$db->rollBack();
		}
	}
	
	public function updateFanpage($since=null, $until=null) {
		$start = time();

		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/feed?access_token=' .$this->_accessToken .'&until=' .$until .'&since=' .$since;
		$posts = array();
		//echo $url; exit();
		$this->getPostsByTimeRange($url, 10, 100, $posts);
		Zend_Debug::dump($posts); //exit();
		
		if(empty($posts)) {
			return;
		}

 		$postLikeList = $this->getLikesFromMyPost($posts, 2, 1000);
 		//Zend_Debug::dump($postLikeList);
		
 		$commentsList = $this->getCommentsFromPost($posts, 5, 1000);
 		//Zend_Debug::dump($commentsList);
 		
 		$pointResult = $this->calculatePostPoints($posts, $commentsList);
 		
  		//Zend_Debug::dump($pointResult); exit();
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$this->_fanpageId .'/albums?access_token=' .$this->_accessToken .'&since=' .$since;
		$albumsList = array();
		$this->getFromUrlRecursive($url, 2, 1000, $albumsList);
		//Zend_Debug::dump($albumsList);
		
		$albumLikesList = $this->getLikesFromMyAlbums($albumsList);
		//Zend_Debug::dump($albumLikesList);

		$albumCommentList = $this->getCommentsFromMyAlbum($albumsList);
		//Zend_Debug::dump($albumCommentList);

		$pointResult = $this->calculateAlbumPoints($pointResult, $albumsList, $albumCommentList);
		//Zend_Debug::dump($pointResult);

		$photoList = array();
		$photoList = $this->getPhotosFromAlbum($albumsList, 2, 1000);
		//Zend_Debug::dump($photoList);
		
		$photoLikesList = $this->getLikesFromMyAlbums($photoList);
		//Zend_Debug::dump($photoLikesList);

		$photoCommentList = $this->getCommentsFromMyAlbum($photoList);
		//Zend_Debug::dump($photoCommentList);
		
		$pointResult = $this->calculatePhotoPoints($pointResult, $photoList, $photoCommentList);
		//Zend_Debug::dump($pointResult);
		
		$commentsList = array_merge($commentsList, $albumCommentList, $photoCommentList);
		//Zend_Debug::dump($commentsList);
		
		$pointResult = $this->calculateCommentPoints($pointResult, $commentsList);
		
		$commentLikeList = $this->getLikesFromMyComment($commentsList);

		$allLikesList = array_merge($postLikeList, $commentLikeList, $albumLikesList, $photoLikesList);
		
		$pointResult = $this->calculateLikesPoints($pointResult, $allLikesList);
		
		Zend_Debug::dump($pointResult);
		
		$fdb = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		
		$db = $fdb->getDefaultAdapter();
		
		echo '<br/>total likes for ' .count($posts) . ' posts: ' .count($postLikeList);
		echo '<br/>total likes for ' .count($commentsList) . ' comments: ' .count($commentLikeList);
		echo '<br/>total likes for ' .count($albumsList) . ' albums ' .count($albumLikesList);
		echo '<br/>total likes for ' .count($photoList) . ' photos ' .count($photoLikesList);
		echo '<br/>Total likes : ' .count($allLikesList);
		
		//Zend_Debug::dump($allLikesList); exit();
		$db->beginTransaction();
		
		try {
			$fdb->savePosts($posts);

			$fdb->saveAlbums($albumsList);
			
			$fdb->savePhotos($photoList);
			
			$fdb->saveComments($commentsList);

			$fdb->saveLikes($allLikesList);
			
			$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
			
			$facebookUsers = $this->getFansList($fansIdsList, $this->_accessToken);
			
			$result = $fdb->saveAndUpdateFans($facebookUsers, $pointResult);
			
			// point checking
			Zend_Debug::dump($result);
			
			$db->commit();
			$stop = time() - $start;
			echo '<br />total execution time: ' .$stop;
	
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$collectorLogger->log ( sprintf ( 'Updated Scan fail: %s',  $e->getMessage ()), Zend_log::ERR);
			$db->rollBack();
		}
	}
	
	public function collectFanpageInsight($level=1, $type=null) {
		$start = time();
		$range = 7776000;
		$since = $start - $range;
		$until = $start;

		$params = array('access_token' => $this->_accessToken,
						'since' => $since,
						'until' => $until
						);
		$url = null;
		$baseUrl = $this->_facebookGraphAPIUrl . $this->_fanpageId;
		switch ($type) {
			case 'likes' 	: $url =  $baseUrl .'/insights/page_fans/lifetime' . '?' .http_build_query($params); break;
			case 'comments' : $url =  $baseUrl .'/insights/page_like_adds/day' . '?' .http_build_query($params); break;
			case 'posts' 	: $url =  $baseUrl .'/insights/page_discussions/day' . '?' .http_build_query($params); break;
			case 'story'    : $url =  $baseUrl .'/insights/page_story_adds/day' . '?' .http_build_query($params); break;
			default			: break;
		}
		
		$insights = array();		
		$this->getInsightsRecursive($url, $level, null, $insights);
		return $insights;
	}
	
	private function getInsightsRecursive($url, $level, $limit=null, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		if($limit) {
			$params['limit'] = $limit;			
		}
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n'; exit();
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			if(!empty($response->error)) throw new Exception($response->error->message);
			$url = !empty($response->paging->previous) ? $response->paging->previous : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$response->data, (array)$result);
			}
			$this->getInsightsRecursive($url, $level, $limit, $result);
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$msg = sprintf('Unable to fetch feed from fanpage %s. Error Message: %s ', $this->_fanpageId, $e->getMessage ());
			$collectorLogger->log($msg , Zend_log::ERR );
			throw new Exception($msg);
		}
	}
	
	public function getPostsRecursive($url, $level=2, $limit, &$result) {
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
			if(!empty($response->error)) throw new Exception($response->error->message);
			$url = !empty($response->paging->next) ? $response->paging->next : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}
			$this->getPostsRecursive($url, $level, $limit, $result);
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$msg = sprintf('Unable to fetch feed from fanpage %s. Error Message: %s ', $this->_fanpageId, $e->getMessage ());
			$collectorLogger->log($msg , Zend_log::ERR );
			throw new Exception($msg);
		}
	}
	
	private function getPostsByTimeRange($url, $level=2, $limit, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		$params['limit'] = $limit;
		echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n';
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			if(!empty($response->error)) throw new Exception($response->error->message);
			$url = !empty($response->paging->previous) ? $response->paging->previous : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}
			$this->getPostsByTimeRange($url, $level, $limit, $result);
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$msg = sprintf('Unable to fetch feed from fanpage %s. Error Message: %s ', $this->_fanpageId, $e->getMessage ());
			$collectorLogger->log($msg , Zend_log::ERR );
			throw new Exception($msg);
		}
	}
	
	private function getCommentsFromPost($posts, $level, $limit=1000) {
		//Zend_Debug::dump($posts);
		$results = array();
		foreach ($posts as $post) {
			$commentCount = 1;
			$hasMore = false;
			if(!empty($post->comments->data)) {
				$commentCount = count($post->comments->data);
			}
			if(!empty($post->comments->count) && $post->comments->count > $commentCount) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $post->id .'/comments?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, $level, $limit, $result);
		
				foreach ($result as $comment) {
					$results[] = $comment;
				}
				$hasMore = true;
			}
			if(! $hasMore && !empty($post->comments->data)) {
				foreach ($post->comments->data as $comment) {
					$results[] = $comment;
				}
			}
		}
		return $results;
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
				if(!empty($likes->code) && $likes->code === 200 && !empty($likes->body)) {
					$likes = json_decode($likes->body);
					foreach ($likes->data as $like) {
						//echo $like->id .' ' .$like->name . 'likes ' .$postIdsGroup[$groupKey][$key] .'<br />';
						$post_type = 'post';
						if(substr_count($postIdsGroup[$groupKey][$key], '_') === 2) {
							$post_type = 'comment';
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
			if(!empty($post->likes->count) && $post->likes->count >= 0) {
				$postIds[] = $post->id;
				if( $post->likes->count > 25 + $offset ) {
					$extraLikesOnPostIds[] = $post->id;
					$extraLikesPosts [] = $post;
				}
			}
		}
	
		echo '<br/>' .$offset + 25 .'<br/>';
		Zend_Debug::dump($extraLikesOnPostIds);
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		Zend_Debug::dump($postIdsGroup);
		exit();
	
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
				if(!empty($likes->code) && $likes->code === 200 && !empty($likes->body)) {
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
	
	private function getLikesFromMyPost($posts, $level=5, $limit=1000) {
		//Zend_Debug::dump($posts);
		$results = array();
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($posts as $post) {
			$likeCount = 1;
			$hasMoreLike = false;
			if(!empty($post->likes->data)) {
				$likeCount = count($post->likes->data);
			}
			if(!empty($post->likes->count) && $post->likes->count > $likeCount) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $post->id .'/likes?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, $level, $limit, $result);
				
				foreach ($result as $like) {
					$results[] = array('fanpage_id'=>$this->_fanpageId,
										'post_id'=>$post->id,
										'facebook_user_id'=>$like->id,
										'post_type'=>'post',
										'updated_time'=>$update_time);					
				}
				$hasMoreLike = true;
			}
			if(! $hasMoreLike && !empty($post->likes->data)) {
				foreach ($post->likes->data as $like) {
					$results[] = array('fanpage_id'=>$this->_fanpageId,
										'post_id'=>$post->id,
										'facebook_user_id'=>$like->id,
										'post_type'=>'post',
										'updated_time'=>$update_time);
				}
			}
			
		}
		return $results;
	}
	
	private function getPhotosFromAlbum($albums, $level=5, $limit=1000) {
		$results = array();
		foreach ($albums as $album) {
			$result = array();
			$url = $this->_facebookGraphAPIUrl . $album->id .'/photos?access_token=' .$this->_accessToken;
			$this->getFromUrlRecursive($url, $level, $limit, $result);
			foreach ($result as $photo) {
				$results[] = $photo;
			}
			$hasMoreLike = true;
		}
		return $results;
	}
	
	private function getQueryRecursive($posts, $queryType, $fanpageId, $access_token, $offset=0, &$resultList) {
		if(empty($posts) || count($posts) === 1 || $offset > 100) {
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
		Zend_Debug::dump($postIdsGroup);
	
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
				if(!empty($values->code) && $values->code === 200 && !empty($values->body)) {
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
		if(empty($url) || $level === 0) {
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
				//$this->getPostsRecursive($url, $level, $limit, $result);
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
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($photos as $photo) {
			if(! empty($photo->likes->data) ) {
				foreach ($photo->likes->data as $like) {
					$likesList[] = array(
							'fanpage_id'        => $fanpageId,
							'post_id'           => $photo->id,
							'facebook_user_id'  => $like->id,
							'post_type'         => 'photo',
							'updated_time'		=> $update_time
					);
				}
			}
		}
	
		return $likesList;
	}
	
	private function getLikesFromMyAlbums($albums) {
		$likesList = array();
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($albums as $album) {
			if(! empty($album->likes->data) ) {
				
				foreach ($album->likes->data as $like) {
					$likesList[] = array(
							'fanpage_id'        => $this->_fanpageId,
							'post_id'           => $album->id,
							'facebook_user_id'  => $like->id,
							'post_type'         => 'photo',
							'updated_time'		=> $update_time
					);
				}
				
				if(count($album->likes->data) >= 25 && !empty($album->likes->paging->next)) {
					$result = array();
					$url = $album->likes->paging->next;
					$this->getFromUrlRecursive($url, 1, 1000, $result);
					foreach ($result as $like) {
						$likesList[] = array(
								'fanpage_id'        => $this->_fanpageId,
								'post_id'           => $album->id,
								'facebook_user_id'  => $like->id,
								'post_type'         => 'photo',
								'updated_time'		=> $update_time
						);
					}
				}
			}
		}
	
		return $likesList;
	}
	
	private function getLikesFromMyComment($commentList) {
		$likesList = array();
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($commentList as $comment) {
			$post_type = 'photo';
			if(substr_count($comment->id, '_') === 2) {
				$post_type = 'comment';
			}
			if(!empty($comment->like_count) && $comment->like_count >= 1) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $comment->id .'/likes?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, 1, 1000, $result);
				foreach ($result as $like) {
					$likesList[] = array(
							'fanpage_id'        => $this->_fanpageId,
							'post_id'           => $comment->id,
							'facebook_user_id'  => $like->id,
							'post_type'         => $post_type,
							'updated_time'		=> $update_time		
					);
				}
			}
		}
		return $likesList;
	}
	
	private function getCommentsFromMyAlbum($albums) {
		$commentList = array();
		foreach ($albums as $album) {
			if(! empty($album->comments->data) ) {
		
				foreach ($album->comments->data as $comment) {
					$commentList[] = $comment;
				}
		
				if(count($album->comments->data) >= 25 && !empty($album->comments->paging->next)) {
					$result = array();
					$url = $album->comments->paging->next;
					$this->getFromUrlRecursive($url, 1, 1000, $result);
					foreach ($result as $comment) {
						$commentList[] = $comment;
					}
				}
			}
		}
		
		return $commentList;
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
			//echo $e->getMessage();
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
				curl_setopt($ch, CURLOPT_URL, $url);
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
	
	private function calculatePostPoints($posts, $commentsList) {
		$totalPoints = 0;
		$postPointResult = array();
		foreach ($posts as $post) {
			$totalLikePoints = 0;
			$totalCommentPoints = 0;
			$virginity = 0;
			$unique = 0;
			if(!empty($post->from->id) && $this->_fanpageId === $post->from->id) {
				$postTime = new Zend_Date($post->created_time, Zend_Date::ISO_8601);
				foreach ($commentsList as $comment) {
					if(preg_match("/{$post->from->id}_/", $comment->id) && $comment->from->id !== $post->from->id) {
						$commentTime = new Zend_Date($comment->created_time, zend_date::ISO_8601);
						$timeDifferentInMinute = floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
						$multiply = 1;
						switch($timeDifferentInMinute) {
							case ($timeDifferentInMinute < 15) : $multiply = 5; break;
							case ($timeDifferentInMinute < 30) : $multiply = 4; break;
							case ($timeDifferentInMinute < 45) : $multiply = 3; break;
							case ($timeDifferentInMinute < 60) : $multiply = 2; break;
							default: break;
						}
						echo $comment->from->id .'gain comment:' .$multiply*2 .' from post ' .$post->from->id .'<br/>';
						if(isset($postPointResult[$comment->from->id])) {
							$postPointResult[$comment->from->id] = $postPointResult[$comment->from->id] + $multiply*2;
						}else {
							$postPointResult[$comment->from->id] = $multiply*2;
						}
					}
				}
					
				continue;
			}
		
			if(empty($post->likes->count) && empty($post->comments->count)) {
				$totalPoints = -5;
				echo $post->from->id .' lost: ' .$totalPoints .' from ' .$post->id .'<br/>';
			}else {
				$virginity = 4;
				
				if(!empty($post->likes->count) && $post->likes->count >= 1) {
					$totalLikePoints = $post->likes->count;
					$unique = $totalLikePoints;
					echo $post->from->id .' gain like: ' .$totalLikePoints .' from ' .$post->id .'<br/>';
				}
				
				$ownerCommentCount = 0;
				if(!empty($post->comments->count) && $post->comments->count >= 1) {
					foreach ($commentsList as $comment) {
						if(preg_match("/{$post->from->id}_/", $comment->id) && $comment->from->id === $post->from->id) {
							$ownerCommentCount++;
						}
					}
					$totalCommentPoints = ($post->comments->count - $ownerCommentCount) * 2;
					$unique += $post->comments->count - $ownerCommentCount;
					echo $post->from->id .' gain comment: ' .$totalCommentPoints .' from ' .$post->id .'<br/>';
				}
				
				$totalPoints = $virginity + $totalLikePoints + $totalCommentPoints + $unique;
			}
				
			if(isset($postPointResult[$post->from->id])) {
				$postPointResult[$post->from->id] = $postPointResult[$post->from->id] + $totalPoints;
			}else {
				$postPointResult[$post->from->id] = $totalPoints;
			}
		}
		
		return $postPointResult;
	}
	
	private function calculateAlbumPoints($pointResult, $albumsList, $albumCommentList) {
		foreach ($albumsList as $album) {
			$postTime = new Zend_Date($album->created_time, Zend_Date::ISO_8601);
			foreach ($albumCommentList as $comment) {
				if($comment->from->id !== $album->from->id) {
					$commentTime = new Zend_Date($comment->created_time, zend_date::ISO_8601);
					$timeDifferentInMinute = floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
					$multiply = 1;
					switch($timeDifferentInMinute) {
						case ($timeDifferentInMinute < 15) : $multiply = 5; break;
						case ($timeDifferentInMinute < 30) : $multiply = 4; break;
						case ($timeDifferentInMinute < 45) : $multiply = 3; break;
						case ($timeDifferentInMinute < 60) : $multiply = 2; break;
						default: break;
					}
					//echo $comment->from->id .'gain comment:' .$multiply*2 .' from post ' .$album->from->id .'<br/>';
					if(isset($pointResult[$comment->from->id])) {
						$pointResult[$comment->from->id] = $pointResult[$comment->from->id] + $multiply*2;
					}else {
						$pointResult[$comment->from->id] = $multiply*2;
					}
				}
			}
		}
		return $pointResult;
	}
	
	private function calculatePhotoPoints($pointResult, $photoList, $photoCommentList) {
		foreach ($photoList as $photo) {
			$postTime = new Zend_Date($photo->created_time, Zend_Date::ISO_8601);
			foreach ($photoCommentList as $comment) {
				if($comment->from->id !== $photo->from->id) {
					$commentTime = new Zend_Date($comment->created_time, zend_date::ISO_8601);
					$timeDifferentInMinute = floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
					$multiply = 1;
					switch($timeDifferentInMinute) {
						case ($timeDifferentInMinute < 15) : $multiply = 5; break;
						case ($timeDifferentInMinute < 30) : $multiply = 4; break;
						case ($timeDifferentInMinute < 45) : $multiply = 3; break;
						case ($timeDifferentInMinute < 60) : $multiply = 2; break;
						default: break;
					}
					echo $comment->from->id .'gain comment:' .$multiply*2 .' from post ' .$photo->from->id .'<br/>';
					if(isset($pointResult[$comment->from->id])) {
						$pointResult[$comment->from->id] = $pointResult[$comment->from->id] + $multiply*2;
					}else {
						$pointResult[$comment->from->id] = $multiply*2;
					}
				}
			}
		}
		return $pointResult;
	}
	
	private function calculateCommentPoints($pointResult, $commentsList) {
		foreach($commentsList as $comment) {
			if($comment->from->id !== $this->_fanpageId && ! empty($comment->like_count)) {
				if(isset($pointResult[$comment->from->id])) {
					$pointResult[$comment->from->id] = $pointResult[$comment->from->id] + $comment->like_count;
				}else {
					$pointResult[$comment->from->id] = $comment->like_count;
				}
			}
		}
		return $pointResult;
	}
	
	private function calculateLikesPoints($pointResult, $likesList) {
		foreach ($likesList as $like) {
			if(isset($pointResult[$like['facebook_user_id']])) {
				$pointResult[$like['facebook_user_id']] = $pointResult[$like['facebook_user_id']] + 1;
			}else {
				$pointResult[$like['facebook_user_id']] = 1;
			}
		}
		return $pointResult;
	}
}

?>