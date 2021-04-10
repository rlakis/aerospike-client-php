<?php
use Core\Model\Ad;
use Core\Model\Classifieds;

const SECTION_MAP = [
        'Apartment'                 => 1, /*OK*/
        'penthouse'                 => 1,
        'duplex'                    => 1,
        'loft apartment'            => 1,
        'Townhouse'                 => 1, /*OK*/
        ''                          => 1,
        'compound'                  => 1,
        'residential half floor'    => 1,
        'residential full floor'    => 1,
        'full floor'                => 1,
        'half floor'                => 1,
        '1 bhk'                     => 1,
        '2 bhk'                     => 1,
        '3 bhk'                     => 1,
        'multiple sale units'       => 1,
        'multiple rental units'     => 1,

        'furnished apartment'       => 2,
        'hotel apartment'           => 2,   
            
        'land commercial'           => 7,
        'land residential'          => 7,
        'land mixed use'            => 7,
        'land'                      => 7,
        'plots'                     => 7,
        'plot'                      => 7,
                
        'commercial half floor'     => 5,
        'commercial full floor'     => 5,
        'shop'                      => 5,
        'retail'                    => 5,

        'building'                  => 8,
        'Commercial Building'       => 8, /*OK*/
        'whole building'            => 8,
        'residential building'      => 8,
        'commercial full building'  => 8,
            
        'Villa'                     => 131, /*OK*/
        'commercial villa'          => 131,
                    
        'Office'                    => 6, /*OK*/
                
        'warehouse'                 => 111,
                    
        'labour camp'               => 162,
        'staff accommodation'       => 162,
                
        'hotel'                     => 419,

        'bungalow'                  => 4,
                
        'studio'                    => 122,
            
        'factory'                   => 367
    ];  

const COUNTRY_MAP = [
        'lebanon'                   => [1,'LB',961,'LBP'],
        'United Arab Emirates'      => [2,'AE',971,'AED'],
        'bahrain'                   => [3,'BH',973,'BHD'],
        'saudi arabia'              => [4,'SA',966,'SAR'],
        'egypt'                     => [5,'EG',20,'EGP'],
        'syria'                     => [6,'SY',963,'SYP'],
        'kuwait'                    => [7,'KW',965,'KWD'],
        'jordan'                    => [8,'JO',962,'JOD'],
        'qatar'                     => [9,'QA',974,'QAR'],
        'sudan'                     => [10,'SD',249,'SDG'],
        'tunisia'                   => [11,'TN',216,'TND'],
        'yemen'                     => [12,'YE',967,'YER'],
        'algeria'                   => [15,'DZ',213,'DZD'],
        'iraq'                      => [122,'IQ',964,'IQD'],
        'morocco'                   => [145,'MA',212,'MAD'],
        'oman'                      => [161,'OM',968,'OMR']
];


 const CITY_MAP = [
    'Dubai'                 => 14,
    'ajman'                 => 436,
    'sharjah'               => 333,
    'Abu Dhabi'             => 4,
    'fujairah'              => 812,
    'ras al khaimah'        => 815,
    'umm al quwain'         => 2609,
    'al ain'                => 6
];

const PURPOSE_MAP = [
    'sale'  => 1,
    'lease' => 2
    
];

if (PHP_SAPI!=='cli') {
    return;
}

if ($argc!==2) {
    echo 'Usage: '.__FILE__.' file.xml', "\n";
    return;
}

if (!file_exists($argv[1])) {
    echo $argv[1], " File does not exists!\n";
    return;    
}

if (substr($argv[1], -3)!=='xml') {
    echo $argv[1], " File is not xml!\n";
    return; 
}

include_once __DIR__.'/../../../config/cfg.php';
include_once __DIR__.'/../../../deps/autoload.php';

Config::instance()->incModelFile('Router')->incModelFile('Db')->incModelFile('Classifieds')
        ->incLibFile('MCUser');


//Config::instance()->incModelFile('NoSQL')->incLibFile('MCSessionHandler')->incLibFile('Logger');

Core\Model\Router::instance()->cache();

$xml=@simplexml_load_string(\file_get_contents($argv[1]), null, LIBXML_NOCDATA);
$json=\json_encode($xml);
$feed=\json_decode($json, true);
//print_r(array_keys($feed));

foreach ($feed['property'] as $k=>$item) {    
    //print_r($item);
    $ad=new \Core\Model\Ad([]);
    $ad->setUID(2)->setDataSet(new Core\Model\Content($ad));
    
    $ad->setSectionID(SECTION_MAP[$item['property_type']]??0)
        ->setPurposeId(PURPOSE_MAP[$item['purpose']]??0)
        ->setCountryId(COUNTRY_MAP[$item['country']][0]??0)
        ->setPrice($item['price']??0);
    
            
    $ad->data()[Classifieds::COUNTRY_CODE]=COUNTRY_MAP[$item['country']][1]??'';
    
    $ad->dataset()->setApp('web', '1.0')
        ->setIpAddress('127.0.0.1')
        ->setIpCountry(strtolower(COUNTRY_MAP[$item['country']][1]??''))
        ->setAdCountry(strtolower(COUNTRY_MAP[$item['country']][1]??''))
        
        ->setCityId(CITY_MAP[$item['city']]??0);
    $ad->data()[Classifieds::CITY_ID]=CITY_MAP[$item['city']]??0;
    
    if (isset($item['description_ar']) && is_string($item['description_ar'])) {
        $ad->setNativeText($item['description_ar']);
    }
    if (isset($item['description_en']) && is_string($item['description_en'])) {
        $ad->setNativeText($item['description_en']);
    }
    $reference_number=$item['reference_number']??'';
    $ad->setDocumentId($item['property_id']??0);
    
    echo '----------------------------------------------------------------------------------------------------------',"\n";
    if ($ad->sectionId()===0) {
        echo 'Invalid section: ', $item['property_type'], "\n";
    }
    if ($ad->countryId()===0) {
        echo 'Invalid country: ', $item['country'], "\n";
    }
    if ($ad->cityId()===0) {
        echo 'Invalid city: ', $item['city'], "\n";
    }
    if ($ad->purposeId()===0) {
        echo 'Invalid purpose: ', $item['purpose'], "\n";
    }
    print_r($ad->data());
    print_r($ad->dataset()->getAsVersion(3));
    //echo "==================================\n";    
}

