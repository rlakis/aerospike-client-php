<?php
use Core\Model\Ad;
use Core\Model\Classifieds;

ini_set('memory_limit','1024M');

const SECTION_MAP = [
        'Apartment'                 => 1,   /*OK*/
        'Penthouse'                 => 1,   /*OK*/
        'duplex'                    => 1,
        'loft apartment'            => 1,
        'Townhouse'                 => 1,   /*OK*/
        ''                          => 1,
        'Compound'                  => 1,   /*OK*/
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
            
        'Commercial Land'           => 7,
        'Residential Land'          => 7,
        'Mixed used land'           => 7,
        'land'                      => 7,
        'plots'                     => 7,
        'plot'                      => 7,
                
        'commercial half floor'     => 5,
        'commercial full floor'     => 5,
        'shop'                      => 5,
        'Retail'                    => 5,

        'building'                  => 8,
        'Commercial Building'       => 346, /*OK*/
        'whole building'            => 8,
        'Residential Building'      => 8,
        'commercial full building'  => 8,
            
        'Villa'                     => 131, /*OK*/
        'Commercial Villa'          => 131,
        'Commercial Villas Compound'=> 131,
                    
        'Office'                    => 6, /*OK*/
                
        'warehouse'                 => 111,
                    
        'labour camp'               => 162,
        'staff accommodation'       => 162,
                
        'hotel'                     => 419,

        'bungalow'                  => 4,
                
        'studio'                    => 122,
        'Studio'                    => 122,
            
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
    'Al Ain'                => 6
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

//if (!file_exists($argv[1])) {
//    echo $argv[1], " File does not exists!\n";
//    return;    
//}

if (substr($argv[1], -3)!=='xml') {
    echo $argv[1], " File is not xml!\n";
    return; 
}

include_once __DIR__.'/../../../config/cfg.php';
include_once __DIR__.'/../../../deps/autoload.php';

Config::instance()->incModelFile('Router')->incModelFile('Db')->incModelFile('Classifieds')
        ->incLibFile('MCUser')->incLibFile('IPQuality')->incLibFile('MCSaveHandler');


Core\Model\Router::instance()->cache();

$uid=3307635;

$parser=new Aqary($uid, $argv[1]);
$parser->load();
$parser->post();

echo \gethostname(), "\n";
class Aqary {
    private int $uid;
    private string $url;
    
    private Core\Lib\MCUser $profile;
    private libphonenumber\PhoneNumberUtil $phoneNumberUtil;
    private Core\Model\DB $db;
    private string $mcn;
    public array $ads=[];
    private array $userAds=[];
    private string $userPath;
    //private string $crmPath;
    private string $repository='/var/db/mourjan-pix/repos/';
    private string $crmPath='/var/db/mourjan-pix/aqarycrm/';

    public function __construct(int $uid, string $url) {
        if (\gethostname()==='h5.mourjan.com') {
            $this->repository='/var/www/mourjan-pix/repos/';
            $this->crmPath='/var/www/mourjan-pix/aqarycrm/';
        }
        
        $this->uid=$uid;
        $this->url=$url;
        $this->profile=new Core\Lib\MCUser($this->uid);
        $this->phoneNumberUtil=libphonenumber\PhoneNumberUtil::getInstance();        
        $num=$this->phoneNumberUtil->parse($this->profile->getMobileNumber(), 'AE');
        $this->mcn=$this->phoneNumberUtil->getRegionCodeForNumber($num);
        $this->db=new \Core\Model\DB();
        $rs=$this->db->get("select doc_id, id, state, last_update from ad_user where web_user_id={$this->uid} and doc_id>''");
        foreach ($rs as $record) {
            $this->userAds[$record['DOC_ID']]=['ID'=>$record['ID'], 'STATE'=>$record['STATE'], 'LUT'=>$record['LAST_UPDATE']];
        }
        unset($rs);
        //var_dump($this->userAds);
        //if (1) die ('End');
    }
    
    
    public function load() : void {
        $xml=\simplexml_load_string(\file_get_contents($this->url), null, LIBXML_NOCDATA);
        $json=\json_encode($xml);
        $feed=\json_decode($json, true);
        $i=0;
        $groupId=floor($this->uid/10000);
        $this->userPath='p'.$groupId.'/'.$this->uid.'/';
        if (!file_exists($this->crmPath.$this->userPath)) { mkdir($this->crmPath.$this->userPath, 0777, true); }
        if (!file_exists($this->repository.'l/'.$this->userPath)) { mkdir($this->repository.'l/'.$this->userPath, 0777, true); }
        if (!file_exists($this->repository.'d/'.$this->userPath)) { mkdir($this->repository.'d/'.$this->userPath, 0777, true); }
        if (!file_exists($this->repository.'m/'.$this->userPath)) { mkdir($this->repository.'m/'.$this->userPath, 0777, true); }
        if (!file_exists($this->repository.'s/'.$this->userPath)) { mkdir($this->repository.'s/'.$this->userPath, 0777, true); }

        foreach ($feed['property'] as $k=>$item) {
            $last_update=$item['@attributes']['last_update'];
            $ad=new \Core\Model\Ad([]);
            $ad->setDocumentId(\strtoupper(\trim($item['reference_number']??'')));
            echo $k,'/',$i, "\t", $last_update, "\t", $this->userAds[$ad->documentId()]['LUT']??'-', "\n";
            
            if ($last_update<($this->userAds[$ad->documentId()]['LUT']??'')) {
                continue;
            }
            $ad->setUID($this->uid)
                ->setDataSet(new Core\Model\Content($ad))                
                ->setSectionID(SECTION_MAP[\trim($item['property_type'])]??0)
                ->setPurposeId(PURPOSE_MAP[$item['purpose']]??0)
                ->setCountryId(COUNTRY_MAP[$item['country']][0]??0)
                ->setCountryCode(COUNTRY_MAP[$item['country']][1]??'')
                ->setPrice($item['price']??0);             
    
            
            $ad->dataset()->setUID($this->uid)->setApp('web', '1.0.0')
                ->setIpAddress('127.0.0.1')
                ->setIpCountry(COUNTRY_MAP[$item['country']][1]??'')
                ->setAdCountry(COUNTRY_MAP[$item['country']][1]??'')
                ->setMobileCountry($this->mcn)
                ->setCityId(CITY_MAP[$item['city']]??0)
                ->setUserLevel($this->profile->getLevel())
                ->setUserLanguage('en')
                ->setUserLocation('SYS')
                ;

            $ad->dataset()->setRegions([$ad->cityId()]);
            
            $ad->propertyId=$item['property_id']??0;
            
            
            if (isset($item['agent']['phone'])) {
                if (is_integer($item['agent']['phone'])) {
                    $this->addPhoneNumber($ad, $item['agent']['phone']);
                } else {
                    print_r($item['agent']['phone']);
                }
            }
            
            if (isset($item['agent']['email']) && strlen($item['agent']['email'])>0) {
                $email=IPQuality::getEMailStatus($item['agent']['email']);
                if (($email['valid']??0)===1) {
                    $ad->dataset()->setEmail($email['sanitized_email']);
                }
            }
            
            if (isset($item['geopoints']) && is_string($item['geopoints']) && strpos($item['geopoints'], ',')>0) {
                $geopoints=explode(",",  $item['geopoints']);
                if (is_array($geopoints) && count($geopoints)===2) {
                    $ad->dataset()->setCoordinate(floatval($geopoints[0]), floatval($geopoints[1]));
                }
            }
            $this->compose($ad, $item);
            
            if ($this->isValid($ad, $item)) {
                $succeededPhotos=[];
                
                if (isset($item['photo']) && \is_array($item['photo']) && isset($item['photo']['url']) && \is_array($item['photo']['url'])) {                    
                    foreach ($item['photo']['url'] as $photoURL) {
                        $tmp=explode('/', $photoURL);
                        $crmPhotoId=$tmp[count($tmp)-2];                                               
                            
                        $headers=$this->getHeaders($photoURL);
                        $contentType=$headers['Content-Type']??'';
                        $contentLength=($headers['Content-Length']??0)+0;
                        if ($contentLength>0 && substr($contentType,0,6)==='image/') {
                            echo $ad->documentId(),': ', $crmPhotoId, "\t", $contentType, "\t", $contentLength, "\n";
                            $image_name=$crmPhotoId.'.'. strtolower(explode("/", $contentType)[1]);
                            
                            $crm_image_name='aqary-'.$image_name;
                            $crm_image_path=$this->crmPath.$this->userPath.$crm_image_name;                            
                            $image_size=file_exists($crm_image_path)?filesize($crm_image_path):0;
                            $signature='';
                            
                            if (!file_exists($crm_image_path) || $image_size===false || $image_size!==$contentLength) {
                                echo 'Downloading ', $photoURL, ' to ', $crm_image_path, "\n"; 
                                set_time_limit(0);
                                $fp=fopen($crm_image_path, 'w+');
                                $ch=curl_init(str_replace(" ","%20",$photoURL));
                                curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                                curl_setopt($ch, CURLOPT_FILE, $fp); 
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_exec($ch); 
                                curl_close($ch);
                                fclose($fp);
                            }
                                                        
                            if (file_exists($crm_image_path) ) {
                                if ($duplicate=$this->checkImageDuplicate($crm_image_path, $signature)) {      
                                    $info=pathinfo($duplicate['FILENAME']);
                                    if (file_exists($this->crmPath.$info['dirname'].'/aqary-'.$info['basename'])) {
                                        echo "A: ", $crm_image_path, "\n";
                                        echo "B: ", $this->crmPath.$info['dirname'].'/aqary-'.$info['basename'], "\n";
                                        $image_name=$info['filename'].'.'.$info['extension'];
                                        $crm_image_path=$this->crmPath.$info['dirname'].'/aqary-'.$info['basename'];
                                    }
                                    else {
                                        $this->db->queryResultArray('update media set filename=? where signature=? and filename starting with ?', [$this->userPath.$image_name, $signature, $this->userPath], true);                                               
                                    }
                                }
                                
                                if ($this->generate_images_sizes($crm_image_path, $image_name)) {                                    
                                    list($image_width, $image_height)=@getimagesize($this->repository.'d/'.$this->userPath.$image_name);
                                    if ($image_width>0 && $image_height>0 && count($succeededPhotos)<5) {
                                        $succeededPhotos[$this->userPath.$image_name]=[$image_width, $image_height];
                                        $this->db->queryResultArray("update or insert into media (signature, filename, width, height) values (?, ?, ?, ?) matching (signature, filename)", 
                                                [$signature, $this->userPath.$image_name, $image_width, $image_height], true);
                                    }
                                }
                                else {
                                    echo "Thumbnails failed\n";
                                }
                            }                                                        
                        }
                        
                        if (count($succeededPhotos)>=5) {  break;  }
                    }
                }
                
                $ad->dataset()->setPictures($succeededPhotos);
                
                $normalizer=new MCSaveHandler();                
                $normalized=$normalizer->getFromContentObject($ad->dataset()->getArray());
                //print_r($normalized);
                if ($normalized && \is_array($normalized)) {                    
                    $ad->dataset()->setNativeText($normalized['other']);
                    $ad->dataset()->setForeignText($normalized['altother']??'');
                    $ad->dataset()->setAttributes($normalized['attrs']);
                    $ad->dataset()->setQualified($normalized['qualified']?1:0);
                    $this->ads[]=$ad;
                }
                
            }
            else {
                echo "Invalid ad!\n";
            }
            $i++;
            $this->print($ad); 
            if ($i>100) {  break;  }
        }
        
        if (\gethostname()!=='h5.mourjan.com') {
            system("sshpass -p '4eDB6WifsxE5sK' rsync -arP /var/db/mourjan-pix/aqarycrm/p{$groupId} h5.mourjan.com:/var/www/mourjan-pix/aqarycrm/");
            system("sshpass -p '4eDB6WifsxE5sK' rsync -arP /var/db/mourjan-pix/repos/l/p{$groupId} h5.mourjan.com:/var/www/mourjan-pix/repos/l/");
            system("sshpass -p '4eDB6WifsxE5sK' rsync -arP /var/db/mourjan-pix/repos/d/p{$groupId} h5.mourjan.com:/var/www/mourjan-pix/repos/d/");
            system("sshpass -p '4eDB6WifsxE5sK' rsync -arP /var/db/mourjan-pix/repos/m/p{$groupId} h5.mourjan.com:/var/www/mourjan-pix/repos/m/");
            system("sshpass -p '4eDB6WifsxE5sK' rsync -arP /var/db/mourjan-pix/repos/s/p{$groupId} h5.mourjan.com:/var/www/mourjan-pix/repos/s/");
        }
    }
    
    
    private function compose(Ad $ad, array $property) : void {
        \mb_regex_encoding('UTF-8');
        $ar=$property['description_ar']??'';
        $en=$property['description_en']??'';
        if (!is_string($ar)) $ar='';
        if (!is_string($en)) $en='';
        $ar=trim(\str_replace("\n", " ", $ar));
        $en=trim(\str_replace("\n", " ", $en));
        $this->collectPhoneNumbers($ad, $ar);
        $this->collectPhoneNumbers($ad, $en);  
        
        $size=$property['size']??0;
        $unit=\strtoupper(\trim($property['property_size_unit']??''));
        $community=\trim($property['community']??'');
        $sub_community=\trim($property['sub_community']??'');
        $community_name=\trim($property['property_name']??'');
        
        if (\mb_strlen($ar)>0) {
            $ar=\preg_replace('/\s+/', ' ', $ar);
            $ar=\preg_replace('/لمزيد من المعلومات.*/mis', '', $ar);
            $ar=\preg_replace('/For more information.*/mis', '', $ar);
            $ar=\trim($ar);
            //echo $ar, "\n";
            
        }
        
        if (\mb_strlen($en)>0) {
            $en=\preg_replace('/For more information.*/mis', '', $en);
            $en=\preg_replace('/\bsq\.f\b/mis', 'sqft', $en);
            $en=\trim($en);
        }        
                        
        $location=$community;
        if ($sub_community) {
            $location.=$community?' - ':'';
            $location.=$sub_community;
        }
        $ad->dataset()->setLocation($location);
        
        if ($en) {
            if ($community && !\preg_match("/{$community}/mis", $en)) { $en.=" in {$community}"; }
            if ($sub_community && !\preg_match("/{$sub_community}/mis", $en)) { $en.=", {$sub_community}"; }
            if ($community_name && !\preg_match("/{$community_name}/mis", $en)) { $en.=", {$community_name}"; }
            if ($size>0 && $unit && !\preg_match('/area\s*:?\s*([0123456789,]+)\s*sq/mis', $en)) {
                $en.=" - {$size} {$unit}";
            }
            if ($ad->price()>0 && !\preg_match('/price\s+\d+/mis', $en)) {
                $en.=" - Price {$ad->price()}";
            }
            
            if (\mb_strlen($ad->documentId())>1) {
                $en.="- Reference {$ad->documentId()}";
            }
        }
        
        if ($ar) {
            if ($size>0 && $unit && \preg_match('/مساح[ةه]\s*\p{Arabic}*\s*[:]?\s*([0-9,]+)\s*(قدم)?/u', $ar)===0) {
                if ($unit==='SQFT') {
                    $unit='قدم';
                }
                $ar.=" - {$size} {$unit}";
            }
            
            if ($ad->price()>0) {
                $purpose=$ad->purposeId()===2?'الايجار':'السعر';
                $ar.=" - {$purpose} {$ad->price()}";
            }
            
             if (\mb_strlen($ad->documentId())>1) {
                $ar.="- المرجع {$ad->documentId()}";
            }
        }
            
            
        if (\mb_strlen($ar)>0 && \mb_strlen($en)) {
            $ad->dataset()->setNativeText($ar);
            $ad->dataset()->setForeignText($en);
        }
        else if (\mb_strlen($ar)>0 || \mb_strlen($en)) {
            $ad->dataset()->setNativeText(\mb_strlen($ar)>0?$ar:$en);
        }
        
        //echo $ad->dataset()->getNativeRTL(), "\n";
        //echo $ad->dataset()->getNativeText(), "\n";
        //echo $ad->dataset()->getForeignText(), "\n";
                
    }
    
    
    private function collectPhoneNumbers(Ad $ad, string $text) : void {
        if (\mb_strlen($text)>0 && \preg_match_all('/(0\d{7,9}|0\d\s\d{3}\s\d{4}\b)/', $text, $matches)) {
            foreach ($matches as $match) {
                try {
                    $this->addPhoneNumber($ad, ($match[1]??\preg_replace('/\s+/', '', $match[0]))+0);
                }
                catch (Exception $e) {
                    echo $e->getMessage(), "\n";
                    var_dump($matches);
                }
            }
        }        
    }
    
    
    public function post() {
        echo $this->uid, "\n";
        foreach ($this->ads as $ad) {
            if ( !empty($ad->dataset()->getContactInfo()) ) {
                $ad->setId($this->userAds[$ad->documentId()]['ID']??0);
                $ad->dataset()->setState(2);
                $ad->dataset()->save();
            }
        }
    }
    
    
    private function getHeaders(string $url) : array {
        $headers=[];
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Connection: keep-alive", "Keep-Alive: timeout=5, max=100"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        
        $output=curl_exec($ch);
        curl_close($ch);
                        
        $data=explode("\n",trim($output));
        $headers['status']=$data[0];
        array_shift($data);
        foreach($data as $part){
            //some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
            $middle=explode(":",$part,2);
            //Supress warning message if $middle[1] does not exist, Thanks to @crayons
            if ( !isset($middle[1]) ) { $middle[1] = null; }
            $headers[trim($middle[0])] = trim($middle[1]);
        }
        return $headers;
    } 
    
    
    private function checkImageDuplicate(string $filename, string &$signature) {
        $imagick_type=new Imagick;
        $file_handle_for_viewing_image_file=@fopen($filename, 'r');
        $image=null;
        try {
            if ($file_handle_for_viewing_image_file!==false) {
                $imagick_type->readImageFile($file_handle_for_viewing_image_file);
                $signature=$imagick_type->getImageSignature();
                $res=$this->db->queryResultArray('select * from media where signature=? and filename starting with ?', [$signature, $this->userPath], true, PDO::FETCH_ASSOC);
                if ($res && count($res)>0) {                     
                    $image=$res[0];                     
                }
            }            
        }
        catch(Exception $e) {
            $image=false;
        }
        return $image;
    }
    
    
    private function generate_images_sizes(string $orginal, string $image_name) : bool {
        //echo __FUNCTION__, ': ', $orginal, "\n";
        $this->generate_image_size($orginal, $this->repository.'l/'.$this->userPath.$image_name);
        $this->generate_image_size($orginal, $this->repository.'d/'.$this->userPath.$image_name, 1024);
        $this->generate_image_size($orginal, $this->repository.'m/'.$this->userPath.$image_name, 480);
        $this->generate_image_size($orginal, $this->repository.'s/'.$this->userPath.$image_name, 200);
        return (file_exists($this->repository.'l/'.$this->userPath.$image_name) && 
                file_exists($this->repository.'d/'.$this->userPath.$image_name) && 
                file_exists($this->repository.'m/'.$this->userPath.$image_name) && 
                file_exists($this->repository.'s/'.$this->userPath.$image_name));
                                
    }
    
    
    private function generate_image_size(string $source_image_path, string $thumbnail_image_path, int $output_width=10000) : bool {
        if (file_exists($thumbnail_image_path) && filesize($thumbnail_image_path)>2048) {
            return true;
        }
        
        list($source_image_width, $source_image_height, $source_image_type)=getimagesize($source_image_path);
        if ($output_width>=$source_image_width) {
            $output_width=$source_image_width;
            $output_height=$source_image_height;
        }
        else {
            $output_height=(int)$source_image_height*$output_width/$source_image_width;
        }
        
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image=@imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image=@imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image=@imagecreatefrompng($source_image_path);
                break;
        }
        
        if (!$source_gd_image) {
            @unlink($source_image_path);
            return false;
        }

        $thumbnail_gd_image=imagecreatetruecolor($output_width, $output_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $output_width, $output_height, $source_image_width, $source_image_height);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                imagegif($thumbnail_gd_image, $thumbnail_image_path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 70);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail_gd_image, $thumbnail_image_path);
                break;
        }
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
        $info=pathinfo($thumbnail_image_path);
        $this->cwebp($source_image_path, $info['dirname'].'/'.$info['filename'].'.webp', $output_width, 0);
        return true;
    }
    
    
    private function cwebp(string $source, string $outfile, int $nw=0, int $nh=0) : void {
        $info = pathinfo($source);
		
    	$cmd = "/usr/local/bin/cwebp -quiet";
    	if ($info['extension']==='png') {
            $cmd.=" -near_lossless 50";
    	}
    	else {
            $cmd.=" -jpeg_like";
    	}
    	$cmd.=" -m 6 -q 75 -sns 70 -mt -low_memory";
            
    	if ($nw>0||$nh>0) {
            $cmd.=" -resize {$nw} {$nh}";
    	}
    	$cmd.=" {$source} -o {$outfile}";
    	//echo $cmd, "\n";
    
    	$retval=-1;
        $out=system($cmd, $retval);  
    	//echo __FUNCTION__.': '.$retval.PHP_EOL.$cmd.PHP_EOL.$out, "\n";        
    }
    
    
    private function addPhoneNumber(Ad $ad, int $number) : void {
        $type=0;
        $num=$this->phoneNumberUtil->parse("{$number}", $ad->countryCode());
        if ($num && $this->phoneNumberUtil->isValidNumber($num)) {
            $type=$this->phoneNumberUtil->getNumberType($num);
            switch($type) {
                case 0:
                    $type=7;
                    break;
                case 1:
                case 2:
                case 5:
                    $type=1;
                    break;
                default:
                    $type=0;
                    break;
            }
        }
        
        if ($type>0) {            
            $ad->dataset()->addPhone($num->getCountryCode(), $this->phoneNumberUtil->getRegionCodeForNumber($num), $num->getNationalNumber(), $type,
            $this->phoneNumberUtil->format($num, libphonenumber\PhoneNumberFormat::E164));
        }        
    }
    
    
    private function isValid(Ad $ad, array $item) : bool {
        $result=true;
        if ($ad->sectionId()===0) {
            echo 'Invalid section: ', $item['property_type'], "\n";
            $result=false;
        }
        if ($ad->countryId()===0) {
            echo 'Invalid country: ', $item['country'], "\n";
            $result=false;
        }
        if ($ad->cityId()===0) {
            echo 'Invalid city: ', $item['city'], "\n";
            $result=false;
        }
        if ($ad->purposeId()===0) {
            echo 'Invalid purpose: ', $item['purpose'], "\n";
            $result=false;
        }
        if (empty($ad->documentId())) {
            $result=false;
        }
        return $result;
    }
    
    
    public function print(Ad $ad) : void {
        echo 'root: ', $ad->rootId(), "\n";
        echo 'section: ', $ad->sectionId(), "\n";
        echo 'purpose: ', $ad->purposeId(), "\n";
        echo 'geopoints: ', $ad->latitude(), ', ', $ad->longitude(), "\n";
        echo 'price: ', $ad->price(), "\n";
        echo 'document: ', $ad->propertyId, "\n";
        echo 'reference: ', $ad->documentId(), "\n";
        print_r($ad->dataset()->getAsVersion(3, false));
        echo '----------------------------------------------------------------------------------------------------------',"\n";
    }
    
}
