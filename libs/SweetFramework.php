<?
require_once('App.php');
/*
@todo
	- Make the fileLoading functions support multiple folder levels
	- Get the framework to load a controller
	- Make sure the app/framework split is working correctly
	- 
*/


function __autoload($class) {



}

class SweetFramework extends App {

	var $site;
	var $viewPrefix;
	var $appShortcut;

	static $urlPattern;

	function __construct($appSettingName) {
		//crap we need for the framework
		//$GLOBALS['app'] = $this; //stop this.
		$this->helper('functional'); //makes my life oh so much easier :)
		$this->lib(array('D', 'Config')); //Get the debuger and the config loader
		D::initialize($this->libs->Config->get('Debug')); //start the debugger up with some config options
		D::time('App', 'SweetFramework - ' . date("F j, Y, g:i a")); //Write what time the app starts to the log
		
		$appInfo = $this->libs->Config->get('SweetFramework', $appSettingName); //get the current app's settings
		define('APP_FOLDER', $appInfo['folder']);
		foreach($appInfo['paths'] as $k => $v) {
			if(!is_array(self::$paths[$k])) {
				self::$paths[$k] = array();
			}
			//add in the applications folders to the frameworks file loader
			self::$paths[$k][] = '/' . APP_FOLDER . '/' . $v .'/';
			//self::$paths[$k][] = join('/', array(LOC, $appInfo['folder'], $v)) .'/'; @todo A/B test these two.
		}
		
		$this->lib(array_merge(array('Uri', 'Theme'), $this->libs->Config->get('site', 'autoload') ));

		$this->libs->Uri->callRoute();
		self::end();
	}
	
	static protected $paths = array(
		'lib' => array('/sweet-framework/libs/'),
		'model' => array(),
		'helper' => array('/sweet-framework/helpers/'),
		'controller' => array(),
		'config' => array('/sweet-framework/settings/')
	);
	
	static $classes = array();
	
	public static function className($file) {
		if(substr($file, -4) == '.php') {
			return substr(strrchr('/' . $file, '/'), 1, -4);
		}
		return substr(strrchr('/' . $file, '/'), 1);
	}
	
	public static function loadFile($path, $fileName) {
		if(file_exists(LOC . $path . $fileName)) {
			require_once(LOC . $path . $fileName);
			return true;
		}
		return false;
	}
	
	public static function fileLoc($name) {
		if(substr($name, -4) != '.php') {
			$name .= '.php';
		}
		return $name;
	}
	
	public static function loadFileType($type, $name) {
		/*  @todo
			- need to use a FileName function here #Maybe
		*/
		//$loc = self::fileLoc($name);
		foreach(self::$paths[$type] as $path) {
			if(self::loadFile($path, $name. '.php')) {
				return true;
			}
		}
		D::warn('Could not load file with type ' . $type . ' and name ' . $name);
		return false;
	}
	
	public static function loadClass($type, $name, $params=array()) {
		self::loadFileType($type, $name);
		$c = self::className($name);
		return new $c($params);
	}
	
	public static function getClass($type, $name, $params=array()) {
		$cName = self::className($name);
		if(!array_key_exists($type . $cName, self::$classes)) {
			self::loadFileType($type, $name);
			self::$classes[$type . $cName] = new $cName($params);
		}
		$return =& self::$classes[$type . $cName];
		return $return;
	}
	
	
	///////////////////
		
	static protected $sweetLibs = array();
	
	/**
	 * end function. Shuts the party down.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function end() {
		if(isset(self::$classes['libSession'] )) {
		
			//@todo make this more module and not so HARDcoded. :)
			self::$classes['libSession']->save();
		}
		D::time('App', 'End');
		D::close();
		exit;
	}
}
/*
Notes:
	File load types:
		- Libs:
			- App classes
			- Regular Codeigniter Library
			- Basic includes
		- Blocks:
		- Settings:
		- Models:
		- Themes:
	
	folders have to work.
	.php is optional
	
	
	there are differnt file "types" kept in a list.
	
	SweetFramework is an App factory.
	
	- get "ClassName" function
	
	"ClassNames" are valid "FileNames".
	
	FileNames are consider busted until caled but the LoadApp file function?
	
	
	
	- LoadFileType file function 		<- "types" abstraction switch happens here
		: load app takes a "FileName" 					- Which uses a isFileReal function?
		
	- LoadFileType in theroy could use a CodeIgniter style loading function for some things
		- Which then in theroy could use a basic include function
			- Which uses a isFileReal function?

*/