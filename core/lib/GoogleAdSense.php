<?php
//require_once __DIR__ . '/../../deps/autoload.php';
require_once '/var/www/mourjan/deps/autoload.php';
require_once '/var/www/mourjan/config/cfg.php';
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
        $this->client=new Google_Client();
        $this->client->setApplicationName("Berysoft");
        $this->client->addScope('https://www.googleapis.com/auth/adsense.readonly');
        $this->client->setAccessType('offline');
        $this->client->setAuthConfigFile('/opt/client_secrets.json');
        //$this->client->setPrompt('select_account consent');
       
        $tokenPath='/opt/token.json';
        if (\file_exists($tokenPath)) {
            $accessToken=\json_decode(file_get_contents($tokenPath), true);
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
        include_once '/var/www/mourjan/deps/google/apiclient-services/src/Adsense.php';
        
        $this->service=new Google_Service_AdSense($this->client);
    }
    
    
    public function setAdClientId(string $AAdClientId) : MCAdSense {
        $this->adClientId=$AAdClientId;
        return $this;
    }
    
    
    public function setAccountId(string $AAccountId) : MCAdSense {
        $this->accountId=$AAccountId;
        return $this;
    }
    
    
    private static function displayTree($service, $parentAccount, $level) {
        print str_repeat(' ', $level);
        printf("Account with ID \"%s\" and name \"%s\" was found.\n", $parentAccount['name'], $parentAccount['displayName']);

        $childAccounts = $service->accounts->listChildAccounts($parentAccount['name']);
        if (!empty($childAccounts)) {
            foreach ($childAccounts as $childAccount) {
                self::displayTree($childAccount, $level + 1);
            }
        }
    }
    
  
    public function tt() {
        $c=new Google_Client();
        $c->addScope('https://www.googleapis.com/auth/adsense.readonly');
        $c->setAccessType('offline');
        $c->setAuthConfig('/opt/client_secrets.json');
        $s=new Google_Service_Adsense($c);
        $tokenPath='/opt/token.json';
        if (\file_exists($tokenPath)) {
            $accessToken=\json_decode(file_get_contents($tokenPath), true);
            $c->setAccessToken($accessToken);
        }
        
        //$accessToken=\json_decode(file_get_contents('/opt/client_secrets.json'), true);
        //$c->setAccessToken($accessToken);

        
        if ($c->getAccessToken()) {
            echo 'Start', "\n";
        }
        $separator = str_repeat('=', 80) . "\n";
        print $separator;
        print "Listing all AdSense accounts\n";
        print $separator;

        $optParams['pageSize'] = 20;
        $pageToken = null;
        do {
            $optParams['pageToken'] = $pageToken;
            $result = $s->accounts->listAccounts($optParams);
            $accounts = null;
            if (!empty($result['accounts'])) {
                $accounts = $result['accounts'];
                foreach ($accounts as $account) {
                    printf("Account with ID \"%s\" and name \"%s\" was found.\n", $account['name'], $account['displayName']);
                }
                $pageToken = $result['nextPageToken'];
            } 
            else {
                print "No accounts found.\n";
            }
        } while ($pageToken);
        print "\n";
        
        if (isset($accounts) && !empty($accounts)) {
            echo $accountId=$accounts[0]['name'];
            print $separator;
            printf("Displaying AdSense account tree for %s\n", $accountId);
            print $separator;

            $account=$s->accounts->get($accountId);
            self::displayTree($s, $account, 0);
            //var_dump($account);
    
            print $separator;
            printf("Listing all ad clients for account %s\n", $accountId);
            print $separator;
    
            $pageToken=null;
            $adClients=null;
            do {
                $optParams['pageToken']=$pageToken;
                $result=$s->accounts_adclients->listAccountsAdclients($accountId, $optParams);
                if (!empty($result['adClients'])) {
                    $adClients=$result['adClients'];
                    foreach ($adClients as $adClient) {
                        printf("Ad client for product \"%s\" with ID \"%s\" was found.\n", $adClient['productCode'], $adClient['name']);
                    }
                    $pageToken=$result['nextPageToken'];
                } 
                else {
                    print "No ad clients found.\n";
                }
            } while ($pageToken);
            print "\n";
    
            if (isset($adClients) && !empty($adClients)) {
                // Get an ad client ID, so we can run the rest of the samples.
                $exampleAdClient=end($adClients);
                $adClientId=$exampleAdClient['name'];
                
                print $separator;
                printf("Running report for ad client %s\n", $adClientId);
                print $separator;
                
                $adClientCode=substr($adClientId, strrpos($adClientId, '/') + 1);

                $optParams=array(
                    'startDate.year' => 2021,
                    'startDate.month' => 3,
                    'startDate.day' => 1,
                    'endDate.year' => 2021,
                    'endDate.month' => 3,
                    'endDate.day' => 31,
                    'metrics' => array(
                        'PAGE_VIEWS', 'AD_REQUESTS', 'AD_REQUESTS_COVERAGE', 'CLICKS',
                        'AD_REQUESTS_CTR', 'COST_PER_CLICK', 'AD_REQUESTS_RPM',
                        'ESTIMATED_EARNINGS'),
                    'dimensions' => 'DATE',
                    'orderBy' => '+DATE'
                );

                // Run report.
                $report=$s->accounts_reports->generate($accountId, $optParams);

                if (isset($report) && isset($report['rows'])) {
                    // Display headers.
                    foreach($report['headers'] as $header) {
                        printf('%25s', $header['name']);
                    }
                    print "\n";

                    // Display results.
                    foreach($report['rows'] as $row) {
                        foreach($row['cells'] as $column) {
                            printf('%25s', $column['value']);
                        }
                        print "\n";
                    }
                } 
                else {
                    print "No rows returned.\n";
                }

                print "\n";
            }
        }

    }
    
    
    public function earnings(string $startDate='2021-1-1', string $endDate='2021-1-31', string $pStartDate='2021-1-1', string $pEndDate='2021-1-31') :array {
        $result=[];
        $json_array=['headers'=>[], 'current'=>[], 'previous'=>[]];
        $startDateTime=DateTime::createFromFormat('Y-m-j', $startDate);
        $endDateTime=DateTime::createFromFormat('Y-m-j', $endDate);
        $prevStartDateTime=DateTime::createFromFormat('Y-m-j', $pStartDate);
        $precEndDateTime=DateTime::createFromFormat('Y-m-j', $pEndDate);
        
        \error_log(PHP_EOL."current:\t". $startDateTime->format('j').'.'.$startDateTime->format('n').'.'.$startDateTime->format('Y').' - '.
                $endDateTime->format('j').'.'.$endDateTime->format('n').'.'.$endDateTime->format('Y'). "\n".
                "previous:\t". $prevStartDateTime->format('j').'.'.$prevStartDateTime->format('n').'.'.$prevStartDateTime->format('Y').' - '.
                $precEndDateTime->format('j').'.'.$precEndDateTime->format('n').'.'.$precEndDateTime->format('Y'). "\n");
      
        $optParams=[
            'startDate.day'=>intval($startDateTime->format('j')),
            'startDate.month'=>intval($startDateTime->format('n')),
            'startDate.year'=>intval($startDateTime->format('Y')),
            'endDate.day'=>intval($endDateTime->format('j')),
            'endDate.month'=>intval($endDateTime->format('n')),
            'endDate.year'=>intval($endDateTime->format('Y')),
            'metrics' => [
                'PAGE_VIEWS', 'AD_REQUESTS', 'AD_REQUESTS_COVERAGE', 'CLICKS',
                'AD_REQUESTS_CTR', 'COST_PER_CLICK', 'AD_REQUESTS_RPM', 'ESTIMATED_EARNINGS'],
            'dimensions' => [ 'PRODUCT_CODE' ],
            'orderBy' => [ '-ESTIMATED_EARNINGS' ],
            'currencyCode' => 'USD',
            'limit' => 20,
            'reportingTimeZone' => 'ACCOUNT_TIME_ZONE'
            ];
   
        
        
        $report=$this->service->accounts_reports->generate($this->accountId, $optParams);
        if (isset($report) && isset($report['rows'])) {
            //$result['headers']=$report['headers'];
            $result['current']=$report['totals'];
            $result['currows']=$report['rows'];
            
            foreach($report['headers'] as $header) {
                //printf('%25s', $header['name']);
                $json_array['headers'][]=$header['name'];
            }
            //print "\n";

            // Display results.
            
            foreach($report['rows'] as $row) {
                $i=0;
                foreach($row['cells'] as $column) {
                    //printf('%25s', $column['value']);
                    $json_array['current'][$json_array['headers'][$i]]=$column['value'];
                    $i++;
                }
                //print "\n";
            }
        }
        
        
        $optParams = [
            'startDate.day'=>intval($prevStartDateTime->format('j')),
            'startDate.month'=>intval($prevStartDateTime->format('n')),
            'startDate.year'=>intval($prevStartDateTime->format('Y')),
            'endDate.day'=>intval($precEndDateTime->format('j')),
            'endDate.month'=>intval($precEndDateTime->format('n')),
            'endDate.year'=>intval($precEndDateTime->format('Y')),
            'metrics' => [
                'PAGE_VIEWS', 'AD_REQUESTS', 'AD_REQUESTS_COVERAGE', 'CLICKS',
                'AD_REQUESTS_CTR', 'COST_PER_CLICK', 'AD_REQUESTS_RPM', 'ESTIMATED_EARNINGS'],
            'dimensions' => [ 'PRODUCT_CODE' ],
            'orderBy' => [ '-ESTIMATED_EARNINGS' ],
            'currencyCode' => 'USD',
            'limit' => 20,
            'reportingTimeZone' => 'GOOGLE_TIME_ZONE'
            ];
      
        $report=$this->service->accounts_reports->generate($this->accountId, $optParams);
        if (isset($report) && isset($report['rows'])) {
            //if (!isset($result['headers'])) { $result['headers']=$report['headers']; }
            
            $result['previous']=$report['totals'];
            //$result['prevrows']=$report['rows'];
            
            if (empty($json_array['headers'])) {
                foreach($report['headers'] as $header) {
                    //printf('%25s', $header['name']);
                    $json_array['headers'][]=$header['name'];
                }
                //print "\n";
            }

            // Display results.
            foreach($report['rows'] as $row) {
                $i=0;
                foreach($row['cells'] as $column) {
                    //printf('%25s', $column['value']);
                    //$json_array['previous'][]=$column['value'];
                    $json_array['previous'][$json_array['headers'][$i]]=$column['value'];
                    $i++;
                }
                //print "\n";
            }
           
        }
        
        //print_r($json_array);
        unset($json_array['headers']);
        return $json_array;
        //return $result;      
    }
    
    
    public function salesByCountry(int $year, int $month) : void {
        $startDate=new \DateTime;
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
        ->setAccountId("accounts/pub-2427907534283641")
        ->earnings('2021-1-1', '2021-9-10', '2020-1-1', '2020-12-31');
}
