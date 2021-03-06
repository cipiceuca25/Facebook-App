<?php
/*
 * A collector service
 */
class Service_FancrankDBService extends Fancrank_Db_Table {
	
	protected $_fanpageId;
	protected $_accessToken;
	protected $_fanpageSetting;

	public function __construct() {
		$args = func_get_args();
        $argsCount = func_num_args();
        if (method_exists($this, $constructor ='__construct' .$argsCount)) {
            call_user_func_array(array($this, $constructor), $args);
        }else {
        	throw new Exception('NO CONSTRUCTOR: ' . get_class() . $constructor, NULL, NULL); 
        } 
	}
	
	function __construct2($fanpage_id, $access_token)
	{
		$this->_fanpageId = $fanpage_id;
		$this->_accessToken = $access_token;
		
		$fanpageSettingModel = new Model_FanpageSetting();
		$settingData = $fanpageSettingModel->findRow($fanpage_id);
		if(!$settingData) {
			$settingData = $fanpageSettingModel->getDefaultSetting();
		}else {
			$settingData = $settingData->toArray();
		}
		
		$this->_fanpageSetting = $settingData;
	}
	
	/*
	 * save fanpage profile to database
	 * 
	 * @param $fanpageProfile a fanpage json object
	 * @param $access_token contains the security information for identifies user access resource permission
	 * @return true if success, else return false
	 */
	public function saveFanpageProfile($url, $data, $access_token=null) {
		
	}
	
	/*
	 * @return a string representation of a giving date in following format: yyyy-MM-dd HH:mm:ss 
	 */
	private function dateToStringForMysql($date) {
		if(!empty ($date)) {
			$date = new Zend_Date($date);
			return $date->toString('yyyy-MM-dd HH:mm:ss');
		}
		return null;
	}
	
	/*
	 * @param array $data facebook user object
	 * @return integer facebook user id
	 */
	public function saveFacebookUser($data) {
		if(empty ($data) || (empty($data->id))) {
			return false;
		}

		if (isset($data->languages)) {
			foreach($data->languages as $language) {
				$lang[] = $language->name;
			}
		} else {
			$lang = array();
		}
		
		$birthday = new Zend_Date(!empty($data->birthday) ? $data->birthday : null, Zend_Date::ISO_8601);
		$updated = new Zend_Date(!empty($data->updated_time) ? $data->updated_time : time(), Zend_Date::ISO_8601);
		 
		$facebookUserData = array(
				'facebook_user_id' 			=> $data->id,
				'facebook_user_name' 		=> !empty($data->name) ? $data->name : '',
				'facebook_user_first_name' 	=> !empty($data->first_name) ? $data->first_name : '',
				'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
				'facebook_user_email' 		=> !empty($data->email) ? $data->email : '',
				'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
				'facebook_user_avatar'    	=> sprintf('https://graph.facebook.com/%s/picture', $data->id),
				'facebook_user_lang'        => implode(',', $lang),
				'facebook_user_birthday'    => $birthday->toString('yyyy-MM-dd HH:mm:ss'),
				'facebook_user_access_token'=> !empty($data->access_token) ? $data->access_token : '',
				'updated_time' 				=> $updated->toString('yyyy-MM-dd HH:mm:ss'),
				'facebook_user_locale' 		=> !empty($data->locale) ? $data->locale : '',
				'hometown' 					=> !empty($data->hometown) ? $data->hometown : '',
				'current_location' 			=> !empty($data->current_location) ? $data->current_location : '',
				'bio' 						=> !empty($data->bio) ? $data->bio : ''
		);
		
		try {
			//save facebook user's relative information into facebook_users table
			$facebookUserModel = new Model_FacebookUsers();
			$facebookUser = $facebookUserModel->findRow($data->id);
			if(empty($facebookUser->facebook_user_id)) {
				$facebookUserModel->insert($facebookUserData);
			}else {
				
			}
			
			//echo('user saved\n');
			return $data->id;
		} catch (Exception $e) {
			//print $e->getMessage();
			$collectorLogger = Zend_Registry::get('collectorLogger');
			$collectorLogger->log(sprintf('Unable to save facebook user %s to database. Error Message: %s ', $facebookUser->id, $e->getMessage()), Zend_log::ERR);
		}
	}

	/*
	 * @param object $fanpageProfile fanpage object
	 * @return integer fanpage id
	 */
	public function saveFanpage($fanpageProfile) {
		if(empty ($fanpageProfile) || empty($fanpageProfile->id)) {
			return false;
		}
		
		//Zend_Debug::dump($fanpageProfile); exit();
		$fanpageData = array(
				'fanpage_id'            => $fanpageProfile->id,
				'fanpage_name'      	=> !empty($fanpageProfile->name) ? $fanpageProfile->name : '',
				'fanpage_category'     	=> !empty($fanpageProfile->category) ? $fanpageProfile->category : '',
				'fanpage_about'			=> !empty($fanpageProfile->about) ? $fanpageProfile->about : '',
				'fanpage_likes'         => !empty($fanpageProfile->likes) ? $fanpageProfile->likes : null,
				'fanpage_username'      => !empty($fanpageProfile->username) ? $fanpageProfile->username : '',
				'fanpage_is_published'  => !empty($fanpageProfile->is_published) ? 1 : 0,
				'fanpage_genre'   		=> !empty($fanpageProfile->genre) ? $fanpageProfile->genre : '',
				'fanpage_can_post'      => !empty($fanpageProfile->can_post) ? $fanpageProfile->can_post : null,
				'has_added_app'			=> !empty($fanpageProfile->has_added_app) ? $fanpageProfile->has_added_app : null,
				'access_token' 			=> $this->_accessToken,
		);
		
		//Zend_Debug::dump($fanpageData); exit();
		try {
			//save fanpage fanpage's relative information into facebook_users table
			$fanpages = new Model_Fanpages();
			$fanpages->saveAndUpdateById($fanpageData, array('id_field_name'=>'fanpage_id'));
			return $fanpageProfile->id;
		} catch (Exception $e) {
			//print $e->getMessage();
			$collectorLogger = Zend_Registry::get('collectorLogger');
			$collectorLogger->log(sprintf('Unable to save fanpage %s to database. Error Message: %s ', $fanpageProfile->id, $e->getMessage()), Zend_log::ERR);
		}
	}
	
	/*
	 * @return return an array of album id that were saved to database successfully
	 */
	public function saveAlbums($albums, $facebookUserId = null) {
		$rows = array();
		$albumModel = new Model_Albums();
		foreach($albums as $k => $album) {
			if( empty($album->id) ) continue;
			$created = new Zend_Date(!empty($album->created_time) ? $album->created_time : null, Zend_Date::ISO_8601);
			$updated = new Zend_Date(!empty($album->updated_time) ? $album->updated_time : null, Zend_Date::ISO_8601);
	
			$row = array(
					'album_id'				=> $album->id,
					'fanpage_id'			=> $this->_fanpageId,
					'facebook_user_id'		=> $facebookUserId,
					'album_name'			=> !empty($album->name) ? $album->name : '',
					'album_description'		=> !empty($album->description) ? $album->description : '',
					'album_location'		=> !empty($album->location) ? $album->location : '',
					'album_link'			=> !empty($album->link) ? $album->link : '',
					'album_cover_photo_id'	=> !empty($album->cover_photo) ? $album->cover_photo : null,
					'album_photo_count'		=> !empty($album->count) ? $album->count : 0,
					'album_type'			=> !empty($album->type) ? $album->type : '',
					'updated_time'			=> $updated->toString('yyyy-MM-dd HH:mm:ss'),
					'created_time'			=> $created->toString('yyyy-MM-dd HH:mm:ss')
			);
	
			$rows[] = $row['album_id'];
			try {
				//save fanpage's album's relative information into facebook_users table
				$albumModel->saveAndUpdateById($row, array('id_field_name'=>'album_id'));
				//echo sprintf('album %s saved\n', $album->id);
			} catch (Exception $e) {
				print $e->getMessage();
				$collectorLogger = Zend_Registry::get('collectorLogger');
				$collectorLogger->log(sprintf('Unable to save album %s from fanpage %s to database. Error Message: %s ', $album->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
			}
		}
		//return array('post_id_list'=>$rows, 'fans_id_list'=> '');
		return $rows;
	}
	
	public function savePhotos($photos) {
		$rows = array();
		$photoModel = new Model_Photos();
		foreach($photos as $photo) {
			if( empty($photo->id) ) continue;
			try {
				$created = new Zend_Date(!empty($photo->created_time) ? $photo->created_time : null, Zend_Date::ISO_8601);
				$updated = new Zend_Date(!empty($photo->updated_time) ? $photo->updated_time : null, Zend_Date::ISO_8601);
				$row = array(
						'photo_id'          => $photo->id,
						'fanpage_id'        => $this->_fanpageId,
						'facebook_user_id'	=> $photo->from->id,
						'photo_album_id'    => $photo->photo_album_id,
						'photo_source'      => !empty($photo->source) ? $photo->source : null,
						'photo_caption'     => isset($photo->name) ? $photo->name : '',
						'photo_picture'		=> !empty($photo->picture) ? $photo->picture : null,
						'photo_position'	=> !empty($photo->position) ? $photo->position : null,
						'photo_width'       => !empty($photo->width) ? $photo->width : null,
						'photo_height'      => !empty($photo->height) ? $photo->height : null,
						'updated_time'      => $created->toString('yyyy-MM-dd HH:mm:ss'),
						'created_time'      => $updated->toString('yyyy-MM-dd HH:mm:ss')
				);
		
				$rows[] = $row['photo_id'];
				//save fanpage's album's relative information into facebook_users table
				$photoModel->saveAndUpdateById($row, array('id_field_name'=>'photo_id'));
				//Zend_Debug::dump($row);
			} catch (Exception $e) {
				print $e->getMessage();
				$collectorLogger = Zend_Registry::get('collectorLogger');
				$collectorLogger->log(sprintf('Unable to save photo %s from fanpage %s to database. Error Message: %s ', $photo->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
				return;
			}
		}
		
		return $rows;
		//Zend_Debug::dump($rows); exit();
	}

	/*
	 * @return return an array of post's id that were saved to the database
	*/
	public function savePosts($posts) {
		$rows = array();
		$postModel = new Model_Posts();
		foreach($posts as $k => $post) {
			if( empty($post->id) ) continue;
			$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null, Zend_Date::ISO_8601);
			$updated = new Zend_Date(!empty($post->updated_time) ? $post->updated_time : null, Zend_Date::ISO_8601);

			$row = array(
					'post_id'               => $post->id,
					'facebook_user_id'      => $post->from->id,
					'fanpage_id'            => $this->_fanpageId,
					'post_message'          => isset($post->message) ? $this->quoteInto($post->message) : '',
					'picture'				=> !empty($post->picture) ? $post->picture : '',
					'link'					=> !empty($post->link) ? $post->link : '',
					'post_type'             => !empty($post->type) ? $post->type : '',
					'status_type'           => !empty($post->status_type) ? $post->status_type : '',
					'post_description'		=> !empty($post->description) ? $this->quoteInto($post->description) : '',
					'post_caption'			=> !empty($post->caption) ? $this->quoteInto($post->caption) : '',
					'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
					'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
					'post_comments_count'   => !empty($post->comments->count) ? $post->comments->count : 0,
					'post_likes_count'      => isset($post->likes) && isset($post->likes->count) ? $post->likes->count : 0
			);

			if (property_exists($post, 'application') && isset($post->application->id)) {
				$row['post_application_id'] = $post->application->id;
				$row['post_application_name'] = empty($post->application->name) ? null : $post->application->name;
			} else {
				$row['post_application_id'] = null;
				$row['post_application_name'] = null;
			}

			$rows[] = $row['post_id'];
			try {
				//save fanpage's post's relative information into post table
				//Zend_Debug::dump($row);
				$postModel->saveAndUpdateById($row, array('id_field_name'=>'post_id'));
			} catch (Exception $e) {
				print $e->getMessage();
				$collectorLogger = Zend_Registry::get('collectorLogger');
				$collectorLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $post->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
				return;
			}
		}
		//return array('post_id_list'=>$rows, 'fans_id_list'=> $fansId);
		return $rows;
	}
	
	public function saveComments($commentsList) {
		$rows = array();
		$commentModel = new Model_Comments ();
		foreach ( $commentsList as $k => $comment ) {
			if (empty ( $comment->id ) || empty ( $comment->created_time ) || empty($comment->from->id))	continue;

			$created = new Zend_Date ( ! empty ( $comment->created_time ) ? $comment->created_time : null, Zend_Date::ISO_8601);
			
			$parentid = $comment->id;
			if(strrpos($comment->id, '_') > 0) {
				$parentid = substr($comment->id, 0, strrpos($comment->id, '_'));
			}
			
			$row = array (
					'comment_id' => $comment->id,
					'fanpage_id' => $this->_fanpageId,
					'comment_post_id' => $parentid,
					'facebook_user_id' => $comment->from->id,
					'comment_message' => $this->quoteInto($comment->message),
					'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
					'comment_likes_count' => isset ( $comment->likes ) ? $comment->likes : 0,
					'comment_type' => $comment->comment_type 
			);
			
			$rows [] = $row ['comment_id'];
			// $fansId[] = $comment->from->id;
			try {
				// save fanpage's post's relative information into post table
				// Zend_Debug::dump($row);
				$commentModel->saveAndUpdateById ($row, array('id_field_name' =>'comment_id'));
			} catch ( Exception $e ) {
				print $e->getMessage ();
				$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
				$collectorLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $comment->id, $this->_fanpageId, $e->getMessage () ), Zend_log::ERR );
				return;
			}
		}

		//return array('comment_id_list'=>$rows, 'fans_id_list'=> $fansId);
		return $rows;
	}

// 	public function saveLikes($likesList) {
// 		if(empty($likesList)) {
// 			return;
// 		}
// 		$likes_model = new Model_Likes;
// 		$cols = array('fanpage_id', 'post_id', 'facebook_user_id', 'post_type', 'updated_time');
// 		$update = array('post_type');
// 		try {
// 			Zend_Debug::dump($likes_model->insertMultiple($likesList, $cols, $update));
// 		} catch (Exception $e) {
// 			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
// 			$collectorLogger->log ( sprintf ( 'Unable to save likes %s',  $e->getMessage ()), Zend_log::ERR);
// 		}
// 	}
	
	public function saveLikes($likesList) {
		$likeModel = new Model_Likes();
		foreach ($likesList as $like) {
			$found = $likeModel->find($like['fanpage_id'], $like['post_id'], $like['facebook_user_id'])->current();
			try {
				if (empty($found)) {
					if (isset($like['target'])) {
						unset($like['target']);
					}
					$likeModel->insert($like);
				} else {
					if($found->likes === 0) {
						$found->likes = 1;
						$found->updated_time = $like['updated_time'];
						$found->save();
					}
				}				
			} catch (Exception $e) {
				$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
				$collectorLogger->log ( sprintf ( 'Unable to save likes %s %s',  $e->getMessage (), implode(' ', $like)), Zend_log::ERR);
			}
		}	
	}
	
	public function saveFans($fansList) {
		$result = array();
		$facebookUserModel = new Model_FacebookUsers();
		$fansModel = new Model_Fans();
		foreach ($fansList as $data) {
			$updated = new Zend_Date(!empty($data->updated_time) ? $data->updated_time : null, Zend_Date::ISO_8601);
			
			try {
				$facebookUserData = array(
						'facebook_user_id' 			=> $data->id,
						'facebook_user_first_name' 	=> !empty($data->first_name) ? $data->first_name : '',
						'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
						'facebook_user_name'		=> !empty($data->name) ? $data->name : $data->first_name .' ' .$data->last_name,
						'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
						'updated_time' 				=> $updated->toString ( 'yyyy-MM-dd HH:mm:ss' ),
						'facebook_user_locale' 		=> !empty($data->facebook_user_locale) ? $data->locale : '',
				);
				
				//save facebook user's relative information into facebook_users table
				$facebookUserModel->saveAndUpdateById($facebookUserData, array('id_field_name'=>'facebook_user_id'));
				
				$fansData = array(
						'facebook_user_id'  => $facebookUserData['facebook_user_id'],
						'fanpage_id'        => $this->_fanpageId,
						'fan_name'			=> trim($facebookUserData['facebook_user_name']),
						'fan_first_name'	=> $facebookUserData['facebook_user_first_name'],
						'fan_last_name'		=> $facebookUserData['facebook_user_last_name'],
						'fan_gender'		=> $facebookUserData['facebook_user_gender']
				);
				
				//add the fan if it doesnt exist
				$select = $fansModel->select();
				$select->where($fansModel->quoteInto('facebook_user_id = ? AND fanpage_id = ?', $facebookUserData['facebook_user_id'], $this->_fanpageId));
				$fan = $fansModel->fetchRow($select);
				
				if (empty($fan)) {
					$fansModel->insert($fansData);
				}else {
					$fansModel->fan_name = $fansData['fan_name'];
					$fansModel->fan_first_name = $fansData['fan_first_name'];
					$fansModel->fan_last_name = $fansData['fan_last_name'];
					$fan->save();
				}
				
				$result[] = $fansData;
			} catch (Exception $e) {
				print $e->getMessage();
				$collectorLogger = Zend_Registry::get('collectorLogger');
				$collectorLogger->log(sprintf('Unable to save fan user %s fanpage %s to database. Error Message: %s ', $data->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
			}
		}
		
		return $result;
	}

	public function saveAndUpdateFans($fansList, $pointResult, $pointLogEnable=false) {
		$result = array();
		$facebookUserModel = new Model_FacebookUsers();
		$fansModel = new Model_Fans();
		foreach ($fansList as $data) {
			$updated = new Zend_Date(!empty($data->updated_time) ? $data->updated_time : null, Zend_Date::ISO_8601);
				
			try {
				$facebookUserData = array(
						'facebook_user_id' 			=> $data->id,
						'facebook_user_first_name' 	=> !empty($data->first_name) ? $data->first_name : '',
						'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
						'facebook_user_name'		=> !empty($data->name) ? $data->name : $data->first_name .' ' .$data->last_name,
						'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
						'updated_time' 				=> $updated->toString ( 'yyyy-MM-dd HH:mm:ss' ),
						'facebook_user_locale' 		=> !empty($data->facebook_user_locale) ? $data->locale : ''
				);
	
				//save facebook user's relative information into facebook_users table
				$facebookUserModel->saveAndUpdateById($facebookUserData, array('id_field_name'=>'facebook_user_id'));
				
				$updateTime = new Zend_Date(time(), Zend_Date::TIMESTAMP);
				
				$fansData = array(
						'facebook_user_id'  => $facebookUserData['facebook_user_id'],
						'fanpage_id'        => $this->_fanpageId,
						'fan_name'			=> trim($facebookUserData['facebook_user_name']),
						'fan_first_name'	=> $facebookUserData['facebook_user_first_name'],
						'fan_last_name'		=> $facebookUserData['facebook_user_last_name'],
						'fan_gender'		=> $facebookUserData['facebook_user_gender'],
						'updated_time'		=> $updateTime->toString('YYYY-MM-dd HH:mm:ss')
				);
	
				$fansModel = new Model_Fans($facebookUserData['facebook_user_id'], $this->_fanpageId);
				
				if ($fansModel->isNewFan()) {
					if(isset($pointResult[$facebookUserData['facebook_user_id']])) {
						$fansData['fan_exp'] = $pointResult[$facebookUserData['facebook_user_id']]['total_points'];
					}
					$fansModel->insertNewFan($fansData);
				} else {
					$fanProfile = $fansModel->getFanProfile();
					$fanProfile->fan_name = $fansData['fan_name'];
					$fanProfile->fan_first_name = $fansData['fan_first_name'];
					$fanProfile->fan_last_name = $fansData['fan_last_name'];
					$fanProfile->updated_time = $fansData['updated_time'];
					if (isset($pointResult[$facebookUserData['facebook_user_id']])) {
						//echo $facebookUserData['facebook_user_id'] .'<br/>';
						$fansModel->updateFanPoints($pointResult[$facebookUserData['facebook_user_id']]['total_points']);
					}

					$fansModel->updateFanProfile();
				}

				if($pointLogEnable && isset($pointResult[$facebookUserData['facebook_user_id']]['point_log'])) {
					$pointLogModel = new Model_PointLog();
					foreach ($pointResult[$facebookUserData['facebook_user_id']]['point_log'] as $pointLog) {
						$pointLog['fanpage_id'] = $this->_fanpageId;
						$pointLog['facebook_user_id'] = $facebookUserData['facebook_user_id'];
						$pointLogModel->insert($pointLog);
					}
				}
				$result[] = $fansData;
			} catch (Exception $e) {
				print $e->getMessage();
				$collectorLogger = Zend_Registry::get('collectorLogger');
				$collectorLogger->log(sprintf('Unable to save fan user %s fanpage %s to database. Error Message: %s ', $data->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
			}
		}
	
		return $result;
	}
	
	public function savePost($post, $enablePointLog=false) {
		$postData = Model_Posts::preparePostData($post, $this->_fanpageId);
		$postModel = new Model_Posts();
		$db = Zend_Db_Table::getDefaultAdapter();
		$pointLogModel = new Model_PointLog();
		
		try {
			$db->beginTransaction();
			$foundPost = $postModel->find($post->id)->current();
			
			if ($foundPost) {
				foreach ($postData as $key=>$row) {
					$foundPost->{$key} = $row;
				}
				$foundPost->save();
			} else {
				if ($postData['facebook_user_id'] == $postData['fanpage_id']) {
					// handle admin post
					$postModel->insert($postData);
				} else {
					// handle user post
					$postModel->insert($postData);
				
					// update fan profile
					$fan = new Model_Fans($postData['facebook_user_id'], $postData['fanpage_id']);
					if (!$fan->isNewFan()) {
						$fan->updateFanPoints($this->_fanpageSetting['point_post_normal']);
						$fan->updateFanProfile();
					}
				
					// update point log data
					$pointLog = array();
					$pointLog['fanpage_id'] = $postData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $postData['facebook_user_id'];
					$pointLog['object_id'] = $postData['post_id'];
					$pointLog['object_type'] = 'posts';
					$pointLog['giving_points'] = $this->_fanpageSetting['point_post_normal'];
					$pointLog['note'] = 'post on fanpage';
					echo $pointLogModel->insert($pointLog);
				}
			}
			
			// commit all update
			$db->commit();
			$db->closeConnection();
			return $post;
		} catch (Exception $e) {
			print $e->getMessage();
			$db->rollBack();
			$db->closeConnection();
			$collectorLogger = Zend_Registry::get('collectorLogger');
			$collectorLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $post->id, $this->_fanpageId, $e->getMessage()), Zend_log::ERR);
			return array();
		}
		return array();
	}
	
	public function saveComment($comment, $post=false, $enablePointLog=false) {
		if (!$post) {
			return array();
		}
		
		$commentModel = new Model_Comments();
		$db = Zend_Db_Table::getDefaultAdapter();
		
		try {
			$db->beginTransaction();
			
			$foundComment = $commentModel->findRow($comment->id); 
			//Zend_Debug::dump($foundComment->toArray());
			if ($foundComment) {
				$commentData = Model_Comments::prepareCommentData($comment);
				foreach ($commentData as $key=>$row) {
					$foundComment->{$key} = $row;
				}
				//$foundComment->save();
			} else {
				if ($comment->from->id != $this->_fanpageId && $post->from->id == $this->_fanpageId &&
						($commentCount = $commentModel->getUserCommentCountByPost($post->id, $comment->from->id)) < $this->_fanpageSetting['point_comment_limit']) {
					// handle comment on admin post
					Zend_Debug::dump($comment->from->id .' admin post : ' .$commentCount);
					$this->saveUserCommentOnAdminPost($commentModel, $comment, $commentCount, $post);
				} else if ($post->from->id != $this->_fanpageId) {
					// handle comment on non-admin post
					$this->saveCommentOnUserPost($commentModel, $comment, $post);
					Zend_Debug::dump($comment->from->id .' user post : ');
				}
			}
			$db->commit();
			$db->closeConnection();
		} catch (Exception $e) {
			echo $e->getMessage() .$e->getCode();
			$db->rollBack();
			$db->closeConnection();
		}
	}
	
	private function saveUserCommentOnAdminPost(Model_Comments $commentModel, $comment, $commentCount, $post) {
		$bonus = 0;
		$commentData = $commentModel->prepareCommentData($comment);
		// insert new comment into database
		echo 'insert ' .$commentModel->insert($commentData) .'<br/>';
		// check bonus
		$postTime = new Zend_Date($post->created_time, Zend_Date::ISO_8601);
		$commentTime = new Zend_Date($comment->created_time, Zend_Date::ISO_8601);
		$timeDifferentInMinute = floor(($commentTime->getTimestamp() - $postTime->getTimestamp()) / 60);
		if ($timeDifferentInMinute < $this->_fanpageSetting['point_bonus_duration']) {
			$bonus = $this->_fanpageSetting['point_comment_limit'] - $commentCount;
		}
		
		// update fan profile
		$fan = new Model_Fans($commentData['facebook_user_id'], $commentData['fanpage_id']);
		if (!$fan->isNewFan()) {
			$fan->updateFanPoints($this->_fanpageSetting['point_comment_admin']+$bonus);
			$fan->updateFanProfile();
			echo 'update fan point';
		}
		
		// update pointlog
		$pointLogModel = new Model_PointLog();
		$pointLog = array();
		$pointLog['fanpage_id'] = $commentData['fanpage_id'];
		$pointLog['facebook_user_id'] = $commentData['facebook_user_id'];
		$pointLog['object_id'] = $commentData['comment_post_id'];
		$pointLog['object_type'] = 'comments';
		$pointLog['giving_points'] = $this->_fanpageSetting['point_comment_admin'] + $bonus;
		$pointLog['bonus'] = $bonus;
		$pointLog['note'] = 'comment on admin object , bonus : ' .$bonus;
		$pointLogModel->insert($pointLog);
		
		return $commentData;
	}
	
	private function saveCommentOnUserPost(Model_Comments $commentModel, $comment, $post) {
		$commentData = $commentModel->prepareCommentData($comment);
		// insert new comment into database
		$commentModel->insert($commentData);

		
		// get the list of unique user id that have interaction with the post
		$postModel = new Model_Posts();
		$postUniqueList = $postModel->getUniqueInteractionList($post->id);
		
		if ($comment->from->id !== $post->id) {
		
			// check virginity break
			$pointLogModel = new Model_PointLog();
			$pointLog = array();
			$givingPoint = 0;
			if (in_array($comment->from->id, $postUniqueList)) {
				// update point log data
				$givingPoint = $this->_fanpageSetting['point_post_comment'];
				$pointLog['fanpage_id'] = $commentData['fanpage_id'];
				$pointLog['facebook_user_id'] =  $post->from->id;
				$pointLog['object_id'] = $post->id;
				$pointLog['object_type'] = 'get_comment';
				$pointLog['giving_points'] = $givingPoint;
				$pointLog['note'] = 'receive point from comment ' .$comment->id;
				Zend_Debug::dump($pointLog);
				echo 'break virgin' .$pointLogModel->insert($pointLog);
			} else {
				if (count($postUniqueList) === 0) {
					// update point log data
					$givingPoint = $this->_fanpageSetting['point_virginity'] + $this->_fanpageSetting['point_post_comment'];
					$pointLog['fanpage_id'] = $commentData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $post->from->id;
					$pointLog['object_id'] = $post->id;
					$pointLog['object_type'] = 'get_virginity_comment';
					$pointLog['giving_points'] = $givingPoint;
					$pointLog['bonus'] = $this->_fanpageSetting['point_virginity'];
					$pointLog['note'] = 'receive unique from comment' .$comment->id .'and ' .$this->_fanpageSetting['point_virginity'] .'bonus on breaking post virginity';
					Zend_Debug::dump($pointLog);
					echo 'break virgin' .$pointLogModel->insert($pointLog);
				} else {
					// update point log data, giving point for unique comment in user post
					$givingPoint = $this->_fanpageSetting['point_post_comment'] + 1;
					$pointLog = array();
					$pointLog['fanpage_id'] = $commentData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $post->from->id;
					$pointLog['object_id'] = $post->id;
					$pointLog['object_type'] = 'get_unique_comment';
					$pointLog['giving_points'] = $this->_fanpageSetting['point_post_comment'] + 1;
					$pointLog['bonus'] = 1;
					$pointLog['note'] = 'receive point from unique comment ' .$comment->id;
					Zend_Debug::dump($pointLog);
					echo $pointLogModel->insert($pointLog);
				}				
			}
			
			// update post owner profile
			$fan = new Model_Fans($post->from->id, $commentData['fanpage_id']);
			if (!$fan->isNewFan()) {
				$fan->updateFanPoints($givingPoint);
				$fan->updateFanProfile();
			}
		}

		return $commentData;
	}
	
	public function saveCommentLike($like, $comment, $enablePointLog=false) {
		$db = Zend_Db_Table::getDefaultAdapter();
		$likeModel = new Model_Likes();
		$likeData = Model_Likes::prepareCommentLikeData($like, $comment);
		
		try {
			$db->beginTransaction();
			$foundLike = $likeModel->find($likeData['fanpage_id'], $likeData['post_id'], $likeData['facebook_user_id'])->current();
			//Zend_Debug::dump($foundLike);
			//Zend_Debug::dump($likeData); $db->closeConnection(); return;
			if (!$foundLike) {
				// insert new like
				Zend_Debug::dump($likeModel->insert($likeData));
				
				// filter self like
				if ($comment->from->id != $likeData['facebook_user_id']) {
					$pointLogModel = new Model_PointLog();
						
					// update non admin fan profile and point log
					if ($comment->from->id != $likeData['fanpage_id']) {
						// update fan profile
						$fan = new Model_Fans($comment->from->id, $likeData['fanpage_id']);
						if (!$fan->isNewFan()) {
							$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
							$fan->updateFanProfile();
						}
						
						// update get like point log
						$pointLog = array();
						$pointLog['fanpage_id'] = $likeData['fanpage_id'];
						$pointLog['facebook_user_id'] =  $comment->from->id;
						$pointLog['object_id'] = $comment->id;
						$pointLog['object_type'] = 'get_likes';
						$pointLog['giving_points'] = $this->_fanpageSetting['point_like_normal'];
						$pointLog['note'] = 'receive likes on comment';
						$pointLogModel->insert($pointLog);
						Zend_Debug::dump($pointLog);
					}
					
					// update non admin fan profile and point log
					if ($likeData['facebook_user_id'] != $likeData['fanpage_id']) {
						// update fan profile
						$fan = new Model_Fans($likeData['facebook_user_id'], $likeData['fanpage_id']);
						if (!$fan->isNewFan()) {
							$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
							$fan->updateFanProfile();
						}
						
						// update like point log
						$pointLog = array();
						$pointLog['fanpage_id'] = $likeData['fanpage_id'];
						$pointLog['facebook_user_id'] =  $likeData['facebook_user_id'];
						$pointLog['object_id'] = $comment->id;
						$pointLog['object_type'] = 'likes';
						$pointLog['giving_points'] = $this->_fanpageSetting['point_like_normal'];
						$pointLog['note'] = 'likes on comment';
						
						$pointLogModel->insert($pointLog);
						Zend_Debug::dump($pointLog);
					}
				}
			} else {
				$foundLike->likes = 1;
				$update = new Zend_Date();
				$foundLike->updated_time = $update->toString('yyyy-MM-dd HH:mm:ss');
				$foundLike->save();
			}
			
			$db->commit();
			$db->closeConnection();
			
			return $likeData;
		} catch(Exception $e) {
			print $e->getMessage();
			$db->rollBack();
			$db->closeConnection();
		}
		
	}
	
	public function savePostLike($like, $post, $enablePointLog=false) {
		$db = Zend_Db_Table::getDefaultAdapter();
		$likeModel = new Model_Likes();
		$likeData = Model_Likes::preparePostLikeData($like, $post);
		
		try {
			$db->beginTransaction();
			$foundLike = $likeModel->find($likeData['fanpage_id'], $likeData['post_id'], $likeData['facebook_user_id'])->current();
			
			if (!$foundLike) {
				// insert new like
				
				if ($post->from->id === $this->_fanpageId && $like->id !== $this->_fanpageId) {
					// handle like on admin post
					Zend_Debug::dump($post->from->id .' admin post : like id :' .$like->id);
					$this->saveUserLikeOnAdminPost($likeModel, $likeData, $post);
				} else if ($post->from->id !== $this->_fanpageId && $post->from->id !== $like->id) {
					// handle like on non-admin post
					$this->saveLikeOnUserPost($likeModel, $likeData, $post);
					Zend_Debug::dump($post->from->id .' user post : like id :' .$like->id);
				}
				Zend_Debug::dump($likeModel->insert($likeData));
			} else {
				$foundLike->likes = 1;
				$update = new Zend_Date();
				$foundLike->updated_time = $update->toString('yyyy-MM-dd HH:mm:ss');
				$foundLike->save();
			}
			
			$db->commit();
			$db->closeConnection();
				
			return $likeData;
		} catch (Exception $e) {
			print $e->getMessage();
			$db->rollBack();
			$db->closeConnection();
		}	
		
	}
	
	private function saveUserLikeOnAdminPost(Model_Likes $likeModel, $likeData, $post) {
		// apply double point bonus
		$bonus = 0;
		$postTime = new Zend_Date($post->created_time, Zend_Date::ISO_8601);
		$now = new Zend_Date();
		$timeDifferentInMinute = floor(($now->getTimestamp() - $postTime->getTimestamp()) / 60);
			
		if ($timeDifferentInMinute < $this->_fanpageSetting['point_bonus_duration']) {
			$bonus = $this->_fanpageSetting['point_like_bonus'];
		}
		
		// update fan profile
		$fan = new Model_Fans($likeData['facebook_user_id'], $likeData['fanpage_id']);
		if (!$fan->isNewFan()) {
			$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
			$fan->updateFanProfile();
		}
		
		//update like point log
		$pointLogModel = new Model_PointLog();
		$pointLog = array();
		$pointLog['fanpage_id'] = $likeData['fanpage_id'];
		$pointLog['facebook_user_id'] =  $likeData['facebook_user_id'];
		$pointLog['object_id'] = $post->id;
		$pointLog['object_type'] = 'likes';
		$pointLog['giving_points'] = $this->_fanpageSetting['point_like_admin'] + $bonus;
		$pointLog['bonus']= $bonus;
		$pointLog['note'] = empty($bonus) ? 'likes on admin post' : sprintf('likes on admin post, %s bonus for likes within %s minutes', $bonus, $this->_fanpageSetting['point_bonus_duration']);
		$pointLogModel->insert($pointLog);
		
	}
	
	private function saveLikeOnUserPost(Model_Likes $likeModel, $likeData, $post) {
		
		// get the list of unique user id that have interaction with the post
		$postModel = new Model_Posts();
		$postUniqueList = $postModel->getUniqueInteractionList($post->id);
		$pointLogModel = new Model_PointLog();
		// check virginity break
		if (!($isUnique = in_array($likeData['facebook_user_id'], $postUniqueList))) {
				
			// check virginity break
			if (count($postUniqueList) === 0) {
				// update get like point log
				$pointLog = array();
				$givingPoint = $this->_fanpageSetting['point_like_normal'] + $this->_fanpageSetting['point_virginity'];
				$pointLog['fanpage_id'] = $likeData['fanpage_id'];
				$pointLog['facebook_user_id'] =  $post->from->id;
				$pointLog['object_id'] = $post->id;
				$pointLog['object_type'] = 'get_virginity_like';
				$pointLog['giving_points'] = $this->_fanpageSetting['point_like_normal'] + $this->_fanpageSetting['point_virginity'] + 1;
				$pointLog['bonus']= $this->_fanpageSetting['point_virginity'] + 1;
				$pointLog['note'] = 'receive like and ' .($this->_fanpageSetting['point_virginity'] + 1) .' bonus on breaking virginity and unique';
				$pointLogModel->insert($pointLog);
				Zend_Debug::dump($pointLog);
					
				// update owner post fan profile
				$fan = new Model_Fans($post->from->id,  $likeData['fanpage_id']);
				if (!$fan->isNewFan()) {
					$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
					$fan->updateFanProfile();
				}
								
				// update like point log
				$pointLog = array();
				$pointLog['fanpage_id'] = $likeData['fanpage_id'];
				$pointLog['facebook_user_id'] =  $likeData['facebook_user_id'];
				$pointLog['object_id'] = $post->id;
				$pointLog['object_type'] = 'likes';
				$pointLog['giving_points'] = $this->_fanpageSetting['point_like_normal'];
				$pointLog['note'] = 'likes on user post';
				$pointLogModel->insert($pointLog);
				
				// update liker fan profile
				$fan = new Model_Fans($likeData['facebook_user_id'],  $likeData['fanpage_id']);
				if (!$fan->isNewFan()) {
					$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
					$fan->updateFanProfile();
				}
			} else {
				// update get like point log
				$pointLog = array();
				$givingPoint = 0;
				if ($isUnique) {
					// update get like point log
					$givingPoint = $this->_fanpageSetting['point_like_normal'] + 1;
					$pointLog['fanpage_id'] = $likeData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $post->from->id;
					$pointLog['object_id'] = $post->id;
					$pointLog['object_type'] = 'get_unique_likes';
					$pointLog['giving_points'] = $givingPoint;
					$pointLog['bonus']= 1;
					$pointLog['note'] = 'receive likes on post and 1 bonus on unique' ;
					$pointLogModel->insert($pointLog);
					Zend_Debug::dump($pointLog);
						
				} else {
					$givingPoint = $this->_fanpageSetting['point_like_normal'];
					$pointLog['fanpage_id'] = $likeData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $post->from->id;
					$pointLog['object_id'] = $post->id;
					$pointLog['object_type'] = 'get_likes';
					$pointLog['giving_points'] = $givingPoint;
					$pointLog['note'] = 'receive likes on post';
					$pointLogModel->insert($pointLog);
					Zend_Debug::dump($pointLog);
				}
					
				// update owner post fan profile
				$fan = new Model_Fans($post->from->id,  $likeData['fanpage_id']);
				if (!$fan->isNewFan()) {
					$fan->updateFanPoints($givingPoint);
					$fan->updateFanProfile();
				}
					
				// update liker point log and fanprofile
				if ($likeData['facebook_user_id'] != $likeData['fanpage_id']) {
					// update liker fan profile
					$fan = new Model_Fans($likeData['facebook_user_id'],  $likeData['fanpage_id']);
					if (!$fan->isNewFan()) {
						$fan->updateFanPoints($this->_fanpageSetting['point_like_normal']);
						$fan->updateFanProfile();
					}
					
					$pointLog = array();
					$pointLog['fanpage_id'] = $likeData['fanpage_id'];
					$pointLog['facebook_user_id'] =  $likeData['facebook_user_id'];
					$pointLog['object_id'] = $likeData['post_id'];
					$pointLog['object_type'] = 'likes';
					$pointLog['giving_points'] = $this->_fanpageSetting['point_like_normal'];
					$pointLog['note'] = 'likes on user post';
					$pointLogModel->insert($pointLog);
					Zend_Debug::dump($pointLog);
				}
			}
		}
		
	}
}
?>