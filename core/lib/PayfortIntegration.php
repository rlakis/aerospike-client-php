<?php
/**
 * @copyright Copyright PayFort 2012-2016 
 * 
 */
class PayfortIntegration {
    private string $gatewayHost='https://checkout.payfort.com/';
    private string $gatewaySandboxHost='https://sbcheckout.payfort.com/';
    private string $language='en';
    /**
     * @var string your Merchant Identifier account (mid)
     */
    //public $merchantIdentifier = 'AUCZNGGy';Sandbox
    public string $merchantIdentifier = 'daHyRFxZ';
    /**
     * @var string your Merchant Identifier account (mid)
     */
    private string $merchantReference='';
    /**
     * @var string your access code
     */
    //public $accessCode         = 'ou8rcz98spCiypVgz67U';//Sandbox
    public string $accessCode         = '2D2ChCFe3duM0LrDMJUf';
    /**
     * @var string sha in passphrase
     */
    public string $SHARequestPhrase    = 'ky9BWdcbDZqn2c';
    /**
     * @var string sha out passphrase
     */
    public string $SHAResponsePhrase   = 'pWgawXBckxLbuf';
    /**
     * @var string hash algorith
     */
    public string $SHAType       = 'sha256';
    
    private string $command='AUTHORIZATION';
    /**
     * @var decimal order amount
     */
    private float $amount=0;
    /**
     * @var string order currency
     */
    private string $currency='USD';
    
    /**
     * @var string item name
     */
    private $itemName='';
    /**
     * @var string item bill name
     */
    public $itemBillName           = '';
    /**
     * @var string you can change it to your email
     */
    private string $customerEmail='';
    /**
     * @var boolean for live account change it to false
     */
    private bool $sandboxMode=false;
    /**
     * @var string  project root folder
     * change it if the project is not on root folder.
     */
    public $projectUrlPath     = '/buyu'; 
    
    
    public string $token_name;
    
    function __construct() {
        //new account sandbox UAE
        $this->sandboxMode=true;
        $this->merchantIdentifier='eaa96024';
        $this->accessCode='k1gon6bzv6wxDyLtQSTg';
        $this->SHARequestPhrase='$2y$10$UNBEbDRl6';
        $this->SHAResponsePhrase='$2y$10$HMJVq7MTz';
    }


    public function setSandBoxMode(bool $value) : void {
        $this->log(__FUNCTION__);
        $this->sandboxMode=$value;
        if ($this->sandboxMode) {
            $this->merchantIdentifier='AUCZNGGy';
            $this->accessCode='ou8rcz98spCiypVgz67U';
        } 
        else {
            $this->merchantIdentifier='daHyRFxZ';
            $this->accessCode='2D2ChCFe3duM0LrDMJUf';            
        }
    }
    
    
    public function setMerchantReference(string $order_id) : PayfortIntegration {
        $this->merchantReference=$order_id;
        return $this;
    }
    
    
    public function setAmount(float $amount) : PayfortIntegration {
        $this->amount=$amount;
        return $this;
    }
    
    
    public function setCurrency(string $currency='USD') : PayfortIntegration {
        $this->currency=$currency;
        return $this;
    }
    
    
    public function setCustomerEmail(string $email) : PayfortIntegration {
        $this->customerEmail=$email;
        return$this;
    }
    
    
    public function setItemName(string $itemName) : PayfortIntegration {
        $this->itemName=$itemName;
        return $this;
    }
    
    
    public function setLanguage(string $lang='en') : PayfortIntegration {
        $this->language=$lang;
        return $this;
    }
       
    
    public function setCommand(string $command='AUTHORIZATION') : PayfortIntegration {
        $this->command=$command;
        return $this;
    }

    
    public function setAsAuthorizationRequest() : PayfortIntegration {
        return $this->setCommand('AUTHORIZATION');
    }
    
    
    public function setAsPurchaseRequest() : PayfortIntegration {
        return $this->setCommand('PURCHASE');
    }
    
    
    public function processRequest(string $paymentMethod='creditcard') {
        $this->log(__FUNCTION__ . $paymentMethod);
        if ($paymentMethod==='cc_merchantpage') {
            $merchantPageData = $this->getMerchantPageData();
            $postData = $merchantPageData['params'];
            $gatewayUrl = $merchantPageData['url'];
        }
        else {
            $data = $this->getRedirectionData($paymentMethod);
            $postData = $data['params'];
            $gatewayUrl = $data['url'];
        }
        $form = $this->getPaymentForm($gatewayUrl, $postData);
        echo json_encode(['form' => $form, 'url' => $gatewayUrl, 'params' => $postData, 'paymentMethod' => $paymentMethod]);
        exit;
    }

    
    public function getRedirectionData(string $paymentMethod='') : array {
        //$merchantReference = $this->generateMerchantReference();
        if ($this->sandboxMode) {
            $gatewayUrl=$this->gatewaySandboxHost.'FortAPI/paymentPage';
        }
        else {
            $gatewayUrl=$this->gatewayHost.'FortAPI/paymentPage';
        }

        if ($paymentMethod==='sadad') {  $this->currency='SAR';  }
        
        $postData = [
            'amount'                => $this->convertFortAmount($this->amount, $this->currency),
            'currency'              => \strtoupper($this->currency),
            'merchant_identifier'   => $this->merchantIdentifier,
            'access_code'           => $this->accessCode,
            'merchant_reference'    => $this->merchantReference,
            'customer_email'        => $this->customerEmail,
            //'customer_name'         => trim($order_info['b_firstname'].' '.$order_info['b_lastname']),
            'command'               => $this->command,
            'language'              => $this->language,
            'return_url'            => $this->getUrl('?newlook=1'), 
            'order_description'     => $this->itemName,
        ];

        if (!$this->sandboxMode && isset($this->token_name) && !empty($this->token_name)) {
            $postData['token_name']=$this->token_name;
        }
        
        if ($paymentMethod==='sadad') {
            $postData['payment_option']='SADAD';
        }
        elseif ($paymentMethod==='naps') {
            $postData['payment_option']='NAPS';
            $postData['order_description']=$this->itemName;
        }
        elseif ($paymentMethod==='installments') {
            $postData['installments']='STANDALONE';
            $postData['command']='PURCHASE';
        }
        $postData['signature']=$this->calculateSignature($postData, 'request');
        
        $debugMsg="Fort Redirect Request Parameters \n".\print_r($postData, 1);
        $this->log($debugMsg);
        return ['url' => $gatewayUrl, 'params' => $postData];
    }
    
    
    public function getMerchantPageData()
    {
        $merchantReference = $this->generateMerchantReference();
        $returnUrl = $this->getUrl('route.php?r=merchantPageReturn');
        if(isset($_GET['3ds']) && $_GET['3ds'] == 'no') {
            $returnUrl = $this->getUrl('route.php?r=merchantPageReturn&3ds=no');
        }
        $iframeParams              = array(
            'merchant_identifier' => $this->merchantIdentifier,
            'access_code'         => $this->accessCode,
            'merchant_reference'  => $merchantReference,
            'service_command'     => 'TOKENIZATION',
            'language'            => $this->language,
            'return_url'          => $returnUrl,
        );
        $iframeParams['signature'] = $this->calculateSignature($iframeParams, 'request');

        if ($this->sandboxMode) {
            $gatewayUrl = $this->gatewaySandboxHost . 'FortAPI/paymentPage';
        }
        else {
            $gatewayUrl = $this->gatewayHost . 'FortAPI/paymentPage';
        }
        $debugMsg = "Fort Merchant Page Request Parameters \n".print_r($iframeParams, 1);
        $this->log($debugMsg);
        
        return array('url' => $gatewayUrl, 'params' => $iframeParams);
    }
    
    
    public function getPaymentForm($gatewayUrl, $postData)
    {
        $form = '<form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post" action="' . $gatewayUrl . '">';
        foreach ($postData as $k => $v) {
            $form .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        }
        $form .= '<input type="submit" id="submit">';
        return $form;
    }
    
    
    function http_strip_query_param($url)
    {
        $pieces = parse_url($url);
        $query = [];
        if ($pieces['query']) {
            parse_str($pieces['query'], $query);
        }

        return $query;
    }

    
    public function processResponse() : array {
        $fortParams=\array_merge($_GET, $_POST);
        
        $debugMsg="Fort Redirect Response Parameters \n".\print_r($fortParams, 1);
        $this->log($debugMsg);
        
        $p=[];
        $reason        = '';
        $response_code = '';
        $success = true;
        if (empty($fortParams)) {
            $success = false;
            $reason = "Invalid Response Parameters";
            $debugMsg = $reason;
            $this->log($debugMsg);
        }
        else {
            //validate payfort response
            $params = $fortParams;
            $responseSignature = $fortParams['signature'];
            $merchantReference = $params['merchant_reference'];
            unset($params['r']);
            unset($params['signature']);
            unset($params['integration_type']);
            $calculatedSignature = $this->calculateSignature($params, 'response');            
            $success       = true;
            $reason        = '';

            if ($responseSignature != $calculatedSignature) {
                $success = false;
                $reason  = 'Invalid signature.';
                $debugMsg = sprintf('Invalid Signature. Calculated Signature: %1s, Response Signature: %2s', $responseSignature, $calculatedSignature);
                $this->log($debugMsg);
            }
            else {
                $response_code    = $params['response_code'];
                $response_message = $params['response_message'];
                $status           = $params['status'];
                if (substr($response_code, 2) != '000') {
                    $success = false;
                    $reason  = $response_message;
                    $debugMsg = $reason;
                    $this->log($debugMsg);
                }
            }
            $p = $params;
        }
        
        if (!$success) {
            $p['error_msg'] = $reason;
            //$return_url = $this->getUrl('error.php?'.http_build_query($p));
        }
        else {
            //$return_url = $this->getUrl('success.php?'.http_build_query($params));
        }
        return $p;
        //echo "<html><body onLoad=\"javascript: window.top.location.href='" . $return_url . "'\"></body></html>";
        //exit;
    }

    
    public function processMerchantPageResponse()
    {
        $fortParams = array_merge($_GET, $_POST);

        $debugMsg = "Fort Merchant Page Response Parameters \n".print_r($fortParams, 1);
        $this->log($debugMsg);
        $reason = '';
        $response_code = '';
        $success = true;
        if(empty($fortParams)) {
            $success = false;
            $reason = "Invalid Response Parameters";
            $debugMsg = $reason;
            $this->log($debugMsg);
        }
        else{
            //validate payfort response
            $params        = $fortParams;
            $responseSignature     = $fortParams['signature'];
            unset($params['r']);
            unset($params['signature']);
            unset($params['integration_type']);
            unset($params['3ds']);
            $merchantReference = $params['merchant_reference'];
            $calculatedSignature = $this->calculateSignature($params, 'response');
            $success       = true;
            $reason        = '';

            if ($responseSignature != $calculatedSignature) {
                $success = false;
                $reason  = 'Invalid signature.';
                $debugMsg = sprintf('Invalid Signature. Calculated Signature: %1s, Response Signature: %2s', $responseSignature, $calculatedSignature);
                $this->log($debugMsg);
            }
            else {
                $response_code    = $params['response_code'];
                $response_message = $params['response_message'];
                $status           = $params['status'];
                if (substr($response_code, 2) != '000') {
                    $success = false;
                    $reason  = $response_message;
                    $debugMsg = $reason;
                    $this->log($debugMsg);
                }
                else {
                    $success         = true;
                    $host2HostParams = $this->merchantPageNotifyFort($fortParams);
                    $debugMsg = "Fort Merchant Page Host2Hots Response Parameters \n".print_r($fortParams, 1);
                    $this->log($debugMsg);
                    if (!$host2HostParams) {
                        $success = false;
                        $reason  = 'Invalid response parameters.';
                        $debugMsg = $reason;
                        $this->log($debugMsg);
                    }
                    else {
                        $params    = $host2HostParams;
                        $responseSignature = $host2HostParams['signature'];
                        $merchantReference = $params['merchant_reference'];
                        unset($params['r']);
                        unset($params['signature']);
                        unset($params['integration_type']);
                        $calculatedSignature = $this->calculateSignature($params, 'response');
                        if ($responseSignature != $calculatedSignature) {
                            $success = false;
                            $reason  = 'Invalid signature.';
                            $debugMsg = sprintf('Invalid Signature. Calculated Signature: %1s, Response Signature: %2s', $responseSignature, $calculatedSignature);
                            $this->log($debugMsg);
                        }
                        else {
                            $response_code = $params['response_code'];
                            if ($response_code == '20064' && isset($params['3ds_url'])) {
                                $success = true;
                                $debugMsg = 'Redirect to 3DS URL : '.$params['3ds_url'];
                                $this->log($debugMsg);
                                echo "<html><body onLoad=\"javascript: window.top.location.href='" . $params['3ds_url'] . "'\"></body></html>";
                                exit;
                                //header('location:'.$params['3ds_url']);
                            }
                            else {
                                if (substr($response_code, 2) != '000') {
                                    $success = false;
                                    $reason  = $host2HostParams['response_message'];
                                    $debugMsg = $reason;
                                    $this->log($debugMsg);
                                }
                            }
                        }
                    }
                }
            }
            
            if(!$success) {
                $p = $params;
                $p['error_msg'] = $reason;
                $return_url = $this->getUrl('error.php?'.http_build_query($p));
            }
            else{
                $return_url = $this->getUrl('success.php?'.http_build_query($params));
            }
            echo "<html><body onLoad=\"javascript: window.top.location.href='" . $return_url . "'\"></body></html>";
            exit;
        }
    }

    
    public function merchantPageNotifyFort($fortParams) {
        //send host to host

        if ($this->sandboxMode) {
            $gatewayUrl = $this->gatewaySandboxHost . 'FortAPI/paymentPage';
        }
        else {
            $gatewayUrl = $this->gatewayHost . 'FortAPI/paymentPage';
        }

        $postData      = array(
            'merchant_reference'  => $fortParams['merchant_reference'],
            'access_code'         => $this->accessCode,
            'command'             => $this->command,
            'merchant_identifier' => $this->merchantIdentifier,
            'customer_ip'         => $_SERVER['REMOTE_ADDR'],
            'amount'              => $this->convertFortAmount($this->amount, $this->currency),
            'currency'            => strtoupper($this->currency),
            'customer_email'      => $this->customerEmail,
            'customer_name'       => 'John Doe',
            'token_name'          => $fortParams['token_name'],
            'language'            => $this->language,
            'return_url'          => $this->getUrl('?payfort=process'),
        );
        if(isset($fortParams['3ds']) && $fortParams['3ds'] == 'no') {
            $postData['check_3ds'] = 'NO';
        }
        
        //calculate request signature
        $signature             = $this->calculateSignature($postData, 'request');
        $postData['signature'] = $signature;

        $debugMsg = "Fort Host2Host Request Parameters \n".print_r($postData, 1);
        $this->log($debugMsg);
        
        if ($this->sandboxMode) {
            $gatewayUrl = $this->gatewaySandboxHost . 'FortAPI/paymentApi';
        }
        else {
            $gatewayUrl = $this->gatewayHost . 'FortAPI/paymentApi';
        }
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        $useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;charset=UTF-8',
                //'Accept: application/json, application/*+json',
                //'Connection:keep-alive'
        ));
        curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, "compress, gzip");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects		
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect
        //curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiCallTimeout']); // timeout in seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);

        //$response_data = array();
        //parse_str($response, $response_data);
        curl_close($ch);

        $array_result = json_decode($response, true);
        
        $debugMsg = "Fort Host2Host Response Parameters \n".print_r($array_result, 1);
        $this->log($debugMsg);
        
        if (!$response || empty($array_result)) {
            return false;
        }
        return $array_result;
    }

    
    /**
     * calculate fort signature
     * @param array $arrData
     * @param string $signType request or response
     * @return string fort signature
     */
    public function calculateSignature($arrData, $signType = 'request') {
        $this->log(__FUNCTION__);
        $shaString = '';
        ksort($arrData);
        foreach ($arrData as $k => $v) {
            $shaString .= "$k=$v";
        }

        if ($signType == 'request') {
            $shaString = $this->SHARequestPhrase . $shaString . $this->SHARequestPhrase;
        }
        else {
            $shaString = $this->SHAResponsePhrase . $shaString . $this->SHAResponsePhrase;
        }
        $signature = hash($this->SHAType, $shaString);
        
        
        $this->log($signature);
        return $signature;
    }
   
    
    /**
     * Convert Amount with dicemal points
     * @param decimal $amount
     * @param string  $currencyCode
     * @return decimal
     */
    public function convertFortAmount(float $amount, string $currencyCode) : int {
        $new_amount=0;
        $total=$amount;
        $decimalPoints=$this->getCurrencyDecimalPoints($currencyCode);
        $new_amount=round($total, $decimalPoints) * (pow(10, $decimalPoints));
        return $new_amount;
    }
    

    public  function castAmountFromFort($amount, $currencyCode) {
        $decimalPoints    = $this->getCurrencyDecimalPoints($currencyCode);
        //return $amount / (pow(10, $decimalPoints));
        $new_amount = round($amount, $decimalPoints) / (pow(10, $decimalPoints));
        return $new_amount;
    }
    
    /**
     * 
     * @param string $currency
     * @param integer 
     */
    public function getCurrencyDecimalPoints(string $currency) : int {
        $decimalPoint=2;
        $arrCurrencies=['JOD'=>3, 'KWD'=>3, 'OMR'=>3, 'TND'=>3, 'BHD'=>3, 'LYD'=>3, 'IQD'=>3];
        if (isset($arrCurrencies[$currency])) {
            $decimalPoint=$arrCurrencies[$currency];
        }
        return $decimalPoint;
    }
    

    public function getUrl(string $path='') : string {  
        $url='https://'.$_SERVER['HTTP_HOST'].$this->projectUrlPath.'/'.($this->language==='ar'?'':$this->language.'/').$path;
        return $url;
    }
    

    public function generateMerchantReference() {
        return rand(0, 9999999999);
    }
    
    /**
     * Log the error on the disk
     */
    public function log($messages) { 
        $logfile = '/var/log/mourjan/payfort.log';
        if (!file_exists($logfile)) 
        {
            $fh = @fopen($logfile, 'w');
            fclose($fh);
        }
        error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), $messages.PHP_EOL), 3, $logfile);
    }
    
    
    /**
     * 
     * @param type $po payment option
     * @return string payment option name
     */
    function getPaymentOptionName($po) {
        switch($po) {
            case 'creditcard' : return 'Credit Cards';
            case 'cc_merchantpage' : return 'Credit Cards (Merchant Page)';
            case 'installments' : return 'Installments';
            case 'sadad' : return 'SADAD';
            case 'naps' : return 'NAPS';
            default : return '';
        }
    }
}
