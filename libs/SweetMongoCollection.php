<?
class SweetMongoCollection extends App, MongoCollection {
	
	public $collection;

	function __construct() {
		$this->lib('Config');
		parent::__construct(
			$this->lib('SweetMongo')->selectDB($this->lib('Config')->get('mongo', 'database')),
			$this->collection
		);
	}
}