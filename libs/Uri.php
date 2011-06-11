<?php
class Uri extends App {

	public $uriArray;
	public $controller;
	public $contorllerFile;
	public $protocol;
	
	//new:
	public $domain;
	public $request;
	public $count = 0;
	public $contorller;

	function __construct() {
		/*
		@todo:
			X Make this library work with the new config set up.
			- Simplify out the class
				- Clean up the functions and remove the ones that im not useing
				- Clean up the classes varibles
				X Add in a callRoute() function that essenitally calls up the correct controller
				X Maybe rewrite the loadUrl function to make it more modular
			- Make it clearer to as what is happening in this code
			X Don't make it so coupled with $_SERVER['QUERY']
		*/
		//[0] => helldsdfs34&what=4
		
		D::log($_SERVER, 'SERVER');
		if(array_key_exists('HTTP_HOST', $_SERVER)) {
			$this->request = $_SERVER['QUERY_STRING'];
			//[HTTP_HOST] => localhost
			$this->domain = $_SERVER['HTTP_HOST'];
			//http or https?
			$this->protocol = strtolower(strstr($_SERVER['SERVER_PROTOCOL'], '/', true));;
	
			
			if(!defined('URL')) {
				if($this->lib('Config')->get('site', 'prettyUrls')) {
					$folder = $_SERVER['REQUEST_URI'];
					if($this->request) {
						define('URL', $this->protocol . '://' . $this->domain . substr($folder, 0, -strlen($this->request)) );
					} else {
						define('URL', $this->protocol . '://' . $this->domain . $folder );
					}
					define('SITE_URL', URL);
				} else {
					$folder = strstr($_SERVER['REQUEST_URI'] .'?', '?', true);
					define('URL', $this->protocol . '://' . $this->domain . $folder );
					define('SITE_URL', URL . '?');
				}
			}
			
			D::log(SITE_URL . $this->request, 'URI Library Loaded');
			D::log(SITE_URL, 'SITE_URL');
			D::log($this->request, 'Request');
		}
		//$this->contorllerFile = $this->lib('Config')->get('site', 'mainController');
	}
	/*
	Calls a specfic route. As in you type in a relative url inside your application and the framework will fire up the controller and call its function and everything for you.
	Watch out it actaully Echos out the content of the controller that is called.
	*/
	function callRoute($request=null, $controller=null) {
		if(isset($request)) {
			$this->request = $request;
		}
		echo f_call($this->loadController());
	}
	
	function getRequest() {
		return $this->request;
	}
	
	/* Leagcy function */
	function getPart($index) {
		return isset($this->uriArray[$index]) ? $this->uriArray[$index] : null;
	}
	/*
	returns part of the url. by default they are seperated by "/" but can also be split by a regular expression.
	so for example:
	
	Uri: /hello/what/you
	
	echo $this->libs->Uri->get(0);
	"hello"
	echo $this->libs->Uri->get(3);
	"you"
	*/
	function get($index) {
		return rawurldecode(f_first((array)$this->rawGet($index)));
	}
	
	/* If you don't like you crap pre url decoded */
	function rawGet($index) {
		return array_key_exists($index, $this->uriArray) ? $this->uriArray[$index] : null;
	}
	/* returns the entire array of uri parts */
	function getArray() {
		return $this->uriArray;
	}
	
	/* Takes you to a differnent Url immedaditily. Shuts down the framework and everything. */
	function redirect($uri = '', $http_response_code = 302) {
		if(substr($uri, 0, 7) != 'http://') {
			//@todo fix this so it works with https
/*
			$this->callRoute($uri);
			exit;
*/
			if($uri == '/') {
				$uri = SITE_URL;	
			} else {
				$uri = SITE_URL . $uri;
			}
		}
		//@todo make this be set off with the debug switch. and if debugging is on it should show a link to the page it would have forwarded to.
		if(headers_sent()) {
			D::show('Headers already sent.', B::a(array('href' => $uri), $uri));
			SweetFramework::end(true);
		} else {
			header('Location: ' . $uri, TRUE, $http_response_code);
			/* @todo you should call an app end event here.*/
			SweetFramework::end(true);
		}	
	}
	
	/*
	Loads a controller. HOLY FLIPPING SMOKES! yes this thing loads a controller. Just type in the file name like you do when you load libraries, helpers or other things in Sweet-Framework.
	So for example:
	
	$this->libs->Uri->request = '';
	$output = $this->libs->Uri->loadController('admin/main');
	
	echo $output;
	//<!DOCTYPE>…(the rest out the html for the main admin page)…
	
	That would echo out the index page of the Admin controller.
	
	This function uses the current request to detrime what controller function it needs to call.
	This is a really low level framework uri routing feature that not many will need to use.

    @todo
    - Make this thing way way way smaller and modular, maybe split it into 2, loadUrl and loadController
	*/
	
	function loadController($controller=null, $request=null) {
		if(isset($controller)) {
			$this->contorllerFile = $controller;
		}
		if(isset($request)) {
			$this->request = $request;
		}
		if(empty($this->contorllerFile)) {
			$this->contorllerFile = $this->lib('Config')->get('site', 'mainController');
		}
		
		$class = SweetFramework::className($this->contorllerFile);
		D::log($class, 'Controller Loaded');
		
		if(!SweetFramework::loadFileType('controller', $this->contorllerFile)) {
			D::error('No Controller Found');
		}
		if(!empty($class::$urlPattern)) {
			$page = $this->loadUrl($class::$urlPattern);
		} else {
			$page = $this->loadUrl(array());
		}
		
		D::log($this->count, 'COUNT');
		D::log($page, 'page');
		
		if(is_array(f_last($page))) {
			if(is_array( f_first(f_last($page)) )) {
				$this->request = f_first($page);
				D::log($this->request, 'Request Reduced');
				if(method_exists($class, 'enRoute')) {
                    //@todo document this feature.
					$class::enRoute();
				}
				return $this->loadController(f_first(f_first(f_last($page))) );
			}
			$page[$this->count] = f_first(f_last($page));
		}
		
		$fpage = f_first($page);
		$this->controller = new $class();
		if(empty($fpage)) {
			return f_callable(array($this->controller, D::log('index', 'Controller Function')));
		} else {
			if(method_exists($class, $fpage)) {
				return f_callable(array($this->controller, D::log($fpage, 'Controller Function')));
			}
		}
		
		//D::show($class, 'controller');
		if(method_exists($class, '__DudeWheresMyCar')) {
			return f_callable(array(
				$this->controller,
				'__DudeWheresMyCar'
			));
		}
		//@todo find a way to check for __DudeWheresMyCar functions higher up the controller tree.
		
		return function() {
			header('HTTP/1.0 404 Not Found');
			echo '<h1>404 error</h1>'; //todo check for some sort of custom 404…
			return false;
		};
	}
	
	private function loadUrl($regexs=array()) {
		$this->uriArray = null;
		if(!empty($regexs)) {
			$this->uriArray = $this->regexArray($regexs);
			$pop = true;
		}
		if(empty($this->uriArray)) {
			$this->uriArray = explode('/', $this->request);
		}
		return $this->uriArray;
	}
	
	private function regexArray($regexs) {
		$matches = array();
		foreach($regexs as $regex => $func) {
			preg_match_all($regex, $this->request, $matches);
			if(f_first($matches)) {
				D::log($regex, 'regex');
				return f_push(
					array($func),
					array_map('f_first', f_rest($matches))
				);
			}
		}
		return false;
	}
	
	static function buildHeaders($headers) {
		return join(
			"\n",
			f_keyMap(
				function($v, $k) {
					return $k . ': ' . $v;
				},
				$headers
			)
		);
	}


}
?>
