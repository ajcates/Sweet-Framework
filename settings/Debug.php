<?
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