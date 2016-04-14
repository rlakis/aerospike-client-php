<?php
$init=false;
if (php_sapi_name()=='cli') {
    $root_path = dirname(dirname(dirname(__FILE__)));
    
    include $root_path.'/index.php';
    if ($argc>1) {
        if ($argc==2 && $argv[1]=='init')
            $init = true;
    }
}
require_once $config['dir'] . '/core/layout/Site.php';
require_once $config['dir'] . '/bin/utils/MourjanMail.php';

class WatchlistProcesser extends Site {
    
    function WatchlistProcesser($router, $init=false){
        $this->force_cache=false;
        echo "Processing Watchlist queue \n";
        parent::Site($router);
        $router->db->setWriteMode(TRUE);
        $this->user=@new User($router->db, $router->cfg, $this);
        if ($init){
            $this->initTable();
        }else {
            $this->classifieds = new Classifieds($router->db);
            $this->processBatch();
        }
        echo "finished processing Watchlist \n";
    }
    
    function initTable(){
        echo "\tre-initializing MAIL_WATCHLIST table with user data \n";
        
        $timer=time();
        
        $q = "select a.id, a.opts from web_users a where a.opts containing 'watch'";
        //$q = "select a.id, a.opts from web_users a where a.lvl!=5 and a.lvl!=6 and octet_length(a.opts)>0 and ( a.email > '' or a.user_email > '' )";
        $users=$this->urlRouter->db->queryResultArray($q, null,TRUE,PDO::FETCH_NUM);
        
        $processed=0;
        $count = count($users);
        echo "\t\tTotal Fetched {$count} records\n";
        for ($i=0; $i<$count; $i++){
            $options = json_decode($users[$i][1],true);
            $watches=$this->user->checkWatchMailSetting($users[$i][0],1,1);
            if($watches && count($watches)){
                $watchArray=array();
                foreach ($watches as $kid => $params){
                     $key=$params['COUNTRY_ID'].'-'.$params['CITY_ID'].'-'.$params['SECTION_ID'].'-'.$params['SECTION_TAG_ID'].'-'.$params['LOCALITY_ID'].'-'.$params['PURPOSE_ID'].'-'.crc32($params['QUERY_TERM']);
                     $watchArray[$key]=$params['ID'];
                } 
                $options['watch']=$watchArray;
            }else{
                unset($options['watch']);
            }
            $this->user->updateOptions($users[$i][0], $options);
            $processed++;
        }
        echo "\t\tProcessed {$processed} records\n";
        echo "\tDone re-initialization in ".(time()-$timer)."\n";
    }
    
    function processBatch(){
        $mourjanMail = new MourjanMail($this->urlRouter->cfg,'');

        $q = 'select a.web_user_id, u.display_name, u.email, u.user_name, u.user_email, u.opts ,a.last_process, u.last_visit, a.id, u.identifier 
        from mail_watchlist a
        left join web_users u on u.id=a.web_user_id
        where (u.email!=\'\' or u.user_email!=\'\') and 
        datediff(hour, a.last_process, current_timestamp) >= a.mail_every*72
        and datediff(hour, u.last_visit, current_timestamp) >= a.mail_every*72 
        and u.last_visit > current_timestamp - 31';
        
        $users=$this->urlRouter->db->queryResultArray($q, null, FALSE, PDO::FETCH_NUM);
        $processed=0;
        $language='ar';
        $count = count($users);
        echo "\t\tTotal Fetched {$count} records\n";
        $this->num=1;
        $this->urlRouter->db->commit();
        $this->urlRouter->db->setWriteMode();
        $updateStamp = $this->urlRouter->db->prepareQuery('update mail_watchlist set last_process = current_timestamp where id=?');

        
        for ($i=0; $i<$count; $i++){
            $options=json_decode($users[$i][5], true);
            
            if (isset($options['lang']) && $options['lang']) $language=$options['lang'];
            else $language='ar';
            
            $adLang='';
            if ($language!='ar') $adLang.=$language.'/';
            
            if (isset($options['watch']) && count($options['watch'])){
                $this->urlRouter->watchId=$users[$i][0];
                $prev_visit=strtotime($users[$i][7]);
                $last_process=strtotime($users[$i][6]);
                $this->user->params['last_visit'] = $last_process > $prev_visit ? $last_process : $prev_visit;

                $this->watchInfo=$this->user->getWatchInfo($this->urlRouter->watchId, 0, 1);
                
                
                
                $watchArray=array();
                foreach($this->watchInfo as $watch){
                    $watchArray[$watch['ID']]=$watch;
                }
                $this->execute();
                
                $this->searchResults=$this->searchResults['body'];
//                
//                $key=md5($users[$i][9]);
//                $identifier=$users[$i][0]+$this->urlRouter->baseUserId;
                if ($this->searchResults && $this->searchResults['total_found']){
                    $ad_keys = array_keys( $this->searchResults["matches"] );

                    $ad_cache = $this->urlRouter->db->getCache()->getMulti($this->searchResults["matches"]);
                    $ad_count = count($ad_keys);
                    $ads=array();
                    foreach ($ad_keys as $id) {
                        $ad = (isset($ad_cache[$id])) ? $ad_cache[$id] : $this->classifieds->getById($id);
                        $ad['channel_id']=$this->searchResults["matches"][$id];
                        $ad['channel_title']=$watchArray[$ad['channel_id']]['TITLE'];
                        $ad['channel_count']=$this->searchResults["sub_total"][$ad['channel_id']];
                        if (!empty($ad[Classifieds::ALT_CONTENT])) {
                            if ($language=="en" && $ad[Classifieds::RTL]) {
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 0;
                            } elseif ($language=="ar" && $ad[Classifieds::RTL]==0) {
                                $ad[Classifieds::CONTENT] = $ad[Classifieds::ALT_CONTENT];
                                $ad[Classifieds::RTL] = 1;          
                            }
                        }
                        
                        $excerpt = $this->BuildExcerpts($ad[Classifieds::CONTENT], 160);
                        if (substr($excerpt, -3) == '...') {
                            $replaces = 0;
                            $excerpt = preg_replace('/(?:<(?!\/)(?!.*>).*)|(?:<(?!\/)(?=.*>)(?!.*<\/.*>)).*(\.\.\.)$/', '$1' , $excerpt, -1, $replaces);
                            if ($replaces){
                                $excerpt.='...';
                            }
                        }
                        $ad[Classifieds::CONTENT]=$excerpt;
                        $ads[]=$ad;
                        /*
                        $content.='<ul style="list-style:none;padding:0;margin:10px 0 20px;overflow:hidden;border:1px solid #CCC">';
                        
                        $link= 'http://www.mourjan.com/'.sprintf($ad[Classifieds::URI_FORMAT], $ad[Classifieds::RTL]?'':'en/',$id);
                        
                        $content.='<li style="overflow:hidden;padding:15px 5px;'.($ad[Classifieds::RTL]?'font-size:13px;direction:rtl;text-align:right':'font-size:12px;direction:ltr;text-align:left').'">';
                        if ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                            $content.='<img style="border:1px solid #CCC;float:'.($ad[Classifieds::RTL] ? 'right;margin:0 0 5px 5px':'left;margin:0 5px 5px 0').'" src="'.$this->urlRouter->cfg['url_resources'].'/repos/s/'.$ad[Classifieds::PICTURES][0].'" />';
                        }
                        $content.='<a href="'.$link.'">'.$ad[Classifieds::TITLE].'</a><br />'.$ad[Classifieds::CONTENT].'</li>';            
                        
                        $isArabic=false;
                        $title=$this->watchInfo[$sectionId]['TITLE'];
                        $title=preg_replace('/<.*?>/', '', $title);
                        $in=' in ';
                        if (preg_match('/[\x{0621}-\x{0669}]/u', $title)){
                            $in=' في ';
                            $isArabic=true;
                            $content.='<li style="font-size:13px;float:right;width:508px;padding:5px;direction:rtl;text-align:right;background-color:#EFEFEF;border-top:1px solid #CCC">';
                        }else {
                            $content.='<li style="font-size:12px;float:right;width:508px;padding:5px;direction:ltr;text-align:left;background-color:#EFEFEF;border-top:1px solid #CCC">';
                        }
                        if ($this->searchResults["sub_total"][$sectionId]>1){
                            $dif=$this->searchResults["sub_total"][$sectionId]-1;
                            if ($isArabic) {
                                $content.='و'.($dif == 1 ? 'إعلان آخر' : ($dif == 2 ? 'إعلانين آخرين' : $dif.' '.($dif < 11 ? 'إعلانات أخرى' 
                                        :
                                    'إعلان آخر' ))).$in;
                            }else {
                                $content.='and '.($dif == 1 ? 'one other ad in ':$dif.' other ads in ');
                            }
                        }            
                        $content.='<a href="http://www.mourjan.com/watchlist/'.$adLang.'?channel='.$sectionId.'&identifier='.$identifier.'&cks='.$key.'">'.$this->watchInfo[$sectionId]['TITLE'].'</a></li>';    
                        
                        $content.='</ul>';
                         * 
                         */
                    }
                    $name=$users[$i][3] ? $users[$i][3] :$users[$i][1];
                    $email=$users[$i][4] ? $users[$i][4] : $users[$i][2];
                    if ($email && $mourjanMail->newAdsNotices($ads, $users[$i][0],$email, $name, $language,$this->searchResults['total_found'])) {                    
                        $processed++;                    
                    }
                }
            }
            $updateStamp->execute(array($users[$i][8]));
        }
        echo "\t\tProcessed {$processed} records\n";
    }
    
}

if (php_sapi_name()=='cli'){
    new WatchlistProcesser($router, $init);
}
?>