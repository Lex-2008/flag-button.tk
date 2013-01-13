<?php
require 'db.php';
if(!ALLOW_RESET)die;

echo '<pre>deleting...', CRLF;

mysql_query('drop table flagbutton')or print('Query 1 failed: ' . mysql_error().CRLF);
mysql_query('drop table usermaps')or print('Query 2 failed: ' . mysql_error().CRLF);
mysql_query('drop table flagbuttonstats')or print('Query 3 failed: ' . mysql_error().CRLF);

unlink(REQUESTS_FILE.'.txt');
unlink(ACCESS_FILE.'.txt');

echo 'creating...', CRLF;

$result = mysql_query("
CREATE TABLE `flagbutton` (
  `host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` int(20) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `latlon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` enum('freegeoip.net','ipgeobase.ru','ipinfodb.com','quova.com','GeoLite','".SITE_NAME."') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`host`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
")or die('Query flagbutton failed: ' . mysql_error().CRLF);

$result = mysql_query("
CREATE TABLE `usermaps` (
  `proj` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` int(20) unsigned NOT NULL,
  `latlon` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`proj`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
")or die('Query usermaps failed: ' . mysql_error().CRLF);

$result = mysql_query("
CREATE TABLE `flagbuttonstats` (
  `ip` int(20) unsigned NOT NULL,
  `ua` text COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
")or die('Query flagbuttonstats failed: ' . mysql_error().CRLF);


?>