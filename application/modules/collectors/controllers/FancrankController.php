<?php
require_once APPLICATION_PATH .'/../library/Facebook/facebook.php';

class Collectors_FancrankController extends Fancrank_Collectors_Controller_BaseController
{
    public function init()
    {
        parent::init();
    }
	
    public function indexAction()
    {
    	
    	$postModel = new Model_Posts();
    	$postUniqueList = $postModel->getUniqueInteractionList('216821905014540_10150514453682161');
    	Zend_Debug::dump($postUniqueList);
    }
    
    public function insightAction() {
    	$analytic = new Fancrank_Analytics_FancrankAnalytics();
    	Zend_Debug::dump($analytic->getTopFanList('123'));
    }
    
    public function fanpageAction() {
    	$fanpageId =  '165668590150326';
    	
    	$fanpageModel = new Model_Fanpages;
    	 
    	$fans_model = new Model_Fans;
    	
     	$postDataByType = $fanpageModel->getPostsStatByFanpageId($fanpageId);
     	Zend_Debug::dump($postDataByType);
     	
     	$topPostByLike = $fanpageModel->getTopPostsByNumberOfLikes($fanpageId, 5);
		Zend_Debug::dump($topPostByLike);
		
		$topFanList = $fanpageModel->getTopFanList($fanpageId, 5);
		Zend_Debug::dump($topFanList);
		
		$fansNumberBySex = $fanpageModel->getFansNumberBySex($fanpageId);
		Zend_Debug::dump($fansNumberBySex);
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
}
