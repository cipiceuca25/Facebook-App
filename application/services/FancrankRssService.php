<?php
/*
 * A Rss service
 */
class Service_FancrankRssService {
	protected $_pageUrl = 'http://www.facebook.com/feeds/page.php?format=atom10&id=';
	protected $_fanpageId = null;
	protected $_rssFeed = array();
	
	public function __constructor($fanpageId = null, $url = null) {
		if ($fanpageId) {
			$this->_fanpageId = $fanpageId;
		}
		if ($url) {
			$this->_pageUrl = $url;
		}
	}
	
	public function readPageRssFeed($fanpageId) {
		if (!$fanpageId) {
			return array();
		}

		$feed = new Zend_Feed_Atom($this->_pageUrl .$fanpageId);

		if ($feed) {
			$this->_rssFeed = $feed;
			return $feed;
		} else {
			return array();
		}		
	}
	
	public function writePageRssFeed($fanpageId) {
		
	}
	
	public function formatRssFeed($format) {
		$formatFeed = array();
		switch ($format) {
			case 'array':
				$formatFeed = $this->rssToArray();
				break;
		}
		return $formatFeed;
	}
	
	private function rssToArray() {
		$arrayFeed = array();
		foreach ($this->_rssFeed as $entry) {
			$data = array (
					'title' => $entry->title,
					'description' => $entry->summary,
					'dateModified' => $entry->updated,
					'authors' => $entry->author,
					'link' => $entry->link,
					'content' => $entry->content
			);
		
			$arrayFeed [] = $data;
		}
		Zend_Debug::dump($arrayFeed);
		return $arrayFeed;
	}
	
	public function getRssFeed() {
		return $this->_rssFeed;
	}
}
?>