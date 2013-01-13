<?php
require 'db.php';
require 'geo.inc.php';

//get host
$host=sanitize($_REQUEST['host']);

//check host cache
$result = mysql_query2(
    "SELECT data
    FROM flagbutton
    WHERE host='$host'
    LIMIT 1",
    false);
$data=mysql_fetch_array($result);

if($data)
    $data=$data[0];
else
    {
    //get ip
    $ip=gethostbyname($host);
    if(!preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$ip))
	{
	$ip=gethostbyname('www.'.$host);
	}
    if(!preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$ip))
	{
	$data="err|Can't get IP for $host||||||||||".SITE_NAME."|";
	$latlon='';
	$source=SITE_NAME;
	}
    else
	{
	//check ip cache
	$ip4=sprintf("%u", ip2long($ip));
	$result = mysql_query2(
	    "SELECT data, latlon, source
	    FROM flagbutton
	    WHERE ip='$ip4'
	    LIMIT 1",
	    false);
	$ret=mysql_fetch_array($result);
	
	if($ret)
	    {
	    $data=$ret[0];
	    $latlon=$ret[1];
	    $source=$ret[2];
	    }
	else
	    {
	    //check geoip db
	    list($data,$latlon,$source)=get_data_for($ip);
	    }
	}
    
    //push data to cache - including the case when we used ip cache
    $data1=mysql_real_escape_string($data);
    $latlon1=mysql_real_escape_string($latlon);
    $source1=mysql_real_escape_string($source);
    mysql_query2(
	"INSERT INTO flagbutton SET
	    host='$host',
	    ip='$ip4',
	    data='$data1',
	    latlon='$latlon1',
	    source='$source1'",
	false);
    }

//show the data to the user (finally!)
echo $data;

//request counter
@file_put_contents(ACCESS_FILE.'.txt',@file_get_contents(ACCESS_FILE.'.txt')+1);
?>