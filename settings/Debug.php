<?
<<<<<<< HEAD
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
=======


if(stristr($_SERVER['HTTP_HOST'], 'local') !== false || stristr($_SERVER['HTTP_HOST'], 'dev') !== false) {
	SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
		'debug' => true,
		'warnings' => true,
		'logfile' => LOC . '/sweet-framework/logs/main.log',
		'growl' => array(
			'host' => 'localhost',
			'password' => 'aldo20'
		)
	));
} else {
	SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
		'debug' => false,
		'warnings' => false,
		'logfile' => LOC . '/sweet-framework/logs/main.log',
		'growl' => array(
			'host' => 'localhost',
			'password' => 'aldo20'
		)
	));
}
>>>>>>> a25dd950c021cb240f26ec7db4b84ae237c973d5
