<?php
/*
 * A dabase service which provides customized CRUD methods for encapsulating complex database operation
 */
class Service_FancrankDB1Service extends Fancrank_Db_Table {
	
	
	/*
	 * save fanpage profile to database
	 * 
	 * @param $fanpageProfile a fanpage json object
	 * @param $access_token contains the security information for identifies user access resource permission
	 * @return true if success, else return false
	 */
	public function saveFanpageProfile($url, $data, $access_token=null) {
		
		$db = $this->getDefaultAdapter();
		$db->beginTransaction();
		$collectorLogger = Zend_Registry::get('collectorLogger');
		
		//save facebook user
		//$facebookUserId = $this->saveFacebookUser(json_decode($data['me']), $access_token, $db);

		//save fanpage
		$fanpageId = $this->saveFanpage(json_decode($data['me']), $access_token, $db);
		
		//save album
		$albums = json_decode($data['albums']);
		//Zend_Debug::dump($albums->data);
		$albumRows = $this->saveAlbums($albums, $db, $fanpageId);
		//Zend_Debug::dump($albumRows); exit();

		//save photos from all albums
		$photosRows = $this->savePhotos($url, $albumRows, $access_token, $db, $fanpageId);

		//save feed post
		$feed = json_decode($data['feed']);
		$feedRow = $this->savePosts($feed, $db, $fanpageId);
		//Zend_Debug::dump($feedRow['fans_id_list']); exit();
		//Zend_Debug::dump($feed->paging->next); exit();
		if(!empty($feed->paging->next)) {
			Collector::queue('1 min', $url, 'feed', $fanpageId);
		}
		//save comments
		
		//commit save
		$db->commit();
		
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
	 * @param $facebookUserProfile facebook user object
	 * @param $access_token an access token
	 * @param $db current default database
	 * @return facebook user id
	 */
	public function saveFacebookUser($data, $access_token=null, $db) {
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
		
		$facebookUserData = array(
				'facebook_user_id' 			=> $data->id,
				'facebook_user_name' 		=> !empty($data->username) ? $data->username : '',
				'facebook_user_first_name' 	=> !empty($data->user_first_name) ? $data->user_first_name : '',
				'facebook_user_last_name' 	=> !empty($data->last_name) ? $data->last_name : '',
				'facebook_user_email' 		=> !empty($data->email) ? $data->email : '',
				'facebook_user_gender' 		=> !empty($data->gender) ? $data->gender : '',
				'facebook_user_avatar'    	=> sprintf('https://graph.facebook.com/%s/picture', $data->id),
				'facebook_user_lang'        => implode(',', $lang),
				'facebook_user_birthday'    => Fancrank_Util_Util::dateToStringForMysql(!empty($data->birthday)),
				'facebook_user_access_token'=> $access_token,
				'updated_time' 				=> Fancrank_Util_Util::dateToStringForMysql(!empty($data->updated_time)),
				'facebook_user_locale' 		=> !empty($data->facebook_user_locale) ? $data->locale : '',
				'hometown' 					=> !empty($data->hometown) ? $data->hometown : '',
				'current_location' 			=> !empty($data->current_location) ? $data->current_location : '',
				'bio' 						=> !empty($data->bio) ? $data->bio : ''
		);
		
		try {
			//save facebook user's relative information into facebook_users table
			$facebookUser = new Model_FacebookUsers();
			$facebookUser->saveAndUpdate($facebookUserData);
		
			//echo('user saved\n');
			return $data->id;
		} catch (Exception $e) {
			print $e->getMessage();
			$collectorLogger = Zend_Registry::get('collectorLogger');
			$collectorLogger->log(sprintf('Unable to save facebook user %s to database. Error Message: %s ', $facebookUser->id, $e->getMessage()), Zend_log::ERR);
			$db->rollBack();
		}
	}

	/*
	 * @param $fanpageProfile fanpage object
	 * @param $access_token an access token
	 * @param $db current default database
	 * @return fanpage id
	 */
	public function saveFanpage($fanpageProfile, $access_token=null, $db) {
		if(empty ($fanpageProfile)) {
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
				'fanpage_is_published'  => !empty($fanpageProfile->is_published) ? $fanpageProfile->is_published: false,
				'fanpage_genre'   		=> !empty($fanpageProfile->genre) ? $fanpageProfile->genre : '',
				'fanpage_can_post'      => !empty($fanpageProfile->can_post) ? $fanpageProfile->can_post : null,
				'has_added_app'			=> !empty($fanpageProfile->has_added_app) ? $fanpageProfile->has_added_app : null,
				'access_token' 			=> $access_token,
		);
		
		//Zend_Debug::dump($fanpageData); exit();
		try {
			//save fanpage fanpage's relative information into facebook_users table
			$fanpages = new Model_Fanpages();
			$fanpages->saveAndUpdateById($fanpageData, array('id_field_name'=>'fanpage_id'));
				
			$db->commit();
			return $fanpageProfile->id;
		} catch (Exception $e) {
			print $e->getMessage();
			$collectorLogger = Zend_Registry::get('collectorLogger');
			$collectorLogger->log(sprintf('Unable to save fanpage %s to database. Error Message: %s ', $fanpageProfile->id, $e->getMessage()), Zend_log::ERR);
			$db->rollBack();
		}
	}
	
	/*
	 * @return return an array of album id that were saved to database successfully
	 */
	public function saveAlbums($albums, $fanpageId, $db, $facebookUserId = null) {
		$rows = array();
		$albumModel = new Model_Albums();
		foreach($albums as $k => $album) {
			if( empty($album->id) ) continue;
			$created = new Zend_Date(!empty($album->created_time) ? $album->created_time : null);
			$updated = new Zend_Date(!empty($album->updated_time) ? $album->updated_time : null);
	
			$row = array(
					'album_id'				=> $album->id,
					'fanpage_id'			=> $fanpageId,
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
				$collectorLogger->log(sprintf('Unable to save album %s from fanpage %s to database. Error Message: %s ', $album->id, $fanpageId, $e->getMessage()), Zend_log::ERR);
				$db->rollBack();
			}
		}
		//return array('post_id_list'=>$rows, 'fans_id_list'=> '');
		return $rows;
	}
	
	public function savePhotos($photos, $fanpageId, $db) {
		$rows = array();
		$photoModel = new Model_Photos();
		foreach($photos as $photo) {
			if( empty($photo->id) ) continue;
			try {
				$created = new Zend_Date(!empty($photo->created_time) ? $photo->created_time : null);
				$updated = new Zend_Date(!empty($photo->updated_time) ? $photo->updated_time : null);
				$row = array(
						'photo_id'          => $photo->id,
						'fanpage_id'        => $fanpageId,
						'facebook_user_id'	=> $photo->from->id,
						'photo_album_id'    => $photo->id,
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
				$collectorLogger->log(sprintf('Unable to save photo %s from fanpage %s to database. Error Message: %s ', $photo->id, $fanpageId, $e->getMessage()), Zend_log::ERR);
				return;
			}
		}
		
		return $rows;
		//Zend_Debug::dump($rows); exit();
	}

	/*
	 * @return return an array of post's id that were saved to the database
	*/
	public function savePosts($posts, $fanpageId, $db) {
		$rows = array();
		$postModel = new Model_Posts();
		foreach($posts as $k => $post) {
			if( empty($post->id) ) continue;
			$created = new Zend_Date(!empty($post->created_time) ? $post->created_time : null);
			$updated = new Zend_Date(!empty($post->updated_time) ? $post->updated_time : null);

			$row = array(
					'post_id'               => $post->id,
					'facebook_user_id'      => $post->from->id,
					'fanpage_id'            => $fanpageId,
					'post_message'          => isset($post->message) ? $post->message : '',
					'post_type'             => !empty($post->type) ? $post->type : '',
					'created_time'          => $created->toString('yyyy-MM-dd HH:mm:ss'),
					'updated_time'          => $updated->toString('yyyy-MM-dd HH:mm:ss'),
					'post_comments_count'   => !empty($post->comments->count) ? $post->comments->count : null,
					'post_likes_count'      => isset($post->likes) && isset($post->likes->count) ? $post->likes->count : 0
			);

			if (property_exists($post, 'application') && isset($post->application_id)) {
				$row['post_application_id'] = $post->application->application_id;
				$row['post_application_name'] = $post->application->application_name;
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
				$collectorLogger->log(sprintf('Unable to save post %s from fanpage %s to database. Error Message: %s ', $post->id, $fanpageId, $e->getMessage()), Zend_log::ERR);
				return;
			}
		}
		//return array('post_id_list'=>$rows, 'fans_id_list'=> $fansId);
		return $rows;
	}
	
	public function saveComments($commentsList, $fanpageId, $db) {
		$commentModel = new Model_Comments ();
		foreach ( $commentsList as $k => $comment ) {
			if (empty ( $comment->id ) || empty ( $comment->created_time ))	continue;

			$created = new Zend_Date ( ! empty ( $comment->created_time ) ? $comment->created_time : null );
			
			$parentid = $comment->id;
			if(strrpos($comment->id, '_') > 0) {
				$parentid = substr($comment->id, 0, strrpos($comment->id, '_'));
			}
			
			$row = array (
					'comment_id' => $comment->id,
					'fanpage_id' => $fanpageId,
					'comment_post_id' => $parentid,
					'facebook_user_id' => $comment->from->id,
					'comment_message' => $comment->message,
					'created_time' => $created->toString ( 'yyyy-MM-dd HH:mm:ss' ),
					'comment_likes_count' => isset ( $comment->likes ) ? $comment->likes : 0 
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
				$collectorLogger->log ( sprintf ( 'Unable to save comment %s fanpage %s to database. Error Message: %s ', $comment->id, $fanpageId, $e->getMessage () ), Zend_log::ERR );
				return;
			}
		}

		//return array('comment_id_list'=>$rows, 'fans_id_list'=> $fansId);
		return $rows;
	}

	public function saveLikes($likesList, $fanpage_id, $db) {
		$likes_model = new Model_Likes;
		$cols = array('fanpage_id', 'post_id', 'facebook_user_id', 'post_type');
		$update = array('post_type');
		try {
			$likes_model->insertMultiple($likesList, $cols, $update);
		} catch (Exception $e) {
			$collectorLogger = Zend_Registry::get ( 'collectorLogger' );
			$collectorLogger->log ( sprintf ( 'Unable to save likes %s',  $e->getMessage ()), Zend_log::ERR);
		}
		
	}
	
	public function saveFans($fanIds) {
		
	}
	
}

?>