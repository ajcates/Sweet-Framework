<?
SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
	'debug' => true,
	'warnings' => true,
	'logfile' => LOC . '/sweet-framework/logs/main.log',
	'growl' => array(
		'host' => 'localhost',
		'password' => ''
	)
));