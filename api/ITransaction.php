<?php

class ITransaction {
    private MobileApi $api;
    
    function __construct(MobileApi $_api) {
        $this->api = $_api;

        switch ($this->api->command) {
            case API_IOS_PRODUCTS:
                $this->getProducts();
                break;
            
            case API_IOS_PURCHASE:                
                $this->setTransaction();
                break;
            
            case API_ANDROID_PURCHASE:                
                $this->setAndroidTransaction();
                break;
            
            default:
                break;
        }
    }
    
    
    function startsWith($haystack, $needle) 
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    
    
    function endsWith($haystack, $needle) 
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
    
    
    public function getProducts() : void {
        $opts=$this->api->userStatus($status);
        if ($opts->disallow_purchase===0||$opts->disallow_purchase===false) {
            $rs = $this->api->db->queryResultArray("select id, product_id, name_ar, name_en, usd_price, mcu from product where iphone=1 and blocked=0 order by usd_price");
            $this->api->result['d']=$rs;
        }
        $this->api->getBalance();
        
    }
    
    
    public function setTransaction() {
        
        $product_id = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $transaction_id = filter_input(INPUT_GET, 'transaction_id', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $transaction_date = date("Y-m-d H:i:s", filter_input(INPUT_GET, 'transaction_date', FILTER_VALIDATE_INT)+0);

        $server_id = intval(get_cfg_var('mourjan.server_id')) ;
               
        try {
            error_log(sprintf("%s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), $this->api->getUUID(), $this->api->getUID(), $product_id, $transaction_id, $transaction_date).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
            if (trim($transaction_id)!="(null)" && !$this->startsWith($transaction_id, $product_id)) {
            	//$this->api->sendSMS('9613287168', "iOS purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
            } 
        } 
        catch (Exception $ex) {
            error_log($ex->getTraceAsString());
        }
        
        $product_rs = $this->api->db->queryResultArray("select * from product where product_id=?", [$product_id]);
        
        if (!empty($product_rs)) {
            $this->api->db->setWriteMode();
            $product_rs=$product_rs[0];
            $this->api->result['d']['product'] = $product_rs;
            $transaction_id = trim($transaction_id);
            
            if ($transaction_id!="(null)" && !$this->startsWith($transaction_id, $product_id) && strlen($transaction_id)<19) {
                $coins = $this->api->db->queryResultArray(
                    "UPDATE OR INSERT INTO T_TRAN " .
                    "(UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, XREF_ID, TRANSACTION_ID, TRANSACTION_DATE, PRODUCT_ID, SERVER_ID, GATEWAY) VALUES ".
                    "(?, current_timestamp, 'USD', ?, 0, ?, 0, ?, ?, ?, ?, 'IOS') ".
                    "MATCHING (UID, TRANSACTION_ID, GATEWAY) RETURNING ID", 
                    [$this->api->getUID(), $product_rs['USD_PRICE']+0.0, $product_rs['MCU']+0.0, $transaction_id, $transaction_date, $product_id, $server_id], 
                    TRUE, PDO::FETCH_NUM);
            
                $this->api->result['d']['tran_id'] = $coins[0];
            } 
            else {
                $this->api->result['d']['tran_id'] = 'none';
            }
        }
        
        $this->api->getBalance();  
    }
    
    
    public function setAndroidTransaction() 
    {
        
        $responseData = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_ENCODED, ['options'=>['default'=>'']]);
        $product_id = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
        $transaction_date = date("Y-m-d H:i:s", filter_input(INPUT_POST, 'transaction_date', FILTER_VALIDATE_INT)+0);
        $server_id = intval(get_cfg_var('mourjan.server_id')) ;
        
        require_once 'AndroidMarket/Licensing/ResponseData.php';
        require_once 'AndroidMarket/Licensing/ResponseValidator.php';
        
        define('PUBLIC_KEY', "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB");
        define('PACKAGE_NAME', 'com.mourjan.classifieds');
        
        $valid = false;
        try
        {
            $validator = new AndroidMarket_Licensing_ResponseValidator(PUBLIC_KEY, PACKAGE_NAME);
            $valid = $validator->verify($responseData, $signature);
        }
        catch(Exception $e)
        {            
        }
        
        if ($product_id && $transaction_date && $transaction_id)
        {
            
            $product_rs = $this->api->db->queryResultArray("select * from product where product_id=?", [$product_id]);

            $this->api->result['transaction_id'] = 0;
            if (!empty($product_rs)) 
            {
                $this->api->db->setWriteMode();
                $product_rs=$product_rs[0];
                
                $old_transaction = $this->api->db->queryResultArray("select id from t_tran where TRANSACTION_ID=?", [$transaction_id]);
                
                if ($old_transaction && count($old_transaction))
                {
                    $this->api->result['transaction_id'] = $transaction_id;
                }
                else
                {
                    $coins = $this->api->db->queryResultArray(
                        "INSERT INTO T_TRAN (UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, XREF_ID, TRANSACTION_ID, TRANSACTION_DATE, PRODUCT_ID, SERVER_ID) VALUES ".
                        "(?, current_timestamp, 'USD', ?, 0, ?, 0, ?, ?, ?, ?) RETURNING ID", 
                        [$this->api->getUID(), $product_rs['USD_PRICE']+0.0, $product_rs['MCU']+0.0, $transaction_id, $transaction_date, $product_id, $server_id], 
                        TRUE, PDO::FETCH_NUM);
                    
                    if($coins && $coins[0])
                    {
                        $this->api->result['transaction_id'] = $transaction_id;
                    }
                }
            }

            //$this->api->sendSMS('9613287168', "iOS purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
            $this->api->getCreditTotal();
            
            //notify devices of total update
           
        }
        
    }
    
}
