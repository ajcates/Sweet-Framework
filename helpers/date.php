<?

/*
@author - icio
	http://www.kirupa.com/forum/showthread.php?t=256343
*/

function getTimeDifference($time1=0, $time2=0) {
	$dif = max($time1, $time2) - min($time1, $time2);
    return array(
		'years' => floor($dif/31536000),
		'weeks' => floor(($dif%31536000)/604800),
		'days' => floor((($dif%31536000)%604800)/86400),
		'hours' => floor(($dif%86400)/3600),
		'minutes' => floor(($dif%3600)/60),
		'seconds' => $dif%60
	);
} 