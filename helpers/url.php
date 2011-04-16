<?
function site_url($uri = '') {
	return SITE_URL . $uri;
}

function redirect($uri = '', $method = 'location', $http_response_code = 302) {
	switch($method) {
		case 'refresh' : header("Refresh:0;url=".site_url($uri));
			break;
		default	:
			if(substr($uri, 0, 7) != 'http://') {
				$uri = site_url($uri);
			}
//@todo make this be set off with the debug switch. and if debugging is on it should show a link to the page it would have forwarded to.
	 		header("Location: " . $uri, TRUE, $http_response_code);
			break;
	}
	/* @todo you should call an app end event here.*/
	SweetFramework::end(true);
	//exit;
}

function addLinks($text) {
	return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $text);
}

function baseUrl($url) {
	return preg_replace('/(http:\/\/.*?)\/.*/i', '$1', $url);
}

function parseQuery($queryString) {
	$returnArray = array();
	parse_str($queryString, $returnArray);
	return $returnArray;
}