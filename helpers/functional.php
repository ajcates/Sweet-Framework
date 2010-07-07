<?
/*
@maybe make an optimized functional helper.

I'd like to welcome you to a higher order of programing.
*/
function f_call($func, $args=array()) {
	//calls a function anonymously
	return call_user_func_array($func, (array)$args);
}

function f_callable($func) {
	//helps turn methods into real functions.
	if(!is_callable($func)) {
        throw new Exception("Hey that's not a function!");
    } 
    if(!is_array($func)) {
    	return $func;
    }
	return function() use ($func) {  
		return call_user_func_array($func, func_get_args());
	};
}

function f_function($v) {
	return function() use($v) { return $v; };
}

function f_first($in) {
	//gets the first item of an array
	if(empty($in) || !is_array($in)) {
		return null;
	} else {
		return array_shift($in);
	}
}

function f_last($in) {
	if(empty($in)) {
		return null;
	} else {
		return array_pop($in);
	}
}

function f_chop($in) {
	//returns an array with all the elements except the last
    if(!empty($in)) {
    	array_pop($in);
    }
    return $in;
}

function f_rest($in) {
	//returns an array with all the elements except the first
    if(!empty($in)) {
    	array_shift($in);
    }
    return $in;
}

function f_construct($first, $rest) {
	//appends $first onto $rest
	if(empty($rest)) {
		return array($first);
	}
	array_unshift($rest, $first);  
	return $rest;
}

function f_push($i, $in) {
	if(empty($in)) {
		return array($i);
	}
	array_push($in, $i);
	return $in;
}

function f_map($transformer, $in) {
	//call $transformer with each item in $in
/*
	if(!empty($in)) {
		return f_construct(
			$transformer(
				f_first($in)
			),
			f_map(
				$transformer,
				f_rest($in)
			)
		);
	} else {
		return array();
	}
*/
	return array_map($transformer, $in);
}

function f_keyMap($transformer, $in, $keys=null) {
	if(!empty($in)) {
		if(!isset($keys)) {
			$keys = array_keys($in);
		}
		return f_construct(
			$transformer(
				f_first($in),
				f_first($keys)
			),
			f_keyMap(
				$transformer,
				f_rest($in),
				f_rest($keys)
			)
		);
	} else {
		return array();
	}
}

function f_kkeyMap($transformer, $in, $keys=null) {
	if(!isset($keys)) {
		return f_kkeyMap($transformer, $in, array_keys($in));
	} else {
		if(!empty($in)) {
			$transformer(
				f_first($in),
				f_first($keys)
			) + f_kkeyMap(
				$transformer,
				f_rest($in),
				f_rest($keys)
			);
		} else {
			return array();
		}
	}
}



function f_filter($tester, $in) {
	if(!empty($in)) {
		if($tester(f_first($in))) {
			return f_construct(
				f_first($in),
				f_filter(
					$tester,
					f_rest($in)
				)
			);
		} else {
			return f_filter(
				$tester,
				f_rest($in)
			);
		}
	} else {
		return array();
	}
}
function f_keyFilter($tester, $in, $keys=null) {
	//??
	if(!isset($keys)) {
		$keys = array_keys($in);
	}
	if(!empty($in)) {
		if($tester(f_first($keys))) {
			return f_construct(
				f_first($in),
				f_keyFilter(
					$tester,
					f_rest($in),
					f_rest($keys)
				)
			);
		} else {
			return f_keyFilter(
				$tester,
				f_rest($in),
				f_rest($keys)
			);
		}
	} else {
		return array();
	}
}

function f_reduce($combiner, $in, $identity=null) {
	//adds the first item with the rest, then does the same with the rest.
	if(!empty($in)) {
		return $combiner(
			f_first($in),
			f_reduce(
				$combiner,
				f_rest($in),
				$identity
			)
		);
	} else {
		return $identity;
	}
}

function f_fill($count) {
	return range(0, $count);
}

function f_sort($func, $in) {
	uasort($in, $func);
	return $in;
}
		
function f_qsort($in) {
	if(!empty($in)) {
		return array_merge(
			f_qsort(
				f_filter(
					function($a) use($in) {
						return $a < f_first($in);
					},
					f_rest($in)
				)
			),
			array(
				f_first($in)
			),
			f_qsort(
				f_filter(
					function($a) use($in) {
						return $a >= f_first($in);
					},
					f_rest($in)
				)
			)
		);
	} else {
		return array();
	}
}


function f_flatten($in) {
	return f_reduce(
		function($a, $b) {
			if(is_array($a)) {
				$a = f_flatten($a);
			}
			return array_merge((array)$a, (array)$b);
		},
		$in
	);
}
//array_reduce(array input, callback function [, int initial])

function f_untree($in) {
	return array_reduce(
		$in,
		function($a, $b) {
			/*
			if(is_array($a)) {
				$a = f_flatten2($a);
			}
			*/
			return array_merge((array)$a, (array)$b);
		}
	);
}


/*
function export() {
	$fields = array_keys($this->getFields());
	return array_map(
		function($v) use($fields) {
			return (object) f_flatten(
				array_map(
					function($d) use($v) {
						return array($d => $v->$d);
					},
					$fields
				)
			);
		},
		$this->all()
	);
}
*/
















