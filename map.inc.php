<?php
ini_set('memory_limit', '160M');
list($w, $h) = getimagesize('map.jpg');
$map = imagecreatefromjpeg( 'map.jpg' );
if($_REQUEST['col'])
    {
    $a = sscanf($_REQUEST['col'], '%2x%2x%2x');
    $dotColour = imagecolorallocate($map,$a[0],$a[1],$a[2]);
    }
elseif(isset($col))
    {
    $a = sscanf($col, '%2x%2x%2x');
    $dotColour = imagecolorallocate($map,$a[0],$a[1],$a[2]);
    }
else
    $dotColour = imagecolorallocate( $map, 200, 200, 200 );

//draw the dots
while($art=mysql_fetch_array($result))
    {
    if($art[0]=='' or $art[0]==':' or $art[0]=='0:0')
        continue;
    $latlon=explode(':',$art[0]);
    $posX = round(($latlon[1]+180)*$w/360);
    $posY = round((-$latlon[0]+90)*$h/180);
    imagefilledellipse( $map, $posX, $posY, 5, 5, $dotColour );
    }

// Finally, send our work off to the browser.
header("Content-type: image/jpeg");
header('Content-Disposition: inline; filename="map.jpg"');
imagejpeg( $map );
imagedestroy( $map );
?>