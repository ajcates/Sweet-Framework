<?
class SweetModel extends App {

	var $model;
	var $items;	
	
	function __construct() {
		//$this->lib('databases/Query');
	}
	
	var $_buildOptions = array(
		'saveMode' => 'update',
		'find' => array(),
		'update' => array()
	);
	
	function find() {
		$this->_buildOptions['find'] = func_get_args();
		
		return $this;
	}
	
	function relative($field) {
		$pullRel = $this->relationships[$field];
		if(is_string($fKey = f_first(array_keys($pullRel)) )) {
			return $this->model(f_first($pullRel[$fKey]));
		} else {
			return $this->model(f_first($pullRel));
		}		
	}
	
	var $_filter;
	
	function filter() {
		$this->_buildOptions['filter'] = func_get_args();
		return $this;
	}
	
	function limit() {
		$this->_buildOptions['limit'] = func_get_args();
		return $this;
	}
	
	function jlimit() {
		$this->_buildOptions['jlimit'] = func_get_args();
		return $this;
	}
	
	function sort() {
		//)
		$this->_buildOptions['sort'] = f_flatten(func_get_args());
		return $this;
	}
	
	function pull() {
		//What am I gonna do here? id you just call pull() with out anything the world flips as it makes an array with 1 flipping null item.
		$this->_buildOptions['pull'] = array_filter(func_get_args());
		return $this;
	}
	
	function offset($a) {
		$this->_buildOptions['limit'][1] = $a;
		return $this;
	}
	
	function joffset($a) {
		$this->_buildOptions['jlimit'][1] = $a;
		return $this;
	}
	
	function update() {
		$this->_buildOptions['update'] = f_flatten(func_get_args());
		$this->_buildOptions['savemode'] = 'update';
		return $this;
	}
		
	function save() {
		if($this->_buildOptions['savemode'] == 'update') {
			return $this->lib('databases/Query')->update($this->tableName)->where($this->_buildFind($this->_buildOptions['find']))->set($this->_buildOptions['update'])->go();
		}
		return false;
	}
	/*
	Takes in an array or an object and inserts it into the db.
	returns back a SweetRow that has all the props you inserted, so like if you didn't inset the id of the item, its not safe to call $item->delete().
	 	 */
	function create($item) {
		//@todo change this to func_get_args ?
		if($this->lib('databases/Query')->insert($item)->into($this->tableName)->go()) {
			if(!is_array($item)) {
				$item = (array)$item;
			}
			$item[$this->pk] = $this->libs->Query->getLastInsert();
			return new SweetRow($this, $item);
		}
		return false;
	}
	
	function delete() {
		return $this->lib('databases/Query')->delete()->from($this->tableName)->where($this->_buildFind($this->_buildOptions['find']))->limit(@$this->_buildOptions['limit'])->go();
	}
	
	function all() {
		$returnItems = array();
		$i = 0;
		$last = null;
		$driver = $this->_build();
		
		$pull = f_untree((array)@$this->_buildOptions['pull']);
		//$pullRefernce = ;
		while($item = $driver->fetch_assoc()) {
	//		D::log($item, 'item');
			if(!empty($item)) {
				if(isset($item[$this->pk]) && $item[$this->pk] === $last) {
					f_call(array($returnItems[$i], 'pass'), array($item));
				} else {
					$i++;
					D::log($item, 'sweetrow - ' . $i);
					$returnItems[$i] = new SweetRow($this, $item, $pull);
					$last = isset($item[$this->pk]) ? $item[$this->pk] : null;
				}
			}
		}
		$this->_buildOptions = array();
		return array_values($returnItems);
	}
	
	function export() {
		return array_map(
			function($r) {
				return $r->export();
			},
			$this->all()
		);
	}
	
	function one() {
		return f_first($this->all());
	}
	
	function getTotalRows() {
		return intval(f_first(f_flatten($this->lib('databases/Query')->reset()->select('*')->from($this->tableName)->count()->results('assoc'))));
	}
	
	function _build() {
		$join = $select = array();
		foreach(array_keys($this->fields) as $field) {
			$select[] = $this->tableName . '.' . $field;
		}
		//array_key_exists('pull', $this->_buildOptions) && 
		if(!empty($this->_buildOptions['pull'])) {
			foreach($this->_buildPulls($this->_buildOptions['pull'], $this->tableName) as $build) {
				$join += (array)$build['join'];
				$select += (array)$build['select'];
			}
		}
		//if(array_key_exists('find', $this->_buildOptions)) {
			//$where = $this->_buildFind($this->_buildOptions['find']);
		//array_reverse()
		//@todo replace @ with ternaries.
		
		return $this->lib('databases/Query')->select($select)->join($join)->from(
			$this->tableName,
			!empty($this->_buildOptions['limit']) ? $this->_buildOptions['limit'] : null
		)->where(
			$this->_buildFind(!empty($this->_buildOptions['find']) ? $this->_buildOptions['find'] : null)
		)->limit(
			!empty($this->_buildOptions['jlimit']) ? D::show($this->_buildOptions['jlimit'], 'jlimit') : null
		)->orderBy(
			!empty($this->_buildOptions['sort']) ? $this->_buildOptions['sort'] : null
		)->go()->getDriver();
	}
	
	function _buildFind($find=null) {
		if(isset($find)) {
			foreach($find as $k => $arg) {
				if(is_int($k) && is_array($arg)) {
					unset($find[$k]);
					$find = array_merge($find, $this->_buildFind($arg));
				} else if(is_string($k) && array_key_exists($k, $this->fields)) {
					unset($find[$k]);
					$find[$this->tableName . '.' . $k] = $arg;
				} else if(is_numeric($arg)) {
					unset($find[$k]);
					$find[$this->tableName . '.' . $this->pk] = $arg;
				}
			}
		}
		return $find;
	}
	
	function _buildPulls($pulls, $on=null, $with=array()) {
		//D::log($pulls, '$pulls');
		$builtPulls = array();
		foreach($pulls as $k => $pull) {
			if(is_string($k)) {
				//sub join?
				
				$pullRel = $this->relationships[$k];
				
				if(is_string($fKey = f_first(array_keys($pullRel)) )) {
					$flName = $fKey;
					$model = $this->model(f_first($pullRel[$fKey]));
				} else {
					$flName = $k;
					$model = $this->model(f_first($pullRel));
				}
				
				if(is_array($rfName = f_last($pullRel))) {
					$rfName = f_last(f_last($pullRel));
				}
				
				$builtPulls[] = $model->_buildPull($k, $pullRel, $on, $flName, $rfName);
				
				$builtPulls = array_merge($builtPulls, $model->_buildPulls((array)$pull, $k, f_push($k, (array)$with) ));
				
			} else {
				if(is_array($pull)) {
					$builtPulls = array_merge($builtPulls, $this->_buildPulls($pull, $on, $with));
					continue;
				}
				//regular join
				if(!array_key_exists($pull, $this->relationships)) {
					D::show($this->relationships, 'Current relationships');
					D::warn($pull . ' can\'t be found in the ' . get_class($this) . ' model');
				}
				$pullRel = $this->relationships[$pull];
				
				if(is_string($fKey = f_first(array_keys($pullRel)) )) {
					$flName = $fKey;
					$model = $this->model(f_first($pullRel[$fKey]));
				} else {
					$flName = $pull;
					$model = $this->model(f_first($pullRel));
				}
				
				if(is_array($rfName = f_last($pullRel))) {
					$rfName = f_last(f_last($pullRel));
				}
				$builtPulls[] = $model->_buildPull(join('$', f_push($pull, $with)), $pullRel, $on, $flName, $rfName);
			}
		}	
		return $builtPulls;
	}
	
	function _buildPull($pull, $pullRel, $tableName, $lfName=null, $rfName=null) {
		$select = $join = array();
		//JOIN CODE:
		
		if(is_array($tableName)) {
			$tableName = join('$', ($tableName));
		}
		
		if(is_array($lfName)) {
			//D::log($pull, '$pull aka $k');
			//D::log($lfName, 'lfName');
			D::stack('is_array($lfName)');
		}
		
		$join[$this->tableName . ' AS ' . $pull] = array(
			$tableName . '.' . $lfName => $pull . '.' . $rfName
		);
		
		//SELECT CODE:
		foreach(array_keys($this->fields) as $field) {
			//$select[$pull . '.' . $field] = str_replace('$', '.', $pull) . '.' . $field;
			$select[str_replace('$', '.', $pull) . '.' . $field] = $pull . '.' . $field;
		}
	//	D::log($select, 'built select');
		return array(
			'join' => $join,
			'select' => $select
		);
	}	
}

class SweetRow {

	public $__data = array();
	public $__errors = array();
	public $__pull; 
	public $__model;
	public $__update = array();
	
	function __construct($model, $item, $pull=array()) {
		$this->__data[] = $item;
		$this->__model = $model;
		$this->__pull = $pull;
	}
	
	function pass($item) {
		$this->__data[] = $item; 
	}
	
	static function mapExport($v) {
		if(is_a($v, 'SweetRow')) {					
			return $v->export();
		} elseif(!empty($v)) {
			return $v;
		} else {
			return null;
		}
	}
	
	function export() {
		/*
		if(isEmpty(f_first($this->__data))) {
			D::log('data is on export is EMPTY');
			return null;
		}
		*/
		$item = array();
		//$this->__model->relationships
		//array_values()
		foreach(array_keys($this->__model->fields) as $field) {
			//$item->$field = $this->$field;
			$o = $this->__get($field);
			if(isset($o)) {
				if(is_scalar($o)) {
					$item[$field] = $o;
				} else {
					if(is_a($o, 'SweetRow')) {
						$item[$field] = $o->export();
					} elseif(is_array($o)) {
						$item[$field] = array_filter(array_map('SweetRow::mapExport', $o));
					} else {
						D::warn('sweet model fup = ' . gettype($o) . ' ' . $field . B::br());
					}
				}
			}
		}
		//How do you add tags to a page item
			//Every pagetag you want to add needs a refernce to a tag and a user.		
		//$item->save();
		if(!empty($this->__pull)) {		
			foreach($this->__pull as $pkey => $p) {
				if(is_string($pkey)) {
					$p = $pkey;
				} elseif(is_array($p)) {
					continue;
				}
				if(array_key_exists($p, $item)) {
					continue;
				}
				$o = $this->__get($p);
				
				if(is_a($o, 'SweetRow')) {
					$item[$p] = $o->export();
				} elseif(is_array($o)) {					
					$item[$p] = array_filter(array_map('SweetRow::mapExport', $o));
				}
			}
		}
		
		//D::log($item, 'export data');
		return $item;
	}
	
	function __get($var) {
		if(!empty($this->__pull) && (array_key_exists($var, $this->__pull) || in_array($var, $this->__pull)) ) {
			
			////// KEYS:
			$varL = strlen($var);
			$keys = array_filter(
				array_keys((array)f_first($this->__data)),
				function($k) use($var, $varL) {
					return ($var == substr($k, 0, $varL));
				}
			);
			//D::log($keys, 'keys');
			
			$varL++;
			$pull = array_key_exists($var, $this->__pull) ? $this->__pull[$var] : array();
			
			$pullRel = $this->__model->relationships[$var];
			if(is_string($fKey = f_first(array_keys($pullRel)) )) {
				//m2m
				$model = SweetFramework::getClass('model', f_first($pullRel[$fKey]));
				$returnItems = array();
				
				if(!isset($model->pk)) {
					foreach($this->__data as $row) {
						if(!empty($row)) {
							$item = self::subRow2Item($keys, $row, $varL);
							if(!empty($item)) {
								$returnItems[] = new SweetRow($model, $item, $pull);
							}
						}
					}
				} else {
					foreach($this->__data as $row) {
						if(!empty($row)) {
							$item = self::subRow2Item($keys, $row, $varL);
							if(!empty($item)) {
								if(array_key_exists($item[$model->pk], $returnItems)) {
									f_call(array($returnItems[$item[$model->pk]], 'pass'), array($item));
								} else {
									$returnItems[$item[$model->pk]] = new SweetRow($model, $item, $pull);
								}
							}
						}
					}
				}
				return $returnItems;
			} else {
				$model = SweetFramework::getClass('model', f_first($pullRel));
				if(!isset($model->pk)) {
					foreach($this->__data as $row) {
						if(!empty($row)) {
							$item = self::subRow2Item($keys, $row, $varL);
							if(!empty($item)) {
								if(isset($returnItem)) {
									$returnItem->pass($item);
								} else {
									$returnItem = new SweetRow($model, $item, $pull);
								}
							}	
						}
					}
				} else {
					foreach($this->__data as $row) {
						if(!empty($row)) {
							$item = self::subRow2Item($keys, $row, $varL);
							if(!empty($item)) {
								if(isset($returnItem) && $returnItem->{$model->pk} == $item[$model->pk]) {
									$returnItem->pass($item);
								} else {
									$returnItem = new SweetRow($model, $item, $pull);
								}
							}
						}
					}
				}
			}
			return isset($returnItem) ? $returnItem : null;		
		} else if(array_key_exists($var, $this->__model->fields)) {
			//basicly this @ is here to make sure you call any field on a SweetRow and it will just return null unless it's been set.
			return !empty($this->__data[0][$var]) ? $this->__data[0][$var] : null;
			//return @f_first($this->__data)->$var;
		}
	}
	
	function __call($var, $args=array()) {
	}
	
	function __set($var, $value) {
		$this->__update[$var] = $value;
		if(is_scalar($value)) {
			$this->__data = array_map(
				function($row) use($var, $value) {
					$row[$var] = $value;
					return $row;
				},
				$this->__data
			);
			return $value;
		} else {
			D::warn('SweetRows do not currently support non scalar values… yet.');
		}
	}
	
	function update($vals) {
		array_map(
			array($this, '__set'),
			array_keys((array)$vals),
			array_values((array)$vals)
		);
		return $this;
	}
	
	function save() {
		if(!empty($this->__update)) {
			return $this->__model->find(array('id' => $this->{$this->__model->pk}))->update($this->__update)->save();
		}
	}
	
	function test() {
		D::show($this->__pull, 'pull');
		D::show(($this->__data), 'first data');
	}
	
	public function delete() {
		return $this->__model->find($this->{$this->__model->pk})->delete();
	}
	
	static function subRow2Item($keys, $row, $varL) {
		$item = array();
		foreach($keys as $key) {
			if(!empty($row[$key])) {
				$item[substr($key, $varL)] = $row[$key];
			}
		}
		return $item;
	}
}


/*


Any time there is a key:
		- there is a sub join needed 
			- Sub joins need:
				- left field name
					- is the key in the parents relation ship structure
						/%
						*'user'* => array(
							'User',
							'id'
						),
						%/
				- right field name
					- is the last element in the parents relation ship structure
						/%
						'user' => array(
							'User',
							*'id'*
						),
						%/
				- table alias
				- right table name = table alias
				- left table name
					- Is the parents alias 
				
				
		- this sub join is based on the realtionShip structure of the key in the current model


Maybe build out a cached structure of the joins attached to the model for the sweetrows?


find is basicaly where but trys to detect an extra couple of types

try and have a static array of functions to check datatypes

the build joins needs to be felixble to allow for differnt model types being joined together.




Pages Table SQL:
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `slug` varchar(256) DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `content` text,
  `dateCreated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `pages_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

Holstr Joins:
'va_items_categories' => array(
	'va_items.item_id' => 'va_items_categories.item_id'
), 
'va_categories' => array(
	'va_items_categories.category_id' => 'va_categories.category_id',
	'va_categories.category_name' => array_map('Query::nullEscape', $filter['brand'])
),


build out the query and then return an array of sweet rows

sweet rows are chainable and can dynamicly create new sweetrows for relation ships

when you call a propertiy on a sweet row it checks to see if its model has that relation ship, if so it returns back a new sweet row
	

PagesModel.pull(array('user', 'group'))
	//should join the pages table to the user table which is joined with the group.
	- The pages model has a relationship for user defined in it. whatever model that points to will be used when finding the realtion ship for group




in theroy models only need to store 1 -> 1 relation ship info becuase more advance data would be kept in other models.
	?although you can describe more complex relationships to use in the model

are realtion ships just premade joins?
	member how i had it you only could define the column name the join func? could i somewhat recreate this with my ORM?

HolsterModel.relationShips = array(
	'guns' => array('id' => array('GunHolsters', 'holster', '')),
	'user' => array('users', 'id')
)

pull('guns')

pull(

=========


->create($keyValue) create a new object from key/value pairs
	//great for realtion ships becuase when they try and read the ID from the object it automaticly gets inserted and returned

->all() is a specical method that returns all of an items objects as a key/value array
	//maybe I need to come up with a new name for this…
	
->find() if you pass it a:
	number: you get that a key/value pair of that item as based on it's id
	array: basicy key/value pairs of the and statment
		if the value is an Array forms an IN statment
		if the value is a model it uses it's primary key(s)
	an array of numbers: Those id's.
	mutiple args, basicly an OR statment
	

->pull($cols) used to pull other types of objects instead of being lazy and doing it later.

->filter() Key value pairs of things you don't want

->limit(max) the amount of items you want to limit it too

->offset(amount, limit) the amount you would like to offset the items, by default limit is infinty unless used in combo with the limit function

->update($keyVal) Key value pairs of the things you would like to set. If you don't call the get when useing this method it sets it executes for all rows.

->save() saves the current objects to the db.

->fix($array) An array of name of the items you would like to fix

->delete() Deletes everything from the current object if no get() has been ran it deletes everything

->sort($keyVal) How you would like to sort these objects from the db if you pass if just a string it will sort by that string DESC


*/
/*
@todo:
	I think I'll have the lazy loading happen if you use the __call
---
$model = $this->_model;
if(array_key_exists($var, $model::$belongsTo)) {
	//->find(array($model::$belongsTo[$var] => $this->_data[$model::$PK] ))->all()
	//@todo add in a limit when im not working with fucktarded mssql
	return $this->getModel($var)->find(array($model::$belongsTo[$var] => $this->_data[$model::$PK] ))->all();
} else if(array_key_exists($var, $model::$objects) && method_exists($this->getLibrary(f_first($model::$objects[$var])), 'get_' . $model::$objects[$var][1])) {
	return $this->getLibrary(f_first($model::$objects[$var]))->{'get_' . $model::$objects[$var][1]}( $var, f_last($model::$objects[$var]) );
} else {
	D::warn('wtf are you trying todo?');
}
*/
/*
	
	- What do i do if a pull wasn't called?
			- how do i tell what pull was called?
		- How do i update things with out pk's?
	- How do i get rid of null tags?
	////////
	
		what if I came up with the concept of sweet data?
		sweetData vs sweetRow
		basicly a data structure for indivual rows that is capable of retriving more rows
		//what abilities would the sweetRow have?
			magic reading methods…
				would first return back a sub row
				that would call the get_field methods correctly
				
			the ability to insert more data on the fly
			
			seprates out normal row data and sub row data;
				
			
			ability to save data back into the db
				do this keep edited data sperate main data
	*/
	
/* 
		ORM TODO:
		- is this where i tell if  i have a m2m relation ship?
			- does it make sense for Forigen Keys to exist like this?
				- not really, if its how m2m relationships are defined.
				
			- do foreign keys always have a field in the current model?
				- yes.
				
			- if it is a forigen key do i only need to return one sweet row item?
				- yes.
			- do m2m always need to return an array?
				- yes.
			- what advantages do i have for detecting m2m relationships
				- the differnces between fk and m2m code?
			- what does the pk mean?
				- //? the pk is used so you dont get an array of all the same item.
					-if it is the same item it passes it to the sweetRow obj
				- do you need it on m2m?
					- shouldn't matter.
					?no
					?not always.
						- the comments example on the pages models proves that it can be avaible.
				- do you need it on fk?
					yes.
						//in order for a fk to point to something, that something needs a pk.
						
					// for the most part the fk is gonna be the same for each row.
						- when is it differnent?
							//if its differnt does that mean there are 2 items?
								//this shouldn't be possible correct?
							//?on m2m?
			
		- how do i handle backwards relationships?
			- how were they defined before?
			- how were they handeled before?
			
			- do they even need to be defined?
				-yes.
			
			- use cases for backwards relationships?
				- m2m relationships are backwards fk relationships. they already work.
		*/
		//)