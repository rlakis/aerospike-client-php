<?php
include get_cfg_var('mourjan.path').'/config/cfg.php';
include_once get_cfg_var('mourjan.path').'/core/model/Db.php';
$db = new Core\Model\Db($config);


$db->queryResultArray("update T_PAYFORT set fort_id=JSONGET('fort_id', data) where fort_id=''", null, TRUE);

$rs = $db->get("select t.ID, t.CURRENCY_ID, t.AMOUNT, cast(t.DATED as date) dated, 
                t.TRANSACTION_DATE, t.TRANSACTION_ID, t.UID, t.XREF_ID, t.net, p.DATA,
                (select first 1 m.mobile 
                 from WEB_USERS_LINKED_MOBILE m 
                 where m.UID=t.UID 
                 and not m.ACTIVATION_TIMESTAMP is null 
                 order by m.ACTIVATION_TIMESTAMP desc) phone
                from T_TRAN t
                left JOIN T_PAYFORT p on p.FORT_ID=t.TRANSACTION_ID
                where t.GATEWAY STARTING with 'PAYFORT'
                and t.TRANSACTION_ID>''
                and t.DATED>='01.02.2018' and t.dated<'01.03.2018'
                order by t.ID
            ");


foreach ($rs as $d) {
    
    $data = json_decode($d['DATA']);
    print_r($data);
    
    if (!isset($data->customer_name)) {
        $data->customer_name = $data->customer_email;
    }
        
    if (empty($d['TRANSACTION_ID']) || !isset($data->fort_id)) {
        continue;
    }
    
    if ($db->get("select 1 from t_invoice where TRANSACTION_ID=? and PAYMENT_GATEWAY='PAYFORT'", [$data->fort_id])) {
        continue;
    }
    
    $sales = $data->amount/100.0;
    $fee = (($data->amount/100.0)*0.35)-0.3;
    $vat = 0.0;
    $customer_phone = 0;
    $customer_country = 'XX';    
    if ($d['PHONE']) {
        $customer_phone = $d['PHONE'];
        if (preg_match("/^961/", "$customer_phone")) {
            $customer_country = 'LB'; 
            $vat=10.0;
            $sales = $sales/1.1;
        }
        else if (preg_match("/^962/", "$customer_phone")) {
            $customer_country = 'JO';            
        }
        else if (preg_match("/^963/", "$customer_phone")) {
            $customer_country = 'SY';            
        }
        else if (preg_match("/^965/", "$customer_phone")) {
            $customer_country = 'KW';            
        }
        else if (preg_match("/^966/", "$customer_phone")) {
            $customer_country = 'SA';            
        }
        else if (preg_match("/^967/", "$customer_phone")) {
            $customer_country = 'YE';            
        }
        else if (preg_match("/^971/", "$customer_phone")) {
            $customer_country = 'AE';            
        }
        else if (preg_match("/^973/", "$customer_phone")) {
            $customer_country = 'BH';            
        }
        else if (preg_match("/^20/", "$customer_phone")) {
            $customer_country = 'EG';            
        }
        else if (preg_match("/^974/", "$customer_phone")) {
            $customer_country = 'QA';            
        }

    }
    
    $db->get(
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
             $data->customer_name, $data->customer_email, $customer_phone, $customer_country, $data->currency, 
             $sales, $vat, $fee, $d['NET'],
             $data->order_description, $data->card_number, $data->expiry_date,
             $data->customer_ip, $d['XREF_ID'], 'PAYFORT'], TRUE);
}


/*
 * 
update T_INVOICE i set i.CUSTOMER_PHONE=(select first 1 m.mobile from WEB_USERS_LINKED_MOBILE m where m.UID=i.UID order by m.ACTIVATION_TIMESTAMP desc)
where i.CUSTOMER_PHONE=0
and EXISTS (select m.mobile from WEB_USERS_LINKED_MOBILE m where m.UID=i.UID)
 */
    
?>
