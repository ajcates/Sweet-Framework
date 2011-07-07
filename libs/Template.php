<?

class M {
	//short cuts for model access in templates. No one is really supposed to know about this.
	public static function __callStatic($name, $arguments=array()) {
		return SweetFramework::getClass('model', $name);
	}
}

class B {
	//get ya blocks!
	
	public static function get($reallyHopeNoOneNamesThereVaribleThis, $values=array()) {
		extract((array) $values);
		ob_start();
		include(LOC . '/sweet-framework/blocks/' . $reallyHopeNoOneNamesThereVaribleThis . '.php' );
		/*
		f_first
		if(is_array($values[0])) {
			$attributes 
		}
		'<' . $tagName . '>'
		*/
		return ob_get_clean();
	}
	
	public static function __callStatic($tagName, $values=array()) {
		
		if(isset($values[0]) && is_array($values[0])) {
			//D::log($values[0], '0 values');
			$attributes = ' ' . join(' ', f_keyMap(
				function($v, $k) {
					if(isset($v)) {
            return $k . '="' . (
              is_array($v)
                ? join(', ', $v)
                : (
                  is_bool($v)
                    ? var_export($v, true)
                    : $v
                )
            ) . '"';
					}
				},
				$values[0]
			));
			$childern = f_rest($values);
		} else {
			$attributes = '';
			$childern =& $values;
		}
		if(empty($childern) && $tagName != 'script') {
			return '<' . $tagName . $attributes . '/>';
		} else {
			return '<' . $tagName . $attributes . '>' . join((array)$childern) . '</' . $tagName . '>';
		}
	}
}

class Template extends App {
	
	public $data = array();
	
	public function __construct() {}
	
	public function __set($name, $value) {
		return ($this->data[$name] = $value);
	}
	
	public function __get($name) {
		return $this->data[$name];
	}
	
	public function set($data) {
		$this->data = array_merge($this->data, $data);
		return $this;
	}
	
	public function render($fileNameThatNoOneBetterUse, $data=null) {
		if(isset($data)) {
			extract($data);
			include(T::$loc . '/templates/' . $fileNameThatNoOneBetterUse . '.php');
		} else {
			extract($this->data);
			include(T::$loc . '/templates/' . $fileNameThatNoOneBetterUse . '.php');
			$this->data = array();
		}
	}
	
	public function get($file, $data=array()) {
		ob_start();
		$this->render($file, $data);
		return ob_get_clean();
	}
}
