<?php
//Todo
/*
@todo
take out the $GLOBABLS and use protected static vars instead

come up with classier names then getSetting and setSetting
	get n set ?
*/
class Config {

	//public static $configArray;
	
	static protected $configs = array();
	
/*
	public function __construct() {
		//SweetEvent::bind('SweetFrameworkEnd', array($this, 'clearConfigs'));
		self::$configs = array();
	}
*/
	
/*
	public function clearConfigs() {
		self::$configs = array();
	}
*/
	
/*
	private $paths = array('/sweet-framework/settings/');
	
	public function addPath($path) {
		array_push(self::$paths, $path);
	}
*/
	
	public function get($className, $param=null) {
		if(!isset(self::$configs[$className])) {
			Sweetframework::loadFileType('config', $className, true);
		}
		if(isset($param)) {
			return isset(self::$configs[$className][$param]) ? self::$configs[$className][$param] : null;
		} else {
			return self::$configs[$className];
		}
	}
	
	public function setAll($nameSpace, $values) {
		self::$configs[$nameSpace] = $values;
	}
	
	public function set($nameSpace, $item, $value) {
		self::$configs[$nameSpace][$item] = $value;
	}
}
/*/------------------------------------------
	old gross code
//-------------------------------------------
*

	public static function initialization() {
		$GLOBALS['sweetFramework_configArray'] = array();
		self::loadSettings('SweetFramework.php');
	}
	
	public static function loadSettings($fileName) {
		
		$config = array();
		$name = str_replace('.php', '', $fileName);
		include('Settings/' . $fileName);
		$GLOBALS['sweetFramework_configArray'][$name] = $config;
	}
	
	public static function setSetting($nameSpace, $item, $value) {
		$GLOBALS['sweetFramework_configArray'][$nameSpace][$item] = $value;
	}
	
	public static function getSetting($nameSpace, $item) {
		if(array_key_exists($item, $GLOBALS['sweetFramework_configArray'][$nameSpace])) {
			return $GLOBALS['sweetFramework_configArray'][$nameSpace][$item];
		} else {
			echo 'Error unknown setting ' . "\n\n";
			return null;
		}
	}
*/