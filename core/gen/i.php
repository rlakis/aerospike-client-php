<?php

function send_socket_msg ( $handle, $data, $length ){
    if ( feof($handle) || fwrite ( $handle, $data, $length ) !== $length ) {
        echo 'connection unexpectedly closed (timed out?)';
        return false;
    }
    echo 'sent',"\n";
    return true;
}

//$fp = fsockopen("db.mourjan.com", 1515, $errno, $errstr, 30);
$fp = stream_socket_client("tcp://db.mourjan.com:1515", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "1|5a4c7dedddaf262c66aa4528294ef82b|1005828";
    //send_socket_msg($fp, $out, strlen($out));
    fwrite($fp, $out);
    while (!feof($fp)) {
        $callback = trim(fgets($fp, 1024));
        if($callback=='okok'){
            echo 'done';
            break;
        }
    }
//    while (!feof($fp)) {
//        echo fgets($fp, 128);
//    }
    fclose($fp);
}
?>
