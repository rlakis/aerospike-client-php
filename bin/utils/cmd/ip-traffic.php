<?php
\ini_set('memory_limit', '-1');
$whitelist=[
    '127.0.0.1'=>1, 
    '66.249.66.50'=>1, 
    '40.77.167.44'=>1, 
    '157.55.39.176'=>1, 
    '40.77.167.79'=>1,
    '157.55.39.65'=>1,
    '207.46.13.75'=>1,
    '207.46.13.122'=>1,
    '157.55.39.42'=>1,
    '66.249.66.144'=>1
    ];

$banned=[
    '117.204.133.67'=>1, 
    '65.21.70.201'=>1, 
    '185.40.210.122'=>1, 
    '213.6.213.254'=>1, 
    '93.168.5.126'=>1,
    '207.180.235.202'=>1,
    '157.90.182.24'=>1,
    '41.219.31.22'=>1,
    '212.115.109.112'=>1];

$rs=[];
$i=0;
$fh=fopen('/var/log/h2o/access.json', 'r');
$mindate=$maxdate=null;

while ($line=fgets($fh)) {
    //echo $line, "\n";
    $request=\json_decode($line);
    if (str_ends_with($request->path, '.js')||str_ends_with($request->path, '.css')||str_ends_with($request->path, '.jpeg')||str_ends_with($request->path, '.png')||str_ends_with($request->path, '.svg')) {
        continue;
    }
    $at=date_create_from_format('YmdHis', $request->at);
    if ($i===0) {
        $mindate=$maxdate=$at;
    }
    else {
        if ($at<$mindate) $mindate=$at;
        if ($at>$maxdate) $maxdate=$at;
    }
    $items=\explode(':', $request->remote);
    if (\count($items)<=2) {
        $ip=$items[0];
    }
    else {
        unset($items[\count($items)-1]);
        $ip=\implode(':', $items);
    }
    if ($whitelist[$ip]??0===1) {  continue;  }
    if ($banned[$ip]??0===1) {  continue;  }
    if (!isset($rs[$ip])) {
        $rs[$ip]=0;
    }
    $rs[$ip]++;
    //print_r($request);
    $i++;
    
    //if ($i>=10000000) break;
}
\fclose($fh);

\asort($rs);

/*
\usort($rs, function ($item1, $item2) {
    return $item1 <=> $item2;
});
*/
$final=[];
foreach ($rs as $ip => $value) {
    if ($value>1000) {
        $final[$ip]=$value;
    }
}
unset($rs);
\print_r($final);


echo 'Period: ', $mindate->format('Y-m-d H:i:s'), "\t", $maxdate->format('Y-m-d H:i:s'), "\n";