<?php
require_once __DIR__.'/../../deps/autoload.php';
require_once __DIR__.'/../model/NoSQL.php';
//define('STORE_ON_DISK', true, false);
//define('TOKEN_FILENAME', 'tokens.dat', false);

use Core\Model\NoSQL;

class MCGoogleAnalytics {
    private $client;
    private $service;
    private $adClientId;
    private $accountId;       
    private $analytics;       
    
    private $view_id_site = '19776330';
    
    private $iso = [
            'lb'=>1,
            'ae'=>2,
            'bh'=>3,
            'sa'=>4,
            'eg'=>5,
            //'sy'=>6,
            'kw'=>7,
            'jo'=>8,
            'qa'=>9, 
            //'sd'=>10,
            'tn'=>11,
            'ye'=>12,
            'dz'=>15,
            'iq'=>106,
            //'ly'=>122,
            'ma'=>145,
            'om'=>161
        ];
    
    public function __construct() {
        // Set up authentication.
        $this->client = new Google_Client();
        $this->client->setApplicationName("Berysoft");
        $this->client->setAuthConfig('/opt/php-service-key.json');
        $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        
        //$this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        //$this->client->setAuthConfigFile('/opt/client_secrets.json');
        
        //$this->client->addScope('https://www.googleapis.com/auth/analytics.readonly');
        
        //$this->client->setAccessType('offline');
        //$this->client->setPrompt('select_account consent');
       // https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A19776330&start-date=30daysAgo&end-date=yesterday&metrics=ga%3Apageviews&dimensions=ga%3ApagePath&sort=-ga%3Apageviews
        
        /*$tokenPath = '/opt/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }*/
        
        $this->analytics = new Google_Service_AnalyticsReporting($this->client);       
        
        //$request = $this->getTestQuery();
        //$request = $this->getDimension();
        

        
        /*
        for ( $reportIndex = 0; $reportIndex < count( $res ); $reportIndex++ ) {
            $report = $res[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            print_r($rows);
        }*/
        
    }
    
    
    public function topPageViews() : MCGoogleAnalytics {
        $request = $this->getPathViews();
        $results = $this->getResultArray($request);
        $totals=$this->parsePathViewsResultArray($results);
        $this->processPathViewsTotals($totals);
        return $this;
    }
    
    public function customerHits() : MCGoogleAnalytics {
        $request = $this->getCustomerInterestHits();
        $results = $this->getResultArray($request);
        $dimensions = $this->parseCustomerHitsArray($results);
        $this->processCustomerHits($dimensions);
        return $this;
    }
    
    
    public function getResultArray($request){
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        return $this->analytics->reports->batchGet( $body );
    }
    
    
    public function getPathViews() : Google_Service_AnalyticsReporting_ReportRequest {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:pageviews");
        $sessions->setAlias("pageviews");
        
        $dimensionPath = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionPath->setName("ga:pagePath");
        
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName("ga:pageviews");
        $ordering->setOrderType("VALUE");   
        $ordering->setSortOrder("DESCENDING");
        
        // Create the DimensionFilter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:pagePath');
        $dimensionFilter->setOperator('REGEXP');
        $dimensionFilter->setExpressions(array('[^?]'));

        // Create the DimensionFilterClauses
        $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setFilters(array($dimensionFilter));
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($dimensionPath));
        $request->setMetrics(array($sessions));
        $request->setDimensionFilterClauses(array($dimensionFilterClause));
        $request->setOrderBys($ordering);
        $request->setPageSize(30000);
        return $request;
    }
    
    
    public function getCustomerInterestHits() : Google_Service_AnalyticsReporting_ReportRequest {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:hits");
        $sessions->setAlias("hits");
        
        $dimensionCountry = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCountry->setName("ga:dimension4");
        
        $dimensionCity = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCity->setName("ga:dimension5");
        
        $dimensionSection = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionSection->setName("ga:dimension3");
        
        $dimensionCustomer = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCustomer->setName("ga:dimension7");
        
        $dimensionPurpose = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionPurpose->setName("ga:dimension8");
        
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:dimension4');
        $dimensionFilter->setOperator('EXACT');
        $dimensionFilter->setExpressions('Global');
        $dimensionFilter->setNot(true);
        
        $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setFilters(array($dimensionFilter));
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions([$dimensionCountry, $dimensionCity, $dimensionSection, $dimensionPurpose, $dimensionCustomer]);
        $request->setMetrics(array($sessions));
        $request->setDimensionFilterClauses(array($dimensionFilterClause));
        //$request->setOrderBys($ordering);
        $request->setPageSize(2000);
        return $request;
    }
    
    
    public function cleanUpPath($path){
        $path=\rtrim(\str_replace('www.mourjan.com', '', \trim($path)), "/");
        $path=\preg_replace('/\/\d+$/', '', $path);
        $path=\preg_replace('/\/en$/', '', $path);
        return $path;
    }
    
    public function parseCustomerHitsArray($results) {
        $rows = [];
        $records = [];
        for ( $reportIndex = 0; $reportIndex < count( $results ); $reportIndex++ ) {
            $report = $results[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            break;
        }
        
        $pk=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'cities-dic');
        $cities=NoSQL::instance()->getBins($pk)['data'];
        $pk=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'sections');
        $sections=NoSQL::instance()->getBins($pk)['data'];
        
        $citiesByUri = [];
        $sectionsByName = [];
        
        foreach($cities as $id => $rec){
            $citiesByUri[$rec['uri']] = $id;
        }
        foreach($sections as $id => $rec){
            $sectionsByName[$rec['name_en']] = $id;
        }
       
        
        for($i = 0, $l = count($rows); $i < $l; $i++){
            $dimensions = $rows[$i]->getDimensions();
            $hits = $rows[$i]->getMetrics()[0]->getValues()[0];
            if(isset($this->iso[$dimensions[0]])){
                $dimensions[0] = $this->iso[$dimensions[0]];
            }else{
                continue;
            }
            if(isset($citiesByUri[$dimensions[1]])){
                $dimensions[1] = $citiesByUri[$dimensions[1]];
            }else{
                continue;
            }
            if(isset($sectionsByName[$dimensions[2]])){
                $dimensions[2] = $sectionsByName[$dimensions[2]];
            }else{
                continue;
            }
            /*if(stripos($dimensions[1], 'cities') != false){
                //continue;
                $dimensions[1] = 0;
            }*/
            $id = $dimensions[4];
            $dimensions[4] = $hits;
            if(!isset($records[$id])) $records[$id] = [];            
            $records[$id][] = $dimensions;
        }
        return $records;
    }
    
    public function parsePathViewsResultArray($results) : array {
        $rows = [];
        $totals = [];
        for ( $reportIndex = 0; $reportIndex < count( $results ); $reportIndex++ ) {
            $report = $results[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            break;
        }
        
        for($i = 0, $l = count($rows); $i < $l; $i++){
            $url = $this->cleanUpPath($rows[$i]->getDimensions()[0]);
            $parsedURL=parse_url($url); 
            if (isset($parsedURL['query'])) {
                continue;
            }
            $path=$parsedURL['path'];
            $views = $rows[$i]->getMetrics()[0]->getValues()[0];
            
            //print_r($path. ' '. $views."\n");
            
            $pk=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_URL_PATH, $path);
            $result=NoSQL::instance()->getBins($pk);
            if (empty($result)) {
                //echo $path, "\t", $views,  "\tinvalid", "\n";
            }
            else if ($result['country_id']<=0) {
                continue;
            }
            else if ($result['section_id']===0) {
                continue;
            }
            else if ($result['purpose_id']===0) {
                continue;
            }
            else {
                if (!isset($totals[$path])) {
                    $totals[$path]=['views'=>$views, 'country'=>$result['country_id'], 'city'=>$result['city_id'], 'section'=>$result['section_id'], 'purpose'=>$result['purpose_id']];
                }
                else {
                    $totals[$path]['views']+=$views;
                }
            }
        }
        return $totals;
    }
    
    
    public function parseTsvFile(string $file='/opt/analytics.tsv') : void {
        $totals=[];
        $handle = fopen($file, 'r');
        if ($handle) {
            while (($row = fgetcsv($handle, 0, "\t")) !== false) {
                if (\count($row)===2) {
                    $row[0]=preg_replace('/[\x00-\x1F\x7F]/u', '', $row[0]);
                    $path = $this->cleanUpPath($row[0]);
                    $views=\preg_replace('/\D/', '',$row[1])+0;
                    
                    $pk=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_URL_PATH, $path);
                    $result=NoSQL::instance()->getBins($pk);
                    if (empty($result)) {
                        //echo $path, "\t", $views,  "\tinvalid", "\n";
                    }
                    else if ($result['country_id']<=0) {
                        continue;
                    }
                    else if ($result['section_id']===0) {
                        continue;
                    }
                    else if ($result['purpose_id']===0) {
                        continue;
                    }
                    else {
                        if (!isset($totals[$path])) {
                            $totals[$path]=['views'=>$views, 'country'=>$result['country_id'], 'city'=>$result['city_id'], 'section'=>$result['section_id'], 'purpose'=>$result['purpose_id']];
                        }
                        else {
                            $totals[$path]['views']+=$views;
                        }
                    }
                }
            }
        }
        fclose($handle);
    }
          
    public function processCustomerHits($dimensions){
        
        foreach($dimensions as $id => $dims){
            if(count($dims) > 5){
                $dims = array_slice($dims, 0, 5);
            }
            if(count($dims) > 1){
                uasort($dims, function($a, $b){ return $b[4] <=> $a[4]; });
            }
        }
        
    }
    
    public function processPathViewsTotals(array $totals){        
        $partitions=[];
        $countries=[];
        $cities=[];
        foreach ($totals as $k => $v) {
            $cn=$v['country'];
            $cc=$v['city'];
            if (!isset($countries[$cn])) {  $countries[$cn]=[];  }            
            if ($cc>0 && !isset($cities[$cc])) {  $cities[$cc]=[];  }
            
            $sp=$v['section'].'-'.$v['purpose'];
            
            
            if ($cc>0) {
                if (!isset($cities[$cc][$sp])) {
                    $cities[$cc][$sp]=$v['views'];
                }
                else {
                    $cities[$cc][$sp]+=$v['views'];
                }
            }
            
            if (!isset($countries[$cn][$sp])) {
                $countries[$cn][$sp]=$v['views'];
            }
            else {
                $countries[$cn][$sp]+=$v['views'];
            }                            
        }
        
        
        foreach ($countries as $cn => $arr) {
            arsort($arr);
            $i=0;
            $countries[$cn]=[];
            foreach ($arr as $k => $v) {
                $countries[$cn][$k]=$v;
                $i++;
                if ($i>4) { break; }
            }
        }
        
        foreach ($cities as $cc => $arr) {
            arsort($arr);
            $i=0;
            $cities[$cc]=[];
            foreach ($arr as $k => $v) {
                $cities[$cc][$k]=$v;
                $i++;
                if ($i>4) { break; }
            }
        }
        //var_dump($cities);
        
        
        foreach ($countries as $cn => $arr) {
            $res=[];
            foreach ($arr as $k => $v) {
                $p=\explode('-', $k);
                //echo $cn, "\t", $p[0], ' ', $p[1], "\t", $v, "\n";
                $res[]=['se'=>$p[0]+0, 'pu'=>$p[1]+0, 'vw'=>$v];
            }
            $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'top-'.$cn);            
            NoSQL::instance()->setBins($key, ['data'=>$res]);
            //var_dump($res);
        }
        
        foreach ($cities as $cc => $arr) {
            $res=[];
            foreach ($arr as $k => $v) {
                $p=\explode('-', $k);
                //echo $cc, "\t", $p[0], ' ', $p[1], "\t", $v, "\n";
                $res[]=['se'=>$p[0]+0, 'pu'=>$p[1]+0, 'vw'=>$v];
            }
            $key=NoSQL::instance()->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, 'top-city-'.$cc); 
            //var_dump($res);
            NoSQL::instance()->setBins($key, ['data'=>$res]);
        }
        
    }
    
    
    /*
    public function getDimension(){
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");
        
        $dimensionCountry = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCountry->setName("ga:dimension4");
        
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName("ga:sessions");
        $ordering->setOrderType("VALUE");   
        $ordering->setSortOrder("DESCENDING");
       
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($dimensionCountry));
        $request->setMetrics(array($sessions));
        $request->setOrderBys($ordering);
        $request->setPageSize(100);
        return $request;
    }
    
    public function getTestQuery(){
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("yesterday");
        
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:pageviews");
        $sessions->setAlias("pageviews");
        
        $dimensionCountry = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionCountry->setName("ga:Country");
        
        $dimensionPath = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionPath->setName("ga:pagePath");
        
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName("ga:pageviews");
        $ordering->setOrderType("VALUE");   
        $ordering->setSortOrder("DESCENDING");
        
        // Create the DimensionFilter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:pagePath');
        $dimensionFilter->setOperator('REGEXP');
        $dimensionFilter->setExpressions(array('[^?]'));

        // Create the DimensionFilterClauses
        $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setFilters(array($dimensionFilter));
        
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id_site);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($dimensionCountry, $dimensionPath));
        $request->setMetrics(array($sessions));
        $request->setDimensionFilterClauses(array($dimensionFilterClause));
        $request->setOrderBys($ordering);
        $request->setPageSize(100);
        return $request;
    }
    */

}

$mcAnalytics = new MCGoogleAnalytics;
$mcAnalytics->topPageViews();
//$mcAnalytics->customerHits();

//$mcAnalytics->parseTsvFile();
