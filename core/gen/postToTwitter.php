<?php 
$root_path = dirname(dirname(dirname(__FILE__)));
include_once $root_path.'/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';
include_once $config['dir'].'/core/lib/TwitterAPIExchange.php';

if ($argc>1) {
    
    
}else {
    
    $settings = array(
        'oauth_access_token' => $config['tw_token'],
        'oauth_access_token_secret' => $config['tw_token_secret'],
        'consumer_key' => $config['tw_consumer_id'],
        'consumer_secret' => $config['tw_consumer_secret']
    );
    
    $update_url = 'https://api.twitter.com/1.1/statuses/update.json';
    $update_media_url = 'https://api.twitter.com/1.1/statuses/update_with_media.json';
    $geo_url = 'https://api.twitter.com/1.1/geo/reverse_geocode.json';
    $requestMethod = 'POST';
    
    $twitter = new TwitterAPIExchange($settings);
    
    $postfields = array(
        'status'    =>  'Mourjan is working on a new feature.. stay tuned.. https://www.mourjan.com/'
    );
    
//    echo $twitter->buildOauth($update_url, $requestMethod)
//                 ->setPostfields($postfields)
//                 ->performRequest();
//
//         exit(0);
    
    
    
    $db=new DB($config);
    $db->setWriteMode(true);

    try{

        $db_instance = $db->getInstance();

        $q='
        select first 1 m.ID, m.AD_ID, a.rtl, 
        a.country_id, a.city_id, 
        c.name_en country_name_en,
        e.name_en city_name_en, 
        c.name_ar country_name_ar,
        e.name_ar city_name_ar,
        e.latitude, e.longitude, 
        a.content, d.content other
        from message_controller m 
        left join ad a on a.ID=m.AD_ID 
        left join country c on c.id=a.country_id 
        left join city e on e.id=a.city_id 
        left join ad_user d on d.id = m.ad_id
        where m.twitter=0 and root_id in (1, 2, 3, 99) 
        and a.HOLD=0 and a.date_added > \'2013-11-26\'  
        order by m.LAST_UPDATE
        ';

        $fetch_ad=$db_instance->prepare($q);


        $q='
        update message_controller set twitter=1 where id = ?
        ';
        $update_ad=$db_instance->prepare($q);

        $fetch_ad->execute();    

        $last_country=0;
        $noPass = 1;
        while (($ad = $fetch_ad->fetch(PDO::FETCH_ASSOC)) !== false) {
            
            if($last_country != $ad['COUNTRY_ID']) {
                
                $content = json_decode($ad['OTHER'],true);
                
                $ad_link = ' https://mourjan.com/'.$ad['AD_ID'].($content['rtl']?'':'/en');
                
                echo $ad_link,"\n";
                $ad_link_length = strlen($ad_link);
                
                
                if($content['extra']['m']!=2 && $content['loc'] && $content['lat'] && $content['lon']){
                    $postfields['lat'] =$content['lat'];
                    $postfields['long'] =$content['lon'];
                    $postfields['display_coordinates'] =true;                            
                }else{
                    $postfields['lat']  =   $ad['LATITUDE'];
                    $postfields['long'] =   $ad['LONGITUDE'];
                    $postfields['display_coordinates'] =true;
//                    $geo_data = $twitter->buildOauth($geo_url, 'GET')
//                                            ->setGetfield('lat='.$ad['LATITUDE'].'&long='.$ad['LONGITUDE'])
//                            ->performRequest();
                }
                
                $max_length = 140;
                if ($content['extra']['p']!=2 && isset($content['pics']) && count($content['pics'])){
                    $img_path='/var/www/mourjan/web/repos/d/';
                    $postfields['possibly_sensitive']=false;
                    foreach($content['pics'] as $key => $val){
                        if(isset($val[0]) && $val[1]){
                            $image = "@/".realpath($img_path.$key);
                            //$image = file_get_contents($img_path.$key);
                            //$postfields['media[]']=base64_encode($image);
                            $postfields['media[]']=$image;
                            $update_url = $update_media_url;
                            $max_length = 110;
                            break;
                        }
                    }
                }
                
                $city_hash_en = '';
                $city_hash_ar = '';
                switch($ad['CITY_ID']){
                    case 26:
                    case 4:
                    case 815:
                        $city_hash_en=preg_replace('/\s/','',$ad['CITY_NAME_EN']);
                        $city_hash_ar=preg_replace('/\s/','',$ad['CITY_NAME_AR']);
                        break;
                    case 6:
                    case 22:
                    case 28:
                    case 29:
                        $city_hash_en=preg_replace('/\s/','',$ad['CITY_NAME_EN']);
                        $city_hash_ar=$ad['CITY_NAME_AR'];
                        break;
                    case 10:
                        $city_hash_en=$ad['CITY_NAME_EN'];
                        $city_hash_ar='القاهرة';
                        break;
                    case 12:
                        $city_hash_en='Asir';
                        $city_hash_ar='عسير';
                        break;
                        break;
                    case 812:
                        $city_hash_en='Fujairah';
                        $city_hash_ar='الفجيرة';
                        break;
                    case 1255:
                        $city_hash_en='Hail';
                        $city_hash_ar='حائل';
                        break;
                    case 1026:
                    case 1200:
                    case 1224:
                    case 1210:
                    case 1226:
                    case 1230:
                    case 1239:
                    case 761:
                    case 20:
                    case 19:
                    case 18:
                    case 17:
                    case 16:
                    case 15:
                    case 5:
                    case 24:
                    case 25:
                        $city_hash_en='';
                        $city_hash_ar='';
                        break;
                    default:
                        $city_hash_en=$ad['CITY_NAME_EN'];
                        $city_hash_ar=$ad['CITY_NAME_AR'];
                        break;
                }
                if($city_hash_ar)$city_hash_ar=' #'.$city_hash_ar;
                if($city_hash_en)$city_hash_en=' #'.$city_hash_en;
                
                $location_hash_en = ' #'.preg_replace('/\s/','',$ad['COUNTRY_NAME_EN']).$city_hash_en;
                $location_hash_ar = ' #'.preg_replace('/\s/','',$ad['COUNTRY_NAME_AR']).$city_hash_ar;
                
                $location_hash = $location_hash_en;
                $location_hash_length = strlen($location_hash);
                if($content['rtl']){
                    $location_hash = $location_hash_ar;
                    $location_hash_length = mb_strlen($location_hash,'UTF-8');
                }

                $text = $content['other'];
                $text = preg_replace('/\x{200B}.*/u', '', $text);
                $text = preg_replace('/\s+/', ' ', $text);
                $text_length = mb_strlen($text,'UTF-8');
                
                if ($text_length+$ad_link_length+$location_hash_length > $max_length){
                    $new_length = $max_length-$ad_link_length-$location_hash_length-2;
                    $text = mb_substr($text, 0, $new_length, 'UTF-8');
                    $pos = mb_strrpos($text," ", 'UTF-8');
                    if($pos){
                        $text = mb_substr($text, 0, $pos, 'UTF-8');
                    }
                    $text.='..';
                }
                $text.=$ad_link;
                $text.=$location_hash;
                $postfields['status']=$text;
                

                if($update_ad->execute(array($ad['ID']))){                    
                    $resp = $twitter->buildOauth($update_url, $requestMethod)
                     ->setPostfields($postfields)
                     ->performRequest();
                }
                $last_country = $ad['COUNTRY_ID'];

                $noPass=0;
            }
        }

    }catch(Exception $ex){
        $db_instance->rollBack();
        error_log($ex->getMessage());
    }
    $db->close();
    
    
}
?>