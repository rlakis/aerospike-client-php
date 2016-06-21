<?php
/*
 * This is a crawler for me-properties.com real estate feed 
 * based in UAE
 */
$FEED_URI = 'http://hm.huskymonkey.org/XML/397990708171305/huskymonkey_portal_full_397990708171305.xml';

$processItems = 50;

$sections_map = array(
    ''              =>  1,
  "land"                    =>  7,
  "plots"                    =>  7,
  'plot'                    =>  7,
  "townhouse"               =>  1,
  "apartment"               =>  1,
  "duplex"               =>  1,
  "full floor"               =>  1,
  'show room'               =>  5,
   'retail'                 =>  5,
   'shop'                 =>  5,
  "furnished apartment"     =>  2,
  "villa"                   =>  131,
  "1 bhk"                   =>  1,
  "2 bhk"                   =>  1,
  "3 bhk"                   =>  1,
  "office"                  =>  6,
  "office space"                  =>  6,
  "pent house"              =>  1,
    "penthouse"            =>   1,
  'building'                =>  8,
  'bulk sale units'         =>  8,
  'whole building'          =>  8,
  'studio'                =>  122,
   'warehouse'              =>  111,
    "other"                 =>  105,
    'staff accommodation'   =>  162
  
);
$cities_map = array(
  "dubai"   =>  14,
  "ajman"   =>  436,
  "sharjah"  =>  333,
  'abu dhabi'=> 4,
  ""        =>  14,
  "ras al khaimah"    =>  814
);

$purpose_map = array(
    'rs'    =>   1,
    'cs'    =>   1,
    'rr'    =>   2,
    'cr'    =>  2,
    ''      =>  1
);


include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';

echo "Starting greatdealuae.com crawler\n";
echo 'Fetching feed page: http://hm.huskymonkey.org/XML/397990708171305/huskymonkey_portal_full_397990708171305.xml',"\n";

$ads = array();

//$xml_feed = file_get_contents();
$user_id=62414;
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
    
    /*
     <Properties>
<property>
<refno>MA5869</refno>
<status>vacant</status>
<lastupdated>2014-01-13 14:26:52</lastupdated>
<category>rr</category>
<sub_category>Apartment</sub_category>
<title>Amazing 1 BHK Apartment For Rent In Dubai At IMPZ</title>
<size>593</size>
<sizeunits>SqFt</sizeunits>
<price>6500</price>
<pricecurrency>AED</pricecurrency>
<bedrooms>1</bedrooms>
<bathrooms>1</bathrooms>
<developer>Deyaar</developer>
<contact_name>Murtada ALALAWI</contact_name>
<contactemail>murtada@greatdealuae.com</contactemail>
<agent_number>971 50 9501550</agent_number>
<agent_pciture_url>
http://hm.huskymonkey.org/MP14/hm_employees/202250708171703.png
</agent_pciture_url>
<project>Oakwood Residency</project>
<area>IMPZ</area>
<city>Dubai</city>
<country>United Arab Emirates</country>
<tags/>
<featured>0</featured>
<investment>0</investment>
<geopoint>25.04,55.1925</geopoint>
<publish_in_social_network>yes</publish_in_social_network>
<e1/>
<e2/>
<youtube_id>CpxdjvxZWfs</youtube_id>
<hm_pdf>
http://hm.huskymonkey.org/viewer/pdfs/392040113142530.pdf
</hm_pdf>
<description>
<![CDATA[
<br><br><br><br><strong>Amenities</strong><br><br>- Built in wardrobes<br>- Central a/c and heating<br>- Covered parking<br>- Pets allowed<br>- Security<br>- Shared gym<br>- Shared pool<br><br>The Oakwood Residency Tower is in a prime location in the International Media Production Zone in Dubai. T
]]>
<![CDATA[
he IMPZ is a mixed-use development which consists of commercial, residential, and entertainment divisions. Upon completion, the 18-storey tower will feature 344 luxury apartments with all amenities close at hand. Oakwood Residency will offer residents a range of facilities including a large rooftop swimming pool with panoramic views of the city. Residents will be able to enjoy the use of a gymnasium, as well as separate ladies and men sauna and steam rooms. The Oakwood Residency Tower is an exciting new development in Dubai and comes at a good time for the increasing number of investors. Deyaars managing director states that they are very excited about the launch and that the project represents a unique opportunity to participate in the emirates high-growth real estate sector. The development is receiving a lot of interest from investors worldwide.<br/><br/>We are a premiere estate brokerage and advisory service firm connecting Dubai real estate to buyers and tenants locally and globally. We have been in the real estate market for over 35 years serving sellers, buyers and tenants alike.
<br/><br/>
<br/><br/>Great Dubai Real Estate is one of the most renowned real estate companies dealing in Dubai residential and commercial properties. The company is well known for its state of the art realty services, exceptional customer services and a wide array of residential and commercial properties. 
<br/><br/>
<br/><br/>We take pride in working with industry professionals who help us picking the only prime developments and the best properties in order to fulfill our customersâ€™ purpose to invest in Dubai properties. Whether you are buying or renting property to live in or investing to get maximum revenue later in the future, you will find nothing less than the best from us. This makes us the market leaders. 
<br/><br/>We deal in the following city: Dubai
]]>
</description>
<photos>
<photo1>
http://hm.huskymonkey.org/plib/654880729071512/p183fajj1nuq41tg21f0f1lgc1lc1.jpg
</photo1>
<photo2>
http://hm.huskymonkey.org/plib/654880729071512/p183fajj1nuq41tg21f0f1lgc1lc1.jpg
</photo2>
<photo3>
http://hm.huskymonkey.org/plib/654880729071512/p180rega7th981ua2dnsnj417664.jpg
</photo3>
<photo4>
http://hm.huskymonkey.org/plib/654880729071512/p180rega7t1ht72e54b71p309es5.jpg
</photo4>
<photo5>
http://hm.huskymonkey.org/plib/654880729071512/p180rega7tcr3iqm41r1pf9diq7.jpg
</photo5>
<photo6>
http://hm.huskymonkey.org/plib/654880729071512/p180rega7tj1iqa0kmejcf16v51.jpg
</photo6>
</photos>
<languages/>
</property> 
     */
    
    $xml = simplexml_load_string($xml_feed, null, LIBXML_NOCDATA);
    if(!$xml){
        echo "Error parsing xml\n";
        exit(0);
    }
    $json = json_encode($xml);
    $feeds = json_decode($json,TRUE);
    $feeds = $feeds['Properties']['property'];
    
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
    
    $start_date = strtotime('2013-08-01 00:00:00');
    
    $ad_date = 0;
    foreach($feeds as $ad){
        $date = getElementValue($ad['lastupdated']);
        $ad_date = strtotime($date);
        
        if($ad_date > $start_date) {
            
            if(preg_match('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]/u', $ad['title'])){
                continue;
            }
            $sphinx->resetFilters();
            $sphinx->resetGroupBy();
    
            $ad['refno'] = preg_replace('/[()]/', '', $ad['refno']);
            
            $sphinx->setFilter("hold", array(0));
            $sphinx->setFilter("user_id", array($user_id));
            $searchRes=$sphinx->Query($ad['refno'], $config['search_index']);
            if($searchRes === false){
                echo "Error in Sphinx :".$ad['refno']."\n";
                var_dump($ad);
                exit(0);
            }
            if (isset($searchRes['total_found']) && $searchRes['total_found']) {
                continue;
            }
            if(isset($ad['city'])) {            
                $city = strtolower(getElementValue($ad['city']));
            }elseif(isset($ad['City'])) {
                $city = strtolower(getElementValue($ad['City']));
            }else{
                echo '--------------------CITY ERROR----------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }
            if(isset($ad['sub_category'])) {            
                $section = strtolower(getElementValue($ad['sub_category']));
            }else{
                echo '------------------SECTION ERROR---------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }
            if(isset($ad['category'])) {            
                $purpose = strtolower(getElementValue($ad['category']));
            }else{
                echo '------------------CATEGORY ERROR--------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }
            $description = $ad['description'];
            

            $sections[$section] = isset($sections[$section])? $sections[$section]+1 : 1;
            $purposes[$purpose] = isset($purposes[$purpose])? $purposes[$purpose]++ : 1;
            $cities[$city] = isset($cities[$city]) && $cities[$city]? $cities[$city]+1 : 1;

            if(!isset($sections_map[$section])){
                echo '--------------------SECTION MAP---------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }else{
                $section_id = $sections_map[$section];
            }

            if(!isset($purpose_map[$purpose])){
                echo '--------------------SECTION MAP---------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }else{
                $purpose_id = $purpose_map[$purpose];
            }

            if(!isset($cities_map[$city])){
                echo '----------------------CITY MAP----------------------',"\n";
                var_dump($ad);
                echo '----------------------------------------------------',"\n";
                $error=1;
                break;
            }else{
                $city_id = $cities_map[$city];
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
                    'e'     =>  $ad['contactemail'],
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
                'website'   =>  '',
                'userLOC'   =>  'SYS',
                'version'   =>  2,
                'SYS_CRAWL' =>  1

            );

            $bathrooms = getElementValue($ad['bathrooms']);
            $bedroom = getElementValue($ad['bedrooms']);
            $price = getElementValue($ad['price']);
            $currency = getElementValue($ad['pricecurrency']);
            $area = getElementValue($ad['size']);
            $area_unit = getElementValue($ad['sizeunits']);
            /*
            $description = preg_replace('/\n.*+/i', '', $description);
            $description = preg_replace('/\<br\/\>/i', ' ', $description);
            $description = preg_replace('/\<br\>/i', ' ', $description);
            $description = preg_replace('/<.*?>/i', '', $description);
            $description = preg_replace('/(?:for mor|for viewing).*?(?:\n|$)/i', '', $description);
            $description = preg_replace('/(?:tel(?:\s|:)|mob(?:\s|:)|www\.).*?(?:\n|$)/i', '', $description);
            $description = preg_replace('/(?:to see\.).*?(?:\n|$)/i', '', $description);
            $description = preg_replace('/(?:call \.).*?(?:\n|$)/i', '', $description);
            $description = preg_replace('/(?:please (?:call|mail)).*?(?:\n|$)/i', '', $description);
            $description = preg_replace('/\n/', ' ', $description);
            $description = preg_replace('/\s+/', ' ', $description);
            $description = preg_replace('/[.,;:]([a-zA-Z])/', '. $1', $description);
            $description = preg_replace('/([a-zA-Z])[.,]([0-9])/', '$1. $2', $description);
            $description = preg_replace('/[.,!]$/', '', strtolower(trim($description)));
*/
            /*
            if($description==''){
                $sub = getElementValue($ad['subcommunity']);
                $com = getElementValue($ad['community']);
                $description = $ad['type'].' '.($purpose_id==1 ? 'for sale':'for rent').' in '.($sub ? $ad['subcommunity'].', ':'').($com ? $ad['community'].', ':'').ucfirst($city);

            }*/
            
            $description = $ad['title'];

            if($bedroom){
                $description.= ' - '.$bedroom.' bedroom'.($bedroom>1 ? 's':'');
            }
            if($bathrooms){
                $description.= ' - '.$bathrooms.' bathroom'.($bathrooms>1 ? 's':'');
            }
            if($area){
                $description.= ' - '.number_format($area).' '.$area_unit;
            }
            if($price){
                $description.= ' - price '.number_format($price).' '.strtoupper($currency);
            }
            $description.= ' - ref: '.$ad['refno'];
            $description = $description.  json_decode('"\u200B"').' / for more details please call '.ucfirst($ad['contact_name']).parseUserAdTime($content['cui'], $content['cut']);


            if($section_id == 1){
                if(preg_match('/(\s|^)furnished/i',$description)){
                    $section_id = 2;
                }
                
                if($purpose=='cs'){
                    $section_id=6;
                }
            }
            if(preg_match('/studio/i',$description)){
                $section_id = 122;
            }
            if($section_id == 5 && preg_match('/restaurant/i',$description)){
                $section_id = 498;
            }
        if(preg_match('/hotel/i',$description)){
            $section_id = 419;
        }

            $description = preg_replace('/\s+/', ' ', $description);
            
            if(preg_match('/对|面|高|档|公|寓|出|租|两|房|售/u', $description)){
                continue;
            }
            
            if(strlen($description)>500){
                continue;
            }

            echo $ad['description'],"\n";
            echo '----------------------------------------------------',"\n";
            echo $description,"\n";
            echo '----------------------------------------------------',"\n";
            echo "Section:\t{$section_id}\t\tPurpose:\t{$purpose_id}\n";
            echo '----------------------------------------------------',"\n";
            echo '----------------------------------------------------',"\n";
            echo '----------------------------------------------------',"\n";
            echo '----------------------------------------------------',"\n";


            $content['other']=$description;

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
                'title'     =>  $ad['refno']
                );
            */

            /*insert ad*/
            
            $insert_ad->bindValue(1, $user_id);
            $insert_ad->bindValue(2, json_encode($content));
            $insert_ad->bindValue(3, $ad['refno']);
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

            $photos = $ad['photos'];
            $pCount = count($photos);
            if($pCount>5)$pCount=5;
            $succeededPhotos=array();
            $default_pic = '';
            $path = '/var/www/mourjan/web/repos/';
            //for ($k=0;$k<$pCount;$k++){
            $k=0;
            
            $processed = array();
            
            foreach($photos as $photo){
                if(isset($processed[$photo])) continue;
                preg_match('/(\.(?:jpg|png|gif|jpeg))$/',$photo,$matches);
                if($matches && count($matches)){
                    $ext = $matches[1];
                    $name = $ad_id.'_'.$k.$ext;
                    $img = $path.'l/'.$name;
                    $pcn = file_get_contents($photo);
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
                    $k++;
                    $processed[$photo]=1;
                    if($k==$pCount) break;
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
}
catch(Exception $ex){
    var_dump($ex);
    $error = 1;
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
$db->close();
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
