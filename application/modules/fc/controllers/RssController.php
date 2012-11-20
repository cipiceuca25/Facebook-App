<?php
class Fc_RssController extends Fancrank_Fc_Controller_BaseController
{

	public function init() {
		$this->_helper->contextSwitch()
			//->addActionContext('fanpage', 'json')
			->addActionContext('fancrank', array('xml', 'json'))
			->initContext();			
	}
	
    public function preDispatch() {
    	$this->view->layout()->disableLayout();
    }

    public function indexAction() {
		$this->render('index');
    }
    
    public function fanpageAction() {
    	//Zend_Debug::dump($this->_getParam('id'));
    	$this->_helper->viewRenderer->setNoRender(true);
    	try {
    		$fanpageId = $this->_getParam('id');
    		if (empty($fanpageId)) {
    			return array();
    		}
    		$rss = new Service_FancrankRssService();
    		$rss->readPageRssFeed($fanpageId);
    		$result = $rss->formatRssFeed('array');
			//Zend_Debug::dump($result);
    		$this->_helper->json($result);
    	} catch (Exception $e) {
    		//echo $e->getMessage();
    		$this->_helper->json(array());
    	}
    	//return;
    }
}

?>