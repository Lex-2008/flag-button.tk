<?php
require 'db.php';

//get stats
$stats=sanitize($_REQUEST['stat']);
$ip4=sprintf("%u", @ip2long(@getenv('REMOTE_ADDR')));
$ua=sanitize(@getenv("HTTP_USER_AGENT"));

//put them to db
mysql_query2(
    "INSERT INTO flagbuttonstats SET
	ip='$ip4',
	ua='$ua',
	data='$stats'
    ");
?>
{}
