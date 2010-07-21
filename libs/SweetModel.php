<?
class SweetModel extends App {

	var $model;
	var $items;	
	
	function __construct() {
		$this->lib('Query');
	}
	
	var $_buildOptions = array(
		'saveMode' => 'update',
		'find' => array(),
		'update' => array()
	);
	
	function find() {
		$this->_buildOptions['find'] = D::log(func_get_args(), 'find args');
		
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
	
	function update() {
		$this->_buildOptions['update'] = f_flatten(func_get_args());
		$this->_buildOptions['savemode'] = 'update';
		return $this;
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
		
		return $this->lib('Query')->select($select)->join($join)->from($this->tableName, @$this->_buildOptions['limit'])->where($this->_buildFind($this->_buildOptions['find']))->orderBy(@$this->_buildOptions['sort'])->go()->getDriver();
	}
	
	function _buildFind($find=array()) {
		foreach($find as $k => $arg) {
			if(is_int($k) && is_array($arg)) {
				$find = array_merge($find, $this->_buildFind($arg));
			} else if(is_string($k) && array_key_exists($k, $this->fields)) {
				unset($find[$k]);
				$find[$this->tableName . '.' . $k] = $arg;
			} else if(is_numeric($arg)) {
				$find[$this->tableName . '.' . $this->pk][] = $arg;
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
				
				/*
				if $flName is an array
					then $k is where its at?
				 				 */
				
				
				$builtPulls[] = $model->_buildPull($k, $pullRel, $on, $flName, $rfName);
				
				$builtPulls = array_merge($builtPulls, $model->_buildPulls((array)$pull, $k, f_push($k, (array)$with) ));
				
			} else {
				if(is_array($pull)) {
					$builtPulls = array_merge($builtPulls, $this->_buildPulls($pull, $on, $with));
					continue;
				}
				//regular join
				D::log($pull, '$pull');
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
			$select[$pull . '.' . $field] = str_replace('$', '.', $pull) . '.' . $field;
		}
	//	D::log($select, 'built select');
		return array(
			'join' => $join,
			'select' => $select
		);
	}
	
	function save() {
		if($this->_buildOptions['savemode'] == 'update') {
			return $this->lib('Query')->update($this->tableName)->where($this->_buildFind($this->_buildOptions['find']))->set($this->_buildOptions['update'])->go();
		}
		return false;
	}
	
	function create($item) {
		//@todo change this to func_get_args ?
		if($this->lib('Query')->insert($item)->into($this->tableName)->go()) {
			if(is_array($item)) {
				return new SweetRow($this, arrayToObj($item));
			} else {
				return new SweetRow($this, $item);
			}
		}
		return false;
	}
	
	function delete() {
		return $this->lib('Query')->delete()->from($this->tableName)->where($where)->limit(@$this->_buildOptions['limit'])->go();
	}
	
	function all() {
		$returnItems = array();
		$i = 0;
		$last = null;
		$driver = $this->_build();
	
		while($item = $driver->fetch_object()) {
			if($item->{$this->pk} == $last) {
				f_call(array($returnItems[$i], 'pass'), array($item));
			} else {
				$i++;
				$returnItems[$i] = new SweetRow($this, $item);
				$last = $item->{$this->pk};
			}
		}
	
		return array_values($returnItems);
	}
	
	function one() {
		return f_first($this->all());
	}
	
	function getTotalRows() {
		return intval(f_first(f_flatten($this->lib('Query')->select('*')->from($this->tableName)->count()->results('assoc'))));
	}
	
	
}

class SweetRow {

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
	public $__data = array();
	public $__errors = array();
	public $__model;
	
	function __construct($model, $item) {
		$this->__data[] = $item;
		$this->__model = $model;
	}
	
	function pass($item) {
		$this->__data[] = $item; 
	}
	
	function __set($var, $value) {
		/*
		$model = $this->_model;
		if (is_callable(array($this->getLibrary(f_first($model::$objects[$var])), 'set_' . $model::$objects[$var][1]))) {
			$value =  $this->getLibrary(f_first($model::$objects[$var]))->{'set_' . $model::$objects[$var][1]}( $var, f_last($model::$objects[$var]) );
		}
		if(count((array)$value) > 1) {
			D::log($value, "Caught error");
			$this->_errors[$var] = $value;
		} else {
			$this->_data[$var] = f_first((array)$value);
			$this->$var = $this->_data[$var];
		}
		*/
	}
	
	function __get($var) {
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
		if(property_exists($this->__model, 'relationships') && array_key_exists($var, $this->__model->relationships)) {
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
			$pullRel = $this->__model->relationships[$var];
			
			if(is_string($fKey = f_first(array_keys($pullRel)) )) {
				//m2m
				$model = SweetFramework::getClass('model', f_first($pullRel[$fKey]));
				foreach($this->__data as $row) {
					$item = new stdClass();
					foreach($keys as $key) {
						//D::log(substr($key, $varL), 'subkey');
						$item->{substr($key, $varL)} = $row->$key;
					}
					//D::log($item, 'm2m item');
					$returnItems[] = new SweetRow($model, $item);
				}
				return $returnItems;
			} else {
				$model = SweetFramework::getClass('model', f_first($pullRel));
				$last = null;
				$returnItems = array();
				foreach($this->__data as $row) {
					$item = new stdClass();
					foreach($keys as $key) {
						if($subKey = substr($key, $varL)) {
							$item->$subKey = $row->$key;	
						}	
					}
					if(isset($returnItem) && $returnItem->{$model->pk} == $last) {
						$returnItem->pass($item);
						//f_call(array($returnItem, 'pass'), array($item));
					} else {
						$returnItem = new SweetRow($model, $item);
						$last = $item->{$model->pk};
					}
					
					//$return[] = $r;
				}
				return $returnItem;
			}
			
			/*
if(!isset($model->pk)) {
				foreach($this->_data as $row) {
					$item = new stdClass();
					foreach($keys as $key) {
						D::log(substr($key, $varL), 'subkey');
						$item->{substr($key, $varL)} = $row->$key;
					}
					$returnItems[] = new SweetRow($model, $item);
				}
			} else {
				foreach($this->_data as $row) {
					$item = new stdClass();
					foreach($keys as $key) {
						if($subKey = substr($key, $varL)) {
							$item->$subKey = $row->$key;	
						}	
					}
					if($item->{$model->pk} == $last) {
						f_call(array($returnItems[$i], 'pass'), array($item));
					} else {
						$i++;
						$returnItems[$i] = new SweetRow($model, $item);
						$last = $item->{$model->pk};
					}
					
					//$return[] = $r;
				}
			}
			
			if(count($returnItems) > 1) {
				D::log($var, 'var many');
				return $returnItems;
			} else {
				D::log($var, 'var single');
				return f_first($returnItems);
			}
*/
			
			
			/*
			$i = 0;
			$last = null;
			foreach($return as $item) {
			
				if($item->{$this->pk} == $last) {
					f_call(array($returnItems[$i], 'pass'), array($item));
				} else {
					$i++;
					$returnItems[$i] = new SweetRow($this, $item);
					$last = $item->{$this->pk};
				}
			
			}
			*/
			
		//	D::log($newSweetRow, '$newSweetRow');
			////////////
			
			//$fields = array_keys( SweetFramework::getClass('model', f_first($pullRel[$fKey]) )->fields ) //SweetFramework::getClass('model', f_first($pullRel[$fKey]) )->relationships;
			//
		} else if(array_key_exists($var, $this->__model->fields)) {
			return f_first($this->__data)->$var;
		}
		/*
		$model = $this->_model;
		if(method_exists($this->getLibrary(f_first($model::$objects[$var])), 'get_' . $model::$objects[$var][1])) {
    		return $this->getLibrary(f_first($model::$objects[$var]))->{'get_' . $model::$objects[$var][1]}($this->_data[$var], f_last($model::$objects[$var]) );
    	}
    	
		return $this->_data[$var];
		*/
	}
	
	function __call($var, $args=array()) {
		/*
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
	}
	
	function set($var, $value) {
		/*
		$this->_data[$var] = $value;
		return $this;
		*/
	}
	
	function get($var, $fetch=false) {
		/*
		if(is_array($var) && 1 < count($var)) {
			return $this->{f_first($var)}->get(f_rest($var), $fetch);
		}
		if($fetch) {
			return $this->_data[f_first((array)$var)];
		} else {
			return $this->{f_first((array)$var)};
		}
		*/
	}
	
	function save() {
		/* @todo figure out if this needs to return its self. */
		/*
		if(!empty($this->_errors)) {
			return false;
		}
		$model = $this->_model;
		return $this->getLibrary('Query')->update($model::$tableName)->where(array($model::$PK => $this->_data[$model::$PK]))->set($this->_data)->go();
		*/
	}
	
	public function delete() {
		return $this->__model->find($this->__model->pk)->delete();
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