<?php
require 'db.php';

//draw the map
$result = mysql_query2("
    SELECT latlon
    FROM flagbutton");

$col='00A0A0';
require 'map.inc.php';
?>