<?
//

/*
todo:
add in more complete support stuff like changing users, passwords etc.

each driver class needs to have these functions and parameters:
	connect() //returns true if we connect
	prepare($sql) //returns true if it was prepaired
	query($sql) //actaully run the query
	results($sql) //return the results from the query
	escape($value) //escape a value useing the db's preferred way.
	$queries = an array of queries you have called so far
	$prepared = wether or not the current sql is prepared or not.
	$connection = the resource to the db.
	$settings = the settings used for the connection
*/
class My_SQLi {
	
	var $prepared = false;
	
	var $queries = array();
	
	var $settings;
	
	var $connection;
	
	var $result;
	
	var $connected = false;
	
	function __construct($settings) {
		$this->settings = $settings;
	}
	
	function connect() {
		//conects useing our settings
		
/* 		 
the @ is here to protect the database from the evil hackers
*/
		$this->connection = new mysqli($this->settings['host'], $this->settings['username'], $this->settings['password'], $this->settings['databaseName']);
		
		
		
		$this->connected = true;
		if (mysqli_connect_errno()) {
		    echo "Couldn't to the db dude, check the settings man.\nHost: " . $this->settings['host'] . "\nUser: " . $this->settings['username'] . "\nDatabase: " . $this->settings['host'] . "\n";
		    return false;
		}
	}
	
	function prepare($sql) {
		//for now we don't actaully prepare our sql… i know i know.
		$this->prepared = true;
	}
	
	function test($value) {
		return $value . ' w00t, db data from any where using any kind of driver.';
	}
	
	function query($sql, $returnType) {
		if($this->prepared == false) {
			$this->prepare($sql);
		}
		//echo "\n SQL call = " . $sql . "\n";
		
		$this->queries[] = $sql;
		$this->result = $this->connection->query($sql);
		
		//D::log($this->connection->error, 'db error');
		D::log($sql, 'Sql call');
		if(!empty($this->connection->error)) {
			D::report('There is something wrong with the sql.', $this->connection->error);
		}

		$this->prepared = false;
		
		//return $this->result->fetch_all(MYSQLI_ASSOC);
		$return = array();
		
		switch ($returnType){
			case 'object':
				while($value = $this->result->fetch_object()) {
					$return[] = $value;
				}
				//D::log($return);
				return $return;
			case 'assoc':
				while($value = $this->result->fetch_assoc()) {
					$return[] = $value;
				}
				return $return; 
			case 'raw':
				return $this->result;
			default:
				return true;
		}
		
	}
	
	function results($sql) {
		return $this->query($sql);
	}
	
	function escape($value) {
		return $this->connection->real_escape_string($value);
	}
}