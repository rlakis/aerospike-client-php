<?php

include_once get_cfg_var('mourjan.path').'/core/lib/Payfort.php';

class MCPayfort extends PayfortIntegration
{
    const LOG_FILE = '/var/log/mourjan/payfort.log';
    
    public $token_name = null;
    public $merchantReference = '';
    
    public function __construct() 
    {
        parent::__construct();
        $this->projectUrlPath = '/buyu';
        $this->sandboxMode = (get_cfg_var('mourjan.server_id')=='99');
        if ($this->sandboxMode)
        {
            $this->merchantIdentifier   = "AUCZNGGy";
            $this->accessCode           = 'ou8rcz98spCiypVgz67U';
            $this->shaIn                = "ky9BWdcbDZqn2c";
            $this->shaOut               = "pWgawXBckxLbuf";
        }
        else
        {
            $this->merchantIdentifier   = 'daHyRFxZ';
            $this->accessCode           = '2D2ChCFe3duM0LrDMJUf';
            $this->shaIn                = 'ky9BWdcbDZqn2c';
            $this->shaOut               = 'pWgawXBckxLbuf';
        }
    }
    
        
    public function setMerchantReference($referenceKey)
    {
        $this->merchantReference = $referenceKey;
        return $this;
    }
    
    
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    
    public function setCustomerEmail($email)
    {
        $this->customerEmail = $email;
        return $this;
    }
    
    
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
        return $this;
    }
    
    
    public function setLanguage($lang='en')
    {
        $this->language = $lang;
        return $this;
    }
    
    
    public function setCommand($command = 'AUTHORIZATION')
    {
        $this->command = $command;        
        return $this;
    }
    
    
    public function setTokenName($token)
    {
        $this->token_name = $token;
    }
    
    
    public function generateMerchantReference()
    {
        return $this->merchantReference;
    }
    
    
    public function log($messages) 
    {
        if (!file_exists(static::LOG_FILE)) 
        {
            $fh = @fopen(static::LOG_FILE, 'w');
            fclose($fh);
        }
        error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), $messages.PHP_EOL), 3, static::LOG_FILE);
    }
}
