#!/usr/bin/php -q
<?php
// Definitions
define('PS', PATH_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

// Define path to application public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)) . DS . 'public');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define path to data directory
defined('DATA_PATH')
    || define('DATA_PATH', APPLICATION_PATH . DS . 'data');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PS, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
		APPLICATION_ENV,
		APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();
$start = time();
$fanpageModel = new Model_Fanpages();

//Zend_Debug::dump($fanpageIdList); exit();

$arg = getopt('p:a::');

if (empty( $arg['p'])) {
	echo 'empty fanpage id';
	exit();	
}

$fanpageId = $arg['p'];
$accessToken = '';

if (isset($arg['a'])) {
	$accessToken = $arg['a'];
} else {
	$accessToken = $fanpageModel->getFanpageAccessToken($fanpageId);
}

if ($accessToken) {
	$collector = new Service_FancrankCollectorService(null, $fanpageId, $accessToken, 'update');
	//$collector->updateFanpageFeed('365+days+ago', 'now');
	$result = $collector->getFanpageFeed('10+days+ago', 'now');
	Zend_Debug::dump($result);	
	echo 'hey';
} else {
	echo 'access token not found or page not in the database';
}

$stop = time() - $start;
echo 'total execution time: ' .$stop;
echo 'job done'.PHP_EOL;
exit();

function isCli() {
	return php_sapi_name() == 'cli';
}

