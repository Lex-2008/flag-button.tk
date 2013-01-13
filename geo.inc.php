<?php
/*
==usage==
list($data,$latlon,$source)=get_data_for($ip);
$data=code|err|ip|co|country|region|city|zip|lat|lng|tz|src|cmp
$latlon=lat:lng
*/

include("geoipcity.inc");
include("geoipregionvars.php");


function my_weight($a)
    {
    //$a=code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
    $r=0;
    //+10 for each country,region,city
    foreach(array(4,5,6) as $q)
        if($a[$q]!='' and $a[$q]!='-')
            $r+=10;
    //+2 for each zip,tz
    foreach(array(7,10) as $q)
        if($a[$q]!='' and $a[$q]!='-')
            $r+=2;
    //add length of latlon, but no more then 10
    $r+=min(strlen($a[8].$a[9]),10);
    return $r;
    }


function get_data_for($ip)
    {
    $src=array('freegeoip.net','ipgeobase.ru','ipinfodb.com','quova.com','GeoLite');//note: you should edit reset.php and edit DB accordingly
        
    $urls=array(
        'http://freegeoip.net/csv/'.$ip,
        'http://ipgeobase.ru:7020/geo?ip='.$ip,
        'http://api.ipinfodb.com/v3/ip-city/?key='.IPINFODB_COM_KEY.'&format=raw&ip='.$ip,
        'http://api.quova.com/v1/ipinfo/'.$ip.'?apikey='.QUOVA_COM_KEY.'&sig='.md5(QUOVA_COM_KEY.QUOVA_COM_SECRET.time()).'&format=json',
        '',
        );
    
    //limit request frequence to once per second
    $c=@file_get_contents('last_request.txt')+1;//time when we can start working
    @file_put_contents('last_request.txt',max($c,microtime(true)));//put expected time of current request: either expected ($c) or real
    if($c>microtime(true))
        {
        //we need to wait
        set_time_limit(ceil($c)-time()+15);//add waiting time + 5 seconds to wait + 10 seconds for other stuff
        while($c>microtime(true))
            usleep($c-microtime(true));
        @file_put_contents('last_request.txt',microtime(true));
        }
    
    //request counter
    $c=@file_get_contents(REQUESTS_FILE.'.txt')+1;
    @file_put_contents(REQUESTS_FILE.'.txt',$c);
    if($c>990) $urls[3]='';
    //~ if(true) $urls[3]='';
    
    //http://no.php.net/manual/ru/function.curl-multi-init.php
    $mh = curl_multi_init();
    foreach ($urls as $i => $url)
        {
        $conn[$i] = curl_init($url);
        curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn[$i],CURLOPT_TIMEOUT,5);
        curl_multi_add_handle($mh, $conn[$i]);
        }
    do {
        $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
    foreach ($urls as $i => $url) {
        $res[$i] = curl_multi_getcontent($conn[$i]);
        curl_multi_remove_handle($mh, $conn[$i]);
        curl_close($conn[$i]);
        }
    curl_multi_close($mh);
    
    
    //freegeoip.net
    if(strpos($res[0],'404: Not Found')!==false)
        $a[0]=array('404','Not Found',$ip,'','','','','','','','');
    elseif(strpos($res[0],'<html>')!==false)
        $a[0]=array('html',$res[0],$ip,'','','','','','','','');
    else
        {
        //ip,co,country,region_code,region_name,city,zip,lat,lon
        $data = explode(',',$res[0]);
        if(count($data)<9)
            $a[0]=array('too short',$res[0],$ip,'','','','','','','','');
        else
            {
            //remove quotes at the beginning and end
            foreach ($data as &$value)
                if($value[0]=='"' and $value[strlen($value)-1]=='"')
                    $value=substr($value,1,-1);
            //code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
            $a[0]=array('ok','',$data[0],$data[1],$data[2],$data[4],$data[5],$data[6],$data[7],$data[8],'');
            }
        }
    
    
    //ipgeobase.ru
    //< ?xml version='1.0' encoding='Windows-1251'? ><ip-answer>{CRLF}<ip value='ip'><inetnum>ip1 - ip2</inetnum><country>co</country><city>city</city><region>region</region><district>Северо-Западный федеральный округ</district><lat>lat</lat><lng>lng</lng></ip></ip-answer>
    $tok = strtok($res[1], "<>");
    $next=false;
    $tmp=array();
    while ($tok !== false)
        {
        if($next)
            {
            $tmp[$next]=$tok;
            $next=false;
            }
        else
            if(in_array($tok,array('country','city','region','lat','lng')))
                $next=$tok;
        $tok = strtok("<>");
        }
    $tmp['city']=iconv("windows-1251", "utf-8",$tmp['city']);
    $tmp['region']=iconv("windows-1251", "utf-8",$tmp['region']);
    $tmp['co']=$tmp['country'];
    if($tmp['co']=='RU') $tmp['country']='Россия';
    if($tmp['co']=='UA') $tmp['country']='Украина';
    //code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
    $a[1]=array('ok','',$ip,$tmp['co'],$tmp['country'],$tmp['region'],$tmp['city'],'',$tmp['lat'],$tmp['lng'],'');
    
    
    //ipinfodb.com
    //statuscode;statusmessage;ip;co;country;region;city;zip;lat;lon;timezone
    $data = explode(';',$res[2]);
    if(count($data)<11)
        $a[2]=array('too short',$res[2],$ip,'','','','','','','','');
    else
        {
        foreach($data as &$val)
            if($val=='-') $val='';
        //code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
        $a[2]=array(strtolower($data[0]),$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9],$data[10]);
        }
    
    
    //quova.com
    $data=json_decode($res[3],true);
    if($data['ipinfo'])
        {
        $tmp['ip']=$data['ipinfo']['ip_address'];
        $tmp['lat']=$data['ipinfo']['Location']['latitude'];
        $tmp['lng']=$data['ipinfo']['Location']['longitude'];
        $tmp['country']=$data['ipinfo']['Location']['CountryData']['country'];
        $tmp['co']=$data['ipinfo']['Location']['CountryData']['country_code'];
        $tmp['region']=$data['ipinfo']['Location']['StateData']['state'];
        $tmp['city']=$data['ipinfo']['Location']['CityData']['city'];
        $tmp['zip']=$data['ipinfo']['Location']['CityData']['postal_code'];
        $tmp['tz']=$data['ipinfo']['Location']['CityData']['time_zone'];
        //convert $tmp['tz'] from h to h:mm
        $tmp['tz']=($tmp['tz']>0?floor($tmp['tz']):ceil($tmp['tz'])).':'.str_pad(fmod(abs($tmp['tz']),1)*60,2,'0',STR_PAD_LEFT);
        //code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
        $a[3]=array('ok','',$tmp['ip'],$tmp['co'],$tmp['country'],$tmp['region'],$tmp['city'],$tmp['zip'],$tmp['lat'],$tmp['lng'],$tmp['tz']);
        }
    elseif($data['gds_error'])
        $a[3]=array($data['gds_error']['http_status'],$data['gds_error']['mess'],'','','','','','','','','');
    else
        $a[3]=array('invalid',$res[3],$ip,'','','','','','','','');
    
    
    //geoip
    $gi = geoip_open(GEOIP_DAT_FILE,GEOIP_STANDARD);
    $record = geoip_record_by_addr($gi,$ip);
    //code|err|ip|co|country|region|city|zip|lat|lng|tz |src|cmp
    $a[4]=array('ok','',$ip,$record->country_code,$record->country_name,$GEOIP_REGION_NAME[$record->country_code][$record->region],$record->city,$record->postal_code,$record->latitude,$record->longitude,'');
    geoip_close($gi); 
    
    
    //choose best
    $b[0]=my_weight($a[0]);
    $b[1]=my_weight($a[1]);
    $b[2]=my_weight($a[2]);
    $b[3]=my_weight($a[3]);
    $b[4]=my_weight($a[4]);
    //add score to Russian provider for Russian region
    if($a[1][3]=='RU' or $a[1][3]=='UA')
        $b[1]+=5;
    $cmp=$b[0].'-'.$b[1].'-'.$b[2].'-'.$b[3].'-'.$b[4];
    $m=max($b);
    $c=array();
    if($b[0]==$m) $c[]=0;
    if($b[1]==$m) $c[]=1;
    if($b[2]==$m) $c[]=2;
    if($b[3]==$m) $c[]=3;
    if($b[4]==$m) $c[]=4;
    $c=$c[mt_rand(0,count($c)-1)];//randomly select among best candidates
    $a[$c][]=$src[$c];
    $a[$c][]=$cmp;
    return array(implode('|',$a[$c]),$a[$c][8].':'.$a[$c][9],$a[$c][11]);
    }
?>