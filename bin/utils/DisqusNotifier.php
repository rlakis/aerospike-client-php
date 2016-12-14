<?php 
class Disqus{
    
    const GET_AD_USER="
    select ad_user.web_user_id id,a.content,a.rtl, u.user_name, u.user_email, u.opts, a.section_id,ad_user.content user_content, u.email  
    from ad a 
    left join ad_user on ad_user.id=a.id
    left join web_users u on ad_user.web_user_id=u.id
    where a.id=? and a.hold=0 and u.lvl != 6 
    and u.user_email is not NULL and u.user_email!='' and u.opts not containing 'coms:\"0\"' 
    ";
    
    var $cfg;
    var $CURL_OPTS=array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'mourjan-api',    
        CURLOPT_HTTPHEADER     => array('Expect:')
    );
    var $curlUrl='';
    var $urlPost='https://disqus.com/api/3.0/forums/listPosts.json';
    var $urlThread='https://disqus.com/api/3.0/threads/details.json';
    var $lastRun='';
    var $limit=100;
    var $threads=array();
    
    var $logFileHandle;
    var $sphinx;
    
    function Disqus($config){
        $this->cfg=$config;
        require_once $config['dir'].'/bin/utils/MourjanMail.php';
        $this->sphinx = new SphinxClient();
        $this->sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
        $this->sphinx->SetConnectTimeout(1000);
        $this->sphinx->SetServer($config['search_host'], $config['search_port']);
    }
    
    function setPostUrl($ch,$cursor=''){              
        $opts = $this->CURL_OPTS;
        $url = $this->urlPost.
                '?access_token=f363fce05f1741e59b3149fcfc3e7bc2'.
                '&api_key=dMmMsFoIMrMPjAE1GCBw8PeKx9DWwzyn3F47SoPt3iaocFskKvIej3RpkklYWUA5'.
                '&api_secret=wp5AiuaC1FPlJRLuot5fSMJIm7j5pFa8Q1AaEu0N5UmJWoBiZG7pIjdwoKPJ4SMt'.
                '&forum=mourjan&limit='.$this->limit.'&order=asc';
        if ($cursor) $url .= '&cursor='.$cursor;
        if ($this->lastRun) $url .= '&since='.$this->lastRun;
        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);
    }
    
    function setThreadUrl($id,$ch){          
        $opts = $this->CURL_OPTS;
        $url = $this->urlThread.
                '?access_token=f363fce05f1741e59b3149fcfc3e7bc2'.
                '&api_key=dMmMsFoIMrMPjAE1GCBw8PeKx9DWwzyn3F47SoPt3iaocFskKvIej3RpkklYWUA5'.
                '&api_secret=wp5AiuaC1FPlJRLuot5fSMJIm7j5pFa8Q1AaEu0N5UmJWoBiZG7pIjdwoKPJ4SMt'.
                '&forum=mourjan&thread='.$id;
        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);
    }
    
    function fetchThread($id,$ch){
        $this->curlUrl=$this->urlThread;
        $this->setThreadUrl($id,$ch);
        $result = curl_exec($ch);
        $result = json_decode($result,true);
        if (isset($result['code']) && $result['code']==0){
            $this->threads[$id]=$result['response'];
            return $this->threads[$id];
        }else {
            return false;
        }
    }

    function processPosts($ch, $cursor=''){
        $this->curlUrl=$this->urlPost;
        $this->setPostUrl($ch,$cursor);
        $result = curl_exec($ch);     
        $result = json_decode($result,true);
        if ($result && isset($result['code']) && $result['code']==0){
            
            require_once $this->cfg['dir'].'/core/model/Db.php';
            $db = new DB($this->cfg);
            
            $stmt = $db->getInstance()->prepare(self::GET_AD_USER);
            
            $mailer = new MourjanMail($this->cfg);
            
            $posts=$result['response'];
            $processed=0;
            $emailed=0;
            if ($posts && is_array($posts)) {
                $noBreak=1;
                foreach ($posts as $post){
                    $thread = isset($this->threads[$post['thread']]) ? $this->threads[$post['thread']]:$this->fetchThread($post['thread'],$ch);
                    if ($thread) {      
                        if (!isset($thread['identifiers'][0])) continue;
                        $stmt->execute(array($thread['identifiers'][0]));
                        if (($ad = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                            if ($ad) {
                                $userOptions=json_decode($ad['OPTS'],true);
                                if (isset($userOptions['lang'])) {
                                    $mailer->language=$userOptions['lang'];
                                }
                                $emailed++;
                                $excerpt = $this->sphinx->BuildExcerpts(array($ad['CONTENT']), 'mouftah', '', array("limit" => 160));
                                $excerpt = trim($excerpt[0]);
                                if (substr($excerpt, -3) == '...') {
                                    $replaces = 0;
                                    $excerpt = preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/', '$1' , $excerpt, -1, $replaces);
                                    if ($replaces){
                                        $excerpt.='...';
                                    }
                                }
                                $adContent = json_decode($ad['USER_CONTENT'], TRUE);
                                $pic='';
                                if (isset($adContent['video']) && is_array($adContent['video']) && count($adContent['video'])) {
                                    $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$adContent['video'][2].'" /></span>';
                                }elseif (isset($adContent['pics']) && is_array($adContent['pics']) && count($adContent['pics'])
                                        && isset($adContent['pic_def']) && $adContent['pic_def']) {
                                            $pic = $adContent['pic_def'];
                                            $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$this->cfg['url_ad_img'].'/repos/d/' . $pic.'" /></span>';
                                }else{
                                    $pic='<img width="90" height="90" src="'.$this->cfg['url_img'].'/90/'.$ad['SECTION_ID'].'.png" />';
                                }
                                $email = $ad['USER_EMAIL'] ? $ad['USER_EMAIL'] : $ad['EMAIL'];
                                if($email) {
                                    $mailer->commentNotify($post['author']['name'], $post['raw_message'], $thread['link'], $excerpt, $email, $pic,$ad['RTL'],$ad['ID'],$ad['USER_NAME']);
                                }
                            }
                        }
                        $date=strtotime($post['createdAt']);                    
                        $this->setLastRun($date);
                        $processed++;
                    }else {
                        echo $thread['response'],"\n";
                        $noBreak=0;
                        break;
                    }
                    
                }
                echo "Batch:\tProcessed: {$processed}\tEmailed: {$emailed}\n";
                if ($noBreak && $result['cursor']['hasNext']) $this->processPosts($ch,$result['cursor']['next']);
            }
        }elseif(isset($result['code'])) {
            echo $result['response'],"\n";
        }
    }
    
    function getLastRun(){
        $filename=$this->cfg['dir'].'/config/comment_stamp.txt';
        $this->logFileHandle=fopen($filename, 'r+');
        $stream=fread($this->logFileHandle, 1024);
        $this->lastRun=(int)$stream;
        return $this->lastRun;
    }
    
    function setLastRun($stamp,$add=0){
        $this->lastRun=$stamp;
        fseek($this->logFileHandle, 0);
        fwrite($this->logFileHandle, $this->lastRun+$add);
    }
    
    function process(){  
        $this->getLastRun();
        echo "Processing Posts after {$this->lastRun}\n";
        $ch= curl_init();          
        $this->processPosts($ch);
        echo "Done Processing\n";
        $this->setLastRun(time());
        curl_close($ch);
        fclose($this->logFileHandle);
    }
}
?>