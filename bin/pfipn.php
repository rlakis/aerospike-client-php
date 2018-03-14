<?php
ini_set('log_errors_max_len', 0);
include_once get_cfg_var('mourjan.path').'/deps/autoload.php';
use Core\Model\NoSQL;

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
use Core\Model\DB;

$language = $_REQUEST['language'] ?? 'en';
if (!in_array($language, ['en','ar']))
{
    $language = 'en';
}

$payFort = new PayfortIntegration();
$payFort->setLanguage($language);

$db = new DB($config);

if (php_sapi_name()=='cli')
{
    if ($argc==2)
    {
        print_r($argv);
        $res = $db->get("select id, data from t_payfort where id=?", [$argv[1]], TRUE);
        if ($res && count($res))
        {
            //print_r($res);
            $pData = json_decode($res[0]['DATA'], true);
            foreach ($pData as $key => $value)
            {
                $_REQUEST[$key] = $value;
                $_GET[$key] = $value;
            }
            print_r($_GET);
        } else {
            return ;
        }
    }
    
    //if (false)
    //{
    //    $pData = '{"amount":"1799","response_code":"14000","card_number":"498778******2930","card_holder_name":"AHMED ISMAIL","signature":"a22f7c4b1e9146e2a92c8ef9197820ffebdda995b84ee28a36a5d41be890576c","merchant_identifier":"daHyRFxZ","access_code":"2D2ChCFe3duM0LrDMJUf","order_description":"30 \u0630\u0647\u0628\u064a\u0629 \u0645\u0631\u062c\u0627\u0646","payment_option":"VISA","expiry_date":"2210","customer_ip":"31.215.66.197","language":"ar","eci":"ECOMMERCE","fort_id":"152070282500090963","command":"PURCHASE","response_message":"\u0639\u0645\u0644\u064a\u0629 \u0646\u0627\u062c\u062d\u0629","merchant_reference":"775021-5278-0","authorization_code":"165273","customer_email":"ahmed.sobieh@yahoo.com","token_name":"66D5D8D738B0B69FE0530100007F4C2F","currency":"USD","status":"14"}';
    //    $pData = json_decode($pData, true);
    //    foreach ($pData as $key => $value)
    //    {
    //        $_REQUEST[$key] = $value;
    //        $_GET[$key] = $value;
    //    }
    //}
}



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
    //var_dump($orderId);
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

//var_dump($orderId);

if($orderId)
{
    if($success)
    {
        $res = $db->get("update t_order set state=?, msg=?, flag=? where id=? and uid=? and state=0 returning id",
                    [2, $payment['fort_id'], $sourceId, $orderId, $userId], TRUE);
        if (isset($payment['token_name']))    
        {
            Core\Model\NoSQL::getInstance()->setUserBin($userId, \Core\Model\ASD\USER_PAYFORT_TOKEN, $payment['token_name']);
        }
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

if (isset($_POST) && count($_POST)>0)
{
    $fort_id = isset($payment) && isset($payment['fort_id']) ? $payment['fort_id'] : '';
    $db->queryResultArray("INSERT INTO T_PAYFORT (DATA, FORT_ID) VALUES (?, ?)", [json_encode($_POST), $fort_id], TRUE);
}


?>