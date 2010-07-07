<?php
SweetFramework::getClass('lib', 'Config')->setAll('SweetFramework', array(
	'benchMark' => false,
	'niceUrls' => false,
	'autoload' => array('Session', 'SweetModel'),
	'app' => array(
		'folder' => 'app',
		'paths' => array(
			'config' => 'settings',
			'lib' => 'libs',
			'model' => 'models',
			'helper' => 'helpers',
			'controller' => 'controllers'
		)
	)
));