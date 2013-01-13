<?php
require 'db-keys.php';

// connect to DB
mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS)
    or die('Could not connect: ' . mysql_error());
mysql_select_db(MYSQL_DB)
    or die('Could not select database: ' . mysql_error());
date_default_timezone_set(DATE_TZ);
mysql_set_charset('utf8');
mysql_query("SET time_zone = ".MYSQL_TZ);



// f*ck the hackers
function sanitize($text,$html=0,$mysql=1)
    {
    if (get_magic_quotes_gpc())
	$text=stripslashes($text);
    switch($html)
	{
	case 2:
	    $text=htmlentities($text,ENT_QUOTES,'utf-8');
	break;
	case 1:
	    $text=htmlspecialchars_decode(htmlentities($text,ENT_QUOTES),ENT_QUOTES);
	break;
	}
    if ($mysql)
	$text=mysql_real_escape_string($text);
    return $text;
    }


define("CRLF", "
");


function mysql_query1($q){echo $q; return 1;}

function mysql_query2($q,$die=true)
    {
    $w=mysql_query($q);
    if($w)
	return $w;
    else
        {
        @file_put_contents('mysqlerr.txt',CRLF.CRLF.date('r').CRLF.mysql_error(),FILE_APPEND);
        if($die)
            die;
        else
            return $w;
        }
    }


?>
