<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Propspace
 *
 * @author toshiba
 */
class Propspace {
    
    const SECTION_MAP = [
        'apartment'                 => 1,
        'penthouse'                 => 1,
        'duplex'                    => 1,
        'loft apartment'            => 1,
        'townhouse'                 => 1,     
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
        'commercial building'       => 8,
        'whole building'            => 8,
        'residential building'      => 8,
        'commercial full building'  => 8,
        
        'villa'                     => 131,
        'commercial villa'          => 131,
        
        'office'                    => 6,
        
        'warehouse'                 => 111,
        
        'labour camp'               => 162,
        'staff accommodation'       => 162,
        
        'hotel'                     => 419,

        'bungalow'                  =>4,

        'studio'                    => 122,

        'factory'                   =>  367
    ];
    
    const CITY_MAP = [
        'dubai'                     => 14,
        'ajman'                     => 436,
        'sharjah'                   => 333,
        'abu dhabi'                 => 4,
        'fujairah'                  => 812,
        'ras al khaimah'            => 815,
        'umm al quwain'             => 2609,
        'al ain'                    => 6
    ];
    
    const CITY_MAP = [
        'lebanon'                   => 1,
        'emirates'                  => 2,
        'bahrain'                   => 3,
        'saudi arabia'              => 4,
        'egypt'                     => 5,
        'syria'                     => 6,
        'kuwait'                    => 7,
        'jordan'                    => 8,
        'qatar'                     => 9,
        'sudan'                     => 10,
        'tunisia'                   => 11,
        'yemen'                     => 12,
        'algeria'                   => 15,
        'iraq'                      => 122,
        'morocco'                   => 145,
        'oman'                      => 161
    ];
    
    CONST SECTION_LABEL = [
        'apartment'                 => 'Apartment',
        'villa'                     => 'Villa',
        'office'                    => 'Office',
        'retail'                    => 'Shop',
        'commercial villa'          => 'Commercial Villa',
        'hotel apartment'           => 'Hotel apartment',
        'warehouse'                 => 'Warehouse',
        'land commercial'           => 'Commercial land',
        'labour camp'               => 'Labour camp',
        'residential building'      => 'Residential building',
        'multiple sale units'       => 'Multiple sale units',
        'multiple rental units'     => 'Multiple rental units',
        'land residential'          => 'Residential land',
        'commercial full building'  => 'Full commercial building',
        'commercial half floor'     => 'Commercial half floor',
        'commercial full floor'     => 'Commercial full floor',
        'penthouse'                 => 'Penthouse',
        'duplex'                    => 'Duplex',
        'loft apartment'            => 'Loft apartment',
        'townhouse'                 => 'Townhouse',
        'hotel'                     => 'Hotel',
        'land mixed use'            => 'Land',
        'bungalow'                  => 'Bungalow',
        'factory'                   => 'Factory',
        'staff accommodation'       => 'Staff accommodation',
        'residential half floor'    => 'Residential half floor',
        'residential full floor'    => 'Residential full floor',
        'full floor'                => 'Full floor',
        'half floor'                => 'Half floor',
        'compound'                  => 'Compound'
    ];
    
    var $UNIT_MEASURE               = 'sqft',
        $CURRENCY                   = 'AED',
        $COUNTRY_CODE               = 971,
        $COUNTRY_XX                 = 'AE',
        $COUNTRY_ID                 = 2,
        $USER_ID                    = 0,
        $FEED_lINK                  = '',
        $config                     = null,
        $NUM_TO_PROCESS             = 0,
        $ERROR_CODE                 = 0,
        $LISTINGS                   = [];
    
    const ERROR =   [
        101     =>      [
            'ar'    =>  '',
            'en'    =>  'unable to access the provided XML feed, please check the link or contact PropSpace for help'
        ],
        102     =>      [
            'ar'    =>  '',
            'en'    =>  'unable to parse the content of XML feed, please contact PropSpace for help'
        ],
        103     =>      [
            'ar'    =>  '',
            'en'    =>  'unable to parse the content of XML feed for it contains malformed data or text, please contact PropSpace for help'
        ],
        104     =>      [
            'ar'    =>  '',
            'en'    =>  'there are no listings to process'
        ],
    ];
    
    function __construct($config, $web_user_id, $feed_url, $number_to_process=0) {
        $this->config       =   $config;
        $this->USER_ID      =   $web_user_id;
        $this->FEED_lINK    =   $feed_url;
        $this->NUM_TO_PROCESS = $number_to_process;
    }
    
    function run(){
        $this->load();
        if(!$this->ERROR_CODE){
            $this->parse();
        }
    }
    
    function parse(){
        
    }
    
    function getErrorMsg($language='en'){
        if($this->ERROR_CODE){
            return $this->ERROR[$this->ERROR_CODE][$language];
        }
    }
    
    function load(){
        $xml_feed = file_get_contents($this->FEED_lINK);
        if(!$xml_feed){
            $this->ERROR_CODE = 101;
        }
    
        $xml = simplexml_load_string($xml_feed, null, LIBXML_NOCDATA);
        if(!$xml){
            $this->ERROR_CODE = 102;
        }
        
        $feeds = null;
        
        $json = json_encode($xml);
        if(json_last_error()){
            $this->ERROR_CODE = 103;
        }else{
            $feeds = json_decode($json,TRUE);
            if(json_last_error()){
                $this->ERROR_CODE = 103;
            }else{
                $this->LISTINGS = $feeds['Listing'];
            }
        }   
    
        if(!$feeds || count($feeds)==0){
            $this->ERROR_CODE = 104;
        }
    }
    
}
