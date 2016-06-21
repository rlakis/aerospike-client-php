<?php
/*
 * This is a crawler for me-properties.com real estate feed 
 * based in UAE
 */
$processItems = 1000;

$FEED_URI = 'http://xml.propspace.com/feed/xml.php?cl=1252&pid=8245&acc=8807';

$sections_map = array(
    "land" => 7,
    "land residential" => 7,
    "land commercial" => 7,
    "plots" => 7,
    "plot" => 7,
    
    '' => 1,
    "townhouse" => 1,
    "apartment" => 1,
    "hotel apartment" => 1,
    "full floor" => 1,
    "1 bhk" => 1,
    "2 bhk" => 1,
    "3 bhk" => 1,
    "penthouse" => 1,
    
    "furnished apartment" => 2,
    
    "villa" => 131,
    
    "office" => 6,
    
    'building' => 8,
    'commercial building' => 8,
    'residential building' => 8,
    'whole building' => 8,
    
    'studio' => 122,
    
    'hotel' => 419,
    
    'shop' => 5,
    'retail' => 5,
    
    "labour camp" => 162,
    
    "warehouse" => 111
);
$cities_map = array(
    "dubai" => 14,
    "ajman" => 436,
    "sharjah" => 333,
    "abu dhabi" => 4
);


include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "Starting Casabella crawler\n";
echo 'Fetching feed page: '.$FEED_URI,"\n";

$ads = array();

//$xml_feed = file_get_contents();
$user_id=1;

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
    
    $xml = simplexml_load_string($xml_feed, null, LIBXML_NOCDATA);
    if(!$xml){
        echo "Error parsing xml\n";
        exit(0);
    }
    $json = json_encode($xml);
    $feeds = json_decode($json,TRUE);
    $feeds = $feeds['Listing'];
    
    if(!$feeds || count($feeds)==0){
        echo "No feed records\n";
        exit(0);
    }
    
    $db = new DB($config);
    $db->ql = new SphinxQL($config['sphinxql'], $config['search_index']);
    
    
    
    $q='insert into ad_user
       (web_user_id,content,title,purpose_id,section_id,rtl,country_id,city_id,latitude,longitude,media)
       values (?,?,?,?,?,?,?,?,?,?,?) returning id';
    $insert_ad=$db->prepareQuery($q);    
    
    $q='update ad_user set content=?,state=?,media=?,date_added=current_timestamp where id=?';
    $update_ad=$db->prepareQuery($q);
    
    $error = 0;
    $i=0;
    $j=0;
    foreach($feeds as $ad){
        //if(in_array($ad['reference_code'],array("RHCR310","VOCS210","VOCS209","VOCS208","VOCS207","VOCS206","VOCS204","VOCS202"))) continue;
        $db->ql->resetFilters(true);
        $db->ql->setFilter("user_id", $user_id);
        $db->ql->addQuery('body', $ad['Property_Ref_No']);
        $searchRes = $db->ql->executeBatch(); 
        
        if($searchRes === false){
            echo "Error in Sphinx\n";
            exit(0);
        }
        if (isset($searchRes['total_found']) && $searchRes['total_found']) {
            continue;
        }
        if(isset($ad['Emirate'])) {            
            $city = getElementValue($ad['Emirate']);
        }else{
            echo '--------------------CITY ERROR----------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        if(isset($ad['Unit_Type'])) {            
            $section = getElementValue($ad['Unit_Type']);
        }else{
            echo '------------------SECTION ERROR---------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        if(isset($ad['Ad_Type'])) {            
            $purpose = getElementValue($ad['Ad_Type']);
        }else{
            echo '------------------CATEGORY ERROR--------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        $description = $ad['Web_Remarks'];

        $sections[$section] = isset($sections[$section])? $sections[$section]++ : 1;
        $purposes[$purpose] = isset($purposes[$purpose])? $purposes[$purpose]++ : 1;
        $cities[$city] = isset($cities[$city]) && $cities[$city]? $cities[$city]++ : 1;
               
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
        if(preg_match('/rent/i',$purpose)){
            $purpose_id=2;
        }
        if(preg_match('/sale/i',$purpose)){
            $purpose_id=1;
        }
        
        if(!$purpose_id){
            echo '-------------------PURPOSE MATCH--------------------',"\n";
            var_dump($ad);
            echo '----------------------------------------------------',"\n";
            $error=1;
            continue;
        }
        
        $raw_number = trim($ad['Listing_Agent_Phone']);
        
        $number = preg_replace('/^0/','+'.$country_code,$raw_number);
        if (!is_numeric($raw_number)) continue;
        
        if($raw_number[1]==5)
            $number_type = 1;
        else $number_type = 7;
        
        $email = isset($ad['Listing_Agent_Email']) && $ad['Listing_Agent_Email'] ? $ad['Listing_Agent_Email'] : '';
        $latitude = isset($ad['Latitude']) && $ad['Latitude'] ? $ad['Latitude'] : 0;
        $longitude = isset($ad['Longitude']) && $ad['Longitude'] ? $ad['Longitude'] : 0;
        $hasMap = ($latitude || $longitude) ? 1 : 2;
        $location = '';
        if($hasMap){
            $location .= isset($ad['Property_Name']) && $ad['Property_Name'] ? $ad['Property_Name'].' ' : '';
            $location .= isset($ad['Community']) && $ad['Community'] ? $ad['Community'] : '';
            $location = trim($location);
        }
        
        
        $content = array(
            'budget'        =>  0,
            'ro'            =>  $root_id,
            'pu'            =>  $purpose_id,
            'se'            =>  $section_id,
            'rtl'           =>  0,
            'other'         =>  '',
            'extra'         =>  array(
                't'         =>  2,
                'v'         =>  2,
                'p'         =>  2,
                'm'         =>  $hasMap,                
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
                'e'     =>  $email,
                't'     =>  '',
                's'     =>  ''
            ),
            'cut'       =>  array(
                't'     =>  0,
                'b'     =>  24,
                'a'     =>  6
            ),
            'lat'       =>  $latitude,
            'lon'       =>  $longitude,
            'loc'       =>  $location,
            'state'     =>  0,
            'pubTo'     =>  array(
                $cities_map[$city]  =>  $cities_map[$city]
            ),
            'website'   =>  '',
            'userLOC'   =>  'SYS',
            'version'   =>  2,
            'SYS_CRAWL' =>  1
            
        );
        
        $bathrooms = getElementValue($ad['No_of_Bathroom']);
        $bedroom = getElementValue($ad['No_of_Rooms']);
        $price = getElementValue($ad['Price']);
        $area = getElementValue($ad['Unit_Builtup_Area']);
        $area_unit = getElementValue($ad['unit_measure']);
        
        /*
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
        $description = preg_replace('/[.,!]$/', '', strtolower(trim($description)));*/
        
        $original = $description;
        
        $original = $description = preg_replace('/(?:<br \/>)+/i', ' - ', $description);
        $original = $description = preg_replace('/<br \/>|<\/p>/i', ' - ', $description);
        $original = $description = preg_replace('/<br \/>/i', ' - ', $description);
        $original = $description = preg_replace('/<.*?>/i', '', $description);
        $original = $description = preg_replace('/\s+/', ' ', $description);
        $original = $description = preg_replace('/-(?:(?:\s|)-)+/', ' ', $description);
        $description = preg_replace('/\s(?:(?:just|pls(?:\.|)|please)\s|)call\s.*$/i', '', $description);
        $original = $description = preg_replace('/[.,;](\s|)-/i', ' - ', $description);        
        
        $original = $description = trim($description);
        $original = $description = preg_replace('/[-.,]$/i', '', $description);
        $original = $description = preg_replace('/^[-.,]/i', '', $description);
        $original = $description = preg_replace('/[!]/i', '', $description);
        $original = $description = trim($description);
        $original = $description = preg_replace('/\s+/', ' ', $description);
        
        if(strlen($description) > 400){
            $description = preg_replace('/(?:ABOUT CASABELLA|Also available|facilities:|Building Amenities:|Building Facilities:|features:).*$/i', '$1', $description);
            
            $original = $description = trim($description);
            $original = $description = preg_replace('/[-.,]$/i', '', $description);
            $original = $description = preg_replace('/^[-.,]/i', '', $description);
            $original = $description = preg_replace('/[!]/i', '', $description);
            $original = $description = trim($description);
            $original = $description = preg_replace('/\s+/', ' ', $description);
        }
        
        
        if(strlen($description) > 400){
            $description = preg_replace('/(price.*\s-).*$/i', '$1', $description);
        
            $description = trim($description);
            $original = $description = preg_replace('/[-.,]$/i', '', $description);
            $original = $description = preg_replace('/^[-.,]/i', '', $description);
            $description = preg_replace('/[!]/i', '', $description);
            $description = trim($description);
            $description = preg_replace('/\s+/', ' ', $description);
        }
        
        $description = trim($description);
        $original = $description = preg_replace('/[-.,]$/i', '', $description);
        $original = $description = preg_replace('/^[-.,]/i', '', $description);
        $description = preg_replace('/([a-zA-Z])([.,])([a-zA-Z0-9])/i', '$1$2 $3', $description);
        $description = preg_replace('/\(\s/', '(', $description);
        $description = preg_replace('/\s\)/', ')', $description);
        $description = preg_replace('/([)])\./', '$1', $description);
        
        if(strlen($description) > 400){
            $description = $ad['Property_Title'];
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
            
        }
        
        $description.= ' - ref: '.$ad['Unit_Reference_No'];
        $description = $description.  json_decode('"\u200B"').' / please call '.ucfirst($ad['Listing_Agent']).parseUserAdTime($content['cui'], $content['cut']);
        
        if($section_id == 1){
            if(preg_match('/furnished/i',$description)){
                $section_id = 2;
            }
        }
        if(preg_match('/studio/i',$description)){
            $section_id = 122;
        }
        
        if(preg_match('/under construction|under-construction/i',$description)){
            $section_id = 1341;
        }
        
        /*            
        if($description==''){
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
        */
        
        
        //echo $ad['description_en'],"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo "INDEX ".$j++."\t LEGNTH: ".strlen($description)."\t Original ".strlen($original)."\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo $description,"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo $original , "\n";
        echo '----------------------------------------------------',"\n";
        echo "Section:\t{$section_id}\t\tPurpose:\t{$purpose_id}\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
        echo '----------------------------------------------------',"\n";
              
        
        $content['other']=$description;
        
        if( strlen($description) > 550){
            continue;
        }
        
        /*
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
        */
        $i++;
        if($i==$processItems){
            $insert_ad->closeCursor();
            $update_ad->closeCursor();
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
