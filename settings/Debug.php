<?
//Development:
SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
	'debug' => true,
	'warnings' => true,
	'logfile' => LOC . '/sweet-framework/logs/main.log',
	'growl' => array(
		'host' => 'localhost',
		'password' => 'aldo20'
	)
));
//Production:
/*
SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
	'debug' => false,
	'warnings' => false,
	'logfile' => LOC . '/sweet-framework/logs/main.log',
	'growl' => array(
		'host' => 'localhost',
		'password' => 'aldo20'
	)
));
break;
*/