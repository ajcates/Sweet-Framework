<?php
//Todo
/*
@todo
take out the $GLOBABLS and use protected static vars instead

come up with classier names then getSetting and setSetting
	get n set ?
*/
class Config {

	public static $configArray;
	
	private $configs;
	
	public function __construct() {}
	
	private $paths = array('/sweet-framework/settings/');
	
	public function addPath($path) {
		array_push($this->paths, $path);
	}
	
	public function get($className, $param=null) {
		if(!isset($this->configs[$className])) {
			Sweetframework::loadFileType('config', $className);
		}
		if(isset($param)) {
			return $this->configs[$className][$param];
		} else {
			return $this->configs[$className];
		}
	}
	
	public function setAll($nameSpace, $values) {
		$this->configs[$nameSpace] = $values;
	}
	
	public function set($nameSpace, $item, $value) {
		$this->configs[$nameSpace][$item] = $value;
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