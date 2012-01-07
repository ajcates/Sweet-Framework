<?
/*
abstract class dbDriver {
	abstract function getField($id);
	abstract function getNumFields();
	abstract function gettingRows();
	abstract function getFieldCount();
	abstract function getFieldTable($id);
	abstract function getLastInsert();
	
	abstract function query($sql);
	abstract function getErrors();
	abstract function escape($value);
}
*/

class Query extends App {
	
	private $_mode = 'select';
	private $_whereData = array();
	private $_selectData;
	private $_joinData;
	private $_setData;
	private $_joins;
	private $_joinOns;
	static public $_fromValue;
	static public $_fromLimit;
	
	private $_whereValue;
	private $_limit;
	private $_orderBy;
	private $_groupBy;
	private $_updateValue;
	private	$_insert;
	private $_selectFunction;
	
	static protected $_driver;
	static $last = '';
		
	public function __construct() {
		$this->helper('misc');
		self::$_driver = $this->lib('databases/Databases')->getCurrentDb();
	}
	
	function insert($values) {
		$this->_insert = $values;
		//D::show($this->_insert, 'Incert');
		return $this;
	}
	
	function into($tableName) {
		$this->_mode='insert';
		return $this->from($tableName);
	}
	
	public function reset() {
		$this->_mode = 'select';
		//@todo turn the following properties into one array.
		self::$_fromValue = null;
		$this->_whereData = array();
		$this->_selectData = null;
		$this->_joinData = null;
		$this->_setData = null;
		$this->_joins = null;
		$this->_joinOns = null;
		$this->_whereValue = null;
		$this->_limit = null;
		$this->_orderBy = null;
		$this->_groupBy = null;
		$this->_updateValue = null;
		$this->_insert = null;
		$this->_selectFunction = null;
		self::$_fromLimit = null;
		return $this;
	}

	public function select($cols='*') {
		if(is_string($cols)) {
			$this->_selectData = func_get_args();
		} else {
			$this->_selectData = (array)$cols;
		}
		return $this;
	}
	
	function from($val, $limit=null) {
		if(is_array($val)) {
			Query::$_fromValue = f_keyMap(
				function($v, $k) {
					if(is_string($k)) {
						return $v . ' AS ' . $k;
					}
					return $v;
				},
				$val
			);
		} else {
			Query::$_fromValue = $val;
		}
		if(isset($limit)) {
			Query::$_fromLimit = array_map('intval', (array)$limit);
		}
		return $this;
	}
	public function where() {
		$this->_whereValue = func_get_args();
//		D::log($this->_whereValue, 'where val');
		return $this;
	}

	public function update($value) {
		$this->_mode = 'update';
		Query::$_fromValue = f_flatten(func_get_args());
		return $this;
	}
	function delete() {
		$this->_mode = 'delete';
		return $this;
	}

	function set($value) {
		$this->_setValue = $value;
		return $this;
		
	}
	
	function count() {
		$this->_selectFunction = 'COUNT';
		return $this;
	}
	
	function join($values) {
//		D::log($values, 'Join Values');
		if(!array_key_exists(0, $values)) {
			$values = array($values);
		}
		$this->_joins = $values;
		return $this;
	}
	
	public function orderBy($value=null) {
		$this->_orderBy = $value;
		return $this;
	}
	
	public function groupBy($value=null) {
		$this->_groupBy = $value;
		return $this;
	}

	public function limit() {
		$this->_limit = array_filter(f_flatten(func_get_args()));
		//D::log($this->_limit, 'limit');
		//D::log(array_filter($this->_limit), 'limit filtered');
		if(empty($this->_limit)) {
			$this->_limit = null;
		}
		return $this;
	}
	
	function _buildSelect() {
		if(isset($this->_selectFunction)) {
			return $this->_selectFunction .'(*)';
		}
		
		
		//'total' => array('COUNT' => 'ft_style.feature_value'),
				//COUNT(`ft_style.feature_value`) AS 'total',
				
			//array('COUNT' => 'ft_style.feature_value')
/*
		'total_count' => array('COUNT' => 'ft_style.feature_value'),
		array('COUNT' => 'ft_style.feature_value')
		'total' => 'va_items.item_id',
		'color_finish' => 'ft_color_finish.feature_value',
		'regularField',
		'style' => 'ft_style.feature_value',
*/
		
		
		return join(', ', f_keyMap(
			function($v, $k) {
 				if(is_array($v)) {
					$v = join(', ', f_keyMap(
						function($vV, $vK) {
							return Query::escape($vK) . '(' . join(',', array_map('Query::escape', (array) $vV)) . ')';
						},
						$v
					));
				} else {
					$v = Query::escape($v);
				}
				if(is_string($k)) {
					return $v . ' AS ' . Query::escape($k, '\'');
				} else {
					return $v;
				}
				/*
				//old:
				if(is_string($k)) {
					if(is_array($v)) {
						return Query::escape($k) . '(' . join(',', array_map('Query::escape', $v)) . ')';
					}
					return Query::escape($k) . ' AS \'' . Query::escape($v) . '\'';
				}
				return $v;
				*/
			},
			$this->_selectData
		));
	}
/*
Array (
    [slug] => articles
    [0] => Array (
        [children.published_on] => 1251788400
        [0] => >
    )
    [1] => Array (
        [children.published_on] => 1254380400
        [0] => <
    )
)

WHERE (
	(
		(
			children.published_on < 1254380400
		)
		AND articles.slug < 'articles'
		AND articles.id < 1254380400
	)
)
*/
	static function _buildWhere($group, $groupOperator='AND', $escape=true) {
		//"Bitch I'll pick the world up and I'ma drop it on your f*ckin' head" - Lil Wayne.
		$keys = array_keys($group);
		if(is_int(f_last($keys)) && is_string(f_last($group))) {
			$operator = f_last($group);
			$group = f_chop($group);
		} else {
			$operator = '=';
		}
		if(is_int(f_first($keys)) && is_string(f_first($group))) {
			$groupOperator = f_first($group);
			$group = f_rest($group);
		}
		$builtArray = f_keyMap(
			function($value, $key) use($groupOperator, $operator, $escape) {
				if(is_int($key) && is_array($value)) {
					$bWhere = Query::_buildWhere($value, $groupOperator, $escape);
					if(!empty($bWhere)) {
						return '(' . "\n\t" . $bWhere . "\n" .')';
					} else {
						return null;
					}
				}
				if(is_string($key)) {
					static $escapeFunc = array('Query' , 'nullEscape');
					if(!$escape) {
						$escapeFunc = 'nothing';
					} else {
						$escapeFunc = f_callable($escapeFunc);
					}
					if(is_array($value)) {
						$key = $escapeFunc($key, '');
						if(f_first(array_keys($value)) !== 0) {
							return join(' ' . $groupOperator . ' ', f_keyMap(function($v, $k) use($key, $escapeFunc) {
								return $key . ' BETWEEN ' . $escapeFunc($k) . ' AND ' . $escapeFunc($v);
							}, $value));
						}
						return $key . ' IN (' . join(', ', array_map($escapeFunc, $value)) . ')'; 
					} else {
						$value = call_user_func($escapeFunc, $value);
						if($value === 'null') {
							if($operator == '=') {
								$operator = 'IS';
							} else {
								$operator = 'IS NOT';
							}
						}
						return Query::escape($key) . ' ' . $operator . ' ' . $value;
					}
				}
			},
			$group
		);
		if(!empty($builtArray)) {
			return join(' ' . $groupOperator . ' ', array_filter($builtArray));
		}
	}
	
	function _buildOrderBy() {
		if(!empty($this->_orderBy)) {
			return "\n" . ' ORDER BY ' . join(' , ', f_keyMap(
				function($v, $k) {
					return Query::escape($k) . ' ' . Query::escape($v);
				},
				$this->_orderBy
			));
		}
	}
	
	function _buildGroupBy() {
		if(!empty($this->_groupBy)) {
			return "\n" . ' GROUP BY ' . join(' , ', array_map('Query::escape', $this->_groupBy));
		}
	}
	
	function _buildJoins() {
	 	if(!empty($this->_joins)) {
		 	return "\n" . join(' ', f_map(
		 		function($join) {
		 	 	 	return join(' ', f_keyMap(
						function($joinSets, $jTable) {
							//$jTableName = f_last(explode(' ', $jTable));
							return "\n" . ' LEFT JOIN ' . $jTable . "\n\t" . 'ON ' . Query::_buildWhere($joinSets, 'AND', false);
						},
						$join
					));
				},
				$this->_joins
			));
	 	}
	}
	
	function _buildLimit($limit=null) {
		//D::show($limit );
		if(!empty($limit)) {
			return "\n" . ' LIMIT ' . join(', ', array_reverse($limit));
		}
	}
	
	function _buildSet($values, $separator='=') {
		return join(
			', ',
			f_keyMap(
				function($v, $k) use ($separator) {
					return Query::escape($k) . ' ' . $separator . Query::nullEscape($v);
				},
				$values
			)
		);
	}
	
	function _buildWhereString($values) {
		if(empty($values)) {
			return '';
		}
		$whereContent = $this->_buildWhere($values);
		if(!empty($whereContent)) {
//			D::log($whereContent, 'where content');
			return "\n" . ' WHERE ' . $whereContent;
		}
	}
	
	function _build() {
		//puts all the stuff together in a magic happy fashion.
		$sqlString = '';
		switch ($this->_mode) {
			case 'select':
				//adds in our select values				
				//@todo Make the second parameter in `form()` actaully be a real "sub query"
					//$this->
				if(isset(self::$_fromLimit)) {
					self::$_fromValue = '(SELECT * FROM ' . Query::$_fromValue . $this->_buildLimit(Query::$_fromLimit) . ') AS ' . Query::$_fromValue;
				}
				
				
				$sqlString = 'SELECT ' . $this->_buildSelect() . "\n" . ' FROM ' . join(', ', (array)Query::$_fromValue) . $this->_buildJoins() .  $this->_buildWhereString($this->_whereValue) . $this->_buildGroupBy() . $this->_buildOrderBy() . $this->_buildLimit($this->_limit);
				break;
			case 'update':
				$sqlString = 'UPDATE ' . f_first(Query::$_fromValue) . "\n" . ' SET ' . $this->_buildSet($this->_setValue) . $this->_buildWhereString($this->_whereValue);
				break;
			case 'insert':
				/*
				f_reduce(
					function($a, $b) {
						return array_merge(array_keys((array)$b), array_keys((array)$a));
					},
					$this->_insert
				);
				*/
				if(!is_array(f_first($this->_insert) )) {
					$this->_insert = array($this->_insert);
				};
				$cols = array_map(function($v) { return Query::nullEscape($v, '`');}, array_keys(array_reduce($this->_insert, 'array_merge_recursive', array())));
				
				
				$sqlString = 'INSERT INTO ' . f_first((array) Query::$_fromValue) . ' (' . join(', ', $cols) . ') VALUES ' . join(', ', f_map(
					function($v) use($cols) {
						return '(' . join(',', f_map(
							function ($i) use ($v) {
								$i = substr($i, 1, -1);
								if(isset($v[$i])) {
									return Query::nullEscape($v[$i]);
								} else {
									return 'null';
								}
							},
							$cols
						)) . ')';
					},
					D::log($this->_insert, 'Insert Data')
				));
				break;
			case 'delete':
				$sqlString = 'DELETE FROM ' . join(', ', (array)Query::$_fromValue) . $this->_buildWhereString($this->_whereValue);
				break;
		}
		$this->sql = $sqlString;
		return $this->sql;
	}
	
	public function go($query=null) {
		
		if(!isset($query)) {
			self::$last = $this->_build();
		} else {
			self::$last = $query;
		}
		$this->reset();
		if(!self::$_driver->query(self::$last)) {
			D::warn('Query Failed: ' . self::$last);
			return false;
		}
		
		return $this;
	}
	
	public function results($type='object', $query=null) {
		if(!isset($query)) {
			self::$last = $this->_build();
		} else {
			self::$last = $query;
		}
		$this->reset();
		return self::$_driver->query(self::$last, $type);
	}
	
	public function getDriver() {
		return self::$_driver;
	}
	
	public function getLastInsert() {
		return self::$_driver->lastInsert();
	}
	
	public static function nullEscape($var, $sep="'") {
		if(!isset($var)) {
			return 'null';
		}
		if(is_bool($var) || is_int($var)) {
			return intval($var);
		}
        if(is_float($var)) {
            return floatval($var);
        }
		return self::escape($var, $sep);
	}
	
	public static function escape($var, $sep='') {
		//Databases::f('query', array($sql, $type));
		//@todo change this.
		if(is_bool($var) || is_int($var)) {
			return intval($var);
		}
        if(is_float($var)) {
            return floatval($var);
        }
		return $sep . mysql_escape_string($var)  . $sep;
	}
		
	/*
	###`select()`
	
	`$this->select('*')`
	:	SELECT *

	`$this->select('colName', 'otherCol')`
	:	SELECT colName, otherCol

	`$this->select(array('colName', 'otherCol'))`
	:	SELECT colName, otherCol

	###`where()`
		
	`$this->select('*')->where(array('item' => 5))->from('table')`
	:	SELECT * FROM table WHERE item = '5'
	
	`$this->select('*')->where(array('item' => 5, 'thing' => 'what'))->from('table')`
	:	SELECT * FROM table WHERE item = '5' AND thing = 'what'

	`$this->select('*')->where(array(
		'OR',
		'item' => 5,
		'thing' => 'what'
	))->from('table')`
	:	SELECT * FROM table WHERE item = '5' OR thing = 'what'

	`$this->select('*')->where(array('item' => 5, 'thing' => 'what', '!='))->from('table')`
	:	SELECT * FROM table WHERE item != '5' OR thing != 'what'

	`$this->select('*')->where(array('item' => array('what', 'who'))->from('table')`
	:	SELECT * FROM table WHERE item IN ('what', 'who')

	`$this->select('*')->where(array('item' => array(10 => 30))->from('table')`
	:	SELECT * FROM table WHERE item BETWEEN (10 AND 30)
	
	###`join()`

	`$this->select('*')->from('Club_RnD.dbo.Posts')->join('Club_RnD.dbo.Comments', array('Club_RnD.dbo.Posts.comments' => 'Club_RnD.dbo.Comments.id'))`
	:	SELECT * FROM Club_RnD.dbo.Posts LEFT JOIN Club_RnD.dbo.Comments ON Club_RnD.dbo.Posts.comments = Club_RnD.dbo.Comments.id
	
	
	###`delete()`
	
	`$this->delete()->from('tableName')`
	:	DELETE FROM tableName
	
	###`set()`
	
	`$this->set(array('key' => 'value')`
	:	SET key = 'value'
	*/
}
