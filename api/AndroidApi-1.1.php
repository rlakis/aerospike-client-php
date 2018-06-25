<?php

use MaxMind\Db\Reader;
use Core\Model\NoSQL;
use Core\Lib\SphinxQL;
use Core\Model\MobileValidation;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;


class AndroidApi 
{
 
    private $api;
    var $mobileValidator=null;
    
    
    function cutOfContacts(&$text) {
        $phone = '/((?:\+|)(?:[0-9]){7,14})/';
        $content=null;
        preg_match('/(?: mobile(?::| \+) | viber(?::| \+) | whatsapp(?::| \+) | phone(?::| \+) | fax(?::| \+) | telefax(?::| \+) | جوال(?::| \+) | موبايل(?::| \+) | واتساب(?::| \+) | فايبر(?::| \+) | هاتف(?::| \+) | فاكس(?::| \+) | تلفاكس(?::| \+) | tel(?:\s|): | call(?:\s|): | ت(?:\s|): | الاتصال | للمفاهمه: | للمفاهمه | ج\/| للمفاهمة: | للاتصال | للاتصال: | ه: )(.*)/ui', $text, $content);
        
        if(!($content && count($content))) {
            preg_match($phone, $text, $content);
            if(!($content && count($content))) {
                return $text;
            }
        }

        if($content && count($content)) {
            $strpos = strpos($text, $content[0]);
            $text = trim(substr($text,0, $strpos));
            $text = trim(preg_replace('/[-\/\\\]$/', '', $text));        
        }
    }
    
    function addAdToResultArray($ad, $isFeatured=0, $isPremium=false) {
        unset($ad[Core\Model\Classifieds::TITLE]);
        unset($ad[Core\Model\Classifieds::ALT_TITLE]);

        unset($ad[Core\Model\Classifieds::CANONICAL_ID]);
        unset($ad[Core\Model\Classifieds::CATEGORY_ID]);
        unset($ad[Core\Model\Classifieds::SECTION_NAME_AR]);
        unset($ad[Core\Model\Classifieds::SECTION_NAME_EN]);
        unset($ad[Core\Model\Classifieds::HELD]);

        $emails = $ad[Core\Model\Classifieds::EMAILS];
       
        $this->cutOfContacts($ad[Core\Model\Classifieds::CONTENT]);        

        $ad[Core\Model\Classifieds::CONTENT] = strip_tags($ad[Core\Model\Classifieds::CONTENT]);

        if($ad[Core\Model\Classifieds::ALT_CONTENT]!="") {
            $this->cutOfContacts($ad[Core\Model\Classifieds::ALT_CONTENT]);
            $ad[Core\Model\Classifieds::ALT_CONTENT] = strip_tags($ad[Core\Model\Classifieds::ALT_CONTENT]);
        }

        if (!empty($emails)) {
            $j=0;
            $email_regex='';
            foreach ($emails as $email) {
                if($j++)$email_regex.='|';
                $email_regex .= addslashes($email);
            }

            //check if email still exists after stripping phone numbers
            $strpos = strpos($ad[Core\Model\Classifieds::CONTENT], $email);
            if($strpos) {
                $ad[Core\Model\Classifieds::CONTENT] = trim(substr($ad[Core\Model\Classifieds::CONTENT],0, $strpos));
                $ad[Core\Model\Classifieds::CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Core\Model\Classifieds::CONTENT]));
            }

            if($ad[Core\Model\Classifieds::ALT_CONTENT]!="") {
                $strpos = strpos($ad[Core\Model\Classifieds::ALT_CONTENT], $email);
                if($strpos) {
                    $ad[Core\Model\Classifieds::ALT_CONTENT] = trim(substr($ad[Core\Model\Classifieds::ALT_CONTENT],0, $strpos));
                    $ad[Core\Model\Classifieds::ALT_CONTENT] = trim(preg_replace('/[-\/\\\]$/', '', $ad[Core\Model\Classifieds::ALT_CONTENT]));
                }
            }

        }

        $this->result['d'][] = [
            $ad[Core\Model\Classifieds::ID],//0
            $ad[Core\Model\Classifieds::PUBLICATION_ID],//1
            $ad[Core\Model\Classifieds::COUNTRY_ID],//2
            $ad[Core\Model\Classifieds::CITY_ID],//3
            $ad[Core\Model\Classifieds::PURPOSE_ID],//4
            $ad[Core\Model\Classifieds::ROOT_ID],//5
            $ad[Core\Model\Classifieds::CONTENT],//6
            $ad[Core\Model\Classifieds::RTL],//7
            $ad[Core\Model\Classifieds::DATE_ADDED],//8
            $ad[Core\Model\Classifieds::SECTION_ID],//9
            $ad[Core\Model\Classifieds::COUNTRY_CODE],//10
            $ad[Core\Model\Classifieds::UNIXTIME],//11
            $ad[Core\Model\Classifieds::EXPIRY_DATE],//12
            $ad[Core\Model\Classifieds::URI_FORMAT],//13
            $ad[Core\Model\Classifieds::LAST_UPDATE],//14
            $ad[Core\Model\Classifieds::LATITUDE],//15
            $ad[Core\Model\Classifieds::LONGITUDE],//16
            $ad[Core\Model\Classifieds::ALT_CONTENT],//17
            $ad[Core\Model\Classifieds::USER_ID],//18
            isset($ad[Core\Model\Classifieds::PICTURES]) && count($ad[Core\Model\Classifieds::PICTURES]) ? $ad[Core\Model\Classifieds::PICTURES] : "",//19
            isset($ad[Core\Model\Classifieds::VIDEO]) ? $ad[Core\Model\Classifieds::VIDEO] : "",//20
            isset($ad[Core\Model\Classifieds::EXTENTED_AR]) ? $ad[Core\Model\Classifieds::EXTENTED_AR] : "",//21
            isset($ad[Core\Model\Classifieds::EXTENTED_EN]) ? $ad[Core\Model\Classifieds::EXTENTED_EN] : "",//22
            isset($ad[Core\Model\Classifieds::LOCALITY_ID]) ? $ad[Core\Model\Classifieds::LOCALITY_ID] : 0,//23
            isset($ad[Core\Model\Classifieds::LOCALITIES_AR]) ? $ad[Core\Model\Classifieds::LOCALITIES_AR] : "",//24
            isset($ad[Core\Model\Classifieds::LOCALITIES_EN]) ? $ad[Core\Model\Classifieds::LOCALITIES_EN] : "",//25
            isset($ad[Core\Model\Classifieds::USER_LEVEL]) ? $ad[Core\Model\Classifieds::USER_LEVEL] : 0,//26
            isset($ad[Core\Model\Classifieds::LOCATION]) ? $ad[Core\Model\Classifieds::LOCATION] : "",//27
            isset($ad[Core\Model\Classifieds::PICTURES_DIM]) && count($ad[Core\Model\Classifieds::PICTURES_DIM]) ? $ad[Core\Model\Classifieds::PICTURES_DIM] : "",//28
            $ad[Core\Model\Classifieds::TELEPHONES], //29
            $ad[Core\Model\Classifieds::EMAILS],//30
            //featured flag
            $isFeatured+0,//31
            isset($ad[Core\Model\Classifieds::CONTACT_INFO]) ? $ad[Core\Model\Classifieds::CONTACT_INFO] : "",//32 (revise for production)
            isset($ad[Core\Model\Classifieds::CONTACT_TIME]) ? $ad[Core\Model\Classifieds::CONTACT_TIME] : "",//33 (revise for production)
            isset($ad[Core\Model\Classifieds::PUBLISHER_TYPE]) ? $ad[Core\Model\Classifieds::PUBLISHER_TYPE] : 0,//34
            $isPremium ? 1:0,//35
            isset($ad[Core\Model\Classifieds::PRICE]) ? $ad[Core\Model\Classifieds::PRICE] : 0//36
        ];
    }


    function __construct(MobileApi $_api) 
    {
        global $appVersion;
        $this->api = $_api;
        if ($this->api->getUID()<=0 && $this->api->getUUID())
        {
            $this->api->user = new MCUser($this->api->getUUID());
            $this->api->uid = $this->api->getUser()->getID();
        }
        
        if($this->api->config['active_maintenance']){
            $this->api->result['e']="503";
        }else{
        
        define ('MOURJAN_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB');
        
        switch ($this->api->command) {
            case API_ANDROID_VERIFY_NUMBER:
                $number = filter_input(INPUT_POST, 'tel');
                $iso = trim(filter_input(INPUT_POST, 'iso'));
                if($number && is_numeric($number) && strlen($iso)==2){
                    $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                    $num = $this->mobileValidator->parse($number, $iso);
                    if($num && $this->mobileValidator->isValidNumber($num)){
                        $this->api->result['d'] = [
                            'check'=>true,
                            'n'=>$this->mobileValidator->format($num, libphonenumber\PhoneNumberFormat::E164),
                            'r'=>$number,
                            't'=>$this->mobileValidator->getNumberType($num),
                            'c'=>$num->getCountryCode(),
                            'x'=>$this->mobileValidator->getRegionCodeForNumber($num)
                        ];   
                    }else{
                        $this->api->result['d'] = ['check'=>false];
                    }                    
                }
                break;
            case API_ANDROID_SYNC_WATCHLIST:  
                $this->api->result['d'] = [];
                $this->api->userStatus($status);
                if ($status == 1) {                    
                    //sync watchlist
                    $results = $this->api->db->queryResultArray(
                            "select s.COUNTRY_ID, s.CITY_ID, IIF(c.root_id is null, 0, c.root_id) root_id, s.SECTION_ID,s.PURPOSE_ID, s.SECTION_TAG_ID, s.LOCALITY_ID, s.QUERY_TERM, s.TITLE, s.publisher_type from subscription s
                            left join section c on c.id = s.section_id 
                            where s.web_user_id = ?", [$this->api->getUID()],true,PDO::FETCH_NUM
                    );
                    if($results && count($results) > 0){
                        $this->api->result['d']['watch']=[];
                        foreach($results as $result){
                            $this->api->result['d']['watch'][] = [
                                $result[0],
                                $result[1],
                                $result[2],
                                $result[3],
                                $result[4],
                                $result[5],
                                $result[6],
                                $result[7],
                                $result[8],
                                $result[9]
                                ];
                        }
                    }else{
                        $this->api->result['d']['watch']=[];
                    }
                }
                break;
            case API_ANDROID_SYNC_ACCOUNT:  
                $this->api->result['d'] = [];
                $opts = $this->api->userStatus($status);                
                if ($status == 1) {
                    //sync favorites
                    $this->api->search(true);
                    $results = $this->api->db->queryResultArray(
                        'select f.ad_id from web_users_favs f
                            left join ad a on a.id = f.ad_id
                            where f.web_user_id = ? and f.deleted = 0 and a.hold = 0', 
                            [$this->api->getUID()], true);
                    if($results && count($results)){
                        include_once $this->api->config['dir'].'/core/model/Classifieds.php';
                        $model = new Core\Model\Classifieds($this->api->db);
                        foreach($results as $result){
                            $ad = $model->getById($result['AD_ID']+0);
                            $this->addAdToResultArray($ad, 0);
                        }
                    }
                    if(count($this->api->result['d'])){
                        $results = $this->api->result['d'];
                        $this->api->result['d'] = [];
                        $this->api->result['d']['favs'] = $results;
                        $results = null;
                        unset($this->api->result['total']);
                    }else{
                        $this->api->result['d'] = [];
                        $this->api->result['d']['favs']=[];
                        unset($this->api->result['total']);
                    }
                    //sync my ads
                    $results = $this->api->db->queryResultArray(
                        'select a.id, a.content, a.state, a.section_id,a.doc_id, '
                            . 'a.purpose_id,a.media, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', a.last_update) last_update, '
                            . 'IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) feature_end, '
                            . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) booking_end, '
                            . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.start_date)) booking_start '
                            . 'from ad_user a '
                            . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked = 0 '
                            . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '
                            . 'where web_user_id = ? and state != 6 and state != 8', [$this->api->getUID()], true
                    );
                    
                    
                    if($results && count($results) > 0){
                        $this->api->result['d']['ads']= [];
                        foreach($results as $result){
                            $content = json_decode($result['CONTENT'],true);
                            $content['id']=$result['ID'];
                            $content['user']=$this->api->getUID();
                            $content['se']=$result['SECTION_ID'];
                            $content['pu']=$result['PURPOSE_ID'];
                            $content['state']=$result['STATE'];
                            $content['media']=$result['MEDIA'];
                            $content['sys_update']=$result['LAST_UPDATE'];
                            /*if($content['state'] != 0){
                                $content['sys_update'] = time();
                            }*/
                            $content['booked']=$result['BOOKING_END'];
                            $content['book']=$result['BOOKING_START'];
                            $content['featured']=$result['FEATURE_END'];
                            if(isset($result['DOC_ID']) && $result['DOC_ID']){
                                $content['SYS_CRAWL']=1;
                            }elseif(isset($content['SYS_CRAWL'])){
                                unset($content['SYS_CRAWL']);
                            }
                            if(isset($content['agent'])){
                                unset($content['agent']);
                            }
                            
                            if(isset($content['userLOC'])){
                                unset($content['userLOC']);
                            }
                            if(isset($content['ip'])){
                                unset($content['ip']);
                            }
                            if(isset($content['text'])){
                                unset($content['text']);
                            }
                            if (isset($content['attrs'])) {
                                unset($content['attrs']);
                            }
                            
                            $this->api->result['d']['ads'][$result['ID']] = json_encode($content);
                        }
                    }else{
                        $this->api->result['d']['ads']= "{}";
                    }
                    
                    //sync watchlist
                    $results = $this->api->db->queryResultArray(
                            "select s.COUNTRY_ID, s.CITY_ID, IIF(c.root_id is null, 0, c.root_id) root_id, s.SECTION_ID,s.PURPOSE_ID, s.SECTION_TAG_ID, s.LOCALITY_ID, s.QUERY_TERM, s.TITLE, s.publisher_type from subscription s
                            left join section c on c.id = s.section_id 
                            where s.web_user_id = ?", [$this->api->getUID()],true,PDO::FETCH_NUM
                    );
                    if($results && count($results) > 0){
                        $this->api->result['d']['watch']=[];
                        foreach($results as $result){
                            $this->api->result['d']['watch'][] = [
                                $result[0],
                                $result[1],
                                $result[2],
                                $result[3],
                                $result[4],
                                $result[5],
                                $result[6],
                                $result[7],
                                $result[8],
                                $result[9]
                                ];
                        }
                    }else{
                        $this->api->result['d']['watch']=[];
                    }
                    
                    //sync notes
                    $results = $this->api->db->queryResultArray(
                            "select ad_id, content,DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', last_update) last_update 
                                from web_users_notes 
                            where web_user_id = ? AND deleted = 0", [$this->api->getUID()],true,PDO::FETCH_NUM
                    );
                    if($results && count($results) > 0){
                        $this->api->result['d']['notes']=[];
                        foreach($results as $result){
                            $this->api->result['d']['notes'][] = [
                                $result[0],
                                $result[1],
                                $result[2]
                                ];
                        }
                    }else{
                        $this->api->result['d']['notes']=[];
                    }
                    
                    //get total
                    $rs = $this->api->db->queryResultArray(
                        "SELECT sum(r.credit - r.debit)
                        FROM T_TRAN r
                        where r.UID=?", [$this->api->getUID()], true);
                    $this->api->result['d']['balance'] = -1;
                    if($rs && count($rs) && $rs[0]['SUM']!=null){
                        if($rs[0]['SUM']){                    
                            $this->api->result['d']['balance']=$rs[0]['SUM']+0;
                        }else{
                            $this->api->result['d']['balance']=0;                    
                        }
                    }
                    
                    //get user mobile
                    $_mobile = $this->api->user->getMobile();
                    if ($_mobile && $_mobile->isVerified()){
                        $this->api->result['d']['mobile']=$_mobile->getNumber();
                    }
                    
                    //suspended                    
                    if ($this->api->user->isSuspended())
                    {
                        $this->api->result['d']['suspend'] = time()+$this->api->user->getSuspensionTime();
                    } 
                    
                    //user level
                    $this->api->result['d']['level'] = $opts->user_level+0;
                }
                break;
            case API_ANDROID_FLAG_AD:
                $opts = $this->api->userStatus($status);
                if ($status == 1) {
                    $id = filter_input(INPUT_GET, 'adid', FILTER_VALIDATE_INT) + 0;
                    $ad = $this->api->getClassified($id);
                    if($ad && !in_array($ad[12], array(190,1179,540))){
                        $helpTopic = 4;
                        $flagId = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT) + 0;
                        switch ($flagId) {
                            case 0:
                                $subject = 'Expired/Sold/Rented';
                                break;
                            case 1:
                                $subject = 'Wrong Phone Number/Email';
                                break;
                            case 2:
                                $subject = 'Miscategorized';
                                break;
                            case 3:
                                $subject = 'Immoral';
                                break;
                            case 4:
                                $subject = 'Spam/Overpost';
                                break;
                            default:
                                $subject = 'Abusive Ad Report';
                                break;
                        }
                        $pushId = '';
                        $msg = "<table>
                            <tr><td><b>UID</b>:</td><td><a href='https://www.mourjan.com/myads/?u={$this->api->getUID()}' target='_blank'>{$this->api->getUID()}</td></tr>
                            <tr><td><b>Ad</b>:</td><td><a target='_blank' href='https://www.mourjan.com/{$id}'>{$id}</a></td></tr>
                            <tr><td><b>Mobile</b>:</td><td>Android</td></tr>
                            </table>";
                        
                        $defUserName = 'a'.$this->api->getUID();
                        $fromName = isset($opts->full_name) && $opts->full_name ? $opts->full_name : $defUserName;
                        //$fromEmail = isset($opts->email) && $opts->email ? $opts->email : ($pushId ? $pushId.'@a.mourjan.com' : $defUserName.'@mourjan.com');
                        $fromEmail = isset($opts->email) && $opts->email ? $opts->email : $defUserName.'@mourjan.com';

                        $res = $this->sendMail("Mourjan Admin", $this->api->config['admin_email'], $fromName, $fromEmail, $subject, $msg, $this->api->config['smtp_contact'], $id, $helpTopic);
                    }
                }
                break;
            case API_ANDROID_PARSE_URI:
                $uri = strtolower(filter_input(INPUT_GET, 'uri'));
                $params = [
                    'q'=>'',
                    'geo_id'    =>  0,
                    'tag_id'    =>  0
                ];
                if ($uri && preg_match('/^(?:http|https):\/\/(?:www\.|)mourjan\.com/', $uri)) {
                    $uri = preg_replace('/^(?:http|https):\/\/(?:www\.|)mourjan\.com/', '', $uri);
                    $uri = preg_replace('/\/en(?:\/|$)/', '/', $uri);
                    $uri = preg_replace('/index\.php/', '', $uri);
                    if (substr($uri, -1) == '/')
                        $uri = substr($uri, 0, -1);
                    if ($uri != '') {
                        
                        preg_match('/\?(.*)/', $uri, $uriParams);
                        
                        if($uriParams && count($uriParams)){
                        $_args = explode('&', $uriParams[1]);
                        $count = count($_args);
                        for ($i = 0; $i < $count; ++$i) {
                            $node = explode('=', $_args[$i]);
                            if (!empty($node[1]) && $node[0]=='q') {
                                $params['q'] = trim(urldecode($node[1]));
                            }
                        }
                        }
                        $uri = preg_replace('/\?.*/','', $uri);
                        if (substr($uri, -1) == '/')
                            $uri = substr($uri, 0, -1);

                        $_args = explode('/', $uri);
                        if (!empty($_args)) {
                            $idx = count($_args) - 1;

                            if (is_numeric($_args[$idx])) {
                                $id = (int) $_args[$idx];
                                $rpos = strrpos($uri, '/');
                                if ($rpos)
                                    $uri = substr($uri, 0, $rpos);

                                if ($id < 1000000000) {
                                    if ($id > 1000) {
                                        $module = 'detail';
                                        $idx = -1;
                                    } else {
                                        unset($_args[$idx]);
                                        $idx--;
                                    }
                                }
                            }
                            if ($idx >= 0 && isset($_args[1]) && is_numeric($_args[1])) {
                                $id = (int) $_args[1];
                                if ($id > 2000000000) {
                                    unset($_args[0]);
                                    $uri = substr($uri, (strlen($id) + 1));
                                } elseif ($id > 1000000000) {
                                    unset($_args[0]);
                                    $uri = substr($uri, (strlen($id) + 1));
                                }
                            }

                            if ($idx > 1 && substr($_args[$idx], 0, 2) == "q-") {
                                $tag_info = explode("-", $_args[$idx]);

                                if (count($tag_info) == 3 && is_numeric($tag_info[1]) && is_numeric($tag_info[2])) {
                                    $params['tag_id'] = $tag_info[1]+0;
                                    $_args[$tag_info[2]] = substr($_args[$tag_info[2]], 0, strrpos($_args[$tag_info[2]], "-"));
                                    unset($_args[$idx]);
                                    $tmp = array();
                                    foreach ($_args as $arg) {
                                        $tmp[] = $arg;
                                    }
                                    $_args = $tmp;
                                    $uri = implode("/", $_args);
                                }
                            } elseif ($idx > 1 && substr($_args[$idx], 0, 2) == "c-") {
                                $tag_info = explode("-", $_args[$idx]);

                                if (count($tag_info) == 3 && is_numeric($tag_info[1]) && is_numeric($tag_info[2])) {
                                    $params['geo_id'] = $tag_info[1]+0;
                                    unset($_args[$tag_info[2]]);
                                    unset($_args[$idx]);
                                    $tmp = array();
                                    foreach ($_args as $arg) {
                                        $tmp[] = $arg;
                                    }
                                    $_args = $tmp;
                                    $uri = implode("/", $_args);
                                }
                            }
                        }

                        $url_codes = $this->api->db->queryCacheResultSimpleArray($uri, "
                                    SELECT r.COUNTRY_ID, r.CITY_ID, r.ROOT_ID, r.SECTION_ID, r.PURPOSE_ID, trim(r.MODULE),
                                    iif(r.TITLE_EN>'', r.TITLE_EN, SUBSTRING(r.title from POSITION(ascii_char(9) , r.title) for 128)),
                                    iif(r.TITLE_AR>'', r.title_ar, SUBSTRING(r.title from 1 for POSITION(ascii_char(9), r.title))),
                                    r.REFERENCE, r.REDIRECT_TO
                                    FROM URI r
                                    where r.PATH=?
                                    and r.BLOCKED=0
                                    ", array($uri), -1, 0, false, TRUE);
                        
                        if ($url_codes) {
                            $params['country_id'] = $url_codes[0];
                            $params['city_id'] = $url_codes[1];
                            $params['root_id'] = $url_codes[2];
                            $params['section_id'] = $url_codes[3];
                            $params['purpose_id'] = $url_codes[4];

                        }else{
                            $params = [];
                        }
                    }
                }
                $this->api->result['d'] = $params;
                break;
                
            case API_ANDROID_FAVORITE:                
                $this->api->userStatus($status);
                if ($status == 1) 
                {
                    $this->api->db->setWriteMode();
                    $data = urldecode(filter_input(INPUT_GET, 'data', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '']]));
                    $data = json_decode($data, true);
                    $checkAdExists = $this->api->db->getInstance()->prepare(
                            'select id from ad where id = ?'
                    );
                    $stmt = $this->api->db->getInstance()->prepare(
                            'update or insert into web_users_favs (web_user_id, ad_id, deleted) '
                            . 'values (?, ?, ?) matching (web_user_id, ad_id) '
                            . 'returning id'
                    );
                    $this->api->result['d'] = [];
                    
                    include_once $this->api->config['dir'] . '/core/lib/SphinxQL.php';
                    $sphinx = new SphinxQL($this->api->config['sphinxql'], $this->api->config['search_index']);

                    
                    $q="select list(web_user_id) from web_users_favs where deleted=0 and ad_id=?";
                    $st = $this->api->db->getInstance()->prepare($q);  
                    
                    foreach ($data as $id => $set) {
                        $succeed=false;
                        $found = false;
                        if ($this->api->db->executeStatement($checkAdExists,[$id])!== false){
                            $oldAd = $checkAdExists->fetch(PDO::FETCH_NUM);
                            if(isset($oldAd[0]) && $oldAd[0]){
                                $found = true;
                            }
                        }
                        if($found){
                            $res = $this->api->db->executeStatement($stmt, [$this->api->getUID(), $id, ($set ? 0 : 1)]);
                            /*if ($res) {                 
                                if ($st->execute([$id])) { 
                                    if ($users=$st->fetch(PDO::FETCH_NUM)) {
                                        $q = "update {$this->api->config['search_index']} set starred=({$users[0]}) where id={$id}";
                                    } else {
                                        $q = "update {$this->api->config['search_index']} set starred=() where id={$id}";   
                                    }
                                    $succeed= $sphinx->directUpdateQuery($q);
                                }

                            }*/
                        }
                        //if ($succeed) {
                        //    $this->api->result['d'][] = $id;
                        //}elseif($sphinx->getLastError()==''){
                            $this->api->result['d'][] = $id;
                        //}
                    }
                    unset($checkAdExists);
                    unset($stmt);
                    unset($st);
                    $sphinx->close();
                }
                break;
            case API_ANDROID_WATCHLIST_ADD:                
                $this->api->userStatus($status);
                //if ($status == 1) {
                    $this->api->db->setWriteMode();
                    $data = urldecode(filter_input(INPUT_GET, 'data', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '']]));
                    $data = json_decode($data, true);
                    $stmt = $this->api->db->getInstance()->prepare(
                            "update or insert into SUBSCRIPTION "
                            . "(WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, TITLE, ADDED, EMAIL, publisher_type) "
                            . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, 0, ?) "
                            . "matching (WEB_USER_ID, COUNTRY_ID, CITY_ID, SECTION_ID, SECTION_TAG_ID, LOCALITY_ID, PURPOSE_ID, QUERY_TERM, publisher_type) "
                            . "returning id"
                    );
                    $this->api->result['d'] = [];
                    foreach ($data as $req) {
                        $publisherType = 0;
                        if(isset($req['publisher_type'])){
                            $publisherType = $req['publisher_type'];
                        }
                        $query = $req['query'];
                        if(strlen($query) > 100){
                            $query = substr($query, 0, 100);
                            $pos = strrpos($query, ' ');
                            if($pos > 0){
                                $query = trim( substr($query, 0, $pos));
                            }
                        }
                        $label = $req['label'];
                        if(strlen($label) > 200){
                            $label = substr($label, 0, 200);
                            $pos = strrpos($label, ' ');
                            if($pos > 0){
                                $label = trim( substr($label, 0, $pos));
                            }
                        }
                        $res = $this->api->db->executeStatement($stmt,[$this->api->getUID(), $req['country_id'], $req['city_id'], $req['section_id'], $req['tag_id'], $req['geo_id'], $req['purpose_id'], $query, $label, $publisherType]);
                        if ($res) {
                            $this->api->result['d'][] = $req['id'];
                        }
                    }
                    unset($stmt);
                //}
                break;
            case API_ANDROID_WATCHLIST_REMOVE:                
                $this->api->userStatus($status);
                //if ($status == 1) {
                    $this->api->db->setWriteMode();
                    $data = urldecode(filter_input(INPUT_GET, 'data', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '']]));
                    $data = json_decode($data, true);
                    $stmt = $this->api->db->getInstance()->prepare(
                            'delete from subscription where web_user_id = ? and country_id = ? '
                            . 'and city_id = ? and section_id = ? and section_tag_id = ? '
                            . 'and locality_id = ? and purpose_id = ? and query_term = ? and publisher_type = ?'
                    );
                    $this->api->result['d'] = [];
                    foreach ($data as $req) {
                        $publisherType = 0;
                        if(isset($req['publisher_type'])){
                            $publisherType = $req['publisher_type'];
                        }
                        $query = $req['query'];
                        if(strlen($query) > 100){
                            $query = substr($query, 0, 100);
                            $pos = strrpos($query, ' ');
                            if($pos > 0){
                                $query = trim( substr($query, 0, $pos) );
                            }
                        }
                        $res = $this->api->db->executeStatement($stmt,[$this->api->getUID(), $req['country_id'], $req['city_id'], $req['section_id'], $req['tag_id'], $req['geo_id'], $req['purpose_id'], $query, $publisherType]);
                        if ($res) {
                            $this->api->result['d'][] = $req['id'];
                        }
                    }
                    unset($stmt);
                //}
                break;
            case API_ANDROID_WATCHLIST_TOUCH:                
                $this->api->userStatus($status);
                //if ($status == 1) {
                    $data = urldecode(filter_input(INPUT_GET, 'data', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '']]));
                    $data = json_decode($data, true);
                    if (is_array($data) && count($data)) {
                        $this->api->db->setWriteMode();
                        $req = $data[0];
                        $publisherType = 0;
                        if(isset($req['publisher_type'])){
                            $publisherType = $req['publisher_type'];
                        }
                        $this->api->db->queryResultArray(
                                'update subscription set badge_count=0, last_visit=current_timestamp '
                                . 'where web_user_id = ? and country_id = ? '
                                . 'and city_id = ? and section_id = ? and section_tag_id = ? '
                                . 'and locality_id = ? and purpose_id = ? and query_term = ? and publisher_type = ?', [$this->api->getUID(), $req['country_id'], $req['city_id'], $req['section_id'], $req['tag_id'], $req['geo_id'], $req['purpose_id'], $req['query'], $publisherType], TRUE
                        );
                    }
                //}
                break;
                
            case API_ANDROID_POST_AD:         
                if($this->api->config['active_maintenance'])
                {
                    $this->api->result['e'] = "503";
                    break;
                }
                
                $opts = $this->api->userStatus($status);   
                $mcUser = new MCUser($this->api->getUID());
                
                if ($status==1 && !$mcUser->isBlocked()) 
                {
                    $this->api->db->setWriteMode();  
                    
                    $direct_publish = filter_input(INPUT_POST, 'pub', FILTER_VALIDATE_INT) + 0;
                    
                    $rid = filter_input(INPUT_POST, 'rid', FILTER_VALIDATE_INT) + 0;
                    $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    $device_lang = filter_input(INPUT_GET, 'hl');
                    if(!in_array($device_lang, ['ar','en'])){
                        $device_lang = 0;
                    }
                    $state = 0;
                    $ad = json_decode(urldecode(filter_input(INPUT_POST, 'ad', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']])), true);
                    
                    $userState = 0;
                    
                    $hasFailure = 0;
                    $hasMajorFailure = 0;
                    
                    $stmt = null;
                    
                    if(count($ad)>0)
                    {    
                    
                        if($ad['se']>0 && $ad['pu']==0)
                        {
                            $ad['pu']=5;
                        }
                        
                        /*
                        if($ad['rtl'] == 1){
                            $ad['other'] .= "\u200B / ".$ad['contact_ar'];
                            if(strlen($ad['altother']) >= 30){
                                $ad['altother'] .= "\u200B / ".$ad['contact_en'];
                            }
                        }else{
                            $ad['other'] .= "\u200B / ".$ad['contact_en'];
                        }
                        */

                        $_original_ad=$ad;
                        include_once $this->api->config['dir'] . '/core/lib/MCSaveHandler.php';                
                        $normalizer = new MCSaveHandler($this->api->config);
                        $normalized = $normalizer->getFromContentObject($ad);
                        $attrs = [];
                        if ($normalized)
                        {
                            $ad = $normalized;
                            $attrs = $normalized['attrs'];
                        }                

                        if (!isset($ad['other']))
                        {
                            NoSQL::Log($ad);
                            NoSQL::Log($_original_ad);
                        }
                        
                        if($device_lang){
                            $ad['hl']=$device_lang;
                        }
                        $ad['rtl'] = ($this->isRTL($ad['other'])) ? 1 : 0;
                        
                        if(isset($ad['altother']) && $ad['altother'])
                        {
                            $ad['altRtl'] = ($this->isRTL($ad['altother'])) ? 1 : 0;

                            if($ad['rtl'] == $ad['altRtl'])
                            {
                                $ad['extra']['t']=2;
                                unset($ad['altRtl']);
                                unset($ad['altother']);
                            }

                            if(isset($ad['altRtl']) && $ad['altRtl'])
                            {
                                $tmp=$ad['other'];
                                $ad['other']=$ad['altother'];
                                $ad['altother']=$tmp;
                                $ad['rtl']=1;
                                $ad['altRtl']=0;
                            }
                        }
                        
                        if(isset($ad['extra']['m']) && !$ad['extra']['m'] && $ad['lat']==0 && $ad['lon']==0){
                            $ad['extra']['m']=2;
                        }
                        
                        $requireReview = 0;
                        
                        $ip ='';
                        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                        }else {
                            $ip = $_SERVER['REMOTE_ADDR'];
                        }
                        $ad['ip']=$ip;                            
                        $databaseFile = '/home/db/GeoLite2-City.mmdb';
                        $reader = new Reader($databaseFile);
                        $geo = $reader->get($ip);
                        $reader->close(); 	
                        
                        $XX='';                
                        if($geo) 
                        {
                            $ad['userLOC'] = isset($geo['city']['names']['en']) && $geo['city']['names']['en'] ? $geo['city']['names']['en'].', ' : '';
                            $ad['userLOC'].=$geo['country']['iso_code'];
                            $ad['userLOC'].=': '. implode(" ,",$geo['location']);                            
                            $XX=$geo['country']['iso_code'];                            
                        } else $ad['userLOC']=0;
                        
                        if($mcUser->isMobileVerified())
                        {
                            $uNum = $mcUser->getMobileNumber();
                            if($uNum)
                            {
                                $validator = libphonenumber\PhoneNumberUtil::getInstance();
                                $uNum = $validator->parse('+'.$uNum, 'LB');
                                $TXX = $validator->getRegionCodeForNumber($uNum);
                                if($TXX)
                                {
                                    $XX=$TXX;
                                }
                            }
                        }
                        
                        if(!$XX || !in_array($XX,[
                                'AE',
                                'BH',
                                'DZ',
                                'YE',
                                'EG',
                                'IQ',
                                'JO',
                                'KW',
                                'LB',
                                'LY',
                                'MA',
                                'QA',
                                'SA',
                                'SD',
                                'SY',
                                'TN',
                                'TR',
                                'OM'
                                ]))
                        {
                            $requireReview = 1;
                        }
                            
                        $city_id = 0;
                        $country_id = 0;
                        $currentCid = 0;
                        $isMultiCountry = false;
                        $cities = $this->api->db->getCitiesDictionary();
                        
                        foreach($ad['pubTo'] as $key => $val)
                        {
                            if(!$city_id && isset($cities[$city_id]))
                            {
                                $city_id=$key;
                            }
                            
                            if($key && isset($cities[$key]))
                            {
                                if($currentCid && $currentCid != $cities[$key][4])
                                {
                                    $isMultiCountry = true;
                                }
                                $currentCid = $cities[$key][4];
                            }
                        }
                        
                        foreach($ad['pubTo'] as $key => $val)
                        {
                            $city_id=$key;
                            break;
                        }
                        
                        if($city_id)
                        {
                            $country_id=$cities[$city_id][4];
                        }
                        
                        $isSCAM = 0;
                        if(isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0)
                        {
                            $blockedEmailPatterns = addcslashes(implode('|', $this->api->config['restricted_email_domains']),'.');
                            $isSCAM = preg_match('/'.$blockedEmailPatterns.'/ui', $ad['cui']['e']);
                        }
                        elseif($requireReview && $country_id && !$isMultiCountry)
                        {
                            $countries = $this->api->db->getCountriesData('en');
                            if(isset($countries[$country_id]['code']))
                            {
                                $countryCode = '+'.$countries[$country_id]['code'];
                                //error_log("mobile check #{$ad['id']}# ".$countryCode);
                                $differentCodes = false;
                                foreach($ad['cui']['p'] as $number)
                                {
                                    //error_log("number ".$number['v']);
                                    //error_log("with ".substr($number['v'], 0, strlen($countryCode)));
                                    if(substr($number['v'], 0, strlen($countryCode)) != $countryCode)
                                    {
                                        $differentCodes = true;
                                    }
                                }
                                
                                if(!$differentCodes)
                                {
                                    //error_log("rollback review");
                                    $requireReview = 0;
                                }
                            }
                        }
                        
                        if(!$isSCAM && !$requireReview && isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0)
                        {
                            $requireReview = preg_match('/\+.*@/', $ad['cui']['e']);
                            if(!$requireReview)
                            {
                                $requireReview = preg_match('/hotel/', $ad['cui']['e']);
                            }
                            if(!$requireReview)
                            {
                                $requireReview = preg_match('/\..*\..*@/', $ad['cui']['e']);
                            }
                        }
                        
                        if($ad['se']==0 || $ad['pu']==0 || count($ad['pubTo'])==0)
                        {
                            $hasFailure=1;
                            //$hasMajorFailure=1;
                            if($device_lang=='ar' || $ad['rtl'])
                            {
                                $msg = 'يرجى تعديل الاعلان وادخال التفاصيل الناقصة';
                            }
                            else
                            {
                                $msg = 'please edit ad and complete missing details';
                            }
                            $ad['msg'] = $msg;
                        }
                        
                        if(isset($ad['SYS_CRAWL']) && $ad['SYS_CRAWL'])
                        {
                            $hasFailure=1;
                            if($device_lang=='ar' || $ad['rtl'])
                            {
                                $msg = 'يرجى استخدام PropSpace لتعديل هذا الاعلان';
                            }
                            else
                            {
                                $msg = 'please use PropSpace to edit this ad';
                            }
                            $ad['msg'] = $msg;
                        }
                        
                        if($opts->appVersion < '1.3.4' || $opts->appVersion=='1.8.8')
                        {
                            $hasFailure=1;
                            if($device_lang=='ar' || $ad['rtl'])
                            {
                                $msg = 'يرجى تحديث تطبيق مرجان لنشر الاعلانات';
                            }
                            else
                            {
                                $msg = 'please update mourjan app to publish ads';
                            }
                            $ad['msg'] = $msg;
                        }
                        
                        if ($isSCAM)
                        {
                            if($mcUser->isMobileVerified())
                            {
                                $this->block($this->api->getUID(), $mcUser->getMobileNumber(), 'scam detection by system based on certain email keywords');
                            }
                            else
                            {
                                $this->setLevel($this->api->getUID(),5);
                            }                            
                        }
                        elseif($requireReview && $ad_id)
                        {
                            $this->referrToSuperAdmin($ad_id);
                        }
                        else if($hasMajorFailure)
                        {
                            $ad_id = 0;
                            $state = 3;
                        }
                        else
                        { 
                            if($ad_id>0) 
                            {
                                //cleanup ad_media xref 
                                if(isset($ad['pics']) && is_array($ad['pics']) && count($ad['pics'])){
                                    $keys = array_keys($ad['pics']);
                                    $filenames = '';
                                    foreach($ad['pics'] as $key => $values){
                                        if($filenames!=''){
                                            $filenames.=',';
                                        }
                                        $filenames .= "'{$key}'";
                                    }

                                    $records = $this->api->db->queryResultArray(
                                        "select id from media where filename in ({$filenames})", null, false
                                    );

                                    if($records !== false){
                                        $mediaIds = [];
                                        if($records && is_array($records)){
                                            foreach ($records as $media){
                                                $mediaIds[] = $media['ID'];
                                            }
                                        }

                                        if(count($mediaIds)){
                                            $mediaIds = implode(",", $mediaIds);

                                            $this->api->db->queryResultArray(
                                                "delete from ad_media where ad_id = ? and media_id not in ({$mediaIds})", [$ad_id], false
                                            );

                                        }else{

                                            $this->api->db->queryResultArray(
                                                "delete from ad_media where ad_id = ?", [$ad_id], false
                                            );

                                        }
                                    }

                                }else{
                                    $this->api->db->queryResultArray(
                                        "delete from ad_media where ad_id = ?", [$ad_id], false
                                    );
                                }
                                //end of ad_media cleanup
                                
                                
                                $this->api->db->queryResultArray(
                                    "update ad set hold=1 where id=? and hold=0 and (exists (select 1 from ad_user d where d.id=? and d.web_user_id=?)) returning id", [$ad_id, $ad_id, $this->api->getUID()], false
                                );
                                
                                if($ad['state'] == 1 && isset($ad['budget']) && $ad['budget']+0 > 0)
                                {
                                    $ad['state'] = 4;
                                }
                                $state = $ad['state'];
                                
                                if($hasFailure){
                                    $state = 3;
                                }
                                $ad['state']=$state;

                                $encodedAd = json_encode($ad);

                                $json_error = json_last_error();

                                if($json_error==5)
                                {
                                    error_log("JSON ERROR");
                                    if(isset($ad['userLOC']) && $ad['userLOC'])
                                    {
                                        $ad['userLOC']=$ad['ip'];
                                        $encodedAd = json_encode($ad);
                                        $json_error = json_last_error();
                                    }
                                }

                                $q='update ad_user set
                                    content=?,purpose_id=?,section_id=?,rtl=?,
                                    country_id=?,city_id=?,latitude=?,longitude=?,state=?,media=?,date_added=current_timestamp 
                                    where id=? and web_user_id+0=? 
                                    returning state, id';
                                $stmt = $this->api->db->getInstance()->prepare($q);

                                $result=null;
                                if ($this->api->db->executeStatement($stmt,[
                                        $encodedAd,
                                        $ad['pu'],
                                        $ad['se'],
                                        $ad['rtl'],
                                        $country_id,
                                        $city_id,
                                        $ad['lat'],
                                        $ad['lon'],
                                        $state,
                                        $ad['media'],
                                        $ad_id,
                                        $this->api->getUID()
                                    ])) 
                                {
                                    $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                }   
                                
                                if (!empty($result)) 
                                {                                        
                                    $state=$result[0]['STATE'];
                                    //$ad_id = (int)$result[0]['ID'];
                                        
                                    $st = $this->api->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                                    $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                                    $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                                    $this->api->db->executeStatement($st);
                                }                                

                                if( $ad['state']==1 ) 
                                {
                                    if($mcUser->isMobileVerified())
                                    {
                                        $userState = $mcUser->isSuspended() ? 1:0;
                                    }
                                    else
                                    {
                                        $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                                    }
                                }
                            }
                            else 
                            {
                                $state = 0;
                                if($direct_publish){
                                    $state = 1;
                                }

                                if($state == 1 && isset($ad['budget']) && $ad['budget']+0 > 0){
                                    $state = 4;
                                }
                                
                                if($hasFailure){
                                    $state = 3;
                                }
                                $ad['state']=$state;

                                $encodedAd = json_encode($ad);

                                $json_error = json_last_error();

                                if($json_error==5){
                                    error_log("JSON ERROR");
                                    if(isset($ad['userLOC']) && $ad['userLOC']){
                                        $ad['userLOC']=$ad['ip'];
                                        $encodedAd = json_encode($ad);
                                        $json_error = json_last_error();
                                    }
                                }

                                $result=$this->api->db->queryResultArray(
                                    "insert into ad_user
                                    (web_user_id,content,title,purpose_id,section_id,rtl, 
                                    country_id,city_id,latitude,longitude,media,state)
                                    values (?,?,'',?,?,?,?,?,?,?,?,{$state}) returning id,state", 
                                    array($this->api->getUID(), $encodedAd, $ad['pu'], $ad['se']
                                        ,$ad['rtl'], $country_id , $city_id, $ad['lat'],$ad['lon'],$ad['media'] ), 
                                        true
                                );

                                if (!empty ($result)) {
                                    $ad_id=$result[0]['ID'];
                                    $state=(int)$result[0]['STATE'];
                                    
                                    $st = $this->api->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                                    $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                                    $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                                    $this->api->db->executeStatement($st);
                                }
                                if($requireReview && $ad_id){
                                    $this->referrToSuperAdmin($ad_id);
                                }
                                
                                if( $state==1 ) {
                                    if($mcUser->isMobileVerified()){
                                        $userState = $mcUser->isSuspended() ? 1:0;
                                    }else{
                                        $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                                    }                          
                                }
                            }
                            if($ad_id && $state == 1){                                
                                $dbAd = $this->api->db->queryResultArray(
                                        'select a.id,
                                        IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) featured_date_ended, 
                                        IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) bo_date_ended '                         
                                        . 'from ad_user a '
                                        . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 '
                                        . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '                                                       
                                        . 'where a.id = ?', [$ad_id], true
                                );
                                if(isset($dbAd[0]['ID']) && $dbAd[0]['ID']){
                                    $dbAd=$dbAd[0];
                                    $current_time = time();
                                    $isFeatured = isset($dbAd['FEATURED_DATE_ENDED']) && $dbAd['FEATURED_DATE_ENDED'] ? ($current_time < $dbAd['FEATURED_DATE_ENDED']) : false;
                                    $isFeatureBooked = isset($dbAd['BO_DATE_ENDED']) && $dbAd['BO_DATE_ENDED'] ? ($current_time < $dbAd['BO_DATE_ENDED']) : false;
                                    if($isFeatured || $isFeatureBooked){
                                        $state = 4;
                                    }
                                }
                            }
                            if($ad_id && $state == 4 && $isMultiCountry){
                                $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);
                                $state = 3;
                                if($device_lang=='ar' || $ad['rtl']){
                                    $msg = 'عذراً ولكن لا يمكن تمييز الاعلان في اكثر من بلد واحد';
                                }else{
                                    $msg = 'Sorry, you cannot publish premium ads targetting more than ONE country';
                                }
                                $ad['state']=$state;
                                $ad['msg'] = $msg;

                                $encodedAd = json_encode($ad);
                                $result=null;
                                $this->api->db->executeStatement($suspendStmt,[                                    
                                    $encodedAd,
                                    $state,
                                    $ad_id
                                ]);
                                unset($suspendStmt);
                            }elseif($ad_id && $userState == 1){
                                $state=3;
                                $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);

                                if($device_lang=='ar' || $ad['rtl']){
                                    $msg = 'لقد تم ايقاف حسابك بشكل مؤقت نظراً للتكرار';
                                }else{
                                    $msg = 'your account is suspended due to repetition';
                                }
                                $ad['state']=$state;
                                $ad['msg'] = $msg;

                                $encodedAd = json_encode($ad);
                                $result=null;
                                $this->api->db->executeStatement($suspendStmt,[                                    
                                    $encodedAd,
                                    $state,
                                    $ad_id
                                ]);
                                unset($suspendStmt);
                            }else if($ad_id && in_array($ad['se'],array(190,1179,540,1114))){
                                $dupliactePending = $this->detectIfAdInPending($ad_id, $ad['se'], $ad['cui']);
                                $state = 3;
                                if($dupliactePending){
                                    $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);
                                    if($device_lang=='ar' || $ad['rtl']){
                                        $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                                    }else{
                                        $msg = 'There is another similar ad pending Editors\' approval';
                                    }
                                    $ad['state']=$state;
                                    $ad['msg'] = $msg;

                                    $encodedAd = json_encode($ad);
                                    $result=null;
                                    $this->api->db->executeStatement($suspendStmt,[                                    
                                        $encodedAd,
                                        $state,
                                        $ad_id
                                    ]);
                                    unset($suspendStmt);
                                }
                            }
                        }
                    }
                    
                    $this->api->result['d'] = [];
                    
                    $this->api->result['d']['id'] = $rid;
                    $this->api->result['d']['adid'] = $ad_id;
                    $this->api->result['d']['state'] = $state;
                    //error_log(json_encode($this->api->result['d']));
                    
                    unset($stmt);
                }
                break;
                
                
                case API_ANDROID_GET_AD:                
                $this->api->userStatus($status);
                if ($status == 1) {      
                    if(isset($_GET['adid'])){
                        $ad_id = filter_input(INPUT_GET, 'adid', FILTER_VALIDATE_INT) + 0;
                    }else{
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    }
                    
                    $result = $this->api->db->queryResultArray(
                            'select a.id, a.content, a.state, a.section_id, a.purpose_id,a.media,DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', a.last_update) last_update,a.doc_id, '
                            . 'IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) feature_end, '
                            . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) booking_end, '                            
                            . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.start_date)) booking_start '                            
                            . 'from ad_user a '
                            . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked=0 '
                            . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '                                                       
                            . 'where a.id = ? and a.web_user_id = ?', [$ad_id, $this->api->getUID()], true
                    );
                    
                    
                    $this->api->result['d'] = [];
                    if($result && isset($result[0]['ID'])){
                        $content = json_decode($result[0]['CONTENT'],true);
                        $content['id']=$result[0]['ID'];
                        $content['user']=$this->api->getUID();
                        $content['se']=$result[0]['SECTION_ID'];
                        $content['pu']=$result[0]['PURPOSE_ID'];
                        $content['state']=$result[0]['STATE'];
                        $content['media']=$result[0]['MEDIA'];
                        $content['sys_update']=$result[0]['LAST_UPDATE'];
                        $content['featured']=$result[0]['FEATURE_END'];
                        $content['booked']=$result[0]['BOOKING_END'];
                        $content['book']=$result[0]['BOOKING_START'];
                        if(isset($result[0]['DOC_ID']) && $result[0]['DOC_ID']){
                            $content['SYS_CRAWL']=1;
                        }elseif(isset($content['SYS_CRAWL'])){
                            unset($content['SYS_CRAWL']);
                        }

                        if(isset($content['userLOC'])){
                            unset($content['userLOC']);
                        }
                        if(isset($content['ip'])){
                            unset($content['ip']);
                        }
                        if(isset($content['text'])){
                            unset($content['text']);
                        }
                        if (isset($content['attrs'])) {
                            unset($content['attrs']);
                        }
                        $this->api->result['d']['ad'] = json_encode($content);
                        
                    }else{
                        $this->api->result['d']['ad'] = "{}";
                    }
                }
                break;
                case API_ANDROID_PUSH_RECEIPT:
                    $this->api->db->setWriteMode(); 
                    if(isset($_GET['uid'])){
                        $id = filter_input(INPUT_GET, 'mid', FILTER_VALIDATE_INT) + 0; 
                    }else{
                        $id = filter_input(INPUT_POST, 'mid', FILTER_VALIDATE_INT) + 0; 
                    }
                    if($id){
                        $publishedAd = $this->api->db->queryResultArray(
                            'update push_queue set delivered = 1 where uuid =? and msg_id=?', [$this->api->getUUID(), $id], true
                        );
                    }
                    break;
                case API_ANDROID_DELETE_AD:                
                $this->api->userStatus($status);
                if ($status == 1) {
                    $this->api->db->setWriteMode();                    
                    $rid = filter_input(INPUT_POST, 'rid', FILTER_VALIDATE_INT) + 0;
                    $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    
                    $publishedAd = $this->api->db->queryResultArray(
                                'select id from ad where id = ?', [$ad_id], true
                        );
                    $result=null;
                    if($publishedAd && count($publishedAd) > 0){
                        $result = $this->api->db->queryResultArray(
                                'update ad_user set state=8 where id=? and web_user_id = ? returning id', [$ad_id, $this->api->getUID()], true
                        );
                    }else{
                        $result = $this->api->db->queryResultArray(
                                'update ad_user set state=6 where id=? and web_user_id = ? returning id', [$ad_id, $this->api->getUID()], true
                        );
                    }
                    
                    $this->api->result['d'] = [];
                    if($result && isset($result[0]['ID'])){
                    
                        $this->api->result['d']['id'] = $rid;
                        
                    }else{
                        $this->api->result['d']['id'] = 0;
                    }
                }
                break;
                case API_ANDROID_SET_NOTE:
                    $this->api->userStatus($status);
                    if ($status == 1) {   
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                        $note = urldecode(filter_input(INPUT_POST, 'note', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                        $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                        IF($ad_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
                            $this->api->db->setWriteMode();  
                            $result = false;
                            if($note == ""){                                
                                $result = $this->api->db->queryResultArray(
                                    "update or insert into web_users_notes (web_user_id, ad_id, deleted) values (?,?,1) matching(web_user_id, ad_id) returning id", [$this->api->getUID(), $ad_id], true
                                );  
                            }else{                                
                                $result = $this->api->db->queryResultArray(
                                    "update or insert into web_users_notes (web_user_id, ad_id, content, deleted) values (?,?,?,0) matching(web_user_id, ad_id) returning id", [$this->api->getUID(), $ad_id, $note], true
                                );  
                            }                  
                    
                            $this->api->result['d'] = [];
                            if($result && isset($result[0]['ID'])){
                                $this->api->result['d']['id'] = $ad_id;                        
                            }else{
                                $this->api->result['d']['id'] = 0;
                            }
                            
                        }
                    }         
                    break;
                case API_ANDROID_HOLD_AD:                
                $this->api->userStatus($status);
                if ($status == 1) {                  
                    $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    $this->api->db->setWriteMode();   
                    $result = $this->api->db->queryResultArray(
                            "update ad a set a.hold = 1 where a.id = ? and ((select web_user_id from ad_user d where d.id = ?) = ?) returning id", [$ad_id,$ad_id, $this->api->getUID()], true
                    );
                    
                    
                    $this->api->result['d'] = [];
                    if($result && isset($result[0]['ID'])){
                        $this->api->result['d']['hold'] = $result[0]['ID'];
                        
                    }else{
                        $this->api->result['d']['hold'] = 0;
                    }
                }
                break;
                
                case API_ANDROID_RENEW_AD:                
                    $opts = $this->api->userStatus($status);                    
                    
                    //$userData = MCSessionHandler::getUser($this->api->getUID());
                    $mcUser = new MCUser($this->api->getUID()); //$userData);
                    $state=0;
                    if ($status == 1 && !$mcUser->isSuspended() && !$mcUser->isBlocked()) 
                    {
                        $this->api->db->setWriteMode();   
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    
                        $ad = $this->api->db->queryResultArray(
                            'select a.id, a.content, a.state, a.section_id, a.purpose_id, a.rtl ' .
                            'from ad_user a ' .
                            'where a.id=? and a.web_user_id=?', [$ad_id, $this->api->getUID()], true
                        );
                    
                        $renew= true;
                    
                        if($ad && isset($ad[0]['ID']) && $ad[0]['ID'])
                        {
                            $ad = $ad[0];
                            $content = json_decode($ad['CONTENT'], true);
                            
                            if(isset($content['budget'])){
                                $content['budget'] = 0;
                            }
                            
                            if(isset($content['SYS_CRAWL']) && $content['SYS_CRAWL']){
                                $this->api->result['d']['renew'] = 0;
                            }else{
                                if(in_array($ad['SECTION_ID'],array(190,1179,540,1114)))
                                {
                                    $dupliactePending = $this->detectIfAdInPending($ad_id, $ad['SECTION_ID'], $content['cui']);
                                    if($dupliactePending)
                                    {
                                        $renew= false;
                                        $state=3;
                                        $q='update ad_user set content=?, state=? where id=?';
                                        $suspendStmt = $this->api->db->getInstance()->prepare($q);
                                        if($content['rtl']){
                                            $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                                        }else{
                                            $msg = 'There is another similar ad pending Editors\' approval';
                                        }
                                        $ad['msg'] = $msg;

                                        $encodedAd = json_encode($content);
                                        $result=null;
                                        $this->api->db->executeStatement($suspendStmt,[                                    
                                            $encodedAd,
                                            $state,
                                            $ad_id
                                        ]);
                                        unset($suspendStmt);
                                    }
                                }

                                if($renew)
                                {                           
                                    $state = 1;
                                    if($opts->appVersion < '1.3.4' || $opts->appVersion=='1.8.8'){
                                        if($device_lang=='ar' || $ad['RTL']){
                                            $msg = 'يرجى تحديث تطبيق مرجان لنشر الاعلانات';
                                        }else{
                                            $msg = 'please update mourjan app to publish ads';
                                        }
                                        $content['msg'] = $msg;
                                        $state = 3;
                                    }
                                    include_once $this->api->config['dir'] . '/core/lib/MCSaveHandler.php';                
                                    $normalizer = new MCSaveHandler($this->api->config);
                                    if (isset($content['attrs'])) {
                                        unset($content['attrs']);
                                    }
                                    $content['state']=$state;
                                    $normalized = $normalizer->getFromContentObject($content);
                                    $attrs = [];
                                    if ($normalized)
                                    {
                                        $ad['CONTENT'] = $normalized;
                                        $attrs = $normalized['attrs'];
                                        if ($ad['SECTION_ID']!=$normalized['se'])
                                            $ad['SECTION_ID']=$normalized['se'];
                                        if ($ad['PURPOSE_ID']!=$normalized['pu'])
                                            $ad['PURPOSE_ID']=$normalized['pu'];
                                    }                                        

                                    $result = $this->api->db->queryResultArray(
                                        "update ad_user a set a.section_id=?, a.purpose_id=?, a.content=?, a.state={$state} where a.id=? and a.web_user_id=? and a.state=9 returning id", 
                                        [$ad['SECTION_ID'], $ad['PURPOSE_ID'], json_encode($ad['CONTENT']), $ad_id, $this->api->getUID()], true);


                                    if (!empty($result)) 
                                    { 
                                        $st = $this->api->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                                        $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                                        $st->bindValue(2, json_encode($attrs, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
                                        $this->api->db->executeStatement($st);                                     
                                    }

                                    $this->api->result['d'] = [];
                                    $this->api->result['d']['renew'] = ($result && isset($result[0]['ID'])) ? $result[0]['ID'] : 0;                                                        
                                }else{
                                    $this->api->result['d']['renew'] = $ad_id;
                                }
                            }
                        }else{
                            $this->api->result['d']['renew'] = 0;
                        }
                    }
                
                break;  
                
                case API_ANDROID_USER_RECEIVE_CALL:
                    /*$number = filter_input(INPUT_POST, 'tel');
                    if($number){
                        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                        $num = $this->mobileValidator->parse($number, 'LB');
                        if (!is_array($this->api->result['d'])){
                            $this->api->result['d']=[];
                        }

                        if ($num && $this->mobileValidator->isValidNumber($num)){
                            $number = intval($number);
                            
                            $record = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number);
                            if($record){
                                
                            }
                        }
                    }*/
                    break;
                
                case API_ANDROID_USER_MAKE_CALL:
                    $number = filter_input(INPUT_POST, 'tel');
                    $reverseCall = filter_input(INPUT_POST, 'reverse', FILTER_SANITIZE_NUMBER_INT);
                    $keyCode = filter_input(INPUT_POST, 'code',FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);                    
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    $signature = trim(filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
                    $appVersion = filter_input(INPUT_GET, 'apv', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    $parity = strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY));                    
                    
                    if($number && base64_decode($signature) == $parity)
                    {
                        $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                        $num = $this->mobileValidator->parse($number, 'LB');
                        if (!is_array($this->api->result['d']))
                        {
                            $this->api->result['d']=[];
                        }

                        if ($num && $this->mobileValidator->isValidNumber($num))
                        {
                            $numberType = $this->mobileValidator->getNumberType($num);
                            if ($numberType==libphonenumber\PhoneNumberType::MOBILE || $numberType==libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE)
                            {
                                if($keyCode){
                                    $defNumber = $number;
                                    $number = intval($number);
                                    
                                    $record = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number);
                                    if($record){
                                        if (isset($record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]>(time()-31536000))
                                        {
                                            $this->api->result['d']['verified'] = true;
                                        }else{
                                            
                                            if($reverseCall){
                                                $keycode = 0;
                                                //error_log("reverse call with key");
                                                //$response = MobileValidation::getInstance()->setUID($this->api->getUID())->verifyNexmoCallPin($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID], $keyCode);                                                
                                                $response = MobileValidation::getInstance()->setUID($this->api->getUID())->verifyEdigearPin($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID], $keyCode+0);                                                
                                                if (isset($response['status']) && $response['status']==200 && isset($response['response']))
                                                {
                                                    if ($response['response']['validated'])
                                                    {
                                                        $activated = NoSQL::getInstance()->mobileActivationByRequestId($this->api->getUID(), $number, $keyCode, $record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]);
                                                        if($activated){
                                                            $this->api->result['d']['verified'] = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $this->api->result['d']['verified']=false;
                                                    }
                                                }
                                            }else{                                            
                                                if (MobileValidation::getInstance()->verifyStatus($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]))
                                                {
                                                    $activated = NoSQL::getInstance()->mobileActivationByRequestId($this->api->getUID(), $number, $keyCode, $record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]);
                                                    if($activated){
                                                        $this->api->result['d']['verified'] = true;
                                                    }
                                                }else{
                                                    $this->api->result['d']['pending'] = true;
                                                }     
                                            }
                                        }
                                    }
                                    $number = $defNumber;
                                    
                                }else{
                                
                                    if(substr($number,0,1)=='+')
                                    {
                                        $number = substr($number,1);
                                    }
                                    
                                    /*check if number is blocked*/
                                    if (NoSQL::getInstance()->isBlacklistedContacts([$number]))
                                    {
                                        $number=0;
                                        $this->api->result['d']['blocked']=true;
                                    }

                                    if($number)
                                    {
                                        /*check if number is suspended*/
                                        $time = MCSessionHandler::checkSuspendedMobile($number);
                                        if($time>60)
                                        {
                                            $number=0;
                                            $this->api->result['d']['suspended']=$time;
                                        }
                                    }                                 

                                    if($number)
                                    {               
                                        $makeCall= false;
                                        
                                        if($reverseCall){
                                            $ret = MobileValidation::getInstance()->setUID($this->api->getUID())->setPlatform(MobileValidation::ANDROID)->requestReverseCLI($number, $response);
                                            switch ($ret) 
                                            {
                                                case MobileValidation::RESULT_ERR_SENT_FEW_MINUTES:
                                                case MobileValidation::RESULT_OK:
                                                    $this->api->result['d']['pending_call'] = true;
                                                    if (isset($response['response']['called']))
                                                    {
                                                        $this->api->result['d']['pending_call'] = false;
                                                    }
                                                    $inCall = $response['response']['cli_full'];
                                                    $inCall = substr($inCall, 0, strlen($inCall)-7);
                                                    $inCall = $inCall . 'yyyXXXX';
                                                    $this->api->result['d']['dial'] = '+'.$inCall;
                                                    break;                        

                                                case MobileValidation::RESULT_ERR_ALREADY_ACTIVE:
                                                    $this->api->result['d']['verified'] = true;
                                                    break;

                                                default:
                                                    $number=0;
                                                    $this->api->result['d']['code'] = 0;
                                                    break;
                                            }      
                                            
                                            
                                        }else{
                                            $result = MobileValidation::getInstance()->
                                                setUID($this->api->getUID())->
                                                setPlatform(MobileValidation::ANDROID)->
                                                sendCallerId($number);
                                            switch ($result)
                                            {
                                                case MobileValidation::RESULT_OK:
                                                case MobileValidation::RESULT_ERR_SENT_FEW_MINUTES:
                                                    $record = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number);
                                                    $this->api->result['d']['dial'] = '+'.$record[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                                                    $this->api->result['d']['code'] = $record[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                                                    break;

                                                case MobileValidation::RESULT_ERR_ALREADY_ACTIVE:
                                                    $this->api->result['d']['verified'] = true;
                                                    break;

                                                default:
                                                    $number=0;
                                                    $this->api->result['d']['code'] = 0;
                                                    break;
                                            } 
                                        }

                                        if($number){               
                                            if(substr($number,0,1)!='+'){
                                                $number = '+'.$number;
                                            }
                                        }

                                    }
                                }
                                
                                $this->api->result['d']['number']=$number;

                            }
                            else
                            {
                                $this->api->result['d']['check'] = false;
                            }
                        }
                        else
                        {
                            $this->api->result['d']['check'] = false;
                        }
                    }
                    break;
                
                case API_ANDROID_USER_NUMBER:
                    $keyCode=0;
                    $number = filter_input(INPUT_POST, 'tel');
                    //$number = filter_input(INPUT_POST, 'tel', FILTER_VALIDATE_INT)+0;
                    $keyCode = filter_input(INPUT_POST, 'code');
                    $keyCode = is_numeric($keyCode) ? $keyCode : 0;
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    $signature = trim(filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]));
                    $appVersion = filter_input(INPUT_GET, 'apv', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    
                    $parity = strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY));
                    error_log(sprintf("%s\t%d\t%s\t%s", date("Y-m-d H:i:s"), $number, $signature, json_encode($_POST)).PHP_EOL, 3, "/var/log/mourjan/sms.log");
                    error_log(sprintf("%s\tS: %s", date("Y-m-d H:i:s"), base64_decode($signature)).PHP_EOL, 3, "/var/log/mourjan/sms.log");
                    error_log(sprintf("%s\tP: %s", date("Y-m-d H:i:s"), $parity).PHP_EOL, 3, "/var/log/mourjan/sms.log");
                    if($number && base64_decode($signature) == $parity)
                    {
                        
                        if($keyCode)
                        {                      
                            $this->api->result['d']['verified']=false;
                            $defNumber = $number;
                            $number = intval($number);
                            if(substr($number,0,1)=='+'){
                                $number = substr($number,1);
                            }

                            $record = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number);
                            if($record){
                                if (isset($record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]) && $record[Core\Model\ASD\USER_MOBILE_DATE_ACTIVATED]>(time()-31536000))
                                {
                                    $this->api->result['d']['verified'] = true;
                                }else{
                                    $response = MobileValidation::getInstance()->setUID($this->api->getUID())->verifyEdigearPin($record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID], $keyCode+0);                                                
                                    if (isset($response['status']) && $response['status']==200 && isset($response['response']))
                                    {
                                        if ($response['response']['validated'])
                                        {
                                            $activated = NoSQL::getInstance()->mobileActivationByRequestId($this->api->getUID(), $number, $keyCode, $record[\Core\Model\ASD\USER_MOBILE_REQUEST_ID]);
                                            if($activated){
                                                $this->api->result['d']['verified'] = true;
                                            }
                                        }
                                    }
                                }
                            }
                            $number = $defNumber;
                            $this->api->result['d']['number']=$number;
                            $this->api->result['d']['code']=$keyCode;
                            
                            
                            /*
                            if(substr($number,0,1)=='+')
                            {
                                $number = substr($number,1);
                            }
                            
                            $this->api->result['d']['number']=$number;
                            $this->api->result['d']['code']=$keyCode;
                                
                            if ($this->api->getUID()==0)
                            {
                                error_log(__FUNCTION__.' uid<0>: ' . json_encode($this->api->user));                                
                                $this->api->result['d']['verified']=false;
                            }
                            else
                            {                                                           
                                if (NoSQL::getInstance()->mobileActivation($this->api->getUID(), $number, $keyCode))
                                {
                                    $this->api->result['d']['verified']=true;
                                }
                                else 
                                {
                                    $this->api->result['d']['verified']=false;
                                }
                            }*/
                            
                            
                        }
                        else
                        {
                            $this->mobileValidator = libphonenumber\PhoneNumberUtil::getInstance();
                            $num = $this->mobileValidator->parse($number, 'LB');
                            if (!is_array($this->api->result['d']))
                            {
                                $this->api->result['d']=[];
                            }
                            
                            if ($num && $this->mobileValidator->isValidNumber($num))
                            {
                                $numberType = $this->mobileValidator->getNumberType($num);
                                if ($numberType==libphonenumber\PhoneNumberType::MOBILE || $numberType==libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE)
                                {
                                    
                                    if(substr($number,0,1)=='+')
                                    {
                                        $number = substr($number,1);
                                    }
                                    
                                    /*check if number is blocked*/
                                    $prv = $this->api->db->queryResultArray('select * from bl_phone where telephone = ?', [$number], true);
                                    
                                    if ($prv !== false && isset($prv[0]['ID']) && $prv[0]['ID'])
                                    {
                                        $number=0;
                                        $keyCode=0;
                                        $this->api->result['d']['blocked']=1;
                                    }
                                    
                                    if($number)
                                    {
                                        /*check if number is suspended*/
                                        $time = MCSessionHandler::checkSuspendedMobile($number);
                                        if($time>60)
                                        {
                                            $number=0;
                                            $keyCode=0;
                                            $this->api->result['d']['suspended']=$time;
                                        }
                                    }                                 
                                    
                                    if($number)
                                    {                                    
                                        $sendSms= false;
                                        $keyCode = 0;
                                        
                                        if (($rs = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number)) !== FALSE)
                                        {
                                            if (isset($rs[Core\Model\ASD\SET_RECORD_ID]) && $rs[Core\Model\ASD\SET_RECORD_ID] && $rs[Core\Model\ASD\USER_MOBILE_VALIDATION_TYPE]==0)
                                            {
                                                $mcMobile = new MCMobile($rs);
                                                $expiredDelivery = !$mcMobile->isSMSDelivered() && (time()-$mcMobile->getRquestedUnixtime())>180 && $mcMobile->getSentSMSCount()<3;
                                                $expiredValidity = $mcMobile->getActicationUnixtime() &&  (time()-$mcMobile->getActicationUnixtime())>365*86400;
                                                $stillValid = $mcMobile->isVerified();
                                                /*
                                                error_log("sms delivered ".($mcMobile->isSMSDelivered() ? "true" : "false"));
                                                error_log("sms last ".(time()-$mcMobile->getRquestedUnixtime()));
                                                error_log("sms count ".($mcMobile->getSentSMSCount()));
                                                error_log("sms valid ".((time()-$mcMobile->getActicationUnixtime())>365*86400 ? "true" : "false"));
                                                */
                                                if ($expiredDelivery && $expiredValidity)
                                                {
                                                    if (NoSQL::getInstance()->mobileUpdate($this->api->getUID(), $number, [\Core\Model\ASD\USER_MOBILE_DATE_REQUESTED => time()]))
                                                    {
                                                        $sendSms = $rs[\Core\Model\ASD\SET_RECORD_ID];
                                                        $keyCode = $rs[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                                                    } 
                                                    else
                                                    {
                                                        $keyCode=0;
                                                        $number=0;
                                                    }
                                                }
                                                else if ($expiredValidity)
                                                {
                                                    $keyCode=mt_rand(1000, 9999);
                                                    if (NoSQL::getInstance()->mobileUpdate($this->api->getUID(), $number, [\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => $keyCode, \Core\Model\ASD\USER_MOBILE_DATE_REQUESTED => time()]))
                                                    {
                                                        $sendSms = $rs[\Core\Model\ASD\SET_RECORD_ID];
                                                        /*
                                                        $this->api->db->queryResultArray(
                                                            "UPDATE WEB_USERS_LINKED_MOBILE set "
                                                            . "code = ?, "
                                                            . "SMS_COUNT=sms_count+1,"
                                                            . "REQUEST_TIMESTAMP=current_timestamp "
                                                            . "where id = ? RETURNING ID", 
                                                            [$keyCode, $rs[Core\Model\ASD\SET_RECORD_ID]], 
                                                            TRUE);
                                                         * 
                                                         */

                                                    } 
                                                    else
                                                    {
                                                        $keyCode=0;
                                                        $number=0;
                                                    }
                                                }
                                                else if ($stillValid)
                                                {
                                                    $this->api->result['d']['check'] = true;
                                                    $number = 0;
                                                    $keyCode = 0;
                                                }
                                                else 
                                                {
                                                    $keyCode = $rs[\Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE];
                                                } 
                                            }
                                            else
                                            {
                                                $keyCode=mt_rand(1000, 9999);
                                                if (NoSQL::getInstance()->mobileInsert([
                                                    \Core\Model\ASD\USER_UID => $this->api->getUID(),
                                                    \Core\Model\ASD\USER_MOBILE_NUMBER => $number,
                                                    \Core\Model\ASD\USER_MOBILE_ACTIVATION_CODE => $keyCode,
                                                    ]))
                                                {
                                                    $record = NoSQL::getInstance()->mobileFetch($this->api->getUID(), $number);
                                                    if ($record)
                                                    {
                                                        $sendSms = $record[\Core\Model\ASD\SET_RECORD_ID];
                                                        /*
                                                        $this->api->db->queryResultArray(
                                                            "INSERT INTO WEB_USERS_LINKED_MOBILE (ID, UID, MOBILE, CODE, DELIVERED, SMS_COUNT,ACTIVATION_TIMESTAMP)
                                                            VALUES (?, ?, ?, ?, 0, 0,null) RETURNING ID",
                                                            [$sendSms, $this->api->getUID(), $number, $keyCode], TRUE);
                                                         * 
                                                         */
                                                    }
                                                    else
                                                    {
                                                        $keyCode=0;
                                                        $number=0;
                                                    }
                                                }
                                                else
                                                {
                                                    $keyCode=0;
                                                    $number=0;
                                                }
                                                
                                            }
                                        }
                                        else
                                        {
                                            $number = 0;
                                            $keyCode = 0;
                                        }
                                        
                                        
                                        if ($sendSms && $number && $keyCode)
                                        {
                                            if (!($status = MobileValidation::getInstance()->
                                                setPlatform(MobileValidation::ANDROID)->
                                                setPin($keyCode)->setUID($this->api->getUID())->
                                                sendSMS($number, "{$keyCode} is your mourjan confirmation code", ['uid'=>$this->api->getUID()])) == MobileValidation::RESULT_OK)
                                            {
                                                $keyCode=0;
                                                $number=0;
                                            }
                                            /*
                                            include_once $this->api->config['dir'].'/core/lib/MourjanNexmo.php';
                                            if (ShortMessageService::send($number, "{$keyCode} is your mourjan confirmation code", ['uid' => $this->api->getUID(), 'mid' => $sendSms, 'platform'=>'android']))
                                            {
                                                NoSQL::getInstance()->mobileIncrSMS($this->api->getUID(), $number);
                                            } 
                                            else
                                            {
                                                $keyCode=0;
                                                $number=0;
                                            }*/
                                        }
                                        
                                        if($number){               
                                            if(substr($number,0,1)!='+'){
                                                $number = '+'.$number;
                                            }
                                        }
                                        
                                        $this->api->result['d']['number']=$number;
                                        if(!isset($this->api->result['d']['check'])){
                                            $this->api->result['d']['code']=$keyCode; 
                                        }
                                    }
                                    else
                                    {
                                        $this->api->result['d']['number']=$number;
                                        if(!isset($this->api->result['d']['check'])){
                                            $this->api->result['d']['code']=$keyCode;
                                        }
                                    }
                                    
                                }
                                else
                                {
                                    $this->api->result['d']['check'] = false;
                                }
                            }
                            else
                            {
                                $this->api->result['d']['check'] = false;
                            }
                            error_log(sprintf("%s\t%s", date("Y-m-d H:i:s"), json_encode($this->api->result)).PHP_EOL, 3, "/var/log/mourjan/sms.log");
                        }
                    }
                    break;
                    
                case API_ANDROID_SIGN_UP: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = -2;
                    $appVersion = filter_input(INPUT_GET, 'apv', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    $username = urldecode(filter_input(INPUT_POST, 'user', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $newId=-2;
                    $keyCode='0';
                    if($username && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        
                        $doProceed=true;
                        $isEmail = preg_match('/@/ui', $username);
                        $isSuspended = 0;
                        if(!$isEmail){
                            $number = $username;
                            if(substr($number,0,1)=='+'){
                                $number = substr($number,1);
                            }
                            /*check if number is blocked*/
                            $prv = $this->api->db->queryResultArray(
                                    'select * from bl_phone where telephone = ?',
                                    [$number],
                                    true
                            );
                            if($prv !== false && isset($prv[0]['ID']) && $prv[0]['ID']){
                                $doProceed=false;
                            }
                            /*check if number is suspended*/
                            $time = MCSessionHandler::checkSuspendedMobile($number);
                            if($doProceed && $time>60){                                    
                                $doProceed=false;
                                $isSuspended = $time;
                            }  
                        }
                        if(!$doProceed){
                            if($appVersion){
                                if($isSuspended){
                                    $newId = -1 * $isSuspended;
                                }else{
                                    $newId = -3;
                                }
                            }else{
                                $newId = -2;
                            }
                        }
                        else
                        { 
                            $sendCode=false;
                            $date = date('Ymd');
                            $USER = new User($this->api->db, $this->api->config, null, 0);
                            $_ret = Core\Model\NoSQL::getInstance()->fetchUserByProviderId($username, \Core\Model\ASD\USER_PROVIDER_MOURJAN, $user);
                            //$user = Core\Model\NoSQL::getInstance()->fetchUserBy ProviderId($username, 'mourjan');// $USER->checkAccount($username);
                           
                            if ($_ret==NoSQL::OK)
                            {
                                $newId = $user[\Core\Model\ASD\USER_PROFILE_ID];
                                $opt = $user[Core\Model\ASD\USER_OPTIONS];

                                if(isset($opt['validating']))
                                {
                                    if(!isset($opt['validating'][$date]) || (isset($opt['validating'][$date]) && $opt['validating'][$date]<2))
                                    {
                                        $sendCode=true;
                                    }
                                }
                                else
                                {
                                    $sendCode=true;
                                } 
                                
                                if(isset($opt['accountKey']) && $opt['accountKey'])
                                {
                                    $keyCode=$opt['accountKey'];
                                }
                            }
                            else if ($_ret== NoSQL::ERR_RECORD_NOT_FOUND)
                            {
                                $user = $USER->createNewAccount($username);
                                if($user && isset($user[\Core\Model\ASD\USER_PROFILE_ID]) && $user[\Core\Model\ASD\USER_PROFILE_ID])
                                {
                                    $newId = $user[\Core\Model\ASD\USER_PROFILE_ID];
                                    //$opt = json_decode($user[0]['OPTS'], true);
                                    $opt = $user[\Core\Model\ASD\USER_OPTIONS];
                                    $sendCode=true;
                                }
                                else
                                {
                                    $newId=-2;
                                }
                            }
                            else
                            {
                                $newId=-2;
                            }
                            
                            if($sendCode)
                            {
                                $isEmail = preg_match('/@/ui', $username);
                                $sent=false;
                                if(!$keyCode){
                                    $keyCode=mt_rand(1000, 9999);
                                }
                                if($isEmail){
                                    require_once $this->api->config['dir'].'/bin/utils/MourjanMail.php';
                                    $mailer=new MourjanMail($this->api->config, $language);

                                    $sent=$mailer->sendEmailCode($username,$keyCode);
                                }else{
                                    $validator = libphonenumber\PhoneNumberUtil::getInstance();
                                    $num = $validator->parse($username, 'LB');
                                    if($num && $validator->isValidNumber($num)){
                                        $numberType = $validator->getNumberType($num);
                                        if ($numberType==libphonenumber\PhoneNumberType::MOBILE || $numberType==libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE)
                                        {
                                            include_once $this->api->config['dir'].'/core/lib/MourjanNexmo.php';
                                            $sent = ShortMessageService::send($username, "{$keyCode} is your mourjan confirmation code");                                            
                                        }
                                        else
                                        {
                                            $sent = false;
                                        }
                                    }
                                    else
                                    {
                                        $sent=false;
                                    }
                                }
                                
                                if($sent)
                                {
                                    if(!isset($opt['validating'])) $opt['validating'] = array();
                                    if(isset($opt['validating'][$date]) && is_numeric($opt['validating'][$date])){
                                        $opt['validating'][$date]++;
                                    }else{
                                        $opt['validating'][$date]=1;
                                    }
                                    $opt['accountKey']=$keyCode;
                                    $USER->updateOptions($newId,$opt);
                                }
                                else
                                {
                                    $newId=-2;
                                }
                            }
                        }
                    }
                    $this->api->result['d']['id']=$newId;
                    $this->api->result['d']['code']=$keyCode;
                    break;
                    
                case API_ANDROID_CHANGE_ACCOUNT:
                    $this->api->result['d'] = [];
                    $newId = -2;
                    $id = filter_input(INPUT_POST, 'nuid', FILTER_VALIDATE_INT) + 0;
                    $code = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT) + 0;
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    
                    if($id && $code && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))
                    {
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $_ret =  NoSQL::getInstance()->getProfileRecord([\Core\Model\ASD\USER_UID=>$id], $user);
                        if($_ret==NoSQL::OK) 
                        {
                            $opt = $user[Core\Model\ASD\USER_OPTIONS];// json_decode($user[0]['OPTS'], true);
                            if(isset($opt['accountKey']) && $opt['accountKey']==$code)
                            {
                            	//error_log("API_ANDROID_CHANGE_ACCOUNT - before");                            	
                                $USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $id);
                                //error_log("API_ANDROID_CHANGE_ACCOUNT - after");
                                $_ret =  NoSQL::getInstance()->getProfileRecord([\Core\Model\ASD\USER_UID=>$this->api->getUID()], $old);
                                if($_ret==NoSQL::OK /*isset($old[\Core\Model\ASD\USER_PROFILE_ID]) && $old[\Core\Model\ASD\USER_PROFILE_ID]*/)
                                {                                    
                                    $opts = $old[Core\Model\ASD\USER_OPTIONS];
                                    if(isset($opts['validating']))
                                    {
                                        unset($opts['validating']);
                                    }
                                    if(isset($opts['accountKey']))
                                    {
                                        unset($opts['accountKey']);
                                    }
                                    $USER->copyUserData($id, $old[Core\Model\ASD\USER_PASSWORD], $old[Core\Model\ASD\USER_RANK], $old[Core\Model\ASD\USER_LEVEL], $old[Core\Model\ASD\USER_PUBLISHER_STATUS], $opts);
                                }
                                else
                                {
                                    if(isset($opt['validating']))
                                    {
                                        unset($opt['validating']);
                                    }
                                    if(isset($opt['accountKey']))
                                    {
                                        unset($opt['accountKey']);
                                    }
                                    $USER->updateOptions($user[\Core\Model\ASD\USER_PROFILE_ID], $opt);
                                }
                                
                                if(substr($user[\Core\Model\ASD\USER_PROVIDER_ID], 0, 1)=='+')
                                {
                                    $USER->updateUserLinkedMobile($id, $user[\Core\Model\ASD\USER_PROVIDER_ID]);
                                }
                                
                                $newId=$id;
                                $this->api->result['d']['provider']=$user[Core\Model\ASD\USER_PROVIDER];
                                
                                switch ($user[Core\Model\ASD\USER_PROVIDER]) 
                                {
                                    case 'mourjan':
                                        $this->api->result['d']['account']=$user[Core\Model\ASD\USER_PROVIDER_ID];
                                        break;

                                    case 'twitter':
                                        $this->api->result['d']['account']=preg_replace('/http(?:s|)::\/\/twitter\.com\//', '', $user[Core\Model\ASD\USER_PROFILE_URL]);
                                        break;
                                    
                                    default:
                                        $this->api->result['d']['account']=$user[\Core\Model\ASD\USER_EMAIL];
                                        break;
                                }                              
                            }
                            else
                            {
                                $newId=-1;
                            }
                        }
                        else
                        {
                            $newId=-1;
                        }
                    }
                    $this->api->result['d']['id']=$newId;
                    break;

                case API_ANDROID_SET_PASSWORD: 
                    $this->api->result['d'] = [];
                    $newId = -2;
                    $id = filter_input(INPUT_POST, 'nuid', FILTER_VALIDATE_INT) + 0;
                    $code = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT) + 0;
                    $password = urldecode(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

                    if($id && $password && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))
                    {
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $user = NoSQL::getInstance()->fetchUser($id);
                        if(isset($user[\Core\Model\ASD\USER_PROFILE_ID]) && $user[\Core\Model\ASD\USER_PROFILE_ID])
                        {
                            $opt = $user[Core\Model\ASD\USER_OPTIONS];
                            
                            if(isset($opt['accountKey']) && $opt['accountKey']==$code)
                            {
                                if($USER->resetPassword($id, $password))
                                {
                                    $USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $id);
                                    $newId=$id;
                                    
                                    if(substr($user[\Core\Model\ASD\USER_PROVIDER_ID],0,1)=='+')
                                    {
                                        $USER->updateUserLinkedMobile($id, $user[\Core\Model\ASD\USER_PROVIDER_ID]);
                                    }
                                    
                                    $this->api->result['d']['provider']=$user[\Core\Model\ASD\USER_PROVIDER];
                                    if($user[\Core\Model\ASD\USER_PROVIDER]=='mourjan')
                                    {
                                        $this->api->result['d']['account']=$user[\Core\Model\ASD\USER_PROVIDER_ID];
                                    }
                                    else if($user[\Core\Model\ASD\USER_PROVIDER]=='twitter')
                                    {
                                        $this->api->result['d']['account']=preg_replace('/http(?:s|)::\/\/twitter\.com\//', '', $user[\Core\Model\ASD\USER_PROFILE_URL]);
                                    }
                                    else
                                    {
                                        $this->api->result['d']['account']=$user[\Core\Model\ASD\USER_EMAIL];
                                    }
                                }
                                else
                                {
                                    $newId=-2;
                                }
                            }
                            else
                            {
                                $newId=-1;
                            }
                        }
                        else
                        {
                            $newId=-1;
                        }
                    }
                    $this->api->result['d']['id']=$newId;
                    break;

                case API_ANDROID_SIGN_IN: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = -2;
                    $username = trim(strtolower(urldecode(filter_input(INPUT_POST, 'user', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]))));
                    $password = urldecode(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    
                    if($username && $password && base64_decode($signature)==strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))
                    {
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $newUid=$USER->authenticateUserAccount($username, $password);
                        if($newUid>0)
                        {
                            $newUid=$USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $newUid);
                            if(!$newUid)
                            {
                                $newUid=-2;
                            }
                            if ($this->api->getUUID()=='773FDB13-965C-4A5D-B7F7-83B7852FA567')
                            {
                                NoSQL::Log( NoSQL::getInstance()->deviceFetch($this->api->getUUID()));
                            }
                        }
                        $this->api->result['d']['id']=$newUid;
                    }
                    //error_log(var_export($this->api->result['d'],true));
                    break;
                    
                case API_ANDROID_SIGN_IN_GOOGLE: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = -2;
                    $provider = 'google';
                    $auth_info = json_decode(trim(urldecode(filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '']]))));
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    if(isset($auth_info->identifier) && $auth_info->identifier && base64_decode($signature)==strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))
                    {
                        $auth_info->email = strtolower($auth_info->email);
                        $auth_info->emailVerified=true;
                        $auth_info->profileURL='';
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $newId = $USER->connectDeviceToAccount($auth_info, $provider, $this->api->getUID(), $this->api->getUUID());
                        if($newId>0)
                        {
                            $this->api->result['d']['id']=$newId;
                        }
                    }
                    break;
                    
                case API_ANDROID_SIGN_OUT: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = 0;
                    $default_id = filter_input(INPUT_POST, 'did', FILTER_VALIDATE_INT) + 0;
                    //$this->api->db->setWriteMode();  
                    if($default_id > 0)
                    {
                        if (NoSQL::getInstance()->deviceSetUID($this->api->getUUID(), $default_id, $this->api->getUID()))
                        {
                            //$result = $this->api->db->queryResultArray(
                            //    "update web_users_device set uid=? where uuid= ? and (uid = ? or uid = ?) returning uid", [$default_id, $this->api->getUUID(), $default_id, $this->api->getUID()], true
                            //);
                        
                        
                            //if($result && isset($result[0]['UID']))
                            //{
                                $this->api->result['d']['id'] = $default_id; //$result[0]['UID'];
                            
                                //get total
                                $rs = $this->api->db->get(
                                    "SELECT sum(r.credit-r.debit) FROM T_TRAN r
                                    where r.UID=?", [$default_id], true);
                        
                                $this->api->result['d']['balance'] = -1;
                                if($rs && count($rs) && $rs[0]['SUM']!=null)
                                {
                                    if($rs[0]['SUM'])
                                    { 
                                        $this->api->result['d']['balance']=$rs[0]['SUM']+0;
                                    }
                                    else
                                    {
                                        $this->api->result['d']['balance']=0;                    
                                    }
                                }
                            //}
                        }
                    }
                break;
                
                case API_ANDROID_CHECK_FIX_CONNECTION_FAILURE:
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = 0;
                    if (NoSQL::getInstance()->deviceSetUID($this->api->getUUID(), $this->api->getUID()))
                    {
                        $this->api->result['d']['id'] = $this->api->getUID();
                        /*
                        $this->api->db->setWriteMode();   
                        $result = $this->api->db->queryResultArray("select uid from web_users_device where uid=? and uuid=?", [$this->api->getUID(), $this->api->getUUID()], true);
                    
                        if(empty($result))
                        {
                            $this->api->db->queryResultArray("update web_users_device set uid=? where uuid=? returning uid", [$this->api->getUID(), $this->api->getUUID()], true);
                            if($result && isset($result[0]['UID']))
                            {
                                $this->api->result['d']['id'] = $result[0]['UID'];
                            }
                        }*/
                    }
                    break;
                                        
                case API_ANDROID_PURCHASE:   
                    $proceed = false;
                    $product_id = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $transaction = filter_input(INPUT_POST, 'tran', FILTER_DEFAULT, ['options'=>['default'=>'']]);
                    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $transaction_date = date("Y-m-d H:i:s", filter_input(INPUT_POST, 'transaction_date', FILTER_VALIDATE_INT)+0);                    
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    
                    if( ( ($product_id && $transaction_date && $transaction_id) || $transaction) && base64_decode($signature)==strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))
                    {
                        
                        $transaction_signature = '';
                        $transaction_payload = '';
                        if($transaction!='')
                        {
                            $tran = json_decode($transaction, true);
                            
                            if(isset($tran['json']))
                            {
                                $tranO = json_decode($tran['json'], true);
                            }
                            
                            if(isset($tran['sig']))
                            {
                                $transaction_signature = trim($tran['sig']);
                            }
                            
                            
                            if(isset($tranO['orderId']))
                            {
                                $transaction_id = trim($tranO['orderId']);
                            }
                            if(isset($tranO['purchaseTime']))
                            {
                                $transaction_date = $tranO['purchaseTime'];
                            }
                            if(isset($tranO['developerPayload']))
                            {
                                $transaction_payload = trim($tranO['developerPayload']);
                            }
                            if(isset($tranO['productId']))
                            {
                                $product_id = trim($tranO['productId']);
                            }
                            
                            if($transaction_id && $transaction_date && $product_id && $transaction_payload)
                            {
                                
                                require_once $this->api->config['dir'].'/core/model/User.php';
                                $USER = new User(null, null, null, 0);
                                $uidKey = $USER->encodeId($this->api->getUID());
                                
                                //if (trim(base64_encode('com.mourjan.classifieds'.$uidKey.$product_id)) == $transaction_payload){
                                    
                                $public_key_base64 = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB";
                                $key =  "-----BEGIN PUBLIC KEY-----\n" .
                                        chunk_split($public_key_base64, 64, "\n") .
                                        '-----END PUBLIC KEY-----';
                                
                                $key = openssl_pkey_get_public($key);
                                    
                                $transaction_signature = base64_decode($transaction_signature);
                                    
                                $verification = openssl_verify($tran['json'], $transaction_signature, $key, OPENSSL_ALGO_SHA1);
                                $proceed=($verification===1);
                                //}
                            }
                        }
                        /*
                        if($transaction == '' && ( $product_id && $transaction_date && $transaction_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))){
                            $proceed = true;
                        }
                        */
                        
                        if($proceed)
                        {
                        
                            error_log(sprintf("Authorized %s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), $this->api->getUUID(), $this->api->getUID(), $product_id, $transaction_id, $transaction_date).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
                            //$this->api->sendSMS('9613287168', "Authorized Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            //$this->api->sendSMS('96171750413', "Authorized Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");

                            $product_rs = $this->api->db->queryResultArray("select * from product where product_id=?", [$product_id]);

                            $this->api->result['transaction_id'] = 0;
                            if (!empty($product_rs)) 
                            {
                                $this->api->db->setWriteMode();
                                $product_rs=$product_rs[0];

                                $old_transaction = $this->api->db->queryResultArray("select id from t_tran where TRANSACTION_ID=?", [$transaction_id]);
                                if($old_transaction && count($old_transaction))
                                {
                                    $this->api->result['transaction_id'] = $transaction_id;
                                }
                                else
                                {
                                    $transaction_date = date("Y-m-d H:i:s",floor($transaction_date / 1000));
                                    $server_id = intval(get_cfg_var('mourjan.server_id')) ;
                                    $coins = $this->api->db->queryResultArray(
                                        "INSERT INTO T_TRAN (UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, XREF_ID, TRANSACTION_ID, TRANSACTION_DATE, PRODUCT_ID, SERVER_ID, GATEWAY) VALUES ".
                                        "(?, current_timestamp, 'USD', ?, 0, ?, 0, ?, ?, ?, ?, 'ANDROID') RETURNING ID", 
                                        [$this->api->getUID(), $product_rs['USD_PRICE']+0.0, $product_rs['MCU']+0.0, $transaction_id, $transaction_date, $product_id, $server_id], 
                                        TRUE, PDO::FETCH_NUM);
                                    if($coins && count($coins))
                                    {
                                        $this->api->result['transaction_id'] = $transaction_id;
                                    }
                                    else
                                    {
                                        $this->api->result['e'] = "500";
                                    }
                                }
                            }
                            else
                            {
                                $this->api->result['e'] = "404";
                            }

                            //$this->api->sendSMS('9613287168', "iOS purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            $this->api->getCreditTotal();
                        
                        }
                        else
                        {                            
                            error_log(sprintf("Declined %s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), $this->api->getUUID(), $this->api->getUID(), $product_id, $transaction_id, $transaction_date).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
                            //$this->api->sendSMS('96171750413', "Declined Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            
                            $this->api->result['e'] = "501";
                        }

                        //notify devices of total update

                    }
                    else
                    {
                        $this->api->result['e'] = "401";
                    }
                    break;
                    
                case API_ANDROID_MAKE_PREMIUM:
                    $this->api->userStatus($status);
                    if ($status == 1) { 
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                        $coins = filter_input(INPUT_POST, 'coins', FILTER_VALIDATE_INT) + 0;
                        $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                        $this->api->db->setWriteMode();  
                        IF($ad_id && $coins && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
                            $result = $this->api->db->queryResultArray("
                                    select a.id, bo.id as bo_id, a.state   
                                    from ad_user a 
                                    left join t_ad_bo bo on bo.ad_id = a.id and bo.blocked = 0 
                                    where a.id = ? and a.web_user_id = ?  
                                    ",[$ad_id, $this->api->getUID()]);
                            $this->api->result['d'] = "{}";
                            if($result && count($result)){
                                if($result[0]['STATE'] == 7){
                                    $pass = true;
                                    if($result[0]['BO_ID']){
                                        $rs = $this->api->db->queryResultArray("
                                        update t_ad_bo set blocked = 1 where id = ? returning blocked 
                                        ",[$result[0]['BO_ID']],true);
                                        if($rs && count($rs)){
                                            $pass = true;
                                        }else{
                                            $pass = false;
                                            $this->api->result['e'] = "500";
                                        }
                                    }
                                    if($pass){
                                        $result = $this->api->db->queryResultArray(
                                        "INSERT INTO T_AD_BO (AD_ID, OFFER_ID, CREDIT, BLOCKED) VALUES ".
                                        "(?, ?, ?, 0) RETURNING ID", [$ad_id, 1, $coins], TRUE);

                                        if($result && count($result)){ 
                                            $result = $this->api->db->queryResultArray(
                                                    'select a.id,a.doc_id, a.content, a.state, a.section_id, a.purpose_id,a.media,DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', a.last_update) last_update, '
                                                    . 'IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) feature_end, '
                                                    . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) booking_end, '                            
                                                    . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.start_date)) booking_start '                            
                                                    . 'from ad_user a '
                                                    . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked =0 '
                                                    . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '                            
                                                    . 'where a.id = ? and a.web_user_id = ?', [$ad_id, $this->api->getUID()], true
                                            );
                                            if($result && isset($result[0]['ID'])){
                                                $content = json_decode($result[0]['CONTENT'],true);
                                                $content['id']=$result[0]['ID'];
                                                $content['user']=$this->api->getUID();
                                                $content['se']=$result[0]['SECTION_ID'];
                                                $content['pu']=$result[0]['PURPOSE_ID'];
                                                $content['state']=$result[0]['STATE'];
                                                $content['media']=$result[0]['MEDIA'];
                                                $content['sys_update']=$result[0]['LAST_UPDATE'];
                                                $content['featured']=$result[0]['FEATURE_END'];
                                                $content['booked']=$result[0]['BOOKING_END'];
                                                $content['book']=$result[0]['BOOKING_START'];
                                                if(isset($result[0]['DOC_ID']) && $result[0]['DOC_ID']){
                                                    $content['SYS_CRAWL']=1;
                                                }elseif(isset($content['SYS_CRAWL'])){
                                                    unset($content['SYS_CRAWL']);
                                                }

                                                if(isset($content['userLOC'])){
                                                    unset($content['userLOC']);
                                                }
                                                if(isset($content['ip'])){
                                                    unset($content['ip']);
                                                }
                                                if(isset($content['text'])){
                                                    unset($content['text']);
                                                }
                                                if (isset($content['attrs'])) {
                                                    unset($content['attrs']);
                                                }
                                                $this->api->result['d'] = json_encode($content);
                                            }else{
                                                $this->api->result['e'] = "500";
                                            }
                                        }else{
                                            $this->api->result['e'] = "500";
                                        }
                                    }
                                }ELSE{
                                    $this->api->result['e'] = "404";
                                }
                            }
                        }else{
                            $this->api->result['e'] = "401";
                        }
                    }
                    break;
                case API_ANDROID_CANCEL_PREMIUM:
                    $this->api->userStatus($status);
                    if ($status == 1) { 
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                        $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                        IF($ad_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        $this->api->db->setWriteMode();   
                        $result = $this->api->db->queryResultArray("
                                select id from ad_user where id = ? and web_user_id = ?  
                                ",[$ad_id, $this->api->getUID()]);
                        $this->api->result['d'] = "{}";
                        if($result && count($result)){
                            $result = $this->api->db->queryResultArray("
                                    update t_ad_bo set blocked = 1 where ad_id = ?
                                    ",[$ad_id],true);
                            
                            if($result){
                                $result = $this->api->db->queryResultArray(
                                        'select a.id, a.doc_id, a.content, a.state, a.section_id, a.purpose_id,a.media,DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', a.last_update) last_update, '
                                        . 'IIF(f.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', f.ended_date)) feature_end, '
                                        . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.end_date)) booking_end, '                            
                                        . 'IIF(bo.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', bo.start_date)) booking_start '                            
                                        . 'from ad_user a '
                                        . 'left join t_ad_bo bo on bo.ad_id=a.id and bo.blocked = 0 '
                                        . 'left join t_ad_featured f on f.ad_id=a.id and current_timestamp between f.added_date and f.ended_date '                            
                                        . 'where a.id = ? and a.web_user_id = ?', [$ad_id, $this->api->getUID()], true
                                );
                                if($result && isset($result[0]['ID'])){
                                    $content = json_decode($result[0]['CONTENT'],true);
                                    $content['id']=$result[0]['ID'];
                                    $content['user']=$this->api->getUID();
                                    $content['se']=$result[0]['SECTION_ID'];
                                    $content['pu']=$result[0]['PURPOSE_ID'];
                                    $content['state']=$result[0]['STATE'];
                                    $content['media']=$result[0]['MEDIA'];
                                    $content['sys_update']=$result[0]['LAST_UPDATE'];
                                    $content['featured']=$result[0]['FEATURE_END'];
                                    $content['booked']=$result[0]['BOOKING_END'];
                                    $content['book']=$result[0]['BOOKING_START'];
                                    if(isset($result[0]['DOC_ID']) && $result[0]['DOC_ID']){
                                        $content['SYS_CRAWL']=1;
                                    }elseif(isset($content['SYS_CRAWL'])){
                                        unset($content['SYS_CRAWL']);
                                    }

                                    if(isset($content['userLOC'])){
                                        unset($content['userLOC']);
                                    }
                                    if(isset($content['ip'])){
                                        unset($content['ip']);
                                    }
                                    if(isset($content['text'])){
                                        unset($content['text']);
                                    }
                                    if (isset($content['attrs'])) {
                                        unset($content['attrs']);
                                    }
                                    $this->api->result['d'] = json_encode($content);
                                }else{
                                        $this->api->result['e'] = "500";
                                    }
                            }else{
                                        $this->api->result['e'] = "500";
                                    }
                        }else{
                            $this->api->result['e'] = "404";
                        }
                        }else{
                            $this->api->result['e'] = "401";
                        }
                    }
                    break;
            case API_ANDROID_GET_PRODUCTS:
                $this->api->userStatus($status);
                if ($status == 1) { 
                    $app_version = filter_input(INPUT_GET, 'apv', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    if(!in_array($language, ['en','ar'])){
                        $language = 'en';
                    }
                    if(!$app_version || $app_version < '1.1.6'){
                        $this->api->result['d'] = [];
                    }else{
                        $rs = $this->api->db->queryResultArray(
                                "SELECT product_id, name_{$language} as name  
                                FROM product 
                                where iphone=0 and blocked=0 and mcu<=30 
                                order by mcu asc");
                        if($rs && count($rs)){
                            $this->api->result['d'] = [];
                            foreach($rs as $product){
                                $this->api->result['d'][] = [$product['PRODUCT_ID'], $product['NAME']];
                            }
                        }
                    }
                }
                break;
            case API_ANDROID_CLAIM_PROMO:
                $this->api->userStatus($status);
                if ($status == 1) { 
                    $promoIdk = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    $claimOk = filter_input(INPUT_GET, 'ok', FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]);
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    IF($promoIdk && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                    
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $USER = new User(null, null, null, 0);
                        $promoId = $USER->decodeId($promoIdk)+0;
                        $this->api->db->setWriteMode(); 
                        if(is_numeric($promoId) && $promoId){
                            $promo = $this->api->db->queryResultArray(
                                    "update t_promotion_users pu
                                    set pu.claimed = 1 
                                    where
                                    pu.id = ? and pu.uid = ? returning promotion_id", array($promoId, $this->api->getUID()));
                            
                            if($claimOk && $promo && count($promo)){
                                
                                $offer = $this->api->db->queryResultArray(
                                    "select coins 
                                     from t_promotion  
                                    where id = ?", array($promo[0]['PROMOTION_ID']));
                                
                                if($offer && count($offer) && $offer[0]) {
                                    $this->api->db->queryResultArray(
                                        "insert into t_tran (uid, currency_id, amount, debit, credit, usd_value) values (?,'MCU',?,0,?,0)", 
                                         array($this->api->getUID(), $offer[0]['COINS'], $offer[0]['COINS'])
                                    );
                                    
                                    $rs = $this->api->db->queryResultArray(
                                        "SELECT sum(r.credit - r.debit)
                                        FROM T_TRAN r
                                        where r.UID=?", [$this->api->getUID()], true);
                                    if($rs && count($rs) && $rs[0]['SUM']!=null){
                                        if($rs[0]['SUM']){                    
                                            $this->result['d']=$rs[0]['SUM']+0;
                                        }else{
                                            $this->result['d']=0;                    
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            case API_ANDROID_GET_PROMO:
                $this->api->userStatus($status);
                if ($status == 1) { 
                    $promoIdk = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING , ['options'=>['default'=>'']]);
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    if(!in_array($language, ['en','ar'])){
                        $language = 'en';
                    }
                    IF($promoIdk && base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $USER = new User(null, null, null, 0);
                        $promoId = $USER->decodeId($promoIdk)+0;
                        
                        if(is_numeric($promoId) && $promoId){
                        
                            $promo = $this->api->db->queryResultArray(
                                    "select p.desc_{$language},pu.uid, pu.claimed, 
                                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', pu.expiry_date) expiry_date,  
                                    DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', current_date) as sys_date 
                                    from
                                    t_promotion_users pu
                                    left join t_promotion p on p.id = pu.promotion_id
                                    where
                                    pu.id = ? and pu.uid = ?", array($promoId, $this->api->getUID()));
                                    
                                    
                            if($promo && count($promo)){
                                $promo = $promo[0];
                                $this->api->result['d'] = [];
                                if($promo['CLAIMED']==0){
                                    if($promo['EXPIRY_DATE'] > $promo['SYS_DATE']){
                                        $this->api->result['d'] = [$promoIdk, $promo['UID'], $promo['DESC_'.  strtoupper($language)]];
                                    }else{
                                        $this->api->result['d'] = [0, $promo['UID'], ($language == 'en'? 'Sorry this offer has expired':'عذراً هذا العرض منتهي الصلاحية')];
                                    }
                                }else{
                                    $this->api->result['d'] = [0, $promo['UID'], ($language == 'en'? 'This offer is already claimed':'لقد حصلت على هذا العرض مسبقاً')];
                                }
                            }
                        }
                    }
                }
                break;
            case API_ANDROID_STATEMENT:
                $this->api->userStatus($status);
                    if ($status == 1) { 
                        $offset = filter_input(INPUT_POST, 'offset', FILTER_VALIDATE_INT) + 0;
                        $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                        $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                        if(!in_array($language, ['en','ar'])){
                            $language = 'en';
                        }
                        IF(base64_decode($signature) == strtoupper(hash_hmac('sha1', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                            $rs = $this->api->db->queryResultArray(
                            "SELECT count(*) as total 
                            FROM T_TRAN r
                            where r.UID=?", [$this->api->getUID()]);
                            $total = 0;
                            if($rs && count($rs)){
                                $total = $rs[0]['TOTAL'];
                                $this->api->result['t'] = $total;

                                $rs = $this->api->db->queryResultArray(
                                "SELECT first 20 skip {$offset} r.ID, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', r.dated) DATED, r.AMOUNT, r.DEBIT, r.CREDIT,
                                r.USD_VALUE, p.NAME_{$language} as offer_name,
                                m.name_{$language} as product_name, 
                                s.name_{$language} as section_name,
                                pu.name_{$language} as purpose_name,
                                a.content, a.state, o.offer_id  
                                FROM T_TRAN r
                                left join T_AD_FEATURED f on f.ID=r.XREF_ID and r.CURRENCY_ID='MCU' and r.DEBIT>0 and r.PRODUCT_ID='' 
                                left join T_AD_BO o on o.ID=f.BO_ID
                                left join T_OFFER p on p.ID=o.OFFER_ID
                                left join product m on m.product_id = r.product_id
                                left join ad_user a on a.id = o.ad_id
                                left join section s on a.section_id = s.id 
                                left join purpose pu on pu.id = a.purpose_id
                                where r.UID=?
                                order by r.ID desc", [$this->api->getUID()]);
                                
                                $balance=0;
                                
                                $balance = $this->api->db->queryResultArray(
                                "SELECT sum(credit - debit) as balance "
                                . "from t_tran where uid = ? and id <= ? ", [$this->api->getUID(), $rs[0]['ID']]);
                                $count=count($rs);
                                $newRs = [];
                                if($balance && isset($balance[0]['BALANCE'])){
                                    $balance = $balance[0]['BALANCE'] == null ? 0 : $balance[0]['BALANCE']+0;
                                    for ($i=0; $i<$count; $i++) {

                                        $rs[$i]['CREDIT'] = $rs[$i]['CREDIT']+0;
                                        $rs[$i]['DEBIT'] = $rs[$i]['DEBIT']+0;
                                        $newRs[$i] = [];
                                        $newRs[$i][] = $rs[$i]['ID'];
                                        $newRs[$i][] = $rs[$i]['DATED'];
                                        $newRs[$i][] = $rs[$i]['CREDIT']+0;
                                        $newRs[$i][] = $rs[$i]['DEBIT']+0;
                                        $newRs[$i][] = $balance;
                                        
                                        $balance = $balance+$rs[$i]['DEBIT']-$rs[$i]['CREDIT'];

                                        $label = '';
                                        if($rs[$i]['CREDIT'] > 0){
                                            if(!$rs[$i]['USD_VALUE'] || !$rs[$i]['PRODUCT_NAME']){
                                                $label = $rs[$i]['PRODUCT_NAME'];
                                                if($language == 'en'){
                                                    $label = $rs[$i]['CREDIT'].' free gold';
                                                }else{
                                                    if($rs[$i]['CREDIT'] == 1){
                                                        $label = 'ذهبية واحدة';
                                                    }else if($rs[$i]['CREDIT'] == 2){
                                                        $label = 'ذهبيتان';
                                                    }else if ($rs[$i]['CREDIT'] >2 && $rs[$i]['CREDIT'] < 11){
                                                        $label = $rs[$i]['CREDIT'].' ذهبيات';
                                                    }else{
                                                        $label = $rs[$i]['CREDIT'].' ذهبية';
                                                    }
                                                    $label .= ' مجانية';
                                                }
                                            }else{
                                                $label = $rs[$i]['PRODUCT_NAME'];
                                            }
                                        }else{
                                            if($rs[$i]['OFFER_ID'] == 1){
                                                if($language == 'ar'){
                                                    $label = 'تمييز لمدة يوم';
                                                }else{
                                                    $label = '1 day Premium';
                                                }
                                            }else{
                                                $label = $rs[$i]['OFFER_NAME'];
                                            }
                                        }

                                        $newRs[$i][] = $label;

                                        $label = '';
                                        if($rs[$i]['DEBIT'] > 0){
                                            if(stristr($rs[$i]['SECTION_NAME'], $rs[$i]['PURPOSE_NAME'])){
                                                $label = $rs[$i]['SECTION_NAME'];
                                            }else{
                                                $label = $rs[$i]['SECTION_NAME'].' | '.$rs[$i]['PURPOSE_NAME'];
                                            }

                                            $newRs[$i][] = $label;
                                            $rtl = 0;
                                            if($rs[$i]['CONTENT']){
                                                $content = json_decode($rs[$i]['CONTENT'], true);
                                                if(isset($content['other'])){
                                                    if($language == 'ar'){
                                                        $newRs[$i][] = $content['other'];
                                                        $rtl = $content['rtl'];
                                                    }else{
                                                        if(isset($content['altother']) && $content['altother']!=''){
                                                            $newRs[$i][] = $content['altother'];
                                                            $rtl = $content['altRtl'];
                                                        }else{
                                                            $newRs[$i][] = $content['other'];
                                                            $rtl = $content['rtl'];
                                                        }
                                                    }
                                                }else{
                                                    $newRs[$i][] = 'NA';
                                                }
                                            }else{
                                                $newRs[$i][] = '';
                                            }
                                            $newRs[$i][] = $rtl;

                                            $newRs[$i][] = $rs[$i]['STATE'];
                                        }else{
                                            $newRs[$i][] = '';
                                            $newRs[$i][] = '';
                                            $newRs[$i][] = 0;
                                            $newRs[$i][] = 0;
                                        }
                                    }
                                }
                                $this->api->result['d'] = json_encode($newRs);
                            }
                        }
                    }
                break;
            default:
                break;
        }
        }
    }
    
    function detectIfAdInPending($adId,$sectionId, $contactInfo=array()){
        $active_ads = 0;
        if(count($contactInfo) && $this->api->getUID()){
            $q='select a.id from ad_user a where (a.id <> '.$adId.' and a.section_id ='.$sectionId.' and a.state in (1,2)) and ( ';
            $params=array();
            $pass = 0;
            if(isset($contactInfo['p']) && count($contactInfo['p'])){
                $q .= "a.content similar to '";
                foreach($contactInfo['p'] as $number){
                    if(isset($number['v']) && trim($number['v'])!=''){
                        //if($pass) $q.= ' or ';
                        if($pass) $q.= '|';
                        //$q .= 'a.content containing ?';                        
                        $q .= '%'.preg_replace('/\+/', '' ,$number['v']).'%';
                        //$params[]=$number['v'];
                        $pass++;
                    }
                }
                $q .= "'";
            }
            $q.=')';
            
            if($pass){
                $active_ads = $this->api->db->queryResultArray($q, $params);
                if($active_ads && isset($active_ads[0]['ID']) && $active_ads[0]['ID']){
                    $active_ads = count($active_ads);
                }
            }
        }
        return $active_ads;
    }
    
    
    function detectDuplicateSuspension($contactInfo=array(), $isMobileVerified=0)
    {
        $state = 0;        
        if(!$isMobileVerified)
        {
            if(count($contactInfo) && $this->api->getUID())
            {
                $q='select distinct u.id, u.lvl from ad_attribute t
                left join ad_user a on a.id = t.ad_id
                left join web_users u on u.id = a.web_user_id
                where
                a.id is not null and
                a.id <> '.$this->api->getUID().' and ( ';
                $params=array();
                $pass = 0;
                if(isset($contactInfo['p']) && count($contactInfo['p']))
                {
                    foreach($contactInfo['p'] as $number){
                        if(isset($number['v']) && trim($number['v'])!=''){
                            if($pass) $q.= ' or ';
                            $q .= '(t.attr_id = 1 and t.attr_value = ?)';
                            $params[]=$number['v'];
                            $pass++;
                        }
                    }
                }
                if(isset($contactInfo['e']) && trim($contactInfo['e'])!=''){
                    if($pass) $q.= ' or ';
                    $q .= '(t.attr_id = 2 and t.attr_value = ?)';
                    $params[]=$contactInfo['e'];
                    $pass++;
                }
                $q.=')';

                if(count($params))
                {
                    $users = $this->api->db->queryResultArray($q, $params);
                    $time = $current_time = time();
                    $blockAccount = false;
                
                    if($users && count($users))
                    {
                        foreach($users as $user)
                        {
                            if($user['LVL']==5)
                            {
                                $blockAccount=true;
                                break;
                            }
//                            elseif($user['OPTS'])
//                            {
//                                $options = json_decode($user['OPTS'],true);
//                                if(isset($options['suspend']) && $options['suspend'] > $time){
//                                    $time = $options['suspend'];
//                                }
//                            }
                        }
                    }
                    
                    if($blockAccount)
                    {
                        $state = 5;
                        $this->setLevel($this->api->getUID(),5);
                    }
                    elseif($time != $current_time)
                    {
                        $state = 1;
                        //$this->suspendUser($this->api->getUID(),$time);
                    }
                }
            }
        }
        return $state;
    }
    
    
    function referrToSuperAdmin($id)
    {
        $result=false;
        $res=$this->api->db->get('update ad_object set super_admin=1 where id=?', [$id], true);
        if ($res!==false) 
        {
            $result=true;
        }
        return $result;
    }    
    
    
    function block($uid, $number, $msg)
    {
        if (Core\Model\NoSQL::getInstance()->blacklistInsert($number, $msg, $uid))
        {
            $q = 'update or insert into bl_phone (telephone, subject, web_user_id) values (?, ?, ?) matching(telephone) returning id';
            $this->db->get($q, [$number, $msg, $uid]);        
            return 1;
        }
        return 0;       
    }
    
    
    function setLevel($id, $level)
    {
        $succeed=false;
        if($id && is_numeric($level)) 
        {
            if (\Core\Model\NoSQL::getInstance()->setUserLevel($id, $level))
            {
                $q="update web_users set lvl=? where id=?";
                $this->db->get($q, [$level, $id], true);
                $succeed=true;
            }
        }
        return $succeed;
    }
    
    /*
    function suspendUser($id, $time)
    {
        $succeed=false;
        
        $options = $this->api->db->queryResultArray("select opts from web_users where id = ?", array($id));
        if($options && count($options)){
            $options = json_decode($options[0]['OPTS'],true);
            $options['suspend']=$time;
            $options = json_encode($options);
            $q=$this->api->db->prepareQuery("update web_users set opts=:options where id=:id");
            $q->bindParam(':options', $options, PDO::PARAM_LOB);
            $q->bindParam(':id', $id, PDO::PARAM_INT);
            if ($this->api->db->executeStatement($q)) {
                $succeed=true;
            }
            unset($q);
        }
        return $succeed;
    }
    */
    
    function zammad($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0) : int 
    {
        $client = new Client([
            'url'           => 'http://ws.mourjan.com', // URL to your Zammad installation
            'username'      => 'admin@berysoft.com',  // Username to use for authentication
            'password'      => 'GQ71BUT2',           // Password to use for authentication
            'debug'         => false,                // Enables debug output
        ]);      
        
        $users = $client->resource( ResourceType::USER )->search($fromEmail);
        if ( !is_array($users) ) 
        {
            if ( $users->hasError() ) 
            {
                error_log( $users->getError() );                
            }
            return 0;
        }
        else
        {
            //error_log( 'Found ' . count($users) . ' user(s) with email address ' . $fromEmail );
            if ($users)
            {
                $user = $users[0];
            }
            else
            {
                $name = trim($fromName);
                $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
                $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
                $user_data = [
                    'login' => $fromEmail,
                    'email' => $fromEmail,
                    'firstname' => $first_name,
                    'lastname' => $last_name                
                ];
                
                $user = $client->resource( ResourceType::USER );
                $user->setValues($user_data);
                $user->save();
                if ( $user->hasError() ) 
                {
                    error_log( $user->getError() );
                    return 0;
                }                        
            }
        }        
        
        $ticket_data = [
            'group_id'    => 1,
            'priority_id' => 2,
            'state_id'    => 1,
            'title'       => $subject,
            'customer_id' => $user->getID(),
            'article'     => [
                'content_type' => 'text/html',
                'subject' => $subject,
                'body'    => $message,
            ],
        ];
        $ticket = $client->resource( ResourceType::TICKET );
        $ticket->setValues($ticket_data);
        $ticket->save();
        
        if ( $ticket->hasError() ) 
        {
            error_log( $ticket->getError() );
            return 0;
        }                
                
        return 1;                
    }
    
    
    function faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account='', $reference=0)
    {
        $res = 0;
        $key='mEI5PRfHaBvbn6El48yZcX492NLb5Cu5';
        $url = 'http://io.mourjan.com:8080/api/v1/authenticate';
     
        $myvars = 'username=rlakis@berysoft.com&password=GQ71BUT2&api_key='.$key;
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $auth = json_decode(curl_exec( $ch ));
        
        if(isset($auth->user_id) && $auth->user_id){
            $url = 'http://io.mourjan.com:8080/api/v1/helpdesk/create?user_id='.$auth->user_id.'&token='.$auth->token;
            $myvars = array();
            $myvars['api_key'] = $key;
            $myvars['user_id'] = $auth->user_id;
            $myvars['token'] = $auth->token;
            $myvars['subject'] = $subject;
            $myvars['body'] = $message;
            $myvars['helptopic']=1;
            $myvars['email']=$fromEmail;
            $myvars['sla']=1;
            $myvars['priority']=2;
            $myvars['code']='0';
            $myvars['mobile']=null;//'9611487521';
            $myvars['phone']='';

            $name = preg_split('/\s+/', trim($fromName), -1, PREG_SPLIT_NO_EMPTY);
            $myvars['first_name']= $name[0];
            $myvars['last_name']='';
            for($i=1; $i<count($name); $i++)
            {
                $myvars['last_name'].=$name[$i]." ";
            }
            $myvars['last_name']= trim($myvars['last_name']);

            if ($fromName=='Abusive Report' && $reference>0)
            {
                $myvars['subject'].= " - {$reference}";
            }

            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-XSRF-TOKEN: '.$auth->token));
            curl_setopt( $ch, CURLOPT_HEADER, 1);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec( $ch );
            curl_close($ch);

            if(preg_match('/HTTP\/.* ([0-9]+) .*/', $response, $status)) 
            {
                if ($status[1]==200)
                {
                    $res= 1;
                }
            }
        }
        
        return $res;
        
    }

    
    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account = '',$reference=0, $helpTopic=1) 
    {
        //return $this->faveo($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account, $reference, $helpTopic);
        return $this->zammad($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account, $reference, $helpTopic);
        /*
        
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $res = 0;
        try {
            $mail->Host = $this->api->config['smtp_server'];
            $mail->SMTPAuth = true;
            $mail->Port = $this->api->config['smtp_port'];
            if ($sender_account) {
                $mail->Username = $sender_account;
            } else {
                $mail->Username = $this->api->config['smtp_user'];
            }
            $mail->Password = $this->api->config['smtp_pass'];
            $mail->SMTPSecure = 'ssl';
            $mail->SetFrom($fromEmail, $fromName);
            if (is_array($toEmail)) {
                foreach ($toEmail as $email) {
                    $mail->AddAddress($email, '');
                }
            } else
                $mail->AddAddress($toEmail, $toName);
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $message;
            if ($mail->Send())
                $res = 1;
        } catch (phpmailerException $e) {
            $res = 0;
            error_log($mail->ErrorInfo);
        } catch (Exception $e) {
            $res = 0;
            error_log($mail->ErrorInfo);
        }
        $mail->ClearAddresses();
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
        return $res;*/
    }
    
    
    function isRTL($text)
    {
        $rtlChars = preg_replace('/[^\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        $ltrChars = preg_replace('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]|[:\\\\\/\-;.,؛،?!؟*@#$%^&_+\'"|0-9\s]/u', '', $text);
        if(strlen($rtlChars) > (strlen($ltrChars)*0.5)){
            return true;
        }else{
            return false;
        }
        /*if(preg_match('/[\x{0621}-\x{064a}\x{0750}-\x{077f}]/u', $text)){
            return true;
        }*/
        return false;
    }

}
