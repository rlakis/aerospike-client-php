<?php
require_once __DIR__ . '/../../deps/autoload.php';
require_once __DIR__ . '/../../config/cfg.php';
// Configure token storage on disk.
// If you want to store refresh tokens in a local disk file, set this to true.
define('STORE_ON_DISK', true, false);
define('TOKEN_FILENAME', 'tokens.dat', false);
//putenv('GOOGLE_APPLICATION_CREDENTIALS=/opt/client_secrets.json');

class MCAdSense {
    private $client;
    private $service;
    private $adClientId;
    private $accountId;        
    
    public function __construct() {
        // Set up authentication.
        $this->client = new Google_Client();
        $this->client->setApplicationName("Berysoft");
        $this->client->addScope('https://www.googleapis.com/auth/adsense.readonly');
        $this->client->setAccessType('offline');
        $this->client->setAuthConfigFile('/opt/client_secrets.json');
        $this->client->setPrompt('select_account consent');
       
        $tokenPath = '/opt/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
        
        // If there is no previous token or it's expired.
        if ($this->client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } 
            else {
                // Request authorization from the user.
                $authUrl=$this->client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode=trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken=$this->client->fetchAccessTokenWithAuthCode($authCode);
                $this->client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
        }
        
        $this->service = new Google_Service_AdSense($this->client);
    }
    
    
    public function setAdClientId(string $AAdClientId) : MCAdSense {
        $this->adClientId=$AAdClientId;
        return $this;
    }
    
    
    public function setAccountId(string $AAccountId) : MCAdSense {
        $this->accountId=$AAccountId;
        return $this;
    }
    
    
    public function earnings(string $startDate='today', string $endDate='today', string $pStartDate='today-1d', string $pEndDate='today-1d') :array {
        $result=[];  
       
        $optParams = [
            'metric' => [
                'PAGE_VIEWS', 'AD_REQUESTS', 'AD_REQUESTS_COVERAGE', 'CLICKS',
                'AD_REQUESTS_CTR', 'COST_PER_CLICK', 'AD_REQUESTS_RPM', 'EARNINGS'],
            'dimension' => 'PRODUCT_CODE',
            'sort' => '-EARNINGS',
            'currency' => 'USD',
            'maxResults' => 20,
            'useTimezoneReporting' => true
            ];
        
        $report = $this->service->accounts_reports->generate($this->accountId, $startDate, $endDate, $optParams);
        if (isset($report) && isset($report['rows'])) {
            $result['headers']=$report['headers'];
            $result['current']=$report['totals'];
            $result['currows']=$report['rows'];
        } 
      
        
        $report = $this->service->accounts_reports->generate($this->accountId, $pStartDate, $pEndDate, $optParams);
        if (isset($report) && isset($report['rows'])) {
            if (!isset($result['headers'])) { $result['headers']=$report['headers']; }
            $result['previous']=$report['totals'];
            $result['prevrows']=$report['rows'];
        }
        return $result;      
    }
    
    
    public function salesByCountry(int $year, int $month) : void {
        $startDate=new DateTime();
        $startDate->setDate($year, $month, 1);
        $endDate=new DateTime("last day of {$year}-{$month}");
        
        echo $startDate->format('Y-m-d'), "\t", $endDate->format('Y-m-d'),  "\n";
        $result=[];
        $optParams=[
            'metric'=>['EARNINGS', 'PAGE_VIEWS_RPM', 'PAGE_VIEWS'],
            'dimension'=> 'COUNTRY_CODE',
            'sort' => '-EARNINGS',
            'currency' => 'AED',
            'maxResults' => 10,
            'useTimezoneReporting' => true
            ];
        $report=$this->service->accounts_reports->generate($this->accountId, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $optParams);       
        
        
        $startDate->add(new DateInterval('P1M'))->add(new DateInterval('P2D'));
        $q ='INSERT INTO SELLING_INVOICE (CLIENT_ACCOUNT_ID, DATED, AGENT_ID, DEFAULT_TAX, PAYMENT_TERM_ID, NB_DAYS, DUE_DATE, BILL_TO, SHIP_TO, SHIPED_BY, SHIPING_COST, TRACKING_REFERENCE, CLIENT_ORDER_REFERENCE, INVOICE_NOTES, INTERNAL_NOTES, REFERENCE, SALES, TAX, RECEIVABLE, PAYMENT_METHOD_ID, BRANCH_ID, DEFAULT_SALES_ACCOUNT_ID) VALUES (';
        $q.="4, '". $startDate->format('d.m.Y'). "', 1, 0, 4, 0, '22.{$month}.{$year}', 'Google Ireland Limited'||ASCII_CHAR(13)||'Google Building Gordon House, 4 Barrow St, Dublin, D04 E5', '', '', 0, '', '', '', '', 0, ";
        $q.=$report['totals'][1].', 0, '.$report['totals'][1].', 3, 1, 17) ';
        $q.='returning SELLING_INVOICE_ID into :id';
        
        $queries=[$q];
        $sellingInvoiceId=4;
        $subTotal=0;
        $pm=0;
        foreach ($report['rows'] as $row) {            
            $q=[];
            $q['SELLING_INVOICE_ID']=':id';
            $q['DESCRIPTION']="'".$this->countryName($row[0])."'";
            $q['QUANTITY']=\round($row[1]/$row[2], 0);
            $q['UNIT_VALUE']=\round($row[1]/$q['QUANTITY'], 5);
            $q['LINE_DISCOUNT']=0;
            $q['LINE_TAX']=0;
            $q['LINE_TOTAL']=$row[1];
            $q['TAX_ID']=0;
            $q['ACCOUNT_ID']=$row[0]==='AE'?19:20;
            $pm+=$q['QUANTITY'];

            $query='INSERT INTO SELLING_INVOICE_LINES ('.\implode(', ', \array_keys($q)).') VALUES ('.\implode(', ', \array_values($q)).')';
            $queries[]=$query;

            $subTotal+=$row[1];
        }
        echo $report['totals'][1], "\t", $subTotal, ' = ', $report['totals'][1]-$subTotal, "\n";
        if ($report['totals'][1]-$subTotal>0) {
            $q=[];
            $q['SELLING_INVOICE_ID']=':id';
            $q['DESCRIPTION']="'Rest of the world'";
            $q['QUANTITY']=\round(($report['totals'][3]/1000)-$pm, 0);
            $q['UNIT_VALUE']=\round(($report['totals'][1]-$subTotal)/$q['QUANTITY'], 5);
            $q['LINE_DISCOUNT']=0;
            $q['LINE_TAX']=0;
            $q['LINE_TOTAL']=$report['totals'][1]-$subTotal;
            $q['TAX_ID']=0;
            $q['ACCOUNT_ID']=20;
            $query='INSERT INTO SELLING_INVOICE_LINES ('.\implode(', ', \array_keys($q)).') VALUES ('.\implode(', ', \array_values($q)).')';
            $queries[]=$query;

        }
        
        print_r($queries);
        $script ='set term #;'.PHP_EOL;
        $script.='EXECUTE BLOCK'.PHP_EOL;
        $script.='as'.PHP_EOL;
        $script.='declare variable id bigint;'.PHP_EOL;
        $script.='begin'.PHP_EOL;
        $script.=implode(";\n", $queries);
        $script.=';'.PHP_EOL;
        $script.='end'.PHP_EOL;
        $script.='#'.PHP_EOL;
        $script.='set term ;#'.PHP_EOL;
        
        echo "\n", $script, "\n";
    }
    
    
    private function countryName(string $code) : string {
        switch ($code) {
            case 'AE':
                return 'United Arab Emirates';
            case 'SA':
                return 'Saudi Arabia';
            case 'KW':
                return 'Kuwait State';
            case 'BH':
                return 'Bahrain Kingdom';
            case 'EG':
                return 'Egypt';
            case 'MA':
                return 'Morocco';
            case 'QA':
                return 'Qatar';
            case 'US':
                return 'United States';
            case 'LB':
                return 'Lebanon';
            case 'OM':
                return 'Oman';
            case 'JO':
                return 'Jordan';
            default:
                return $code;
        }
    }
}


if (php_sapi_name()==='cli') {
    $mcAdSense=new MCAdSense;
    $mcAdSense->setAdClientId("313743502213-delb6cit3u4jrjvrsb4dsihpsoak2emm.apps.googleusercontent.com")
        ->setAccountId("pub-2427907534283641")
        ->salesByCountry(2020, 3);
}
