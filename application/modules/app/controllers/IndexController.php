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
			
			$this->view->user_id = $this->data['user_id'];
			$this->view->fanpage_id = $this->data['page']['id'];
        }

        if(empty($this->view->fanpage_id)) {
        	$this->_redirect('http://www.fancrank.com');
        }
        //set the proper navbar
        $this->_helper->layout()->navbar = $this->view->getHelper('partial')->partial('partials/loggedout.phtml', array('fanpage_id' => $this->data['page']['id']));
        if($this->_auth->hasIdentity()) {
            //bring the user into the app if he is already logged in
			$this->_redirect('/app/app/index/' .$this->data['page']['id']);   
		}
    }

    public function indexAction()
    {
    	$this->_helper->layout->setLayout('default_layout');
    	$model = new Model_Rankings;
    	//$post = new Model_Posts;
    	$follow = new Model_Subscribes();
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
    	$fanpage = array(
    				'topFans'=>array(),
    				'mostPopular'=>array(),
    				'topTalker'=>array(),
    				'topClicker'=>array(),
    				'topFollowed'=>array()
    			);

    	if(!empty($this->data['page']['id'])) {
    		$cache = Zend_Registry::get('memcache');
    		$cache->setLifetime(1800);
    		
    		try {
    			$fanpageId = $this->data['page']['id'];

    			//Check to see if the $fanpageId is cached and look it up if not
    			if(isset($cache) && !$cache->load($fanpageId)){
    				echo 'db look up';
    				//Look up the $fanpageId
    				$fanpage['topFans'] = $model->getTopFans($this->data['page']['id'], 5);
    				//Zend_Debug::dump($topFans);
    				
    				$fanpage['mostPopular'] = $model->getMostPopular($this->data['page']['id'], 5);
    				//Zend_Debug::dump($mostPopular);
    				
    				$fanpage['topTalker'] = $model->getTopTalker($this->data['page']['id'], 5);
    				//Zend_Debug::dump($topTalker);
    				
    				$fanpage['topClicker'] = $model->getTopClicker($this->data['page']['id'], 5);
    				//Zend_Debug::dump($topClicker);
    				 
    				//$topPosts = $model->getTopPosts($this->data['page']['id'], 5);
    				$fanpage['topFollowed'] = $follow->getTopFollowed($this->data['page']['id'], 5);
    				//$latestPost = $post ->getLatestPost($this->data['page']['id'],5);
    				
    				//Save to the cache, so we don't have to look it up next time
    				$cache->save($fanpage, $fanpageId);
    			}else {
    				//echo 'memcache look up';
    				$fanpage = $cache->load($fanpageId);
    			}
    		} catch (Exception $e) {
    			Zend_Registry::get('appLogger')->log($e->getMessage() .' ' .$e->getCode(), Zend_Log::NOTICE, 'memcache info');
    			//echo $e->getMessage();
    		}
    	}
    	//Zend_Debug::dump($color); exit();
    	//$this->view->user_name= $this->getUserName();
    	$this->view->top_fans = $fanpage['topFans'];
    	$this->view->most_popular = $fanpage['mostPopular'];
    	$this->view->top_talker = $fanpage['topTalker'];
    	$this->view->top_clicker = $fanpage['topClicker'];
    	$this->view->top_followed = $fanpage['topFollowed'];
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

