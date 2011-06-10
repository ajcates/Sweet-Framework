<?php
switch(trim(`hostname`)) {
    case 'ajcates-macbook.local':
        SweetFramework::getClass('lib', 'Config')->setAll('Debug', array(
            'debug' => true,
            'warnings' => true,
            'logfile' => LOC . '/sweet-framework/logs/main.log',
            'growl' => array(
                'host' => 'localhost',
                'password' => 'aldo20'
            )
        ));
		break;
	default:
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
}
