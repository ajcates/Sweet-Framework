<?
SweetFramework::getClass('lib', 'Config')->setAll('Session', array(
	'timeout' => 31536000,
	'hashFunction' => 'sha512',
	'cookieSecret' => 'h07rsouY43hSNpNAVvcEKDrXkzs4rasdfasdfkOL9iUe3Vx5RjTfrthw49BC6xeGNvw2nI55z1RH',
	'cookieName' => 'crad-token',
	'use' => array($_SERVER['REMOTE_ADDR']),
	'sslCookies' => false,
	'tableName' => 'Sessions',
	'dataTableName' => 'SessionData'
));
