<?
class StaticProxy {

	static protected $_proxyClass;

	static public function __callStatic($name, $args) {
		//D::show(static::$_proxyClass, 'proxy class');
		return call_user_func_array(array(static::$_proxyClass, $name), $args);
	}
	
	public function __call($name, $args) {
		return call_user_func_array(array(static::$_proxyClass, $name), $args);
	}
	
	public function __get($name) {
		return static::$_proxyClass->$name;
	}
	
	public function __set($name, $v) {
		return static::$_proxyClass->$name = $v;
	}
	
	static public function _initProxy($obj) {
		static::$_proxyClass = $obj;
	}
}