<?

function color_shade($color, $shade) {
  /*
    Orignally written by hajamie@home 
    http://www.php.net/manual/en/function.hexdec.php#58104
  */

  $r = hexdec(substr($color,0,2));
  $g = hexdec(substr($color,2,2));
  $b = hexdec(substr($color,4,2));
  $sum = ($r + $g + $b);
  $x = (($shade * 3) - $sum) / $sum;

  if ($x >= 0) {
    $x = $x + 1;
  } else {
    $x = 1 + $x;
  }
  return dechex(intval($x * $r)) . dechex(intval($x * $g)) . dechex(intval($x * $b));
}

function color_opposite($color) {
  /*
    Orignally written by ?
    http://www.php.net/manual/en/function.hexdec.php#57622
  */
  $r = dechex(255 - hexdec(substr($color,0,2)));
  $r = (strlen($r) > 1) ? $r : '0'.$r;
  $g = dechex(255 - hexdec(substr($color,2,2)));
  $g = (strlen($g) > 1) ? $g : '0'.$g;
  $b = dechex(255 - hexdec(substr($color,4,2)));
  $b = (strlen($b) > 1) ? $b : '0'.$b;
  return $r.$g.$b;
}


function color_combine($color1, $color2, $ratio = .5) {
  /*
    Orignally written by maddddidley at yahoo dot com 
    http://www.php.net/manual/en/function.hexdec.php#80646
  */
  $cR1 = 1 - $ratio;
  $cR2 = $ratio;

  
  $r1 = hexdec(substr($color1, 0, 2));
  $g1 = hexdec(substr($color1, 2, 2));
  $b1 = hexdec(substr($color1, 4, 2)); 

  $r2 = hexdec(substr($color2, 0, 2));
  $g2 = hexdec(substr($color2, 2, 2));
  $b2 = hexdec(substr($color2, 4, 2));

  $r3 = dechex( ceil(
    $r1 * $cR1 + $r2 * $cR2
  ) );
  $g3 = dechex( ceil(
    $g1 * $cR1 + $g2 * $cR2
  ) );
  $b3 = dechex( ceil(
    $b1 * $cR1 + $b2 * $cR2
  ) );

  //return rgbhex();
  if(strlen($r3) < 2) {
    $r3 = 0 . $r3;
  }
  if(strlen($g3) < 2) {
    $g3 = 0 . $g3;
  }
  if(strlen($b3) < 2) {
    $b3 = 0 . $b3;
  }
  return $r3 . $g3 . $b3;
}
