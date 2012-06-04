<?php
/*
 * A dabase service which provides customized CRUD methods for encapsulating complex database operation
 */
class Service_FancrankDBService extends Fancrank_Db_Table {
	
	/*
	 * save fanpage profile to database
	 * 
	 * @param $fanpageProfile a fanpage json object
	 * @param $access_token contains the security information for identifies user access resource permission
	 * @return true if success, else return false
	 */
	public function saveFanpageProfile($data, $access_token=null) {

		$db = $this->getAdapter();
		$db->beginTransaction();
		$collectorLogger = Zend_Registry::get('collectorLogger');

		if(empty ($data['me'])) {
			return false;
		}
		$facebookUserProfile = json_decode($data['me']);
		//Zend_Debug::dump($facebookUserProfile); exit();
		$facebookUserData = array(
				'facebook_user_id' 		=> $facebookUserProfile->id,
				'facebook_user_name' 	=> !empty($facebookUserProfile->username) ? $facebookUserProfile->username : '',
				'facebook_user_email' 	=> !empty($facebookUserProfile->email) ? $facebookUserProfile->email : '',
				'facebook_user_gender' 	=> !empty($facebookUserProfile->gender) ? $facebookUserProfile->gender : '',
				'access_token' 			=> $access_token,
				'updated_time' 			=> !empty($facebookUserProfile->updated_time) ? 
											$this->dateToStringForMysql($facebookUserProfile->update_date) : null,
				'locale' 				=> !empty($facebookUserProfile->locale) ? $facebookUserProfile->locale : '',
				'hometown' 				=> !empty($facebookUserProfile->hometown) ? $facebookUserProfile->hometown : '',
				'current_location' 		=> !empty($facebookUserProfile->current_location) ? $facebookUserProfile->current_location : '',
				'bio' 					=> !empty($facebookUserProfile->bio) ? $facebookUserProfile->bio : ''
		);
		
		try {
			//save facebook user's relative information into facebook_users table
			$facebookUser = new Model_FacebookUsers();
			$facebookUser->saveAndUpdate($facebookUserData);

			echo('user saved\n');
		} catch (Exception $e) {
			print $e->getMessage();
			$collectorLogger->log(sprintf('Unable to save facebook user %s to database. Error Message: %s ', $fanpageProfile->id, $e->getMessage()), Zend_log::ERR);
			$db->rollBack();
		}
		
		//Zend_Debug::dump($data); exit();
		if(empty ($data['fanpage'])) {
			return false;
		}

		$fanpageProfile = json_decode($data['fanpage']);
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
			
			
			echo('fanpage saved\n');
			//$db->commit();
		} catch (Exception $e) {
			print $e->getMessage();
			$collectorLogger->log(sprintf('Unable to save fanpage %s to database. Error Message: %s ', $fanpageProfile->id, $e->getMessage()), Zend_log::ERR);
			$db->rollBack();
		}
		
		//save album
		$albums = json_decode($data['albums']);
		//Zend_Debug::dump($albums->data); exit();
		$rows = array();
		if( !empty($albums->data) ) {
			$albumModel = new Model_Albums();
			foreach($albums->data as $k => $album) {
				if( empty($album->id) ) continue;
				$created = new Zend_Date(!empty($album->created_time) ? $album->created_time : null);
				$updated = new Zend_Date(!empty($album->updated_time) ? $album->updated_time : null);
				
				$row = array(
						'album_id'				=> $album->id,
						'fanpage_id'			=> $fanpageProfile->id,
						'facebook_user_id'		=> $facebookUserProfile->id,
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
				
				$rows[] = $row;
				try {
					//save fanpage's album's relative information into facebook_users table
					$albumModel->saveAndUpdateById($row, array('id_field_name'=>'album_id'));
						
					echo sprintf('album %s saved', $album->id);
				} catch (Exception $e) {
					print $e->getMessage();
					$collectorLogger->log(sprintf('Unable to save fanpage %s to database. Error Message: %s ', $fanpageProfile->id, $e->getMessage()), Zend_log::ERR);
					$db->rollBack();
				}
			}
			// commit albums save
			$db->commit();
		}
		
		Zend_Debug::dump($rows); exit();
	}
	
	/*
	 * save fanpage profile to database
	 * 
	 * @return true if success
	 */
	public function savePosts() {
		echo 'posts have been saved';
	}
	
	/*
	 * @return a string representation of a giving date in following format: yyyy-MM-dd HH:mm:ss 
	 */
	private function dateToStringForMysql($date) {
		$date = new Zend_Date($date);
		return $date->toString('yyyy-MM-dd HH:mm:ss');
	}
	
}

?>