<?
require_once('App.php');
/*
@todo
	- Make the fileLoading functions support multiple folder levels
	- Get the framework to load a controller
	- Make sure the app/framework split is working correctly
	- 
*/
class SweetEvent {
	static $events = array();
	
	static function bind($name, $func) {
		if(!isset(self::$events[$name])) {
			self::$events[$name] = array($func);
		} else {
			self::$events[$name][] = $func;
		}
	}

	static function trigger($name) {
		D::log('Triggering Event: ' . $name);
		if(isset(self::$events[$name])) {
			array_map('call_user_func', (array)self::$events[$name]);
		}
	}
}

class SweetFramework extends App {
	
	static $classes = array();

	function __construct() {
		//crap we need for the framework
		//$GLOBALS['app'] = $this; //stop this.
		$this->helper('functional'); //makes my life oh so much easier :)
		$this->lib(array('D', 'Config')); //Get the debuger and the config loader
		
		D::initialize($this->libs->Config->get('Debug')); //start the debugger up with some config options
		D::time('App', 'SweetFramework - ' . date('F j, Y, g:i a')); //Write what time the framework starts to the log
		$this->lib('Uri');
	}
	
	function loadApp($appSettingName, $mainApp=false) {
	
		$appInfo = $this->libs->Config->get('SweetFramework', $appSettingName); //get the current app's settings
		
	//	D::show($this->libs->Config->get('SweetFramework'), 'sweet-framework settings');
		
		
		
		foreach($appInfo['paths'] as $k => $v) {
			if(!is_array(self::$paths[$k])) {
				self::$paths[$k] = array();
			}
			//add in the applications folders to the frameworks file loader
			self::$paths[$k][] = '/' . $appInfo['folder'] . '/' . $v .'/';
			//self::$paths[$k][] = join('/', array(LOC, $appInfo['folder'], $v)) .'/'; @todo A/B test these two.
		}
		if($mainApp == true && !defined('APP_FOLDER')) {
			define('APP_FOLDER', $appInfo['folder']);
			$this->lib(array('Theme', $this->libs->Config->get('site', 'autoload')) );
			
			if(!$this->libs->Theme->set($this->libs->Config->get('site', 'theme'))) {
				D::error('Theme could not be found. Debug: $Config->getSetting(\'Site\', \'defaultTheme\') = ' . $this->libs->Config->get('site', 'defaultTheme'));
			}
			
		}
		return $this;
	}
	
	function run($route=null) {
		D::log('App Run');
		$this->libs->Uri->callRoute($route);
	}
	
	static protected $paths = array(
		'lib' => array('/sweet-framework/libs/'),
		'model' => array(),
		'helper' => array('/sweet-framework/helpers/'),
		'controller' => array(),
		'config' => array('/sweet-framework/settings/')
	);
	
	public static function className($file) {
/*
		if(substr($file, -4) == '.php') {
			return substr(strrchr('/' . $file, '/'), 1, -4);
		}
*/
		return substr(strrchr('/' . $file, '/'), 1);
	}
	
	public static function loadFile($path, $fileName, $forceLoad=false) {
		if(file_exists(LOC . $path . $fileName)) {
			if($forceLoad) {
				require(LOC . $path . $fileName);
			} else {
				require_once(LOC . $path . $fileName);
			}
			return true;
		}
		return false;
	}
	
	public static function loadFileType($type, $name, $forceLoad=false) {
		/*  @todo
			- need to use a FileName function here #Maybe
		*/
		//$loc = self::fileLoc($name);
		foreach(self::$paths[$type] as $path) {
			if(self::loadFile($path, $name. '.php', $forceLoad)) {
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
		if(!array_key_exists(($tcName = $type . $cName), self::$classes)) {
			self::loadFileType($type, $name);
			self::$classes[$tcName] = new $cName($params);
			if(method_exists(self::$classes[$tcName], '__sweetConstruct')) {
				self::$classes[$tcName]->__sweetConstruct();
			}
		}
		$return =& self::$classes[$tcName];
		return $return;
	}
	
	
	///////////////////
		
//	static protected $sweetLibs = array();
	
	/**
	 * end function. Shuts the party down.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function end() {
		SweetEvent::trigger('SweetFrameworkEnd');
		
		SweetEvent::$events = array();
		
		D::time('App', 'End');
		D::close();
		
		self::$paths = array(
			'lib' => array('/sweet-framework/libs/'),
			'model' => array(),
			'helper' => array('/sweet-framework/helpers/'),
			'controller' => array(),
			'config' => array('/sweet-framework/settings/')
		);
		self::$classes = array();
		
		//exit;
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