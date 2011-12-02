<?

class T {
	//Just a simple global struct to keep T varibles
	static $url;
	static $loc;
	public static function get($name, $arguments=array()) {
		return SweetFramework::getClass('lib', 'Template')->get($name, $arguments);
	}
	public static function __callStatic($name, $arguments=array()) {
		return SweetFramework::getClass('lib', 'Template')->$name;
	}
}

class V {
	//view?
	static function get($reallyHopeNoOneNamesThereVaribleThis, $values=array()) {
		extract($values, EXTR_OVERWRITE);
		ob_start();
		include(T::$loc . '/views/' . $reallyHopeNoOneNamesThereVaribleThis . '.php' );
		return ob_get_clean();
	}
	public static function __callStatic($varName, $values=array()) {
		return SweetFramework::getClass('lib', 'Template')->$varName;
		//f_call(array(, 'get'), $args)
	}
}

class Theme extends App {
	
	protected static $themeUrl;
	protected static $themeLoc;
	
	function __construct() {}

	static function set($name) {
		$newPlace = 'themes/' . $name;
		if(is_dir(APP_FOLDER . '/' . $newPlace)) {
			if(defined('URL')) {
				if(substr(URL, -1) == '?') {
					T::$url = self::$themeUrl = substr(URL, 0, -1) . APP_NAME . '/' . $newPlace . '/';
				} else {
					T::$url = self::$themeUrl = URL . APP_NAME . '/' . $newPlace . '/';
				}
			}			
			T::$loc =  self::$themeLoc = APP_FOLDER . '/' . $newPlace;
			D::log($name, 'Theme Set');
			return true;
		} else {
			D::error('Theme doesn\'t exist');
		}
	}
	
	static function loadSnippets($names) {
		foreach((array)$names as $name) {
			require_once(T::$loc . 'snippets/' . $name . '.php' );
		}
	}
	
	function showView($name, $options=array()) {
		echo V::get($name, $options);
	}
	
	function getView($name, $options=array()) {
		return V::get($name, $options);
	}
}
