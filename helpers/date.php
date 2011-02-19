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

/*
@author - ghotinet
	http://www.php.net/manual/en/function.date.php#101379
@example:
$start = strtotime("1 January 2010");
$end = strtotime("13 December 2010");

// Add as many holidays as desired.
$holidays = array();
$holidays[] = "4 July 2010";            // Falls on a Sunday; doesn't affect count
$holidays[] = "6 September 2010";        // Falls on a Monday; reduces count by one

echo networkdays($start, $end, $holidays);    // Returns 246
*/
function networkdays($s, $e, $holidays = array()) {
    // If the start and end dates are given in the wrong order, flip them.    
    if ($s > $e)
        return networkdays($e, $s, $holidays);

    // Find the ISO-8601 day of the week for the two dates.
    $sd = date("N", $s);
    $ed = date("N", $e);

    // Find the number of weeks between the dates.
    $w = floor(($e - $s)/(86400*7));    # Divide the difference in the two times by seven days to get the number of weeks.
    if ($ed >= $sd) { $w--; }        # If the end date falls on the same day of the week or a later day of the week than the start date, subtract a week.

    // Calculate net working days.
    $nwd = max(6 - $sd, 0);    # If the start day is Saturday or Sunday, add zero, otherewise add six minus the weekday number.
    $nwd += min($ed, 5);    # If the end day is Saturday or Sunday, add five, otherwise add the weekday number.
    $nwd += $w * 5;        # Add five days for each week in between.

    // Iterate through the array of holidays. For each holiday between the start and end dates that isn't a Saturday or a Sunday, remove one day.
    foreach ($holidays as $h) {
        $h = strtotime($h);
        if ($h > $s && $h < $e && date("N", $h) < 6)
            $nwd--;
    }

    return $nwd;
}
