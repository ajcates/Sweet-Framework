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
class My_SQL {
	
	var $prepared = false;
	
	var $queries = array();
	
	var $settings;
	
	var $connection;
	
	var $result;
	
	var $connected = false;
	
	function __construct($settings) {
		$this->settings = $settings;
	}
	
	function connect($settings=null) {
		//print_r($settings);
		if(isset($settings)) {
			$this->settings = $settings;
		}
	//	D::show($this->settings, 'newDB');
		//connects using our settings
		
/* 	@todo make the @ be able to be turned on and off by debug mode	 */
		$this->connection = @mysql_connect($this->settings['host'], $this->settings['username'], $this->settings['password']);
		if (!$this->connection)	{
			D::warn("Couldn't to the db dude, check the settings man.\nHost: " . $this->settings['host'] . "\nUser: " . $this->settings['username'] . "\nDatabase: " . $this->settings['host']);
			return false;
		} else {
			if(!mysql_select_db($this->settings['databaseName'])) {
				D::warn('Could not select the database: ' . $this->settings['databaseName']);
				return false;
			}
			D::log('DB Connected');
			$this->connected = true;
			return true;
		}
	}

	function prepare($sql) {
		//for now we don't actaully prepare our sql… i know i know.
		$this->prepared = true;
	}
	
	function test($value) {
		return $value . ' w00t, db data from any where using any kind of driver.';
	}
	
	function query($sql, $returnType='null') {
		if($this->prepared == false) {
			$this->prepare($sql);
		}
		//echo "\n SQL call = " . $sql . "\n";
		
		$this->queries[] = $sql;
	//	$this->result = $this->connection->query($sql);
		D::log($sql, 'Sql call');
		
		$this->result = mysql_query($sql, $this->connection);
		
		$this->prepared = false;
		
		if(!$this->result) {
			D::log(mysql_error($this->connection), 'SQL Errors');
			return false;
		}
		
		$returnArray = array();
/* 	@todo
get rid of this switch and use an array of functions instead.
	 */
		switch ($returnType) {
			case 'object':
				if(!is_resource($this->result)) {
					D::stackTrace();
				}
				while($row = mysql_fetch_object($this->result)) {
					$returnArray[] = $row;
				}
				return $returnArray;
					
				
				break;
			case 'assoc':
				while($row = mysql_fetch_assoc($this->result)) {
					$returnArray[] = $row;
				}
				return $returnArray;
				break;
			case 'raw':
				return $this->result;
				break;
			default:
				return true;
		}
	}
	
	function results($sql) {
		//@todo figure wtf this is
		return $this->query($sql);
	}
	
	function escape($value) {
		return mysql_real_escape_string((string)$value, $this->connection);
	}
}