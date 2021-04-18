<?php
use Core\Model\Ad;
use Core\Model\Classifieds;

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
        'land mixed use'            => 7,
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


//Config::instance()->incModelFile('NoSQL')->incLibFile('MCSessionHandler')->incLibFile('Logger');

Core\Model\Router::instance()->cache();

$uid=3307635;

$parser=new Aqary($uid, $argv[1]);
$parser->load();

class Aqary {
    private int $uid;
    private string $url;
    private Core\Lib\MCUser $profile;
    private libphonenumber\PhoneNumberUtil $phoneNumberUtil;
    private Core\Model\DB $db;
    private string $mcn;
    public array $ads=[];
    private string $userPath;
    //private string $crmPath;
    private string $repository='/tmp/mourjan-pix/repos/';
    private string $crmPath='/tmp/mourjan-pix/aqarycrm/';

    public function __construct(int $uid, string $url) {
        $this->uid=$uid;
        $this->url=$url;
        $this->profile=new Core\Lib\MCUser($this->uid);
        $this->phoneNumberUtil=libphonenumber\PhoneNumberUtil::getInstance();        
        $num=$this->phoneNumberUtil->parse($this->profile->getMobileNumber(), 'AE');
        $this->mcn=$this->phoneNumberUtil->getRegionCodeForNumber($num);
        $this->db=new \Core\Model\DB();
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
            $ad=new \Core\Model\Ad([]);
            $ad->setUID($this->uid)
                ->setDataSet(new Core\Model\Content($ad))                
                ->setSectionID(SECTION_MAP[$item['property_type']]??0)
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
            $ad->propertyReference=$item['reference_number']??'';
            
            if (isset($item['agent']['phone'])) {
                $this->addPhoneNumber($ad, $item['agent']['phone']);
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
            $en=$item['description_en']??'';
            preg_match_all('/(0\d{7,9}|0\d\s\d{3}\s\d{4}\b)/', $en, $matches);
            
            foreach ($matches as $match) {
                $this->addPhoneNumber($ad, $match[1]+0);
            }
            $en=\preg_replace('/For more information.*/mis', '', $en);
            $en=\preg_replace('/\bsq\.f\b/mis', 'sqft', $en);
            
            $size=$item['size']??0;
            $community=$item['community']??'';
            $sub_community=$item['sub_community']??'';
            $community_name=$item['property_name']??'';
            $ad->dataset()->setLocation($community);
            if ($community && !\preg_match("/{$community}/mis", $en)) {
                $en.=" in {$community}";
            }
            if ($sub_community && !\preg_match("/{$sub_community}/mis", $en)) {
                $en.=", {$sub_community}";
            }
            if ($community_name && !\preg_match("/{$community_name}/mis", $en)) {
                $en.=", {$community_name}";
            }
            if ($size>0 && isset($item['property_size_unit']) && !\preg_match('/area\s+\d+/mis', $en)) {
                $en.=" - {$size} {$item['property_size_unit']}";
            }
            if ($ad->price()>0 && !preg_match('/price\s+\d+/mis', $en)) {
                $en.=" - Price {$ad->price()}";
            }
            
            if (mb_strlen($ad->propertyReference)>1) {
                $en.="- Reference {$ad->propertyReference}";
            }
            $ad->dataset()->setNativeText($en);
            
            //var_dump($en);
            if ($this->isValid($ad, $item)) {
                $succeededPhotos=[];
                if (isset($item['photo']) && \is_array($item['photo']) && isset($item['photo']['url'])) {                    
                    $imgIdx=0;                    
                    foreach ($item['photo']['url'] as $photoURL) {
                        $tmp=explode('/', $photoURL);
                        $crmPhotoId=$tmp[count($tmp)-2];                                               
                            
                        $headers=$this->getHeaders($photoURL);
                        $contentType=$headers['Content-Type']??'';
                        $contentLength=($headers['Content-Length']??0)+0;
                        if ($contentLength>0 && substr($contentType,0,6)==='image/') {
                            echo $crmPhotoId, "\t", $contentType, "\t", $contentLength, "\n";
                            $image_name=$crmPhotoId.'.'. strtolower(explode("/", $contentType)[1]);
                            
                            $crm_image_name=$this->uid.'-'.$image_name;
                            $crm_image_path=$crmPath.$dir.$crm_image_name;
                            
                            $image_size=file_exists($crm_image_path)?filesize($crm_image_path):0;
                            //$is_new_image=false;
                            $signature='';
                            $media_id=0;
                            if (!file_exists($crm_image_path) || $image_size===false || $image_size!==$contentLength) {
                                set_time_limit(0);
                                $fp=fopen($crm_image_path, 'w+');
                                $ch=curl_init(str_replace(" ","%20",$photoURL));
                                curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                                curl_setopt($ch, CURLOPT_FILE, $fp); 
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_exec($ch); 
                                curl_close($ch);
                                fclose($fp);
                                //$is_new_image=true;
                            }
                            //else {
                            //    $is_new_image=!file_exists($path.'l/'.$dir.$image_name)||!file_exists($path.'d/'.$dir.$image_name)||!file_exists($path.'m/'.$dir.$image_name)||!file_exists($path.'s/'.$dir.$image_name);
                            //}
                            
                            if (file_exists($crm_image_path) ) {
                                if ($duplicate=$this->checkImageDuplicate($crm_image_path, $signature)) {                                    
                                    $media_id=$duplicate['ID'];     
                                    $info=pathinfo($duplicate['FILENAME']);
                                    $image_name=$info['filename'].'.'.$info['extension'];
                                    $crm_image_path=$this->crmPath.$duplicate['FILENAME'];
                                }
                                
                                $thumbnails_success=$this->generate_images_sizes($crm_image_path, $image_name);
                            
                            
                                if (file_exists($path.'l/'.$dir.$image_name) && file_exists($path.'d/'.$dir.$image_name) && file_exists($path.'m/'.$dir.$image_name) && file_exists($path.'s/'.$dir.$image_name)) {
                                    if ($dupplicate=$this->checkImageDuplicate($crm_image_path, $signature)) {
                                        list($image_width, $image_height)=@getimagesize($path.'d/'.$dir.$image_name);
                                        if ($image_width>0 && $image_height>0) {
                                            $succeededPhotos[$dupplicate['FILENAME']]=[$image_width, $image_height];
                                            $media_id=$dupplicate['ID'];
                                            //$succeededPhotos[$dir.$image_name]=[$image_width, $image_height];                                    
                                        }
                                    }
                                    else {
                                    
                                    }
                                }
                            
                            }
                            
                            
                        }
                    }
                }
                
                $ad->dataset()->setPictures($succeededPhotos);
                
                $normalizer=new MCSaveHandler();                
                $normalized=$normalizer->getFromContentObject($ad->dataset()->getArray());
                if ($normalized && \is_array($normalized)) {                    
                    $ad->dataset()->setNativeText($normalized['other']);
                    $ad->dataset()->setAttributes($normalized['attrs']);
                    $ad->dataset()->setQualified($normalized['qualified']?1:0);
                    $this->ads[]=$ad;
                }
            }
            else echo "Invalid ad!\n";
            $this->print($ad); 
            $i++;
            if ($i>1) {  break;  }
        }
    }
    
    
    public function test() {
        foreach ($this->ads as $ad) {
            
        }
    }
    
    
    private function getHeaders(string $url) : array {
        $headers=[];
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
        $imagick_type=new Imagick();
        $file_handle_for_viewing_image_file=@fopen($filename, 'r');
        $image=null;
        try {
            if ($file_handle_for_viewing_image_file!==false) {
                $imagick_type->readImageFile($file_handle_for_viewing_image_file);
                $signature=$imagick_type->getImageSignature();
                $res=$this->db->queryResultArray('select * from media where signature=?', [$signature], true, PDO::FETCH_ASSOC);
                var_dump($res);
                if ($res && count($res)) { $image=$res[0]; }
            }
        }
        catch(Exception $e) {
            $image=false;
        }
        return $image;
    }
    
    
    private function generate_images_sizes(string $orginal, string $image_name) : bool {
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
		
    	$cmd = "/usr/local/bin/cwebp";
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
    	echo $cmd, "\n";
    
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
        return $result;
    }
    
    
    public function print(Ad $ad) : void {
        echo 'root: ', $ad->rootId(), "\n";
        echo 'section: ', $ad->sectionId(), "\n";
        echo 'purpose: ', $ad->purposeId(), "\n";
        echo 'geopoints: ', $ad->latitude(), ', ', $ad->longitude(), "\n";
        echo 'price: ', $ad->price(), "\n";
        echo 'document: ', $ad->propertyId, "\n";
        echo 'reference: ', $ad->propertyReference, "\n";
        print_r($ad->dataset()->getAsVersion(3, false));
        echo '----------------------------------------------------------------------------------------------------------',"\n";
    }
    
}
