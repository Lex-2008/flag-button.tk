<!DOCTYPE HTML><html><head>
<title>Stats</title>
<style>
body, html {height: 100%; margin: 0; padding: 0}
table {top: 0; left: 0; width: 100%; height: 100%; position: absolute}
td {vertical-align: middle; text-align: center}
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

</style>
</head><body>
<table><tr><td><div class="content">
    <h1>Stats</h1>
    
<?php
require 'db.php';

function formatN(&$size)
    {
    if($size<10000)
        return '';
    $size=round($size/1000);
    if($size<10000)
        return 'k';
    $size=round($size/1000);
    if($size<10000)
        return 'M';
    $size=round($size/1000);
    if($size<10000)
        return 'G';
    $size=round($size/1000);
    if($size<10000)
        return 'T';
    return 'X';
    }

$result = mysql_query2("
    SHOW TABLE STATUS LIKE 'flagbutton'
    ");
$art=mysql_fetch_assoc($result);
$size=round($art['Data_length']+$art['Index_length']);
$size1=formatN($size);
?>
    Records: <b><?php echo round($art['Rows']) ?></b>,
    Size: <b><?php echo $size ?></b> <?php echo $size1 ?>b,
    Record size: <b><?php echo round(($art['Data_length']+$art['Index_length'])/$art['Rows']) ?></b> b,
    Data/Index: <b><?php echo round(($art['Data_length'])/($art['Data_length']+$art['Index_length'])*100) ?>/<?php echo round(($art['Index_length'])/($art['Data_length']+$art['Index_length'])*100) ?></b>.
    
    <br>
<?php
$result = mysql_query2("
    SELECT time
    FROM `flagbutton` 
    ORDER BY time ASC 
    LIMIT 1
    ");
$art=mysql_fetch_array($result);
?>
    Oldest entry: <?php echo preg_replace('/(....-..-)(..).*/','$1<b>$2</b>',$art[0]) ?>
    (Delete in <b><?php echo CLEAN_CACHE_INTERVAL - (round(time()/24/60/60) % CLEAN_CACHE_INTERVAL) ?></b> days)

    
    <!-- ==================================== -->
    
    <h2>Recent</h2>
<?php
$result = mysql_query2("
    SELECT host
    FROM `flagbutton`
    ORDER BY time DESC
    LIMIT 10");
$dat=array();
while($art=mysql_fetch_array($result))
    {
    if(strpos($art[0],'operaunite.com')!==false)
        continue;
    $dat[]='<a href="http://'.$art[0].'/">'.$art[0].'</a>';
    }
echo implode(', ',$dat);
?>
    
    <!-- ==================================== -->
    
    <h2>Duplicates</h2>
    <!--<div class="info">
        This table will show sites using same IP. Only first 10 items with number of duplicates more then 3 are shown.
        <a href="#" onclick="document.getElementById('host-group').style.display='block';this.parentNode.style.display='none';return false;">[show]</a>
    </div>-->
    <ul id="host-group"> <!--style="display:none"-->
<?php
$result = mysql_query2("
    SELECT GROUP_CONCAT(host),COUNT(ip)
    FROM `flagbutton`
    WHERE ip<>0
    GROUP BY ip
    ORDER BY COUNT(ip) DESC
    LIMIT 10");
while($art=mysql_fetch_array($result))
    {
    if(strpos($art[0],'operaunite.com')!==false)
        {
        echo '<li>(',$art[1],') ','*.operaunite.com','</li>';
        continue;
        }
    $hosts=explode(',',$art[0]);
    if($art[1]<10)
        {
        echo '<li>(',$art[1],') ','...','</li>';
        break;
        }
    echo '<li>(',$art[1],') ',implode(', ',$hosts),'</li>';
    }
?>
    </ul>
    
    
    
    <div class="warning">
        <b>Warning!</b> Following information will be processed and presented to you by third-party Google chart API.
    </div>
    
    <!-- ==================================== -->
    
    <h2>Sources</h2>
<?php
$result = mysql_query2("
    SELECT source, count(source)
    FROM `flagbutton`
    GROUP BY source");

$src=array();
$dat=array();

while($art=mysql_fetch_array($result))
    {
    $src[]=$art[0];
    $dat[]=$art[1];
    }
$dats=array_sum($dat);
foreach($dat as &$val)
    $val=round($val/$dats*100);

$dat=implode(',',$dat);
$src=implode('|',$src);
$url='http://chart.apis.google.com/chart?cht=p3&chs=400x100&chd=t:'.$dat.'&chl='.$src;
?>
    <div class="info">
        This chart will show how often results from each source are considered the best among others.
        <a rel="src" href="<?php echo $url ?>">[show]</a>
    </div>
    <img id="src" style="display:none">
    
    <!-- ==================================== -->
    
    <h2>Requests</h2>
<?php

//grab the list of filenames
$dir=scandir('.');
$today=intval(date('Y')*365.2425+date('z'));
$data1=array();
$data2=array();
foreach($dir as $file)
    {
    if(preg_match('|'.ACCESS_FILE.'-(\d*-\d*-\d*)-(\d*).txt|',$file,$m))
        {
        //difference in days
        //~ $days=date_diff(date_create('20'.$m[1]),$today,true)->days;//https://bugs.php.net/bug.php?id=51184
        $dt=date_create($m[1]);
        $days=$today-intval($dt->format('Y')*365.2425+$dt->format('z'));
        if($days<5)
            $data1[$days]=file_get_contents($file);
        }
    if(preg_match('|'.REQUESTS_FILE.'-(\d*-\d*-\d*)-(\d*).txt|',$file,$m))
        {
        //difference in days
        //~ $days=date_diff(date_create('20'.$m[1]),$today,true)->days;//https://bugs.php.net/bug.php?id=51184
        $dt=date_create($m[1]);
        $days=$today-intval($dt->format('Y')*365.2425+$dt->format('z'));
        if($days<5)
            $data2[$days]=file_get_contents($file);
        }
    }
if($data1[0])
    {
    //we already made a backup today, so all dates must be shifted: today->yesterday, yesterday->the day before, etc
    ksort($data1);//workaround against array_unshift changing keys
    array_unshift($data1,0);
    }
$data1[0]=@file_get_contents(ACCESS_FILE.'.txt');
if($data2[0])
    {
    ksort($data2);
    array_unshift($data2,0);
    }
$data2[0]=@file_get_contents(REQUESTS_FILE.'.txt');


$src=array();
$dat1=array();
$dat2=array();
for($day=4;$day>=0;$day--)
    {
    $src[]=date_format(date_create('-'.$day.' day'),'j');
    $dat1[]=$data1[$day];
    $dat2[]=$data2[$day];
    }
$max=max(max($dat1),max($dat2));
foreach($dat1 as &$val)
    $val=round($val/$max*100);
foreach($dat2 as &$val)
    $val=round($val/$max*100);
$src[4]='today';

$dat1=implode(',',$dat1);
$dat2=implode(',',$dat2);
$src=implode('|',$src);
$url='http://chart.googleapis.com/chart?cht=bvo&chs=300x100&chco=44ff44,ff6666&chdl=to+'.SITE_NAME.'+API|to+external+services&chd=t:'.$dat1.'|'.$dat2.'&chl='.$src;
?>
    <div class="info">
        This chart will show relative amount of requests per last 5 days.
        Green bars show amount of requests to this API,
        red bars show amount of requests from this service to external sources.
        <a rel="hist" href="<?php echo $url ?>">[show]</a>
    </div>
    <img id="hist" style="display:none">
    


</div></td></tr></table>
<script>
var q=document.getElementsByTagName('a');
for(var w=0;w<q.length;w++)
    {
    if(q[w].rel)
    q[w].onclick=function()
        {
        document.getElementById(this.rel).style.display='block';
        document.getElementById(this.rel).src=this.href;
        this.parentNode.style.display='none';
        return false;
        }
    }
</script>
</body></html>
