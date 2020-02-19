<?php
require_once __DIR__ . '/../../deps/autoload.php';

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
                $authUrl = $this->client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
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
}

/*
$mcAdSense = new MCAdSense;
$mcAdSense->setAdClientId("313743502213-delb6cit3u4jrjvrsb4dsihpsoak2emm.apps.googleusercontent.com")
        ->setAccountId("pub-2427907534283641")
        ->earnings();
 * 
 * 
 */