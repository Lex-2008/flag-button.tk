<?php
require 'db.php';
require 'geo.inc.php';
$for=sanitize($_REQUEST['for']);

//log the user
//note we're using explode, because HTTP_X_FORWARDED_FOR might have comma-separated list of ips as well
$addr = explode(",",@getenv('HTTP_CLIENT_IP').','.@getenv('HTTP_X_FORWARDED_FOR').','.@getenv('REMOTE_ADDR'));

//check geoip db
foreach($addr as $ip)
    {
    //check if the user is already in DB
    if(!$ip or $ip=='127.0.0.1')
        continue;
    $ip4=sprintf("%u", ip2long($ip));
    $r=mysql_query2( //should not fail here
    "SELECT 1 FROM usermaps WHERE
        proj='$for'
        AND
        ip='$ip4'
        LIMIT 1");
    if(mysql_num_rows($r))
        continue;
        //user is not in DB -- add him there
    list($data,$latlon,$source)=get_data_for($ip);
    if($latlon=='0:0' or $latlon==':' or $latlon=='')
        continue;
    mysql_query2( //it's okay if it's error
        "INSERT INTO usermaps SET
            proj='$for',
            latlon='$latlon',
            ip='$ip4'",
        false);
    };


//draw the map
$result = mysql_query2("
    SELECT DISTINCT latlon
    FROM usermaps
    WHERE proj='$for'");

$col='FF0000';
require 'map.inc.php';
?>