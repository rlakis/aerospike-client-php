<?php

ini_set('log_errors_max_len', 0);
$sandbox = (get_cfg_var('mourjan.server_id')=='99');
$logfile = '/var/log/mourjan/payfort.log';


// STEP 1: read POST data

// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream.

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) 
{
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
    {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) 
{
   $get_magic_quotes_exists = true;
}

foreach ($myPost as $key => $value) 
{
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) 
    {
        $value = urlencode(stripslashes($value));
    } 
    else 
    {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}


if (!file_exists($logfile)) 
{
    $fh = @fopen($logfile, 'w');
    fclose($fh);
}
error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), json_encode($_POST).PHP_EOL), 3, $logfile);


// STEP 2: POST IPN data back to PayPal to validate
$ppurl = ($sandbox==FALSE) ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
$ch = curl_init($ppurl);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) 
{
    error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);


// STEP 3: Inspect IPN validation result and act accordingly

if (strcmp ($res, "VERIFIED") == 0)
{
    include get_cfg_var('mourjan.path').'/config/cfg.php';
    
    $success = false;
    
    // The IPN is verified, process it:
    // check whether the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process the notification

    // assign posted variables to local variables
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $txn_type = $_POST['txn_type'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
    $user_id = $_POST['custom'];
    $net = $payment_amount - $_POST['mc_fee'];
    
    $credits = 1;
    if ($payment_amount==4.99) $credits = 7;
    elseif ($payment_amount==8.99) $credits = 14;
    elseif ($payment_amount==12.99) $credits = 21;
    elseif ($payment_amount==17.99) $credits = 30;
    elseif ($payment_amount==49.99) $credits = 100;
    
    //$item_name.=" - {$credits}";
    $payment_date = new DateTime($_POST['payment_date']);
    $payment_date->setTimeZone( new DateTimeZone( 'UTC' ));            
    $txn_date = $payment_date->format('Y-m-d H:i:s');

    $db = new DB($config, FALSE);
    
    if ($txn_type=='web_accept')
    {
        
        $old_transaction = $db->queryResultArray("select id from t_tran where TRANSACTION_ID=?", [$txn_id]);
        if ($old_transaction && count($old_transaction) > 0) {
            //do nothing
            //$user->pending['PAYPAL_OLD'] = true;
            
        } else {
            $processed = $db->queryResultArray(
                "INSERT INTO T_TRAN 
                (UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, USD_VALUE, XREF_ID, PRODUCT_ID, TRANSACTION_ID, TRANSACTION_DATE, VALID, SERVER_ID, NET, GATEWAY) VALUES 
                ({$user_id}, current_timestamp, '{$payment_currency}', {$payment_amount}, 0, {$credits}, {$payment_amount}, 0, '{$item_name}', '{$txn_id}', '{$txn_date}', 1, {$config['server_id']}, {$net}, 'PAYPAL')",
                null, TRUE);
        }
        
    }
    // IPN message values depend upon the type of notification sent.
    // To loop through the &_POST array and print the NV pairs to the screen:
    /*foreach($_POST as $key => $value)
    {
        error_log( $key." = ". $value );
    }*/
}
else if (strcmp ($res, "INVALID") == 0)
{
    // IPN invalid, log for manual investigation
    error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), "The response from IPN was: " .$res.PHP_EOL), 3, $logfile);
}


?>