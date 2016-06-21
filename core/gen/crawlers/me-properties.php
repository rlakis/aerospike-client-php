<?php
/*
 * This is a crawler for me-properties.com real estate feed 
 * based in UAE
 */
$processItems = 50;

$FEED_URI = 'http://me-properties.com/freefeeds.php';

$sections_map = array(
  "land"                    =>  7,
  "plots"                    =>  7,
  "plot"                    =>  7,
  "townhouse"               =>  1,
  "apartment"               =>  1,
  "full floor"               =>  1,
  "furnished apartment"     =>  2,
    ''                      =>  1,
  "villa"                   =>  131,
  "1 bhk"                   =>  1,
  "2 bhk"                   =>  1,
  "3 bhk"                   =>  1,
  "office"                  =>  6,
  "pent house"              =>  1,
  'building'                =>  8,
    'whole building'        =>  8,
  'studio'                =>  122,
   'hotel'                  =>  419,
    'shop'                =>  5,
    'retail'                =>  5
  
);
$cities_map = array(
  "dubai"   =>  14,
  "ajman"   =>  436,
  "sharjah"  =>  333,
  ""        =>  14
);


include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "Starting me-properties.com crawler\n";
echo 'Fetching feed page: http://me-properties.com/freefeeds.php',"\n";

$ads = array();

//$xml_feed = file_get_contents();
$user_id=41781;
$country_code=971;
$country_xx='AE';

$country_id=2;
$city_id = 0;
$root_id = 1;
$section_id = 0;
$purpose_id = 0;

$sections=array();
$purposes=array();
$cities=array();

try{    
    $xml_feed = file_get_contents($FEED_URI);
    if(!$xml_feed){
        echo "Empty feed\n";
        exit(0);
    }
    /*$xml_feed = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><properties><property>
<reference_code>MHRS185</reference_code>
<City>Dubai</City>
<community>Dubai Land</community>
<subcommunity>Al Ruwayyah</subcommunity>
<title><![CDATA[Corner plot in The Villa in RL sector]]></title>
<description_en><![CDATA[Corner plot in The villa in Dubai land in RL-Sector (Al Ruwayya).Diffrent plots available in the same location.For more details please call 0508505950 www.me-properties.com]]></description_en>
<sqft>15187</sqft>
<bedroom></bedroom>
<bathroom></bathroom>
<category>Residential For Sale</category>
<type>land</type>
<price>3796000</price>
<photos>
<photo>http://me-properties.com/images/uploaded/properties/user_file18510122013084843.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file218510122013084844.jpg</photo>
</photos>
<lastupdated>2013-10-12 12:48:43</lastupdated>
<agent_name>massoud</agent_name>
<agent_number>+9714 3699312</agent_number>
<agent_email>massoud@me-properties.com</agent_email>
<company>ME Properties</company>
<website>http://me-properties.com</website>
<logo>http://me-properties.com/images/logo.png</logo>
</property><property>
<reference_code>MHRR76</reference_code>
<City>Dubai</City>
<community>Downtown Dubai</community>
<subcommunity>Burj Khalifa</subcommunity>
<title><![CDATA[Immaculate two bedroom+Study Type B in Burj Khalifa]]></title>
<description_en><![CDATA[An amazing fully furnished appartment above 25th floor in Burj Khalifa.This beautiful apartment is fully furnished with italian tailor maid furnitures.Full see view,lake and sheikh zayed road.For viewing please call 0508505950 www.me-properties.com]]></description_en>
<sqft>1640</sqft>
<bedroom>2</bedroom>
<bathroom>2</bathroom>
<category>Residential For Rent</category>
<type>Apartment</type>
<price>315000</price>
<photos>
<photo>http://me-properties.com/images/uploaded/properties/user_file7607292013181637.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file27607292013181637.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file37607292013181637.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file47607292013181637.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file57607292013181638.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file67607292013181638.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file77607292013181639.jpg</photo>
<photo>http://me-properties.com/images/uploaded/properties/user_file87607292013181641.jpg</photo>
</photos>
<lastupdated>2013-08-04 12:30:00</lastupdated>
<agent_name>massoud</agent_name>
<agent_number>+9714 3699312</agent_number>
<agent_email>massoud@me-properties.com</agent_email>
<company>ME Properties</company>
<website>http://me-properties.com</website>
<logo>http://me-properties.com/images/logo.png</logo>
</property></properties>';*/
    
    $xml = simplexml_load_string($xml_feed, null, LIBXML_NOCDATA);
    if(!$xml){
        echo "Error parsing xml\n";
        exit(0);
    }
    $json = json_encode($xml);
    $feeds = json_decode($json,TRUE);
    $feeds = $feeds['property'];
    if(!$feeds || count($feeds)==0){
        echo "No feed records\n";
        exit(0);
    }
    
    $sphinx = new SphinxClient();
    $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
    $sphinx->SetConnectTimeout(1000);
    $sphinx->SetServer($config['search_host'], $config['search_port']);
    
    $db = new DB($config);
    
    $q='insert into ad_user
       (web_user_id,content,title,purpose_id,section_id,rtl,country_id,city_id,latitude,longitude,media)
       values (?,?,?,?,?,?,?,?,?,?,?) returning id';
    $insert_ad=$db->prepareQuery($q);    
    
    $q='update ad_user set content=?,state=?,media=?,date_added=current_timestamp where id=?';
    $update_ad=$db->prepareQuery($q);
    
    $error = 0;
    $i=0;
    foreach($feeds as $ad){
        if(in_array($ad['reference_code'],array("RHCR310","VOCS210","VOCS209","VOCS208","VOCS207","VOCS206","VOCS204","VOCS202"))) continue;
        $sphinx->resetFilters();
        $sphinx->resetGroupBy();

        $sphinx->setFilter("hold", array(0));
        $sphinx->setFilter("user_id", array($user_id));
        $searchRes=$sphinx->Query($ad['reference_code'], $config['search_index']);
        if($searchRes === false){
            echo "Error in Sphinx\n";
            exit(0);
        }
        if (isset($searchRes['total_found']) && $searchRes['total_found']) {
            continue;
        }
        if(isset($ad['city'])) {            
            $city = getElementValue($ad['city']);
        }elseif(isset($ad['City'])) {
            $city = getElementValue($ad['City']);
        }else{
            echo '--------------------CITY ERROR----------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        if(isset($ad['type'])) {            
            $section = getElementValue($ad['type']);
        }else{
            echo '------------------SECTION ERROR---------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        if(isset($ad['category'])) {            
            $purpose = getElementValue($ad['category']);
        }else{
            echo '------------------CATEGORY ERROR--------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        $description = $ad['description_en'];

        $sections[$section] = isset($sections[$section])? $sections[$section]+1 : 1;
        $purposes[$purpose] = isset($purposes[$purpose])? $purposes[$purpose]++ : 1;
        $cities[$city] = isset($cities[$city]) && $cities[$city]? $cities[$city]+1 : 1;
               
        if(!isset($sections_map[$section])){
            echo '--------------------SECTION MAP---------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }else{
            $section_id = $sections_map[$section];
        }
        
        if(!isset($cities_map[$city])){
            echo '----------------------CITY MAP----------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }else{
            $city_id = $cities_map[$city];
        }
        
        $purpose_id=0;
        if(preg_match('/for rent/',$purpose)){
            $purpose_id=2;
        }
        if(preg_match('/for sale/',$purpose)){
            $purpose_id=1;
        }
        
        if(!$purpose_id){
            echo '-------------------PURPOSE MATCH--------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        
        $number = preg_replace('/ /','',$ad['agent_number']);
        if (!is_numeric($number)) continue;
        $raw_number = preg_replace('/\+'.$country_code.'|^'.$country_code.'/', '', $number);
        if($raw_number[0]==5)
            $number_type = 1;
        else $number_type = 7;
        
        
        $content = array(
            'ro'            =>  $root_id,
            'pu'            =>  $purpose_id,
            'se'            =>  $section_id,
            'rtl'           =>  0,
            'other'         =>  '',
            /*'pics'          =>  array(),
            'pic_default'   =>  '',*/
            'extra'         =>  array(
                't'         =>  2,
                'v'         =>  2,
                'p'         =>  2,
                'm'         =>  2,                
            ),
            'cui'           =>  array(
                'p'     =>  array(
                    array(
                        'v'     =>  $number,
                        't'     =>  $number_type,
                        'c'     =>  $country_code,
                        'r'     =>  $raw_number,
                        'i'     =>  $country_xx
                    )                    
                ),
                'b'     =>  '',
                'e'     =>  $ad['agent_email'],
                't'     =>  '',
                's'     =>  ''
            ),
            'cut'       =>  array(
                't'     =>  0,
                'b'     =>  24,
                'a'     =>  6
            ),
            'lat'       =>  '',
            'lon'       =>  '',
            'loc'       =>  '',
            'state'     =>  0,
            'pubTo'     =>  array(
                $cities_map[$city]  =>  $cities_map[$city]
            ),
            'website'   =>  $ad['website'],
            'userLOC'   =>  'SYS',
            'version'   =>  2,
            'SYS_CRAWL' =>  1
            
        );
        
        $bathrooms = getElementValue($ad['bathroom']);
        $bedroom = getElementValue($ad['bedroom']);
        $price = getElementValue($ad['price']);
        $area = getElementValue($ad['sqft']);
        
        $description = preg_replace('/\n.*+/i', '', $description);
        $description = preg_replace('/(?:for mor|for viewing).*?(?:\n|$)/i', '', $description);
        $description = preg_replace('/(?:\stel(?:\s|:)|mob(?:\s|:)|www\.).*?(?:\n|$)/i', '', $description);
        $description = preg_replace('/(?:to see\.).*?(?:\n|$)/i', '', $description);
        $description = preg_replace('/(?:call \.).*?(?:\n|$)/i', '', $description);
        $description = preg_replace('/(?:please (?:call|mail)).*?(?:\n|$)/i', '', $description);
        $description = preg_replace('/\n/', ' ', $description);
        $description = preg_replace('/\s+/', ' ', $description);
        $description = preg_replace('/[.,;:]([a-zA-Z])/', '. $1', $description);
        $description = preg_replace('/([a-zA-Z])[.,]([0-9])/', '$1. $2', $description);
        $description = preg_replace('/[.,!]$/', '', strtolower(trim($description)));
        
                    
        if($description==''){
        /*    if(strlen($ad['title'])>10){
                $description = $ad['title'].' - ';
            }
        */    
            $sub = getElementValue($ad['subcommunity']);
            $com = getElementValue($ad['community']);
            $description = $ad['type'].' '.($purpose_id==1 ? 'for sale':'for rent').' in '.($sub ? $ad['subcommunity'].', ':'').($com ? $ad['community'].', ':'').ucfirst($city);
            
        }
        
        if($bedroom){
            $description.= ' - '.$bedroom.' bedroom'.($bedroom>1 ? 's':'');
        }
        if($bathrooms){
            $description.= ' - '.$bathrooms.' bathroom'.($bathrooms>1 ? 's':'');
        }
        if($area){
            $area = preg_replace('/[^0-9]/', '',$area);
            $description.= ' - '.number_format($area).' sqft';
        }
        if($price){
            $description.= ' - price '.number_format($price).' AED';
        }
        $description.= ' - ref: '.$ad['reference_code'];
        $description = $description.  json_decode('"\u200B"').' / for more details please call '.ucfirst($ad['agent_name']).parseUserAdTime($content['cui'], $content['cut']);
        
        
        if($section_id == 1){
            if(preg_match('/(\s|^)furnished/i',$description)){
                $section_id = 2;
            }
        }
        if(preg_match('/studio/i',$description)){
            $section_id = 122;
        }
        
        if(preg_match('/hotel/i',$description)){
            $section_id = 419;
        }
        
        if(preg_match('/call\/message\/whatsapp/u', $description)){
                continue;
            }
        
        if(strlen($description)>500){
                continue;
            }
        
        
        echo $ad['description_en'],"\n";
        echo '----------------------------------------------------',"\n";
        echo $description,"\n";
        echo '----------------------------------------------------',"\n";
        echo "Section:\t{$section_id}\t\tPurpose:\t{$purpose_id}\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
              
        
        $content['other']=$description;
        
        if( strlen($description) > 400){
            continue;
        }
        
        /*
        $pad = array(
            'user'      =>  $user_id,
            'ro'        =>  $root_id,
            'se'        =>  $section_id,
            'pu'        =>  $purpose_id,
            'rtl'       =>  0,
            'cn'        =>  $country_id,
            'c'         =>  $cities_map[$city],
            'lon'       =>  0,
            'lat'       =>  0,
            'state'     =>  0,
            'content'   => json_encode($content),
            'title'     =>  $ad['reference_code']
            );
        */
        
        /*insert ad*/
        $insert_ad->bindValue(1, $user_id);
        $insert_ad->bindValue(2, json_encode($content));
        $insert_ad->bindValue(3, $ad['reference_code']);
        $insert_ad->bindValue(4, $purpose_id);
        $insert_ad->bindValue(5, $section_id);
        $insert_ad->bindValue(6, 0);//rtl
        $insert_ad->bindValue(7, $country_id);
        $insert_ad->bindValue(8, $city_id);
        $insert_ad->bindValue(9, 0);//lat
        $insert_ad->bindValue(10, 0);//lon
        $insert_ad->bindValue(11, 0, PDO::PARAM_INT);//media
        
        $ad_rec=null;
        $ad_id=0;
        if ($insert_ad->execute()) {
            $ad_rec=$insert_ad->fetchAll(PDO::FETCH_ASSOC);
            if (!empty ($ad_rec)) {
                $ad_id=$ad_rec[0]['ID'];
            }else{
                continue;
            }
        }
        if(!$ad_id){
            echo "Error inserting record\n";
            exit(0);
        }        
        
        $photos = getElementValue($ad['photos'],'photo');
        $pCount = count($photos);
        if($pCount>5)$pCount=5;
        $succeededPhotos=array();
        $default_pic = '';
        $path = '/var/www/mourjan/web/repos/';
        for ($k=0;$k<$pCount;$k++){
            preg_match('/(\.(?:jpg|png|gif|jpeg))$/',$photos[$k],$matches);
            if($matches && count($matches)){
                $ext = $matches[1];
                $name = $ad_id.'_'.$k.$ext;
                $img = $path.'l/'.$name;
                $pcn = file_get_contents($photos[$k]);
                if(strlen($pcn)){
                    file_put_contents($img, $pcn);
                    if(file_exists($img)){                        
                        $image_width=0;
                        $image_height=0;
                        list($image_width, $image_height) = getimagesize($img);
                        $re_sampling = true;
                        $re_sampling=generate_image_size($img, $path.'d/'.$name, 648);
                        if($re_sampling)
                            $re_sampling=generate_image_size($img, $path.'m/'.$name, 300);
                        if($re_sampling)
                            $re_sampling=generate_image_size($img, $path.'s/'.$name, 120);
                        
                        if($re_sampling){
                            list($image_width, $image_height) = getimagesize($path.'d/'.$name);
                            $succeededPhotos[$name]=array($image_width,$image_height);
                            if(!$default_pic) $default_pic = $name;
                        }
                    }
                }
            }
        }
        $media = 0;
        if(count($succeededPhotos)){
            $content['pic_idx']=$k-1;
            $content['pics']=$succeededPhotos;
            $content['pic_def']=$default_pic;
            $media = 1;
            $content['extra']['p']=1;
        }
        $update_ad->bindValue(1, json_encode($content));
        $update_ad->bindValue(2, 1, PDO::PARAM_INT);
        $update_ad->bindValue(3, $media, PDO::PARAM_INT);
        $update_ad->bindValue(4, $ad_id, PDO::PARAM_INT);
        
        $update_ad->execute();        
        
        $i++;
        if($i==$processItems){
                $db->close();
                exit(0);
            }
    }
}
catch(Exception $ex){
    var_dump($ex);
}
if($error){
    echo "\nSections\n";
    var_dump($sections);
    echo '----------------------------------------------------',"\n";
    echo "\ncities\n";
    var_dump($cities);
    echo '----------------------------------------------------',"\n";
    echo "\npurposes\n";
    var_dump($purposes);
    echo '----------------------------------------------------',"\n";
}else{
    echo 'Processed '.$i,"\n";
}
/*
$db=new DB($config);

$stmt=$db->prepareQuery(
    "insert into section_tag (section_id, lang, name, uri, query_term, blocked) values (?,?,?,?,?,0)"
    );
    foreach ($tagsToInsert as $tag){
        if ($stmt->execute($tag)) 
            echo 'pass',"\n";
        else echo 'fail',"\n";
    }

$db->close();
 * 
 */
echo 'Done me-properties.com crawling',"\n\n";

function getElementValue($element,$is_array=0){
    if(is_array($element)){
        if($is_array){
            return $element[$is_array];
        }else{
            return null;
        }
    }else{
        return strtolower($element);
    }
}

function parseUserAdTime($cui /*contact user info array*/,$cut /*contact user times array*/,$d=0 /* ad rtl */){
    $l=count($cui['p']);
    $t='';
    //$v=$d?' / ':' / ';
    $v='';
    if($l){
        $s='';
        $g='';
        $k=0;
        $r=array();
        for($i=0;$i<$l;$i++){
            $g=$cui['p'][$i]['v'];
            $k=$cui['p'][$i]['t'];
            $s='';
            if(!isset($r[$k])){
                switch($k){
                    case 1:
                        $s=($d?'موبايل':'Mobile');
                        break;
                    case 2:
                        $s=($d?'موبايل + فايبر':'Mobile + Viber');
                        break;
                    case 3:
                        $s=($d?'موبايل + واتساب':'Mobile + Whatsapp');
                        break;
                    case 4:
                        $s=($d?'موبايل + فايبر + واتساب':'Mobile + Viber + Whatsapp');
                        break;
                    case 5:
                        $s=($d?'واتساب فقط':'Whatsapp only');
                        break;
                    case 7:
                        $s=($d?'هاتف':'Phone');
                        break;
                    case 8:
                        $s=($d?'تلفاكس':'Telefax');
                        break;
                    case 9:
                        $s=($d?'فاكس':'Fax');
                        break;
                    case 13:
                        $s=($d?'فايبر + واتساب فقط':'Viber + Whatsapp only');
                        break;
                    default:
                        break;
                }
                $r[$k]=array(
                    's'=>' - '.$s.': ',
                    'c'=>0
                );
            }  
            if($r[$k]['c'])
                $r[$k]['s'].=$d?' او ':' or ';
            $r[$k]['s'].='<span class="pn">'.$g.'</span>';
            $r[$k]['c']++;
        }
        foreach ($r as $key => $value){
            $t.=$value['s'];
        }
    }
    if($cui['b']){
        $t.=' - '.($d?'بلاكبيري مسنجر':'BBM pin').': <span class="pn">'.$cui['b'].'</span>';
    }
    if($cui['t']){
        $t.=' - '.($d?'تويتر':'Twitter').': <span class="pn">'.$cui['t'].'</span>';
    }
    if($cui['s']){
        $t.=' - '.($d?'سكايب':'Skype').': <span class="pn">'.$cui['s'].'</span>';
    }
    if($cui['e']){
        $t.=' - '.($d?'البريد الإلكتروني':'Email').': <span class="pn">'.$cui['e'].'</span>';
    }
    if($t)
        $v.=substr($t,2);
    else $v='';
    return $v;
}

function generate_image_size($source_image_path, $thumbnail_image_path, $output_width){
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    if($output_width >= $source_image_width){
        $output_width = $source_image_width;
        $output_height = $source_image_height;
    }else{
        $output_height = (int)$source_image_height * $output_width / $source_image_width;
    }
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    
    $thumbnail_gd_image = imagecreatetruecolor($output_width, $output_height);
    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $output_width, $output_height, $source_image_width, $source_image_height);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            imagegif($thumbnail_gd_image, $thumbnail_image_path);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 60);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail_gd_image, $thumbnail_image_path);
            break;
    }
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}
?>
