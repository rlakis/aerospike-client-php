<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';
include_once $config['dir'].'/core/lib/facebook.php';

$countries = array(
    'DZ'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'BH'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'EG'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false,
                            'cities'        =>  array(
                                10      =>  '[]',//cairo - jizah - Al Qalyubiyah
                                11      =>  '[]',//alexandria - al buhayra - 
                                22      =>  '[]',//said - asyut - upper egypt
                                23     =>  '[]',//delta - Ash Sharqiyah - mansoura - Ad Daqahliyah
                            )
                            
                            //[] aswan - Suhaj
                            //[] Qina - 
                            //[] Al Minya
                            //[] Al Minufiyah
                            //[] Al Fayum
                            //[] Kafr ash Shaykh
                        ),
    'AE'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  true,
                            'cities'        =>  array(
                                4       =>  '[94,95,201,276,641,728,110,438,577,728]',//Abu Dhabi
                                6       =>  '[117,110]',//Al Ain
                                14      =>  '[195,96,236,334,345,368,510,509,512,715,       111,360,515,542,543,541,557,575,765,        118,114,         350,810,811,845]',//Dubai
                                812     =>  '[141,140,402,403,727]',//Fujaira
                                815     =>  '[173,306,519,713,712,711]',//Ras Al Khayma
                            )
                            //[111,360,515,542,543,541,557,575,765]    sharjah
                            //[118,114]     ajman
                            //[350,810,811,845]        Umm Al Quwain
                        ),
    'IQ'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'JO'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'KW'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'LB'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'LY'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'MA'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'OM'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'QA'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'SA'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  true,
                            'cities'        =>  array(
                                7      =>  '[2115687,2115281,2115280,2114956,2113788,2115039,2115019,2114412,2114429,2114510,2114622,2114647,2114654]',//Riyadh
                                8      =>  '[2115988,2115838,2115745,2115734,2115716,2115581,2115201,2113952,2114976,2113681,2113985,2114006,2114014,2114588,2114925,2114927,2114074]',//Jeddah - makka
                                9      =>  '[2116375,2116364,2116198,2115958,2115957,2115917,2115916,2115897,2115630,2115576,2115466,2115246,2114979,2114968,2114929,2114430,2114324,2114532,2115034,2113953,2113915,2114990,2114534,2113658,2113659,2114893,2114862,2113763,2113845,2114735,2113958,2114035,2114202,2114433,2114491]',//Dammam - Sharkiya
                                12     =>  '[2116154,2116069,2116057,2115959,2115914,2113650,2113654,2113670,2113822,2114407,2114074]',//Assir - abha - Jizan
                                26     =>  '[2116298,2116089,2115713,2114206,2114620,2114621,2114622,2114623]',//Madina , ehsa, hasa
                                27     =>  '[2114937,2115046,2116266]',//tabuk - al jawf
                                28     =>  '[2115867,2115864,2115863,2115851,2115251,2115113,2113991,2114059,2114481]',//Qassim
                                //ehsa copy of madina
                                29     =>  '[2116298,2116089,2115713,2114206,2114620,2114621,2114622,2114623]',//ehssa
                                
                                1255   =>  '[2116319,2114287,2115755,2116213,2116193]',//hael
                            )//search?q=&type=adregion&country_list=["SA"]&list=global
                            //[]    Jizan
                        ),
    'SD'        =>      false,
    'SY'        =>      false,
    'TN'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        ),
    'YE'        =>      array(
                            'has_regions'   =>  false,
                            'has_cities'    =>  false
                        )
);





$facebook = new Facebook(array(
 'appId'    => $config['fb_app_id'],
 'secret'   => $config['fb_app_secret'],
 'cookie'   => true,
));

if ($argc>1) {
    
    $facebook->setAccessToken($config['fb_user_token']);
    $facebook->setExtendedAccessToken();
    echo "\n",$facebook->getAccessToken(),"\n";
    
}else {
    $pass = 0;
    $facebook->setAccessToken($config['fb_user_etoken']);
    try{
        $page_info = $facebook->api('/me/accounts');
        if(isset($page_info['data']) && count($page_info['data'])){
            foreach($page_info['data'] as $page){
                if($page['name']=='Mourjan'){
                    $facebook->setAccessToken($page['access_token']);
                    $pass = 1;
                    break;
                }
            }
        }
        
    }catch(Exception $ex){
        error_log($ex->getMessage());
        $pass = 0;
    }

    if($pass){
        
        
        $db=new DB($config);
        $db->setWriteMode(true);
        try{

            $db_instance = $db->getInstance();

            $q='
            select first 1 m.ID, m.AD_ID, a.rtl, c.id_2 as country_code, a.city_id, a.country_id 
            from message_controller m 
            left join ad a on a.ID=m.AD_ID 
            left join country c on c.id=a.country_id 
            where m.facebook=0 and root_id in (1, 2, 3, 99) 
            and a.HOLD=0 and a.date_added > \'2013-11-26\' 
            order by m.LAST_UPDATE
            ';

            $fetch_ad=$db_instance->prepare($q);


            $q='
            update message_controller set facebook=1 where id = ?
            ';
            $update_ad=$db_instance->prepare($q);

            $fetch_ad->execute();    

            $last_country=0;
            $noPass = 1;
            while (($ad = $fetch_ad->fetch(PDO::FETCH_ASSOC)) !== false) {
                $countryCode = trim($ad['COUNTRY_CODE']);

                if($countries[$countryCode]===false){
                    $update_ad->execute(array($ad['ID']));
                    $noPass=0;
                }elseif($last_country != $ad['COUNTRY_ID']) {

                    $gating     =  '{"countries":["'.$countryCode.'"]}'; 
                    $targetting = '{"countries":["'.$countryCode.'"]' . 
                            ($countries[$countryCode]['has_cities'] && isset($countries[$countryCode]['cities'][$ad['CITY_ID']]) ? ',"cities":'.$countries[$countryCode]['cities'][$ad['CITY_ID']] : '')
                            .'}';


                    if($update_ad->execute(array($ad['ID']))){                        
                        $facebook->api('/'.$config['fb_page_id'].'/feed','post', array(
                            'link' => 'https://www.mourjan.com/'. $ad['AD_ID'] . '/'.($ad['RTL'] ? '':'en/'),
                            'feed_targeting'    =>  $targetting
                        ));
                    }

                    $last_country = $ad['COUNTRY_ID'];

                    $noPass=0;
                }
            }
            /*
            if($noPass){
                $tabs=$facebook->api('/'.$config['fb_page_id'].'/tabs','get', array(
                    'access_token' => $config['fb_page_etoken']
                ));
            }*/

        }catch(Exception $ex){
            $db_instance->rollBack();
            error_log($ex->getMessage());

        //    $facebook->setAccessToken($config['fb_page_token']);
        //    $facebook->setExtendedAccessToken();
        //    echo "\n",$facebook->getAccessToken(),"\n";
        }
        $db->close();
    
    }
    
}
?>