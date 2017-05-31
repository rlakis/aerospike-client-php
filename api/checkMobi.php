<?php
include get_cfg_var("mourjan.path") . "/config/cfg.php";
require_once get_cfg_var('mourjan.path'). '/core/model/NoSQL.php';

use Core\Model\NoSQL;

$request_body = file_get_contents('php://input');

error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), $request_body).PHP_EOL, 3, "/var/log/mourjan/sms.log");

$res = json_decode( $request_body, TRUE ); //convert JSON into array

if (isset($res['key']) && isset($res['number']))
{
    //NoSQL::getInstance()->mobileSetDeliveredCode($uid, intval($res['number']));
}


if (isset($res['id']) && isset($res['to']) && isset($res['status']))
{
    if ($res['status']=='delivered')
    {
        
    }
}

//    if (isset($json_request['deliveryInfoNotification']))
//    {
//        $to='mourjan';
//        $networkcode=0;
//        $address=$json_request['deliveryInfoNotification']['deliveryInfo']['address'];
//        $msisdn = str_replace('tel:','',$address);    
//        $deliveryStatus = $json_request['deliveryInfoNotification']['deliveryInfo']['deliveryStatus'];
//        $messageId=$json_request['deliveryInfoNotification']['callbackData'];
//        if ($deliveryStatus==='DeliveredToTerminal')
//        {
//            $errCode=0;
//            $to='mourjan';
//        }
//        else
//        {
//            $errCode=1;
//        }
//    }