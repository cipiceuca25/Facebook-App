<?php
/**
 * FanCrank
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the FanCrank OEM license
 *
 * @category    service
 * @copyright   Copyright (c) 2012 FanCrank
 * @license
 */
class Service_FancrankCollectorService {
	protected $_facebookGraphAPIUrl;
	protected $_fanpageId;
	protected $_accessToken;
	protected $_type;
	protected $_fanpageSetting;
	
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
		
		$fanpageSettingModel = new Model_FanpageSetting();
		$settingData = $fanpageSettingModel->findRow($fanpage_id);
		if(!$settingData) {
			$settingData = $fanpageSettingModel->getDefaultSetting();
		}else {
			$settingData = $settingData->toArray();
		}
		
		$this->_fanpageSetting = $settingData;
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
	
	public function getFanpageFeed($since=null, $until=null) {
		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/feed?access_token=' .$this->_accessToken .'&until=' .$until .'&since=' .$since;
		
		$posts = array();
		//echo $url; exit();
		$this->getPostsByTimeRange($url, 10, 100, $posts);
		Zend_Debug::dump($posts);
		
		if(empty($posts)) {
			return array();
		}
		
 		$postLikeList = $this->getLikesFromMyPost($posts, 2, 1000);
 		//Zend_Debug::dump($postLikeList); 

 		$postCommentsList = $this->getCommentsAndItsLikesFromPost($posts, 5, 1000);
 		//Zend_Debug::dump($postCommentsList);
 		return $posts;
	}
	
	public function updateFanpageFeed($since=null, $until=null, $synchronization=true) {
		$feed = $this->getFanpageFeed($since, $until);
		$fancrankDB = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		
		$saveFanList = array();
		// save and update fans
		try {
			//Zend_Debug::dump($facebookUsers);
			$fansIdList = $this->getActiveFansFromFeed($feed);
			$saveFanList = $fancrankDB->saveAndUpdateFans($fansIdList, null, false);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		// handle fanpage feed
		foreach ( $feed as $post ) {
			// save post
			if (! $fancrankDB->savePost ( $post )) {
				echo 'error to save post';
				continue;
			}
			
			// handle post comments
			if (! empty ( $post->comments->data )) {
				foreach ( $post->comments->data as $comment ) {
					// save comment
					$comment->comment_type = $post->type;
					if (! $fancrankDB->saveComment ( $comment, $post )) {
						continue;
					}
					
					// save comment like
					if (! empty ( $comment->like_list )) {
						foreach ( $comment->like_list as $like ) {
							$fancrankDB->saveCommentLike ( $like, $comment );
						}
					}
				}
			}
			
			// handle post likes
			if (! empty ( $post->likes->data )) {
				foreach ( $post->likes->data as $like ) {
					$fancrankDB->savePostLike ( $like, $post );
				}
			}
		}

		// update fan stat
		$fanStat = new Model_FansObjectsStats();
		if (!empty($saveFanList)) {
			Zend_Debug::dump($saveFanList);
			foreach ($saveFanList as $row) {
				$fan = new Model_Fans($row['facebook_user_id'], $this->_fanpageId);
				$fanStat->updatedFanWithPoint($this->_fanpageId, $row['facebook_user_id'], $fan->getFanExp(), $fan->getFanPoint());
			}
		}
	}
	
	public function getActiveFansFromFeed($feed) {
		$fansIdList = array();
		$fanModel = new Model_Fans();
		foreach ($feed as $post) {
			if ($post->from->id !== $this->_fanpageId) {
				$fansIdList[] = $post->from->id;
			}
			
			// scan post comments
			if (! empty ( $post->comments->data )) {
				foreach ( $post->comments->data as $comment ) {
					if ($comment->from->id !== $this->_fanpageId) {
						$fansIdList[] = $comment->from->id;
					}
					// scan comment likes
					if (! empty ( $comment->like_list )) {
						foreach ( $comment->like_list as $like ) {
							if ($like->id !== $this->_fanpageId) {
								$fansIdList[] = $like->id;
							}
						}
					}
				}
			}
				
			// handle post likes
			if (! empty ( $post->likes->data )) {
				foreach ( $post->likes->data as $like ) {
					if ($like->id !== $this->_fanpageId) {
						$fansIdList[] = $like->id;
					}
				}
			}
		}

		return $this->getFansList(array_unique($fansIdList));
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
		
 		$postCommentsList = $this->getCommentsFromPost($posts, 5, 1000);
 		//Zend_Debug::dump($postCommentsList);
		
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$this->_fanpageId .'/albums?access_token=' .$this->_accessToken;
		$albumsList = array();
		$this->getFromUrlRecursive($url, 2, 1000, $albumsList);
		//Zend_Debug::dump($albumsList);
		$albumLikesList = $this->getLikesFromMyAlbums($albumsList, 'album');
		//Zend_Debug::dump($albumLikesList);
		
		$albumCommentList = $this->getCommentsFromMyAlbum($albumsList, 'album');
		//Zend_Debug::dump($albumCommentList);
		
		$photoList = array();
		$photoList = $this->getPhotosFromAlbum($albumsList, 2, 1000);
		//Zend_Debug::dump($photoList);
		$photoLikesList = $this->getLikesFromMyAlbums($photoList, 'photo');
		//Zend_Debug::dump($photoLikesList);
		$photoCommentList = $this->getCommentsFromMyAlbum($photoList, 'photo');
		//Zend_Debug::dump($photoCommentList);
		
		$commentsList = array_merge($postCommentsList, $albumCommentList, $photoCommentList);
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
		
		$filePath = DATA_PATH .'/temp/' .$this->_fanpageId .'_last_fullscan.data';
		
		if (file_exists($filePath)) {
			//echo "The file $filePath exists";
			//$lastUpdatedData = unserialize( file_get_contents( $filePath ) );
		}else {
			$lastUpdatedData['posts'] = $posts;
			$lastUpdatedData['postCommentsList'] = $postCommentsList;
			$lastUpdatedData['postLikeList'] = $postLikeList;

			$lastUpdatedData['albumsList'] = $albumsList;
			$lastUpdatedData['albumLikesList'] = $albumLikesList;
			$lastUpdatedData['albumCommentList'] = $albumCommentList;

			$lastUpdatedData['photoList'] = $photoList;
			$lastUpdatedData['photoLikesList'] = $photoLikesList;
			$lastUpdatedData['photoCommentList'] = $photoCommentList;
			
			$lastUpdatedData['commentsList'] = $commentsList;
			$lastUpdatedData['commentLikeList'] = $commentLikeList;
			$lastUpdatedData['allLikesList'] = $allLikesList;
			file_put_contents( $filePath, serialize( $lastUpdatedData ) );
		}
		
		$pointResult = $this->calculatePostPoints($posts, $postCommentsList, $postLikeList);
		$pointResult = $this->calculateAlbumPoints($pointResult, $albumsList, $albumCommentList);
		$pointResult = $this->calculatePhotoPoints($pointResult, $photoList, $photoCommentList);
		$pointResult = $this->calculateCommentPoints($pointResult, $commentsList, $commentLikeList);
		$pointResult = $this->calculateLikesPoints($pointResult, $allLikesList);
		
		//Zend_Debug::dump($pointResult);
		//exit();
		$db->beginTransaction();
		
		try {
			$fdb->savePosts($posts);
			
			$fdb->saveAlbums($albumsList);
			
			$fdb->savePhotos($photoList);
			
			$fdb->saveComments($commentsList);
			
			$fdb->saveLikes($allLikesList);
			
			$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
			
			$facebookUsers = $this->getFansList($fansIdsList, $this->_accessToken);
			//$result = $fdb->saveFans($facebookUsers);
			$result = $fdb->saveAndUpdateFans($facebookUsers, $pointResult);
			
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
		$pointResult = array();
		$lastUpdatedData = array();
		$overallData = array();
		
		//Zend_Debug::dump($lastUpdatedData['posts']);
		//exit();
		$url = $this->_facebookGraphAPIUrl . $this->_fanpageId .'/feed?access_token=' .$this->_accessToken .'&until=' .$until .'&since=' .$since;
		
		$posts = array();
		//echo $url; exit();
		$this->getPostsByTimeRange($url, 10, 100, $posts);
		Zend_Debug::dump($posts);
		
		echo '---------------';

		if(empty($posts)) {
			return;
		}

 		$postLikeList = $this->getLikesFromMyPost($posts, 2, 1000);
 		Zend_Debug::dump($postLikeList); 

 		$postCommentsList = $this->getCommentsFromPost($posts, 5, 1000);
 		Zend_Debug::dump($postCommentsList);

 		//$pointResult = $this->calculatePostPoints($posts, $postCommentsList, $postLikeList);
  		//Zend_Debug::dump($pointResult); exit();
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$this->_fanpageId .'/albums?access_token=' .$this->_accessToken .'&since=30+days+ago';// .$since;
		$albumsList = array();
		$this->getFromUrlRecursive($url, 2, 1000, $albumsList);
		Zend_Debug::dump($albumsList);
		
		$albumLikesList = $this->getLikesFromMyAlbums($albumsList, 'album');
		Zend_Debug::dump($albumLikesList);

		$albumCommentList = $this->getCommentsFromMyAlbum($albumsList, 'album');
		Zend_Debug::dump($albumCommentList);

		//$pointResult = $this->calculateAlbumPoints($pointResult, $albumsList, $albumCommentList);
		//Zend_Debug::dump($pointResult);
		
		$photoList = array();
		$photoList = $this->getPhotosFromAlbum($albumsList, 2, 1000);
		Zend_Debug::dump($photoList);

		$photoLikesList = $this->getLikesFromMyAlbums($photoList, 'photo');
		Zend_Debug::dump($photoLikesList);

		$photoCommentList = $this->getCommentsFromMyAlbum($photoList, 'photo');
		Zend_Debug::dump($photoCommentList);
		
		//$pointResult = $this->calculatePhotoPoints($pointResult, $photoList, $photoCommentList);
		//Zend_Debug::dump($pointResult);
		
		$commentsList = array_merge($postCommentsList, $albumCommentList, $photoCommentList);
		//Zend_Debug::dump($commentsList);
		
		$commentLikeList = $this->getLikesFromMyComment($commentsList);
		Zend_Debug::dump($commentLikeList);
		
		//$pointResult = $this->calculateCommentPoints($pointResult, $commentsList, $commentLikeList);

		$allLikesList = array_merge($postLikeList, $commentLikeList, $albumLikesList, $photoLikesList);
		
		//$pointResult = $this->calculateLikesPoints($pointResult, $allLikesList);
		
		$fdb = new Service_FancrankDBService($this->_fanpageId, $this->_accessToken);
		
		$db = $fdb->getDefaultAdapter();
		
		echo '<br/>total likes for ' .count($posts) . ' posts: ' .count($postLikeList);
		echo '<br/>total likes for ' .count($commentsList) . ' comments: ' .count($commentLikeList);
		echo '<br/>total likes for ' .count($albumsList) . ' albums ' .count($albumLikesList);
		echo '<br/>total likes for ' .count($photoList) . ' photos ' .count($photoLikesList);
		echo '<br/>Total likes : ' .count($allLikesList);
		
		$filePath = DATA_PATH .'/temp/' .$this->_fanpageId .'_last_update.data';
		
		if (file_exists($filePath)) {
			//echo "The file $filePath exists";
			//$lastUpdatedData = unserialize( file_get_contents( $filePath ) );
		}else {
			$lastUpdatedData['posts'] = $posts;
			$lastUpdatedData['postCommentsList'] = $postCommentsList;
			$lastUpdatedData['postLikeList'] = $postLikeList;
		
			$lastUpdatedData['albumsList'] = $albumsList;
			$lastUpdatedData['albumLikesList'] = $albumLikesList;
			$lastUpdatedData['albumCommentList'] = $albumCommentList;
		
			$lastUpdatedData['photoList'] = $photoList;
			$lastUpdatedData['photoLikesList'] = $photoLikesList;
			$lastUpdatedData['photoCommentList'] = $photoCommentList;
				
			$lastUpdatedData['commentsList'] = $commentsList;
			$lastUpdatedData['commentLikeList'] = $commentLikeList;
			$lastUpdatedData['allLikesList'] = $allLikesList;
			file_put_contents( $filePath, serialize( $lastUpdatedData ) );
		}
		
		//Zend_Debug::dump($allLikesList);
		
		//get fanpage setting
		
		$pointResult = $this->calculatePostPoints($posts, $postCommentsList, $postLikeList); 
		$pointResult = $this->calculateAlbumPoints($pointResult, $albumsList, $albumCommentList);
		$pointResult = $this->calculatePhotoPoints($pointResult, $photoList, $photoCommentList);
		$pointResult = $this->calculateCommentPoints($pointResult, $commentsList, $commentLikeList);
		$pointResult = $this->calculateLikesPoints($pointResult, $allLikesList);
		Zend_Debug::dump($pointResult);

		$db->beginTransaction();
		
		try {
			$fdb->savePosts($posts);

			$fdb->saveAlbums($albumsList);
			
			$fdb->savePhotos($photoList);
			
			$fdb->saveComments($commentsList);

			$fdb->saveLikes($allLikesList);
			
			$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
			
			$facebookUsers = $this->getFansList($fansIdsList, $this->_accessToken);
			
			$result = $fdb->saveAndUpdateFans($facebookUsers, $pointResult, true);
			
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
			case 'comments' : $url =  $baseUrl .'/insights/page_fan_adds/day' . '?' .http_build_query($params); break;
			case 'posts' 	: $url =  $baseUrl .'/insights/page_story_adds_unique/day' . '?' .http_build_query($params); break;
			default			: break;
		}
		
		$insights = array();		
		$this->getInsightsRecursive($url, $level, null, $insights);
		return $insights;
	}
	
	protected function getInsightsRecursive($url, $level, $limit=null, &$result) {
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
	
	private function getCommentsFromPost(&$posts, $level, $limit=500) {
		//Zend_Debug::dump($posts);
		$results = array();
		foreach ($posts as $key => $post) {
			$commentCount = 1;
			$hasMore = false;
			if(!empty($post->comments->data)) {
				$commentCount = count($post->comments->data);
			}
			if(!empty($post->comments->count) && $post->comments->count > $commentCount) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $post->id .'/comments?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, $level, $limit, $result);
				if (!empty($result)) {
					$posts[$key]->comments->data = $result;
				}
				foreach ($result as $comment) {
					$comment->comment_type = !empty($post->type) ? $post->type : '';
					$results[] = $comment;
				}
				$hasMore = true;
			}
			if(! $hasMore && !empty($post->comments->data)) {
				foreach ($post->comments->data as $comment) {
					$comment->comment_type = !empty($post->type) ? $post->type : '';
					$results[] = $comment;
				}
			}
		}
		return $results;
	}
	
	private function getCommentsAndItsLikesFromPost(&$posts, $level, $limit=500) {
		//Zend_Debug::dump($posts);
		$results = array();
		foreach ($posts as $key => $post) {
			$commentCount = 1;
			$hasMore = false;
			if(!empty($post->comments->data)) {
				$commentCount = count($post->comments->data);
			}
			if(!empty($post->comments->count) && $post->comments->count > $commentCount) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $post->id .'/comments?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, $level, $limit, $result);
				if (!empty($result)) {
					$posts[$key]->comments->data = $result;
				}
				foreach ($result as $k=>$comment) {
					$posts[$key]->comments->data[$k]->like_list = $this->getLikesFromComment($comment);
				}
				$hasMore = true;
			}
			if(! $hasMore && !empty($post->comments->data)) {
				foreach ($post->comments->data as $k=>$comment) {
					$posts[$key]->comments->data[$k]->like_list = $this->getLikesFromComment($comment);
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
	
	private function getLikesFromMyPost(&$posts, $level=5, $limit=5000) {
		//Zend_Debug::dump($posts);
		$results = array();
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($posts as $key=>$post) {
			$likeCount = 1;
			$hasMoreLike = false;
			$time = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
			$created_time = $time->toString('yyyy-MM-dd HH:mm:ss');
			if (!empty($post->likes->data)) {
				$likeCount = count($post->likes->data);
			}
			
			$likeData = array(	'fanpage_id'=>$this->_fanpageId,
								'post_id'=>$post->id
							);
			
			if ($post->from->id === $this->_fanpageId) {
				$likeData['target'] = 'admin';
			} else {
				$likeData['target'] = 'user';
			}
			
			if (!empty($post->likes->count) && $post->likes->count > $likeCount) {
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $post->id .'/likes?access_token=' .$this->_accessToken;
				
				if (!empty($limit) && $limit > 1) {
					$level = ceil($post->likes->count / $limit); 
				}
				
				$this->getFromUrlRecursive($url, $level, $limit, $result);
				if (!empty($result)) {
					$posts[$key]->likes->data = $result;
				}
				foreach ($result as $like) {
					$likeData['facebook_user_id'] = $like->id;
					$likeData['post_type'] = !empty($post->type) ? $post->type : '';
					$likeData['created_time'] = $created_time;
					$likeData['updated_time'] = $update_time;
					$results[] = $likeData;					
				}
				$hasMoreLike = true;
			}
			
			if(! $hasMoreLike && !empty($post->likes->data)) {
				foreach ($post->likes->data as $like) {
					$likeData['facebook_user_id'] = $like->id;
					$likeData['post_type'] = !empty($post->type) ? $post->type : '';
					$likeData['created_time'] = $created_time;
					$likeData['updated_time'] = $update_time;
					$results[] = $likeData;	
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
				$photo->photo_album_id = $album->id;
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
			foreach (json_decode($result) as $key=>$values) {
				if (!empty($values->code) && $values->code === 200 && !empty($values->body)) {
					$values = json_decode($values->body);
					if (!empty ($values->data)) {
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
		if (empty($url) || $level === 0) {
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
			if (! empty($response->data)) {
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
			if (! empty($photo->comments)) {
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
	
	private function getLikesFromMyAlbums($albums, $type) {
		$likesList = array();
		$time = new Zend_Date();
		$update_time = $time->toString('yyyy-MM-dd HH:mm:ss');
		foreach ($albums as $album) {
			if(! empty($album->likes->data) ) {
				$time = new Zend_Date(!empty($album->created_time) ? $album->created_time : null, Zend_Date::ISO_8601);
				$created_time = $time->toString('yyyy-MM-dd HH:mm:ss');
				foreach ($album->likes->data as $like) {
					$likesList[] = array(
							'fanpage_id'        => $this->_fanpageId,
							'post_id'           => $album->id,
							'facebook_user_id'  => $like->id,
							'created_time'		=> $created_time,
							'updated_time'		=> $update_time,
							'post_type'			=> $type,
							'target'			=> 'admin'
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
								'created_time'		=> $created_time,	
								'updated_time'		=> $update_time,
								'post_type'			=> $type,
								'target'			=> 'admin'
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
			if(!empty($comment->like_count) && $comment->like_count >= 1) {
				$time = new Zend_Date(!empty($comment->created_time) ? $comment->created_time : null, Zend_Date::ISO_8601);
				$created_time = $time->toString('yyyy-MM-dd HH:mm:ss');
				$result = array();
				$url = $this->_facebookGraphAPIUrl . $comment->id .'/likes?access_token=' .$this->_accessToken;
				$this->getFromUrlRecursive($url, 1, 1000, $result);
				foreach ($result as $like) {
					$likesList[] = array(
							'fanpage_id'        => $this->_fanpageId,
							'post_id'           => $comment->id,
							'facebook_user_id'  => $like->id,
							'created_time'		=> $created_time,
							'updated_time'		=> $update_time,
							'post_type'         => $comment->comment_type .'_comment',
							'target'			=> 'comment',
					);
				}
			}
		}
		return $likesList;
	}
	
	public function getLikesFromComment($comment) {
		$result = array();
		if(!empty($comment->like_count) && $comment->like_count >= 1) {
			$url = $this->_facebookGraphAPIUrl . $comment->id .'/likes?access_token=' .$this->_accessToken;
			$this->getFromUrlRecursive($url, 1, 1000, $result);
		}
		echo 'comment likes.......';
		Zend_Debug::dump($result);
		return $result;
	}
	
	private function getCommentsFromMyAlbum($albums, $type) {
		$commentList = array();
		foreach ($albums as $album) {
			if(! empty($album->comments->data) ) {
		
				foreach ($album->comments->data as $comment) {
					$comment->comment_type = $type;
					$commentList[] = $comment;
				}
		
				if(count($album->comments->data) >= 25 && !empty($album->comments->paging->next)) {
					$result = array();
					$url = $album->comments->paging->next;
					$this->getFromUrlRecursive($url, 1, 1000, $result);
					foreach ($result as $comment) {
						$comment->comment_type = $type;
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
				}else if (!empty($v['facebook_user_id'])) {
					$fansIdList[] = $v['facebook_user_id'];
				}
			}
		}
		 
		return array_unique($fansIdList);
	}
	
	public function getFansList($fansIdsList, $access_token=null) {
		if (!$access_token) {
			$access_token = $this->_accessToken;
		}
		 
		$results = array();
		$fansIdsGroup = $this->arrayToGroups($fansIdsList, 500);
		try {
			foreach ($fansIdsGroup as $fansIds) {
				$batchQuery = $this->batchQueryBuilder($fansIds, $access_token);
				foreach (json_decode($this->httpCurl($batchQuery)) as $fans) {
					if(empty($fans->first_name) || empty($fans->id)) continue;
					$results[] = $fans;
				}
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return $results;
	}
	
	private function batchQueryBuilder($ids, $access_token) {
		$params = 'ids=' .implode(',', $ids);
		return 'https://graph.facebook.com/?' .$params .'&access_token=' .$access_token;
	}
	
	protected function httpCurl($url, $params=null, $method=null) {
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
	
	private function calculatePostPoints($posts, $commentsList, $postLikeList) {
		$totalPoints = 0;
		$pointResult = array();
		$xpResult = array();
		$postModel = new Model_Posts();
		$commentModel = new Model_Comments();
		
		foreach ($posts as $post) {
			$totalLikePoints = 0;
			$totalCommentPoints = 0;
			$virginity = 0;
			$unique = 0;
			if(!empty($post->from->id) && $this->_fanpageId === $post->from->id) {
				$postTime = new Zend_Date($post->created_time, Zend_Date::ISO_8601);
				foreach ($commentsList as $comment) {
					
					if(preg_match("/{$post->id}_/", $comment->id) && $comment->from->id !== $post->from->id && !$commentModel->findRow($comment->id)) {
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
						//echo $comment->from->id .'gain comment: ' .$multiply*2 .' points from admin post ' .$post->id .'<br/>';
						$gain = $multiply * $this->_fanpageSetting['point_comment_admin'];
						if(isset($pointResult[$comment->from->id])) {
							$pointResult[$comment->from->id]['total_points'] = $pointResult[$comment->from->id]['total_points'] + $gain;
						}else {
							$pointResult[$comment->from->id]['total_points'] = $gain;
						}
						$pointResult[$comment->from->id]['xp'] = $pointResult[$comment->from->id]['total_points'];
						$pointResult[$comment->from->id]['point_log'][] = array(
									'object_id'=> $comment->id,
									'object_type'=> 'comments',
									'giving_points'=> $gain,
									'bonus'=> $gain - $this->_fanpageSetting['point_comment_admin'],
									'note'=> 'comments on admin post'
								);
						//$xpResult[$comment->from->id] = $pointResult[$comment->from->id];
					}
				}
					
				continue;
			}
			
			$oldPost = $postModel->findRow($post->id);
			
			if(empty($post->likes->count) && empty($post->comments->count) && !$oldPost) {
				$totalPoints = $this->_fanpageSetting['point_post_normal'];
				//echo $post->from->id .' lost: ' .$totalPoints .' from ' .$post->id .'<br/>';
			}else if(!$oldPost) {
				$virginity = $this->_fanpageSetting['point_virginity'];
				$uniqueUser = array();
				if(!empty($post->likes->count) && $post->likes->count >= 1) {
					$totalLikePoints = $post->likes->count * $this->_fanpageSetting['point_like_normal'];
					foreach ($postLikeList as $like) {
						if($like['post_id'] === $post->id && $like['facebook_user_id'] !== $post->from->id) {
							$uniqueUser[$like['facebook_user_id']] = 1;
						}
					}
					//echo $post->from->id .' gain new like: ' .$totalLikePoints .' from ' .$post->id .'<br/>';
				}
				
				$ownerCommentCount = 0;
				if(!empty($post->comments->count) && $post->comments->count >= 1) {
					foreach ($commentsList as $comment) {
						if(preg_match("/{$post->id}_/", $comment->id)) {
							if($comment->from->id === $post->from->id) {
								$ownerCommentCount++;
							}else {
								$uniqueUser[$comment->from->id] = 1;
							}
						}
					}
					
					$totalCommentPoints = ($post->comments->count - $ownerCommentCount) * 2;

					//echo $post->from->id .' gain new comment: ' .$totalCommentPoints .' from ' .$post->id .'<br/>';
				}
				
				//echo $post->from->id .' gain new unique: ' .count($uniqueUser) .' from post ' .$post->id .'<br/>';
				
				$totalPoints = $virginity + $totalLikePoints + $totalCommentPoints + count($uniqueUser) - $this->_fanpageSetting['point_post_normal'];
			}else if($oldPost) {

				$likeModel = new Model_Likes();
				$uniqueUser = array();
				if(!empty($post->likes->count) && $post->likes->count >= 1) {
					foreach ($postLikeList as $like) {
						if($like['post_id'] === $post->id && !$likeModel->getLikes($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])) {
							$uniqueUser[$like['facebook_user_id']] = 1;
						}
					}
					//echo $post->from->id .' gain more like: ' .count($uniqueUser) .' from ' .$post->id .'<br/>';
				}
				
				$uniqueCommentUser = array();
				$newCommentCount = 0;
				if(!empty($post->comments->count) && $post->comments->count >= 1) {
					foreach ($commentsList as $comment) {
						if(preg_match("/{$post->id}_/", $comment->id) && !$commentModel->findRow($comment->id)) {
							$newCommentCount++;
							if(isset($uniqueCommentUser[$comment->from->id])) {
								$uniqueCommentUser[$comment->from->id] += 1;
							}else {
								$uniqueCommentUser[$comment->from->id] = 1;
							}
						}
					}
					$totalCommentPoints =  $newCommentCount * 2;
					//echo $post->from->id .' gain more comment: ' .$totalCommentPoints .' from ' .$post->id .'<br/>';
				}
				
				$unique = count($uniqueUser) + count($uniqueCommentUser) - count(array_intersect_key($uniqueUser, $uniqueCommentUser));
				
				//echo $post->from->id .' gain more unique: ' .$unique .' from post ' .$post->id .'<br/>';
				$virginity = 0;
				if(empty($oldPost->post_likes_count) && empty($oldPost->post_comments_count) && !empty($unique)) {
					$virginity = $this->_fanpageSetting['point_virginity'];
				}
				
				$totalPoints = $totalLikePoints + $totalCommentPoints + $unique + $virginity;
			}
				
			if(isset($pointResult[$post->from->id])) {
				$pointResult[$post->from->id]['total_points'] = $pointResult[$post->from->id]['total_points'] + $totalPoints;
				$pointResult[$post->from->id]['xp'] = $totalPoints < 0 ? $pointResult[$post->from->id]['xp'] : $pointResult[$post->from->id]['xp'] + $totalPoints;
			}else {
				$pointResult[$post->from->id]['total_points'] = $totalPoints;
				$pointResult[$post->from->id]['xp'] = $totalPoints < 0 ? 0 : $totalPoints;
			}
			
			if($oldPost && $totalPoints <= 0) {
				continue;
			}
			$pointResult[$post->from->id]['point_log'][] = array(
					'object_id'=> $post->id,
					'object_type'=> 'posts',
					'giving_points'=> $totalPoints,
					'bonus'=> 0,
					'note'=> 'post on fanpage'
			);
		}
		
		return $pointResult;
	}
	
	private function calculateAlbumPoints($pointResult, $albumsList, $albumCommentList) {
		$commentModel = new Model_Comments();
		
		foreach ($albumsList as $album) {
			$postTime = new Zend_Date($album->created_time, Zend_Date::ISO_8601);
			foreach ($albumCommentList as $comment) {
				if(preg_match("/{$album->id}_/", $comment->id) && $comment->from->id !== $album->from->id && !$commentModel->findRow($comment->id)) {
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
					$gain = $multiply * $this->_fanpageSetting['point_comment_admin'];
					if(isset($pointResult[$comment->from->id])) {
						$pointResult[$comment->from->id]['total_points'] = $pointResult[$comment->from->id]['total_points'] + $gain;
					}else {
						$pointResult[$comment->from->id]['total_points'] = $gain;
					}
					$pointResult[$comment->from->id]['xp'] = $pointResult[$comment->from->id]['total_points'];
					$pointResult[$comment->from->id]['point_log'][] = array(
							'object_id'=> $comment->id,
							'object_type'=> 'comments',
							'giving_points'=> $gain,
							'bonus'=> $gain - $this->_fanpageSetting['point_comment_admin'],
							'note'=> 'comment on album'
					);
				}
			}
		}
		return $pointResult;
	}
	
	private function calculatePhotoPoints($pointResult, $photoList, $photoCommentList) {
		$commentModel = new Model_Comments();
		
		foreach ($photoList as $photo) {
			$postTime = new Zend_Date($photo->created_time, Zend_Date::ISO_8601);
			foreach ($photoCommentList as $comment) {
				if(preg_match("/{$photo->id}_/", $comment->id) && $comment->from->id !== $photo->from->id && !$commentModel->findRow($comment->id)) {
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
					//echo $comment->from->id .'gain comment:' .$multiply*2 .' from post ' .$photo->id .'<br/>';
					$gain = $multiply * $this->_fanpageSetting['point_comment_admin'];
					if(isset($pointResult[$comment->from->id])) {
						$pointResult[$comment->from->id]['total_points'] = $pointResult[$comment->from->id]['total_points'] + $gain;
					}else {
						$pointResult[$comment->from->id]['total_points'] = $gain;
					}
					$pointResult[$comment->from->id]['xp'] = $pointResult[$comment->from->id]['total_points'];
					$pointResult[$comment->from->id]['point_log'][] = array(
							'object_id'=> $comment->id,
							'object_type'=> 'comments',
							'giving_points'=> $gain,
							'bonus'=> $gain - $this->_fanpageSetting['point_comment_admin'],
							'note'=> 'comment on photo'
					);
				}
			}
		}
		return $pointResult;
	}
	
	private function calculateCommentPoints($pointResult, $commentsList, $commentLikeList) {
		$commentModel = new Model_Comments();
		
		foreach($commentsList as $comment) {
			if($comment->from->id !== $this->_fanpageId && ! empty($comment->like_count)) {
				foreach ($commentLikeList as $commentLike) {
					if($comment->id === $commentLike['post_id'] && !$commentModel->findRow($comment->id)) {
						$gain = $comment->like_count * $this->_fanpageSetting['point_comment_normal'];
						if(isset($pointResult[$comment->from->id])) {
							$pointResult[$comment->from->id]['total_points'] += $gain;
						}else {
							$pointResult[$comment->from->id]['total_points'] = $gain;
						}
						$pointResult[$comment->from->id]['xp'] = $pointResult[$comment->from->id]['total_points'];
						$pointResult[$comment->from->id]['point_log'][] = array(
								'object_id'=> $comment->id,
								'object_type'=> 'comments',
								'giving_points'=> $gain,
								'bonus'=> 0,
								'note'=> 'comments on non-admin post, gain points basic on total # of likes'
						);
					}
				}
			}
		}
		return $pointResult;
	}
	
	private function calculateLikesPoints($pointResult, $likesList) {
		$likeModel = new Model_Likes();
		foreach ($likesList as $like) {
			if($likeModel->getLikes($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])) continue;
			
			//check like target
			$gain = $this->_fanpageSetting['point_like_normal'];
			if($like['target'] === 'admin') {
				$gain = $this->_fanpageSetting['point_like_admin'];
			}
			
			if(isset($pointResult[$like['facebook_user_id']])) {
				$pointResult[$like['facebook_user_id']]['total_points'] += $gain;
			}else {
				$pointResult[$like['facebook_user_id']]['total_points'] = $gain;
			}
			$pointResult[$like['facebook_user_id']]['xp'] = $pointResult[$like['facebook_user_id']]['total_points'];
			$pointResult[$like['facebook_user_id']]['point_log'][] = array(
					'object_id'=> $like['post_id'],
					'object_type'=> 'likes',
					'giving_points'=> $gain,
					'bonus'=> 0,
					'note'=> 'likes on ' .$like['target'] .' object'
			);
		}
		return $pointResult;
	}
	
	private function filterPosts($previousPosts, $newPosts) {
		if(empty($previousPosts)) {
			return $newPosts;
		}
		
		$result = array();
		foreach ($newPosts as $newPost) {
			$found = false;
			foreach($previousPosts as $prePost) {
				if($newPost->id !== $prePost->id) continue;
				$preLikeCount = 0;
				$newLikeCount = 0;
				if(!empty($prePost->likes->count)) {
					$preLikeCount = $prePost->likes->count;
				}
				if(!empty($newPost->likes->count)) {
					$newLikeCount = $newPost->likes->count;
				}
				if($newPost->updated_time !== $prePost->updated_time || $newLikeCount > $preLikeCount) {
					//echo 'new like count: ' .$newLikeCount .' old count: ' .$preLikeCount;
					$result[] = $newPost;
				}
				$found = true;
				break;
			}
			if(!$found) {
				$result[] = $newPost;
			}
		}
		return $result;
	}
	
	private function filterLikes($previousLikes, $newLikes) {
		if(empty($previousLikes)) {
			return $newLikes;
		}
		
		$result = array();
		foreach ($newLikes as $newLike) {
			$found = false;
			foreach($previousLikes as $preLike) {
				if($newPost->id !== $prePost->id) continue;
				$found = true;
				break;
			}
			if(!$found) {
				$result[] = $newLike;
			}
		}
		return $result;
	}
	
	private function filterComment($previousCommentList, $newCommentList) {
		if(empty($previousCommentList)) {
			return $newCommentList;
		}
		$result = array();
		foreach ($previousCommentList as $preComment) {
			$found = false;
			foreach($newCommentList as $newComment) {
				if($newComment->id !== $preComment->id) continue;
				$found = true;
				break;
			}
			if(!$found) {
				$result[] = $preComment;
			}
		}
		return $result;
	}
	
	// get single post data
	public function getFullPost($postId) {
		// get basic post data
		try {
			$client = new Zend_Http_Client;
			$client->setUri("https://graph.facebook.com/". $postId);
			$client->setMethod(Zend_Http_Client::GET);
			$client->setParameterGet('access_token', $this->_accessToken);
		
			$response = $client->request();
		
			$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
			Zend_Debug::dump($result);
			
			if (!empty($result->error)) {
				$type = isset($result->error->type) ? $result->error->type : '';
				$code = isset($result->error->code) ? $result->error->code : '';
				$message = isset($result->error->message) ? $result->error->message : '';
				$msg = sprintf('type: %s, $code: %s, message: %s', $type, $code, $message);
				throw new Exception($msg);
			}
		
			if (!empty ($result)) {
				$postModel = new Model_Posts();
				$created = new Zend_Date(!empty($result->created_time) ? $result->created_time : null, Zend_Date::ISO_8601);
				$updated = new Zend_Date(!empty($result->updated_time) ? $result->updated_time : null, Zend_Date::ISO_8601);
		
				$row = array(
						'post_id'               => $result->id,
						'facebook_user_id'      => $result->from->id,
						'fanpage_id'            => $this->_fanpageId,
						'post_message'          => isset($result->message) ? $postModel->getDefaultAdapter()->quote($result->message) : '',
						'picture'				=> !empty($result->picture) ? $result->picture : '',
						'link'					=> !empty($result->link) ? $result->link : '',
						'post_type'             => !empty($result->type) ? $result->type : '',
						'status_type'           => !empty($result->status_type) ? $result->status_type : '',
						'post_description'		=> !empty($result->description) ? $postModel->getDefaultAdapter()->quote($result->description) : '',
						'post_caption'			=> !empty($result->caption) ? $postModel->getDefaultAdapter()->quote($result->caption) : '',
						'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
						'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
						'post_comments_count'   => !empty($result->comments->count) ? $result->comments->count : 0,
						'post_likes_count'      => isset($result->likes) && isset($result->likes->count) ? $result->likes->count : 0
				);
		
				if (property_exists($result, 'application') && isset($result->application->id)) {
					$row['post_application_id'] = $result->application->id;
					$row['post_application_name'] = empty($result->application->name) ? null : $result->application->name;
				} else {
					$row['post_application_id'] = null;
					$row['post_application_name'] = null;
				}
				
				$commentList = $this->getCommentsFromPost(array($result), 2);
				$row['comment_list'] = $commentList;
				
				$likeList = $this->getLikesFromMyPost(array($result), 2);
				$row['like_list'] = $likeList;
				
				return $row;
			}
		} catch (Exception $e){
			echo $e->getMessage();
			return null;
		}
	}
	
	public function getNewFanListFromPost($post) {
		$fansIdList = array();
		$fansIdList[] = $post['facebook_user_id'];
		$fanModel = new Model_Fans();
		foreach ($post['comment_list'] as $comment) {
			if (!empty($comment->from->id) && 
					$comment->from->id != $post['fanpage_id'] && 
					!$fanModel->find($comment->from->id, $post['fanpage_id'])->count()) {
				$fansIdList[] = $comment->from->id;
			}
		}
		
		foreach ($post['like_list'] as $like) {
			if (!empty($like['facebook_user_id']) &&
					$like['facebook_user_id'] != $post['fanpage_id'] &&
					!$fanModel->find($like['facebook_user_id'], $post['fanpage_id'])->count()) {
				$fansIdList[] = $like['facebook_user_id'];
			}
		}
		
		$fansIdsList = array_unique($fansIdList);
		
		return $this->getFansList($fansIdsList, $this->_accessToken);
	}
	
	public function test() {
		$filePath = DATA_PATH .'/temp/' .$this->_fanpageId .'_last_update.data';
		
		if (file_exists($filePath)) {
			echo "The file $filePath exists";
			$lastUpdatedData = unserialize( file_get_contents( $filePath ) );
			$posts = $lastUpdatedData['posts'];
			$postCommentsList = $lastUpdatedData['postCommentsList'];
			$postLikeList = $lastUpdatedData['postLikeList'];

			$albumsList = $lastUpdatedData['albumsList'];
			$albumLikesList = $lastUpdatedData['albumLikesList'];
			$albumCommentList = $lastUpdatedData['albumCommentList'];

			$photoList = $lastUpdatedData['photoList'];
			$photoLikesList = $lastUpdatedData['photoLikesList'];
			$photoCommentList = $lastUpdatedData['photoCommentList'];
			
			$commentsList = $lastUpdatedData['commentsList'];
			$commentLikeList = $lastUpdatedData['commentLikeList'];
			
			$allLikesList = $lastUpdatedData['allLikesList'];

			//Zend_Debug::dump($photoCommentList); exit();
			$pointResult = $this->calculatePostPoints($posts, $postCommentsList, $postLikeList);
			//Zend_Debug::dump($pointResult); exit();
			
			$pointResult = $this->calculateAlbumPoints($pointResult, $albumsList, $albumCommentList);

// 			arsort($pointResult, SORT_REGULAR);
// 			Zend_Debug::dump($pointResult);
// 			exit();
				
			$pointResult = $this->calculatePhotoPoints($pointResult, $photoList, $photoCommentList);
// 			arsort($pointResult, SORT_NUMERIC);
// 			Zend_Debug::dump($pointResult);
// 			exit();
				
			$pointResult = $this->calculateCommentPoints($pointResult, $commentsList, $commentLikeList);
			$pointResult = $this->calculateLikesPoints($pointResult, $allLikesList);

			arsort($pointResult, SORT_NUMERIC);
			Zend_Debug::dump($pointResult);
		}else {
			echo "The file $filePath not exists";
		}
		
	}
	
}

?>