<?php
if (get_cfg_var('mourjan.server_id')=='1')
    require_once '/var/www/dev.mourjan/config/cfg.php';
else
    require_once '/home/www/mourjan/config/cfg.php';

include_once $config['dir'].'/core/model/Db.php';

class JApp {
    var $cfg,$db,$sphinx,$request,$params;
    var $rp=true;
    var $msg='';
    var $data=array();
    
    private static $stmt_get_ad = null;
    private static $stmt_get_ext = null;
    private static $stmt_get_loc = null;

    function JApp($config){
        $this->cfg = $config;        
        $this->db = new DB($config);
        
        if(isset($_REQUEST['q']) && $_REQUEST['q']){
            $this->request=$_REQUEST['q'];
        }
        
        $this->run();
    }
    
    function clearData($key=''){
        if($key){
            if(isset($this->data[$key])) 
                unset($this->data[$key]);
        }else  $this->data=array();
    }

    function setData($res, $key=''){
        if ($key) $this->data[$key]=$res;
        else $this->data[]=$res;
    }

    function mergeData($res, $key){
        if (isset ($this->data[$key])) $this->data[$key]=array_merge($this->data[$key], $res);
        else $this->data[$key]=$res;
    }

    function process(){
        $res=array();
        $res['success']=$this->rp;
        if($this->msg)
            $res['MSG']=$this->msg;
        $res = array_merge($res,$this->data);
        header('content-type: application/json; charset=utf-8');
        //header("access-control-allow-headers: X-Requested-With, Content-Type");
        header("access-control-allow-origin: *");
        echo json_encode($res);
    }

    function fail($msg='illegal access'){
	$this->msg=$msg;
        $this->rp=false;
        $this->process();
        if ($this->db)
            $this->db->close ();
        if ($this->sphinx) {
            $this->sphinx->close();
        }
        exit (0);
    }  
    
    public function __destruct() {
        if ($this->sphinx) {
            $this->sphinx->close();
        }
        if ($this->db)
            $this->db->close (); 
    } 
    
    function getClassified($id) {
        $ad = $this->db->cacheGet($id);
        if ($ad) {
            return $ad;
        }
        
        if (!self::$stmt_get_ad) {
            self::$stmt_get_ad = $this->db->getInstance()->prepare(
                "select 
                    ad.id, ad.hold, ad.title, ad.publication_id, ad.country_id, ad.city_id, 
                    section.category_id, ad.purpose_id, section.root_id, ad.content, ad.rtl, 
                    ad.date_added, ad.section_id, trim(country.id_2), 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.DATE_ADDED), 
                    ad.canonical_id, ad.expiry_date, link.url flink, 
                    '/'||lower(country.ID_2)||'/'||city.uri||'/'||section.uri||'/'||purpose.uri||'/%s%d/' uri,
                    section.name_ar, section.name_en, 
                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', ad.LAST_UPDATE),
                    ad_user.latitude, ad_user.longitude,
                    ad_translated.title alter_title, ad_translated.content alter_content,
                    ad_user.web_user_id,                     
                    wu.user_rank,
                    IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', featured.ended_date)) featured_date_ended, 
                    IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', bo.end_date)) bo_date_ended, 
                    ad.publisher_type,
                    ad_user.content user_content
                from ad
                left join country on country.id=ad.country_id 
                left join city on city.id=ad.city_id
                left join section on section.id=ad.section_id
                left join purpose on purpose.id=ad.purpose_id
                left join link on link.ad_id=ad.id
                left join ad_user on ad_user.id=ad.id
                left join ad_translated on ad_translated.ad_id=ad.id 
                left join t_ad_bo bo on bo.ad_id=ad.id and bo.blocked=0 
                left join web_users wu on wu.id = ad_user.web_user_id 
                left join t_ad_featured featured on featured.ad_id=ad.id and current_timestamp between featured.added_date and featured.ended_date  
                where ad.id = ?"
                );

            self::$stmt_get_ext = $this->db->getInstance()->prepare("
                SELECT r.SECTION_TAG_ID, t.LANG
                FROM AD_TAG r
                left join SECTION_TAG t on t.ID=r.SECTION_TAG_ID
                where r.AD_ID=?
                ");

            self::$stmt_get_loc = $this->db->getInstance()->prepare("
                SELECT r.LOCALITY_ID, g.NAME, g.CITY_ID, g.PARENT_ID, g.LANG
                FROM AD_LOCALITY r
                left join GEO_TAG g on g.ID=r.LOCALITY_ID
                where r.AD_ID=?
                ");
        }
        
        self::$stmt_get_ad->execute([$id]);
        if (($row = self::$stmt_get_ad->fetch(PDO::FETCH_NUM)) !== false) {
            $count = count($row);
            for ($i=0; $i<$count; $i++) {
                if(is_numeric($row[$i])) $row[$i] = $row[$i]+0;
            }

            $user_content = $row[$count-1];
            unset($row[$count-1]);

            $ad=$row;
            
            $ad[Classifieds::PICTURES] = NULL;
            $ad[Classifieds::PICTURES_DIM] = NULL;
            $ad[Classifieds::VIDEO] = NULL; 
            $ad[Classifieds::LOCALITIES_AR] = NULL;
            $ad[Classifieds::LOCALITIES_EN] = NULL;
            $ad[Classifieds::USER_LEVEL] = 0;
            $ad[Classifieds::DONE] = 0;
            $ad[Classifieds::LOCATION] = NULL;
            $ad[Classifieds::USER_RANK] = $row[$count-5];
            $ad[Classifieds::FEATURE_ENDING_DATE] = $row[$count-4];
            $ad[Classifieds::BO_ENDING_DATE] = $row[$count-3];
            $ad[Classifieds::PUBLISHER_TYPE] = $row[$count-2];
                
            // parser
            $decoder = json_decode($user_content, TRUE);
            if (isset($decoder['pics']) && is_array($decoder['pics']) && count($decoder['pics'])) {

                foreach ($decoder['pics'] as $pic => $is_set) {
                    if ($is_set){
                        if(is_array($is_set)){
                            $ad[Classifieds::PICTURES_DIM][]=$is_set;
                        }
                        $ad[Classifieds::PICTURES][] = $pic;
                    }
                }
            }
            if(isset($decoder['cui'])){
                $ad[Classifieds::CONTACT_INFO] = $decoder['cui'];
            }
            
            if(isset($decoder['cut'])){
                $ad[Classifieds::CONTACT_TIME] = $decoder['cut'];
            }

            if (isset($decoder['loc']) && $decoder['loc']) {
                $ad[Classifieds::LOCATION] = $decoder['loc'];
            }
            
            if (isset($decoder['video']) && is_array($decoder['video']) && count($decoder['video'])) {
                $ad[Classifieds::VIDEO] = $decoder['video'];
            }
            
            if (isset($decoder['userLvl']) && $decoder['userLvl']) {
                $ad[Classifieds::USER_LEVEL] = $decoder['userLvl'];
            }

            if ($ad[Classifieds::ROOT_ID]==1) {
                self::$stmt_get_loc->execute(array($id));
                while (($locRow = self::$stmt_get_loc->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $ad[$locRow['LANG']=='ar' ? Classifieds::LOCALITIES_AR : Classifieds::LOCALITIES_EN][$locRow['LOCALITY_ID']+0] =
                            array($locRow['NAME'], $locRow['CITY_ID'], $locRow['PARENT_ID']);
                }
            }
            elseif ($ad[Classifieds::ROOT_ID]==2) {
                self::$stmt_get_ext->execute(array($id));
                while (($extRow = self::$stmt_get_ext->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $ad[$extRow['LANG']=='ar' ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN] = $extRow['SECTION_TAG_ID']+0;
                }
            }

            $this->db->cacheSet($id, $ad, $this->cfg['ttl_long']);
            return $ad;
        }
        return FALSE;
    }
    
    function detectEmail($ad){
        $matches=null;
        preg_match_all('/(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/i', $ad, $matches);
        return $matches;
    }
    
    function processTextNumbers(&$text, $pubId = 0, $countryCode = 0, &$matches = array()) {
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content = null;
        //preg_match('/( للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);

        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        if (!($content && count($content))) {
            /* $tmpTxt=preg_replace('/\<.*?>/', '', $text);
              preg_match('/([0-9\-\\\\\/\+\s]*$)/', $tmpTxt,$content); */
            return $text;
        }

        if ($content && count($content)) {
            $str = $content[1];

            $strpos = strpos($text, $content[0]);
            $text = trim(substr($text, 0, $strpos));
            $text = trim(preg_replace('/[-\/\\\]$/', '', $text));

            if ($str) {
                if ($this->formatNumbers) {
                    $nums = array();
                    $numInst = array();
                    $numbers = null;
                    preg_match_all($phone, $str, $numbers);
                    if ($numbers && count($numbers[1])) {
                        foreach ($numbers[1] as $match) {
                            $number = $match;
                            try {
                                if ($pubId == 1) {
                                    $numInst[] = $num = $this->mobileValidator->parse($number, $this->formatNumbers);
                                } else {
                                    $numInst[] = $num = $this->mobileValidator->parse($number, $countryCode);
                                }
                                if ($num && $this->mobileValidator->isValidNumber($num)) {
                                    $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                    if ($rCode == $this->formatNumbers) {
                                        $num = $this->mobileValidator->formatInOriginalFormat($num, $this->formatNumbers);
                                    } else {
                                        $num = $this->mobileValidator->formatOutOfCountryCallingNumber($num, $this->formatNumbers);
                                    }
                                    $nums[] = array($number, $num);
                                } else {
                                    $hasCCode = preg_match('/^\+/', $number);
                                    switch ($countryCode) {
                                        case 'SA':
                                            if ($hasCCode) {
                                                $num = substr($number, 4);
                                            } else {
                                                $num = $number;
                                            }
                                            if (strlen($num) == 7) {
                                                switch ($pubId) {
                                                    case 9:
                                                        $num = '011' . $num;
                                                        break;
                                                    case 12:
                                                    case 18:
                                                        $tmp = '013' . $num;
                                                        $tmp = $this->mobileValidator->parse($num, $countryCode);
                                                        if ($tmp && $this->mobileValidator->isValidNumber($tmp)) {
                                                            $num = '013' . $num;
                                                        } else {
                                                            $num = '011' . $num;
                                                        }
                                                        break;
                                                }
                                            }
                                            break;
                                        case 'EG':
                                            if ($hasCCode) {
                                                $num = substr($number, 3);
                                            } else {
                                                $num = $number;
                                            }
                                            if (strlen($num) == 7) {
                                                switch ($pubId) {
                                                    case 13:
                                                        $num = '2' . $num;
                                                        break;
                                                    case 14:
                                                        $num = '3' . $num;
                                                        break;
                                                }
                                            } elseif (strlen($num) == 8) {
                                                switch ($pubId) {
                                                    case 13:
                                                        $num = '2' . $num;
                                                        break;
                                                }
                                            }
                                            break;
                                    }
                                    if ($num != $number) {
                                        $num = $this->mobileValidator->parse($num, $countryCode);
                                        if ($num && $this->mobileValidator->isValidNumber($num)) {
                                            $rCode = $this->mobileValidator->getRegionCodeForNumber($num);
                                            if ($rCode == $this->formatNumbers) {
                                                $num = $this->mobileValidator->formatInOriginalFormat($num, $this->formatNumbers);
                                            } else {
                                                $num = $this->mobileValidator->formatOutOfCountryCallingNumber($num, $this->formatNumbers);
                                            }
                                            $nums[] = array($number, $num);
                                        } else {
                                            $nums[] = array($number, $number);
                                        }
                                    } else {
                                        $nums[] = array($number, $number);
                                    }
                                }
                            } catch (Exception $ex) {
                                $nums[] = array($number, $number);
                            }
                        }
                        $mobile = array();
                        $phone = array();
                        $undefined = array();
                        $i = 0;

                        foreach ($nums as $num) {
                            if ($num[0] != $num[1]) {
                                $type = $this->mobileValidator->getNumberType($numInst[$i++]);
                                if ($type == 1 || $type == 2)
                                    $mobile[] = $num;
                                elseif ($type == 0 || $type == 2)
                                    $phone[] = $num;
                                else
                                    $undefined[] = $num;
                            }else {
                                $undefined[] = $num;
                            }
                        }
                        $matches = array(
                            $mobile,
                            $phone,
                            $undefined
                        );
                        /* if(preg_match('/\<span class/',$text)){
                          if(in_array($pubId, [9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,34,36,37,38,39,40,41,46,47,48,52,54,55])){ // Waseet
                          $mobile=array();
                          $phone=array();
                          $undefined = array();
                          $i=0;
                          foreach($nums as $num){
                          if($num[0]!=$num[1]){
                          $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                          if($type==1 || $type==2)
                          $mobile[]=$num;
                          elseif($type==0 || $type==2)
                          $phone[]=$num;
                          else $undefined[]=$num;
                          }else{
                          $undefined[]=$num;
                          }
                          }
                          $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                          $res = '';
                          if(count($mobile) || count($phone)){
                          if(count($mobile)){
                          $res.=($isArabic ? 'موبايل':'Mobile').': ';
                          $i=0;
                          foreach($mobile as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="pn o1">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          if(count($phone)){
                          if($res)$res.='- ';
                          $res.=($isArabic ? 'هاتف':'Phone').': ';
                          $i=0;
                          foreach($phone as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="pn o7">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          }elseif(count($undefined)){
                          $res.=($isArabic ? 'هاتف':'Phone').': ';
                          $i=0;
                          foreach($undefined as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="vn">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          $divider=null;
                          preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                          $pos=0;
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          if(!$pos){
                          $divider=null;
                          preg_match('/(<span)/',$text,$divider);
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          }
                          }
                          }
                          if(!$pos){
                          $srh='';
                          foreach($nums as $num){
                          $srh .= $num[0].'|';
                          }
                          if($srh){
                          $srh.=substr($srh,0,-1);
                          $srh=  preg_replace('/\+/','\\+' , $srh);
                          $divider=null;
                          preg_match('/(<span class="pn">'.$srh.')/',$text,$divider);
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          }
                          }
                          }
                          ////if($pos)
                          ////$text = substr($text,0,$pos);
                          ////if($res)
                          ////$text.=' / '.$res;
                          }else{
                          foreach($nums as $num){
                          if($num[0]!=$num[1]){
                          $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                          ////$text = preg_replace('/'.$num[0].'/', $num[1], $text);
                          $matches[]=$num[1];
                          }else{
                          $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                          ////$text = preg_replace('/\<span class="pn(?:[a-z0-9]*)">'.$num[0].'\<\/span\>/', '<span class="vn">'.$num[1].'</span>', $text);
                          }
                          }
                          }
                          }else{
                          if(in_array($pubId, [9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,34,36,37,38,39,40,41,46,47,48,52,54,55])){ // Waseet
                          $mobile=array();
                          $phone=array();
                          $undefined = array();
                          $i=0;
                          foreach($nums as $num){
                          if($num[0]!=$num[1]){
                          $type=$this->mobileValidator->getNumberType($numInst[$i++]);
                          if($type==1 || $type==2)
                          $mobile[]=$num;
                          elseif($type==0 || $type==2)
                          $phone[]=$num;
                          else $undefined[]=$num;
                          }else{
                          $undefined[]=$num;
                          }
                          }
                          $isArabic = preg_match('/[\x{0621}-\x{064a}]/u', $text);
                          $res = '';
                          if(count($mobile) || count($phone)){
                          if(count($mobile)){
                          $res.=($isArabic ? 'موبايل':'Mobile').': ';
                          $i=0;
                          foreach($mobile as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="pn o1">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          if(count($phone)){
                          if($res)$res.='- ';
                          $res.=($isArabic ? 'هاتف':'Phone').': ';
                          $i=0;
                          foreach($phone as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="pn o7">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          }elseif(count($undefined)){
                          $res.=($isArabic ? 'هاتف':'Phone').': ';
                          $i=0;
                          foreach($undefined as $mob){
                          if($i)$res.=($isArabic ? 'او ':'or ');
                          $res.='<span class="vn">'.$mob[1].'</span> ';
                          $matches[]=$mob[1];
                          $i++;
                          }
                          }
                          $divider=null;
                          preg_match('/( للمفاهمه: | للمفاهمه | ج\/| ت\/| للمفاهمة: | فاكس: | للمفاهمة | جوال | للاتصال | للاتصال: | ه: | - call: | call: | - tel: | tel: | tel | - ت: | ت: | ت )/i',$text,$divider);
                          $pos=0;
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          if(!$pos){
                          $divider=null;
                          preg_match('/(<span)/',$text,$divider);
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          }
                          }
                          }
                          if(!$pos){
                          $srh='';
                          foreach($nums as $num){
                          $srh .= $num[0].'|';
                          }
                          if($srh){
                          $srh.=substr($srh,0,-1);
                          $srh=  preg_replace('/\+/','\\+' , $srh);
                          $divider=null;
                          preg_match('/('.$srh.')/',$text,$divider);
                          if($divider && count($divider)){
                          $pos = strpos($text, $divider[1]);
                          }
                          }
                          }
                          if($pos)
                          ////$text = substr($text,0,$pos);
                          if($res){
                          ////$text.=' / '.$res;
                          }
                          }else{
                          foreach($nums as $num){
                          if($num[0]!=$num[1]){
                          $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                          ////$text = preg_replace('/'.$num[0].'/', '<span class="pn">'.$num[1].'</span>', $text);
                          $matches[]=$num[1];
                          }else{
                          $num[0]=  preg_replace('/\+/','\\+' , $num[0]);
                          ////$text = preg_replace('/'.$num[0].'/', '<span class="vn">'.$num[1].'</span>', $text);
                          }
                          }
                          }
                          } */
                    }
                } else {
                    if ($pubId != 1) {
                        if (!preg_match('/\<span class/', $text)) {
                            preg_match_all($phone, $str, $numbers);
                            if ($numbers && count($numbers[1])) {
                                foreach ($numbers[1] as $match) {
                                    $number = $match;
                                    $number = preg_replace('/\+/', '\\+', $number);
                                    ////$text = preg_replace('/('.$number.')/', '<span class="pn">$1</span>', $text);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $text;
    }
    
    function run(){
        if(!isset($this->request))$this->fail();
        
        switch($this->request){
            case 'get-roots':
                $country_id=(isset($_GET['cn']) && is_numeric($_GET['cn']) ? $_GET['cn'] : 0);
                $city_id=(isset($_GET['c']) && is_numeric($_GET['c']) ? $_GET['c'] : 0);
                if($city_id || $country_id){
                    
                    $roots = $this->db->queryCacheResultSimpleArray(
                        "roots",
                        "select r.ID, r.NAME_AR, r.NAME_EN, r.URI
                        from root r 
                        order by 1",
                        null, 0, $this->cfg['ttl_long']);
                    
                    $roots_count=$this->db->queryCacheResultSimpleArray(                
                        "roots_{$country_id}_{$city_id}",
                        "select r.ID, c.counter, c.unixtime, c.purposes
                        from root r 
                        left join counts c 
                        on c.country_id={$country_id} 
                        and c.city_id={$city_id} 
                        and c.root_id=r.id 
                        and c.section_id=0 
                        and c.purpose_id=0 
                        where c.counter>0 
                        order by 1",
                        null, 0, $this->cfg['ttl_long']);
                        
                    $result = array();
                    
                    foreach ($roots as $root){
                        $res = array(
                            'id'        =>  $root[0],
                            'en'        =>  $root[2],
                            'ar'        =>  $root[1],
                            'uri'       =>  $root[3].'/',
                            'count'     =>  0,
                            'ts'        =>  0
                        );
                        if(isset($roots_count[$root[0]])){
                            $res['count']   =   $roots_count[$root[0]][1];
                            $res['ts']   =   $roots_count[$root[0]][2];
                        }
                        $result[] = $res;
                    }
                    $this->setData($result,'roots');
                    $this->process();
                }else $this->fail ();
                break;
                
                
            case 'get-purposes':
                $country_id=(isset($_GET['cn']) && is_numeric($_GET['cn']) ? $_GET['cn'] : 0);
                $city_id=(isset($_GET['c']) && is_numeric($_GET['c']) ? $_GET['c'] : 0);
                $root_id=(isset($_GET['ro']) && is_numeric($_GET['ro']) ? $_GET['ro'] : 0);
                $section_id=(isset($_GET['se']) && is_numeric($_GET['se']) ? $_GET['se'] : 0);
                if($city_id || $country_id || $section_id || $root_id){
                    
                    $roots = $this->db->queryCacheResultSimpleArray(
                        'purposes',
                        'select ID, NAME_AR, NAME_EN, URI
                        from purpose
                        where blocked=0',
                        null, 0, $this->cfg['ttl_long']);
                    
                    $roots_count=$this->db->queryCacheResultSimpleArray(
                        "purposes_{$country_id}_{$city_id}_{$root_id}_{$section_id}",
                        "select p.ID, d.counter, d.unixtime,
                        (d.AVG_IMPRESSIONS*1.0)/(365.0/12.0) DAILY_IMPRESSIONS, d.MOURJAN_ADS
                        from purpose p 
                        left join counts d 
                            on d.country_id={$country_id} 
                            and d.city_id={$city_id} 
                            and d.root_id={$root_id} 
                            and d.section_id={$section_id} 
                            and d.purpose_id=p.id 
                            where d.counter > 0 
                        order by d.counter desc",
                        null, 0, $this->cfg['ttl_long']);
                        
                    $result = array();
                    
                    foreach ($roots as $root){
                        if($root[0]==3){
                            $root[1]='وظائف شاغرة';
                        }
                        if($root[0]==4){
                            $root[1]='يبحث عن عمل';
                        }
                        if($root[0]==6 || $root[0]==7){
                            $root[2]='Looking '.$root[2];
                        }
                        $res = array(
                            'id'        =>  $root[0],
                            'en'        =>  $root[2],
                            'ar'        =>  $root[1],
                            'uri'       =>  $root[3].'/',
                            'count'     =>  0,
                            'ts'        =>  0
                        );
                        if(isset($roots_count[$root[0]])){
                            $res['count']   =   $roots_count[$root[0]][1];
                            $res['ts']   =   $roots_count[$root[0]][2];
                        }
                        $result[] = $res;
                    }
                    
                    $this->setData($result,'purposes');
                    $this->process();
                    
                }else $this->fail ();
                break;
                
            case 'get-sections':
                $country_id=(isset($_GET['cn']) && is_numeric($_GET['cn']) ? $_GET['cn'] : 0);
                $city_id=(isset($_GET['c']) && is_numeric($_GET['c']) ? $_GET['c'] : 0);
                if($city_id || $country_id){
                    
                    /*$root_sections =$this->db->queryCacheResultSimpleArray(
                        "sections_app",
                        "select s.ID, s.NAME_AR, s.NAME_EN, s.URI, s.ROOT_ID
                        from section s where s.blocked = 0 order by s.root_id",
                        null, 0, $this->cfg['ttl_long']);*/
                    
                    $sections_count=$this->db->queryCacheResultSimpleArray(
                        "sections_app_{$country_id}_{$city_id}",
                        "select s.ID,s.NAME_AR, s.NAME_EN, s.URI, s.ROOT_ID, t.counter, t.unixtime, t.purposes 
                        from section s 
                        left join root r on r.id=s.root_id 
                        left join counts t 
                            on t.country_id={$country_id}
                            and t.city_id={$city_id} 
                            and t.root_id=s.root_id  
                            and s.id=t.section_id 
                            and t.purpose_id=0 
                        where t.counter>0",
                        null, 0, $this->cfg['ttl_long'], 1);
                        
                    $result = array();
                    
                    foreach ($sections_count as $root){
                        $result[] = array(
                            'id'        =>  $root[0],
                            'en'        =>  $root[2],
                            'ar'        =>  $root[1],
                            'ro'        =>  $root[4],
                            'uri'       =>  $root[3].'/',
                            'count'     =>  $root[5],
                            'ts'        =>  $root[6],
                            'pu'        =>  $root[7]
                        );
                    }
                    $this->setData($result,'sections');
                    $this->process(); 
                    
                }else $this->fail ();
                break;
            
            case 'results':
                $country_id=(isset($_GET['cn']) && is_numeric($_GET['cn']) ? $_GET['cn'] : 0);
                $city_id=(isset($_GET['c']) && is_numeric($_GET['c']) ? $_GET['c'] : 0);
                $root_id=(isset($_GET['ro']) && is_numeric($_GET['ro']) ? $_GET['ro'] : 0);
                $section_id=(isset($_GET['se']) && is_numeric($_GET['se']) ? $_GET['se'] : 0);
                $purpose_id=(isset($_GET['pu']) && is_numeric($_GET['pu']) ? $_GET['pu'] : 0);
                $offset = (isset($_GET['start']) && is_numeric($_GET['start']) ? $_GET['start'] : 0);
                $num = (isset($_GET['limit']) && is_numeric($_GET['limit']) ? $_GET['limit'] : 25);
                $keywords = (isset($_GET['kw']) ? filter_input(INPUT_GET, 'kw', FILTER_SANITIZE_STRING) : '');
                
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneNumberUtil.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/CountryCodeToRegionCodeMap.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneNumber.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneNumberFormat.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneMetadata.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneNumberDesc.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/NumberFormat.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/CountryCodeSource.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/Matcher.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/PhoneNumberType.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/NumberParseException.php';
                require_once $this->cfg['dir'].'/core/lib/libphonenumber/ValidationResult.php';

                $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                $this->formatNumbers="LB";
                
                $this->sphinx = $sphinx = new SphinxClient();
                $sphinx->resetFilters();
                $sphinx->resetGroupBy();
                $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
                $sphinx->SetConnectTimeout(1000);
                $sphinx->SetServer($this->cfg['search_host'], $this->cfg['search_port']);
                $sphinx->SetFilter('hold', array(0));
                $sphinx->SetFilter('canonical_id', array(0));
                if ($country_id) {
                    $sphinx->setFilter('country', [$country_id]);
                }
                if ($city_id) {
                    $sphinx->setFilter('city', [$city_id]);
                }
                if ($section_id) {
                    $sphinx->setFilter('section_id', [$section_id]);
                }
                if ($root_id) {
                    $sphinx->setFilter('root_id', [$root_id]);
                }
                if ($purpose_id) {
                    $sphinx->setFilter('purpose_id', [$purpose_id]);
                }
                
                $sphinx->SetSortMode(SPH_SORT_EXTENDED, 'date_added desc');
                $sphinx->SetLimits($offset, $num, $this->cfg['search_results_max']);

                $query = $sphinx->Query($keywords, $this->cfg['search_index']);


                if ($sphinx->getLastError()) {
                    $this->fail($sphinx->getLastError());
                } else {
                    include_once $this->cfg['dir'].'/core/model/Classifieds.php';
                    
                    $this->setData($query['total_found'], 'total');
                    
                    $adResult = array();
                    
                    if (isset($query['matches'])) {
                        
                        $keys = array_keys($query['matches']);
                        $count = count($keys);
                        for ($i=0; $i<$count; $i++) {
                            $ad = $this->getClassified($keys[$i]+0);
                            if ($ad) {

                                unset($ad[Classifieds::TITLE]);
                                unset($ad[Classifieds::ALT_TITLE]);
                                unset($ad[Classifieds::CANONICAL_ID]);
                                unset($ad[Classifieds::CATEGORY_ID]);
                                unset($ad[Classifieds::SECTION_NAME_AR]);
                                unset($ad[Classifieds::SECTION_NAME_EN]);
                                unset($ad[Classifieds::HELD]);


                                $tmpContent = $ad[Classifieds::CONTENT];

                                $telNumbers = [];
                                $this->processTextNumbers($ad[Classifieds::CONTENT], $ad[Classifieds::PUBLICATION_ID], $ad[Classifieds::COUNTRY_CODE], $telNumbers);

                                $ad[Classifieds::CONTENT] = strip_tags($ad[Classifieds::CONTENT]);

                                $emails = $this->detectEmail($tmpContent);
                                if($emails && count($emails)){
                                    $emails = $emails[0];
                                    if($emails && count($emails)){
                                        $j=0;
                                        $email_regex='';
                                        foreach ($emails as $email){
                                            if($j++)$email_regex.='|';
                                            $email_regex .= addslashes($email);
                                        }
                                        //check if email still exists after stripping phone numbers
                                        $strpos = strpos($ad[Classifieds::CONTENT], $email);
                                        if($strpos){
                                            $ad[Classifieds::CONTENT] = trim(substr($ad[Classifieds::CONTENT],0, $strpos));
                                            $ad[Classifieds::CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Classifieds::CONTENT]));
                                        }

                                    }
                                }
                                $ad[Classifieds::EMAILS] = $emails;


                                $ad[Classifieds::TELEPHONES] = $telNumbers;
                                
                                $picCount=0;
                                $pic='';
                                $picWidth=0;
                                $picHeight=0;
                                $isVideo=0;
                                if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                                    if (isset($ad[Classifieds::PICTURES]) && is_array($ad[Classifieds::PICTURES]) && count($ad[Classifieds::PICTURES])) {
                                        $picCount=count($ad[Classifieds::PICTURES]);
                                    }
                                    $pic = '<img width="120" height="68" src="' . $ad[Classifieds::VIDEO][2] . '" />';
                                    $isVideo = 1;
                                } elseif ($ad[Classifieds::PICTURES] && is_array($ad[Classifieds::PICTURES])  && count($ad[Classifieds::PICTURES])) {
                                    $picCount=count($ad[Classifieds::PICTURES]);
                                    
                                    $picSizes=$ad[Classifieds::PICTURES_DIM];
                                    if(isset($picSizes[0])){
                                        $picSizes = $picSizes[0];
                                        if($picSizes[0] > 120){
                                            $picWidth = 120;
                                            $picHeight = floor((120 * $picSizes[1])/$picSizes[0]);
                                        }else{
                                            $picWidth = $picSizes[0];
                                            $picHeight = $picSizes[1];
                                        }
                                        
                                        $pic= '<img height="'.$picHeight.'" width="'.$picWidth.'" src="' . $this->cfg['url_ad_img'] . '/repos/s/' . $ad[Classifieds::PICTURES][0] . '" />';
                                    }else{
                                        $pic ='';
                                        $picCount = 0;
                                    }
                                    
                                    
                                    
                                } else {
                                    //$pic= '<img src="' . $this->cfg['url_img'] . '/30/' . $ad[Classifieds::SECTION_ID] . '.png" />';
                                }


                                $adResult[] = [
                                    'id'    =>  $ad[Classifieds::ID], 
                                    'text'  =>  $ad[Classifieds::CONTENT],
                                    'rtl'   =>  $ad[Classifieds::RTL],
                                    'ts'    =>  $ad[Classifieds::UNIXTIME],
                                    'p'     =>  $pic,
                                    'v'     =>  $isVideo,
                                    'x'     =>  $picCount
                                    /*,
                                    'cc'    =>  $ad[Classifieds::COUNTRY_CODE],
                                    'lat'   =>  $ad[Classifieds::UNIXTIME],
                                    $ad[Classifieds::EXPIRY_DATE],
                                    $ad[Classifieds::URI_FORMAT],
                                    $ad[Classifieds::LAST_UPDATE],
                                    $ad[Classifieds::LATITUDE],
                                    $ad[Classifieds::LONGITUDE],
                                    $ad[Classifieds::ALT_CONTENT],
                                    $ad[Classifieds::USER_ID],
                                    $ad[Classifieds::PICTURES],
                                    $ad[Classifieds::VIDEO],
                                    isset($ad[Classifieds::EXTENTED_AR]) ? $ad[Classifieds::EXTENTED_AR] : "",
                                    isset($ad[Classifieds::EXTENTED_EN]) ? $ad[Classifieds::EXTENTED_EN] : "",
                                    isset($ad[Classifieds::LOCALITY_ID]) ? $ad[Classifieds::LOCALITY_ID] : 0,
                                    $ad[Classifieds::LOCALITIES_AR],
                                    $ad[Classifieds::LOCALITIES_EN],
                                    $ad[Classifieds::USER_LEVEL],
                                    $ad[Classifieds::LOCATION],
                                    $ad[Classifieds::PICTURES_DIM],
                                    $ad[Classifieds::TELEPHONES], 
                                    $ad[Classifieds::EMAILS]];*/
                                ];
                            }
                        }
                        
                    }
                    $this->setData($adResult,'search');
                    $this->process();
                }
                
                break;
            
            
            
            case 'getULoc':
                $country_id=0;
                $country_code='';
                $city_id=0;
                $ip=0;
                if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                if($ip){
                    $geo = @geoip_record_by_name($ip);
                    if (!empty($geo)){
                        $country_code = strtolower(trim($geo['country_code']));
                        
                        $pos=array();
                        
                        if (array_key_exists('latitude', $geo))
                            $pos[0] = $geo['latitude'];
                        else
                            $pos[0] = 0.0;

                        if (array_key_exists('longitude', $geo))
                            $pos[1] = $geo['longitude'];
                        else
                            $pos[1] = 0.0;

                        if (array_key_exists($country_code, $this->cfg['iso_countries'])) {
                            $country_id = $this->cfg['iso_countries'][$country_code];

                            $countryCities=$this->db->queryCacheResultSimpleArray("cities_{$country_id}_en",
                                "select c.ID 
                                from city c
                                where c.country_id={$country_id} 
                                and c.blocked=0
                                order by NAME_EN",
                                null, 0, $this->cfg['ttl_long']);
                                
                            $cities=$this->db->queryCacheResultSimpleArray(
                            'cities',
                            'select c.ID, c.NAME_AR, c.NAME_EN, c.URI, s.counter, s.UNIXTIME, c.COUNTRY_ID, c.LATITUDE, c.LONGITUDE
                            from city c 
                            left join counts s 
                                    on s.country_id=c.country_id 
                                    and s.city_id=c.id
                                    and s.root_id=0 
                                    and s.section_id=0 
                                    and s.purpose_id=0 
                            where s.counter>=0 
                            and c.blocked=0',
                            null, 0, $this->cfg['ttl_long']);

                            if (count($countryCities) > 1) {
                                $default_city = -1;
                                $min = PHP_INT_MAX;
                                foreach ($countryCities as $key=>$ct) {   
                                    $dist = $this->distance($cities[$key][7], $cities[$key][8], $pos[0], $pos[1]);
                                    if ($dist<$min){
                                        $default_city=$key;
                                        $min=$dist;
                                    }
                                }
                                if ($default_city>0) {
                                    $city_id=$default_city;
                                }
                            }
                        }
                    }
                }
                $this->setData($country_code,'cc');
                $this->setData($country_id,'cn');
                $this->setData($city_id,'c');
                $this->process();
                break;
            default:
                $this->fail();
                break;
        }
    }
}

new JApp($config);

?>
