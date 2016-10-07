<?php
ini_set('log_errors_max_len', 0);
$sandbox = (get_cfg_var('mourjan.server_id')=='99');
$logfile = '/var/log/mourjan/payfort.log';

if (!file_exists($logfile)) 
{
    $fh = @fopen($logfile, 'w');
    fclose($fh);
}
error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), json_encode($_POST).PHP_EOL), 3, $logfile);


include_once get_cfg_var('mourjan.path') . '/config/cfg.php';
include_once $config['dir']. '/core/model/Db.php';
include_once $config['dir'].'/core/lib/PayfortIntegration.php';


$language = $_REQUEST['language'];
if(!in_array($language, ['en','ar'])){
    $language = 'en';
}
$payFort = new PayfortIntegration();
$payFort->setLanguage($language);
$payment = $payFort->processResponse();


$success = true;
$internalError = false;
if(isset($payment['error_msg'])){
    $success = false;
}

$orderId = 0;
$userId = 0;
if(isset($payment['merchant_reference'])){
    $orderId = preg_split('/-/', $payment['merchant_reference']);
    if($orderId && count($orderId)==2 && is_numeric($orderId[0]) && is_numeric($orderId[1])){
        
        $userId = (int)$orderId[0];
        $orderId = (int)$orderId[1];
        
    }else{
        $orderId=0;
    }
}
if($orderId){
    $db = new DB($config);
    if($success){
        $res = $db->queryResultArray(
                    "update t_order set state = ?, msg = ? where id = ? and uid = ? and state = 0 returning id",
                    [2, $payment['fort_id'], $orderId, $userId], TRUE);
    }else{
        $state = 3;
        if( ($error_code = substr($payment['response_code'],-3))=="072"){
            $state = 1;
        }
        $res = $db->queryResultArray(
                    "update t_order set state = ?, msg = ? where id = ? and uid = ? and state = 0 returning id",
                    [$state, $payment['error_msg'], $orderId, $userId], TRUE);
    }
}
?>