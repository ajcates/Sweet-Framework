<?
class App {

	public $libs;
	public $models;

	function lib($lib) {
		if(is_array($lib)) {
			return f_last(array_map(array($this, 'lib'), $lib));
		}
		return $this->libs->{SweetFramework::className($lib)} = SweetFramework::getClass('lib', $lib);
	}
	
	function model($model) {
		if(is_array($model)) {
			return f_last(array_map(array($this, 'model'), $model));
		}
		return $this->models->{SweetFramework::className($model)} = SweetFramework::getClass('model', $model);
	}
	
	function helper($helper) {
		if(is_array($helper)) {
			return f_last(array_map(array($this, 'helper'), $helper));
		}
		return SweetFramework::loadFileType('helper', $helper);
	}
}

/*
    See PHP doesn't always have to be ugly. This part of the framework is so nice
    I don't even want to put comments in it.

    Pretty much every fricking thing in the framework extends this. So I try and
    keep things as lite as possible.

    See the trick is the app class only uses static methods of the SweetFramework,
    so I can use right away.

    Another trick is the check to see if the argument is an array, and if it is, it
    will map the array to the currently called function. This lets you load up a
    bunch of models/libs/helpers with one call.
*/
