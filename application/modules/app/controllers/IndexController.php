<?php

class App_IndexController extends Fancrank_App_Controller_BaseController
{
    public function preDispatch()
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_auth->setStorage(new Zend_Auth_Storage_Session('Fancrank_App'));
        //$this->data = $this->getSignedRequest($this->_getParam('signed_request'));
        //Zend_Debug::dump($_REQUEST['signed_request']);
        if (APPLICATION_ENV == 'stage') {
            $this->data['page']['id'] = $this->_request->getParam('id');
            $this->view->fanpage_id = $this->_request->getParam('id');
            //$this->data['user_id'] = '48903527'; //set test data for signed param (this one is adgezaza)
            $this->data['user_id'] = $this->_getParam('user_id'); //set test user id from url
            $this->view->user_id = $this->data['user_id'];
            $this->view->access_token = $this->_getParam('access_token');
            //Zend_debug::dump($this->data['user_id']);
        }else {
            if (isset($_REQUEST['signed_request'])) {
                $fb = new Service_FancrankFBService();
                $this->data['page']['id']= $fb->getFanPageId();
                $this->data['user_id']=$fb->getFanPageUserId();
                $this->view->user_id = $this->data['user_id'];
                // Zend_Debug::dump($fb->getSignedData());
                //Zend_Debug::dump($this->data['page']['id']);
            } else {
                $this->data['page']['id'] = $this->_getParam('id');
                $this->data['user_id'] = $this->_getParam('user_id');
            }
            $fanpageModel = new Model_Fanpages();
			$this->view->user_id = $this->data['user_id'];
			$this->view->fanpage_id = $this->data['page']['id'];
			$this->view->fanpage = $fanpageModel->findRow($this->data['page']['id']);
        }

        if(empty($this->view->fanpage_id)) {
        	$this->_redirect('http://www.fancrank.com');
        }
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
        	$this->_facebook_user = $this->_auth->getIdentity();
        	if(!empty($this->data['user_id']) && $this->_facebook_user->facebook_user_id !== $this->data['user_id']) {
        		$this->_forward('logout', 'app', 'app');
        	}else {
        		$this->_redirect('/app/app/index/' .$this->data['page']['id']);
        	}
		}
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout');
    	$fanpageModel = new Model_Fanpages();
    	
    	$model = new Model_Rankings;
    	$leaderboardLogModel = new Model_LeaderboardLog();
    	
    	//$post = new Model_Posts;
    	//$colorChoice = new Model_UsersColorChoice;
    	$user = new Model_FacebookUsers();
    	$user = $user->find($this->data['user_id'])->current();
    	if($user) {
    		$this->view->facebook_user = $user;
    		//$facebook = new Service_FancrankFBService();
    		//echo $facebook->getAppAccessToken();
    		if($user->installed) {
    			$this->view->newuser = false;
    		}else {
    			$this->view->newuser = true;
    		}
    		//$access_token = $this->facebook_user->facebook_user_access_token;
    		//$this->view->feed = $this->getFeed($access_token);
    	}else {
    		$this->view->facebook_user = null;
    		if(!empty($this->data['user_id'])) {
    			$this->view->newuser = true;
    		}
    	}
    	
    	//get top fans list from memcache
   		if(!empty($this->data['page']['id'])) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
     		$cache->remove($this->data['page']['id'] . '_topfan');
     		$cache->remove($this->data['page']['id'] . '_topfanall');
     		$cache->remove($this->data['page']['id'] . '_topfanlastweek');
//     		$cache->remove($this->_fanpageId . '_topclicker');
//     		$cache->remove($this->_fanpageId . '_topfollowed');
//     		$cache->remove($this->_fanpageId . '_toptalker');
//     		$cache->remove($this->_fanpageId . '_fanfavorite');
//     		try {
//     			//Check to see if the $fanpageId is cached and look it up if not
//     			if(isset($cache) && !$cache->load($this->_fanpageId . '_topfollowed')){
//     				$toplist = $model->getTopFollowedByWeek($this->_fanpageId, 5);
//     				$cache->save($toplist, $this->_fanpageId . '_topfollowed');
//     			}else{
//     				$toplist = $cache->load($this->_fanpageId . '_topfollowed');
//     			}
    				
//     		} catch (Exception $e) {
//   				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
//   				//echo $e->getMessage();
//   			} 
  			
//   			try{
    			
//     			if(isset($cache) && !$cache->load($this->_fanpageId . '_topclicker')){
//     				$toplist = $model->getTopClickerByWeek($this->_fanpageId, 5);
    				
//     				$cache->save($toplist, $this->_fanpageId . '_topclicker');
//     			}else{
//     				$toplist = $cache->load($this->_fanpageId . '_topclicker');
//     			}
    				
//     		} catch (Exception $e) {
//     			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
//     						//echo $e->getMessage();
//     		}
    		
//     		try{
//     			if(isset($cache) && !$cache->load($this->_fanpageId . '_fanfavorite')){
//     				$toplist = $model->getMostPopularByWeek($this->_fanpageId, 5);
    				
//     				$cache->save($toplist, $this->_fanpageId . '_fanfavorite');
//     			}else{
//     				$toplist = $cache->load($this->_fanpageId . '_fanfavorite');
//     			}
//     		} catch (Exception $e) {
//     				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
//     				//echo $e->getMessage();
//     		}
	    			
	    	try{
    			if(isset($cache) && !$cache->load($this->data['page']['id'] . '_topfanlastweek')){
    				$topFansLastWeek =  $leaderboardLogModel->getLastWeekTopFans($this->data['page']['id']);
    				
    				$cache->save($topFansLastWeek, $this->data['page']['id'] . '_topfanlastweek');
    			}else{
    				$topFansLastWeek = $cache->load($this->data['page']['id'] . '_topfanlastweek');
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    		
    		try{		
    			if(isset($cache) && !$cache->load($this->data['page']['id'] . '_topfan')){
    				$topfan = $model->getTopFansByWeek($this->data['page']['id'], 5);
    				$cache->save($topfan, $this->data['page']['id'] . '_topfan');
    			}else{
   					$topfan = $cache->load($this->data['page']['id'] . '_topfan');
    			}
    			
    		} catch (Exception $e) {
  				Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
  				//echo $e->getMessage();
  			}    	
    	}
    	    	
    	
    
    	
    	
    	/*
    	if(isset($fanpage->fanpage_level) && $fanpage->fanpage_level != 3) {
    		for ($i=0; $i<count($fanpage2['topFans']); $i++){
    			$fanpage2['topFans'][$i]['number_of_posts'] = '?';
    		}	
    		for ($i=0; $i<count($fanpage2['mostPopular']); $i++){
    			$fanpage2['mostPopular'][$i]['count'] = '?';
    		}	
    		for ($i=0; $i<count($fanpage2['topTalker']); $i++){
    			$fanpage2['topTalker'][$i]['number_of_posts'] = '?';
    		}
    		for ($i=0; $i<count($fanpage2['topClicker']); $i++){
    			$fanpage2['topClicker'][$i]['number_of_likes'] = '?';
    		
    		}
    		for ($i=0; $i<count($fanpage2['topFollowed']); $i++){
    			$fanpage2['topFollowed'][$i]['count'] = '?';
    		}
    		for ($i=0; $i<count($fanpage2['topFansAllTime']); $i++){
    			$fanpage2['topFansAllTime'][$i]['count'] = '?';
    		}
    	}
    	*/
    	//Zend_Debug::dump($fanpage2['topFansLastWeek']);
    	
    	$this->view->top_fans_last_week = $topFansLastWeek;
    	$this->view->top_fans = $topfan;
//     	$this->view->most_popular = $fanpage2['mostPopular'];
//     	$this->view->top_talker = $fanpage2['topTalker'];
//     	$this->view->top_clicker = $fanpage2['topClicker'];
//     	$this->view->top_followed = $fanpage2['topFollowed'];
//     	$this->view->top_fans_all_time = $fanpage2['topFansAllTime'];
    	$this->render('index');
    }
    
    protected function getUserName(){
    	$client = new Zend_Http_Client;
    	$client->setUri("https://graph.facebook.com/". $this->data['user_id']);
    	$client->setMethod(Zend_Http_Client::GET);
 		
    	
    	$response = $client->request();
    	
    	$result = Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    	
    	if(!empty ($result)) {
    		
    		return $result->first_name;
    	}
    	
    	
    }
    
}

