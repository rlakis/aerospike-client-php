<?php

require_once 'vendor/autoload.php';
require_once 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
use MaxMind\Db\Reader;

class AndroidApi {

    private $api;

    function __construct($_api) {
        global $appVersion;
        $this->api = $_api;
        
        if($this->api->config['active_maintenance']){
            $this->api->result['e']="active maintenance";
        }else{
        
        define ('MOURJAN_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB');
        
        switch ($this->api->command) {
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
                $this->api->userStatus($status);
                //error_log($status);
                if ($status == 1) {
                    //sync favorites
                    $this->api->search(true);
                    if(count($this->api->result['d'])){
                        $results = $this->api->result['d'];
                        $this->api->result['d'] = [];
                        $this->api->result['d']['favs'] = $results;
                        $results = null;
                        unset($this->api->result['total']);
                    }elseif(!$this->api->result['e']){
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
                            $content['booked']=$result['BOOKING_END'];
                            $content['book']=$result['BOOKING_START'];
                            $content['featured']=$result['FEATURE_END'];
                            if(isset($result['DOC_ID']) && $result['DOC_ID']){
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
                }
                break;
            case API_ANDROID_FLAG_AD:
                $this->api->userStatus($status);
                if ($status == 1) {
                    $id = filter_input(INPUT_GET, 'adid', FILTER_VALIDATE_INT) + 0;
                    $ad = $this->api->getClassified($id);
                    if($ad && !in_array($ad[12], array(190,1179,540))){
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
                        $msg = "<table>
                            <tr><td><b>UID</b>:</td><td>{$this->api->getUID()}</td></tr>
                            <tr><td><b>Ad</b>:</td><td><a href='https://www.mourjan.com/{$id}'>{$id}</a></td></tr>
                            <tr><td><b>Mobile</b>:</td><td>Android</td></tr>
                            <tr><td><b>Sender</b>:</td><td>AndroidApi-{$appVersion}</td></tr>
                            </table>";

                        $res = $this->sendMail("Mourjan Admin", $this->api->config['admin_email'], 'Mourjan Android', $this->api->config['smtp_user'], $subject, $msg, $this->api->config['smtp_contact']);
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
                    $uri = preg_replace('/\/en(?:\/|$)/', '', $uri);
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
                if ($status == 1) {
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
                        if ($checkAdExists->execute(array($id))!== false){
                            $oldAd = $checkAdExists->fetch(PDO::FETCH_NUM);
                            if(isset($oldAd[0]) && $oldAd[0]){
                                $found = true;
                            }
                        }
                        if($found){
                            $res = $stmt->execute([$this->api->getUID(), $id, ($set ? 0 : 1)]);
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
                          //  $this->api->result['d'][] = $id;
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
                        $res = $stmt->execute([$this->api->getUID(), $req['country_id'], $req['city_id'], $req['section_id'], $req['tag_id'], $req['geo_id'], $req['purpose_id'], $query, $label, $publisherType]);
                        if ($res) {
                            $this->api->result['d'][] = $req['id'];
                        }
                    }
                    $stmt->closeCursor();
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
                        $res = $stmt->execute([$this->api->getUID(), $req['country_id'], $req['city_id'], $req['section_id'], $req['tag_id'], $req['geo_id'], $req['purpose_id'], $query, $publisherType]);
                        if ($res) {
                            $this->api->result['d'][] = $req['id'];
                        }
                    }
                    $stmt->closeCursor();
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
                if($this->api->config['active_maintenance']){
                    $this->api->result['e'] = "503";
                    break;
                }            
                $opts = $this->api->userStatus($status);
                if ($status == 1 && (!isset($opts->suspend) || $opts->suspend <= time() )) {
                    $this->api->db->setWriteMode();
                    $direct_publish = filter_input(INPUT_POST, 'pub', FILTER_VALIDATE_INT) + 0;
                    
                    $rid = filter_input(INPUT_POST, 'rid', FILTER_VALIDATE_INT) + 0;
                    $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    $state = 0;
                    $ad = json_decode(urldecode(filter_input(INPUT_POST, 'ad', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']])),true);
                    
                    $userState = 0;
                    
                    $hasFailure = 0;
                    $hasFailureMsg = 0;
                    
                    $stmt = null;
                    if(count($ad) > 0){    
                        
                        if($ad['se']>0 && $ad['pu']==0){
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

                        include_once $this->api->config['dir'] . '/core/lib/MCSaveHandler.php';                
                        $normalizer = new MCSaveHandler($this->api->config);
                        $normalized = $normalizer->getFromContentObject($ad);
                        $attrs = [];
                        if ($normalized)
                        {
                            $ad = $normalized;
                            $attrs = $normalized['attrs'];
                        }                

                        $ad['rtl'] = ($this->isRTL($ad['other'])) ? 1 : 0;
                        
                        if(isset($ad['altother']) && $ad['altother']){

                            $ad['altRtl'] = ($this->isRTL($ad['altother'])) ? 1 : 0;

                            if($ad['rtl'] == $ad['altRtl']){
                                $ad['extra']['t']=2;
                                unset($ad['altRtl']);
                                unset($ad['altother']);
                            }

                            if(isset($ad['altRtl']) && $ad['altRtl']){
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
                        if($geo) {
                            $ad['userLOC'] = isset($geo['city']['names']['en']) && $geo['city']['names']['en'] ? $geo['city']['names']['en'].', ' : '';
                            $ad['userLOC'].=$geo['country']['iso_code'];
                            $ad['userLOC'].=': '. implode(" ,",$geo['location']);                            
                            
                            if(!in_array($geo['country']['iso_code'],[
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
                                'TR'
                                ])){
                                $requireReview = 1;
                            }
                            
                        } else $ad['userLOC']=0;
                            
                        $city_id = 0;
                        $country_id = 0;
                        $currentCid = 0;
                        $isMultiCountry = false;
                        $cities = $this->api->db->getCitiesDictionary();
                        foreach($ad['pubTo'] as $key => $val){
                            if(!$city_id && isset($cities[$city_id])){
                                $city_id=$key;
                            }
                            if($key && isset($cities[$key])){
                                if($currentCid && $currentCid != $cities[$key][4]){
                                    $isMultiCountry = true;
                                }
                                $currentCid = $cities[$key][4];
                            }
                        }
                        foreach($ad['pubTo'] as $key => $val){
                            $city_id=$key;
                            break;
                        }
                        if($city_id){
                            $country_id=$cities[$city_id][4];
                        }
                        
                        
                        $isSCAM = 0;
                        if(isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0){
                            $blockedEmailPatterns = addcslashes(implode('|', $this->api->config['restricted_email_domains']),'.');
                            $isSCAM = preg_match('/'.$blockedEmailPatterns.'/ui', $ad['cui']['e']);
                        }   
                        if(!$isSCAM && !$requireReview && isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0){
                            $requireReview = preg_match('/\+.*@/', $ad['cui']['e']);
                            if(!$requireReview){
                                $requireReview = preg_match('/hotel/', $ad['cui']['e']);
                            }
                            if(!$requireReview){
                                $requireReview = preg_match('/\..*\..*@/', $ad['cui']['e']);
                            }
                        }
                        
                        
                        if($ad['se']==0 || count($ad['pubTo'])==0){
                            $hasFailure=1;
                            if($ad['rtl']){
                                $msg = 'يرجى تعديل الاعلان وادخال التفاصيل الناقصة';
                            }else{
                                $msg = 'please edit ad and complete missing details';
                            }
                            $ad['msg'] = $msg;
                        }
                        
                        if(isset($ad['SYS_CRAWL']) && $ad['SYS_CRAWL']){
                            $hasFailure=1;
                            if($ad['rtl']){
                                $msg = 'يرجى استخدام PropSpace لتعديل هذا الاعلان';
                            }else{
                                $msg = 'please use PropSpace to edit this ad';
                            }
                            $ad['msg'] = $msg;
                        }
                        
                        if ($isSCAM){
                            
                            $this->setLevel($this->api->getUID(),5);
                            
                        }elseif($requireReview){
                            $this->referrToSuperAdmin($adId);
                        }elseif($hasFailure){
                            $ad_id = 0;
                            $state = 3;
                        }else{
                        
                            if($ad_id > 0) {     
                                if($ad['state'] == 1 && isset($ad['budget']) && $ad['budget']+0 > 0){
                                    $ad['state'] = 4;
                                }
                                $state = $ad['state'];

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

                                $q='update ad_user set
                                    content=?,purpose_id=?,section_id=?,rtl=?,
                                    country_id=?,city_id=?,latitude=?,longitude=?,state=?,media=?,date_added=current_timestamp 
                                    where id=? and web_user_id+0=? 
                                    returning state, id';
                                    $stmt = $this->api->db->getInstance()->prepare($q);

                                $result=null;
                                if ($stmt->execute([
                                        $encodedAd,
                                        $ad['pu'],
                                        $ad['se'],
                                        $ad['rtl'],
                                        $country_id,
                                        $city_id,
                                        $ad['lat'],
                                        $ad['lon'],
                                        $ad['state'],
                                        $ad['media'],
                                        $ad_id,
                                        $this->api->getUID()
                                    ])) 
                                {
                                    $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                }
                                    
                                    if (!empty($result)) {
                                        
                                        $state=(int)$result[0]['STATE'];
                                        $ad_id = (int)$result[0]['ID'];
                                        
                                        $st = $this->api->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                                        $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                                        $st->bindValue(2, preg_replace('/\s+/', ' ', json_encode($attrs, JSON_UNESCAPED_UNICODE)), PDO::PARAM_STR);
                                        $st->execute();
                                        
                                    }                                

                                    if( $ad['state']==1 ) {
                                        $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                                    }
                            }else {
                                $state = 0;
                                if($direct_publish){
                                    $state = 1;
                                }

                                if($state == 1 && isset($ad['budget']) && $ad['budget']+0 > 0){
                                    $state = 4;
                                }

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
                                    $st->execute();
                                }
                                
                                if( $state==1 ) {
                                    $userState = $this->detectDuplicateSuspension($ad['cui']);                            
                                }
                            }
                            if($ad_id && $state == 4 && $isMultiCountry){
                                $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);

                                if($ad['rtl']){
                                    $msg = 'عذراً ولكن لا يمكن تمييز الاعلان في اكثر من بلد واحد';
                                }else{
                                    $msg = 'Sorry, you cannot publish premium ads targetting more than ONE country';
                                }
                                $ad['msg'] = $msg;

                                $encodedAd = json_encode($ad);
                                $result=null;
                                $suspendStmt->execute([
                                    $encodedAd,
                                    3,
                                    $ad_id
                                ]);
                                unset($suspendStmt);
                            }elseif($ad_id && $userState == 1){
                                $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);

                                if($ad['rtl']){
                                    $msg = 'لقد تم ايقاف حسابك بشكل مؤقت نظراً للتكرار';
                                }else{
                                    $msg = 'your account is suspended due to repetition';
                                }
                                $ad['msg'] = $msg;

                                $encodedAd = json_encode($ad);
                                $result=null;
                                $suspendStmt->execute([
                                    $encodedAd,
                                    3,
                                    $ad_id
                                ]);
                                unset($suspendStmt);
                            }else if($ad_id && in_array($ad['se'],array(190,1179,540,1114))){
                                $dupliactePending = $this->detectIfAdInPending($ad_id, $ad['se'], $ad['cui']);
                                if($dupliactePending){
                                    $q='update ad_user set
                                    content=?,state=? 
                                    where id=?';
                                    $suspendStmt = $this->api->db->getInstance()->prepare($q);
                                    if($ad['rtl']){
                                        $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                                    }else{
                                        $msg = 'There is another similar ad pending Editors\' approval';
                                    }
                                    $ad['msg'] = $msg;

                                    $encodedAd = json_encode($ad);
                                    $result=null;
                                    $suspendStmt->execute([
                                        $encodedAd,
                                        3,
                                        $ad_id
                                    ]);
                                    $suspendStmt->closeCursor();
                                }
                            }
                        }
                    }
                    
                    $this->api->result['d'] = [];
                    
                    $this->api->result['d']['id'] = $rid;
                    $this->api->result['d']['adid'] = $ad_id;
                    $this->api->result['d']['state'] = $state;
                    
                    unset($stmt);
                    
                   
                }
                break;
                case API_ANDROID_GET_AD:                
                $this->api->userStatus($status);
                if ($status == 1) {                  
                    $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    
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
                    $id = filter_input(INPUT_POST, 'mid', FILTER_VALIDATE_INT) + 0; 
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
                        IF($ad_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
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
                    if ($status == 1 && (!isset($opts->suspend) || $opts->suspend <= time() )) 
                    {
                        $this->api->db->setWriteMode();   
                        $ad_id = filter_input(INPUT_POST, 'adid', FILTER_VALIDATE_INT) + 0;
                    
                        $ad = $this->api->db->queryResultArray(
                            'select a.id, a.content, a.state, a.section_id, a.purpose_id ' .
                            'from ad_user a ' .
                            'where a.id=? and a.web_user_id=?', [$ad_id, $this->api->getUID()], true
                        );
                    
                        $renew= true;
                    
                        if($ad && isset($ad[0]['ID']) && $ad[0]['ID'])
                        {
                            $ad = $ad[0];
                            $content = json_decode($ad['CONTENT'], true);
                            
                            if(isset($content['SYS_CRAWL']) && $content['SYS_CRAWL']){
                                $this->api->result['d']['renew'] = 0;
                            }else{
                                if(in_array($ad['SECTION_ID'],array(190,1179,540,1114)))
                                {
                                    $dupliactePending = $this->detectIfAdInPending($ad_id, $ad['SECTION_ID'], $content['cui']);
                                    if($dupliactePending)
                                    {
                                        $renew= false;
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
                                        $suspendStmt->execute([$encodedAd, 3, $ad_id]);
                                        $suspendStmt->closeCursor();
                                    }
                                }

                                if($renew)
                                {
                                    include_once $this->api->config['dir'] . '/core/lib/MCSaveHandler.php';                
                                    $normalizer = new MCSaveHandler($this->api->config);
                                    if (isset($content['attrs'])) {
                                        unset($content['attrs']);
                                    }
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
                                        "update ad_user a set a.section_id=?, a.purpose_id=?, a.content=?, a.state=1 where a.id=? and a.web_user_id=? and a.state=9 returning id", 
                                        [$ad['SECTION_ID'], $ad['PURPOSE_ID'], json_encode($ad['CONTENT']), $ad_id, $this->api->getUID()], true);


                                    if (!empty($result)) 
                                    { 
                                        $st = $this->api->db->getInstance()->prepare("update or insert into ad_object (id, attributes) values (?, ?)");
                                        $st->bindValue(1, $ad_id, PDO::PARAM_INT);
                                        $st->bindValue(2, json_encode($attrs, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
                                        $st->execute();                                        
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
                case API_ANDROID_SIGN_UP: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = -2;
                    $username = urldecode(filter_input(INPUT_POST, 'user', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $language = filter_input(INPUT_GET, 'hl', FILTER_SANITIZE_STRING , ['options'=>['default'=>'en']]);
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $newId=-2;
                    $keyCode='0';
                    if($username && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $user=$USER->checkAccount($username);
                        $sendCode=false;
                        $date = date('Ymd');
                        if(isset($user[0]['ID']) && $user[0]['ID']){
                            $newId = $user[0]['ID'];
                            $opt = json_decode($user[0]['OPTS'], true);
                            
                            if(isset($opt['validating'])){
                                if(!isset($opt['validating'][$date]) || (isset($opt['validating'][$date]) && $opt['validating'][$date]<2)){
                                    $sendCode=true;
                                }
                            }else{
                                $sendCode=true;
                            } 
                            if(isset($opt['accountKey']) && $opt['accountKey']){
                                $keyCode=$opt['accountKey'];
                            }
                        }else{             
                            $user = $USER->createNewAccount($username);
                            if(isset($user[0]['ID']) && $user[0]['ID']){
                                $newId = $user[0]['ID'];
                                $opt = json_decode($user[0]['OPTS'], true);
                                $sendCode=true;
                            }else{
                                $newId=-2;
                            }
                        }
                        if($sendCode){
                            $isEmail = preg_match('/@/', $username);
                            $sent=false;
                            if(!$keyCode){
                                $keyCode=mt_rand(1000, 9999);
                            }
                            if($isEmail){
                                require_once $this->api->config['dir'].'/bin/utils/MourjanMail.php';
                                $mailer=new MourjanMail($this->api->config, $language);
                                
                                $sent=$mailer->sendEmailCode($username,$keyCode);
                            }else{
                                include_once $this->api->config['dir'].'/core/lib/nexmo/NexmoMessage.php';
                                $sms = new NexmoMessage($this->api->config['nexmo_key'], $this->api->config['nexmo_secret']);
                                $sent = $sms->sendText($username, 'mourjan',
                                $keyCode." is your mourjan confirmation code",
                                $newId);
                            }
                            if($sent){
                                if(!isset($opt['validating'])) $opt['validating'] = array();
                                if(isset($opt['validating'][$date]) && is_numeric($opt['validating'][$date])){
                                    $opt['validating'][$date]++;
                                }else{
                                    $opt['validating'][$date]=1;
                                }
                                $opt['accountKey']=$keyCode;
                                $USER->updateOptions($newId,$opt);
                            }else{
                                $newId=-2;
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
                    if($id && $code && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $user=$USER->getAccount($id);
                        if(isset($user[0]['ID']) && $user[0]['ID']){
                            $opt = json_decode($user[0]['OPTS'], true);
                            if(isset($opt['accountKey']) && $opt['accountKey']==$code){
                            	error_log("API_ANDROID_CHANGE_ACCOUNT - before");
                            	
                                $USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $id);
                                error_log("API_ANDROID_CHANGE_ACCOUNT - after");
                                $old=$USER->getAccount($this->api->getUID());
                                if(isset($old[0]['ID']) && $old[0]['ID']){
                                    $old=$old[0];
                                    $USER->copyUserData($id, $old['USER_PASS'], $old['USER_RANK'], $old['LVL'], $old['USER_PUBLISHER'], $old['OPTS']);
                                }
                                $newId=$id;
                                $this->api->result['d']['provider']=$user[0]['PROVIDER'];
                                if($user[0]['PROVIDER']=='mourjan'){
                                    $this->api->result['d']['account']=$user[0]['IDENTIFIER'];
                                }else if($user[0]['PROVIDER']=='twitter'){
                                    $this->api->result['d']['account']=preg_replace('/http(?:s|)::\/\/twitter\.com\//','',$user[0]['PROFILE_URL']);
                                }else{
                                    $this->api->result['d']['account']=$user[0]['EMAIL'];
                                }
                            }else{
                                $newId=-1;
                            }
                        }else{
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
                    if($id && $password && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $user=$USER->getAccount($id);
                        if(isset($user[0]['ID']) && $user[0]['ID']){
                            $opt = json_decode($user[0]['OPTS'], true);
                            if(isset($opt['accountKey']) && $opt['accountKey']==$code){
                                error_log("BEFORE RESET");
                                if($USER->resetPassword($id, $password)){
                                    error_log("BEFORE MERGE");
                                    $USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $id);
                                    error_log("AFTER MERGE");
                                    $newId=$id;
                                    $this->api->result['d']['provider']=$user[0]['PROVIDER'];
                                    if($user[0]['PROVIDER']=='mourjan'){
                                        $this->api->result['d']['account']=$user[0]['IDENTIFIER'];
                                    }else if($user[0]['PROVIDER']=='twitter'){
                                        $this->api->result['d']['account']=preg_replace('/http(?:s|)::\/\/twitter\.com\//','',$user[0]['PROFILE_URL']);
                                    }else{
                                        $this->api->result['d']['account']=$user[0]['EMAIL'];
                                    }
                                }else{
                                    $newId=-2;
                                }
                            }else{
                                $newId=-1;
                            }
                        }else{
                            $newId=-1;
                        }
                    }
                    $this->api->result['d']['id']=$newId;
                    break;
                case API_ANDROID_SIGN_IN: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = -2;
                    $username = urldecode(filter_input(INPUT_POST, 'user', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $password = urldecode(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_ENCODED, ['options' => ['default' => '{}']]));
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    //error_log($username);
                    if($username && $password && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        require_once $this->api->config['dir'].'/core/model/User.php';
                        $this->api->db->setWriteMode();  
                        $USER = new User($this->api->db, $this->api->config, null, 0);
                        $newUid=$USER->authenticateUserAccount($username, $password);
                        if($newUid>0){
                            $newUid=$USER->mergeDeviceToAccount($this->api->getUUID(), $this->api->getUID(), $newUid);
                            if(!$newUid){
                                $newUid=-2;
                            }
                        }
                        $this->api->result['d']['id']=$newUid;
                    }
                    //error_log(var_export($this->api->result['d'],true));
                    break;
                case API_ANDROID_SIGN_OUT: 
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = 0;
                //$this->api->userStatus($status);
                //if ($status == 1) {
                    $default_id = filter_input(INPUT_POST, 'did', FILTER_VALIDATE_INT) + 0;
                    $this->api->db->setWriteMode();  
                    if($default_id > 0){
                        $result = $this->api->db->queryResultArray(
                                "update web_users_device set uid = ? where uuid = ? and (uid = ? or uid = ?) returning uid", [$default_id, $this->api->getUUID(), $default_id, $this->api->getUID()], true
                        );
                        if($result && isset($result[0]['UID'])){
                            $this->api->result['d']['id'] = $result[0]['UID'];
                            
                            //get total
                            $rs = $this->api->db->queryResultArray(
                                "SELECT sum(r.credit - r.debit)
                                FROM T_TRAN r
                                where r.UID=?", [$result[0]['UID']], true);
                            $this->api->result['d']['balance'] = -1;
                            if($rs && count($rs) && $rs[0]['SUM']!=null){
                                if($rs[0]['SUM']){                    
                                    $this->api->result['d']['balance']=$rs[0]['SUM']+0;
                                }else{
                                    $this->api->result['d']['balance']=0;                    
                                }
                            }
                        }
                    }
                //}
                break;
                case API_ANDROID_CHECK_FIX_CONNECTION_FAILURE:
                    $this->api->result['d'] = [];
                    $this->api->result['d']['id'] = 0;
                    $this->api->db->setWriteMode();   
                    $result = $this->api->db->queryResultArray(
                        "select uid from web_users_device where uid = ? and uuid = ?", [$this->api->getUID(), $this->api->getUUID()], true
                    );
                    if(empty($result)){
                        $this->api->db->queryResultArray(
                            "update web_users_device set uid = ? where uuid = ? returning uid", [$this->api->getUID(), $this->api->getUUID()], true
                        );
                        if($result && isset($result[0]['UID'])){
                            $this->api->result['d']['id'] = $result[0]['UID'];
                        }
                    }
                    break;
                case API_ANDROID_PURCHASE:   
                    $proceed = false;
                    $product_id = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $transaction = filter_input(INPUT_POST, 'tran', FILTER_DEFAULT, ['options'=>['default'=>'']]);
                    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    $transaction_date = date("Y-m-d H:i:s", filter_input(INPUT_POST, 'transaction_date', FILTER_VALIDATE_INT)+0);                    
                    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                    IF( ( ($product_id && $transaction_date && $transaction_id) || $transaction) && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
                        $transaction_signature = '';
                        $transaction_payload = '';
                        if($transaction != ''){
                            $tran = json_decode($transaction, true);
                            
                            if(isset($tran['json'])){
                                $tranO = json_decode($tran['json'], true);
                            }
                            
                            if(isset($tran['sig'])){
                                $transaction_signature = trim($tran['sig']);
                            }
                            
                            
                            if(isset($tranO['orderId'])){
                                $transaction_id = trim($tranO['orderId']);
                            }
                            if(isset($tranO['purchaseTime'])){
                                $transaction_date = $tranO['purchaseTime'];
                            }
                            if(isset($tranO['developerPayload'])){
                                $transaction_payload = trim($tranO['developerPayload']);
                            }
                            if(isset($tranO['productId'])){
                                $product_id = trim($tranO['productId']);
                            }
                            
                            if($transaction_id && $transaction_date && $product_id && $transaction_payload){
                                
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
                                    if($verification === 1){
                                        $proceed = true;
                                    }
                                //}
                            }
                        }
                        /*
                        if($transaction == '' && ( $product_id && $transaction_date && $transaction_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY)))){
                            $proceed = true;
                        }
                        */
                        if($proceed){
                        
                            error_log(sprintf("Authorized %s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), $this->api->getUUID(), $this->api->getUID(), $product_id, $transaction_id, $transaction_date).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
                            //$this->api->sendSMS('9613287168', "Authorized Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            //$this->api->sendSMS('96171750413', "Authorized Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");

                            $product_rs = $this->api->db->queryResultArray("select * from product where product_id=?", [$product_id]);

                            $this->api->result['transaction_id'] = 0;
                            if (!empty($product_rs)) {
                                $this->api->db->setWriteMode();
                                $product_rs=$product_rs[0];

                                $old_transaction = $this->api->db->queryResultArray("select id from t_tran where TRANSACTION_ID=?", [$transaction_id]);
                                if($old_transaction && count($old_transaction)){
                                    $this->api->result['transaction_id'] = $transaction_id;
                                }else{
                                    $transaction_date = date("Y-m-d H:i:s",floor($transaction_date / 1000));
                                    $server_id = intval(get_cfg_var('mourjan.server_id')) ;
                                    $coins = $this->api->db->queryResultArray(
                                        "INSERT INTO T_TRAN (UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, XREF_ID, TRANSACTION_ID, TRANSACTION_DATE, PRODUCT_ID, SERVER_ID, GATEWAY) VALUES ".
                                        "(?, current_timestamp, 'USD', ?, 0, ?, 0, ?, ?, ?, ?, 'ANDROID') RETURNING ID", 
                                        [$this->api->getUID(), $product_rs['USD_PRICE']+0.0, $product_rs['MCU']+0.0, $transaction_id, $transaction_date, $product_id, $server_id], 
                                        TRUE, PDO::FETCH_NUM);
                                    if($coins && count($coins)){
                                        $this->api->result['transaction_id'] = $transaction_id;
                                    }else{
                                        $this->api->result['e'] = "500";
                                    }
                                }
                            }else{
                                $this->api->result['e'] = "404";
                            }

                            //$this->api->sendSMS('9613287168', "iOS purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            $this->api->getCreditTotal();
                        
                        }else{
                            
                            error_log(sprintf("Declined %s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), $this->api->getUUID(), $this->api->getUID(), $product_id, $transaction_id, $transaction_date).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
                            //$this->api->sendSMS('9613287168', "Declined Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");
                            $this->api->sendSMS('96171750413', "Declined Android purchase UID {$this->api->getUID()}\nServer: {$this->api->config['server_id']}\nProduct: {$product_id}\nTransaction: {$transaction_id}\nDate: {$transaction_date}");

                            
                            $this->api->result['e'] = "501";
                        }

                        //notify devices of total update

                    }else{
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
                        IF($ad_id && $coins && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
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
                        IF($ad_id && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
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
                    IF($promoIdk && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                    
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
                    IF($promoIdk && base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
                        
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
                        IF(base64_decode($signature) == strtoupper(hash_hmac('sha1', ($_SERVER['HTTPS'] == 'on' ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], MOURJAN_KEY))){
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
    
    function detectDuplicateSuspension($contactInfo=array()){
        $state = 0;
        if(count($contactInfo) && $this->api->getUID()){
            $q='select distinct u.id,u.lvl,u.opts from ad_attribute t
            left join ad_user a on a.id = t.ad_id
            left join web_users u on u.id = a.web_user_id
            where
            a.id is not null and
            a.id <> '.$this->api->getUID().' and ( ';
            $params=array();
            $pass = 0;
            if(isset($contactInfo['p']) && count($contactInfo['p'])){
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
            
            if(count($params)){
                $users = $this->api->db->queryResultArray($q, $params);
                $time = $current_time = time();
                $blockAccount = false;
                if($users && count($users)){
                    foreach($users as $user){
                        if($user['LVL']==5){
                            $blockAccount=true;
                            break;
                        }elseif($user['OPTS']){
                            $options = json_decode($user['OPTS'],true);
                            if(isset($options['suspend']) && $options['suspend'] > $time){
                                $time = $options['suspend'];
                            }
                        }
                    }
                }
                if($blockAccount){
                    $state = 5;
                    $this->setLevel($this->api->getUID(),5);
                }elseif($time != $current_time){
                    $state = 1;
                    $this->suspendUser($this->api->getUID(),$time);
                }
            }
        }
        return $state;
    }
    
    function referrToSuperAdmin($id){
        $result=false;
        $res=$this->api->db->queryResultArray(
                    'update ad_object set super_admin=1 where id=?',
                    array($id),true);
        if ($res!==false) {
            $result=true;
        }
        return $result;
    }
    
    function setLevel($id,$level){
        $succeed=false;
        if($id && is_numeric($level)) {
            $q="update web_users set lvl=? where id=?";
            if ($this->api->db->queryResultArray($q, array($level,$id), true)) {
                $succeed=true;
            }
        }
        return $succeed;
    }
    
    function suspendUser($id, $time){
        $succeed=false;
        
        $options = $this->api->db->queryResultArray("select opts from web_users where id = ?", array($id));
        if($options && count($options)){
            $options = json_decode($options[0]['OPTS'],true);
            $options['suspend']=$time;
            $options = json_encode($options);
            $q=$this->api->db->prepareQuery("update web_users set opts=:options where id=:id");
            $q->bindParam(':options', $options, PDO::PARAM_LOB);
            $q->bindParam(':id', $id, PDO::PARAM_INT);
            if ($q->execute()) {
                $succeed=true;
                error_log('MOBILE SUSPENDED '.$id);
            }
            $q->closeCursor();
        }
        return $succeed;
    }

    function sendMail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $sender_account = '') {
        //require_once ('lib/phpmailer.class.php');
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
        return $res;
    }
    
    function isRTL($text){
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
