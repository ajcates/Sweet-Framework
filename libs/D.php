<?
//debuging class… uber helpful.
/*
@todo write out timing functions in order to find slow spots in code.
@todo write out a pesudo languge for transfering debugging messages to javascript(json?)

@todo
	Find a way to support some sort of json logging functionality… with support to pushing it to js console of some sort.
	Maybe allow the js console to pause scripts.	
	Make the js console open up with a bookmarklet.
	Make the design look kinda like if webinspector and tweetie had a kid.
	
	A counter function so i can count how many times something is being called
	
*/
class Timer { 
	public $total; 
	public $time; 
	
	public function __construct() { 
		//$this->total = $this->time = (float)microtime(true); 
		$this->reset();
	} 
	
	public function clock() { 
		return -$this->time + ($this->time = (microtime(true))); 
	}
	
	public function elapsed() { 
		return (microtime(true)) - $this->total; 
	} 
	
	public function reset() { 
		$this->total=$this->time=(microtime(true));
	} 
}

class D {
	protected static $handle;
	protected static $growlr;
	
	protected static $config = array(
		'debug' => true,
		'warnings' => true,
		'logfile' => '../logs/main.log',
		'growl' => array(
			'host' => 'localhost',
			'password' => 'aldo20'
		)
	);
	protected static $timers;
	
	static function initialize($config) {
		self::$config = array_merge(self::$config, (array)$config);
	}

	static function log($var=null, $label=null) {
		if(self::$config['debug']) {
			if(!isset(self::$handle)) {
				self::$handle = fopen(self::$config['logfile'], 'w+');
			}
			if(!isset($label)) {
				$label = '';
    		} else {
    			$label .= ': ';
    		}
    		fwrite(self::$handle, "\n" . $label . stripcslashes(print_r($var, true)) . "\n\n");
		}
		return $var;
	}
	
	static function time($timer, $label='') {
		if(!isset(self::$timers)) {
			self::$timers = array();
		}
		if(!isset(self::$timers[$timer])) {
			self::$timers[$timer] = new Timer();
			return D::log(0, $label . ' | ' . $timer . ' Started ');
		}
		self::$timers[$timer]->clock();
	    return $label . ' | ' .D::log(round(self::$timers[$timer]->elapsed(), 4) . 's', $timer . ' | ' . $label);
	}

	static function growl($var, $label=null) {
		
		if(self::$config['debug']) {
		
            if(!isset(self::$growlr)) {
            	try {
            		require_once('growl.php');
					self::$growlr = new Growl(self::$config['growl']['host'], self::$config['growl']['password']);
					self::$growlr->addNotification('log');
					self::$growlr->register();	
					self::log(self::$growlr);
            	} catch (Exception $e) {
            		self::warn($a, 'growl failed');
            		self::$growlr = false;
            	}
            }
            D::log($var, $label);
            if(self::$growlr) {
            	try {
            		self::$growlr->notify('log', $label, print_r($var, true));	
            	} catch (Exception $e) {
            		self::warn($a, 'growl failed');
            		self::$growlr = false;
            	}
            	
            }
		}
		return $var;
	}
	static function show($var, $label='') {
		echo "\n";
		if(!empty($label)) {
			echo $label . ":\n";
		}
		//@todo make this only go if the debugging is enabled.
		print_r(self::log($var, $label));
		echo "\n";
		return $var;
	}
	static function report($context, $error) {
		//$temp = "\n	There has been an error. Context: " . $context . "\n	" . "Error: " . $error . "\n	Back trace:" . print_r(stacktrace(), true);
		//self::log($temp, 'Fatal and kinda expected error');
		throw new Exception($context . ' In… ' . $error);
		exit($temp);
	}

	static function error($error='There has been an error') {
		throw new Exception($error);
		//exit(self::log("\n	There has been an error. Message: \n" . $error . "\n	Back trace:" . print_r(stacktrace(), true), 'Fatal and expected error'));
	}
	
	static function warn($warning) {
		//D::show('WHAT');
		if(self::$config['warnings']) {
			D::show(D::stack(), $warning);
		}		
	}

	static function stackTrace() {
		D::log(stacktrace(), 'Stack Trace');
	}
	
	static function stack($label='Label') {
		return D::log(
			"\n" . join(
				"\n",
				array_reverse(array_map(
					function($v) {
						return ' ' . $v['function'] . '();' . "\n    →" . substr(substr(@$v['file'], strlen(LOC)), 1, -4) . ' | line:' . @$v['line'];
					},
					debug_backtrace()
				))
			),
			$label . ' - Stack Trace'
		);
	}

	static function debug() {
		self::log(debug_backtrace());
	}

	static function close() {
        if(self::$config['debug']) {
            fclose(self::$handle);
        }
	}
}