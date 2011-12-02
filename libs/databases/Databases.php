<?
/*
new idea you have Databases object which holds differnt database objects, this allows for multiple connections to manged very easily.
	the config file is now called databases uses the config['database']['param'] syntxt
@todo
	- Fork this bitch and try and make it a singleton object and see if that speeds shit up.

*/
//Note that this class doesn't seem to do all that much, it does.
class Databases extends App {
	
	protected $databases = array();
	public $currentDatabase;
	
	function __construct() {
		if($defaultDb = Config::get('site', 'database')) {
			$this->newDb($defaultDb);
		}
	}
	
	public function f($funcName, $args=array()) {
		D::log(self::getCurrentDb(), 'current db');
		return self::callFuncOnDb(self::getCurrentDb(), $funcName, $args);
	}
	
	public function newDb($name) {
		
		$database = Config::get('databases', $name);
		//Sweetframework::getClass('lib', 'databases/drivers/' . $database['driver'],  );
		//App::includeLibrary('Databases/Drivers/' . $database['driver'] . '.php');
		
		$this->setCurrentDb($name);
		
		$this->databases[$name] = Sweetframework::loadClass('lib', 'databases/drivers/' . $database['driver'],  $database);     //new $database['driver']($database);
		if(!$this->databases[$name]->connect()) {
			D::warn('failed to connect to the db');
		}
	}
	
	public function callFuncOnDb($dbname, $funcName, $args=array()) {
		//print_r(self::$databases[$dbname]);
		return call_user_func_array(array($this->databases[$dbname], $funcName), $args);
	}
	
	public function setCurrentDb($dbname) {
		$this->currentDatabase = $dbname;
	}
	
	public function getCurrentDb() {
		return $this->databases[$this->currentDatabase];
	}
	
	public function getDbVar($dbname, $varName) {
		return $this->databases[$name]->$varName;
	}
}
