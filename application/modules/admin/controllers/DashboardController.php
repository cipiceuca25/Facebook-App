<?php

class Admin_DashboardController extends Fancrank_Admin_Controller_BaseController
{
    public function indexAction()
    {
    
    }

    public function fanpagesAction() 
    {
    	//Zend_Debug::dump($this->_getParam('id'));
    	$uid = $this->_identity->facebook_user_id;
    	$access_token= $this->_identity->facebook_user_access_token;
    	
    	if(empty($uid) || empty($access_token)) {
    		$this->view->pages = array();
    		return;
    	}
        $fanpages_model = new Model_Fanpages;
        $pages = $fanpages_model->getActiveFanpagesByUserId($uid);
        //$pages = $this->getUserPagesList($uid, $access_token);
        $this->view->pages = $pages;
    }

    public function myaccountAction()
    {
    	Zend_Debug::dump($this->_identity);
        $this->view->user_id = $this->_identity->facebook_user_id;
        $this->view->user_email = $this->_identity->facebook_user_email;
        $this->view->user_first_name  = $this->_identity->facebook_user_first_name;
        $this->view->user_last_name = $this->_identity->facebook_user_last_name;
    }

    public function logoutAction() 
    {
        $this->_identity = $this->_auth->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }

    public function previewAction()
    {
        $fanpages_model = new Model_Fanpages;
        $fanpage = $fanpages_model->findByFanpageId($this->_getParam('id'))->current();

        $this->view->installed = $fanpage->installed;
        $this->view->page_id = $this->_getParam('id');
        $colorChoice = new Model_UsersColorChoice;
        $choice = $colorChoice->getColorChoice($this->_getParam('id'))->color_choice;
        if(empty($choice)) {
        	$choice = 3;
        }
        $this->view->fanpageTheme = $choice;

        if ($fanpage->active) {
        	//maybe we should be asking for a relavant time from the api user and pass it as a parameter in the queries
        	$topfans = new Model_Rankings();
        	$this->view->top_fans = $topfans->getTopFans($this->_getParam('id'));
        	$this->view->most_popular = $topfans->getMostPopular($this->_getParam('id'));
        	$this->view->top_talker = $topfans->getTopTalker($this->_getParam('id'));
        	$this->view->top_clicker = $topfans->getTopClicker($this->_getParam('id'));        	
        }else {
        	$this->view->top_fans = array();
        	$this->view->most_popular = array();
        	$this->view->top_talker = array();
        	$this->view->top_clicker = array();
        }

    }

    /*
     * @return array return an array list of user's page
     */
    protected function getUserPagesList($uid, $userAccessToken) {
    	Service_FancrankFBService::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    	
    	if(APPLICATION_ENV !== 'production') {
    		$sources = new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', 'development');
    		$this->config = $sources->get('facebook');
    		$config = array(
    				'appId'  => $this->config->client_id,
    				'secret' => $this->config->client_secret,
    				'cookie' => true,
    		);
    	}

    	$facebook = new Service_FancrankFBService($config);
    	$facebook->setAccessToken($userAccessToken);
    	try {
    		$pages = $facebook->api(array('method' => 'fql.query','query' => 'SELECT page_id  FROM page_admin WHERE uid = '.$uid.''));
    		$arr = array();
    		foreach ($pages as $page) {
    			$arr[] = $page['page_id'];
    		}

    		$ids = implode(',', $arr);
    		if(empty($ids)) {
    			return array();
    		}
    		
			$pages = $facebook->api('/?ids=' .$ids);
    		//Zend_Debug::dump($pages); exit();
    		return $pages;
    	}catch(FacebookApiException $e) {
    		//Zend_Debug::dump($e->getResult());
    		error_log(json_encode($e->getResult()));
    		return array();
    	}
    }
    
    public function analyticAction() {
    	$fanpageId = $this->_getParam('id');
    	
    	$fanpageModel = new Model_Fanpages;
    	 
    	$fans_model = new Model_Fans;
    	
     	$postDataByType = $fanpageModel->getPostsStatByFanpageId($fanpageId);

     	$date = new Zend_Date();
     	$date->subDay(2);
     	
     	$newFans = $fanpageModel->getNewFansNumberSince($fanpageId, $date->toString('yyyy-MM-dd HH:mm:ss'), 5);
     	
    	
     	$topPostByLike = $fanpageModel->getTopPostsByNumberOfLikes($fanpageId, 5);
     	$topPostByComment = $fanpageModel->getTopPostsByNumberOfComments($fanpageId, 5);
     	$topFanList = $fanpageModel->getTopFanList($fanpageId, 50);
     	$fansNumberBySex = $fanpageModel->getFansNumberBySex($fanpageId);
    	
    	$this->view->fans = $fans_model->countAll();
    	$this->view->new_fans = $newFans;
    	$this->view->page_id = $fanpageId;
    	//Zend_Debug::dump($this->_getParam('id')); exit();
    	$this->view->post_data = json_encode($postDataByType);
    	$this->view->fansNumberBySex = json_encode($fansNumberBySex);
    	$this->view->topFanList = $topFanList;
    	$this->view->topPostByLike = $topPostByLike;
    	$this->view->topPostByComment = $topPostByComment;
	}
    
}

