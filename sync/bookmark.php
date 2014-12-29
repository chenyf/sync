<?php

define("BAIKAL_CONTEXT", TRUE);
define("PROJECT_CONTEXT_BASEURI", "/");

if(file_exists(getcwd() . "/Core")) {
	# Flat FTP mode
	define("PROJECT_PATH_ROOT", getcwd() . "/");	#./
} else {
	# Dedicated server mode
	define("PROJECT_PATH_ROOT", dirname(getcwd()) . "/");	#../
}

if(!file_exists(PROJECT_PATH_ROOT . 'vendor/')) {
	die('<h1>Incomplete installation</h1><p>Ba&iuml;kal dependencies have not been installed. Please, execute "<strong>composer install</strong>" in the folder where you installed Ba&iuml;kal.');
}

require PROJECT_PATH_ROOT . 'vendor/autoload.php';

$GLOBALS['LOG'] = array(
		'intLevel'   => 7,	//notice, warning, fatal
		'strLogFile' => '/letv/logs/sync/bookmark.log',
);

# Bootstraping Flake
\Flake\Framework::bootstrap();

# Bootstrapping Baïkal
\Baikal\Framework::bootstrap();

$backend = new \Sabre\SimpleDAV\Bookmark\PDO($GLOBALS["DB"]->getPDO());
$rootNode = new \Sabre\SimpleDAV\Bookmark\Bookmark($backend);  
$server = new \Sabre\DAV\Server($rootNode);
$server->setBaseUri('/sync/bookmark/');
$server->addPlugin(new \Sabre\SimpleDAV\Bookmark\Plugin());
$server->exec();

?>
