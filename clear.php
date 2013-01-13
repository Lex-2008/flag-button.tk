<?php
require 'db.php';
if(!ALLOW_CLEAR)die;

$a=array();
$s=strtr($_REQUEST['list'],"'",'"');
if($l=json_decode($s,true))
    {
    foreach($l as $k=>$one)
	{
	$a[]='host LIKE "%'.mysql_real_escape_string($k).'"';
	}
    $a='DELETE FROM flagbutton WHERE '.implode(' OR ',$a);
    mysql_query($a)or print('Query failed: ' . mysql_error().CRLF);
    echo 'ok';
    }
else
    echo 'fail';
?>