<?php

require get_cfg_var('mourjan.path'). '/config/cfg.php';
require_once get_cfg_var('mourjan.path'). '/core/model/NoSQL.php';

use Core\Model\NoSQL;

$call_id=null;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING)=='GET')
{
        $msisdn = filter_input(INPUT_GET, 'msisdn', FILTER_VALIDATE_INT)+0;
        $to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING);
        $networkcode = filter_input(INPUT_GET, 'network-code', FILTER_VALIDATE_INT)+0;
        $messageId = filter_input(INPUT_GET, 'messageId', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_GET, 'price', FILTER_VALIDATE_FLOAT)+0;
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
        $scts = filter_input(INPUT_GET, 'scts', FILTER_VALIDATE_INT)+0;
        $errCode = filter_input(INPUT_GET, 'err-code', FILTER_VALIDATE_INT)+0;
        $messageTimestamp = filter_input(INPUT_GET, 'message-timestamp', FILTER_SANITIZE_STRING);
        $reference = filter_input(INPUT_GET, 'client-ref', FILTER_SANITIZE_STRING);
        $text = filter_input(INPUT_GET, 'text', FILTER_SANITIZE_STRING);

        $call_id = filter_input(INPUT_GET, 'call-id', FILTER_SANITIZE_STRING);
        error_log(json_encode($_GET).PHP_EOL, 3, "/var/log/mourjan/sms.log");
    }
    else
    {        
        $msisdn = filter_input(INPUT_POST, 'msisdn', FILTER_VALIDATE_INT)+0;
        $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_STRING);
        $networkcode = filter_input(INPUT_POST, 'network-code', FILTER_VALIDATE_INT)+0;
        $messageId = filter_input(INPUT_POST, 'messageId', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT)+0;
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $scts = filter_input(INPUT_POST, 'scts', FILTER_VALIDATE_INT)+0;
        $errCode = filter_input(INPUT_POST, 'err-code', FILTER_VALIDATE_INT)+0;
        $messageTimestamp = filter_input(INPUT_POST, 'message-timestamp', FILTER_SANITIZE_STRING);
        $reference = filter_input(INPUT_POST, 'client-ref', FILTER_SANITIZE_STRING);
        $text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING);
        
        error_log(json_encode($_POST).PHP_EOL, 3, "/var/log/mourjan/sms.log");    
    }
    error_log(sprintf("%s\t%d\t%s\t%d\t%s\t%f\t%s\t%d\t%d\t%s\t%d\t%s", date("Y-m-d H:i:s"), $msisdn, $to, $networkcode, $messageId, $price, $status, $scts, $errCode, $messageTimestamp, $reference, $text).PHP_EOL, 3, "/var/log/mourjan/sms.log");

//}

if ($call_id)
{
    return;
}

$uid=0;

if ($errCode==0 && ($to=="Mourjan"||$to=="12242144077"||$to=="mourjan"||$to=="33644630401"))
{
    if (strlen($reference)>1)
    {
        $json = json_decode($reference, false);
        $uid = $json->uid ?? 0;
    }
    
    if (NoSQL::getInstance()->mobileSetDeliveredCode($uid, $msisdn, $messageId))
    {
        error_log(sprintf("%s\t%d\tis written", date("Y-m-d H:i:s"), $msisdn).PHP_EOL, 3, "/var/log/mourjan/sms.log");
    }
}
else
{
    //error_log( json_encode($_GET) );
}


