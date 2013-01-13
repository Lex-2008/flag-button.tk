<!DOCTYPE HTML><html><head>
<title>captcha!</title>
<style>
body, html {height: 100%; margin: 0; padding: 0}
table {top: 0; left: 0; width: 100%; height: 100%; position: absolute}
td {vertical-align: middle}
form {text-align: center}
h1 {font-style:italic; margin-top:-0.5em}
</style>
</head><body>
<table><tr><td>
    <form action="captcha2.php">
    <h1>Captcha!</h1>
    To continue, please solve this little mystery:
    <?php
require 'db-keys.php';

do {
    $t=time();
    $n=mt_rand();
    $s=md5($t.$n.CAPTCHA_SECRET);
    $a=sscanf($s,'%2x%2x%2x');
    //check that they don't match and there's no zeros
    }while($a[0]==$a[1] or $a[0]==$a[2] or $a[1]==$a[2] or $a[0]==0 or $a[1]==0 or $a[2]==0);


//(x-1)(x-2)(x-3)=x3-(1+2+3)x2+(12+23+13)x+123
$b[0]=$a[0]*$a[1]*$a[2];
$b[1]=$a[0]*$a[1]+$a[0]*$a[2]+$a[1]*$a[2];
$b[2]=$a[0]+$a[1]+$a[2];
echo '<h3>x<sup>3</sup>-',$b[2],'x<sup>2</sup>+',$b[1],'x-',$b[0],'=0</h3>';

if(CAPTCHA_SOLVED)
    {
    echo 'x<sub>1</sub>=<input type="text" name="a[0]" value="',$a[0],'" size="3">, ';
    echo 'x<sub>2</sub>=<input type="text" name="a[1]" value="',$a[1],'" size="3">, ';
    echo 'x<sub>3</sub>=<input type="text" name="a[2]" value="',$a[2],'" size="3">, ';
    }
else
    {
    echo 'x<sub>1</sub>=<input type="text" name="a[0]" size="3">, ';
    echo 'x<sub>2</sub>=<input type="text" name="a[1]" size="3">, ';
    echo 'x<sub>3</sub>=<input type="text" name="a[2]" size="3">.';
    }
echo '<input type="hidden" name="b[0]" value="',$b[0],'">';
echo '<input type="hidden" name="b[1]" value="',$b[1],'">';
echo '<input type="hidden" name="b[2]" value="',$b[2],'">';
echo '<input type="hidden" name="t" value="',$t,'">';
echo '<input type="hidden" name="n" value="',$n,'">';
?>
	<br>
	<br>
        <input type="Submit">
    </form>
</td></tr></table>
</body></html>
