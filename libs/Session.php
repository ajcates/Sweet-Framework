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
	private $_flashKeep = array();
	private $_flash = array();
	private $_config = array();
	
	function __construct() {
		$this->lib(array('databases/Query'));
		D::log('Session is starting');
		$this->_config = (object) Config::get('Session');//@todo make $this->_config an actual array.
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
				$this->_flashRemove[$d->name] = true;
			} else {
				$this->_data[$d->name] = unserialize($d->value);
			}
		}
	}
	
	function checkCookie() {
		if($this->_valid === true) {
			return true;
		}
		
		$cookieName = $this->_config->cookieName;
		if(isset($_COOKIE[$cookieName])) {
			//D::log($_COOKIE[$cookieName], 'Cookie Set');
			$cookie = explode('_', $_COOKIE[$cookieName]);
			$row = f_first($this->libs->Query->select('*')->from($this->_config->tableName)->where(array('id' => f_first($cookie)))->results());
			if(!empty($row) && $this->encryptCheckString($row->uid) === f_last($cookie)) {
				if($this->encryptCheckString($this->getCheckString($row->uid)) === $row->checkString) {
					$this->_valid = true;
					$this->_id = $row->id;
					$this->loadData($this->libs->Query->select('*')->from($this->_config->dataTableName)->where(array('session' => $this->_id))->results());
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
		return $uid . '_' . join('', $this->_config->checkString);
	}
	function encryptCheckString($checkString) {
		return hash_hmac($this->_config->hashFunction, $checkString, $this->_config->cookieSecret);
	}
	
	function generateCookie($checkString, $uid) {
		//@todo remove the mssql depencdy here.
		$this->libs->Query->insert(array(
			'checkString' => $this->encryptCheckString($checkString),
			'created' => time(),
			'uid' => $uid
		))->into($this->_config->tableName)->go();
		$lastId = $this->libs->Query->getLastInsert();
		return $lastId . '_' . $this->encryptCheckString($uid);
	}
	
	function saveCookie($info) {
		//@todo pull out into a cookie library.
		//setcookie($cookieName, $encyptedCookieString, $expire, $path, $domain, $secure);
		D::log($info, 'Setting cookie');
		return setcookie($this->_config->cookieName, $info, time() + $this->_config->timeout, '/', null, $this->_config->sslCookies);
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
				return isset($this->_flash[$key]) ? $this->_flash[$key] : null;
			}
			if(is_array($key)) {
				foreach($key as $k => $v) {
					$this->flash($k, $v);
				}
				return $this->flash(f_last($key));
			}
			$this->_flash[$key] = $value;
			$this->libs->Query->insert(array(
				'name' => $key,
				'value' => serialize($this->_flash[$key]),
				'session' => $this->_id,
				'flash' => 1
			))->into($this->_config->dataTableName)->go();
		//}
	}
	
	/**
	 * keepflash function.
	 * A function lets you keep flash data items for one more additional page load
	 * @access public
	 * @param mixed $key. (default: null)
	 * @return $this
	 */
	function keepflash($key=null) {
		if(is_array($key)) {
			array_map(array($this, __METHOD__), $key);
		} else {
			$this->_flashKeep[$key] = true;
		}
		return $this;
	}
	
	function keepAllFlash() {
		return $this->keepflash(array_keys($this->_flash));
	}
	
	function save() {
		D::log('saving session');
	//	if($this->checkCookie()) {
			foreach($this->_changed as $key) {
				$this->libs->Query->update($this->_config->dataTableName)->where(array('name' => $key, 'session' => $this->_id))->set(array('value' => serialize($this->_data[$key])))->go();
			}
			foreach($this->_new as $key) {
				$this->libs->Query->insert(array('name' => $key, 'value' => serialize($this->_data[$key]), 'session' => $this->_id))->into($this->_config->dataTableName)->go();
			}
			$flashRemove = array_diff_key($this->_flashRemove, $this->_flashKeep);
			if(!empty($flashRemove)) {
				$this->libs->Query->delete()->where(array('name' => array_keys($flashRemove), 'session' => $this->_id, 'flash' => 1))->from($this->_config->dataTableName)->go();
			}
	//	}
	}
	
	function destroy() {
		setcookie ($this->_config->cookieName, '', time() - 86400);
/*
		try {
			
		} catch(Exception $e) {
			D::log($e, 'Session destruction notice.');
		}
*/
		$this->libs->Query->delete()->where(array('session' => $this->_id))->from($this->_config->dataTableName)->go();
		$this->libs->Query->delete()->where(array('id' => $this->_id))->from($this->_config->tableName)->go();
		$this->_changed = array();
		$this->_new = array();
		$this->_data = array();
		$this->_flashRemove = array();
		$this->_flashRemove = array();
		$this->_flashAdd = array();
		$this->_flash = array();
	}
}