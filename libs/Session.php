<?
/*
@todo
Make this code really fast, it basicly is ran on every page no matter what. 
 */
 
class Session extends App {
	
	private $_id = false;
	private $_valid = false;
	private $_changed = array();
	private $_new = array();
	private $_data = array();
	private $_flashRemove = array();
	private $_flashAdd = array();
	private $_flash = array();
	
	function __construct() {
		$this->lib(array('Config', 'databases/Query'));
		
		//$this->libs->Config->get('Session', 'sessionTableName')
		
		//$this->libs->Config->get('Session', 'sessionDataTableName')
		D::log('Session is starting');
		$this->start();
		
		SweetEvent::bind('SweetFrameworkEnd', array($this, 'save'));
	}
	
	function start() {
		return $this->checkCookie();
	}
	
	function loadData($data) {
		foreach($data as $d) {
			if($d->flash) {
				$this->_flash[$d->name] = unserialize($d->value);
				$this->_flashRemove[] = $d->name;
			} else {
				$this->_data[$d->name] = unserialize($d->value);
			}
		}
	}
	
	function checkCookie() {
		if($this->_valid === true) {
			return true;
		}
		
		$cookieName = $this->libs->Config->get('Session', 'cookieName');
		if(isset($_COOKIE[$cookieName])) {
			//D::log($_COOKIE[$cookieName], 'Cookie Set');
			$cookie = explode('_', $_COOKIE[$cookieName]);
			$row = f_first($this->libs->Query->select('*')->from($this->libs->Config->get('Session', 'tableName'))->where(array('id' => f_first($cookie)))->results());
			if(!empty($row) && $this->encryptCheckString($row->uid) === f_last($cookie)) {
				if($this->encryptCheckString($this->getCheckString($row->uid)) === $row->checkString) {
					$this->_valid = true;
					$this->_id = $row->id;
					$this->loadData($this->libs->Query->select('*')->from($this->libs->Config->get('Session', 'dataTableName'))->where(array('session' => $this->_id))->results());
					D::log($this->_data, 'cookie is good. heres some data:');
					return true;
				}
			}
		}
		$uid = $this->getUid();
		if(!$this->saveCookie($this->generateCookie($this->getCheckString($uid), $uid)) ) {
			D::warn('Cookie Failed');
			return false;
		} else {
			return true;
		}
	}
	
	function getUid() {
		return uniqid(mt_rand(), true);
	}
	
	function getCheckString($uid) {
		//return hash_hmac($this->libs->Config->get('Session', 'hashFunction'), $checkString, $this->libs->Config->get('Session', 'cookieSecret'));
		return $uid . '_' . join('', $this->libs->Config->get('Session', 'use'));
	}
	function encryptCheckString($checkString) {
		return hash_hmac($this->libs->Config->get('Session', 'hashFunction'), $checkString, $this->libs->Config->get('Session', 'cookieSecret'));
	}
	
	function generateCookie($checkString, $uid) {
		//@todo remove the mssql depencdy here.
		return f_first(f_first( $this->libs->Query->insert(array(
			'checkString' => $this->encryptCheckString($checkString),
			'created' => time(),
			'uid' => $uid
		))->into($this->libs->Config->get('Session', 'tableName'))->go()->getDriver()->query('SELECT max(@@IDENTITY) AS \'id\' FROM ' . $this->libs->Config->get('Session', 'tableName'), 'assoc') )) . '_' . $this->encryptCheckString($uid);
	}
	
	function saveCookie($info) {
		//@todo pull out into a cookie library.
		//setcookie($cookieName, $encyptedCookieString, $expire, $path, $domain, $secure);
		D::log($info, 'Setting cookie');
		return setcookie($this->libs->Config->get('Session', 'cookieName'), $info, time() + $this->libs->Config->get('Session', 'timeout'), '/', null, $this->libs->Config->get('Session', 'sslCookies'));
	}
	
	function data($key, $value=null) {
		//if($this->checkCookie()) {
			if(!isset($value)) {
				return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
			}
			if(is_array($key)) {
				foreach($key as $k => $v) {
					$this->data($k, $v);
				}
				return $this->data(f_last($key));
			}
			if(isset($this->_data[$key])) {
				$this->_changed[] = $key;
			} else {
				$this->_new[] = $key;
			}
			$this->_data[$key] = $value;
		//}
	}
	
	function flash($key, $value=null) {
		//if($this->checkCookie()) {
			if(!isset($value)) {
				return @$this->_flash[$key];
			}
			if(is_array($key)) {
				foreach($key as $k => $v) {
					$this->flash($k, $v);
				}
				return $this->flash(f_last($key));
			}
			$this->_flash[$key] = $value;
			$this->libs->Query->insert(array('name' => $key, 'value' => serialize($this->_flash[$key]), 'session' => $this->_id, 'flash' => 1))->into($this->libs->Config->get('Session', 'dataTableName'))->go();
		//}
	}
	
	function save() {
		D::log('saving session');
	//	if($this->checkCookie()) {
			D::log($this->_data, 'data');
			foreach($this->_changed as $key) {
				$this->libs->Query->update($this->libs->Config->get('Session', 'dataTableName'))->where(array('name' => $key, 'session' => $this->_id))->set(array('value' => serialize($this->_data[$key])))->go();
			}
			foreach($this->_new as $key) {
				$this->libs->Query->insert(array('name' => $key, 'value' => serialize($this->_data[$key]), 'session' => $this->_id))->into($this->libs->Config->get('Session', 'dataTableName'))->go();
			}
			if(!empty($this->_flashRemove)) {
				$this->libs->Query->delete()->where(array('name' => $this->_flashRemove, 'session' => $this->_id, 'flash' => 1))->from($this->libs->Config->get('Session', 'dataTableName'))->go();
			}
	//	}
	}
	
	function destroy() {
		setcookie ($this->libs->Config->get('Session', 'cookieName'), '', time() - 86400);
		$this->libs->Query->delete()->where(array('id' => $this->_id))->from($this->libs->Config->get('Session', 'tableName'))->go();
		$this->_changed = array();
		$this->_new = array();
		$this->_data = array();
		$this->_flashRemove = array();
		$this->_flashRemove = array();
		$this->_flashAdd = array();
		$this->_flash = array();
	}
}