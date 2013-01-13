<!DOCTYPE HTML><html><head>
<title>captcha!</title>
<style>
body, html {height: 100%; margin: 0; padding: 0}
table {top: 0; left: 0; width: 100%; height: 100%; position: absolute}
td {vertical-align: middle}
form {text-align: center}
h1 {margin-top:-0.5em}
</style>
</head><body>
<table><tr><td>
    <form>
	<?php
require 'db-keys.php';
$fail=false;

$b=$_REQUEST['b'];
for($i=0;$i<3;$i++)
    {
    $x=$_REQUEST['a'][$i];
    if($x*$x*$x-$b[2]*$x*$x+$b[1]*$x-$b[0]!=0)
	$fail=true;
    }

$t=$_REQUEST['t'];
$n=$_REQUEST['n'];
$s=md5($t.$n.CAPTCHA_SECRET);
$a=sscanf($s,'%2x%2x%2x');
//note that we can't compare $a with $_REQUEST[a], because they might have a different order

//(x-1)(x-2)(x-3)=x3-(1+2+3)x2+(12+23+13)x+123
$c[0]=$a[0]*$a[1]*$a[2];
$c[1]=$a[0]*$a[1]+$a[0]*$a[2]+$a[1]*$a[2];
$c[2]=$a[0]+$a[1]+$a[2];

//above $a is computer-generated correct answers. Below $a is user-provided data
$a=$_REQUEST['a'];

if($fail)
    echo '<h1>FAIL</h1>';//wrong values
elseif($a[0]==$a[1] or $a[0]==$a[2] or $a[1]==$a[2] or $a[0]==0 or $a[1]==0 or $a[2]==0)
    echo '<h1>FAIL</h1>Please try more.';//duplicates (or zeros, but this shouldn't happen)
elseif($c[0]!=$b[0] or $c[1]!=$b[1] or $c[2]!=$b[2])
    echo '<h1>FAIL</h1>Just fail.';//wrong "b" parameters
elseif(time()>$_REQUEST['t']+60)//time fail
    echo '<h1>Too slow!</h1>I admire your mathematical abilities, but can you be faster?<br>I already forgot what I asked!';
else //force is strong in this one!
    echo 'The best way to contact me is <br> via email: <a href="mailto:spam@flag-button.tk">spam@flag-button.tk</a>.';

?>
	
    </form>
</td></tr></table>
</body></html>
