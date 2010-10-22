<?
class CISupport {

	function __construct() {
		if(!defined('CI_SUPPORT')) {
		
			define('FILE_READ_MODE', 0644); 
			define('FILE_WRITE_MODE', 0666); 
			define('DIR_READ_MODE', 0755); 
			define('DIR_WRITE_MODE', 0777); 
			
			/* 
			|-------------------------------------------------------------------------- 
			| File Stream Modes 
			|-------------------------------------------------------------------------- 
			| 
			| These modes are used when working with fopen()/popen() 
			| 
			*/ 
			
			define('FOPEN_READ',                             'rb'); 
			define('FOPEN_READ_WRITE',                        'r+b'); 
			define('FOPEN_WRITE_CREATE_DESTRUCTIVE',         'wb'); // truncates existing file data, use with care 
			define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',     'w+b'); // truncates existing file data, use with care 
			define('FOPEN_WRITE_CREATE',                     'ab'); 
			define('FOPEN_READ_WRITE_CREATE',                 'a+b'); 
			define('FOPEN_WRITE_CREATE_STRICT',             'xb'); 
			define('FOPEN_READ_WRITE_CREATE_STRICT',        'x+b'); 

			define('CI_SUPPORT', true);
		}
	}
}

if(!function_exists('is_really_writable')) {
	function is_really_writable($file) {	
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE) {
			return is_writable($file);
		}
	
		// For windows servers and safe_mode "on" installations we'll actually
		// write a file then read it.  Bah...
		if (is_dir($file)) {
			$file = rtrim($file, '/').'/'.md5(rand(1,100));
	
			if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
				return FALSE;
			}
			
			fclose($fp);
			@chmod($file, DIR_WRITE_MODE);
			@unlink($file);
			return TRUE;
		} elseif (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
			return FALSE;
		}
		
		fclose($fp);
		return TRUE;
	}

}