<?
SweetFramework::getClass('lib', 'Config')->setAll('Session', array(
	'timeout' => 31536000,
	'hashFunction' => 'sha512',
	'cookieSecret' => 'h07rsouY43hSNpNAVvcEKDrasdfasdsdfasedefeeeasx5RjTfrthw49BC6xeGNvw2nI55z1RH',
	'cookieName' => 'sweet-hash',
	'use' => array($_SERVER['REMOTE_ADDR']),
	'sslCookies' => false,
	'tableName' => 'sessions',
	'dataTableName' => 'sessionData'
));
