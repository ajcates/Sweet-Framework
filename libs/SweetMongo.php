<?
class SweetMongo extends App {


	private $connection;
	private $db;
	private $collection;

	function __construct() {
		$this->connection = new Mongo(Config::get('mongo', 'host') . ':' . Config::get('mongo', 'port'));
		$this->db = $this->connection->selectDB(Config::get('mongo', 'database'));
	}
	
	function getCollection($name) {
		return $this->db->$name;
	}
	
	function __call($name, $args) {
		return call_user_func_array(array($this->db, $name), $args);
	}
	
}