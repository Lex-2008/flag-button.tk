<!DOCTYPE HTML><html><head>
<title>Stats</title>
<style>
body, html {height: 100%; margin: 0; padding: 0}
table.q {top: 0; left: 0; width: 100%; height: 100%; position: absolute}
td.w {vertical-align: middle; text-align: center}
h1 {margin-top:-0.5em}
.content {margin:auto; width:30em}
img {margin:auto}
.info
    {
    border:1px solid blue;
    background:lightcyan;
    margin-top:1em;
    margin-bottom:1em;
    }
.warning
    {
    border:1px solid red;
    background:yellow;
    margin-bottom:1em;
    }
ul
    {
    padding-left:0px;
    margin-left:0px;
    }
li
    {
    padding-left:0px;
    margin-left:0px;
    display:block;
    }
#host-group li
    {
    border:1px solid silver;
    }
table.r span
{
    display:block;
    background:lightgray;
}
td.perc
    {
	width:500px;
    }

</style>
</head><body>
<table class="q"><tr><td class="w"><div class="content">
    <h1>Stats</h1>
    
<?php
require 'db.php';

//get data

$result = mysql_query2("
    SELECT data from flagbuttonstats
    WHERE UNIX_TIMESTAMP(time)>UNIX_TIMESTAMP()-(7*24*60*60)
    ");
$data=array('ver'=>array(),'len'=>array(),'pref'=>array());
while($art=mysql_fetch_array($result))
    {
    $art=json_decode($art[0],true);
    if($art['ver'])
        foreach($art['ver'] as $n=>$v)
            {
            if(!$data['ver'][$n])
                $data['ver'][$n]=array();
            if(!$data['ver'][$n][$v])
                $data['ver'][$n][$v]=0;
            $data['ver'][$n][$v]++;
            }
    if($art['len'])
        foreach($art['len'] as $n=>$v)
            {
            if(!$data['len'][$n])
                $data['len'][$n]=array();
            $data['len'][$n][]=$v;
            }
    if($art['pref'])
        foreach($art['pref'] as $n=>$v)
            {
            if(!$data['pref'][$n])
                $data['pref'][$n]=array();
            if(!$data['pref'][$n][$v])
                $data['pref'][$n][$v]=0;
            $data['pref'][$n][$v]++;
            }
    }

//~ print_r($data);

//show data
?>
    <!-- ==================================== -->
    
    <h2>Versions</h2>
<table class="r">
<?php
foreach($data['ver'] as $title=>$sub)
    {
    echo "<tr><th colspan=\"3\">{$title}</th></tr>";
    $s=0;
    $m=$sub[0];
    foreach($sub as $v)
        {
        $s+=$v;
        if($v>$m)
            $m=$v;
        }
    foreach($sub as $name=>$val)
	{
	$prc=round($val*100/$s);
	$len=$val*100/$m;
	echo "<tr><td>{$name}</td><th>$val</th><td class=\"perc\"><span style=\"width:$len%\">$prc%</span></td></tr>";
	}
    }

?>
</table>
    <!-- ==================================== -->
    
    <h2>Lengths</h2>
<table class="r">
<tr><td></td><td>min</td><td>max</td><td>med</td><td>avg</td></tr>
<?php
//c http://www.mdj.us/web-development/php-programming/calculating-the-median-average-values-of-an-array-with-php/
foreach($data['len'] as $title=>$arr)
    {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    $min=$arr[0];
    $max=$arr[$count-1];
    $avg=array_sum($arr)/$count;
    
    echo "<tr><th>{$title}</th><td>$min</td><td>$max</td><td>$median</td><td>$avg</td></tr>";    
    }

?>
</table>
    <!-- ==================================== -->
    
    <h2>Preferences</h2>
<table class="r">
<?php
foreach($data['pref'] as $title=>$sub)
    {
    echo "<tr><th colspan=\"3\">{$title}</th></tr>";
    $s=0;
    $m=$sub[0];
    foreach($sub as $v)
        {
        $s+=$v;
        if($v>$m)
            $m=$v;
        }
    foreach($sub as $name=>$val)
	{
	$prc=round($val*100/$s);
	$len=$val*100/$m;
	echo "<tr><td>{$name}</td><th>$val</th><td class=\"perc\"><span style=\"width:$len%\">$prc%</span></td></tr>";
	}
    }

?>

</body></html>