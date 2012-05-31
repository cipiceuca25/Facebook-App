<?php
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
    	echo 'This request will fetch client profile, 50 likes, 50 posts and 50 comments within each post';
    	$url = 'https://graph.facebook.com/';// . '?access_token=' . $token;
    	$postLimit = 50;
    	$commentLimit = 50;
    	$likesLimit = 50;
    	
      	$queries = array(
    			array('method' => 'GET', 'relative_url' => $this->getRequest()->getParam('fanpage_id') .'/'),
     			array('method' => 'GET', 'relative_url' => $this->getRequest()->getParam('fanpage_id') .'/posts?limit='.$postLimit),
    			array('method' => 'GET', 'relative_url' => $this->getRequest()->getParam('fanpage_id') .'/likes?limit='.$likesLimit)
    	);
    	
    	$access_token = $this->getRequest()->getParam('access_token');
    	//$arrget = 'batch=' .json_encode($queries) .'&access_token=' .$access_token .'&method=post';
    	$arrpost = 'batch=' .json_encode($queries) .'&access_token=' .$access_token;
    	//Zend_Debug::dump($arrpost);
    	
    	//echo $url . '?' .$arrpost;
    	//exit();
		$result = $this->requestFacebookAPI_POST($url, $arrpost);
		try {

		} catch (Exception $e) {
			echo $e->getMessage();
			return;
		}
  
		
		if ($result === FALSE)
		{
			// Redirect to error page
		}
		else
		{
				$response = json_decode($result,true);
				//Zend_Debug::dump($response);
				$data = $this->responseParser($response, array('me', 'posts', 'likes'));
				Zend_Debug::dump($data);
				
				/*
				foreach ($data as $k=>$v){
					echo '<p>' .$k .' data ------------------------------------------</p>';
					Zend_Debug::dump(json_decode($v, true));
					echo '</br>';
				}*/
					
				//Zend_Debug::dump(json_decode($data['posts'], true));
				
				//echo likes
				//echo facebook user general profile
				echo '<p>Who is ' .$this->getRequest()->getParam('fanpage_id') .': </p>';
				Zend_Debug::dump(json_decode($data['me'], true));
				
				
				echo '<p>' .$this->getRequest()->getParam('fanpage_id') .' likes following: </p>';
				Zend_Debug::dump(json_decode($data['likes'], true));
				
				$posts = json_decode($data['posts'], true);
				//Zend_Debug::dump($posts);
				//exit();
				$commentArray = array();
				foreach($posts['data'] as $post) {
					$commentArray[] = array('method' => 'GET', 'relative_url' => $post['id'] .'?limit='.$commentLimit);	
				}
				
				//Zend_Debug::dump(json_encode($commentArray));
				$commentQueries = json_encode($commentArray);
				
				$arrpost = 'batch=' .$commentQueries .'&access_token=' .$access_token;
				//Zend_Debug::dump($arrpost);
				//exit();
				
				$result = $this->requestFacebookAPI_POST($url, $arrpost);
				
				if ($result === FALSE)
				{
					// Redirect to error page
				}else {
					//Zend_Debug::dump(json_decode($result, true));
					$posts = json_decode($result, true);
					foreach ($posts as $data) {
						$post = json_decode($data['body'], true);
						echo '<p>' .$post['id'] .' post: </p>';
						
						Zend_Debug::dump($post);
					}
					$timeTaken = time() - $_SERVER['REQUEST_TIME'];
					
					echo "Total execution time: " .$timeTaken;
				}
				
		}
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
    
    private function feedParser($feed) {
    	
    }
    
    private function responseParser($response, $keyName = array()) {
    	if(count($response) != count($keyName)) {
    		return;
    	}
    	
    	$arr = array();
    	foreach ($response as $key => $res){
			if($res['code'] === 200) {
				$arr[$keyName[$key]] = $res['body'];
			}
    	}
    	
    	return $arr;
    }
    
    //$data is array of json objects
    private function idCollector($data) {
    	$idArray = array();
    	foreach($data as $k => $v){
    		$result = $this->searchByKey('id', json_decode($v, true));
    		$idArray = array_merge($idArray, $result);
    	}
    	
    	return $idArray;
    }

    function search_key_r($array, $key, &$results)
    {
    	if (!is_array($array))
    		return;
 
    	if (array_key_exists($key, $array))
    		$results[] = $array[$key];
    
    	foreach ($array as $subarray)
    		$this->search_key_r($subarray, $key, $results);
    }
    
    function searchByKey($key, $array)
    {
    	$results = array();
    
    	$this->search_key_r($array, $key, $results);
    
    	return $results;
    }
    
    
}
