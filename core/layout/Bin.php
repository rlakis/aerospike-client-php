<?php
require_once 'vendor/autoload.php';
use MaxMind\Db\Reader;

require_once $config['dir'].'/core/layout/Site.php';

class AjaxHandler extends Site {

    var $rp=1;
    var $msg='';
    var $data=array();
    var $sid='';
    var $dir='';
    var $host='';

    function __construct($router){
        parent::__construct($router);
        $this->dir=$router->cfg['dir'];
        $this->host=$router->cfg['host'];
        $this->sid=session_id().$router->cfg['site_key'];
    }

    function setData($res, $label=''){
        if ($label) $this->data[$label]=$res;
        else $this->data[]=$res;
    }

    function mergeData($res, $label){
        if (isset ($this->data[$label])) $this->data[$label]=array_merge($this->data[$label], $res);
        else $this->data[$label]=$res;
    }

    function process(){
        $res=array();
        $res['RP']=$this->rp;
        $res['MSG']=$this->msg;
        $res['DATA']=$this->data;
        header("Content-Type: application/json");
        print json_encode($res);
    }

    function processRaw($res){
        header("Content-Type: application/json");
        echo json_encode($res);
    }

    function fail($msg='illegal access failure'){
	$this->msg=$msg;
        $this->rp=0;
        $this->process();
        exit (0);
    }
}


class Bin extends AjaxHandler{

    function __construct($router){
        parent::__construct($router);
        $this->actionSwitch();
    }

    function logAdmin($adId,$state,$msg=""){
        if($this->user->info['id'] && $this->user->info['level']==9 && $adId){
            $this->urlRouter->db->queryResultArray(
                'insert into log_admin (ad_id, admin_id, state,msg) values (?,?,?,?)', array(
                $adId,
                $this->user->info['id'],
                $state,
                $msg
            ));
        }
    }

    function getCountryUnit($countryId){
        $unit='$ USD';
        $currencies = $this->urlRouter->db->queryCacheResultSimpleArray(
                'currencies',
                'select trim(id) id,name_ar,name_en from currency',
        null, 0, $this->urlRouter->cfg['ttl_long']);
        $rcunit=$currencies[trim($this->urlRouter->countries[$countryId][6])];
        $unit=trim($rcunit[0]);
        if ($this->urlRouter->siteLanguage=='ar' && $rcunit[1]!='') $unit=trim($rcunit[1]);
        if ($unit=='USD') $unit='$';
        else $unit=' '.$unit;
        return $unit;
    }    
    
    function actionSwitch(){
        switch($this->urlRouter->module) {
            case 'ajax-screen':
                if (isset($_POST['w']) && $_POST['w'] && isset($_POST['h']) && $_POST['h']) {
                    $w=$_POST['w'];
                    $h=$_POST['h'];
                    $update=0;
                    if(isset($_POST['c'])){
                        $c = $this->post('c','boolean');
                        if($c){
                            $this->user->params['hasCanvas']=1;
                        }else{
                            $this->user->params['hasCanvas']=0;
                        }
                        $update=1;
                    }
                    if (is_numeric($w) && is_numeric($h) && $w && $h){
                        if(!$this->user->info['id']){
                            $this->user->params['etag']=$w*-1;
                        }elseif(isset($this->user->params['etag'])){
                            unset($this->user->params['etag']);
                        }
                        $this->user->params['screen']=array($w,$h);
                        $update=1;
                    }
                    if($update){
                        $this->user->update();
                        $this->user->setCookieData();
                    }
                }
                $this->process();
                break;
            case 'ajax-sorting':
                $order = $this->get('or','boolean');
                $this->user->params['catsort']=$order;
                $this->user->update();
                $this->process();
                break;
            case 'ajax-changepu':
                if($this->user->info['id'] && $this->user->info['level']==9){
                    $lang = $this->get('hl');
                    $this->fieldNameIndex=1;
                    if(!in_array($lang,array('ar','en'))){
                        $lang = 'ar';
                    }
                    if($lang=='en'){
                        $this->fieldNameIndex=2;
                    }
                    $this->urlRouter->siteLanguage=$lang;
                    $this->load_lang(array('main'), $lang);
                    
                    $id = $this->get('i');
                    $ro = $this->get('r');
                    $se = $this->get('s');
                    $pu = $this->get('p');
                    
                    $text = '';
                    if(isset($_POST['t'])){
                        $text = $_POST['t'];
                    }
                    $textIdx = $this->post('dx');
                    $textRtl = $this->post('rtl');
                    
                    $imgAdmin = $this->get('img','boolean');
                    $imgIdx = $this->get('ix');
                    
                    $this->urlRouter->db->setWriteMode();  
                    
                    $ad=$this->user->getPendingAds($id);
                    if (!empty($ad)) {
                        $textOnly=false;
                        
                        $ad=$ad[0];
                        $content=json_decode($ad['CONTENT'],true);
                        
                        if($imgAdmin){
                            $newImgs = [];
                            $i=0;
                            $imageToRemove = '';
                            foreach($content['pics'] as $img => $dim){
                                if($i++ != $imgIdx){
                                    $newImgs[$img]=$dim;
                                }else{
                                    $imageToRemove = $img;
                                }
                            }
                            if($imageToRemove){
                                $media = $this->urlRouter->db->queryResultArray("select * from media where filename = ?",[$imageToRemove],true);
                                if($media && count($media)){
                                    $this->urlRouter->db->queryResultArray("delete from ad_media where ad_id = ? and media_id = ?",[$id,$media[0]['ID']],true);
                                }
                            }
                            
                            $content['pics']=$newImgs;
                            
                            $images='';
                            if (isset($content['pics']) && is_array($content['pics']) && count($content['pics'])) {
                                $pass=0;
                                foreach($content['pics'] as $img => $dim){
                                    if($pass==0){
                                        $content['pic_def']=$img;
                                    }
                                    if($images){
                                        $images.="||";
                                    }
                                    $images.='<img width="118" src="'.$this->urlRouter->cfg['url_ad_img'].'/repos/s/' . $img . '" />';
                                    $pass=1;
                                }
                            }else{
                                unset($content['pic_def']);
                                $content['extra']['p']=2;
                            }
                            if (isset($content['video']) && $content['video'] && count($content['video'])) {
                                if($images){
                                    $images.="||";
                                }
                                $vid = $content['video'][2];
                                $images .='<img width="118" height="93" src="' . $vid . '" /><span class="play"></span>';
                            }

                            if($images){
                                $images.="||";
                            }
                            $images.='<img class="ir" src="'.$this->urlRouter->cfg['url_img'].'/90/' . $ad['SECTION_ID'] . '.png" />';
                            
                        }
                        
                        if($ro){
                            $content['ro']=$ro;
                        }
                        if($se){
                            $content['se']=$se;
                            $ad['SECTION_ID']=$se;
                        }
                        if($pu){                            
                            $content['pu']=$pu;
                            $ad['PURPOSE_ID']=$pu;
                        }
                        
                        if($textIdx){
                            $text=trim($text);
                            if($text){
                                $text.=json_decode('"\u200b"').' '.$this->user->parseUserAdTime($content['cui'],$content['cut'],$textRtl);
                            }
                            $textOnly=true;
                            if($textIdx==1){
                                $content['other']=$text;
                                $content['rtl']=$textRtl;
                            }else{
                                $content['altother']=$text;
                                $content['altRtl']=$textRtl;
                            }
                        }
                        if($content['other']=='' && $content['altother']){
                            $content['other']=$content['altother'];
                            $content['rtl']=$content['altRtl'];
                            $content['altother']='';
                            $content['altRtl']=0;
                            $textIdx=1;
                        }
                        $text = $content['other'];
                        $rtl = $content['rtl'];
                        $text2 = isset($content['altother']) ? $content['altother'] : '';
                        $rtl2 = isset($content['altRtl']) ? $content['altRtl'] : '';
                        
                        if($text2==''){
                            $content['extra']['t']=2;
                        }
                        
                        $root = $content['ro'];
                        $section=$content['se'];
                        $purpose=$content['pu'];
                        $content = json_encode($content);
                        
                        if($this->urlRouter->db->queryResultArray(
                            'update ad_user set content=?, section_id=?, purpose_id=? where id = ?', array(
                             $content,
                                $section,
                                $purpose,
                                $id
                        ))){
                            if($imgAdmin){
                                $redisAction = 'editorialImg'; 
                                $this->setData($images, 'sic');
                                $this->setData($imgIdx, 'dx');
                            }elseif($textOnly){
                                $redisAction = 'editorialText';
                                $this->setData($text, 't');
                                $this->setData($text2, 't2');
                                $this->setData($rtl, 'rtl');
                                $this->setData($rtl2, 'rtl2');
                                $this->setData($textIdx, 'dx');
                            }else{                                
                                $label = $this->getAdSection($ad, $root);
                                $this->setData($label, 'label');
                                $this->setData($root, 'ro');
                                $this->setData($section, 'se');
                                $this->setData($purpose, 'pu');
                                $redisAction = 'editorialUpdate';
                            }
                            $this->setData($id, 'id');
                            $this->process();
                            
                            try {
            	
                                $redis = new Redis();
                                $data = [
                                    'cmd'   =>  $redisAction,
                                    'data'  => $this->data
                                ];
                                if ($redis->connect('h8.mourjan.com', 6379, 1, NULL, 50)) {
                                    $redis->publish('editorial', json_encode($data));
                                } 

                            } catch (RedisException $re) {}
                            
                            $this->logAdmin($id, 6);
                        }else{
                            $this->fail();
                        }
                    }
                }else{
                    $this->fail();
                }
                break;
            case 'ajax-getshouts-private':
                if($this->user->info['id']){
                    $lang = $this->get('hl');
                    if(!in_array($lang,array('ar','en'))){
                        $lang = 'ar';
                    }
                    $notes = $this->urlRouter->db->queryResultArray(
                                'select first 5 s.id, s.text_'.$lang.' from
                                shoutout s
                                left join web_users_shoutouts_xref x on x.shoutout_id = s.id and x.uid = ?
                                where x.id is null and s.members_only = 1 and s.publish_type in (0,2,6)
                                order by s.id desc', array(
                                    $this->user->info['id']
                                ));
                    $this->setData($notes, 'shouts');
                }
                $this->process();
                break;
            case 'ajax-propspace':
                $del = $this->get('del');
                if($del && is_numeric($del)){
                    if($this->user->info['id']){
                        $success = $this->urlRouter->db->queryResultArray(
                            'update ad_user set state=8 where web_user_id = ? and doc_id > \'\'', array(
                                $this->user->info['id']
                            ));
                        if($success){
                            $success = $this->urlRouter->db->queryResultArray(
                                'delete from web_users_propspace where id = ? and uid = ?', array(
                                    $del,
                                    $this->user->info['id']
                                ));
                            if($success){
                                $this->process();
                            }else{
                                $this->fail($this->lang['sys_err']);
                            }
                        }else{
                            $this->fail($this->lang['sys_err']);
                        }
                    }else{
                        $this->fail($this->lang['ERROR_101']);
                    }
                }else{
                    $lang = $this->get('hl');
                    $url = trim($this->get('url'));
                    if(!in_array($lang,array('ar','en'))){
                        $lang = 'ar';
                    }
                    $this->urlRouter->siteLanguage=$lang;   
                    $this->load_lang(array('main'), $lang);
                    if($this->user->info['id']){
                        if($this->urlRouter->cfg['active_maintenance']){
                            $this->fail(strtolower($this->lang['title_site_maintenance']));
                        }else{
                            $params = array(
                                "uid" => $this->user->info['id'],
                                "url" => $url,
                                "hl"    =>  $lang
                            );
                            $script = 'https://up.mourjan.com/prop.php?'.http_build_query($params);
                            $headers = array(
                                'Content-Type: application/json',
                                'Connection: Keep-Alive',
                                'Keep-Alive: 300'
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $script);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


                            $result = curl_exec($ch);
                            if ($result == NULL || curl_errno($ch)) {
                                $this->fail($this->lang['sys_error'].' '.  curl_error($ch));
                            }else{
                                $object = json_decode($result,true);
                                if(json_last_error()){
                                    $msg = $result;
                                    if(strpos($result, 'ERROR_')!==false){
                                        $msg = $this->lang[$result];
                                    }
                                    error_log("Failed to connect {$this->user->info['id']} <{$url}>".PHP_EOL, 3, '/var/log/mourjan/propspace.error.log');
                                    $this->fail($msg);
                                }else{                                        
                                    $this->setData($object, 'feed');
                                    $this->process();
                                }
                            }
                            /*$connection = ssh2_connect('p1.mourjan.com', 22);
                            if($connection){
                                if(ssh2_auth_password($connection, 'root', 'x8p72CYDdweTty')){
                                    $stream = ssh2_exec($connection, '/usr/local/bin/php /opt/mourjan/utils/linkProp.php '.$this->user->info['id'].' "'.$url.'"');
                                    stream_set_blocking($stream, true);                                
                                    $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                                    $result = stream_get_contents($stream_out);
                                    if($result===false){
                                        $this->fail($this->lang['sys_error']);
                                    }else{
                                        error_log($result);
                                        $object = json_decode($result,true);
                                        if(json_last_error()){
                                            $msg = $result;
                                            if(strpos($result, 'ERROR_')!==false){
                                                $msg = $this->lang[$result];
                                            }
                                            $this->fail($msg);
                                        }else{                                        
                                            $this->setData($object, 'feed');
                                            $this->process();
                                        }
                                    }
                                }else{
                                    $this->fail($this->lang['sys_error']);
                                }
                                unset($connection);
                            }else{
                                $this->fail($this->lang['sys_error']);
                            }*/
                        }
                    }else{
                        $this->fail($this->lang['ERROR_101']);
                    }
                }
                break;
            case 'ajax-delshout':   
                if($this->user->info['id']){
                    $shoutId = $this->post('i','uint');
                    if($shoutId){
                        $this->urlRouter->db->queryResultArray(
                                'insert into web_users_shoutouts_xref (shoutout_id, uid) values (?, ?)', 
                                array($shoutId,
                                    $this->user->info['id']
                                ));
                    }
                }
                $this->process();
                break;
            case 'ajax-home':
                if($this->user->info['id']){
                    $lang = $this->post('hl');
                    if(!in_array($lang,array('ar','en'))){
                        $lang = 'ar';
                    }
                    $result = null;
                    if($this->user->info['level']!=5){
                        $balance = $this->user->getStatement($this->user->info['id'],0,true);
                        if($balance && isset($balance['balance']) && $balance['balance']){
                            $this->setData($balance['balance'], 'balance');
                        }else{
                            $this->setData(0, 'balance');
                        }
                        
                        $ads = $this->urlRouter->db->queryResultArray(
                                'select a.state, count(*)
                                from ad_user a
                                where a.state != 6 and a.state != 8 and a.web_user_id = ?
                                group by 1', array(
                                    $this->user->info['id']
                                ));
                        if($ads !== false){
                            $stats = [0,0,0,0,0];
                            foreach ($ads as $ad){
                                switch($ad['STATE']){
                                    case 9:
                                        $stats[3] += $ad['COUNT'];
                                        break;
                                    case 7:
                                        $stats[0] += $ad['COUNT'];
                                        break;
                                    case 4:
                                        $stats[1] += $ad['COUNT'];
                                        break;
                                    case 3:
                                        $stats[1] += $ad['COUNT'];
                                        $stats[4] += $ad['COUNT'];
                                        break;
                                    case 2:
                                        $stats[1] += $ad['COUNT'];
                                        break;
                                    case 1:
                                        $stats[1] += $ad['COUNT'];
                                        break;
                                    case 0:
                                    default:
                                        $stats[2] += $ad['COUNT'];
                                        break;
                                }
                            }
                            $this->setData($stats, 'ads');
                        }
                        
                        $props = $this->urlRouter->db->queryResultArray(
                                "select id,url,counter,DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', last_crawl) as crawl from web_users_propspace where uid = ?", array(
                                    $this->user->info['id']
                                ));
                        if($props !== false && count($props)){
                            $propspace = [];
                            foreach ($props as $prop){
                                $propspace[] = [$prop['ID'],$prop['URL'],$prop['CRAWL']];
                            }
                            $this->setData($propspace, 'props');
                        }
                    }
                    
                    $this->initSphinx();
                    $this->urlRouter->db->ql->setSortBy('date_added desc');
                    $this->urlRouter->db->ql->setFilter('starred', $this->user->info['id'], false);
                    $this->urlRouter->db->ql->setLimits(0, 1000);
                    $this->urlRouter->db->ql->setSelect('id');
                    $this->urlRouter->db->ql->addQuery('body', '');
                    $favs = $this->urlRouter->db->ql->executeBatch();
                    if(isset($favs['body']['matches'])){
                        $this->setData($favs['body']['total_found'], 'favs');
                    }else{
                        $this->setData(0, 'favs');
                    }
                    
                    $results=array();
                    $watchInfo = $this->user->getWatchInfo($this->user->info['id']);
                    $this->runQueries ($watchInfo, $results);
                    if ($results && count($results)) { 
                        $total = 0;
                        foreach ($results as $result) {
                            if (isset($result['matches'])) {
                                $total+=count($result['matches']);
                            }
                        }
                        $this->setData($total, 'watch');
                    }else{
                        $this->setData(0, 'watch');
                    }
                    
                    $notes = $this->urlRouter->db->queryResultArray(
                                'select first 5 s.id, s.text_'.$lang.' as t,s.action_link_'.$lang.' as l  from
                                shoutout s
                                left join web_users_shoutouts_xref x on x.shoutout_id = s.id and x.uid = ?
                                where x.id is null and s.members_only = 1 and s.publish_type in (0,2,6)
                                order by s.id desc', array(
                                    $this->user->info['id']
                                ));
                    $this->setData($notes, 'shouts');
                    
                    $this->process();
                }
                break;
            case 'ajax-balance':
                if($this->user->info['id']){
                    $userId = $this->get('u','uint');
                    if($userId){         
                        if($this->user->info['level']==9 || $this->user->info['id']==$userId){
                            $res=$this->user->getStatement($userId, 0, true);
                            if($res && $res['balance']){
                                $this->setData($res['balance'],'balance');
                            }else {
                                $this->setData(0,'balance');
                            }                            
                            $this->process();
                        }else{                            
                            $this->fail(103);
                        }
                    }else{
                        $this->fail(102);
                    }
                }else{
                    $this->fail(101);
                }
                break;
            case 'ajax-keyword':
                if($this->user->info['id'] && $this->user->isSuperUser()){
                    $key = $this->get('k');
                    $country = $this->get('c');
                    if($key){         
                        $res = $this->urlRouter->db->queryResultArray('select * from country_loc_keywords c where c.keyword containing ? and c.cc = ? order by c.keyword',[$key,$country], true);
                        if(is_array($res)){
                            $this->setData($res,'keys');           
                        }else{
                            $this->setData([],'keys');
                        }
                        $this->process();
                    }elseif(isset($_POST['id'])){
                        $id=  $this->post('id');
                        $AR=  trim($this->post('ar'));
                        $EN=  trim($this->post('en'));
                        $TAR=  trim($this->post('tar'));
                        $TEN=  trim($this->post('ten'));
                        $CC=  trim($this->post('cc'));
                        if($id){
                            $res = $this->urlRouter->db->queryResultArray('update country_loc_keywords set keyword=?,en=?,en_form=?,ar_form=?,cc=? where id=?',[$AR,$EN,$TEN,$TAR,$CC,$id], true);
                            if($res){
                                $this->process();
                            }else{
                                $this->fail(103);
                            }
                        }else{
                            if($AR){
                                $res = $this->urlRouter->db->queryResultArray('insert into country_loc_keywords (keyword,en,en_form,ar_form,cc) values (?,?,?,?,?)',[$AR,$EN,$TEN,$TAR,$CC], true);
                                if($res){
                                    $this->process();
                                }else{
                                    $this->fail(105);
                                }
                            }else{
                                $this->fail(104);
                            }
                        }
                    }else{
                        if(isset($_GET['rotate'])){
                            $this->urlRouter->db->queryResultArray("select PHP('touch', '{$country}', '') from rdb\$database",null, true);
                            $this->process();
                        }else{
                            $this->fail(102);
                        }
                    }
                }else{
                    $this->fail(101);
                }
                break;
            case 'ajax-stat':
                if ($this->urlRouter->cfg['server_id']<=0 || $this->urlRouter->cfg['server_id']>=99)
                {
                    return;
                }
                
                if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
                    //error_log(PHP_EOL.'BOT: ' .$_SERVER['HTTP_USER_AGENT'].PHP_EOL);
                    return;
                }
                $req=NULL;  
                $stat_servers = $this->urlRouter->db->getCache()->get("sphinx_servers");
                //error_log(json_encode($stat_servers));
                /*
                $stat_servers = [                   
                    ["host"=>"h7.mourjan.com", "port"=>9307, 'socket'=>''], 
                    ["host"=>"h6.mourjan.com", "port"=>9307, 'socket'=>''], 
                    ["host"=>"h2.mourjan.com", "port"=>9307, 'socket'=>''], 
                    ["host"=>"h3.mourjan.com", "port"=>9307, 'socket'=>''], 
                    ["host"=>"h4.mourjan.com", "port"=>9307, 'socket'=>'']];
                */
                if (isset($_POST['a']) && $_POST['a']) {
                    $final_req = $req = (is_array($_POST['a']) ? $_POST['a'] : json_decode($_POST['a'],true) );
                }
            
                if ($req) {
                    $stat_server = $this->urlRouter->db->getCache()->get("stat_server");
                    $redis = new Redis();
                    $ok = 1;
                    try {
                        /*
                        $redis->connect($this->urlRouter->cfg['rs-host'], $this->urlRouter->cfg['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
                        $redis->setOption(Redis::OPT_PREFIX, $this->urlRouter->cfg['rs-prefix']);
                        $redis->select($this->urlRouter->cfg['rs-index']);
                        */
                        $redis->connect($stat_server['host'], $stat_server['port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
                        $redis->setOption(Redis::OPT_PREFIX, $stat_server['prefix']);
                        $redis->select($stat_server['index']);
                        
                    } catch (RedisException $e) {
                        $ok=0;
                    }
                    $countryCode = '';
                    $referer = ''; 
                    
                    if (is_array($req) && count($req)) {
                        if(isset($_POST['app'])){
                            $referer = 'mourjan-app'; 
                            $countryCode = (isset($_POST['code']) && strlen($_POST['code']) == 2) ? strtoupper(trim($_POST['code'])) : '';
                        }else{
                            if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], 'mourjan')){
                                $referer = substr($_SERVER['HTTP_REFERER'],0,256);
                            }
                            //require_once $this->urlRouter->cfg['dir'].'/core/model/Sdb.php';
                            //$db = new SDB($this->urlRouter->cfg);
                            //$stmt=$db->prepareQuery(
                            //    'insert into REQS (ad_id,country,referer) values (?,?,?)'
                            //);
                            if(!isset($this->user->params['user_country'])){
                                $this->checkUserGeo();
                            }
                            $countryCode = strtoupper(trim($this->user->params['user_country']));
                            if(strlen($countryCode)!=2)$countryCode='';
                        }
                        
                     
                       
                        $result=NULL;
                        $batch='';

                        foreach ($req as $action => $refs) {
                            switch ($action) {
                                case 'ad-imp':
                                    foreach ($final_req as $final_action => $final_refs) {
                                        if ($final_action=='ad-imp') {
                                            foreach ($final_refs as $id) {
                                                $id = (int)$id;
                                                $batch.= "select id, impressions, publication_id from {$this->urlRouter->cfg['search_index']} where id={$id};\n";
                                                
                                                $adData=$this->classifieds->getById($id);
                                                
                                                if (isset($adData[Classifieds::USER_ID]) && $adData[Classifieds::USER_ID]>0) { 
                                                    if ($ok==1 && $redis->isConnected()) {
                                                        
                                                        $redis->sAdd('U'.$adData[Classifieds::USER_ID], $id);
                                                        if (!$redis->hIncrBy('AI'.$id, date("Y-m-d"), 1)) {
                                                            error_log(sprintf("%s\tad-imp\t%d\t%s\t%s", date("Y-m-d H:i:s"), $id, $countryCode, $referer).PHP_EOL, 3, "/var/log/mourjan/stat.log");                                                            
                                                        }
                                                    } else {
                                                        error_log(sprintf("%s\tad-imp\t%d\t%s\t%s", date("Y-m-d H:i:s"), $id, $countryCode, $referer).PHP_EOL, 3, "/var/log/mourjan/stat.log");                                                        
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                case 'ad-clk':
                                    if ($refs && is_numeric($refs)) {
                                        
                                        $adData=$this->classifieds->getById($refs);                                                
                                        if (isset($adData[Classifieds::USER_ID]) && $adData[Classifieds::USER_ID]>0) {
                                            if ($ok==1 && $redis->isConnected()) {                                                    
                                                $redis->sAdd('U'.$adData[Classifieds::USER_ID], $refs);
                                                $redis->hIncrBy('AC'.$refs, date("Y-m-d"), 1);
                                            } else {
                                                error_log(sprintf("%s\tad-clk\t%d\t%s", date("Y-m-d H:i:s"), $refs, $countryCode).PHP_EOL, 3, "/var/log/mourjan/stat.log");                                                
                                            }
                                        }

                                    }
                                    break;
                                default:
                                    break;
                            }
                        }

                        
                        if (!empty($batch)) {
                            foreach ($stat_servers as $stat_server) {
                                $ss = new SphinxQL($stat_server, $this->urlRouter->cfg['search_index']);
                                $result=$this->urlRouter->db->ql->search($batch);
                                //error_log(PHP_EOL . var_export($result, TRUE).PHP_EOL);
                                foreach($result['matches'] as $row) {
                                    if (isset($row[0])) {
                                        $row=$row[0];                                        
                                    }
                                    //error_log(PHP_EOL.var_export($row, TRUE).PHP_EOL);
                                    if (!isset($row['id'])) {
                                        //error_log(var_export($row, TRUE) . PHP_EOL . var_export($result['matches'], TRUE));
                                        continue;
                                    }
                                    if ($row['publication_id']!=1) {
                                        continue;
                                    }
                                    
                                    $id=$row['id']+0;    
                                    $im=$row['impressions']+1;     
                                                                                        
                                    $ss->getConnection()->real_query("update {$this->urlRouter->cfg['search_index']} set impressions={$im} where id={$id}");
                                }
                            }
                        }                                           
                    }
                    
                    if ($ok) {
                        $redis->close();
                    }
                    
                }
                $this->process();
                if(isset($_POST['l']) && $_POST['l']){
                    $req = (is_array($_POST['l']) ? $_POST['l'] : json_decode($_POST['l'],true));
                    if($req && is_array($req)){
                        if( 
                                isset($req['cn']) && is_numeric($req['cn']) && 
                                isset($req['c']) && is_numeric($req['c']) && 
                                isset($req['se']) && is_numeric($req['se']) && $req['se'] && 
                                isset($req['pu']) && is_numeric($req['pu'])  
                                ){
                            if (isset($this->user->params['last'])){
                                $last = $this->user->params['last'];
                                if ($last['cn'] == $req['cn'] && $last['c'] == $req['c'] && $last['se'] != $req['se']){
                                    error_log(sprintf("%d\t%d\t%d\t%d\t%d\t%d\t%s", $req['se'], $last['se'], $req['cn'], $req['c'], $req['pu'], $last['pu'], date("Y-m-d H:i:s")).PHP_EOL, 3, "/var/log/mourjan/s-follow.log");
                                    /*
                                    $q='update or insert into section_follow
                                    (section_id, to_section_id, country_id, city_id, purpose_id, to_purpose_id)
                                    values (?,?,?,?,?,?)
                                    matching (section_id,to_section_id,country_id,city_id,purpose_id,to_purpose_id)';
                                    $this->urlRouter->db->queryResultArray($q, array(
                                        $req['se'],
                                        $last['se'],
                                        $req['cn'],
                                        $req['c'],
                                        $req['pu'],
                                        $last['pu']
                                    ));
                                     * 
                                     */
                                    $this->user->params['last']=$req;
                                    $this->user->update();
                                }
                            }else {
                                $this->user->params['last']=$req;
                                $this->user->update();
                            }
                        }
                    }
                }
                break;
            case 'ajax-ga':
                $uid=$this->post('u', 'uint');
                $archive = $this->post('x', 'boolean');
                if($this->user->info['id'] && ($this->user->info['id']==$uid || $this->user->info['level']==9)){
                    
                    $aid = $this->post('a', 'uint');
                    
                    $showInteractions = 0;
                    if($this->user->info['level']==9 || in_array($this->user->info['id'],$this->urlRouter->cfg['enabled_interactions']) ){
                        $showInteractions = 1;
                    }
                    $stat_server = $this->urlRouter->db->getCache()->get("stat_server");
                    $redis = new Redis();
                    /*
                    $redis->connect($this->urlRouter->cfg['rs-host'], $this->urlRouter->cfg['rs-port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
                    $redis->setOption(Redis::OPT_PREFIX, $this->urlRouter->cfg['rs-prefix']);
                    $redis->select($this->urlRouter->cfg['rs-index']);
                    */
                    
                    $redis->connect($stat_server['host'], $stat_server['port'], 1, NULL, 100); // 1 sec timeout, 100ms delay between reconnection attempts.
                    $redis->setOption(Redis::OPT_PREFIX, $stat_server['prefix']);
                    $redis->select($stat_server['index']);
                    
                    if($aid){
                        $count=0;
                        $q='select cast(r.ts as date) as d,count(*) as c
                        from xref x
                        left join reqs r on x.ad_id = r.ad_id
                        where x.ad_id = ? and r.ad_id is not null  
                        group by 1';
                        //$res=$db->queryResultArray($q,array($aid));
                        
                        $res = $redis->hGetAll('AI'.$aid);
                        ksort($res, SORT_STRING);
                    
                        $data = array();
                        $cdata = array();
                        $dt=0;
                        if($res && count($res)){
                            $i=0;
                            $curDt=0;
                            $prevDt=0;
                            foreach($res as $date=>$hits){
                                $curDt=strtotime($date);
                                if($i==0)$dt=$curDt;
                                else{
                                    $ddif = $curDt-$prevDt;
                                    if($ddif>86400){
                                        $span = $ddif / 86400;
                                        for($k=0;$k<$span-1;$k++){
                                            $data[]=0;   
                                            $i++;
                                        }
                                    }
                                }
                                $prevDt=$curDt;
                                $data[]=(int)$hits;
                                $count+=(int)$hits;
                                $i++;
                            }
                            if($showInteractions){
                                $q='select cast(r.ts as date) as d,count(*) as c
                                from xref x
                                left join clks r on x.ad_id = r.ad_id
                                where x.ad_id = ? and r.ad_id is not null  
                                group by 1';
                                //$rc=$db->queryResultArray($q,array($aid));
                                $rc = $redis->hGetAll('AC'.$aid);
                                ksort($rc, SORT_STRING);
                                if($rc && count($res)){
                                    $j=0;
                                    $curDt=0;
                                    $prevDt=$dt-86400;
                                    foreach($rc as $date=>$clks){
                                        $curDt=strtotime($date);
                                        $ddif = $curDt-$prevDt;
                                        if($ddif>86400){
                                            $span = $ddif / 86400;
                                            for($k=0;$k<$span-1;$k++){
                                                $cdata[]=0;   
                                                $j++;
                                            }
                                        }
                                        //echo '<br>';
                                        $prevDt=$curDt;
                                        $cdata[]=(int)$clks;
                                        $j++;
                                    }
                                    if($j<$i){
                                        for($k=$j;$k<$i;$k++){
                                            $cdata[]=0;   
                                        }
                                    }
                                }else{
                                    foreach($data as $imp){
                                        $cdata[]=0;
                                    }
                                }
                            }
                        }
                        $this->setData($data,'c');
                        if(count($cdata))$this->setData($cdata,'k');
                        $this->setData($count,'t');
                        $this->setData($dt*1000,'d');
                    }else{
                        $total = 0;
                        $summary = [];
                        if(!$archive){
                            $q='select cast(r.ts as date) as d,
                            count(*) as c

                            from xref x
                            left join reqs r on x.ad_id = r.ad_id

                            where x.web_user_id = ? and r.ad_id is not null 
                            and r.ts > dateadd(-1 month to current_date)

                            group by 1';
                            //$res=$db->queryResultArray($q,array($uid));
                            $sdate = time()-2592000; // 30 days
              
                            $ads = $redis->sGetMembers('U'.$uid);
                            $res = [];
                            
                            foreach ($ads as $id) {                            
                                $impressions = $redis->hGetAll('AI'.$id);
                                foreach ($impressions as $date => $value) {
                                    if (isset($summary[$id])) {
                                        $summary[$id]+=$value+0;
                                    } else {
                                        $summary[$id]=$value+0;
                                    }
                                    if (strtotime($date)<$sdate) continue;
                                    
                                    if (isset($res[$date])) {
                                        $res[$date]+=$value+0;
                                    } else {
                                        $res[$date]=$value+0;
                                    }
                                }                                
                            }
                            ksort($res, SORT_STRING);
                            if (count($res)>30) {
                                $nres=[];
                                $res=array_slice($res, -30);
                            }
                          
                            
                            $data = array();
                            $cdata = array();
                            $dt=0;
                            if($res && count($res)){
                                $i=0;
                                $curDt=0;
                                $prevDt=0;
                                foreach($res as $date=>$hits){
                                    $curDt=strtotime($date);
                                    if($i==0)$dt=$curDt;
                                    else{
                                        $ddif = $curDt-$prevDt;
                                        if($ddif>=86400){
                                            $span = (int)$ddif / 86400;
                                            for($k=0;$k<$span-1;$k++){
                                                $data[]=0;  
                                                $i++;
                                            }
                                        }
                                    }
                                    $prevDt=$curDt;
                                    $data[]=(int)$hits;  
                                    $total += (int)$hits;
                                    $i++;
                                }
                              
                                if($showInteractions){
                                
                                    $q='select cast(r.ts as date) as d,
                                    count(*) as c

                                    from xref x
                                    left join clks r on x.ad_id = r.ad_id

                                    where x.web_user_id = ? and r.ad_id is not null
                                    and r.ts > dateadd(-1 month to current_date)

                                    group by 1';
                                    //$rc=$db->queryResultArray($q,array($uid));
                                    $rc = [];
                                    foreach ($ads as $id) {                            
                                        $clicks = $redis->hGetAll('AC'.$id);
                                        foreach ($clicks as $date => $value) {
                                            if (isset($rc[$date])) {
                                                $rc[$date]+=$value+0;
                                            } else {
                                                $rc[$date]=$value+0;
                                            }
                                        }                                
                                    }
                                    ksort($rc, SORT_STRING);
                                    //error_log(var_export($rc, TRUE));
                                    //$dt=0;
                                    if($rc && count($rc)){
                                        $j=0;
                                        $curDt=0;
                                        $prevDt=$dt-86400;
                                        foreach($rc as $date=>$clicks){
                                            $curDt=strtotime($date);
                                            if($curDt<$dt) continue;
                                            $ddif = $curDt-$prevDt;
                                            
                                            //echo (int)$ddif / 86400,"\n";
                                            if($ddif>=86400){
                                                $span = (int)$ddif / 86400;
                                                //echo $span,"\n";
                                                for($k=0;$k<$span-1;$k++){
                                                    $cdata[]=0;
                                                    //echo $j,"\n";   
                                                    $j++;
                                                }
                                            }
                                            $prevDt=$curDt;
                                            $cdata[]=(int)$clicks;
                                            $j++;
                                        }
                                        if($j<$i){
                                            for($k=$j;$k<$i;$k++){
                                                $cdata[]=0;   
                                            }
                                        }
                                    }else{
                                        foreach($data as $imp){
                                            $cdata[]=0;
                                        }
                                    }
                                }
                            }
                            $this->setData($data,'c');
                            if(count($cdata))$this->setData($cdata,'k');
                            $this->setData($dt*1000,'d');
                        }

                        $q='select x.ad_id,count(*) as c
                        from xref x
                        left join reqs r on x.ad_id = r.ad_id
                        where x.hold='.($archive ? 1 : 0).'  
                        and x.web_user_id = ?  and r.ad_id is not null 
                        group by 1';
                        //$res=$db->queryResultArray($q,array($uid));
                        if($summary && count($summary)){
                            //$count=0;
                            //$data = array();
                            //foreach($res as $r){
                                //$count+=(int)$r['C'];
                            //    $data[$r['AD_ID']]=(int)$r['C'];
                            //}
                            $this->setData($summary,'a');                            
                            //$this->setData($count,'t');
                        }else{
                            $this->setData(0,'a');
                            //$this->setData(0,'t');
                        }
                        $this->setData($total,'t');
                    }
                    $this->process();
                    
                    $redis->close();
                }else $this->fail('101');
                break;
            case 'ajax-menu':
                if(isset($_GET['c'])){
                    $c = $this->get('c','boolean');
                    if($c){
                        $this->user->params['hasCanvas']=1;
                    }else{
                        $this->user->params['hasCanvas']=0;
                    }
                    $this->user->update();
                }
                $hash=$this->get('h');
                if ($hash){
                    $content=eval('?'.'>'.file_get_contents( dirname( $this->urlRouter->cfg['dir'] ) .'/tmp/gen/'.$hash.'99.php').'<'.'?');
                    echo $content;
                }else $this->fail('101');
                break;
            case 'ajax-prog':
                $id = $this->post('id', 'uint');
                if($id){
                    $key = ini_get("session.upload_progress.prefix").$id;
                    if(isset($_SESSION[$key])){                        
                        $upload_progress = $_SESSION[$key];
                        $progress = round( ($upload_progress['bytes_processed'] / $upload_progress['content_length']) * 100 );

                    }else{
                        $progress=100;
                    }
                    $this->setData($progress,'p');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-getad':
                $id=$this->get('x', 'uint');
                if($id){
                    $ad=$this->classifieds->getById($id);
                    if (isset($ad[Classifieds::ID])){
                        $result=[];
                        $result['i']=$ad[Classifieds::ID];
                        $result['p']=0;
                        if ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                             $result['p']=$ad[Classifieds::PICTURES];
                        }
                        $this->setData($result,'i');
                        $this->process();
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-ads':
                $id=$this->post('id', 'uint');
                $lang=$this->post('l');
                if($id){
                    if ($lang=='ar') $lang='ar';
                    else $lang='en';
                    $ad=$this->classifieds->getById($id);
                    if (isset($ad[Classifieds::ID])){
                        $result=array();
                        if (!empty($ad[Classifieds::ALT_CONTENT])) {
                            if ($lang=="en" && $ad[Classifieds::RTL]) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                           } elseif ($lang=="ar" && $ad[Classifieds::RTL]==0) {
                               $ad[Classifieds::TITLE] = $ad[Classifieds::ALT_TITLE];
                               $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                           }       
                       }
                       $result['i']=$ad[Classifieds::ID];
                       //$result['t']=$ad[Classifieds::TITLE];
                       //$title=$this->sphinx->BuildExcerpts(array($ad[Classifieds::CONTENT]), 'mouftah', '', array("limit" => 30));
                       $title=$this->BuildExcerpts($ad[Classifieds::CONTENT], 30);
                       $result['t']=$title;
                       $result['c']=$ad[Classifieds::CONTENT];
                       $result['l']=$this->urlRouter->cfg['host'].sprintf($ad[Classifieds::URI_FORMAT], ($lang=="ar"?"":"{$lang}/"), $ad[Classifieds::ID]);
                       $result['p']='';
                       if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])){
                           $result['p']=$ad[Classifieds::VIDEO][2];                           
                       }elseif ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                            $result['p']=$ad[Classifieds::PICTURES][0];
                       }else{
                           $result['p']=$this->urlRouter->cfg['url_img'].'/200/'.$ad[Classifieds::SECTION_ID].'.png';
                       }
                       $this->setData($result,'i');
                       $this->process();
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-favorite':
                $users_udpate=array();
                $id=$this->post('id', 'uint');
                $s=$this->post('s', 'boolean');
                $site=$this;
                if ($id && ($s!==false) && $this->user->info['id']) {
                    if ($this->user->updateFavorite($id, $s)) {
                        $this->process();
                    }else $this->fail("102");
                }elseif ($id && $this->user->info['id']==0){
                    $this->user->pending['fav']=$id;
                    $this->user->update();
                    $this->process();
                }else $this->fail('101');
                break;
            case 'manifest-mobile':
                /*$module=$this->get('m');
                $stamp=$this->get('t');
                $id=$this->user->info['id'];
                //if($module == 'post'){
                        header("HTTP/1.1 410 Gone");
                        exit(0);
                //}
                if(0 && $module == 'favorite'){
                    header("HTTP/1.1 410 Gone");
                }else{
                header('Content-type: text/cache-manifest');
                header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
                header("Pragma: no-cache");
                header("Expires: ".date('r'));
                
                echo "CACHE MANIFEST\n";
                echo "# module:{$module} {$stamp}\n";
                echo "# version:{$this->urlRouter->cfg['etag']}\n";
                echo "# online:".($id ? 1 : 0)."\n";
                echo "CACHE:\n";
                echo "{$this->urlRouter->cfg['url_img']}/favicon.ico\n";
                echo "{$this->urlRouter->cfg['url_zepto']}\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/main_m".($this->urlRouter->siteLanguage=='en' ? '':'_ar').".css\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/mms.css\n";
                echo "{$this->urlRouter->cfg['url_js_mobile']}/m_gen.js\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/main_m.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/bbg.png\n"; 
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/f/all.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/realestate.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/cars.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/jobs.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/service.png\n";
                echo "{$this->urlRouter->cfg['url_css_mobile']}/i/misc.png\n";
                echo "NETWORK:\n";
                echo "*\n";
                echo "FALLBACK:\n";
                echo "/ {$this->urlRouter->cfg['host']}/check-connection/".($this->urlRouter->siteLanguage=='en' ? 'en/':'')."\n";
                }
                exit (0);*/
                break;
            case 'ajax-post-se':
                $id=$this->post('r', 'uint');
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;                
                if ($id && $this->user->info['id']) {
                    $this->load_lang(array('post'), $lang);
                    $sections=$this->urlRouter->db->queryCacheResultSimpleArray(
                    "req_sections_{$this->urlRouter->siteLanguage}_{$id}",
                    "select s.ID,s.name_".$this->urlRouter->siteLanguage."
                    from section s
                    left join category c on c.id=s.category_id
                    where c.root_id={$id} 
                    order by s.NAME_{$this->urlRouter->siteLanguage}", null, null, $this->urlRouter->cfg['ttl_long']);
                    $res=array('m'=>'','i'=>array());
                    $res['m']=$this->lang['m_h_s'.$id];
                    foreach ($sections as $section){
                        $res['i'][]=array($section[0],$section[1]);
                    }
                    $this->setData($res,'s');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-code-list':
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') {
                    $q='select c.code,c.id,c.name_'.$lang.',c.locked,trim(id_2)    
                        from country c 
                        where id != 109 
                        order by c.locked desc,c.name_'.$lang;
                    $cc=$this->urlRouter->db->queryCacheResultSimpleArray(
                        'country_codes_req_'.$lang,
                        $q,
                        null, null, $this->urlRouter->cfg['ttl_long']);
                    $this->setData($cc,'l');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-remove-watch':
                $id=$this->post('id', 'numeric');
                if ($id && $this->user->info['id']){
                    if ($this->user->removeWatch($id)){
                        unset($this->user->params['loadedWatch']);
                        $this->user->update();
                        $this->user->updateOptions();
                        if ($this->user->info['email']){
                            $mailFrequency=(isset($this->user->info['options']['mailEvery']) && $this->user->info['options']['mailEvery']) ? $this->user->info['options']['mailEvery'] : 1;
                            $this->user->checkWatchMailSetting($this->user->info['id'], $mailFrequency);
                        }
                        $this->process();
                    }else $this->fail("102");
                }else $this->fail('101');
                break;
            case 'ajax-watch-update':
                $id=$this->post('id', 'uint');
                $title=$this->post('t');
                $email=$this->post('e','boolean');
                $email = $email ? 1 : 0;
                if ($id && $title && $this->user->info['id']){
                    $title=trim(preg_replace('/[\.,;\-_+=%^!?\/\\،؛{}|`~]/u', ' ', $title));
                    $title=preg_replace("/\s+/u", ' ', $title);
                    $title=preg_replace("/<.*?>/u", '', $title);
                    if ($title){
                        if (preg_match('/[\x{0621}-\x{0669}]/u', $title)){
                            $title='<span class="ar">'.$title.'</span>';
                        }else {
                            $title='<span class="en">'.$title.'</span>';
                        }
                        if ($this->user->updateWatch($id, $title,$email)){
                            unset($this->user->params['loadedWatch']);
                            $this->user->update();
                            if ($this->user->info['email']){
                                $mailFrequency=(isset($this->user->info['options']['mailEvery']) && $this->user->info['options']['mailEvery']) ? $this->user->info['options']['mailEvery'] : 1;
                                $this->user->checkWatchMailSetting($this->user->info['id'], $mailFrequency);
                            }
                            $this->setData($title, 'T');
                            $this->process();
                        }else $this->fail("102");
                    }else $this->fail('103');
                }else $this->fail('101');
                break;
            case 'ajax-watch':
                $section=$this->post('s', 'uint');
                $purpose=$this->post('p', 'uint');
                $city=$this->post('c', 'uint');
                $country=$this->post('cn', 'uint');
                $extended=$this->post('e', 'uint');
                $locality=$this->post('l', 'uint');
                $query=$this->post('q');
                $title=$this->post('t');
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;
                $this->load_lang(array('bin'));
                if (($section && $country)||$query){
                    $params=array(
                            'cn'    =>  $country,
                            'c'    =>  $city,
                            's'    =>  $section,
                            'p'    =>  $purpose,
                            'e'    =>  $extended,
                            'l'    =>  $locality,
                            'q'    =>  $query,
                            't'    =>  $title
                        );
                    if ($this->user->info['id'] && $this->user->info['level']!=6  && $this->user->info['level']!=5) {
                        $count=isset($this->user->info['options']['watch']) ? count($this->user->info['options']['watch']) : 0;
                        if ($count<20) {
                            $id=$this->user->insertWatch($params);
                            if ($id) {
                                unset($this->user->params['loadedWatch']);
                                $this->user->update();
                                $this->user->updateOptions();
                                if ($this->user->info['email']){
                                    $mailFrequency=(isset($this->user->info['options']['mailEvery']) && $this->user->info['options']['mailEvery']) ? $this->user->info['options']['mailEvery'] : 1;
                                    $this->user->checkWatchMailSetting($this->user->info['id'], $mailFrequency);
                                }
                                $this->setData($id,'id');
                                $this->process();
                            }else $this->fail($this->lang['db_err'].'. '.$this->lang['persist_err']);
                        }else $this->fail ($this->lang['max_watchlist_err']);
                    }elseif(!$this->user->info['id']){ 
                        $this->user->pending['watch']=$params;
                        $this->user->update();
                        $this->process();
                    }
                }else $this->fail($this->lang['system_err']);
                break;
            case 'ajax-country-cities':
                $id=$this->post('i', 'uint');
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;
                $fidx=1;
                if ($this->urlRouter->siteLanguage=='en') $fidx=2;
                $res=array();
                if ($id && is_numeric($id)){
                    $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$id}_{$this->urlRouter->siteLanguage}",
                        "select c.ID
                        from city c
                        where c.country_id={$id}
                        and c.blocked=0
                        order by NAME_".  strtoupper($this->urlRouter->siteLanguage),
                        null, 0, $this->urlRouter->cfg['ttl_long']);
                    foreach ($countryCities as $key=>$val){
                        $res[$key]=$this->urlRouter->cities[$key][$fidx];
                    }
                    if (count($res)<2) $res=array();
                    $this->setData($res,'C');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-cc-add':
                $country=$this->post('i', 'numeric');
                $city=$this->post('c', 'numeric');
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;
                $fidx=1;
                if ($this->urlRouter->siteLanguage=='en') $fidx=2;
                if (isset($this->user->pending['post']) && $country && $city){
                    $res=array();
                    $this->user->pending['post']['zloc']='';
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if (!isset ($adContent['pubTo']))$adContent['pubTo']=array();
                    if ($country==-1) {
                        foreach ($this->urlRouter->cities as $id=>$city){
                            if (isset ($this->urlRouter->countries[$this->urlRouter->cities[$id][6]]))
                                $adContent['pubTo'][$id]=$id;
                        }
                    }elseif (isset($this->urlRouter->countries[$country])) {
                        if ($city==-1){
                            $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$country}_{$this->urlRouter->siteLanguage}",
                            "select c.ID
                            from city c
                            where c.country_id={$country}
                            and c.blocked=0
                            order by NAME_".  strtoupper($this->urlRouter->siteLanguage),
                            null, 0, $this->urlRouter->cfg['ttl_long']);
                            foreach ($countryCities as $id=>$city){
                                $adContent['pubTo'][$id]=$id;
                            }
                        }else {
                            if (isset($this->urlRouter->cities[$city])) $adContent['pubTo'][$city]=$city;
                        }
                    }
                    $i=0;
                    $def=array();
                    $sloc='';
                    $countries=array();
                    $cities=array();
                    foreach ($adContent['pubTo'] as $cty=>$val) {
                        if($i==0 && !$this->user->pending['post']['dcn']){
                                $def[0]=$this->urlRouter->cities[$cty][6];
                                $def[1]=$cty;
                                $this->user->pending['post']['cn']=$this->urlRouter->cities[$cty][6];
                                $this->user->pending['post']['c']=$cty;
                        }
                        $countries[$this->urlRouter->cities[$cty][6]]=$this->urlRouter->cities[$cty][6];
                        $cities[$cty]=$cty;
                        $res[$cty]=$this->urlRouter->countries[$this->urlRouter->cities[$cty][6]][$fidx].' - '.$this->urlRouter->cities[$cty][$fidx];
                    }
                    if (!$this->user->pending['post']['dcn']){
                        if (count($countries)==1){
                            $countryId=array_pop($countries);
                            $sloc=$this->urlRouter->countries[$countryId][$fidx];
                            $this->user->pending['post']['code']=$def[3]=$this->urlRouter->countries[$countryId][3].'|+'.$this->urlRouter->countries[$countryId][7];
                            $def[4]=$this->getCountryUnit($countryId);
                        }
                        if (count($cities)==1){
                            $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}_{$this->urlRouter->siteLanguage}",
                            "select c.ID
                            from city c
                            where c.country_id={$countryId}
                            and c.blocked=0
                            order by NAME_".  strtoupper($this->urlRouter->siteLanguage),
                            null, 0, $this->urlRouter->cfg['ttl_long']);
                            if(count($countryCities)>1) {
                                $key=array_pop($cities);
                                if ($key==15)
                                    $sloc=$this->urlRouter->cities[$key][$fidx];
                                else
                                    $sloc=$this->urlRouter->cities[$key][$fidx].' '.$sloc;
                                
                            }
                        }
                        $sloc=trim($sloc);
                        $def[2]=$sloc;
                        $this->user->pending['post']['zloc']=$sloc;
                    }
                    $this->user->pending['post']['content']=json_encode($adContent);
                    $this->user->update();
                    $this->user->saveAd($this->user->pending['post']['state']);
                    asort($res);
                    $this->setData($res,'L');
                    $this->setData($def,'D');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-cc-remove':
                $city=$this->post('i', 'numeric');
                $lang=$this->post('lang');
                if ($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;
                $fidx=1;
                if ($this->urlRouter->siteLanguage=='en') $fidx=2;
                if (isset($this->user->pending['post'])){
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if ($city){
                        $this->user->pending['post']['zloc']='';
                        $res=array();
                        $def=array();
                        if ($city<0) {
                            $adContent['pubTo']=array();
                        }elseif (isset ($adContent['pubTo'][$city])) {
                            if (!$this->user->pending['post']['dcn'] && $city==$this->user->pending['post']['c']){
                                $this->user->pending['post']['c']=0;
                                $this->user->pending['post']['cn']=0;
                                $def[0]=0;
                                $def[1]=0;
                            }
                            unset ($adContent['pubTo'][$city]);
                        }
                    }else {
                        if (!isset($adContent['pubTo']))$adContent['pubTo']=array();
                        $this->user->pending['post']['dcn']=0;
                        $this->user->pending['post']['dcni']='';
                        $this->user->pending['post']['dc']=0;
                        $this->user->pending['post']['dci']='';
                        $this->user->pending['post']['gloc']='';
                        $this->user->pending['post']['tloc']='';
                        $this->user->pending['post']['loc']='';
                        $this->user->pending['post']['sloc']='';
                        $this->user->pending['post']['lat']=0;
                        $this->user->pending['post']['lon']=0;
                    }
                    if (!count($adContent['pubTo'])) {
                        if (!$this->user->pending['post']['dcn']){
                            $this->user->pending['post']['c']=0;
                            $this->user->pending['post']['cn']=0;
                            $def[0]=0;
                            $def[1]=0;
                        }
                        unset($adContent['pubTo']);                        
                    }else {
                        foreach ($adContent['pubTo'] as $cty=>$val) {
                            if (!$this->user->pending['post']['dcn']) {
                                $def[0]=$this->urlRouter->cities[$cty][6];
                                $def[1]=$cty;
                                $this->user->pending['post']['cn']=$this->urlRouter->cities[$cty][6];
                                $this->user->pending['post']['c']=$cty;
                            }
                            break;
                        }
                    }
                    $sloc='';
                    $countries=array();
                    $cities=array();
                    if (!$this->user->pending['post']['dcn']){
                        if(isset($adContent['pubTo']) && count($adContent['pubTo'])) {
                            foreach ($adContent['pubTo'] as $cty=>$val) {
                                $countries[$this->urlRouter->cities[$cty][6]]=$this->urlRouter->cities[$cty][6];
                                $cities[$cty]=$cty;
                            }
                            if (count($countries)==1){
                                $countryId=array_pop($countries);
                                $sloc=$this->urlRouter->countries[$countryId][$fidx];
                                $this->user->pending['post']['code']=$def[3]=$this->urlRouter->countries[$countryId][3].'|+'.$this->urlRouter->countries[$countryId][7];
                                $def[4]=$this->getCountryUnit($countryId);
                            }
                            if (count($cities)==1){
                                $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}_{$this->urlRouter->siteLanguage}",
                                "select c.ID
                                from city c
                                where c.country_id={$countryId}
                                and c.blocked=0
                                order by NAME_".  strtoupper($this->urlRouter->siteLanguage),
                                null, 0, $this->urlRouter->cfg['ttl_long']);
                                if(count($countryCities)>1)
                                $sloc=$this->urlRouter->cities[array_pop($cities)][$fidx].' '.$sloc;
                            }
                            $sloc=trim($sloc);
                            $def[2]=$sloc;
                            $this->user->pending['post']['zloc']=$sloc;
                        }else {
                            $def[2]='';
                            $def[3]='';
                            $def[4]='';
                            $this->user->pending['post']['zloc']=$sloc;
                        }
                    }
                    $this->user->pending['post']['content']=json_encode($adContent);
                    $this->user->update();
                    $this->user->saveAd($this->user->pending['post']['state']);
                    $this->setData($def,'D');
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-location':
                //if (isset($this->user->params['mobile']) && $this->user->params['mobile']){
                     
                    if (isset ($_POST['loc'])){
                        $locations= (is_array($_POST['loc']) ? $_POST['loc'] : json_decode($_POST['loc'],true) );
                        $lang=strtoupper($_POST['lang']);
                        
                        $isSearch = (isset($_POST['search'])&&$_POST['search']==1) ? true : false;
                        if($isSearch) {
                            $this->fail('110');
                        }
                        $level=0;
                        $lastLat=0;
                        $lastLong=0;
                        $cityId=0;
                        $countryId=0;
                        $parentId=0;
                        
                        $types=$this->urlRouter->db->queryCacheResultSimpleArray(
                        'map_types',
                        'select name,id from gtypes',
                        null, 0, $this->urlRouter->cfg['ttl_long']);

                        $stmt=$this->urlRouter->db->prepareQuery(
                            "update or insert into gmap (type_id,name_{$lang},short_name_{$lang},latitude,longitude,parent_id) values (?,?,?,?,?,?) matching(type_id,latitude,longitude) returning id"
                        );
                        $cityStmt=$this->urlRouter->db->prepareQuery(
                            "update or insert into city
                            (name_{$lang},latitude,longitude,parent_id,country_id) values (?,?,?,?,?)
                            matching(latitude,longitude) returning id,blocked"
                        );
                            
                        $getParentStmt=$this->urlRouter->db->prepareQuery(
                            'select * from city c where c.parent_id = ? and country_id = ?'
                        );
                        
                        $updateDuplicateStmt = $this->urlRouter->db->prepareQuery(
                                'update city set name_'.$lang.' = ?, latitude = ?, longitude = ? where id = ?'
                                );
                            
                        $len=count($locations);
                        
                        for($k=1;$k<$len;$k++){
                            if ($locations[$k]['type']==$locations[$k-1]['type']) {
                                unset ($locations[$k-1]);
                            }
                        }
                        
                        $len=count($locations);
                        
                        if($isSearch && $len){
                            foreach ($locations as $loc){
                                if (isset ($types[$loc['type']])) {
                                    $loc['latitude']=  number_format($loc['latitude'], 8);
                                    $loc['longitude']=  number_format($loc['longitude'], 8);
                                    $loc['vw_ne_lat']=  number_format($loc['vw_ne_lat'], 8);
                                    $loc['vw_ne_lon']=  number_format($loc['vw_ne_lon'], 8);
                                    $loc['vw_sw_lon']=  number_format($loc['vw_sw_lon'], 8);
                                    $loc['vw_sw_lat']=  number_format($loc['vw_sw_lat'], 8);
                                    $type=$types[$loc['type']][1];
                                    $countryId = 0;
                                    $cityId=0;
                                    
                                    $siblingCities = $this->urlRouter->db->queryResultArray('select * from city c where c.latitude between ? and ? and c.longitude between ? and ?',array($loc['vw_sw_lat'],$loc['vw_ne_lat'],$loc['vw_sw_lon'],$loc['vw_ne_lon']));
                                    
                                    $parents=array();
                                    $topParent=0;
                                    $foundDuplicate = false;
                                    foreach($siblingCities as $city){
                                        $tmpName = strtolower($loc['name']);
                                        if(strtolower($city['NAME_AR'])== $tmpName || strtolower($city['NAME_EN'])==$tmpName){
                                            $foundDuplicate = true;
                                            $this->urlRouter->db->queryResultArray('update city set country_id = ?, name_'.$lang.' = ?, latitude = ?, longitude = ? where id = ?',array($city['COUNTRY_ID'],$city['ID']),true);
                                            break;
                                        }
                                        if(!isset($parents[$city['PARENT_ID']])){
                                            $parents[$city['PARENT_ID']]=0;
                                        }
                                        $parents[$city['PARENT_ID']]++;
                                        $countryId = $city['COUNTRY_ID'];
                                    }
                                    if(!$foundDuplicate){
                                        foreach($parents as $cid => $cnt){
                                            if($cnt > $topParent){
                                                $topParent = $cnt;
                                                $cityId = $cid;
                                            }
                                        }

                                        if($cityId && $countryId) {
                                            if(in_array($type,array(6,7,8,9,10,11,12,23,24,25,26,27,28,29))){
                                                $loc['name']=preg_replace('/\(.*\)?/', '', $loc['name']);
                                                $loc['short']=preg_replace('/\(.*\)?/', '', $loc['short']);
                                                $short=$loc['short'];
                                                if ($lang=='AR' || ($lang=='EN' && !preg_match('/[\x{0621}-\x{064a}]/u', $short))){
                                                    $cityStmt->execute(array($loc['name'],$loc['latitude'],$loc['longitude'], $cityId, $countryId));
                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            }                            
                        }elseif($len) {
                        
                            foreach ($locations as $loc){
                                if (isset ($types[$loc['type']])) {
                                    $loc['latitude']=  number_format($loc['latitude'], 8);
                                    $loc['longitude']=  number_format($loc['longitude'], 8);
                                    $type=$types[$loc['type']][1];
                                    
                                    //error_log("\n>>{$loc['type']}<<\n");
                                    //error_log(">>{$loc['name']}<<\n\n");
                                    
                                    if ($type==5) {
                                        $miniStmt=$this->urlRouter->db->prepareQuery("update or insert into country
                                                (name_{$lang},id_2,latitude,longitude)
                                                values
                                                (?,?,?,?) matching(id_2) returning id,code,blocked");
                                        if($miniStmt->execute(array($loc['name'],$loc['short'],$loc['latitude'],$loc['longitude']))){
                                            $tmp=$miniStmt->fetch(PDO::FETCH_NUM);
                                            $countryId=$tmp[0];
                                        }
                                    }else{
                                        if($level != $type && in_array($type,array(6,7,8,9,10,11,12,23,24,25,26,27,28,29)) && $countryId){
                                            $level=$type;
                                            $loc['name']=preg_replace('/\(.*\)?/', '', $loc['name']);
                                            $loc['name']=preg_replace('/[\x{0600}-\x{061E}]/u', '', $loc['name']);
                                            $loc['name']=preg_replace('/[\x{064B}-\x{065E}\x{06D4}-\x{06ED}\x{0730}-\x{074C}\x{07A6}-\x{07AF}\x{0816}-\x{082D}]/u', '', $loc['name']);
                                            $loc['short']=preg_replace('/\(.*\)?/', '', $loc['short']);
                                            $short=$loc['short'];
                                            if ($lang=='AR' || ($lang=='EN' && !preg_match('/[\x{0621}-\x{064a}]/u', $short))){
                                                //error_log("\n\n11".$loc['name']."\n\n");
                                                if ($lastLat!=$loc['latitude'] || $lastLong!=$loc['longitude']) {
                                                    //error_log("\n\n22".$loc['name']."\n\n");
                                                    
                                                    $getParentStmt->execute([$cityId,$countryId]);
                                                    //$this->urlRouter->db->queryResultArray('select * from city c where c.parent_id = ? and country_id = ?',array($cityId));
                                                    $foundDuplicate = false;
                                                    //error_log('City: '.$loc['name']."\n");
                                                    //error_log('Parent City: '.$cityId."\n");
                                                    while($city = $getParentStmt->fetch(PDO::FETCH_ASSOC)){
                                                    //foreach($siblingCities as $city){
                                                        $tmpName = strtolower($loc['name']);
                                                        if(strtolower($city['NAME_AR'])== $tmpName || strtolower($city['NAME_EN'])==$tmpName){
                                                            //error_log('UPDATE ID: '.$city['ID']."\n");
                                                            //error_log('Match AR: '.$city['NAME_AR']."\n");
                                                            //error_log('Match EN: '.$city['NAME_EN']."\n");
                                                            $lastLat=$loc['latitude'];
                                                            $lastLong=$loc['longitude'];
                                                            $cityId=$city['ID'];
                                                            $foundDuplicate = true;
                                                            try{
                                                                $updateDuplicateStmt->execute(array($loc['name'],$loc['latitude'],$loc['longitude'],$city['ID']));
                                                            }catch(Exception $e){
                                                                error_log('AJAX Location: '.$e->getMessage().PHP_EOL);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                    if (!$foundDuplicate){
                                                        if($cityStmt->execute(array($loc['name'],$loc['latitude'],$loc['longitude'], $cityId, $countryId))) {
                                                            $lastLat=$loc['latitude'];
                                                            $lastLong=$loc['longitude'];
                                                            $tmp=$cityStmt->fetch(PDO::FETCH_NUM);
                                                            //error_log("\n\n".$cityId." ".$loc['name']."\n\n");
                                                            $cityId=$tmp[0];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($stmt->execute(array($type,$loc['name'],$loc['short'],$loc['latitude'],$loc['longitude'],$parentId))) {
                                        $tmp=$stmt->fetch(PDO::FETCH_NUM);
                                        $parentId=$tmp[0];
                                    }
                                }
                            }
                        }
                        $this->process();
                        
                    }else{
                        $this->fail('101');
                    }
                /*}else{
                if (isset ($_POST['loc']) && is_array($_POST['loc'])) {
                    $lang=strtoupper($_POST['lang']);
                    if (!isset ($this->user->pending['post'])){
                        $this->user->pending['post']=array(
                            'id'=>0,
                            'user'=>  $this->user->info['id'],
                            'ro'=>0,
                            'pu'=>0,
                            'se'=>0,
                            'rtl'=>0,
                            'cn'=>0,
                            'c'=>0,
                            'dc'=>0,
                            'dcn'=>0,
                            'lon'=>0,
                            'lat'=>0,
                            'state'=>0,
                            'content'=>json_encode(array()),
                            'title'=>'',
                            'dcni'=>'',
                            'dci'=>'',
                            'loc'=>'',
                            'gloc'=>'',
                            'tloc'=>'',
                            'zloc'=>'',
                            'code'=>''
                        );
                    }
                    $types=$this->urlRouter->db->queryCacheResultSimpleArray(
                        'map_types',
                        'select name,id from gtypes',
                        null, 0, $this->urlRouter->cfg['ttl_long']);

                    $stmt=$this->urlRouter->db->prepareQuery(
                        "update or insert into gmap (type_id,name_{$lang},short_name_{$lang},latitude,longitude,parent_id) values (?,?,?,?,?,?) matching(type_id,latitude,longitude) returning id"
                    );
                    $cityStmt=$this->urlRouter->db->prepareQuery(
                        "update or insert into city
                        (name_{$lang},latitude,longitude,parent_id,country_id) values (?,?,?,?,?)
                        matching(latitude,longitude) returning id,blocked"
                    );

                    $locations=$_POST['loc'];                    
                    $parentId=0;
                    $countryId=0;
                    $cityId=0;
                    $level=0;
                    $gLocation='';
                    $tLocation='';
                    $localCities=array();
                    $adLocation=array();
                    $newCountry=false;
                    $lastLat=0;
                    $lastLong=0;
                    $forceDefCity=0;
                    $len=count($locations);
                    for($k=1;$k<$len;$k++){
                        if ($locations[$k]['type']==$locations[$k-1]['type']) {
                            unset ($locations[$k-1]);
                        }
                    }
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    foreach ($locations as $loc){
                        if (isset ($types[$loc['type']])) {
                            $loc['latitude']=  number_format($loc['latitude'], 8);
                            $loc['longitude']=  number_format($loc['longitude'], 8);
                            $type=$types[$loc['type']][1];
                            if ($type==5) {
                                $miniStmt=$this->urlRouter->db->prepareQuery("update or insert into country
                                        (name_{$lang},id_2,latitude,longitude)
                                        values
                                        (?,?,?,?) matching(id_2) returning id,code,blocked");
                                if($miniStmt->execute(array($loc['name'],$loc['short'],$loc['latitude'],$loc['longitude']))){
                                    $tmp=$miniStmt->fetch(PDO::FETCH_NUM);
                                    $countryId=$tmp[0];
                                    if ($tmp[2]) $forceDefCity=1;
                                    if ($countryId==2){
                                        if ($lang=='AR') $loc['name']='الإمارات';
                                        else $loc['name']='Emirates';
                                    }
                                    $this->user->pending['post']['cn']=$countryId;
                                    $this->user->pending['post']['dcn']=$countryId;
                                    $this->user->pending['post']['dcni']=strtolower(trim($loc['name']));
                                    $this->user->pending['post']['code']=strtolower(trim($loc['short'])).'|+'.$tmp[1];
                                    $adContent['fields']['pc1']=$adContent['fields']['pc2']=$adContent['fields']['pc3']=$this->user->pending['post']['code'];
                                    $this->user->pending['post']['content']=json_encode($adContent);
                                }
                            }else{
                                if($level != $type && in_array($type,array(6,7,8,9,10,11,12)) && $countryId){
                                    $level=$type;
                                    $loc['name']=preg_replace('/\(.*\)?/', '', $loc['name']);
                                    $loc['short']=preg_replace('/\(.*\)?/', '', $loc['short']);
                                    $short=$loc['short'];
                                    if ($lastLat!=$loc['latitude'] || $lastLong!=$loc['longitude']) {
                                    if ($cityStmt->execute(array($loc['name'],$loc['latitude'],$loc['longitude'], $cityId, $countryId))) {
                                        $lastLat=$loc['latitude'];
                                        $lastLong=$loc['longitude'];
                                        $tmp=$cityStmt->fetch(PDO::FETCH_NUM);
                                        $cityId=$tmp[0];
                                        if ($tmp[1]==0 || $forceDefCity){
                                            $forceDefCity=0;
                                            $this->user->pending['post']['dc']=$tmp[1];
                                            $this->user->pending['post']['dci']=$loc['name'];
                                        }

                                        if ($type==6 || $type==10) $tLocation=$short;

                                        if($tmp[1] && !in_array($short, $localCities)) {
                                            if ($type>6) {
                                                $loc['name']=preg_replace('/\(.*\)?/', '', $loc['name']);
                                                $adLocation[]=$loc['name'];
                                            }
                                            $localCities[]=$short;
                                            if (in_array($type, array(6,7,8,10))) {
                                                $gLocation=$short;
                                            }
                                        }
                                        
                                        $this->user->pending['post']['c']=$cityId;
                                    }}
                                }
                                elseif (in_array($type,array(2,3)) && $countryId){
                                    $adLocation[]=$loc['name'];
                                }
                            }
                            if ($stmt->execute(array($type,$loc['name'],$loc['short'],$loc['latitude'],$loc['longitude'],$parentId))) {
                                $tmp=$stmt->fetch(PDO::FETCH_NUM);
                                $parentId=$tmp[0];
                            }
                        }
                    }
                    if (!$countryId || !$cityId){                        
                        if (!isset($adContent['pubTo']))$adContent['pubTo']=array();
                        $this->user->pending['post']['dcn']=0;
                        $this->user->pending['post']['dcni']='';
                        $this->user->pending['post']['dc']=0;
                        $this->user->pending['post']['dci']='';
                        $this->user->pending['post']['gloc']='';
                        $this->user->pending['post']['tloc']='';
                        $this->user->pending['post']['loc']='';
                        $this->user->pending['post']['lat']=0;
                        $this->user->pending['post']['lon']=0;
                        if (!count($adContent['pubTo'])) {
                            if (!$this->user->pending['post']['dcn']){
                                $this->user->pending['post']['c']=0;
                                $this->user->pending['post']['cn']=0;
                                $countryId=0;
                                $cityId=0;
                            }
                            unset($adContent['pubTo']);                        
                        }else {
                            foreach ($adContent['pubTo'] as $cty=>$val) {
                                $this->user->pending['post']['cn']=$countryId=$this->urlRouter->cities[$cty][6];
                                $this->user->pending['post']['c']=$cityId=$cty;
                                break;
                            }
                        }
                        if(isset($adContent['pubTo']) && count($adContent['pubTo'])) {
                            $countries=array();
                            $cities=array();
                            foreach ($adContent['pubTo'] as $cty=>$val) {
                                $countries[$countryId]=$countryId;
                                $cities[$cty]=$cty;
                            }
                            if (count($countries)==1){
                                $countryId=array_pop($countries);
                                $sloc=$this->urlRouter->countries[$countryId][$fidx];
                                $this->user->pending['post']['code']=$this->urlRouter->countries[$countryId][3].'|+'.$this->urlRouter->countries[$countryId][7];
                            }
                            if (count($cities)==1){
                                $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}_{$this->urlRouter->siteLanguage}",
                                "select c.ID
                                from city c
                                where c.country_id={$countryId}
                                and c.blocked=0
                                order by NAME_".  strtoupper($this->urlRouter->siteLanguage),
                                null, 0, $this->urlRouter->cfg['ttl_long']);
                                if(count($countryCities)>1)
                                    $sloc=$this->urlRouter->cities[array_pop($cities)][$fidx].' '.$sloc;
                            }
                            $sloc=trim($sloc);
                            $this->user->pending['post']['zloc']=$sloc;
                        }else {
                            $this->user->pending['post']['zloc']=$sloc;
                        }
                    }else {
                        if (count($adLocation))$adLocation=implode(' ', array_reverse($adLocation));
                        $this->user->pending['post']['zloc']='';
                        $this->user->pending['post']['gloc']=ucfirst(strtolower($gLocation));
                        $this->user->pending['post']['tloc']=ucfirst(strtolower($tLocation));
                        if ($adLocation=="") $adLocation=$this->user->pending['post']['tloc'];
                        $this->user->pending['post']['loc']=ucfirst(strtolower($adLocation));
                        $this->user->pending['post']['lat']=$loc['latitude'];
                        $this->user->pending['post']['lon']=$loc['longitude'];
                    }
                    $this->user->update();
                    $data=array('cn'=>$countryId,'c'=>$cityId);
                    $this->setData($data,'loc');
                    $this->process();
                    
                }else $this->fail();}*/
                break;
            case "ajax-pending":
                if (isset ($_POST['m'])){
                    $module=$_POST['m'];

                    $this->user->pending['redirect']=$module;
                    $this->user->update();
                    
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-pi':
                if(!isset($this->user->info['inc'])){
                    $this->user->info['inc']=0;
                }
                $this->user->info['inc']++;
                $this->user->update();
                $this->process();
                break;
            case "ajax-adsave":
                //if (isset($this->user->params['mobile']) && $this->user->params['mobile']){
       
                    if($this->user->info['id'] && isset($_POST['o'])){
                        include_once $this->urlRouter->cfg['dir'] . '/core/lib/MCAdTextHandler.php';
                        $textHandler = new AdTextFormatter();
                        
                        $error_path = "/var/log/adsave.log";
                        $ad=(is_array($_POST['o']) ? $_POST['o'] : json_decode($_POST['o'],true) );
//                        error_log('--------------------------------------------------------------------------------------------------------'.PHP_EOL,3,$error_path);                    
                        
                        if(!is_array($ad))
                            $ad = array();
                        
                        if(!isset($ad['id']))
                            $ad['id']=0;
                        if(!$ad['id'] || !isset($this->user->pending['post']['state']) || !isset($this->user->pending['post']['id']) 
                                || ($ad['id'] && $ad['id']!=$this->user->pending['post']['id'])
                            ){
                            $this->user->loadAdToSession($ad['id']);
                        }
                        $sContent=json_decode($this->user->pending['post']['content'],true);
                        
                        if(isset($sContent['ip']))
                            $ad['ip']=$sContent['ip'];
                        if(isset($sContent['userLOC']))
                            $ad['userLOC']=$sContent['userLOC'];
                        if(isset($sContent['agent']))
                            $ad['agent']=$sContent['agent'];
                        if(isset($sContent['state']))
                            $ad['state']=$sContent['state'];
                        if(!isset($ad['other']) && isset($sContent['other'])){
                            $ad['other']=$sContent['other'];
                        }

                        if(!isset($ad['rtl']) && isset($sContent['rtl'])){
                            $ad['rtl']=$sContent['rtl'];
                        }
                        if(!isset($ad['loc']) && isset($sContent['loc'])){
                            $ad['loc']=$sContent['loc'];
                        }
                        if($ad['extra']['t']!=2 && !isset($ad['altother']) && isset($sContent['altother'])){
                            $ad['altother']=$sContent['altother'];
                        }
                        if($ad['extra']['t']!=2 && !isset($ad['altRtl']) && isset($sContent['altRtl'])){
                            $ad['altRtl']=$sContent['altRtl'];
                        }
                        if(isset($sContent['pics'])){
                            $ad['pics']=$sContent['pics'];
                        }
                        if(!isset($ad['pic_def']) && isset($sContent['pic_def'])){
                            $ad['pic_def']=$sContent['pic_def'];
                        }
                        if(isset($sContent['pic_idx'])){
                            $ad['pic_idx']=$sContent['pic_idx'];
                        }
                        if(!isset($ad['video']) && isset($sContent['video'])){
                            $ad['video']=$sContent['video'];
                        }
                        if($ad['user']==$this->user->info['id'] && isset($this->user->params['mobile']) && $this->user->params['mobile']){
                            $ad['mobile']=1;
                        }else{
                            $ad['mobile']=0;
                        }
                        
                        
                        
                        if(!$ad['id']){
                            $this->user->pending['post']['user']=$this->user->info['id'];
                        }
                        
                        $this->user->pending['post']['ro']=$ad['ro'];
                        $sectionId = $this->user->pending['post']['se']=$ad['se'];
                        $purposeId = $this->user->pending['post']['pu']=$ad['pu'];
                        $this->user->pending['post']['rtl']=$ad['rtl'];
                        $this->user->pending['post']['lat']=$ad['lat'];
                        $this->user->pending['post']['lon']=$ad['lon'];
                        $this->user->pending['post']['title']='';
                        
                        $cityId=0;
                        $countryId=0;
                        $currentCid = 0;
                        $isMultiCountry = false;
                        
                        if(count($ad['pubTo'])){
                            foreach($ad['pubTo'] as $key => $val){
                                if(!is_numeric($key)){
                                    unset($ad['pubTo'][$key]);
                                    continue;
                                }
                                if(!$cityId && $cityId!=64)$cityId=$key;
                                if($cityId==64){
                                    if(isset($ad['pubTo']['64']))
                                        unset($ad['pubTo']['64']);
                                    elseif(isset($ad['pubTo'][64]))
                                        unset($ad['pubTo'][64]);
                                    $cityId=0;
                                    $key = 0;
                                }
                                if($key && isset($this->urlRouter->cities[$key][4])){
                                    if($currentCid && $currentCid != $this->urlRouter->cities[$key][4]){
                                        $isMultiCountry = true;
                                    }
                                    $currentCid = $this->urlRouter->cities[$key][4];
                                }
                            }
                        }
                        if($cityId){
                            $countryId=$this->urlRouter->cities[$cityId][4];
                        }
                        $this->user->pending['post']['c']=$cityId;
                        $this->user->pending['post']['cn']=$countryId;
                        
                        $requireReview = 0;
                        
                        if ($this->user->info['level']!=9) $ad['userLvl']=$this->user->info['level'];
                        if ($this->user->info['id'] == $this->user->pending['post']['user']){
                            $ad['agent']=$_SERVER['HTTP_USER_AGENT'];
                            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                            }else {
                                $ip = $_SERVER['REMOTE_ADDR'];
                            }
                            $ad['ip']=$ip;   
                            $geo = $this->urlRouter->getIpLocation($ip);
                            if($geo) {
                            	$ad['userLOC'] = isset($geo['city']['names']['en']) ? $geo['city']['names']['en'].', ' : '';
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
                                    'TR',
                                    'OM'
                                    ])){
                                    $requireReview = 1;
                                }
                                
                            } else $ad['userLOC']=0;
                        }
                        
                        $publish=(isset($_POST['pub']) && $_POST['pub'] ? (int)$_POST['pub'] : 0);
                        if ($publish!=1 && ($publish!=2 || ($publish==2 && $this->user->info['level']!=9)))$publish=0;
                        $tmpPublish=$publish;
                        if ($this->user->info['level']==9 && $ad['user']!=$this->user->info['id'] && 
                                $this->user->pending['post']['state']==1 && $publish==0) {
                            $publish=1;
                        }
                        
                        //switching all rental cars to rental services
                        if($publish==1 && $ad['ro']==2 && $ad['pu']==2){
                            $this->user->pending['post']['ro']=$ad['ro']=4;
                            $this->user->pending['post']['pu']=$ad['pu']=5;
                            $this->user->pending['post']['se']=$ad['se']=431;
                        }
                        
                        $textHandler->setText($ad['other']);
                        $textHandler->format();                
                        $ad['other'] = $textHandler->text;
                            
                        if($this->isRTL($ad['other'])){
                            $ad['rtl']=1;
                        }else{
                            $ad['rtl']=0;
                        }
                        
                        if(isset($ad['altother']) && $ad['altother']){
                            $textHandler->setText($ad['altother']);
                            $textHandler->format();                
                            $ad['altother'] = $textHandler->text;
                            if($this->isRTL($ad['altother'])){
                                $ad['altRtl']=1;
                            }else{
                                $ad['altRtl']=0;
                            }
                            
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
                        $this->user->pending['post']['rtl']=$ad['rtl'];
                        
                        if(isset($ad['loc']) && $ad['loc']){
                            $ad['sloc']=$ad['loc'];
                        }
                        
                        
                        if($publish == 1){                            
                            $sections = $this->urlRouter->db->getSections();
                            if(isset($sections[$sectionId]) && $sections[$sectionId][5] && $sections[$sectionId][8]==$purposeId){
                                $this->user->pending['post']['ro']=$ad['ro']=$sections[$sections[$sectionId][5]][4];
                                $this->user->pending['post']['se']=$ad['se']=$sections[$sectionId][5];
                                $this->user->pending['post']['pu']=$ad['pu']=$sections[$sectionId][9];
                            }
                            
                            
                        }
                        $wrongPhoneNumber = false;
                        if($publish== 1 && isset($ad['cui']['p']) && count($ad['cui']['p'])){
                                $numbers = [];
                                $validator = libphonenumber\PhoneNumberUtil::getInstance();
                                foreach($ad['cui']['p'] as $number){
                                    if(isset($number['v']) && trim($number['v'])!=''){
                                        $num = $validator->parse($number['v'], $number['i']);
                                        
                                        $isValid = false;
                                        if($validator->isValidNumber($num)){
                                            $mType = $validator->getNumberType($num);
                                            $isValid = true;
                                        }else{
                                            if(strlen($number['r']) > 15){
                                                $corrected = false;
                                                $tmp2 = '';
                                                for($i = 6, $l = (strlen($number['r'])/2)+5; $i < $l; $i++){
                                                    $tmp = substr($number['r'], 0, $i);
                                                    $num = $validator->parse($tmp, $number['i']);
                                                    if($validator->isValidNumber($num)){
                                                        $tNum = [
                                                            'v' =>  $validator->format($num, libphonenumber\PhoneNumberFormat::E164),
                                                            't' =>  1,
                                                            'c' =>  $number['c'],
                                                            'r' =>  $tmp,
                                                            'i' => $number['i']
                                                        ];
                                                        $mType = $validator->getNumberType($num);
                                                        switch ($mType) {
                                                            case 3:
                                                            case 0:
                                                                $tNum['t'] = 7;
                                                                break;
                                                            case 5:
                                                            case 2:
                                                            case 1:
                                                                $tNum['t'] = 1;
                                                                break;
                                                            default:
                                                                $tNum = null;
                                                                break;
                                                        }
                                                        if($tNum){
                                                            $numbers[] = $tNum;
                                                        }
                                                        
                                                        $tmp = substr($number['r'], strlen($tmp));
                                                        $num = $validator->parse($tmp, $number['i']);
                                                        if($validator->isValidNumber($num)){
                                                            $tNum = [
                                                                'v' =>  $validator->format($num, libphonenumber\PhoneNumberFormat::E164),
                                                                't' =>  1,
                                                                'c' =>  $number['c'],
                                                                'r' =>  $tmp,
                                                                'i' => $number['i']
                                                            ];
                                                            $mType = $validator->getNumberType($num);
                                                            switch ($mType) {
                                                                case 3:
                                                                case 0:
                                                                    $tNum['t'] = 7;
                                                                    break;
                                                                case 5:
                                                                case 2:
                                                                case 1:
                                                                    $tNum['t'] = 1;
                                                                    break;
                                                                default:
                                                                    $tNum = null;
                                                                    break;
                                                            }
                                                            if($tNum){
                                                                $numbers[] = $tNum;
                                                            }
                                                        }
                                                        $corrected = true;
                                                        break;
                                                    }
                                                }
                                                if($corrected){
                                                    continue;
                                                }
                                            }else{
                                                
                                                $num = $validator->parse($number['r'], $number['i']);
                                                if($validator->isValidNumber($num)){
                                                    
                                                    $isValid = true;
                                                    $number['v'] = $validator->format($num, libphonenumber\PhoneNumberFormat::E164);
                                                    $number['i'] = $validator->getRegionCodeForNumber($num);
                                                    $number['c'] = $validator->getCountryCodeForRegion($number['i']);
                                                    $mType = $validator->getNumberType($num);
                                                    
                                                    
                                                }else{
                                                    
                                                    if(strlen($this->user->params['user_country'])==2){
                                                        $num = $validator->parse($number['r'],  strtoupper($this->user->params['user_country']));
                                                        if($validator->isValidNumber($num)){

                                                            $isValid = true;
                                                            $number['v'] = $validator->format($num, libphonenumber\PhoneNumberFormat::E164);
                                                            $number['i'] = $validator->getRegionCodeForNumber($num);
                                                            $number['c'] = $validator->getCountryCodeForRegion($number['i']);
                                                            $mType = $validator->getNumberType($num);

                                                        }
                                                    }
                                                }
                                                
                                            }
                                        }
                                        if($isValid){
                                            $ot = $number['t'];
                                            switch ($mType) {
                                                case 3:
                                                case 0:
                                                    if (!($ot >= 7 && $ot <= 9)){
                                                        $number['t'] = 7;
                                                    }
                                                    break;
                                                case 1:
                                                    if (!(($ot >= 1 && $ot < 6) || $ot == 13)){
                                                        $number['t'] = 1;
                                                    }
                                                    break;
                                                case 5:
                                                case 2:
                                                    if (!(($ot >= 1 && $ot <= 9 && $ot != 6) || $ot == 13)){
                                                        $number['t'] = 1;
                                                    }
                                                    break;
                                                default:
                                                    break;
                                            }
                                            
                                            $numbers[] = $number;
                                        }
                                    }
                                }
                                if(count($numbers)){
                                    $ad['cui']['p'] = $numbers;
                                    
                                    if (isset ($ad['other']) && $ad['other']){
                                        $other=$ad['other'];
                                        $other=preg_replace('/\x{200B}.*/u', '', $other);
                                        $adRTL=preg_match('/[\x{0621}-\x{064a}]/u', $other);
                                        $ad['other']=$other;
                                        $ad['other'].="\xE2\x80\x8B".$this->user->parseUserAdTime($ad['cui'],$ad['cut'],$adRTL);
                                    }
                                    if (isset ($ad['altother']) && $ad['altother']){
                                        $altOther=$ad['altother'];
                                        $altOther=preg_replace('/\x{200B}.*/u', '', $altOther);
                                        $altRTL=preg_match('/[\x{0621}-\x{064a}]/u', $altOther);
                                        $ad['altother']=$altOther;
                                        $ad['altother'].="\xE2\x80\x8B".$this->user->parseUserAdTime($ad['cui'],$ad['cut'],$altRTL);
                                    }
                                    
                                    //check is local number
                                    if($requireReview && $countryId && !$isMultiCountry && trim($ad['cui']['e'])==''){
                                        $countryCode = '+'.$this->urlRouter->countries[$countryId]['code'];
                                        //error_log("check #{$ad['id']}# ".$countryCode);
                                        $differentCodes = false;
                                        foreach($numbers as $number){
                                            //error_log("number ".$number['v']);
                                            //error_log("with ".substr($number['v'], 0, strlen($countryCode)));
                                            if(substr($number['v'], 0, strlen($countryCode)) != $countryCode){
                                                $differentCodes = true;
                                            }
                                        }
                                        if(!$differentCodes){
                                            //error_log("rollback review");
                                            $requireReview = 0;
                                        }
                                    }
                                }else{
                                    $wrongPhoneNumber = true;
                                }
                            }                            
                        $this->user->pending['post']['content']=json_encode($ad);
                        
                        $json_error = json_last_error();
                        
                        if($json_error==5){
                            if(isset($ad['userLOC'])){
                                $ad['userLOC']=$ad['ip'];
                                $this->user->pending['post']['content']=json_encode($ad);
                                $json_error = json_last_error();
                            }
                        }
                        $isSCAM = 0;
                        if($publish== 1 && isset($ad['cui']['e']) && strlen($ad['cui']['e'])>0){
                            $blockedEmailPatterns = addcslashes(implode('|', $this->urlRouter->cfg['restricted_email_domains']),'.');
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
                        
                        if($publish == 1 && isset($ad['budget']) && is_numeric($ad['budget']) && $ad['budget']> 0){
                            $publish = 4;
                        } 
                        
                        $adId = $this->user->pending['post']['id'];
                        
                        if($publish == 1){
                            $dbAd = $this->user->getPendingAds($adId,0,0,true);
                            if(isset($dbAd[0]['ID']) && $dbAd[0]['ID']){
                                $dbAd=$dbAd[0];
                                $current_time = time();
                                $isFeatured = isset($dbAd['FEATURED_DATE_ENDED']) && $dbAd['FEATURED_DATE_ENDED'] ? ($current_time < $dbAd['FEATURED_DATE_ENDED']) : false;
                                $isFeatureBooked = isset($dbAd['BO_DATE_ENDED']) && $dbAd['BO_DATE_ENDED'] ? ($current_time < $dbAd['BO_DATE_ENDED']) : false;
                                if($isFeatured || $isFeatureBooked){
                                    $publish = 4;
                                }
                            }
                 
                        }
                        
                        $this->user->update();
                        if(!$isSCAM){
                            $this->user->saveAd($publish);
                            $this->logAdmin($this->user->pending['post']['id'], $publish);
                        }
                        
                        $result=array(
                            'id'=>$this->user->pending['post']['id'],
                            'user'=>$this->user->pending['post']['user'],
                            'state'=>$ad['state'],
                            'text'=> $ad['other'],
                            'trsl'=> isset($ad['altother']) ? $ad['altother'] : ''
                        );
                        
                        /*
                        if( $this->user->info['level']==9 && 
                                $this->user->pending['post']['user']!=$this->user->info['id'] && 
                                $publish == 2 && 
                                $this->user->pending['post']['se'] == 190
                              ){
                                  $balance = $this->user->getStatement($this->user->pending['post']['user'], 0, true);
                                  if($balance && isset($balance['balance']) && !$balance['balance']){
                                      $rank = $this->user->getRank($this->user->pending['post']['user']);
                                      if($rank !== false){
                                          if($rank < 2){
                                            error_log('USER AUTO-SUSPENDED 72 '.$this->user->pending['post']['user']);
                                            $this->user->suspend($this->user->pending['post']['user'], 72);                                              
                                          }else{
                                            error_log('USER AUTO-SUSPENDED 24 '.$this->user->pending['post']['user']);
                                            $this->user->suspend($this->user->pending['post']['user'], 24);
                                          }
                                      }else{
                                          error_log('USER AUTO-SUSPENDED NO RANK');
                                      }
                                  }else{
                                      error_log('USER AUTO-SUSPENDED NO BALANCE');
                                  }
                        }*/
                        
                        $section_id = $this->user->pending['post']['se'];
                        
                        if( ($publish==1 || $publish==4) && $this->user->info['level']!=9) {
                            unset($this->user->pending['post']);
                            $this->user->update();
                        }
                        $this->setData($result,'I');
                        $this->process();
                        
                        if($isSCAM){
                            
                            $this->user->setLevel($this->user->info['id'],5);
                        
                        }elseif($requireReview){
                            $this->user->referrToSuperAdmin($adId);
                        }else{
                            
                            $status = 0;
                            if($publish==1 && $this->user->info['level']!=9 && $adId) {
                                $status = $this->user->detectDuplicateSuspension($ad['cui']); 
                                if($ad['rtl']){
                                    $msg = 'لقد تم ايقاف حسابك بشكل مؤقت نظراً للتكرار';
                                }else{
                                    $msg = 'your account is suspended due to repetition';
                                }
                                if($status == 1){
                                    $this->user->rejectAd($adId,$msg);
                                }else if(in_array($section_id,array(190,1179,540,1114))){
                                    $dupliactePending = $this->user->detectIfAdInPending($adId, $section_id, $ad['cui']);
                                    if($dupliactePending){
                                        if($ad['rtl']){
                                            $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                                        }else{
                                            $msg = 'There is another similar ad pending Editors\' approval';
                                        }
                                        $this->user->rejectAd($adId,$msg);
                                    }
                                }
                            }else if( ($publish ==1 || $publish==4) && $wrongPhoneNumber && $adId){
                                if($ad['rtl']){
                                    $msg = 'يرجى تصحيح رقم الهاتف أو تحديد رمز المنطقة إن لزم';
                                }else{
                                    $msg = 'please correct the phone number or specify the area code if applicable';
                                }
                                $this->user->rejectAd($adId,$msg);
                            }else if($publish == 4 && $isMultiCountry){
                                if($ad['rtl']){
                                    $msg = 'عذراً ولكن لا يمكن تمييز الاعلان في اكثر من بلد واحد';
                                }else{
                                    $msg = 'Sorry, you cannot publish premium ads targetting more than ONE country';
                                }
                                $this->user->rejectAd($adId,$msg);
                            }

                            if($ad['rtl']){
                                $this->urlRouter->siteLanguage='ar';
                                $this->lnIndex=0;
                            }else{
                                $this->lnIndex=1;
                                $this->urlRouter->siteLanguage='en';
                            }
                            $this->load_lang(array('main'));

                            if ($status!=5 && ($publish==1 || $publish==4) && isset($ad['video']) && $ad['video'][0]) {
                                require_once 'Zend/Loader.php';
                                Zend_Loader::loadClass('Zend_Gdata_YouTube');
                                Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                                Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                                $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                                $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                                try {
                                    $adSection = $this->getAdSection($ad);
                                    if(mb_strlen($adSection,'UTF-8')>60){
                                        $adSection = mb_substr($adSection, 0, 57,'UTF-8').'...';
                                    }
                                    $content = $ad['other'];
                                    $content=preg_replace('/\<.*?\>/u', '', $ad['other']);

                                    $entry = $yt->getVideoEntry($ad['video'][0],null,true);
                                    $editLink= $entry->getEditLink()->getHref();
                                    $entry->setVideoTitle($adSection);
                                    $entry->setVideoDescription($content);

                                    $yt->updateEntry($entry, $editLink);
                                }catch (Zend_Gdata_App_HttpException $httpException) {
                                    error_log($httpException->getRawResponseBody());
                                    //echo $httpException->getMessage();
                                    //$this->fail($httpException->getMessage());
                                    //var_dump($httpException->getMessage());
                                } catch (Zend_Gdata_App_Exception $e) {
                                    error_log($e->getMessage());
                                    //echo $e->getMessage();
                                    //var_dump($e->getMessage());
                                }
                            }
                        
                        }
                    }else $this->fail('101');
                /*}else {
                if ($this->user->info['id'] && isset ($_POST['fields']) && isset($this->user->pending['post']['id'])){
                    $fields=$_POST['fields'];
                    $text=$_POST['text'];
                    $title=ucfirst(trim($_POST['title']));
                    
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    $adContent['fields']=$fields;
                    $adContent['text']=ucfirst(trim($text));
                    $adContent['code']=$this->user->pending['post']['code'];
                    $adContent['loc']=$this->user->pending['post']['loc'];
                    $adContent['gloc']=$this->user->pending['post']['gloc'];
                    $adContent['tloc']=$this->user->pending['post']['tloc'];
                    $adContent['zloc']=$this->user->pending['post']['zloc'];
                    $adContent['dc']=$this->user->pending['post']['dc'];
                    $adContent['dci']=$this->user->pending['post']['dci'];
                    $adContent['dcn']=$this->user->pending['post']['dcn'];
                    $adContent['dcni']=$this->user->pending['post']['dcni'];
                    $adContent['ro']=$this->user->pending['post']['ro'];
                    if ($this->user->info['level']!=9) $adContent['userLvl']=$this->user->info['level'];
                    $adContent['version']=1;
                    if ($this->user->info['id'] == $this->user->pending['post']['user']){
                        $adContent['ip']=$_SERVER['REMOTE_ADDR'];
                        $adContent['agent']=$_SERVER['HTTP_USER_AGENT'];
                        $adContent['userLOC']=implode(" ,", geoip_record_by_name($_SERVER['REMOTE_ADDR']));
                    }
                    $this->user->pending['post']['content']=json_encode($adContent);
                    $this->user->pending['post']['title']=$title;
                    
                                        
                    
                    $publish=(int)$_POST['pub'];
                    if ($publish!=1 && ($publish!=2 || ($publish==2 && $this->user->info['level']!=9)))$publish=0;
                    $tmpPublish=$publish;
                    if ($this->user->info['level']==9 &&
                            $this->user->pending['post']['state']==1 && $publish==0) {
                        $publish=1;
                    }
                    
                    $this->user->update();
                    $this->user->saveAd($publish);
                    
                    if ($publish==2 && isset($adContent['video']) && $adContent['video'][0]) {
                        require_once 'Zend/Loader.php';
                        Zend_Loader::loadClass('Zend_Gdata_YouTube');
                        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                        Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                        $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                        $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                        try {
                        $entry = $yt->getVideoEntry($adContent['video'][0],null,true);
                        $editLink= $entry->getEditLink()->getHref();
                        $title=preg_replace('/[\.,;\-_+=$%^&@!?\/\\،؛{}|`~]/u', '', $title);
                        $entry->setVideoTitle($title);
                        $content=preg_replace('/[\.,;\-_+=$%^&@!?\/\\،؛{}|`~]/u', '', $adContent['text']);
                        $entry->setVideoDescription($content);
                        
                        $yt->updateEntry($entry, $editLink);
                        }catch (Zend_Gdata_App_HttpException $httpException) {
                            error_log($httpException->getRawResponseBody());
                            //echo $httpException->getMessage();
                            //$this->fail($httpException->getMessage());
                        } catch (Zend_Gdata_App_Exception $e) {
                            error_log($e->getMessage());
                            //echo $e->getMessage();
                            //$this->fail($e->getMessage());
                        }
                    }
                    
                    $this->setData($tmpPublish,'S');
                    $this->process();
                }else $this->fail('101');
                }*/
                break;
            case "ajax-logo":                
                require_once("lib/class.upload.php");
                $image_ok=false;
                $thumb_ok=false;
                $icon_ok=true;
                $handle=null;
                $ssh=null;
                $connected=false;
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(1,2,3,9)) && isset($_FILES['pic'])) {
                    
                    $path=$this->urlRouter->cfg['dir'].'/web/usr/';
                    $tempName=$_FILES['pic']['tmp_name'];
                    $_size=@getimagesize($tempName);                  
                    if (is_array($_size) && $_size[0] && $_size[1]) {
                    $handle = new Upload($_FILES['pic']);
                    if ($handle->uploaded) {
                        if (isset($this->user->info['options']['page']['logo'][0])){
                            $opic=$this->user->info['options']['page']['logo'][0];
                            $opath=$this->urlRouter->cfg['dir'].'/web/usr/';
                            @unlink($opath.'logo/'.$opic);
                            @unlink($opath.'icon/'.$opic);
                            @unlink($opath.'logo_org/'.$opic);
                            if ($this->urlRouter->cfg['server_id']>1) {
                                $ssh = ssh2_connect('h1.mourjan.com', 22);
                                if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                                    $sftp = ssh2_sftp($ssh);
                                    $connected=true;
                                    ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/logo_org/'.$opic);
                                    ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/logo/'.$opic);
                                    ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/icon/'.$opic);
                                }
                            }
                            unset($this->user->info['options']['page']['logo']);
                            $this->user->update();
                            $this->user->updateOptions();
                        }
                        $filename = $this->user->info['id']+$this->urlRouter->basePartnerId.'_'.time();
                        $handle->file_new_name_body   = $filename;
                        $handle->file_overwrite   = true;
                        $handle->Process($path.'logo_org/');
                        if ($handle->processed) {
                            $image_ok = true;
                            $extension='.'.$handle->file_src_name_ext;
                            list($image_width, $image_height) = getimagesize($path.'logo_org/'.$filename.$extension);
                        }else {
                            @unlink($tempName);
                        }
                        if ($image_ok) {
                            
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width > 200){
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = 200;
                            }elseif ($image_width < 200) {
                                $handle->image_default_color = '#F8F8F8';
                                $handle->image_ratio_fill        = 'TB';
                                $handle->image_x              = 200;
                            }
                            $handle->Process($path.'logo/');
                            if ($handle->processed) {
                                $thumb_ok=true;
                            }
                            
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width<$image_height) {
                                $handle->image_ratio_x        = true;
                                $handle->image_y              = 70;
                            }else {
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = 70;
                            }
                            $handle->Process($path.'icon/');
                            if ($handle->processed) {
                                $icon_ok=true;
                            }
                        }

                    }else {
                        @unlink($tempName);
                    }
                    
                    }elseif($tempName) {
                       @unlink($tempName);
                    }
                    
                }

                if ($image_ok && $thumb_ok && $icon_ok) {
                    $filename .= $extension;

                    if ($this->urlRouter->cfg['server_id']>1){
                        if (!$ssh) $ssh = ssh2_connect('h1.mourjan.com', 22);
                        if ($connected || ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                            if (!ssh2_scp_send($ssh, $path.'logo_org/'.$filename, '/var/www/mourjan/web/usr/logo_org/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'logo_org/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'logo/'.$filename, '/var/www/mourjan/web/usr/logo/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'logo_org/'.$filename);
                                @unlink($path.'logo/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'icon/'.$filename, '/var/www/mourjan/web/usr/icon/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'logo_org/'.$filename);
                                @unlink($path.'logo/'.$filename);
                                @unlink($path.'icon/'.$filename);
                            }
                        } else {
                            $image_ok=FALSE;
                        }
                    }
                }

                if ($image_ok && $thumb_ok && $icon_ok) {
                    if (!isset($this->user->info['options']))
                        $this->user->info['options']=array();
                    if (!isset($this->user->info['options']['page']))
                        $this->user->info['options']['page']=array();
                    if (!isset($this->user->info['options']['page']['logo']))
                        $this->user->info['options']['page']['logo']=array();
                    list($image_width, $image_height) = getimagesize($path.'logo/'.$filename);
                    $this->user->info['options']['page']['logo']=array($filename,$image_width,$image_height);
                    $this->user->update();
                    $this->user->updateOptions();
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(<?= '"'.$filename.'"' ?>,<?= '"'.$this->urlRouter->cfg['url_resources'].'/usr/logo/'.$filename.'"' ?>,"picLU","<?= $image_width ?>","<?= $image_height ?>");</script><?php
                }
                else {
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(false, false,"picLU");</script><?php
                }
                break;
            case "ajax-banner":                
                require_once("lib/class.upload.php");
                $image_ok=false;
                $thumb_ok=false;
                $handle=null;
                $ssh=null;
                $connected=false;
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(1,2,3,9)) && isset($_FILES['pic'])) {
                    
                    $path=$this->urlRouter->cfg['dir'].'/web/usr/';
                    $tempName=$_FILES['pic']['tmp_name'];
                    $_size=@getimagesize($tempName);                  
                    if (is_array($_size) && $_size[0] && $_size[1]) {
                    $handle = new Upload($_FILES['pic']);
                    if ($handle->uploaded) {
                        if(isset($this->user->info['options']['page']['banner'][0])) {                        
                            $opic=$this->user->info['options']['page']['banner'][0];
                            $opath=$this->urlRouter->cfg['dir'].'/web/usr/';
                            @unlink($opath.'banner/'.$opic);
                            @unlink($opath.'banner_org/'.$opic);
                            if ($this->urlRouter->cfg['server_id']>1) {
                                $ssh = ssh2_connect('h1.mourjan.com', 22);
                                if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                                    $sftp = ssh2_sftp($ssh);
                                    $connected=true;
                                    ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/banner_org/'.$opic);
                                    ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/banner/'.$opic);
                                }
                            }
                        }
                        
                        $filename = $this->user->info['id']+$this->urlRouter->basePartnerId.'_'.time();;
                        $handle->file_new_name_body   = $filename;
                        $handle->file_overwrite   = true;
                        $handle->Process($path.'banner_org/');
                        if ($handle->processed) {
                            $image_ok = true;
                            $extension='.'.$handle->file_src_name_ext;
                            list($image_width, $image_height) = getimagesize($path.'banner_org/'.$filename.$extension);
                        }else {
                            @unlink($tempName);
                        }
                        if ($image_ok) {
                            
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width<970){
                                $handle->image_default_color = '#FFFFFF';
                                $handle->image_ratio_fill        = 'TB';
                                $handle->image_x              = 970;
                            }elseif ($image_width<$image_height) {
                                $handle->image_ratio_x        = true;
                                $handle->image_y              = 200;
                            }else {
                                $handle->image_ratio_crop        = true;
                                $handle->image_x              = 970;
                            }
                            $handle->Process($path.'banner/');
                            if ($handle->processed) {
                                $thumb_ok=true;
                            }
                        }

                    }else {
                        @unlink($tempName);
                    }
                    
                    }elseif($tempName) {
                       @unlink($tempName);
                    }
                    
                }

                if ($image_ok && $thumb_ok) {
                    $filename .= $extension;

                    if ($this->urlRouter->cfg['server_id']>1){
                        if(!$ssh)$ssh = ssh2_connect('h1.mourjan.com', 22);
                        if ($connected || ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                            if (!ssh2_scp_send($ssh, $path.'banner_org/'.$filename, '/var/www/mourjan/web/usr/banner_org/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'banner_org/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'banner/'.$filename, '/var/www/mourjan/web/usr/banner/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'banner_org/'.$filename);
                                @unlink($path.'banner/'.$filename);
                            }
                        } else {
                            $image_ok=FALSE;
                        }
                    }
                }

                if ($image_ok && $thumb_ok) {
                    if (!isset($this->user->info['options']))
                        $this->user->info['options']=array();
                    if (!isset($this->user->info['options']['page']))
                        $this->user->info['options']['page']=array();
                    if (!isset($this->user->info['options']['page']['banner']))
                        $this->user->info['options']['page']['banner']=array();
                    
                    list($image_width, $image_height) = getimagesize($path.'banner/'.$filename);
                    $this->user->info['options']['page']['banner']=array($filename,$image_width,$image_height);
                    $this->user->update();
                    $this->user->updateOptions();
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(<?= '"'.$filename.'"' ?>,<?= '"'.$this->urlRouter->cfg['url_resources'].'/usr/banner/'.$filename.'"' ?>,"picBU","<?= $image_width ?>","<?= $image_height ?>");</script><?php
                }
                else {
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(false, false,"picBU");</script><?php
                }
                break;
            case "ajax-upload":
                require_once("lib/class.upload.php");
                $image_ok=false;
                $thumb_ok=false;
                $mobile_ok=false;
                $widget_ok=false;
                $handle=null;
                if ($this->user->info['id'] && isset($_FILES['pic']) && isset($this->user->pending['post']['id']) && $this->user->pending['post']['id']) {

                    $id=$this->user->pending['post']['id'];
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if (isset ($adContent['pics']) && count($adContent['pics'])>4) {
                        //do nothing
                    }else {
                    $picIndex=0;
                    if (isset ($adContent['pic_idx'])) {
                        $picIndex=$adContent['pic_idx'];
                    }
                    $path=$this->urlRouter->cfg['dir'].'/web/repos/';
                    $tempName=$_FILES['pic']['tmp_name'];
                    $_size=@getimagesize($tempName);                  
                    if (is_array($_size) && $_size[0] && $_size[1]) {
                    $handle = new Upload($_FILES['pic']);
                    if ($handle->uploaded) {
                        $filename = $id."_".$picIndex++;
                        $handle->file_new_name_body   = $filename;
                        $handle->file_overwrite   = true;
                        $handle->Process($path.'l/');
                        if ($handle->processed) {
                            $image_ok = true;
                            $extension='.'.$handle->file_src_name_ext;
                            list($image_width, $image_height) = getimagesize($path.'l/'.$filename.$extension);
                        }else {
                            @unlink($tempName);
                        }
                        if ($image_ok) {
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width>120) {
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = 120;
                            }else{
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = $image_width;
                            }
                            $handle->Process($path.'s/');
                            if ($handle->processed) {
                                $thumb_ok=true;
                            }
                            
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width>300) {
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = 300;
                            }else{
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = $image_width;
                            }
                            $handle->Process($path.'m/');
                            if ($handle->processed) {
                                $mobile_ok=true;
                            }

                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->image_resize         = true;
                            if ($image_width>648) {
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = 648;
                            }else{
                                $handle->image_ratio_y        = true;
                                $handle->image_x              = $image_width;
                            }
                            $handle->Process($path.'d/');
                            if ($handle->processed) {
                                list($image_width, $image_height) = getimagesize($path.'d/'.$filename.$extension);
                                $widget_ok=true;
                            }
                        }

                    }else {
                        @unlink($tempName);
                    }
                    
                    }elseif($tempName) {
                       @unlink($tempName);
                    }
                    
                    }
                }

                if ($image_ok && $thumb_ok && $mobile_ok && $widget_ok) {
                    $filename .= $extension;

                    if ($this->urlRouter->cfg['server_id']>1){
                        $ssh = ssh2_connect('h1.mourjan.com', 22);
                        if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                            if (!ssh2_scp_send($ssh, $path.'l/'.$filename, '/var/www/mourjan/web/repos/l/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'l/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'s/'.$filename, '/var/www/mourjan/web/repos/s/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'l/'.$filename);
                                @unlink($path.'s/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'d/'.$filename, '/var/www/mourjan/web/repos/d/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'d/'.$filename);
                                @unlink($path.'l/'.$filename);
                                @unlink($path.'s/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'m/'.$filename, '/var/www/mourjan/web/repos/m/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'m/'.$filename);
                                @unlink($path.'d/'.$filename);
                                @unlink($path.'l/'.$filename);
                                @unlink($path.'s/'.$filename);
                            }
                        } else {
                            $image_ok=FALSE;
                        }
                    }
                }

                if ($image_ok && $thumb_ok && $mobile_ok && $widget_ok) {
                    if (!isset($adContent['pics'])) {
                        $adContent['pics']=array();
                    }
                    $adContent['pics'][$filename]=array($image_width,$image_height);
                    $adContent['pic_idx']=$picIndex;
                    $isDefault=false;
                    if (count($adContent['pics'])==1) {
                        $adContent['pic_def']=$filename;
                        $isDefault=true;
                    }
                    if(isset($adContent['extra']['p']))$adContent['extra']['p']=1;

                    $this->user->pending['post']['content']=json_encode($adContent);
                    $this->user->update();
                    $this->user->saveAd();
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(<?= '"'.$filename.'"' ?>);</script><?php
                }
                else {
                    if ($handle)
                        error_log($handle->error);
                    else error_log('no user session');
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback();</script><?php
                }
                break;
            case "ajax-ilogo":
                require_once("lib/class.upload.php");
                $image_ok=false;
                $thumb_ok=false;
                $handle=null;
                if ($this->user->info['id'] && ($this->user->info['level']==8 || $this->user->info['level']==9) && isset($_FILES['pic'])) {

                    $id=$this->user->encodeId($this->user->info['id']);
                    $picIndex=$this->user->info['options']['HS']['logo-idx'];
                    
                    
                    $path=$this->urlRouter->cfg['dir'].'/web/usr/';
                    
                    $tempName=$_FILES['pic']['tmp_name'];
                    $_size=@getimagesize($tempName);             
                    
                    if (is_array($_size) && $_size[0] && $_size[1]) {
                        $image_width=$_size[0];
                        $image_height=$_size[1];
                        $image_render_x_height =ceil(($image_height*250)/$image_width);
                        //$image_render_y_height =ceil(($image_width*100)/$image_height);
                        $handle = new Upload($_FILES['pic']);
                        if ($handle->uploaded) {
                            $filename = $id.$picIndex;
                            $handle->file_new_name_body   = $filename;
                            $handle->file_overwrite   = true;
                            $handle->Process($path.'logo_org/');
                            if ($handle->processed) {
                                $image_ok = true;
                                $extension='.'.$handle->file_src_name_ext;
                            }else {
                                @unlink($tempName);
                            }
                            if ($image_ok) {
                                $handle->file_new_name_body   = $filename;
                                $handle->file_overwrite   = true;
                                $handle->image_resize         = true;
                                if ($image_width>=$image_height && $image_render_x_height<=100 ) {
                                    $handle->image_ratio_y        = true;
                                    $handle->image_x              = 250;
                                }else{
                                    $handle->image_ratio_x        = true;
                                    $handle->image_y              = 100;
                                }
                                $handle->Process($path.'logo/');
                                if ($handle->processed) {
                                    list($image_width, $image_height) = getimagesize($path.'logo/'.$filename.$extension);
                                    $thumb_ok=true;
                                }
                            }

                        }else {
                            @unlink($tempName);
                        }
                    
                    }elseif($tempName) {
                       @unlink($tempName);
                    }
                }

                if ($image_ok && $thumb_ok) {
                    $filename .= $extension;

                    if ($this->urlRouter->cfg['server_id']>1){
                        $ssh = ssh2_connect('h1.mourjan.com', 22);
                        if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                            if (!ssh2_scp_send($ssh, $path.'logo_org/'.$filename, '/var/www/mourjan/web/usr/logo_org/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'logo_org/'.$filename);
                            }
                            elseif (!ssh2_scp_send($ssh, $path.'logo/'.$filename, '/var/www/mourjan/web/usr/logo/'.$filename, 0664)) {
                                $image_ok = FALSE;
                                @unlink($path.'logo_org/'.$filename);
                                @unlink($path.'logo/'.$filename);
                            }
                        } else {
                            $image_ok=FALSE;
                        }
                    }
                }

                if ($image_ok && $thumb_ok) {
                    $this->user->info['options']['HS']['logo-idx']++;
                    $this->user->info['options']['HS']['logo']=array($filename,$image_width,$image_height);
                    $this->user->updateOptions();
                    $this->user->update();
                    if($this->user->info['options']['HS']['url']){
                        $filename = '<a href=\"'.$this->user->info['options']['HS']['url'].'\"><img src=\"'.$this->urlRouter->cfg['url_resources'].'/usr/logo/'.$filename.'\" /></a><span id=\"bt_logo\" class=\"edit\"></span>';
                    }else{
                        $filename = '<img src=\"'.$this->urlRouter->cfg['url_resources'].'/usr/logo/'.$filename.'\" /><span id=\"bt_logo\" class=\"edit\"></span>';
                    }
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback(<?= '"'.$filename.'"' ?>);</script><?php
                }
                else {
                    if ($handle)
                        error_log($handle->error);
                    else error_log('no user session');
                    ?><script type="text/javascript" language="javascript" defer>top.uploadCallback();</script><?php
                }
                break;
            case 'ajax-ifav':
                if (isset ($_POST['i']) && isset ($this->user->pending['post']['content'])) {
                    $fn=$_POST['i'];
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if (isset ($adContent['pics'])) {
                        $found=false;
                        foreach ($adContent['pics'] as $pic=>$val){
                            if($pic==$fn){
                                $found=true;
                                $adContent['pic_def']=$fn;
                                break;
                            }
                        }
                        if ($found){
                            $this->user->pending['post']['content']=json_encode($adContent);
                            $this->user->update();
                            $this->user->saveAd();
                            $this->process();
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case "ajax-gdel":
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(1,2,3,9))) {
                    if (isset($this->user->info['options']['page']['logo'][0])){
                        $pic=$this->user->info['options']['page']['logo'][0];
                        $path=$this->urlRouter->cfg['dir'].'/web/usr/';
                        @unlink($path.'logo/'.$pic);
                        @unlink($path.'icon/'.$pic);
                        @unlink($path.'logo_org/'.$pic);
                        if ($this->urlRouter->cfg['server_id']>1) {
                            $ssh = ssh2_connect('h1.mourjan.com', 22);
                            if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                                $sftp = ssh2_sftp($ssh);
                                ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/logo_org/'.$pic);
                                ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/logo/'.$pic);
                                ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/icon/'.$pic);
                            }
                        }
                        unset($this->user->info['options']['page']['logo']);
                        $this->user->update();
                        $this->user->updateOptions();
                    }
                    $this->process();
                }else $this->fail('101');
                break;
            case "ajax-bdel":
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(1,2,3,9))) {
                    if (isset($this->user->info['options']['page']['banner'][0])){
                        $pic=$this->user->info['options']['page']['banner'][0];
                        $path=$this->urlRouter->cfg['dir'].'/web/usr/';
                        @unlink($path.'banner/'.$pic);
                        @unlink($path.'banner_org/'.$pic);
                        if ($this->urlRouter->cfg['server_id']>1) {
                            $ssh = ssh2_connect('h1.mourjan.com', 22);
                            if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                                $sftp = ssh2_sftp($ssh);
                                ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/banner_org/'.$pic);
                                ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/usr/banner/'.$pic);
                            }
                        }
                        unset($this->user->info['options']['page']['banner']);
                        $this->user->update();
                        $this->user->updateOptions();
                    }
                    $this->process();
                }else $this->fail('101');
                break;
            case "ajax-idel":
                if (isset ($_POST['i']) && isset ($this->user->pending['post']['content'])) {
                    $fn=$_POST['i'];
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if (isset ($adContent['pics'])) {
                        $found=false;
                        $path=$this->urlRouter->cfg['dir'].'/web/repos/';
                        foreach ($adContent['pics'] as $pic=>$val){
                            if ($pic==$fn){
                                $found=true;
                                unset ($adContent['pics'][$pic]);
                                if ($adContent['pic_def']==$pic) {
                                    if (count($adContent['pics'])) {
                                        foreach ($adContent['pics'] as $p2=>$v2){
                                            $adContent['pic_def']=$p2;
                                            break;
                                        }
                                    }else $adContent['pic_def']='';
                                }
                                //@unlink($path.'l/'.$pic);
                                //@unlink($path.'d/'.$pic);
                                //@unlink($path.'s/'.$pic);
/*
                                if ($this->urlRouter->cfg['server_id']>1) {
                                    $ssh = ssh2_connect('h1.mourjan.com', 22);
                                    if (ssh2_auth_password($ssh, 'mourjan-sync', 'GQ71BUT2')) {
                                        $sftp = ssh2_sftp($ssh);
                                        ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/l/'.$pic);
                                        ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/s/'.$pic);
                                        ssh2_sftp_unlink($sftp, '/var/www/mourjan/web/repos/d/'.$pic);
                                    }
                                }
  */                              
                                break;
                            }
                        }
                        
                        //if ($found) {
                            $media = $this->urlRouter->db->queryResultArray("select * from media where filename = ?",[$fn],true);
                            if($media && count($media)){
                                $this->urlRouter->db->queryResultArray("delete from ad_media where ad_id = ? and media_id = ?",[$this->user->pending['post']['id'],$media[0]['ID']],true);
                            }
                            if(count($adContent['pics'])==0){
                                if(isset($adContent['extra']['p']))$adContent['extra']['p']=0;
                            }
                            $this->user->pending['post']['content']=json_encode($adContent);
                            $this->user->update();
                            $this->user->saveAd();
                            $this->setData($adContent['pic_def'],'def');
                            $this->process();
                        //}else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-approve':
                if ($this->user->info['level']==9 && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    if (is_numeric($id)){
                        if ($this->user->approveAd($id)) {
                            $this->process();
                            $this->logAdmin($id, 2);
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-help':
                if ($this->user->info['level']==9 && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    if (is_numeric($id)){
                        if ($this->user->referrToSuperAdmin($id)) {
                            $this->process();
                            
                            try {
            	
                                $redis = new Redis();
                                $data = [
                                    'cmd'   =>  'superAdmin',
                                    'data'  => [
                                        'id'=>$id
                                    ]
                                ];
                                if ($redis->connect('h8.mourjan.com', 6379, 1, NULL, 50)) {
                                    $redis->publish('editorial', json_encode($data));
                                }

                            } catch (RedisException $re) {}
                            
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-mpre':
                $ad_id = filter_input(INPUT_POST, 'i', FILTER_VALIDATE_INT) + 0;
                $coins = filter_input(INPUT_POST, 'c', FILTER_VALIDATE_INT) + 0;
                $user = filter_input(INPUT_POST, 'u', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

                $this->urlRouter->db->setWriteMode();  
                IF($ad_id && $coins && $this->user->info['id'] == $this->user->decodeId($user)){

                    $result = $this->urlRouter->db->queryResultArray("
                            select a.id, bo.id as bo_id, a.state   
                            from ad_user a 
                            left join t_ad_bo bo on bo.ad_id = a.id and bo.blocked = 0 
                            where a.id = ? and a.web_user_id = ?  
                            ",[$ad_id, $this->user->info['id']]);
                    
                    if($result && count($result)){
                        if($result[0]['STATE'] == 7){
                            $pass = true;
                            if($result[0]['BO_ID']){
                                $rs = $this->urlRouter->db->queryResultArray("
                                update t_ad_bo set blocked = 1 where id = ? returning blocked 
                                ",[$result[0]['BO_ID']],true);
                                if($rs && isset($rs[0]['BLOCKED'])){
                                    $pass = true;
                                }else{
                                    $pass = false;
                                }
                            }
                            if($pass){
                                $result = $this->urlRouter->db->queryResultArray(
                                "INSERT INTO T_AD_BO (AD_ID, OFFER_ID, CREDIT, BLOCKED) VALUES ".
                                "(?, ?, ?, 0) RETURNING ID", [$ad_id, 1, $coins], TRUE);

                                if($result && isset($result[0]['ID'])){ 
                                    $this->process();
                                }else{
                                    $this->fail('500');
                                }
                            }else{
                                $this->fail('500');
                            }
                        }else{
                            $this->fail('404');
                        }
                    }
                }else{
                    $this->fail('401');
                }
                break;
            case 'ajax-spre':
                $ad_id = filter_input(INPUT_POST, 'i', FILTER_VALIDATE_INT) + 0;
                $user = filter_input(INPUT_POST, 'u', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
                $lang = filter_input(INPUT_POST, 'hl', FILTER_SANITIZE_STRING, ['options'=>['default'=>'ar']]);
                IF($ad_id && $this->user->info['id'] == $this->user->decodeId($user)){
                    if(!in_array($lang,array('en','ar'))){
                        $lang = 'ar';
                    }
                    $this->urlRouter->db->setWriteMode();   
                    $result = $this->urlRouter->db->queryResultArray("
                            select a.id, 
                            IIF(featured.id is null, 0, DATEDIFF(SECOND, timestamp '01-01-1970 00:00:00', featured.ended_date)) feature_end 
                            from ad_user a 
                            left join t_ad_featured featured on featured.ad_id=a.id and current_timestamp between featured.added_date and featured.ended_date 
                            where a.id = ? and a.web_user_id = ?  
                            ",[$ad_id, $this->user->info['id']]);
                    if($result && count($result)){
                        $rs = $this->urlRouter->db->queryResultArray("
                                update t_ad_bo set blocked = 1 where ad_id = ?
                                ",[$ad_id],true);

                        if($rs && count($rs)){
                            $expire = $result[0]['FEATURE_END'] - time();
                            $dated ='';
                            if($expire > 0){
                                $d = floor( $expire / 3600);
                                if($d){
                                    if($lang == 'ar'){
                                        if($d == 1){
                                            $dated = 'ساعة';
                                        }else if($d==2){
                                            $dated = 'ساعتين';
                                        }else if($d < 11){
                                            $dated = $d.' ساعات';
                                        }else{
                                            $dated = $d.' ساعة';
                                        }
                                    }else{
                                        if($d == 1){
                                            $dated = '1 hour';
                                        }else{
                                            $dated = $d.' hours';
                                        }
                                    }
                                }
                                $d = floor( ($expire%3600) /60);
                                if($d){
                                    if($dated!=''){
                                        if($lang == 'ar'){
                                            $dated .= ' و';
                                        }else{
                                            $dated .= ' and ';
                                        }
                                    }
                                    if($lang == 'ar'){
                                        if($d == 1){
                                            $dated .= 'دقيقة';
                                        }else if($d==2){
                                            $dated .= 'دقيقتين';
                                        }else if($d < 11){
                                            $dated .= $d.' دقائق';
                                        }else{
                                            $dated .= $d.' دقيقة';
                                        }
                                    }else{
                                        if($d == 1){
                                            $dated .= '1 minute';
                                        }else{
                                            $dated .= $d.' minutes';
                                        }
                                    }
                                }
                            }
                            $this->setData($dated, 'end');
                            $this->process();
                        }else{
                            $this->fail('500');
                        }
                    }else{
                        $this->fail('404');
                    }
                }else{
                    $this->fail('401');
                }
                break;
            case 'ajax-reject':
                if ($this->user->info['level']==9 && isset ($_POST['i'])) {
                    $msg=trim($_POST['msg']);
                    $id=$_POST['i'];
                    $warn=$_POST['w'];
                    if (is_numeric($id)){
                        if ($this->user->rejectAd($id,$msg,0)) {
                            $this->process();
                            $this->logAdmin($id,3,$msg);
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-ahold':
                if ($this->user->info['id'] && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    if (is_numeric($id)){
                        if ($this->user->holdAd($id)) {
                            $this->process();
                            $this->logAdmin($id, 9);
                        }else {
                            $this->fail('103');
                        }
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-arenew':
                if ($this->user->info['id'] && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    if (is_numeric($id)){
                        $renew = true;
                        $status = 0;
                        if($this->user->info['level']!=9) {
                            
                            $ad = $this->user->getPendingAds($id);
                            if ($ad && isset($ad[0]['ID']) && $ad[0]['ID']) {
                                $ad=$ad[0];
                                $section_id = $ad['SECTION_ID'];
                                $ad = json_decode($ad['CONTENT'], TRUE);
                            
                                $status = $this->user->detectDuplicateSuspension($ad['cui']); 
                                if($ad['rtl']){
                                    $msg = 'لقد تم ايقاف حسابك بشكل مؤقت نظراً للتكرار';
                                }else{
                                    $msg = 'your account is suspended due to repetition';
                                }
                                if($status == 1){
                                    $renew= false;
                                    $this->user->rejectAd($id,$msg);
                                }else if(in_array($section_id,array(190,1179,540,1114))){
                                    $dupliactePending = $this->user->detectIfAdInPending($id, $section_id, $ad['cui']);
                                    if($dupliactePending){
                                        $renew= false;
                                        if($ad['rtl']){
                                            $msg = 'هنالك اعلان مماثل في لائحة الانتظار وبالنتظار موافقة محرري الموقع';
                                        }else{
                                            $msg = 'There is another similar ad pending Editors\' approval';
                                        }
                                        $this->user->rejectAd($id,$msg);
                                    }
                                }
                            }
                        }
                        if($renew){
                            if ($this->user->renewAd($id)) {
                                $this->process();
                            }else $this->fail('103');
                        }else{
                            $this->process();
                        }
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-pay':
                if ($this->user->info['id'] && isset ($_POST['i'])) {
                    $id=$this->post('i','numeric');
                    $lang = $this->post('hl');
                    if (is_numeric($id)){
                        $product=$this->urlRouter->db->queryResultArray(
                            "select * from product where id=?",
                            array($id), true);
                        if(isset($product[0]['ID']) && $product[0]['ID']){
                            $product = $product[0];
                            $product['MCU'] = (int)$product['MCU'];
                            $product['USD_PRICE'] = number_format($product['USD_PRICE'],2);
                            $orderId='';
                            $order=$this->urlRouter->db->queryResultArray(
                                "insert into t_order (uid,currency_id,amount,debit,credit,usd_value,server_id) values (?,?,?,?,?,?,?) returning id",
                                [
                                    $this->user->info['id'],
                                    'USD',
                                    $product['USD_PRICE'],
                                    0,
                                    $product['MCU'],
                                    $product['USD_PRICE'],
                                    $this->urlRouter->cfg['server_id']
                                ], true);
                            if(isset($order[0]['ID']) && $order[0]['ID']){
                                $orderId=$this->user->info['id'].'-'.$order[0]['ID'];   
                                
                                require_once $this->urlRouter->cfg['dir'].'/core/lib/PayfortIntegration.php';
                                
                                $objFort = new PayfortIntegration();
                                $objFort->setAmount($product['USD_PRICE']);
                                $objFort->setCustomerEmail($this->user->info['email']);
                                $objFort->setItemName($product['MCU'].($lang!='ar' ? ' mourjan gold':' ذهبية مرجان'));
                                //$objFort->setItemBillName((1 ? ' mourjan gold':' ذهبية مرجان'));
                                $objFort->setMerchantReference($orderId);
                                $objFort->setLanguage($lang);
                                $objFort->setCommand('PURCHASE');
                                
                                $form = $objFort->getRedirectionData('');
                                $formData = '';
                                foreach($form['params'] as $k => $v){
                                    $formData .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
                                }
                                $this->setData($formData, "D");
                                $this->setData($form['url'], "U");
                                /*
                                $product['MCU'] = (int)$product['MCU'];
                                $passPhrase = $this->urlRouter->cfg['payfor_pass_phrase_out'];
                                $sandbox = $this->urlRouter->cfg['server_id']==99 ? true : false;
                                $access_code = $this->urlRouter->cfg['payfor_access_code'];
                                $webscr = $sandbox ? $this->urlRouter->cfg['payfor_url_test'] : $this->urlRouter->cfg['payfor_url'];
                                $return_url = $this->urlRouter->cfg['host'] . '/buyu/' . ($this->urlRouter->siteLanguage!='ar' ? $this->urlRouter->siteLanguage . '/' : '');
                                $merchant_id = $this->urlRouter->cfg['payfor_merchant_id'];

                                $requestParams = [
                                    'access_code' => $access_code ,
                                    'amount' => $product['USD_PRICE'],
                                    'currency' => 'USD',
                                    'customer_email' => $this->user->info['email'],
                                    'merchant_reference' => '1-3',
                                    'order_description' =>  $product['MCU'].(1 ? ' mourjan gold':' ذهبية مرجان'),
                                    'language' => $lang,
                                    'merchant_identifier' => $merchant_id,
                                    'command' => 'PURCHASE',
                                    'return_url'=>$return_url,
                                    'dynamic_descriptor' => $product['MCU']. ' mourjan gold'
                                ];

                                ksort($requestParams);

                                $signature = '';
                                foreach ($requestParams as $a => $b) {
                                    $signature .= $a . '=' .$b;
                                }
                                $signature = $passPhrase.$signature.$passPhrase; 
                                
                                $signature = hash('sha256', $signature);  
                                
                                

                                $this->setData($signature, "S");
                                $this->setData($orderId, "O");
                                 * 
                                 */
                                $this->process();
                                
                            }else $this->fail('104');                            
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-adel':
                if ($this->user->info['id'] && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    $hide=$this->post('h','numeric');
                    if (is_numeric($id)){
                        if ($this->user->deletePendingAd($id,$hide)) 
                                $this->process();
                        else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case "ajax-sections":
                $rootId=$this->post('ro', 'uint');
                $siteLanguage=strtolower($this->post('sl', 'filter'));
                if ($rootId && $siteLanguage) {
                    $sections=$this->urlRouter->db->queryCacheResultSimpleArray(
                    "req_sections_{$siteLanguage}_{$rootId}",
                    "select s.ID,s.name_".$siteLanguage."
                    from section s
                    left join category c on c.id=s.category_id
                    where c.root_id={$rootId} and s.id not in (19,29,63,105,114)
                    order by s.NAME_{$siteLanguage}",
                    null, 0, $this->urlRouter->cfg['ttl_long']);

                    $countPerColumn=ceil(count($sections)/5);
                    $res='<ul>';
                    $i=0;
                    $lastChar='';
                    $firstChar='';
                    foreach ($sections as $section) {
                        $firstChar=mb_substr($section[1], 0, 1, 'UTF8');
                        if ($lastChar!=$firstChar) {
                            if ($i>=$countPerColumn) {
                                $res.='</ul><ul>';
                                $i=0;
                            }
                            $lastChar=$firstChar;
                            $res.='<li><h5>'.$lastChar.'</h5></li>';
                        }
                        $res.='<li><a onclick="sd('.$section[0].')">'.$section[1].'</a></li>';
                        $i++;
                    }
                    $res.='</ul>';
                    $this->setData($res, "S");
                    $this->process();
                }else $this->fail();
                break;
            case 'ajax-ipage':
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(8,9))) {
                    $form=$this->post('f');
                    $fields=$this->post('i', 'array');
                    if ($form && is_array($fields)){
                        $error=false;
                        switch($form){
                            case 'url':
                                $url = trim($fields[0]);
                                if($url && !preg_match('/^(?:http|https)\:\/\//',$url))
                                    $url='http://'.$url;
                                $this->user->info['options']['HS']['url']=$url;
                                $res = '';
                                if($url){
                                    if(count($this->user->info['options']['HS']['logo'])){
                                        $res='<a href="'.$this->user->info['options']['HS']['url'].'"><img width="'.$this->user->info['options']['HS']['logo'][1].'" height="'.$this->user->info['options']['HS']['logo'][2].'" src="'.$this->urlRouter->cfg['url_resources'].'/usr/logo/'.$this->user->info['options']['HS']['logo'][0].'" /></a><span id="bt_logo" class="edit"></span>';
                                    }else{
                                        $res='<img height="" src="'.$this->urlRouter->cfg['url_css'].'/i/photo.png" /> <a id="bt_logo" href="">add logo</a>';
                                    }
                                }else{
                                    if(count($this->user->info['options']['HS']['logo'])){
                                        $res='<img width="'.$this->user->info['options']['HS']['logo'][1].'" height="'.$this->user->info['options']['HS']['logo'][2].'" src="'.$this->urlRouter->cfg['url_resources'].'/usr/logo/'.$this->user->info['options']['HS']['logo'][0].'" /><span id="bt_logo" class="edit"></span>';
                                    }else{
                                        $res='<img height="" src="'.$this->urlRouter->cfg['url_css'].'/i/photo.png" /> <a id="bt_logo" href="">add logo</a>';
                                    }
                                }
                                $this->setData($res,'r');
                                $this->setData($url,'a');
                                break;
                            case 'offer':
                                $text = $fields[0];
                                $text = trim(preg_replace('/\<.*?\>/','',$text));
                                $fields[1] = $link = trim($fields[1]);
                                if(strlen($text)){
                                    if(0 && $link && filter_var($link, FILTER_VALIDATE_URL)===false){
                                        $error = '103';
                                    }else{
                                        if(!preg_match('/^(?:http|https)\:\/\//',$fields[1]))
                                            $fields[1]='http://'.$fields[1];
                                        $this->user->info['options']['HS']['offer']=$fields;
                                        $fields[0]=nl2br($text);
                                    }
                                }else{
                                    $fields=array();
                                    $this->user->info['options']['HS']['offer']=array();
                                }
                                if(!$error){
                                    $this->setData($fields,'r');
                                }
                                break;
                            case 'contact':
                                $text = $fields[0];
                                $text = trim(preg_replace('/\<.*?\>/','',$text));
                                if(strlen($text)){
                                    $this->user->info['options']['HS']['contact']=$text;
                                    $text = nl2br($text);
                                }else{
                                    $text='';
                                    $this->user->info['options']['HS']['contact']='';
                                }
                                if(!$error){
                                    $this->setData($text,'r');
                                }
                                break;
                            case 'links':
                                $text = $fields[0];
                                $text = trim(preg_replace('/\<.*?\>/','',$text));
                                $fields[1] = $link = trim($fields[1]);
                                $idx = !is_numeric($fields[2]) ? null : $fields[2];
                                if(!isset($this->user->info['options']['HS']['links']))
                                    $this->user->info['options']['HS']['links']=array();
                                $count = count($this->user->info['options']['HS']['links']);
                                if($count<6 || !is_null($idx)){
                                    if(strlen($text)){
                                        if(0 && $link && filter_var($link, FILTER_VALIDATE_URL)===false){
                                            $error = '103';
                                        }else{
                                            $fields[0]=$text;
                                            if(!preg_match('/^(?:http|https)\:\/\//',$fields[1]))
                                                $fields[1]='http://'.$fields[1];
                                            if(!is_null($idx) && isset($this->user->info['options']['HS']['links'][$idx]))
                                                $this->user->info['options']['HS']['links'][$idx]=$fields;
                                            else
                                                $this->user->info['options']['HS']['links'][]=$fields;
                                            $this->setData($fields,'r');
                                        }
                                    }
                                }else{
                                    $error = '104';
                                }
                                break;
                            case 'links_all':
                                $links = $fields[0];
                                if($links==-1)$links=array();
                                if(!isset($this->user->info['options']['HS']['links']))
                                    $this->user->info['options']['HS']['links']=array();
                                $this->user->info['options']['HS']['links']=$links;
                                $this->setData($fields,'r');
                                break;
                            default:
                                $error = '102';
                                break;
                        }
                        if($error){                            
                            $this->fail($error);
                        }else{
                            $this->user->updateOptions();
                            $this->user->update();
                            $this->process();
                        }
                    }
                }else $this->fail('101');
                break;
            case 'ajax-page':
                $lang=$this->post('lang');
                if ($lang!=='en' && $lang!=='ar') $lang='ar';
                $this->load_lang(array('account','profile'), $lang);
                if ($this->user->info['id'] && in_array($this->user->info['level'], array(1,2,3,9))) {
                    $form=$this->post('form');
                    $fields=$this->post('fields', 'array');
                    if ($form && is_array($fields)){
                        try{
                        switch($form){
                            case 'uri':
                                $this->load_lang(array('main'), $lang);
                                if (isset($fields['uri']) && $fields['uri'] && !preg_match('/[^a-z\-\.0-9]/u', $fields['uri'])){
                                    $name=$fields['uri'];
                                    if (!in_array($name, array('mourjan','lakis','robert','bassel','morjan','mrjan','morgan','merjan','morjen','morgen','mrjn')) && $this->user->checkUriAvailability($name)){
                                        if (!isset($this->user->info['options']))
                                            $this->user->info['options']=array();
                                        if (!isset($this->user->info['options']['page']))
                                            $this->user->info['options']['page']=array();
                                        $this->setData(1,'ok');
                                        if (isset($this->user->info['options']['page']['uri']) && $this->user->info['options']['page']['uri']){
                                            $this->fail($this->lang['uriAlreadySet'].' <b>'.$this->urlRouter->cfg['host'].'/'.$this->user->info['options']['page']['uri'].'</b>');
                                        }else {
                                            $this->user->info['options']['page']['uri']=$name;
                                            $this->user->update();
                                            $this->user->updateOptions();
                                            $this->fail($this->lang['uriSet'].' <b>'.$this->urlRouter->cfg['host'].'/'.$name.'</b>');
                                        }
                                    }else {
                                        $this->fail('<b>'.$name.'</b> '.stripcslashes($this->lang['uriTaken']));
                                    }
                                }else $this->fail(stripcslashes($this->lang['uriName']));
                                break;
                            case 'links':
                                if (!isset($this->user->info['options']))
                                    $this->user->info['options']=array();
                                if (!isset($this->user->info['options']['page']))
                                    $this->user->info['options']['page']=array();
                                if (!isset($this->user->info['options']['page']['links']))
                                    $this->user->info['options']['page']['links']=array();
                                $result=array();
                                $social='';
                                foreach($fields as $key => $field){
                                    $pass=true;
                                    $website=trim($field);
                                    if (isset($this->user->info['options']['page']['links'][$key]) 
                                            && $this->user->info['options']['page']['links'][$key]==$website) {
                                        $result[$key]=array('value',$website);
                                        if ($website) {
                                            switch($key){
                                                case 'fb':
                                                    $test='http://www.facebook.com/'.$website;
                                                    if($social) $social.=' - ';
                                                    $social.="<a targe='blank' href='".$test."'>Facebook</a>";
                                                    break;
                                                case 'tw':
                                                    $test='http://twitter.com/'.$website;
                                                    if($social) $social.=' - ';
                                                    $social.="<a targe='blank' href='".$test."'>Twitter</a>";
                                                    break;
                                                case 'gp':
                                                    $test='http://plus.google.com/'.$website;
                                                    if($social) $social.=' - ';
                                                    $social.="<a targe='blank' href='".$test."'>Google+</a>";
                                                    break;
                                                case 'lk':
                                                    $test='http://www.linkedin.com/in/'.$website;
                                                    if($social) $social.=' - ';
                                                    $social.="<a targe='blank' href='".$test."'>LinkedIn</a>";
                                                    break;
                                                default:
                                                    $test='';
                                                    break;
                                            }
                                        }
                                        continue;
                                    }
                                    if ($website) {
                                        switch($key){
                                            case 'fb':
                                                $test='http://www.facebook.com/'.$website;
                                                if($social) $social.=' - ';
                                                $social.="<a targe='blank' href='".$test."'>Facebook</a>";
                                                break;
                                            case 'tw':
                                                $test='http://twitter.com/'.$website;
                                                if($social) $social.=' - ';
                                                $social.="<a targe='blank' href='".$test."'>Twitter</a>";
                                                break;
                                            case 'gp':
                                                $test='http://plus.google.com/'.$website;
                                                if($social) $social.=' - ';
                                                $social.="<a targe='blank' href='".$test."'>Google+</a>";
                                                break;
                                            case 'lk':
                                                $test='http://www.linkedin.com/in/'.$website;
                                                if($social) $social.=' - ';
                                                $social.="<a targe='blank' href='".$test."'>LinkedIn</a>";
                                                break;
                                            default:
                                                $test='';
                                                break;
                                        }
                                        if ($test && !$this->pingUrl($test)){
                                            $pass=false;
                                        }
                                        if ($pass){
                                            $result[$key]=array('value',$website);
                                            $this->user->info['options']['page']['links'][$key]=$website;
                                        }else {
                                            $result[$key]=array('error',  $this->lang['valid_'.$key]);
                                        }
                                    }else {
                                        unset($this->user->info['options']['page']['links'][$key]);
                                    }
                                }
                                $this->user->update();
                                $this->user->updateOptions();
                                $result['social']=array('social',$social);
                                $this->setData($result,'fields');
                                $this->process();
                                break;
                            case 'email':
                                if (isset($fields['email']) && ($fields['email']=="" || preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)\b/', $fields['email'])) ) {
                                        if ($fields['email']!="" && (!isset($this->user->info['options']['page']['email']) || 
                                                $this->user->info['options']['page']['email']!=$fields['email']) && 
                                                (isset($this->user->info['email']) && $this->user->info['email']!=$fields['email'])
                                                ) {
                                            require_once $this->dir.'/bin/utils/MourjanMail.php';
                                            
                                            $sessionKey=md5($this->sid.$this->user->info['id'].$this->user->info['provider'].time());
                                            $verifyLink=$this->host.'/page/'.($lang=='ar'?'':$lang.'/').'?action=verify&key='.urlencode($sessionKey);
                                            
                                            $mailer=new MourjanMail($this->urlRouter->cfg, $this->user->info['options']['lang']);
                                            if ($mailer->sendPageEmailValidation($fields['email'],$verifyLink,$this->user->info['name'])){
                                                $this->user->info['options']['bmail']=$fields['email'];
                                                $this->user->info['options']['bmailKey']=$sessionKey;
                                                $this->user->update();
                                                $this->user->updateOptions();
                                                $result=array();
                                                $result['email']=array('value', $fields['email'], '<ok class="ar">'.preg_replace('/{email}/', $fields['email'],$this->lang['emailSent']).'</ok>');
                                                $this->setData($result,'fields');
                                                $this->process();
                                            }else $this->fail($this->lang['systemErr']);
                                        }else {
                                            unset($this->user->info['options']['bmail']);
                                            unset($this->user->info['options']['bmailKey']);
                                            if (!isset($this->user->info['options']))
                                                $this->user->info['options']=array();
                                            if (!isset($this->user->info['options']['page']))
                                                $this->user->info['options']['page']=array();
                                            $this->user->info['options']['page']['email']=$fields['email'];
                                            $this->user->update();
                                            $this->user->updateOptions();
                                            $result=array();
                                            $result['email']=array('value', $fields['email']);
                                            $this->setData($result,'fields');
                                            $this->process();
                                        }
                                }else {
                                    $fields['email']=$this->lang['validEmail'];
                                    $this->setData($fields,'fields');
                                    $this->fail($this->lang['wrongInfo']);
                                }
                                break;
                            case 'contact':
                                if (isset($fields['c']) && isset($fields['m']) && isset($fields['n']) && is_numeric($fields['n'])){
                                    if (!isset($this->user->info['options']))
                                        $this->user->info['options']=array();
                                    if (!isset($this->user->info['options']['page']))
                                        $this->user->info['options']['page']=array();
                                    if (!isset($this->user->info['options']['page']['contact']))
                                        $this->user->info['options']['page']['contact']=array();
                                    $this->user->info['options']['page']['contact'][]=array($fields['m'],$fields['c'],(int)$fields['n']);
                                    $this->user->update();
                                    $this->user->updateOptions();
                                    $result=$this->user->formatContactNumbers($this->user->info['options']['page']['contact'], $this->lang);
                                    $this->setData($result, 'nums');
                                    $this->process($result);
                                }else {
                                    $this->fail('103');
                                }
                                break;
                            case 'rmnum':
                                if (isset($fields['idx'])){
                                    if (isset($this->user->info['options']['page']['contact'][$fields['idx']]))
                                        unset($this->user->info['options']['page']['contact'][$fields['idx']]);
                                    $nums = array();
                                    $i=0;
                                    foreach ($this->user->info['options']['page']['contact'] as $contact){
                                        $nums[$i++]=$contact;
                                        
                                    }
                                    $this->user->info['options']['page']['contact']=$nums;
                                    $this->user->update();
                                    $this->user->updateOptions();
                                    $result=$this->user->formatContactNumbers($this->user->info['options']['page']['contact'], $this->lang);
                                    $this->setData($result, 'nums');
                                    $this->process($result);
                                }else {
                                    $this->fail('103');
                                }
                                break;
                            case 'website':
                                $pass=true;
                                if (isset($fields['website'])){
                                    $website=$fields['website'] ? trim($fields['website']) : '';
                                    if ($website) {
                                        if (substr($website, 0,7)!='http://' && substr($website, 0,8)!='https://')
                                                $website='http://'.$website;
                                         if (!$this->pingUrl($website))
                                            $pass=false;
                                    }
                                    if ($pass){
                                        if (!isset($this->user->info['options']))
                                            $this->user->info['options']=array();
                                        if (!isset($this->user->info['options']['page']))
                                            $this->user->info['options']['page']=array();
                                        $this->user->info['options']['page']['url']=$website;
                                        $this->user->update();
                                        
                                        $this->user->updateOptions();

                                        $result=array(
                                            'website'=>array('value',  utf8_encode($website))
                                        );
                                        $this->setData($result,'fields');
                                        $this->process();
                                    }else {
                                        $result=array(
                                            'website'=>$this->lang['validWebsite']
                                        );
                                        $this->setData($result,'fields');
                                        $this->fail($this->lang['wrongInfo']);
                                    }
                                }else {
                                    $this->fail('103');
                                }
                                break;
                            case 'title':
                                $pass=true;
                                $errorFields=array();
                                if (!(isset($fields['titleEn']) && !preg_match('/[^\s0-9a-zA-Z.\-\x{0621}-\x{0669}]/u', $fields['titleEn']))) { 
                                    $errorFields['titleEn']=$this->lang['validTitle'];
                                    $pass=false;
                                }
                                if (!(isset($fields['titleAr']) && !preg_match('/[^\s0-9a-zA-Z.\-\x{0621}-\x{0669}]/u', $fields['titleAr']))) { 
                                    $errorFields['titleAr']=$this->lang['validTitle'];
                                    $pass=false;
                                }
                                if ($pass) {
                                    $fields['titleEn']=trim(preg_replace("/<.*?>/", "", $fields['titleEn']));
                                    $fields['titleAr']=trim(preg_replace("/<.*?>/", "", $fields['titleAr']));
                                    if (!isset($this->user->info['options']))
                                        $this->user->info['options']=array();
                                    if (!isset($this->user->info['options']['page']))
                                        $this->user->info['options']['page']=array();
                                    if (!isset($this->user->info['options']['page']['t']))
                                        $this->user->info['options']['page']['t']=array();
                                    $this->user->info['options']['page']['t']['en']=$fields['titleEn'];
                                    $this->user->info['options']['page']['t']['ar']=$fields['titleAr'];
                                    
                                    $this->user->update();
                                    $this->user->updateOptions();
                                    /***********************************/
                                    $pageTitle='<i>'.$this->lang['missingTitle'].'</i>';
                                    if ($fields['titleEn']==$fields['titleAr'] && $fields['titleEn']) {
                                        $pageTitle=$fields['titleEn'];
                                    }elseif ($lang=='ar'){
                                        if ($fields['titleAr']){
                                            $pageTitle=$fields['titleAr'];
                                            if ($pageTitle && $fields['titleEn'])$pageTitle.=' - '.$fields['titleEn'];
                                        }elseif ($fields['titleEn'])$pageTitle=$fields['titleEn'];
                                    }else {            
                                        if ($fields['titleEn']){
                                            $pageTitle=$fields['titleEn'];
                                            if ($pageTitle && $fields['titleAr'])$pageTitle.=' - '.$fields['titleAr'];
                                        }elseif ($fields['titleAr'])$pageTitle=$fields['titleAr'];
                                    }
                                    /***********************************/
                                    $result=array(
                                        'titleEn'=>array('value',$fields['titleEn']),
                                        'titleAr'=>array('value',$fields['titleAr']),
                                        'title'=>array('value',$pageTitle)
                                    );
                                    
                                    $this->setData($result,'fields');
                                    $this->process();
                                }else {
                                    $this->setData($errorFields,'fields');
                                    $this->fail($this->lang['wrongInfo']);
                                }
                                break;
                            case 'address':
                                $pass=true;
                                $errorFields=array();
                                if (isset($fields['addressEn']) && isset($fields['addressAr'])) { 
                                    $fields['addressEn']=trim(preg_replace("/<.*?>/", "", $fields['addressEn']));
                                    $fields['addressAr']=trim(preg_replace("/<.*?>/", "", $fields['addressAr']));
                                    $addressEn= nl2br($fields['addressEn']);
                                    $addressAr= nl2br($fields['addressAr']);
                                    if (!isset($this->user->info['options']))
                                        $this->user->info['options']=array();
                                    if (!isset($this->user->info['options']['page']))
                                        $this->user->info['options']['page']=array();
                                    $this->user->info['options']['page']['adrEn']=$addressEn;
                                    $this->user->info['options']['page']['adrAr']=$addressAr;
                                    
                                    $this->user->update();
                                    $this->user->updateOptions();
                                    /***********************************/
                                    $result=array(
                                        'addressEn'=>array('value',$fields['addressEn']),
                                        'addressAr'=>array('value',$fields['addressAr'])
                                    );
                                    
                                    $this->setData($result,'fields');
                                    $this->process();
                                }else {
                                    $this->fail($this->lang['wrongInfo']);
                                }
                                break;
                            case 'desc':
                                $pass=true;
                                $errorFields=array();
                                if (isset($fields['descEn']) && isset($fields['descAr'])) {
                                   
                                    $fields['descEn']=trim(preg_replace("/<.*?>/", "", $fields['descEn']));
                                    $fields['descAr']=trim(preg_replace("/<.*?>/", "", $fields['descAr']));
                                    $descEn= nl2br($fields['descEn']);
                                    $descAr = nl2br($fields['descAr']);
                                    
                                    if (!isset($this->user->info['options']))
                                        $this->user->info['options']=array();
                                    if (!isset($this->user->info['options']['page']))
                                        $this->user->info['options']['page']=array();
                                    $this->user->info['options']['page']['descEn']=$descEn;
                                    $this->user->info['options']['page']['descAr']=$descAr;
                                    
                                    $this->user->update();
                                    $this->user->updateOptions();
                                    $result=array(
                                        'descEn'=>array('value',$fields['descEn']),
                                        'descAr'=>array('value',$fields['descAr'])
                                    );
                                    $this->setData($result,'fields');
                                    $this->process();
                                }else {
                                    $this->fail($this->lang['wrongInfo']);
                                }

                                break;
                            default:
                                $this->fail('102');
                                break;
                        }
                        }catch(Exception $ex){
                            error_log($ex->getMessage());
                            $this->fail('103');
                        }
                    }else $this->fail('101');
                }else $this->fail($this->lang['sessionTO']);
                break;
            case 'ajax-page-loc':
                if ($this->user->info['id']) {
                    $loc=$this->post('loc','filter');
                    $lat=$this->post('lat');
                    $lon=$this->post('lon');
                    if (is_numeric($lat) && is_numeric($lon)){
                    if (!isset($this->user->info['options']))
                        $this->user->info['options']=array();
                    if (!isset($this->user->info['options']['page']))
                        $this->user->info['options']['page']=array();
                    if (!isset($this->user->info['options']['page']['loc']))
                        $this->user->info['options']['page']['loc']=array();
                    $this->user->info['options']['page']['loc']['name']=$loc;
                    $this->user->info['options']['page']['loc']['lat']=$lat;
                    $this->user->info['options']['page']['loc']['lon']=$lon;
                    $this->user->update();
                    $this->user->updateOptions();
                    $this->process();
                    }else $this->fail('102');
                }else $this->fail('101');
                break; 
            case 'ajax-account':
                $lang=$this->post('lang');
                if ($lang!=='en' && $lang!=='ar') $lang='ar';
                $this->load_lang(array('account'), $lang);
                if ($this->user->info['id']) {
                    $form=$this->post('form');
                    $fields=$this->post('fields', 'array');
                    if ($form && is_array($fields)){
                        switch($form){
                            case 'lang':
                                if (isset($fields['lang']) && in_array($fields['lang'],array('en','ar')) ) {
                                    if (isset($this->user->info['options']['lang']) && $this->user->info['options']['lang']==$fields['lang']){
                                        $result=array();
                                        $result['lang']=array('value',$fields['lang'],($fields['lang']=='ar' ? 'العربية' : 'English' ));
                                        $this->setData($result,'fields');
                                        $this->process();
                                    }else {
                                        $this->user->info['options']['lang']=$fields['lang'];
                                        $this->user->update();
                                        $this->user->updateOptions();
                                        $result=array();
                                        $result['lang']=array('value',$fields['lang'],($fields['lang']=='ar' ? 'العربية' : 'English' ));
                                        $this->setData($result,'fields');
                                        $this->process();
                                    }
                                }else {
                                    $this->fail('103');
                                }
                                break;
                            case 'name':
                                if (isset($fields['name']) && mb_strlen($fields['name'])>2 && !preg_match('/[0-9]|[\,\.\'\{}\[\]\@\#\$\%\^\&\*\-\_\+\=\(\)\~\`\?\/\\\]/', $fields['name']) ) {
                                    if ($this->user->info['name']==$fields['name']){
                                        $result=array();
                                        $result['name']=array('value',$fields['name']);
                                        $this->setData($result,'fields');
                                        $this->process();
                                    }else {
                                        if ($this->urlRouter->db->queryResultArray('update web_users set user_name=? where id=?',array($fields['name'],$this->user->info['id']), true)) {
                                            $result=array();
                                            $result['name']=array('value',$fields['name']);
                                            $this->setData($result,'fields');
                                            $this->user->info['name']=$fields['name'];
                                            $this->user->update();
                                            $this->process();
                                        }else $this->fail($this->lang['systemErr']);
                                    }
                                }else {
                                    $fields['name']=$this->lang['validName'];
                                    $this->setData($fields,'fields');
                                    $this->fail($this->lang['wrongInfo']);
                                }
                                break;
                            case 'email':
                                if ($this->user->info['id']){
                                    if(isset($fields['email']) && preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)\b/', $fields['email']) ) {
                                        if ($this->user->info['email']!=$fields['email']) {
                                            require_once $this->dir.'/bin/utils/MourjanMail.php';
                                            
                                            $sessionKey=md5($this->sid.$this->user->info['id'].$this->user->info['provider'].time());
                                            
                                            $sKey=$this->user->encodeRequest('email_verify',array($this->user->info['id']));
                                            $verifyLink=$this->host.'/a/'.($lang=='ar'?'':$lang.'/').'?k='.$sKey.'&key='.urlencode($sessionKey);
                                            
                                            $mailer=new MourjanMail($this->urlRouter->cfg, $this->user->info['options']['lang']);
                                            if ($mailer->sendEmailValidation($fields['email'],$verifyLink,$this->user->info['name'])){
                                                $this->user->info['options']['email']=$fields['email'];
                                                $this->user->info['options']['emailKey']=$sessionKey;
                                                $this->user->update();
                                                $this->user->updateOptions();
                                                $result=array();
                                                $result['email']=array('value', $fields['email'], '<ok>'.preg_replace('/{email}/', $fields['email'],$this->lang['emailSent']).'</ok>');
                                                $this->setData($result,'fields');
                                                $this->process();
                                            }else $this->fail($this->lang['systemErr']);
                                        }else {
                                            unset($this->user->info['options']['email']);
                                            unset($this->user->info['options']['emailKey']);
                                            $this->user->update();
                                            $this->user->updateOptions();
                                            $result=array();
                                            $result['email']=array('value', $fields['email']);
                                            $this->setData($result,'fields');
                                            $this->process();
                                        }
                                    }else {
                                        $fields['email']=$this->lang['validEmail'];
                                        $this->setData($fields,'fields');
                                        $this->fail($this->lang['wrongInfo']);
                                    }
                                }else $this->fail('101');
                                break;
                            case 'notifications':
                                $old=$notifications=array('ads'=>1,'coms'=>1, 'news'=>1,'third'=>1);
                                if (isset($this->user->info['options']['nb']) && is_array($this->user->info['options']['nb'])) $old=$notifications=array_merge($notifications,$this->user->info['options']['nb']);
                                if (isset($fields['ads']) && $fields['ads']) $notifications['ads']=1;
                                else $notifications['ads']=0;
                                if (isset($fields['news']) && $fields['news']) $notifications['news']=1;
                                else $notifications['news']=0;
                                if (isset($fields['third']) && $fields['third']) $notifications['third']=1;
                                else $notifications['third']=0;
                                if (isset($fields['coms']) && $fields['coms']) $notifications['coms']=1;
                                else $notifications['coms']=0;
                                $this->user->info['options']['nb']=$notifications;
                                if ($this->user->updateOptions()) {                                    
                                    $this->user->update();
                                    $result=array();
                                    $result['ads']=array('checked', ($notifications['ads'] ? 'checked' : ''));
                                    $result['coms']=array('checked', ($notifications['coms'] ? 'checked' : ''));
                                    $result['news']=array('checked', ($notifications['news'] ? 'checked' : ''));
                                    $result['third']=array('checked', ($notifications['third'] ? 'checked' : ''));
                                    $this->setData($result,'fields');
                                    $this->process();
                                }else {
                                    $this->user->info['options']['nb']=$old;
                                    $this->user->update();
                                    $this->fail($this->lang['systemErr']);
                                }
                                break;
                            default:
                                $this->fail('102');
                                break;
                        }
                    }else $this->fail('101');
                }else $this->fail($this->lang['sessionTO']);
                break;

            case 'ajax-support':
                if ($this->user->info['id'])
                {
                    $lang=$this->post('lang');
                    $this->load_lang(array('post'),$lang);
                    $name=$this->user->info['name'];
                    $email=$this->user->info['email'];
                    if(isset($_POST['obj']))
                        $data=json_encode($_POST['obj']);
                    elseif (isset($this->user->pending['post']))
                        $data=json_encode($this->user->pending['post']);
                    
                    //$geo = implode(" ,", geoip_record_by_name($_SERVER['REMOTE_ADDR']));
                    $geo = $this->urlRouter->getIpLocation();
                    $geostr = "";
                    if (isset($geo['country']) && isset($geo['country']['names']) && isset($geo['country']['names']['en']))
                    {
                        $geostr.= $geo['country']['names']['en'];
                    }

                    if (isset($geo['location']) && isset($geo['location']['time_zone']))
                    {
                        $geostr.= " - {$geo['location']['time_zone']} [{$geo['location']['latitude']}, {$geo['location']['longitude']}]";
                    }
                    $msg="<table>
                    <tr><td><b>ID</b>:</td><td>{$this->user->info['id']}</td></tr>
                    <tr><td><b>Name</b>:</td><td>{$name}</td></tr>
                    <tr><td><b>Email</b>:</td><td>{$email}</td></tr>
                    <tr><td><b>Lang</b>:</td><td>{$lang}</td></tr>
                    <tr><td><b>Location</b>:</td><td>{$geostr}</td></tr>
                    <tr><td><b>Mobile</b>:</td><td>".((isset($this->user->params['mobile']) && $this->user->params['mobile']) ? 'yes':'no')."</td></tr>
                    <tr><td><b>Agent Language</b>:</td><td>".$_SERVER['HTTP_ACCEPT_LANGUAGE']."</td></tr>
                    <tr><td><b>User Agent</b>:</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>
                    <tr><td colspan='2'>>>>".$data."<<<<</td></tr>
                    </table>";
                    $res=$this->sendMail("Mourjan Admin", $this->urlRouter->cfg['admin_email'], $name,$email,"Ad Publish Support Request",$msg,$this->urlRouter->cfg['smtp_contact']);
                    if (!$res) {
                        $this->fail($this->lang['errSupport']);
                    }else {
                        $this->msg=$this->lang['supportOk'];
                        $this->process();
                    }
                }else $this->fail('101');
                break;

            case 'ajax-contact':
                $lang = $this->post('lang');
                $this->load_lang(array('contact'),$lang);
                $subject = 'User Feedback';
                $name = $this->post('name', 'filter');
                $email = $this->post('email', 'filter');
                $feed = $this->post('msg', 'filter');
                			           
        	$geo = $this->urlRouter->getIpLocation();
                $mobile= (isset($this->user->params['mobile'])) ? $mobile=$this->user->params['mobile'] : 0;
                $geostr = "";
                if (isset($geo['country']) && isset($geo['country']['names']) && isset($geo['country']['names']['en']))
                {
                    $geostr.= $geo['country']['names']['en'];
                }

                if (isset($geo['location']) && isset($geo['location']['time_zone']))
                {
                    $geostr.= " - {$geo['location']['time_zone']} [{$geo['location']['latitude']}, {$geo['location']['longitude']}]";
                }
                if ($mobile)
                {
                    $geostr.= " - Mobile";
                }

                $msg = "<style>table{border-collapse:collapse;border-spacing:2px;border-color:gray;} th,td{border: 1px solid #cecfd5;padding: 10px 15px;}</style><table><tr>";
                if ($this->user->info['id'])
                {
                    $msg.="<td><b>Name</b></td><td><a href='https://www.mourjan.com/myads/?u={$this->user->info['id']}' target=_blank>{$name}</a></td>";
                }
                else
                {
                    $msg.="<td><b>Name</b></td><td>{$name}</td>";
                }
                $msg.="<td><b>Location</b></td><td>{$geostr}</td>";
                if (isset($this->user->params['country']) && $this->user->params['country']>0)
                {
                    if (isset($this->urlRouter->countries[$this->user->params['country']]))
                    {
                        $msg.="<td><b>Target</b></td><td>{$this->urlRouter->countries[$this->user->params['country']]['uri']}";
                        if (isset($this->user->params['city']) && $this->user->params['city']>0)
                        {
                            if (isset($this->urlRouter->countries[$this->user->params['country']]['cities'][$this->user->params['city']]))
                            {
                                $msg.=" - {$this->urlRouter->countries[$this->user->params['country']]['cities'][$this->user->params['city']]['uri']}";
                            }
                            else
                            {
                                $msg.=" - {$this->user->params['city']}";
                            }
                        }
                    }
                    else
                    {
                        $msg.="<tr><td><b>Target</b></td><td>{$this->user->params['country']}";
                        if (isset($this->user->params['city']))
                        {
                            $msg.=" - {$this->user->params['city']}";
                        }
                    }

                    $msg.="</td></tr>";
                } else $msg.="</tr>";
                $msg.="<tr><td><b>Locale</b></td><td>".filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING)."</td>";
                $msg.="<td><b>Browser</b></td><td colspan='3'>".filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING)."</td></tr>";
                $msg.="<tr><td colspan='6'>{$feed}</td></tr>";
                $msg.="</table>";
/*
                $msg="<table>
                    <tr><td><b>ID</b>:</td><td>{$this->user->info['id']}</td></tr>
                    <tr><td><b>Name</b>:</td><td>{$name}</td></tr>
                    <tr><td><b>Email</b>:</td><td>{$email}</td></tr>
                    <tr><td><b>Location</b>:</td><td>{$geostr}</td></tr>
                    <tr><td><b>Selected Country</b>:</td><td>".(isset ($this->user->params['country']) ? $this->user->params['country'] : "0")."</td></tr>
                    <tr><td><b>Agent Language</b>:</td><td>".$_SERVER['HTTP_ACCEPT_LANGUAGE']."</td></tr>
                    <tr><td><b>Is Mobile</b>:</td><td>".($mobile ? 'Yes': 'No')."</td></tr>
                    <tr><td><b>User Agent</b>:</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>
                    <tr><td colspan='2'>{$feed}</td></tr>
                    </table>";
*/
                $res=$this->sendMail("Mourjan Support", $this->urlRouter->cfg['admin_email'], $name, $email, $subject, $msg, $this->urlRouter->cfg['smtp_contact']);
                if (!$res)
                {
                    $this->fail($this->lang['errSys']);
                }
                else
                {
                    $this->msg=$this->lang['msgOk'];
                    $this->process();
                }
                break;

            case 'ajax-section-update':
                if($this->user->info['id'] && $this->user->info['level']==9){
                $id=$this->post('id', 'numeric');
                $name_en=$this->post('en', 'filter');
                $name_ar=$this->post('ar', 'filter');
                $uri=$this->post('uri', 'filter');
                if ($id && $name_en && $name_ar && $uri) {

                    $sname_en=$this->post('sen', 'filter');
                    if(!$sname_en) $sname_en=$name_en;
                    $sname_ar=$this->post('sar', 'filter');
                    if(!$sname_ar) $sname_ar=$name_ar;

                    $stmt=$this->urlRouter->db->prepareQuery("
                        update or insert into naming
                        (type_id,origin_id,lang,single,plural)
                        values
                        (?,?,?,?,?) matching (type_id,origin_id,lang)");
                    if ($name_en) $stmt->execute(array(2,$id,'en',$sname_en,$name_en));
                    if ($name_ar) $stmt->execute(array(2,$id,'ar',$sname_ar,$name_ar));

                    $res=$this->urlRouter->db->queryResultArray(
                    "update section set name_ar=?,name_en=?,uri=?,blocked=0 where id=?",
                    array($name_ar,$name_en,$uri,$id), true);
                    if (!empty($res)) {
                        /*$this->urlRouter->db->queryCacheResultSimpleArray(
                        "sections",
                        "select s.ID, s.NAME_AR, s.NAME_EN, s.URI, s.ROOT_ID
                        from section s",
                        null, 0, $this->urlRouter->cfg['ttl_long'], true);*/
                        
                        $this->urlRouter->db->getSections(true);

                        $this->process();
                    }else $this->fail("102");
                }else $this->fail("101");
                }else $this->fail();
                break;
            case 'ajax-section-delete':
                if($this->user->info['id'] && $this->user->info['level']==9){
                $id=$this->post('id', 'numeric');
                if ($id) {
                    $rootId=$this->urlRouter->sections[$id][4];
                    $stmt=$this->urlRouter->db->prepareQuery("
                        delete from section where id=?");
                    $res=$stmt->execute(array($id));
                    if ($res) {

                        /*
                        $this->urlRouter->db->queryCacheResultSimpleArray(
                        "sections",
                        "select s.ID, s.NAME_AR, s.NAME_EN, s.URI, s.ROOT_ID
                        from section s",
                        null, 0, $this->urlRouter->cfg['ttl_long'], true);
                         * 
                         */
                        $this->urlRouter->db->getSections(true);

                        $this->urlRouter->db->queryCacheResultSimpleArray(
                        "req_sections_en_{$rootId}",
                        "select s.ID,s.name_en
                        from section s
                        left join category c on c.id=s.category_id
                        where c.root_id={$rootId} and s.id not in (19,29,63,105,114)
                        order by s.NAME_EN",
                        null, 0, $this->urlRouter->cfg['ttl_long'], true);

                        $this->urlRouter->db->queryCacheResultSimpleArray(
                        "req_sections_ar_{$rootId}",
                        "select s.ID,s.name_ar
                        from section s
                        left join category c on c.id=s.category_id
                        where c.root_id={$rootId} and s.id not in (19,29,63,105,114) 
                        order by s.NAME_AR",
                        null, 0, $this->urlRouter->cfg['ttl_long'], true);

                        $this->process();
                    }else $this->fail("102");
                }else $this->fail("101");
                }else $this->fail();
                break;
            case 'ajax-report':                
                $mobile= (isset($this->user->params['mobile']) && $this->user->params['mobile'])? 1 : 0;
                $id=$this->post('id', 'int');
                $name = $this->post('name', 'filter');
                $userEmail = $this->post('email', 'filter');
                
                $flag = -1;
                $helpTopic=4;
                if(isset($_POST['flag']) && in_array($_POST['flag'],[0,1,2,3,4,5])){
                    $flag=$this->post('flag', 'int');
                }
                if ($id && isset($this->user->info['level']) && $this->user->info['level']==9) {                    
                    $this->urlRouter->db->queryResultArray("EXECUTE PROCEDURE SP\$HOLD_AD({$id})");
                    $this->process();
                    $this->logAdmin($id, 9);
                }
                elseif($id) {
                    $feed='';
                    switch($flag){
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
                    $feed=$this->post('msg', 'filter');
                    
                    $feed=trim($feed);
                    $geo = $this->urlRouter->getIpLocation();
                
                    $geostr = "";
                    if (isset($geo['country']) && isset($geo['country']['names']) && isset($geo['country']['names']['en'])){
                        $geostr.= $geo['country']['names']['en'];
                    }

                    if (isset($geo['location']) && isset($geo['location']['time_zone'])){
                        $geostr.= " - {$geo['location']['time_zone']} [{$geo['location']['latitude']}, {$geo['location']['longitude']}]";
                    }
                    if ($mobile){
                        $geostr.= " - Mobile";
                    }

                    $msg = "<style>table{border-collapse:collapse;border-spacing:2px;border-color:gray;} th,td{border: 1px solid #cecfd5;padding: 10px 15px;}</style><table><tr>";
                    if (isset($this->user->info['id']) && $this->user->info['id']>0){
                        $name=$this->user->info['name'];
                        $msg.="<td><b>Name</b></td><td><a href='{$this->urlRouter->cfg['host']}/myads/?u={$this->user->info['id']}' target='_blank'>{$name}</a></td>";
                    }
                    $msg.="<td><b>Location</b></td><td>{$geostr}</td>";
                    if (isset($this->user->params['country']))
                    {
                        if (isset($this->urlRouter->countries[$this->user->params['country']]))
                        {
                            $msg.="<td><b>Target</b></td><td>{$this->urlRouter->countries[$this->user->params['country']]['uri']}";
                            if (isset($this->user->params['city']) && $this->user->params['city']>0)
                            {
                                if (isset($this->urlRouter->countries[$this->user->params['country']]['cities'][$this->user->params['city']]))
                                {
                                    $msg.=" - {$this->urlRouter->countries[$this->user->params['country']]['cities'][$this->user->params['city']]['uri']}";
                                }
                                else
                                {
                                    $msg.=" - {$this->user->params['city']}";
                                }
                            }
                        }
                        else
                        {
                            $msg.="<td><b>Target</b></td><td>{$this->user->params['country']}";
                            if (isset($this->user->params['city']))
                            {
                                $msg.=" - {$this->user->params['city']}";
                            }
                        }

                        $msg.="</td></tr>";
                    } else $msg.="</tr>";
                    $msg.="<tr><td><b>Locale</b></td><td>".filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING)."</td>";
                    $msg.="<td><b>Browser</b></td><td colspan='3'>".filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING)."</td></tr>";
                    $msg.="<tr><td colspan='6'><a href='{$this->urlRouter->cfg['host']}/{$id}' target=_blank>{$feed}</a></td></tr>";
                    $msg.="</table>";
                  
                    $res=$this->sendMail("Mourjan Admin", $this->urlRouter->cfg['admin_email'], ($name) ? $name : 'Abusive Report', ($userEmail ? $userEmail : $this->urlRouter->cfg['smtp_user']), $subject, $msg, $this->urlRouter->cfg['smtp_contact'], $id, $helpTopic);
                    $this->process();
                }else{
                    $this->fail('101');
                }
                break;
                
            case 'ajax-ususpend':
                if ($this->user->info['level']==9 && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    $hours=(int)$_POST['v'];
                    if (is_numeric($id) && $hours){
                        $options = $this->user->getOptions($id);
                        if($options) {
                            $options =  json_decode($options,true);
                            $options['suspend']=time()+($hours*3600);
                            if($this->user->updateOptions($id,$options)) {
                                //$q = 'update ad_user set state = 0 where web_user_id = ? and state = 1';
                                //$this->urlRouter->db->queryResultArray($q,array($id));
                                //$this->user->setReloadFlag($id);
                                $this->process();
                            }else $this->fail('104');
                        }else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-ublock':
                if ($this->user->info['level']==9 && isset ($_POST['i'])) {
                    $id=$_POST['i'];
                    $msg=trim($_POST['msg']);
                    if($msg=='')$msg='Scam Detection';
                    $msg .= ' by admin '.$this->user->info['id'];
                    if (is_numeric($id)){
                        if($msg){
                            $options = $this->user->getOptions($id);
                            if($options) {
                                $options =  json_decode($options,true);
                                if(!isset($options['block']))$options['block']=array();
                                $options['block'][]=$msg;
                                if($this->user->updateOptions($id,$options)) {
                                    //$this->user->setReloadFlag($id);
                                }else$this->fail('105');
                            }else $this->fail('104');
                        }
                        if ($this->user->setLevel($id,5)) 
                                $this->process();
                        else $this->fail('103');
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-video-upload':
            case 'video-upload':
                if ($this->user->info['id'] && isset($this->user->pending['post']['id'])){
                    $action=$this->post('action','uint');
                    $lang=  $this->post('lang');
                    if($lang=='en'||$lang=='ar') $this->urlRouter->siteLanguage=$lang;
                    $this->load_lang(array('post'));
                    require_once 'Zend/Loader.php';
                    Zend_Loader::loadClass('Zend_Gdata_YouTube');
                    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                    Zend_Loader::loadClass('Zend_Gdata_App_Exception');
                    
                    $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);//, null, null, null, null, $this->urlRouter->cfg['host']);

                    $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                    $yt->setMajorProtocolVersion(2);
                    
                    switch($action){
                        case 0:
                        default:
                            $adContent=json_decode($this->user->pending['post']['content'],true);
                            
                            $newVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
                            /*$title='';
                            if ($this->user->pending['post']['title']) {
                                $title=$this->user->pending['post']['title'];
                                $title=  preg_replace('/[.,;-_+=$%^&@!?،؛{}|`~]/', '', $title);
                            }
                            if ($title=='')*/
                            $title='Mourjan video '.$this->user->pending['post']['id'];
                            /*if (isset($adContent['text']) && $adContent['text']!='') {  
                                $content=preg_replace('/[.,;-_+=$%^&@!?/\،؛{}|`~]/', '', $adContent['text']);
                                $newVideoEntry->setVideoDescription($content);
                            }else {*/
                            $newVideoEntry->setVideoDescription($title);
                            //}
                            $newVideoEntry->setVideoTitle($title);

                            //make sure first character in category is capitalized
                            $videoCategory = $adContent['ro']==2 ? 'Autos':'People';
                            $newVideoEntry->setVideoCategory($videoCategory);

                            // convert videoTags from whitespace separated into comma separated
                            //$videoTagsArray = explode(' ', trim($videoTags));
                            //$newVideoEntry->setVideoTags(implode(', ', $videoTagsArray));
                            try {
                                $tokenArray = $yt->getFormUploadToken($newVideoEntry);
                            } catch (Zend_Gdata_App_HttpException $httpException) {
                                error_log($httpException->getRawResponseBody());
                                $this->fail($httpException->getRawResponseBody());
                            } catch (Zend_Gdata_App_Exception $e) {
                                error_log($e->getMessage());
                                $this->fail($e->getMessage());
                            }
                            if (isset($tokenArray['token'])) {
                                $tokenValue = $tokenArray['token'];
                                $postUrl = $tokenArray['url'];
                                $nextUrl = $this->urlRouter->cfg['host'].'/video-upload-ready/'.($lang!='ar' ? $lang.'/':'');
                                
                                //if (isset($this->user->params['mobile']) && $this->user->params['mobile']){
                                    $form='<form onsubmit="if(FLK){upVid(this);return false}" target="vupload" action="'.$postUrl.'?nexturl='.$nextUrl.'" method="post" enctype="multipart/form-data">';
                                    $form.='<ul><li class="nobd"><div class="ipt"><input onchange="setVideo(this)" class="nsh" name="file" type="file"/><input name="token" type="hidden" value="'.$tokenValue.'"/></div></li>';
                                    $form.='<li class="liw hid"><b class="ah">'.$this->lang['video_file_format'].'</b></li>';
                                    $form.='<li class="nobd hid"><b class="load h_43"></b></li>';
                                    $form.='<li><b class="ah ctr act2">';
                                    $form.='<input class="bt ok off" value="'.$this->lang['upload'].'" type="submit" />';
                                    $form.='<span onclick="cVUp(this,1)" class="bt cl">'.$this->lang['cancel'].'</span>';
                                    $form.='</b></li></ul>';
                                    $form.='</form><iframe class="hid" name="vupload" src="/web/blank.html"></iframe>';
                                /*}else{
                                    $form='<form onsubmit="updVo()" target="vupload" action="'.$postUrl.'?nexturl='.$nextUrl.'" method="post" enctype="multipart/form-data">';
                                    $form.='<input class="rc vin" name="file" type="file"/>';
                                    $form.='<input name="token" type="hidden" value="'.$tokenValue.'"/>';
                                    $form.='<input class="rc bt bta" value="'.$this->lang['upload'].'" type="submit" />';
                                    $form.='</form>';
                                }*/
                                /*$form=array(
                                    'action'=>$postUrl.'?nexturl='.$nextUrl,
                                    'token'=>$tokenValue
                                );*/
                                $this->setData($form,'form');
                                $this->process();
                            }else $this->fail(102);
                            break;
                    }
                    
                }else $this->fail('101');
                break;
            case 'ajax-upload-ready':
            case 'video-upload-ready':
                $result='';
                $pass=0;
                $rtl=0;
                if (isset($this->user->pending['post']['rtl'])) $rtl = $this->user->pending['post']['rtl'];
                if ($rtl) $this->urlRouter->siteLanguage='ar';
                $this->load_lang(array('post'));
                
                $videoId=$this->get('id');
                $status=$this->get('status');
                if ($status=='200' && $videoId) {
                    require_once 'Zend/Loader.php';
                    Zend_Loader::loadClass('Zend_Gdata_YouTube');
                    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                    Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                    $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                    $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                    //$yt->setMajorProtocolVersion(2);
                    $pass=1;
                    try {
                        $entry = $yt->getFullVideoEntry($videoId);
                        $videoUrl = htmlspecialchars($this->findFlashUrl($entry));  
                        $firstThumbnail = htmlspecialchars($entry->mediaGroup->thumbnail[0]->url);
                        $state=$entry->getVideoState();
                        if (is_object($state)){
                            $name=$state->getName();
                            if ($name!='processing') {
                                $pass=0;
                            }
                        }
                        $result = "<div class='sh vtd'><img class='vth' src='". $firstThumbnail ."' width='130' height='97' /><span class='play' href='".$videoUrl."&autoplay=1'></span><span onclick='vdel(this)' class='mx'></span></div>";
                        
                    } catch (Zend_Gdata_App_HttpException $httpException) {
                        error_log($httpException->getRawResponseBody());
                        $pass=0;
                    } catch (Zend_Gdata_App_Exception $e) {
                        error_log($e->getMessage());
                        $pass=0;
                    }                   
                    if ($pass){
                        $adContent=json_decode($this->user->pending['post']['content'],true);
                        if (isset($adContent['video'])) $this->deleteVideo ($adContent['video'],$yt);
                        $adContent['video']=array($videoId,$videoUrl,$firstThumbnail);
                        $this->user->pending['post']['content']=json_encode($adContent);
                        $this->user->update();
                        $this->user->saveAd();
                    }else {
                        $result=$this->lang['uploadFail'];
                    }
                }else {
                    $result=$this->lang['uploadFail'];
                }
                ?><script type="text/javascript">document.domain='mourjan.com';top.updVd(<?= $pass ?>,"<?= $result ?>");</script><?php
                break;
            case 'ajax-upload-check':
            case 'video-upload-check':
                if ($this->user->info['id'] && isset($this->user->pending['post']['id'])){
                    $lang='ar';
                    $tLang=$this->post('lang');
                    if($tLang=='ar'||$tLang=='en')$lang=$tLang;
                    $this->urlRouter->siteLanguage=$lang;
                    $this->load_lang(array('post'));
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    if (isset($adContent['video']) && $adContent['video'][0]){
                        require_once 'Zend/Loader.php';
                        Zend_Loader::loadClass('Zend_Gdata_YouTube');
                        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                        Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                        $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                        $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                        $pass=1;
                        $append=true;
                        try {
                            $entry = $yt->getFullVideoEntry($adContent['video'][0]);
                            $videoUrl = htmlspecialchars($this->findFlashUrl($entry));  
                            $firstThumbnail = htmlspecialchars($entry->mediaGroup->thumbnail[0]->url);
                            $state = $entry->getVideoState();
                            if (is_object($state)){
                                $name=$state->getName();
                                if ($name=='processing') {
                                    $this->setData(1,'P');
                                    $append=false;
                                }else {
                                    $pass=0;
                                    $this->deleteVideo($adContent['video'][0]);
                                }
                            }
                        } catch (Zend_Gdata_App_HttpException $httpException) {
                            error_log($httpException->getRawResponseBody());
                            $pass=0;
                        } catch (Zend_Gdata_App_Exception $e) {
                            error_log($e->getMessage());
                            $pass=0;
                        } 
                        if ($pass) {
                            //if ($append && isset($this->user->params['mobile']) && $this->user->params['mobile']){
                            if ($append){
                                $matches=null;
                                $vId=preg_match('/\/v\/([a-zA-Z0-9]*?)\?/', $videoUrl, $matches);

                                $vurl=$videoUrl;
                                $os=0;
                                if ($vId) {
                                    $vId=$matches[1];
                                    $os=preg_match('/(android|iphone)/i', $_SERVER['HTTP_USER_AGENT'], $matches);
                                    if($os){
                                        $os=strtolower($matches[1]);
                                        switch($os){
                                            case 'iphone':
                                                $vurl='youtube:'.$vId;
                                                break;
                                            case 'android':
                                                $vurl='vnd.youtube:'.$vId;
                                                break;
                                            default:
                                                break;
                                        }
                                    }
                                 }
                                $this->setData("<a class='ctr ah' target='blank' href='".$vurl."&autoplay=1'><span title='".$this->lang['removeVideo']."' onclick='delV(this)' class='pz pzd'></span><img src='". $firstThumbnail ."' width='250' height='200' /><span class='play'></span></a>", 'video');
                            }
                            $this->process();
                        }else {
                            $this->fail(stripcslashes($this->lang['uploadFail']));
                        }
                    }else $this->fail(102);
                }else $this->fail(101);
                break;
            case 'ajax-video-delete':
            case 'video-delete':
                if ($this->user->info['id'] && isset($this->user->pending['post']['id'])) {
                    $adContent=json_decode($this->user->pending['post']['content'],true);
                    $pass=false;
                    if (isset($adContent['video'])) {
                        if ($this->deleteVideo($adContent['video'])) {
                            unset($adContent['video']);
                            $this->user->pending['post']['content']=json_encode($adContent);
                            $this->user->update();
                            $this->user->saveAd();
                            $this->process();
                        }else $this->fail(103);
                    }else $this->process();
                }else $this->fail(101);
                break;
            case 'ajax-video-link':
            case 'video-link':
                $videoId=$this->post('id');
                if ($videoId && $this->user->info['id'] && isset($this->user->pending['post']['id'])) {
                    $lang='ar';
                    $tLang=$this->post('lang');
                    if($tLang=='ar'||$tLang=='en')$lang=$tLang;
                    
                    //require_once 'Google/autoload.php';
                    
                    try{
                    
                        $client = new Google_Client();
                        $client->setApplicationName('Mourjan.com Uploader');
                        $apiKey = $this->urlRouter->cfg['gapp_api_key'];

                        if (strpos($apiKey, "<") !== false) {
                            echo missingApiKeyWarning();
                            exit;
                        }
                        $client->setDeveloperKey($apiKey);

                        $service = new Google_Service_YouTube($client);
                        $optParams = array('id' => $videoId);
                        $videosResponse = $service->videos->listVideos('snippet', $optParams);
                        
                        $thumbnails = null;
                        foreach ($videosResponse['items'] as $videoResult) {
                            $thumbnails = $videoResult['snippet']['thumbnails'];
                            break;
                        }
                        
                        if(isset($thumbnails['modelData']) && count($thumbnails['modelData'])){
                            
                            //error_log(var_export($thumbnails['modelData'],true));
                                                       
                            $videoUrl = 'https://www.youtube.com/watch?v='.$videoId;
                            $firstThumbnail = htmlspecialchars($thumbnails['modelData']['medium']['url']);
                            if(isset($thumbnails['modelData']['standard'])){
                                $displayThumb = htmlspecialchars($thumbnails['modelData']['standard']['url']);
                                $width = $thumbnails['modelData']['standard']['width'];
                                $height = $thumbnails['modelData']['standard']['height'];
                            }else{
                                $displayThumb = htmlspecialchars($thumbnails['modelData']['high']['url']);
                                $width = $thumbnails['modelData']['high']['width'];
                                $height = $thumbnails['modelData']['high']['height'];
                            }
                            
                            $hiRes = true;
                            if(isset($this->user->params['mobile']) && $this->user->params['mobile']){
                                $displayThumb = $firstThumbnail;
                                $width = $thumbnails['modelData']['medium']['width'];
                                $height = $thumbnails['modelData']['medium']['height'];
                                $hiRes = false;
                            }
                            $matches=null;
                            
                            $vId=$videoId;                            
                            $vurl=$videoUrl;
                            $os=0;
                            
                            $os=preg_match('/(android|iphone)/i', $_SERVER['HTTP_USER_AGENT'], $matches);
                            if($os){
                                $os=strtolower($matches[1]);
                                switch($os){
                                    case 'iphone':
                                        $vurl='youtube:'.$vId;
                                        break;
                                    case 'android':
                                        $vurl='vnd.youtube:'.$vId;
                                        break;
                                    default:
                                        break;
                                }
                             }
                            $result = "<a class='ctr ah' target='blank' href='".$vurl."&autoplay=1'><span onclick='delV(this)' title='".($lang=='ar'?'إزالة الفيديو':'remove video')."' class='pz pzd'></span><img src='". $displayThumb ."' width='{$width}' height='{$height}' /><span class='play'></span></a>";
                        
                            $adContent=json_decode($this->user->pending['post']['content'],true);
                           
                            $adContent['video']=array('',$videoUrl,$firstThumbnail);
                            $this->user->pending['post']['content']=json_encode($adContent);
                            $this->user->update();
                            $this->user->saveAd();

                            $this->setData($result, 'video');

                            $this->process();
                        }
                    } catch (Google_Service_Exception $e) {
                        $this->fail($e->getMessage());
                      } catch (Google_Exception $e) {
                        $this->fail($e->getMessage());
                      }
                }else $this->fail('101');
                break;
            case 'ajax-password':
                $pass=$this->post('v');
                error_log("PASSWORD: <{$pass}>".(isset($this->user->pending['password_new']) ? ' | NEW':'').(isset($this->user->pending['password_reset']) ? ' | RESET':'').PHP_EOL, 3, "/var/log/mourjan/password.log");
                    
                $lang='ar';
                $tLang=$this->post('lang');
                if($tLang=='ar'||$tLang=='en')$lang=$tLang;
                if($pass && isset($this->user->pending['user_id']) && (isset($this->user->pending['password_new']) || isset($this->user->pending['password_reset'])))
                {
                    if($this->user->updatePassword($pass)){
                        $this->process();
                    }else{
                        $this->fail('102');
                    }
                } else {
                    error_log(var_export($_SERVER,true).PHP_EOL, 3, "/var/log/mourjan/password.log");
                    //error_log("PASSWORD SERVER LOG: ".var_export($_SERVER,true).PHP_EOL);
                    $this->fail('101');
                }
                break;
            case 'ajax-preset':
                $email=$this->post('v');
                $user_id = 0;
                $lang='ar';
                $tLang=$this->post('lang');
                if($tLang=='ar'||$tLang=='en')$lang=$tLang;
                $date = date('Ymd');
                $send_email=false;
                if (!$this->user->info['id']){
                    if ($email && $this->isEmail($email) ){
                        $user = $this->user->checkAccount($email);
                        if($user===false){
                            $this->fail("103");
                        }else{
                            if(count($user)){
                                $user = $user[0];
                                $user_id = $user['ID'];
                                $opt = json_decode($user['OPTS'], true);
                                if(isset($opt['validating'])){
                                    $this->fail('106');
                                }elseif(!isset($opt['resetting']) || (isset($opt['resetting']) && !isset($opt['resetting'][$date])) ){
                                    $send_email=true;
                                    if(isset($opt['lang']))$lang=$opt['lang'];
                                }
                                if(!$send_email){
                                    $this->fail('105');
                                }else{
                                    require_once $this->dir.'/bin/utils/MourjanMail.php';
                                    $mailer=new MourjanMail($this->urlRouter->cfg, $lang);

                                    $verifyLink='';

                                    $sessionKey=md5($this->sid.$user_id.time());
                                    
                                    //if(isset($opt['resetting']))
                                    //    $sessionKey=$opt['resetting'];
                                    
                                    $sKey=$this->user->encodeRequest('reset_password',array($user_id));
                                    $verifyLink=$this->host.'/a/'.($lang=='ar'?'':$lang.'/').'?k='.$sKey.'&key='.urlencode($sessionKey);
                                    
                                    if ($mailer->sendResetPass($email,$verifyLink)){
                                        if(!isset($opt['resetting'])) $opt['resetting'] = array();
                                        $opt['resetting'][$date]=1;
                                        $opt['resetKey']=$sessionKey;
                                        $this->user->updateOptions($user_id,$opt);
                                        $this->process();
                                    }else{
                                        $this->fail('107');
                                    }
                                }
                            }else $this->fail('104');
                        }
                    }else $this->fail('102');
                }else $this->fail('101');
                break;
            case 'ajax-register':
                $email=$this->post('v');
                $user_id = 0;
                $lang='ar';
                $tLang=$this->post('lang');
                if($tLang=='ar'||$tLang=='en')$lang=$tLang;
                $date = date('Ymd');
                if (!$this->user->info['id']){
                    if ($email && $this->isEmail($email) ){
                        $user = $this->user->checkAccount($email);
                        if($user===false){
                            $this->fail("103");
                        }else{
                            $send_email= false;
                            $user_exists = false;
                            if(count($user)){
                                $user = $user[0];
                                if($user['USER_PASS']){
                                    $user_exists=true;
                                }else{
                                    $user_id = $user['ID'];
                                    $opt = json_decode($user['OPTS'], true);
                                    if(isset($opt['validating'])){
                                        if(!isset($opt['validating'][$date]) || (isset($opt['validating'][$date]) && $opt['validating'][$date]<2)){
                                            $send_email=true;
                                            if(isset($opt['lang']))$lang=$opt['lang'];
                                        }
                                    }else{
                                        $send_email=true;
                                        if(isset($opt['lang']))$lang=$opt['lang'];
                                    }                                    
                                }
                            }else{  
                                //create new record
                                $send_email=true;
                            }
                            if($user_exists){
                                $this->fail('103');
                            }elseif(!$send_email){
                                $this->fail('104');
                            }else{
                                
                                if(!$user_id){
                                    $user = $this->user->createNewByEmail($email);
                                    if($user && count($user)){
                                        $user_id = $user[0]['ID'];
                                        $opt = json_decode($user[0]['OPTS'], true);
                                    }
                                }
                                if($user_id){
                                    require_once $this->dir.'/bin/utils/MourjanMail.php';
                                    $mailer=new MourjanMail($this->urlRouter->cfg, $lang);

                                    $verifyLink='';

                                    $sessionKey=md5($this->sid.$user_id.time());
                                    if(isset($opt['accountKey'])){
                                        $sessionKey=$opt['accountKey'];
                                    }
                                    $sKey=$this->user->encodeRequest('reset_password',array($user_id));
                                    $verifyLink=$this->host.'/a/'.($lang=='ar'?'':$lang.'/').'?k='.$sKey.'&key='.urlencode($sessionKey);

                                    if ($mailer->sendNewAccount($email,$verifyLink)){
                                        if(!isset($opt['validating'])) $opt['validating'] = array();
                                        if(isset($opt['validating'][$date]) && is_numeric($opt['validating'][$date])){
                                            $opt['validating'][$date]++;
                                        }else{
                                            $opt['validating'][$date]=1;
                                        }
                                        $opt['accountKey']=$sessionKey;
                                        $this->user->updateOptions($user_id,$opt);
                                        $this->process();
                                    }else{
                                        $this->fail('105');
                                    }
                                }else{
                                    $this->fail('106');
                                }
                            }
                        }
                    }else $this->fail("102");
                }else $this->fail('101');
                break;
            case 'ajax-js-error':
                $error=$this->post('e');
                if($error){
                    $url=$this->post('u');
                    if(!preg_match('/facebook|google|sharethis/i',$url)){
                        $line=$this->post('ln');
                        $msg='JAVASCRIPT'.(isset($this->user->params['mobile']) && $this->user->params['mobile'] ? ' MOBILE':'').' ERROR: '.$error.' >> LINE: '.$line.' >> URL: '.$url. ' >> USER AGENT: '.$_SERVER['HTTP_USER_AGENT'].' >> USER_ID: '.$this->user->info['id'];
                        error_log($msg);
                    }
                }
                $this->process();
                break;
            case 'ajax-close-banner':
                $banner_label=$this->post('id');
                if($banner_label && isset($this->user->params[$banner_label])){
                    $this->user->params[$banner_label]=0;
                    $this->user->update();
                    $this->process();
                }else $this->fail('101');
                break;
            case 'ajax-user-type':
                if($this->user->info['id'] && $this->user->info['level']==9){
                    $userId = $this->get('u','numeric');
                    $userType = $this->get('t','numeric');
                    if($userId && in_array($userType,array(1,2))){
                        if($this->user->setType($userId, $userType)){
                            $q = 'update ad a set a.publisher_type = '.($userType==1 ? 1:3).' where a.id in (select u.id from ad_user u where u.web_user_id = ?)';
                            if($this->urlRouter->db->queryResultArray(
                            $q,
                            array($userId), true)){
                                $this->process();
                            }else{
                                $this->fail('104');
                            }
                        }else $this->fail('103');
                    }else $this->fail('102');
                }  else {
                    $this->fail('101');
                }
                break;
            case 'ajax-mute':
                $mute = $this->get('s','boolean');
                $this->user->params['mute']=$mute;
                $this->user->update();
                $this->process();
                break;
            default:
                $this->fail();
                break;
        }
    }
    
    function deleteVideo($video, $yt=null){
        $pass=true;
        if ($video[0]){
            if (!$yt) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass('Zend_Gdata_YouTube');
                Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                Zend_Loader::loadClass('Zend_Gdata_App_Exception');

                $httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->urlRouter->cfg['yt_user'],$this->urlRouter->cfg['yt_pass'], Zend_Gdata_YouTube::AUTH_SERVICE_NAME);
                $yt = new Zend_Gdata_YouTube($httpClient, 'Mourjan.com Uploader', null, $this->urlRouter->cfg['yt_dev_key']);
                $yt->setMajorProtocolVersion(2);
                
            }
                try {
                    $entry = $yt->getVideoEntry($video[0],null,true);
                    $httpResponse = $yt->delete($entry);
                } catch (Zend_Gdata_App_HttpException $httpException) {
                    error_log($httpException->getRawResponseBody());
                    $pass=false;
                    if ($httpException->getCode()==0){
                        $pass=true;
                    }
                } catch (Zend_Gdata_App_Exception $e) {
                    error_log($e->getMessage());
                    $pass=false;
                }
        }
        return $pass;
    }
    
    function pingUrl($url=NULL)  
    {  
        if($url == NULL) return false;  
        $ch = curl_init($url);  
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $data = curl_exec($ch); 
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);  
        if( ($httpcode>=200 && $httpcode<400) || $httpcode==403 ){  
            return true;  
        } else {  
            return false;  
        }  
    }
    
    
    function getAdSection($ad, $rootId=0) {
        $section='';
        switch($ad['PURPOSE_ID']){
            case 1:
            case 2:
            case 999:
            case 8:
                $section=$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex].' '.$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex];
                break;
            case 6:
            case 7:
                $section=$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].' '.$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                break;
            case 3:
            case 4:
            case 5:
                if(preg_match('/'.$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].'/', $this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex])){
                    $section=$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }else {
                    $in=' ';
                    if ($this->urlRouter->siteLanguage=='en')$in=' '.$this->lang['in'].' ';
                    $section=$this->urlRouter->purposes[$ad['PURPOSE_ID']][$this->fieldNameIndex].$in.$this->urlRouter->sections[$ad['SECTION_ID']][$this->fieldNameIndex];
                }
                break;
           }
           
           $adContent = json_decode($ad['CONTENT'], true);
           $countries = $this->urlRouter->db->getCountriesDictionary(); // $this->urlRouter->countries;
           if (isset($adContent['pubTo'])) {
                $fieldIndex=2;
                $comma=',';
                if ($this->urlRouter->siteLanguage=='ar'){
                    $fieldIndex=1;
                    $comma='،';
                }
                $countriesArray=array();
                $cities = $this->urlRouter->cities;
                
                $content='';
                foreach ($adContent['pubTo'] as $city => $value){
                    
                    if (isset($cities[$city]) && isset($cities[$city][4])) {
                        $country_id=$cities[$city][4];
                        
                        if (!isset($countriesArray[$cities[$city][4]])){
                            /*
                            $ccs=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$cities[$city][4]}_{$this->urlRouter->siteLanguage}",
                                    "select c.ID 
                                    from city c
                                    where c.country_id={$country_id} 
                                    and c.blocked=0
                                    order by NAME_". $this->urlRouter->siteLanguage,
                                    null, 0, $this->urlRouter->cfg['ttl_long']);
                             * 
                             */
                            
                            $ccs = $countries[$country_id][6];
                            if ($ccs && count($ccs)>0){
                                $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],array());
                            }else {
                                $countriesArray[$country_id]=array($countries[$country_id][$fieldIndex],false);
                            }
                        }
                        if ($countriesArray[$country_id][1]!==false) $countriesArray[$country_id][1][]=$cities[$city][$fieldIndex];
                    }
                }
                $i=0;
                foreach ($countriesArray as $key => $value) {
                    if ($i)$content.=' - ';
                    $content.=$value[0];
                    if ($value[1]!==false) $content.=' ('.implode ($comma, $value[1]).')';
                    $i++;
                }
                
                if ($content) {
                    $section=$section.' '.$this->lang['in'].' '.$content;
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,0,$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }
            }elseif(isset ($countries[$ad['COUNTRY_ID']])) {
                $countryId=$ad['COUNTRY_ID']; //$this->urlRouter->countries[$ad['COUNTRY_ID']][0];
                $countryCities=$countries[$countryId][6];/* $this->urlRouter->db->queryCacheResultSimpleArray("cities_{$countryId}",
                     "select c.ID from city c
                     where c.country_id={$countryId} and c.blocked=0",
                     null, 0, $this->urlRouter->cfg['ttl_long']);*/
                if (count($countryCities)>0 && isset($this->urlRouter->cities[$ad['CITY_ID']])){
                    $section=$section.' '.$this->lang['in'].' '.$this->urlRouter->cities[$ad['CITY_ID']][$this->fieldNameIndex].' '.$countries[$countryId][$this->fieldNameIndex];
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,$ad['CITY_ID'],$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }else {
                    $section=$section.' '.$this->lang['in'].' '.$countries[$countryId][$this->fieldNameIndex];
                    //$section='<a href="'.$this->urlRouter->getURL($countryId,0,$rootId,$ad['SECTION_ID'],$ad['PURPOSE_ID']).'">'.$section.'</a>';
                }
            }
        //if($section) $section='<span class="sk">'.$section.'</span>';
        if($section) {
            if($this->isMobile){
                $section='<b class="ah">'.$section.'</b>';
            }else {
                $section='<span class="k">'.$section.' - <b>' . $this->formatSinceDate(strtotime($ad['DATE_ADDED'])) . '</b></span>';
            }
        }
        return $section;
    }
}
?>
