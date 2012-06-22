<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Collectors_Facebook1Controller extends Fancrank_Collectors_Controller_BaseController
{
    private $types = array(
        'fans'  => 'fans',
        'feed' => 'feed',
	'comments' => 'comments',
	'likes' => 'likes',
        'albums' => 'photo',
        'rankings'  => 'rankings'
    );

    public function init()
    {
        parent::init();
        
        // get the fanpage object
        /*
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
        */
    }

    public function batchfetchAction() {
    	$type = '_request';
		$fanpageId = $this->$type->getParam('fanpage_id');
		echo $fanpageId;
		//$accessToken = $this->_request->getParam('access_token');
		//$fancrankFB = new Service_FancrankFBService();
		//echo http_build_query(array());
		//$fancrankFB->collectFanpageInfo($fanpageId, $accessToken);
    }
    
    public function testgetAction() {
    	$fanpageId = '178384541065';
    	$fansIdsList = array('100000238528080','100000536039022','5705293','100000815659824','100003096353963');
    	$access_token='AAAFWUgw4ZCZB8BAO8ZCgMOINWwydm4xmEdqrN0ukBW2zJWi6JrNtG1d8iyADBEEBz6TZA36K4QTbaIAHQPZANFIQYbgAce88RwZATuV1M4swZDZD';
    	$facebookUsers = $this->getFansList($fansIdsList, $access_token);
    	
    	$fb1 = new Service_FancrankDB1Service();
    	$result = $fb1->saveFans($facebookUsers, $fanpageId);
    	Zend_Debug::dump($result);
    }
    
	public function collectAction() {
		$start = time();
		$fanpageId = $this->_request->getParam('fanpage_id');
		if(empty($fanpageId)) {
			die('miss fanpage_id');
		}
		$access_token='AAAFWUgw4ZCZB8BAO8ZCgMOINWwydm4xmEdqrN0ukBW2zJWi6JrNtG1d8iyADBEEBz6TZA36K4QTbaIAHQPZANFIQYbgAce88RwZATuV1M4swZDZD';
		$meUrl = 'https://graph.facebook.com/' .$fanpageId;
		$pageProfile = $this->httpCurl($meUrl, array('access_token'=>$access_token), 'get');
		
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		// init default db adapter
		$db = Zend_Db::factory($config->resources->db);
		$db->beginTransaction();
		$fb1 = new Service_FancrankDB1Service();
		$fb1->saveFanpage(json_decode($pageProfile), $access_token, $db);
		//Zend_Debug::dump(json_decode($pageProfile)); exit();
		
		
		$url = 'https://graph.facebook.com/' .$fanpageId .'/feed?access_token=' .$access_token;
		//$url= 'https://graph.facebook.com/178384541065_264991040242303/likes?value=1&limit=25';
		//get all posts from feed
		$posts = array();
		echo '<br/>Posts from feed---------------------------------------------------------------';
		$this->getPostsRecursive($url, 3, $posts);
		Zend_Debug::dump($posts);

		
		if(empty($posts)) {
			return;
		}
		//get all the likes from all posts in the feed
		echo '<br/>Likes from all posts---------------------------------------------------------------';
		$postLikeList = array();
		$this->getLikesFromMyPostRecursive($posts, $fanpageId, $access_token, 0, $postLikeList);
		Zend_Debug::dump($postLikeList);
		
		//get all comments recursively
		echo '<br/>All comments from feed---------------------------------------------------------------';		
		$commentsList = array();
		$this->getQueryRecursive($posts, 'comments', $fanpageId, $access_token, 0, $commentsList);
		//get all the likes from all comments
		Zend_Debug::dump($commentsList);
		
		//get all albums recursively
		$url = 'https://graph.facebook.com/' .$fanpageId .'/albums?access_token=' .$access_token;
		echo '<br/>All albums from fanpage---------------------------------------------------------------';
		$albumsList = array();
		$this->getFromUrlRecursive($url, 3, $albumsList);
		Zend_Debug::dump($albumsList);
		
		//get all photos recursively
		echo '<br/>All photos from fanpage---------------------------------------------------------------';
		$photoList = array();
		$this->getQueryRecursive($albumsList, 'photos', $fanpageId, $access_token, 0, $photoList);
		Zend_Debug::dump($photoList);
		
		$likeList = array();
		$albumLikesList = $this->getLikesFromMyPhotos($albumsList, $fanpageId);
		$photoLikesList = $this->getLikesFromMyPhotos($photoList, $fanpageId);

		$commentsList = array_merge($commentsList, $this->getCommentsFromPhotos($albumsList, $fanpageId), $this->getCommentsFromPhotos($photoList, $fanpageId));
		//get all the likes from all comments
		$commentLikeList = array();
		$this->getLikesFromCommentsRecursive($commentsList, $fanpageId, $access_token, 0, $commentLikeList);
		
		echo '<br/>total likes for ' .count($posts) . ' posts: ' .count($postLikeList);
		echo '<br/>total likes for ' .count($commentsList) . ' comments: ' .count($commentLikeList);
		echo '<br/>total likes for ' .count($albumsList) . ' albums ' .count($albumLikesList);
		echo '<br/>total likes for ' .count($photoList) . ' photos ' .count($photoLikesList);
		$allLikesList = array_merge($postLikeList, $commentLikeList, $albumLikesList, $photoLikesList);
		echo '<br/>Total likes : ' .count($allLikesList);


		$db->beginTransaction();
		$fb1->savePosts($posts, $fanpageId, $db);
		
		$fb1->saveAlbums($albumsList, $fanpageId, $db);
		
		$fb1->savePhotos($photoList, $fanpageId, $db);
		
		$fb1->saveComments($commentsList, $fanpageId, $db);
		
		$fb1->saveLikes($allLikesList, $fanpageId, $db);

		echo '<br/>All Fans Info--------------------------------------------------';
		$fansIdsList = $this->fansIdCollector($posts, $commentsList,  $allLikesList);
		//Zend_Debug::dump($fansIdsList);
		
		$facebookUsers = $this->getFansList($fansIdsList, $access_token);
		
		$result = $fb1->saveFans($facebookUsers, $fanpageId);
		Zend_Debug::dump($result);
		$db->commit();
		
		$stop = time() - $start;
		echo '<br />total execution time: ' .$stop;
		
		echo '<br/>start to save into database: ';
		//save posts
	}
	
	/*
	 * This method will retrieve all posts from giving url recursively 
	 * 
	 * @param string $url a next page url
	 * @param int numbers of recursive call
	 * @param mixed $result a callback result
	 * @return mixed return an array 
	 */
	private function getPostsRecursive($url, $level=2, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		$params['limit'] = 200;
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n'; exit();
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			$url = !empty($response->paging->next) ? $response->paging->next : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}
			$this->getPostsRecursive($url, $level, $result);
		} catch (Exception $e) {
			return array();
		}	
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
				//Zend_Debug::dump($post->comments);
				//$result[] = $post->likes->data;
				$postIds[] = $post->id;
				if($queryType === 'photos' && $post->count > 25 + $offset || $queryType !== 'photos' && $post->$queryType->count > 25 + $offset) {
					$extraPostIds[] = $post->id;
					$extraPosts [] = $post;
				}
			}
		}
	
		//echo 'Extra post id has more than '. $offset+25 . $queryType;
		//Zend_Debug::dump($extraPostIds);
		//Zend_Debug::dump($extraLikesPosts);
		//use bruteforce method
		//Zend_Debug::dump($this->bruteForceLoopQuery($postIds, $fanpageId, $access_token));
		//return;
		//echo $this->batchQueryBuilder($postIds); exit();
	
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//echo 'post id group';
		Zend_Debug::dump($postIdsGroup);
	
		//$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array(array('paramName'=>$queryType, 'limit'=>50)), $access_token);
		//Zend_Debug::dump($batchQueries); exit();
	
		try {
			$results = array();
			//note: we could implement this loop with multi thread for optimization later on.
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>$queryType, 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
				
			//$result = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
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
	
		//echo 'total' .$queryType .'count : ' .count($resultList);
		//echo '<br />next recursion--------------------------------<br/>';
		$this->getQueryRecursive($extraPosts, $queryType, $fanpageId, $access_token, $offset+25, $resultList);
	}
	
	private function getPhotosRecursive($posts, $queryType, $fanpageId, $access_token, $offset=0, &$resultList) {
		if(empty($posts) || $offset > 100) {
			return null;
		}
		$postIds = array();
		$extraPosts = array();
		$extraPostIds = array();
		foreach ($posts as $post) {
			//if(!empty($post->likes->count) && $post->likes->count >= 1 && preg_match("/{$post->from->id}_/", $post->id)) {
			if(!empty($post->count) && $post->count >= 1) {
				//Zend_Debug::dump($post->comments);
				//$result[] = $post->likes->data;
				$postIds[] = $post->id;
				if( $post->$queryType->count > 25 + $offset ) {
					$extraPostIds[] = $post->id;
					$extraPosts [] = $post;
				}
			}
		}
	
		echo 'Extra post id has more than '. $offset+25 . $queryType;
		Zend_Debug::dump($extraPostIds);
		//Zend_Debug::dump($extraLikesPosts);
		//use bruteforce method
		//Zend_Debug::dump($this->bruteForceLoopQuery($postIds, $fanpageId, $access_token));
		//return;
		//echo $this->batchQueryBuilder($postIds); exit();
	
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		echo 'post id group';
		Zend_Debug::dump($postIdsGroup);
	
		$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array(array('paramName'=>'photos', 'limit'=>50)), $access_token);
		//Zend_Debug::dump($batchQueries); exit();
	
		try {
			$results = array();
			//note: we could implement this loop with multi thread for optimization later on.
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>$queryType, 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
	
			//$result = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
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
					if(!empty($values->data)) {
						foreach ($values->data as $value) {
							echo $value->id .' ' .$queryType .'postId: '.$postIdsGroup[$groupKey][$key] .'<br />';
							$resultList[] = $value;
						}
					}
				}
			}
		}
	
		echo 'total' .$queryType .'count : ' .count($resultList);
		echo '<br />next recursion--------------------------------<br/>';
		$this->getQueryRecursive($extraPosts, $queryType, $fanpageId, $access_token, $offset+25, $resultList);
	}
	
	private function getLikesFromMyPost($posts, $fanpageId, $access_token) {
		$postIds = array();
		$extraLikesOnPostIds = array();
		foreach ($posts as $post) {
			//if(!empty($post->likes->count) && $post->likes->count >= 1 && preg_match("/{$post->from->id}_/", $post->id)) {
			if(!empty($post->likes->count) && $post->likes->count >= 1) {
				//Zend_Debug::dump($post->comments);
				//$result[] = $post->likes->data;
				$postIds[] = $post->id;
				if( $post->likes->count > 25 ) {
					$extraLikesOnPostIds[] = $post->id;
				}
			}
		}
		
		//echo 'Extra post id has more than 25 likes';
		//Zend_Debug::dump($extraLikesOnPostIds);
		//use bruteforce method 
		//Zend_Debug::dump($this->bruteForceLoopQuery($postIds, $fanpageId, $access_token));
		//return;
		//echo $this->batchQueryBuilder($postIds); exit();

		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//Zend_Debug::dump($postIdsGroup);
		
		$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array(array('paramName'=>'likes', 'limit'=>50)), $access_token);
		//Zend_Debug::dump($batchQueries); exit();
		
		try {
			$results = array();
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('likes'), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
			
			//$result = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
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
		
		echo 'total like count : ' .count($likesList);
		Zend_Debug::dump($likesList);
// 		foreach(json_decode($result) as $key=>$likes) {
// 			if($likes->code === 200 && !empty($likes->body)) {
// 				$likes = json_decode($likes->body);
// 				foreach ($likes->data as $like) {
// 					echo $like->id .' ' .$like->name . 'likes ' .$postIds[$key] .'<br />';
// 					$likesList[] = array(
// 							'fanpage_id'        => $fanpageId,
// 							'post_id'           => $postIds[$key],
// 							'facebook_user_id'  => $like->id,
// 							'post_type'         => 'post'
// 					);
// 				}
// 			}
// 		}
		//Zend_Debug::dump(json_decode($result));
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
	
	private function getLikesFromMyPostRecursive($posts, $fanpageId, $access_token, $offset=0, &$likesList) {
		if(empty($posts) || $offset > 100) {
			return null;
		}
		$postIds = array();
		$extraLikesPosts = array();
		$extraLikesOnPostIds = array();
		foreach ($posts as $post) {
			//if(!empty($post->likes->count) && $post->likes->count >= 1 && preg_match("/{$post->from->id}_/", $post->id)) {
			if(!empty($post->likes->count) && $post->likes->count >= 1) {
				//Zend_Debug::dump($post);
				//$result[] = $post->likes->data;
				$postIds[] = $post->id;
				if( $post->likes->count > 25 + $offset ) {
					$extraLikesOnPostIds[] = $post->id;
					$extraLikesPosts [] = $post;
				}
			}
		}
	
		//echo 'Extra post id has more than '. $offset+25 .' likes ';
		Zend_Debug::dump($extraLikesOnPostIds);
		//Zend_Debug::dump($extraLikesPosts);
		//use bruteforce method
		//Zend_Debug::dump($this->bruteForceLoopQuery($postIds, $fanpageId, $access_token));
		//return;
		//echo $this->batchQueryBuilder($postIds); exit();
	
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//echo 'post id group';
		Zend_Debug::dump($postIdsGroup);
	
		//$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array(array('paramName'=>'likes', 'limit'=>50)), $access_token);
		//Zend_Debug::dump($batchQueries); exit();
	
		try {
			$results = array();
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>'likes', 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
				
			//$result = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
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
	
		//echo 'total like count : ' .count($likesList);
		//echo '<br />next recursion--------------------------------<br/>';
		$this->getLikesFromMyPostRecursive($extraLikesPosts, $fanpageId, $access_token, $offset+25, $likesList);
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
				//Zend_Debug::dump($post->comments);
				//$result[] = $post->likes->data;
				$postIds[] = $post->id;
				if($post->like_count > 25 + $offset) {
					$extraPostIds[] = $post->id;
					$extraPosts [] = $post;
				}
			}
		}
		
		//echo 'Extra comment id has more than '. $offset+25 .'likes <br/>';
		//Zend_Debug::dump($extraPostIds);
		//Zend_Debug::dump($extraLikesPosts);
		//use bruteforce method
		//Zend_Debug::dump($this->bruteForceLoopQuery($postIds, $fanpageId, $access_token));
		//return;
		//echo $this->batchQueryBuilder($postIds); exit();
		
		$postIdsGroup = $this->arrayToGroups($postIds, 50);
		//echo 'comment id group';
		Zend_Debug::dump($postIdsGroup);
		
		//$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array(array('paramName'=>'likes', 'limit'=>50)), $access_token);
		//Zend_Debug::dump($batchQueries); exit();
		
		try {
			$results = array();
			//note: we could implement this loop with multi thread for optimization later on.
			foreach ($postIdsGroup as $postIds) {
				$batchQueries = Fancrank_Util_Util::batchQueryBuilder($postIds, array('paramName'=>'likes', 'offset'=>$offset), $access_token);
				$results[] = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
			}
		
			//$result = Fancrank_Util_Util::requestFacebookAPI_POST('https://graph.facebook.com/', $batchQueries);
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
		//echo 'total like count : ' .count($likesList);
		//echo '<br />next recursion--------------------------------<br/>';
		$this->getLikesFromCommentsRecursive($extraPosts, $fanpageId, $access_token, $offset+25, $likesList);
	}
	
	private function bruteForceLoopQuery($postsIds, $fanpageId, $access_token) {
		$baseUrl = 'https://graph.facebook.com/';
		$results = array();
		foreach ($postsIds as $postId) {
			$result = array();
			$url = $baseUrl .'/' .$postId .'/likes?access_token=' .$access_token;
			$this->getFromUrlRecursive($url, 2, $result);
			foreach ($result as $arr) {
				$results[] = array('post_id'=>$postId, '$fanpage_id'=>$fanpageId, 'type'=>'post', 'id'=>$arr->id, 'name'=>$arr->name);
			}
			
			//echo $url .'<br />';
		}
		return $results;
	}
	
	private function  arrayToGroups($source, $pergroup) {
		$grouped = array ();
		$groupCount = ceil ( count ( $source ) / $pergroup );
		$queue = $source;
		for($r = 0; $r < $groupCount; $r ++) {
			array_push ( $grouped, array_splice ( $queue, 0, $pergroup ) );
		}
		return $grouped;
	}
	
	private function getFromUrlRecursive($url, $level = 5, &$result) {
		if(empty($url) || $level == 0) {
			return array();
		}
		$level = $level - 1;
		$query = explode('?', $url);
		parse_str($query[1], $params);
		$params['limit'] = 200;
		//echo 'level: ' .$level .'url: ' .$query[0] .'?' .http_build_query($params) .'\n';
		$curlReturn = $this->httpCurl($query[0], $params, 'get');
		try {
			$response = json_decode($curlReturn);
			$url = !empty($response->paging->next) ? $response->paging->next : null;
			if(! empty($response->data)) {
				$result = array_merge((array)$result, (array)$response->data);
			}			
			$this->getPostsRecursive($url, $level, $result);
		} catch (Exception $e) {
			return array();
		}
	}
	
	private function batchQueryBuilder($ids, $access_token) {
		$params = 'ids=' .implode(',', $ids);
		return 'https://graph.facebook.com/?' .$params .'&access_token=' .$access_token;
	}
	
    private function requestFacebookAPI_GET($url, $arpost) {

    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url . "?" . $arpost);
    	curl_setopt($ch, CURLOPT_POST, false);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    	$result = curl_exec($ch);
    	curl_close($ch);
    
    	return $result;
    
    }
    

    private function requestFacebookAPI_POST($url, $arpost) {
    
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $arpost);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    	$result = curl_exec($ch);
    
    	curl_close($ch);
    
    	return $result;
    
    }
    
    /*
     * @return retrun an array of json objects 
     */
    private function responseParser($response, $queryType = array()) {
    	$arr = array();
    	foreach ($response as $key => $res){
			if($res['code'] === 200 && !empty($res['body'])) {
				$arr["$queryType[$key]"] = $res['body'];
			}
    	}
    	
    	return $arr;
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
    	Zend_Debug::dump($fansIdsGroup);
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
    
    private function search_key_r($array, $key, &$results)
    {
    	if (!is_array($array))
    		return;
 
    	if (array_key_exists($key, $array))
    		$results[] = $array[$key];
    
    	foreach ($array as $subarray)
    		$this->search_key_r($subarray, $key, $results);
    }
    
    private function searchByKey($key, $array)
    {
    	$results = array();
    
    	$this->search_key_r($array, $key, $results);
    
    	return $results;
    }
    
    /*
     * A function to encode a batch query in a single url
    *
    * @param $fanpageId fanpage id
    * @param $arr params after usesr id in url. Example: https://graph.facebook.com/userId/param
    * @param $access_token an access token
    * @param $defaultOption an array of additional default params
    */
    private function batchFanpageQueryBuilder($fanageId, $arr, $access_token, $defaultOption=array()) {
    	if(empty($access_token)) {
    		return array();
    	}
    	$method = 'GET';
    	$limit = 0;
    	$since = null;
    	$until = null;
    	$result = array();
    	foreach ($arr as $key => $value) {
    		if(is_array($value) && !empty($value)) {
    			$tmp = array('method'=>'GET', 'relative_url'=> '/' .$fanageId. '?');
    			foreach ($value as $k => $v) {
    				switch ($k){
    					case 'paramName':
    						if( $v === 'me' ) {
    							$tmp['relative_url'] = '/me?';
    						}else {
    							$tmp['relative_url'] = '/' .$fanageId .'/' .$v .'?';
    						}
    						break;
    					case 'method': $tmp['method'] = $v; break;
    					case 'relative_url': $tmp['relative_url'] = $v; break;
    					case 'limit': $tmp['relative_url'] .= '&' .'limit=' .$v; break;
    					case 'since': $tmp['relative_url'] .= '&' .'since=' .$v; break;
    					case 'until': $tmp['relative_url'] .= '&' .'until=' .$v; break;
    					default: break;
    				}
    			}
    			$result[] = $tmp;
    		}else if(is_string($value)) {
    			if( $value === 'me') {
    				$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId);
    			}else {
    				$result[] = array('method' => 'GET', 'relative_url' => '/' .$fanageId .'/' .$value);
    			}
    		}
    	}
    	 
    	//$arrpost = 'batch=' .json_encode($result) .'&access_token=' .$access_token;
    	return 'batch=' .json_encode($result) .'&access_token=' .$access_token;
    }
    
    public function appinfoAction() {
    	echo 'app info start: ';
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$config = array(
    			'appId'  => '376385369079791',
    			'secret' => '21f20fe69b53f7aebd13fee7488da3a7',
    			'cookie' => true,
    	);
    	$facebook = new Service_FancrankFBService($config);
    	$facebook->setAccessToken($this->_getParam('access_token'));
    	$user_profile = $facebook->api('/me');

    	Zend_Debug::dump($user_profile);
    		try {
    			/*
   				$facebook->api('/me/feed','POST',
   							array(
   								'message' => 'This is a test message from' .$user,
   								'link' => 'http://www.fancrank.local/app/app/post'
   							)
   						);
   				*/
    			$uid = $user_profile['id'];

    			$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id FROM page_admin WHERE uid = '.$uid.''));
				Zend_Debug::dump($pages);
				echo 'end';
       			}catch(FacebookApiException $e) {
    				$result = $e->getResult();
    				Zend_Debug::dump($result);
    				error_log(json_encode($result));
    				$user = null;
    			}
    }
    
    public function getpagesAction() {
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$uid = $this->_getParam('id');
    	$facebook = new Service_FancrankFBService();
    	$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id FROM page_admin WHERE uid = '.$uid.''));
    	foreach($pages as $k=>$v) {
    		echo 'page id#:'.$v['page_id'].'<br/>';
    	}
    }
    
    public function getpagelikesAction() {
    	$params=array('fanpage_id'=>'178384541065', 'access_token'=>'AAAFWUgw4ZCZB8BAO8ZCgMOINWwydm4xmEdqrN0ukBW2zJWi6JrNtG1d8iyADBEEBz6TZA36K4QTbaIAHQPZANFIQYbgAce88RwZATuV1M4swZDZD', 'limit'=>5);
    	$facebook = new Service_FancrankFBService();
    	$facebook->setAccessToken('AAAFWUgw4ZCZB8BAO8ZCgMOINWwydm4xmEdqrN0ukBW2zJWi6JrNtG1d8iyADBEEBz6TZA36K4QTbaIAHQPZANFIQYbgAce88RwZATuV1M4swZDZD');
		$likeID = $facebook->api(
				array( 'method' => 'fql.query', 'query' =>
				"select uid from page_fan where page_id = 178384541065" )
		);
		
		Zend_Debug::dump($likeID);
    }
    
    public function addtabAction() {
    	//install the tab
    	Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	$facebook = new Service_FancrankFBService();
    	$fanpage_id = '283104988451477';
    	$access_token = 'AAAFHFbxmJmgBACY4evXMsLuuKyilosy0ZArHAQXJZA27VnBVR2uDX6RXLnYuZAOsUn7hUwrVrB4l2QLSJMlAzwLhOjUyOklRfCg0TDfQwBssk8VqzQU';
    	/*
    	$client = new Zend_Http_Client;
    	$client->setUri('https://graph.facebook.com/' . $fanpage_id . '/tabs');
    	$client->setMethod(Zend_Http_Client::DELETE);
    	$client->setParameterPost('access_token', $access_token);
    	$client->setParameterPost('app_id', '359633657407080');
    	
    	$response = $client->request();
    	*/
    	try {
    		$result = $facebook->api("/283104988451477/tabs/app_376385369079791","delete", array('custom_name' => 'fancrank', 'access_token' => $access_token,  'app_id' => '376385369079791') );
    		Zend_Debug::dump($result);
    	} catch (Exception $e) {
    		$result = $e->getResult();
    		Zend_Debug::dump($result);
    	}
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
