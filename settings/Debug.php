<?php
if(stristr(@$_SERVER['USER'], 'ajcates') || stristr(@$_SERVER['HTTP_HOST'], 'ajcates') || stristr(@$_SERVER['HTTP_HOST'], 'localhost')) {
  //dev mode:
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
    'debug' => true,
    'warnings' => false,
    'logfile' => LOC . '/sweet-framework/logs/main.log',
    'growl' => array(
      'host' => 'localhost',
      'password' => 'aldo20'
    )
  ));
}
