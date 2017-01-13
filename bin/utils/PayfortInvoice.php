<?php
include get_cfg_var('mourjan.path').'/config/cfg.php';
include_once get_cfg_var('mourjan.path').'/core/model/Db.php';
$db = new Db($config);
$rs = $db->queryResultArray(
                "select t.ID, t.CURRENCY_ID, t.AMOUNT, cast(t.DATED as date) dated, t.TRANSACTION_DATE, t.TRANSACTION_ID, t.UID, t.XREF_ID, t.net, p.DATA
                from T_TRAN t
                left JOIN T_PAYFORT p on p.FORT_ID=t.TRANSACTION_ID
                where t.GATEWAY = 'PAYFORT'
                and t.DATED between '01.10.2016' and '31.12.2016 23:59:59'
                order by t.ID
            ");


foreach ($rs as $d) 
{
    
    $data = json_decode($d['DATA']);
    print_r($data);
    if (!isset($data->customer_name))
    {
        $data->customer_name = $data->customer_email;
    }
    $db->queryResultArray(
            "INSERT INTO T_INVOICE (
                UID, INVOICE_DATE, INVOICE_NO, PAYMENT_OPTION, TRANSACTION_ID, 
                CUSTOMER_NAME, CUSTOMER_EMAIL, CUSTOMER_PHONE, CUSTOMER_COUNTRY_ID, 
                CURRENCY_ID, SALES, VAT_PERCENTAGE, PAYMENT_FEES, NET_REVENUE, 
                DESCRIPTION, CARD_NUMBER, CARD_EXPIRY, 
                CUSTOMER_IP, ORDER_ID, PAYMENT_GATEWAY)
             VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, 
                ?, ?, ?,
                ?, ?, ?)", 
            [$d['UID'], $d['DATED'], 0, $data->payment_option, $data->fort_id, 
             $data->customer_name, $data->customer_email, 0, 'XX', $data->currency, 
             $data->amount/100.0, 0, (($data->amount/100.0)*0.35)-0.3, $d['NET'],
             $data->order_description, $data->card_number, $data->expiry_date,
             $data->customer_ip, $d['XREF_ID'], 'PAYFORT'], TRUE);
}
    
?>
