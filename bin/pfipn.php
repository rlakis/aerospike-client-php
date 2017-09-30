<?php
ini_set('log_errors_max_len', 0);
$sandbox = (get_cfg_var('mourjan.server_id')=='99');
$logfile = '/var/log/mourjan/payfort.log';
/*
if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
           $headers = ''; 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
} 
*/

if (!file_exists($logfile)) 
{
    $fh = @fopen($logfile, 'w');
    fclose($fh);
}

/*
$headers = getallheaders();
$logMsg = '------------------------------------'.PHP_EOL;
foreach($headers as $key => $value){
    $logMsg.=$key.'::'.$value.PHP_EOL;
}
error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), $logMsg.json_encode($_POST).PHP_EOL), 3, $logfile);
*/

error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), json_encode($_POST).PHP_EOL), 3, $logfile);

include_once get_cfg_var('mourjan.path') . '/config/cfg.php';
include_once $config['dir']. '/core/model/Db.php';
include_once $config['dir'].'/core/lib/PayfortIntegration.php';
use Core\Model\DB;

if (FALSE)
{
    $pData = '{"amount":"4999","response_code":"14000","card_number":"456835******1003","card_holder_name":"muhanad abdulhamid jaafar","signature":"03e198817a15be82dafe89f0fcc6de01e12554a62fb6bb93d87c10108e82fe03","merchant_identifier":"daHyRFxZ","access_code":"2D2ChCFe3duM0LrDMJUf","order_description":"100 \u0630\u0647\u0628\u064a\u0629 \u0645\u0631\u062c\u0627\u0646","expiry_date":"2010","payment_option":"VISA","customer_ip":"109.177.210.121","language":"ar","eci":"ECOMMERCE","fort_id":"150660853900071782","command":"PURCHASE","response_message":"\u0639\u0645\u0644\u064a\u0629 \u0646\u0627\u062c\u062d\u0629","authorization_code":"679914","merchant_reference":"527998-3132-0","customer_email":"muhanad@supervision-sv.com","currency":"USD","status":"14"}';
    $pData = json_decode($pData, true);
    foreach ($pData as $key => $value)
    {
        $_REQUEST[$key] = $value;
        $_GET[$key] = $value;
    }
}


$language = $_REQUEST['language'] ?? 'en';
if (!in_array($language, ['en','ar']))
{
    $language = 'en';
}

$payFort = new PayfortIntegration();
$payFort->setLanguage($language);
$payment = $payFort->processResponse();


$success = true;
$internalError = false;
if(isset($payment['error_msg']))
{
    $success = false;
}

$orderId = 0;
$userId = 0;
$sourceId = 0;

if (isset($payment['merchant_reference']))
{
    $orderId = preg_split('/-/', $payment['merchant_reference']);
    if($orderId && (count($orderId)==2||count($orderId)==3) && is_numeric($orderId[0]) && is_numeric($orderId[1]))
    {
        if (isset($orderId[2]))
        {
            $sourceId=(int)$orderId[2];
        }
    
        $userId = (int)$orderId[0];
        $orderId = (int)$orderId[1];   
        
    }
    else
    {
        $orderId=0;
    }
}

$db = new DB($config);
//var_dump($orderId);

if($orderId)
{
    if($success)
    {
        $res = $db->get("update t_order set state=?, msg=?, flag=? where id=? and uid=? and state=0 returning id",
                    [2, $payment['fort_id'], $sourceId, $orderId, $userId], TRUE);
    }
    else
    {
        $state = 3;
        if( ($error_code = substr($payment['response_code'],-3))=="072")
        {
            $state = 1;
        }

        $res = $db->get("update t_order set state=?, msg=?, flag=? where id=? and uid=? and state=0 returning id",
                    [$state, $payment['error_msg'], $sourceId, $orderId, $userId], TRUE);
    }
}

$db->queryResultArray("INSERT INTO T_PAYFORT (DATA) VALUES (?)", [json_encode($_POST)], TRUE);


?>