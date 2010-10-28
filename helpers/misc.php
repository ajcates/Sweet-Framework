<?php
//misc functions n stuff that help me
define('EMPTY_STRING', '');


/*
function chain($baseItem, $items=array()) {
	return eval('return ' . join('->', f_construct('$baseItem', (array)$items)) . ';');
}
*/

//print_r( get_tree_arr3(LOC . '/app'), true)
function getTreeDir( $dr = '', $tree = array() ) {
	foreach((array) glob($dr . '*') as $fl ) {
		$fl_nice = str_replace( dirname( $fl ).'/' , '' , $fl );
		if ( is_dir( $fl ) ) { //if there's a dir, go deeper
			$tree[ $fl_nice ] = getTreeDir( $fl . '*/'); 
		} else {
			$tree[] = $fl_nice ;
		}
    }
	return $tree;
}

function chain($baseItem, $items=array()) {
	if(!empty($items)) {
		return chain($baseItem->{f_first($items)}, f_rest($items));	
	} else {
		return $baseItem;
	}
}

function multiKey($item, $keys, $default=null) {
	foreach($keys as $key) {		
		if(array_key_exists($key, $item) && !isEmpty(($trimmed = trim($item[$key])))) {
			return $trimmed;
		}
	}
	return $default;
}

function ifthereshow($test, $show, $else=null) {
	if(!empty($test)) {
		return $show;
	} else {
		return $else;
	}
}

function same($a, $b) {
	return ($a == $b);
}

function gravatar($email, $options=null) {
	return 'http://www.gravatar.com/avatar/' . md5(strtolower($email)) . '?' . http_build_query(isset($options) ? $options : array('s' => 50, 'd' => 'identicon', 'r' => 'g'));
}

function properJsonDecode($json) {
	//maybe if we check something on the left we can validate that the value on the right is actaully a value and not part of a string.
	$return = json_decode(D::log(preg_replace('@"(\w*)"\s*:\s*(-?\d{9,})\s*([,|\}])@', '"$1":"$2"$3', $json), 'raw json') );
	switch(json_last_error()) {
        case JSON_ERROR_DEPTH:
            $echo = ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_CTRL_CHAR:
            $echo = ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            $echo = ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_NONE:
            $echo = ' - No errors';
        break;
	}
	D::growl($echo, 'json error');
	
	
	return $return;
}


function notRetardedParse_str($str, $r=array()) {
	parse_str($str, $r);
	return $r;
}


/*
function sup($output) {
	$output = str_replace('®', '<sup>®</sup>', $output);
	$output = str_replace('®', '<sup>®</sup>', $output);
	$output = str_replace('™', '<sup>™</sup>', $output);
	$output = str_replace('™', '<sup>™</sup>', $output);
	
    return str_replace('™', '<sup>™</sup>', $output);
    array('™', '®', '&reg;', '&trade;')
   	
}
*/


function foxy_utf8_to_nce($utf = '') {
	//Orignally written by limalopex.eisfux.de - http://us2.php.net/manual/en/function.imagettftext.php#57416
	if(empty($utf)) {
		return($utf);
	}

	$max_count = 5; // flag-bits in $max_mark ( 1111 1000 == 5 times 1) 
	$max_mark = 248; // marker for a (theoretical ;-)) 5-byte-char and mask for a 4-byte-char; 

	$html = '';
	for($str_pos = 0; $str_pos < strlen($utf); $str_pos++) { 
	    $old_chr = $utf{$str_pos}; 
	    $old_val = ord( $utf{$str_pos} ); 
	    $new_val = 0; 
	
	    $utf8_marker = 0; 
	
	    // skip non-utf-8-chars 
	    if( $old_val > 127 ) { 
			$mark = $max_mark; 
			for($byte_ctr = $max_count; $byte_ctr > 2; $byte_ctr--) { 
		        // actual byte is utf-8-marker? 
				if( ( $old_val & $mark  ) == ( ($mark << 1) & 255 ) ) { 
					$utf8_marker = $byte_ctr - 1; 
					break; 
				} 
				$mark = ($mark << 1) & 255; 
			} 
		} 

    // marker found: collect following bytes 
    if($utf8_marker > 1 and isset( $utf{$str_pos + 1} ) ) { 
      $str_off = 0; 
      $new_val = $old_val & (127 >> $utf8_marker); 
      for($byte_ctr = $utf8_marker; $byte_ctr > 1; $byte_ctr--) { 

        // check if following chars are UTF8 additional data blocks 
        // UTF8 and ord() > 127 
        if( (ord($utf{$str_pos + 1}) & 192) == 128 ) { 
          $new_val = $new_val << 6; 
          $str_off++; 
          // no need for Addition, bitwise OR is sufficient 
          // 63: more UTF8-bytes; 0011 1111 
          $new_val = $new_val | ( ord( $utf{$str_pos + $str_off} ) & 63 ); 
        } 
        // no UTF8, but ord() > 127 
        // nevertheless convert first char to NCE 
        else { 
          $new_val = $old_val; 
        } 
      } 
      // build NCE-Code 
      $html .= '&#'.$new_val.';'; 
      // Skip additional UTF-8-Bytes 
      $str_pos = $str_pos + $str_off; 
    } else { 
      $html .= chr($old_val); 
      $new_val = $old_val; 
    } 
  } 
  return($html); 
}
function isEmpty($var) {
	return empty($var);
}

function matchAll($pattern, $subject) {
	$matches = array();
	if(preg_match_all($pattern, $subject, $matches)) {
		return $matches;
	}
	return null;
}

function match($pattern, $subject) {
	$matches = array();
	if(preg_match($pattern, $subject, $matches)) {		
		return $matches;
	}
	return null;
}

function stacktrace() {
	return f_map(
		function($code) {
			return 'Function: ' . @$code['function'] . ' File: ' . @$code['file'] . ' Line: ' . @$code['line'] . "\n";
		},
		debug_backtrace()
	);
}

function objToArray($obj) {
	$dataArray = array();
	foreach($obj as $k => $v) {
		$dataArray[$k] = $v;
	}
	return $dataArray;
}
function arrayToObj($array) {
	$obj = new stdClass();
	foreach($array as $k => $v) {
		if(!empty($k)) {
			$obj->$k = $v;
		}
	}
	return $obj;
}

function nothing($arg) {
	return $arg;
}

function extendFunction($callback, $function) {
	//sadly only works with varible functions.
	return $callback($function);
}

function notRetardedSort($sort, $type=SORT_REGULAR) {
	sort($sort, $type);
	return $sort;
}
function notRetardedKSort($sort, $type=SORT_REGULAR) {
	ksort($sort, $type);
	return $sort;
}

function sortBy($objects_array, $p) {
	uasort(
		$objects_array,
		function($a, $b) use($p) {
			if($a->$p == $b->$p) {
				return 0;
			} else if($a->$p > $b->$p) {
				return 1;
			} else {
				return -1;
			}
		}
	);
	return $objects_array;
}

function arraySortBy($array, $p) {
	uasort(
		$array,
		function($a, $b) use($p) {
			if($a[$p] == $b[$p]) {
				return 0;
			} else if($a[$p] > $b[$p]) {
				return 1;
			} else {
				return -1;
			}
		}
	);
	return $array;
}