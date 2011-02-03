<?php

function photoUrl($entry) {
/*
	$queryString = substr(strstr($entry->link[1]->attributes()->href, '?'), 1);
	parse_str($queryString, $vars);
	echo $queryString . "\n\n";
	return SITE_URL . '/users/' . $vars['friendID'] . '/albums/' . $vars['albumID'] . '/photos/' . $vars['imageID'];
*/
	return true;
}

function linkHelper($item) {
	global $app;
	
	
	$item->mood = parseLinks($item->mood);
	$item->status = parseLinks($item->status);
	
	@$app->site->links->add($item);
	return $item;
}

function parseLinks($text) {
	return preg_replace_callback('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', 'aTag', $text);
}
function hasLinks($text) {
	return preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $text);
}
function aTag($url) {
	return '<a href="' . $url[0] . '">' . $url[0] . '</a>';
}