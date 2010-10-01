<?
class App {

	public $libs;
	public $models;

	function __construct() {}
	
	function lib($lib) {
		if(is_array($lib)) {
			return f_last(array_map(f_callable(array($this, 'lib')), $lib));
		}
		return $this->libs->{SweetFramework::className($lib)} = SweetFramework::getClass('lib', $lib);
	}
	
	function model($model) {
		if(is_array($model)) {
			return f_last(array_map(f_callable(array($this, 'model')), $model));
		}
		return $this->models->{SweetFramework::className($model)} = SweetFramework::getClass('model', $model);
	}
	
	function helper($helper) {
		if(is_array($helper)) {
			return f_last(array_map(f_callable(array($this, 'helper')), $helper));
		}
		return SweetFramework::loadFileType('helper', $helper);
	}
}