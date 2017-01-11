<?php

require get_cfg_var('mourjan.path'). '/config/cfg.php';



//echo "sms delivery called";
/*
/api/SMSDelivery.php?
 * msisdn=9613287168
 * &to=Mourjan
 * &network-code=41501
 * &messageId=030000003AF732BC
 * &price=0.02100000
 * &status=delivered
 * &scts=1404091023
 * &err-code=0
 * &client-ref=123456
 * &message-timestamp=2014-04-09+10%3A23%3A19
 * HTTP/1.1" 200 5 "-" "Nexmo/MessagingHUB/v1.0" "-"
 */


$msisdn = filter_input(INPUT_GET, 'msisdn', FILTER_VALIDATE_INT)+0;
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING);
$networkcode = filter_input(INPUT_GET, 'network-code', FILTER_VALIDATE_INT)+0;
$messageId = filter_input(INPUT_GET, 'messageId', FILTER_SANITIZE_STRING);
$price = filter_input(INPUT_GET, 'price', FILTER_VALIDATE_FLOAT)+0;
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
$scts = filter_input(INPUT_GET, 'scts', FILTER_VALIDATE_INT)+0;
$errCode = filter_input(INPUT_GET, 'err-code', FILTER_VALIDATE_INT)+0;
$messageTimestamp = filter_input(INPUT_GET, 'message-timestamp', FILTER_SANITIZE_STRING);
$reference = filter_input(INPUT_GET, 'client-ref', FILTER_VALIDATE_INT)+0;
$text = filter_input(INPUT_GET, 'text', FILTER_SANITIZE_STRING);


error_log(json_encode($_GET).PHP_EOL, 3, "/var/log/mourjan/sms.log");
error_log(json_encode($_POST).PHP_EOL, 3, "/var/log/mourjan/sms.log");
error_log(sprintf("%s\t%d\t%s\t%d\t%s\t%f\t%s\t%d\t%d\t%s\t%d\t%s", date("Y-m-d H:i:s"), $msisdn, $to, $networkcode, $messageId, $price, $status, $scts, $errCode, $messageTimestamp, $reference, $text).PHP_EOL, 3, "/var/log/mourjan/sms.log");

if ($errCode==0 && $reference>0 && ($to=="Mourjan"||$to=="12242144077"||$to=="mourjan"||$to=="33644630401")) 
{
    $isAndroidValidate=false;
    if(substr($reference,0,1)=='m'){
        $reference = substr($reference,1);
        $isAndroidValidate=true;
    }
    $db = new DB($config);
    if($isAndroidValidate){
        $db->queryResultArray("UPDATE WEB_USERS_LINKED_MOBILE SET DELIVERED=1 WHERE ID=? and DELIVERED=0", [$reference], TRUE);
    }else{
        $db->queryResultArray("UPDATE WEB_USERS_MOBILE SET DELIVERED=1 WHERE ID=? and MOBILE=? and DELIVERED=0", [$reference, $msisdn], TRUE);    
    }
    error_log(sprintf("%s\t%d\tis written", date("Y-m-d H:i:s"), $msisdn).PHP_EOL, 3, "/var/log/mourjan/sms.log");
    $db->close();
}
else
{
    error_log( var_export($_GET, true) );
}


