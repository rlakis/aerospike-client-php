<?php

$force = TRUE;

if (php_sapi_name()=='cli') {
    $root_path = dirname(dirname(dirname(__FILE__)));
    
    include $root_path.'/index.php';
    if ($argc>1) {
        if ($argc==3 && $argv[1]=='counter')
            $force = FALSE;
        else
            $force = intval($argv[1])+0;
    }
}

require_once $config['dir'] . '/core/layout/Site.php';

class GenModules extends Site {

    var $num=10, $start=0;
    var $log=null,$last_id=0,$current_id;

    var $publicationId=0,$orderId=0,$rangeId=0;
    var $rootName="", $countryName="", $categoryName="", $sectionName="", $purposeName="";

    var $web_modules = "dev_modules";
    
    var $encode_statement = null;
    var $force_cache;

    
    function GenModules($router, $fc=TRUE){
        $this->force_cache=$fc;
        echo "Caching mode: ", $this->force_cache, "\n";
        parent::Site($router);
        echo "start website modules generation @".date("r"), "\n";
        if (array_key_exists('q', $_GET)) $this->rewrite_uri();

        $this->urlRouter->db->queryCacheResultSimpleArray("counter_all",
            "select counter from counts 
            where country_id=0 
            and city_id=0 
            and root_id=0 
            and section_id=0 
            and purpose_id=0", null, -1, 86400, $this->force_cache);

        $this->generate("ar");
        $this->generate("en");

        echo "finish website modules generation @".date("r"), "\n";
    }


    function encode() {
        if (!$this->encode_statement) {
            $this->encode_statement = $this->urlRouter->db->getInstance()->prepare(
                    "select path||'/' PATH from uri 
                    where country_id=? and city_id=? and root_id=? and section_id=? and purpose_id=?");
        }
        
        //$result = $this->urlRouter->cfg['url_base'];
        $result = '';
        $pp = ($this->urlRouter->rootId==4)?0:$this->urlRouter->purposeId;

        $this->encode_statement->execute(array($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $pp));
        $rs = $this->encode_statement->fetch(PDO::FETCH_NUM);
        if (!empty($rs)) {            
            $result.= $rs[0];
        }else {
            $result='/';
        }
        if ($this->urlRouter->siteLanguage!='ar')
            $result.=$this->urlRouter->siteLanguage.'/';
        return $result;
    }
    
    
    function rewrite_uri() {
        $dbm = dba_open( $this->urlRouter->cfg["dir"]."/core/gen/cache/uri-tmp.db", 'n', 'gdbm' ) or die( "Couldn't open database" );;
        $map = $this->urlRouter->db->getInstance()->query(
            "select u.path, u.country_id, u.city_id, u.root_id, u.section_id, 
            u.purpose_id, iif(c.ID_2 is null, '', trim(lower(c.ID_2))) country_code,
            u.title,
            iif(c.BLOCKED is null, 0, c.BLOCKED) country_block, 
            iif(t.BLOCKED is null, 0, t.BLOCKED) city_block
            from URI u
            left join COUNTRY c on c.ID=u.COUNTRY_ID
            left join CITY t on t.ID=u.CITY_ID
            order by u.country_id");
        $country_id=-1;
        $ignore_cities = false;
        while ($uri=$map->fetch(PDO::FETCH_ASSOC)) {
            if ($uri['COUNTRY_BLOCK']) continue;
            if ($uri['CITY_BLOCK']) continue;
            if ($country_id!=$uri['COUNTRY_ID']) {
                $country_id=$uri['COUNTRY_ID'];
                $ignore_cities = false;
                $st = $this->urlRouter->db->getInstance()->query("select count(*) from city where country_id={$country_id} and blocked=0");
                if (!empty($st) && $ct=$st->fetch(PDO::FETCH_NUM)) {
                    $ignore_cities = ((int)$ct[0]==1);
                }
            }
            if ($ignore_cities && $uri['CITY_ID']>0) continue;
            
            $vals = $uri['COUNTRY_ID'].'|'.$uri['CITY_ID'].'|'.
                    $uri['ROOT_ID'].'|'.$uri['SECTION_ID'].'|'.$uri['PURPOSE_ID'].'|'.
                    $uri['TITLE'].'|';
            
            $vals.=($uri['ROOT_ID']=='0')?'index':'search';
            
            dba_insert( $uri['PATH'], $vals, $dbm);
        }
        
        dba_insert('/contact', '0|0|0|0|0|'. 'تواصل مع مرجان'.chr(9).'Contact Mourjan.com|contact', $dbm);
        dba_insert('/favorites', '0|0|0|0|0|'.chr(9).'|favorites', $dbm);
        dba_insert('/post', '0|0|0|0|0|'.chr(9).'|post', $dbm);
        dba_insert('/myads', '0|0|0|0|0|'.'اعلاناتي'.chr(9).'My ads|myads', $dbm);
        dba_insert('/privacy', '0|0|0|0|0|'.'Mourjan.com - سياسة الخصوصوية'.chr(9).'Mourjan Privacy Policy|privacy', $dbm);
        dba_insert('/terms', '0|0|0|0|0|'.'Mourjan.com - شروط الاستخدام'.chr(9).'Mourjan Terms of Use|terms', $dbm);
        dba_insert('/about', '0|0|0|0|0|'.'ما هو موقع مرجان'.chr(9).'About Mourjan.com|about', $dbm);
        dba_insert('/oauth', '0|0|0|0|0|'.chr(9).'|oauth', $dbm);
        dba_insert('/blocked', '0|0|0|0|0|'.chr(9).'|blocked', $dbm);
        dba_insert('/publication-prices', '0|0|0|0|0|لائحة الأسعار لنشر الإعلان في جرائد الإعلانات المبوبة'.chr(9).'Classifieds Publications Ad Placement Pricing List|publication-prices', $dbm);
        dba_insert('/core/gen/cache', '0|0|0|0|0|'.chr(9).'|cache', $dbm);

        dba_insert('/bin/ajax-favorite', '0|0|0|0|0|'.chr(9).'|ajax-favorite', $dbm);
        dba_insert('/bin/ajax-contact', '0|0|0|0|0|'.chr(9).'|ajax-contact', $dbm);
        dba_insert('/bin/ajax-location', '0|0|0|0|0|'.chr(9).'|ajax-location', $dbm);
        dba_insert('/bin/ajax-sections', '0|0|0|0|0|'.chr(9).'|ajax-sections', $dbm);
        dba_insert('/bin/ajax-upload', '0|0|0|0|0|'.chr(9).'|ajax-upload', $dbm);
        dba_insert('/bin/ajax-idel', '0|0|0|0|0|'.chr(9).'|ajax-idel', $dbm);
        dba_insert('/bin/ajax-approve', '0|0|0|0|0|'.chr(9).'|ajax-approve', $dbm);
        dba_insert('/bin/ajax-reject', '0|0|0|0|0|'.chr(9).'|ajax-reject', $dbm);
        dba_insert('/bin/ajax-ifav', '0|0|0|0|0|'.chr(9).'|ajax-ifav', $dbm);
        dba_insert('/bin/ajax-adel', '0|0|0|0|0|'.chr(9).'|ajax-adel', $dbm);
        dba_insert('/bin/ajax-adsave', '0|0|0|0|0|'.chr(9).'|ajax-adsave', $dbm);
        dba_insert('/bin/ajax-pending', '0|0|0|0|0|'.chr(9).'|ajax-pending', $dbm);
        dba_insert('/bin/ajax-section-update', '0|0|0|0|0|'.chr(9).'|ajax-section-update', $dbm);
        dba_insert('/bin/ajax-section-delete', '0|0|0|0|0|'.chr(9).'|ajax-section-delete', $dbm);

        dba_insert('/bin/ajax-support', '0|0|0|0|0|'.chr(9).'|ajax-support', $dbm);
        dba_insert('/bin/ajax-ahold', '0|0|0|0|0|'.chr(9).'|ajax-ahold', $dbm);
        dba_insert('/bin/ajax-country-cities', '0|0|0|0|0|'.chr(9).'|ajax-country-cities', $dbm);
        dba_insert('/bin/ajax-cc-remove', '0|0|0|0|0|'.chr(9).'|ajax-cc-remove', $dbm);
        dba_insert('/bin/ajax-cc-add', '0|0|0|0|0|'.chr(9).'|ajax-cc-add', $dbm);
        dba_insert('/bin/ajax-arenew', '0|0|0|0|0|'.chr(9).'|ajax-arenew', $dbm);
        dba_insert('/bin/ajax-report', '0|0|0|0|0|'.chr(9).'|ajax-report', $dbm);
        dba_insert('/bin/ajax-menu', '0|0|0|0|0|'.chr(9).'|ajax-menu', $dbm);

        dba_close($dbm);
        rename( $this->urlRouter->cfg["dir"]."/core/gen/cache/uri-tmp.db",  $this->urlRouter->cfg["dir"]."/core/gen/cache/uri.db");
    }

    
    function generate($lang="en"){
        $sl = strtoupper($lang);
        $this->urlRouter->siteLanguage=$lang;
        $this->urlRouter->cache($this->force_cache);
        $this->field_name='NAME_'.$sl;
        $this->urlRouter->rootId=0; 
        $this->urlRouter->sectionId=0;
        $this->urlRouter->purposeId=0;
        $fieldIndex=2;
        if($sl=='AR')$fieldIndex=1;
        
        $cntUl='<ul id="um00" class="mul sh">';        
        $count=count($this->urlRouter->countries)+count($this->urlRouter->cities);
        $split=floor($count/7);
        $forceSplit=false;
        $i=0;
        foreach ($this->urlRouter->countries as $country){
            if($country[0]!=15) continue;
            if($country[0]==110 || $country[0]==103) continue;
            if ($i==0 && !$forceSplit) $cntUl.='<li><ul class="ml">';
            elseif ($forceSplit) {
                $cntUl.='</ul></li><li><ul class="ml">';
                $forceSplit=false;
            }
            $this->urlRouter->countryId=$country[0];
            $countryCities=$this->urlRouter->db->queryCacheResultSimpleArray("cities_{$this->urlRouter->countryId}_{$this->urlRouter->siteLanguage}",
                "select c.ID 
                from city c
                where c.country_id={$this->urlRouter->countryId} 
                and c.blocked=0 
                order by NAME_".$sl,
               null, 0, $this->urlRouter->cfg['ttl_long']);
            
            $this->urlRouter->cityId=0;
            $this->urlRouter->siteLanguage='ar';
            $_link = $this->encode();
            $this->urlRouter->siteLanguage=$lang;
            $cntUl.='<li><a href="javascript:cl(\''.$_link.'\')"><span class="cf c'.$this->urlRouter->countryId.'"></span>'.$country[$fieldIndex].'</a></li>';
            $i++;
            if (count($countryCities) > 1) {
                //$cntUl.='<div>';
                foreach($countryCities as $cid){
                    if(isset($this->urlRouter->cities[$cid[0]])){
                        $this->urlRouter->cityId=$cid[0];
                        $_link = $this->encode();
                        $cntUl.='<li><a class="pa" href="javascript:cl(\''.$_link.'\')">'.$this->urlRouter->cities[$cid[0]][$fieldIndex].'</a></li>';
                        $i++;
                    }
                }
                //$cntUl.='</div></li>';
            }
            if($i>=$split) {
                $i=0;
                $forceSplit=true;
            }
        }        
        $this->urlRouter->countryId=0;
        $this->urlRouter->cityId=0;
        $cntUl.='<li><a href="javascript:cl(\'/\')"><span class="cf c'.$this->urlRouter->countryId.'"></span>'.($this->urlRouter->siteLanguage == 'ar' ? 'جميع البلدان':'All Countries').'</a></li>';
        $cntUl.='</ul></li></ul>';
        $this->countryUL=$cntUl;
                

        $this->urlRouter->countryId=0;
        $this->urlRouter->cityId=0;
        $this->urlRouter->rootId=0;
        $this->urlRouter->sectionId=0;
        $this->urlRouter->purposeId=0;
                        
        $q = "select cn.id, cn.NAME_AR, cn.NAME_EN, lower(trim(cn.ID_2)),
        c.ID, c.NAME_AR, c.NAME_EN, c.URI, d.counter, d.unixtime, cn.id||'-'||c.id
        from country cn
        left join city c on c.country_id=cn.id
        left join counts d 
            on cn.id=d.country_id 
            and d.city_id=c.id 
            and d.root_id=0 
            and d.section_id=0 
            and d.purpose_id=0 
        where cn.blocked=0 
        and c.blocked=0
        and d.counter>0
        order by cn.NAME_{$sl}, c.NAME_{$sl}";
        
        $this->urlRouter->db->queryCacheResultSimpleArray("mobile_countries_{$lang}", $q, null, 10, 86400, $this->force_cache);

        $this->urlRouter->alterCache($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->force_cache);

//        $this->insertBlock($this->top_menu(), 0, '', 0, 0);
//        $this->insertBlock( $this->top_dropdowns(), 99, '', 0, 0 );
//        $this->insertBlock($this->index_country_list(), 2, 'index', 0, 0);


//        $changed = $this->urlRouter->db->queryResultArray(
//                "SELECT DISTINCT country_id, city_id FROM COUNTS");

        foreach ($this->urlRouter->countries as $country) {
if($country[0]!=15) continue;
//            $is_changed = false;
//            foreach ($changed as $geo) {
//                if ($geo['COUNTRY_ID']==$country[0]) {
//                    $is_changed = true;
//                    break;
//                }
//            }
//
//            if (!$is_changed) continue;

            $this->urlRouter->countryId=$country[0];
            $this->urlRouter->cityId=0;
            $this->urlRouter->rootId=0;
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId=0;
            
            $this->urlRouter->alterCache($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->force_cache);

            $this->insertBlock($this->top_menu(), 0, '', $this->urlRouter->countryId, 0);
            $this->insertBlock($this->top_dropdowns(), 99, '', $this->urlRouter->countryId, 0);
            $this->insertBlock($this->index_country_list(), 2, 'index', $this->urlRouter->countryId, 0);

            foreach ($this->urlRouter->cities as $city) {
                if ($city[6]!=$this->urlRouter->countryId) continue;

//                $is_changed = false;
//                foreach ($changed as $geo) {
//                    if ($geo['CITY_ID']==$city[0]) {
//                        $is_changed = true;
//                        break;
//                    }
//                }
//
//                if (!$is_changed) continue;

                //echo var_export($city);
                $this->urlRouter->cityId=$city[0];
                $this->urlRouter->alterCache($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->force_cache);
                $this->insertBlock($this->top_menu(), 0, '', $this->urlRouter->countryId, $this->urlRouter->cityId);
                $this->insertBlock($this->top_dropdowns(), 99, '', $this->urlRouter->countryId, $this->urlRouter->cityId);
                $this->insertBlock($this->index_country_list(), 2, 'index', $this->urlRouter->countryId, $this->urlRouter->cityId);
            }
        }
        $this->urlRouter->countryId=0;
        $this->urlRouter->cityId=0;
    }
    
    function index_country_list(){  
        //$isRightFB=true;
        $content = '<div class="dl">';
        $content .= '<div class="dur">';
        /*$content .= '<?= $this->fill_ad("zone_2", "adc") ?>'; */
        $q = "select decode(r.ID,1,1,3,2,99,3,4,4,2,9), r.*, c.counter, c.unixtime, c.purposes
            from root r 
            left join counts c on c.country_id={$this->urlRouter->countryId} 
                    and c.root_id=r.id 
                    and c.city_id={$this->urlRouter->cityId} 
                    and c.section_id=0 and c.purpose_id=0 
            where c.counter>0 order by 1";            
        $roots=$this->urlRouter->db->queryResultArray($q);
        
        $sl = strtoupper($this->urlRouter->siteLanguage);
          
        if(count($roots)<5){
            $q = "select decode(r.ID,1,1,3,2,99,3,4,4,2,9), r.*, c.counter, c.unixtime, c.purposes
            from root r 
            left join counts c on c.country_id={$this->urlRouter->countryId} 
                    and c.root_id=r.id 
                    and c.city_id={$this->urlRouter->cityId} 
                    and c.section_id=0 and c.purpose_id=0 
            order by 1";            
            $roots=$this->urlRouter->db->queryResultArray($q);
            //echo $this->urlRouter->countryId,"\n";
            $rootCount=array(
                1 =>    array(0,''),
                2 =>    array(0,''),
                3 =>    array(0,''),
                4 =>    array(0,''),
                99 =>    array(0,'')
            );
            $columns=0;
            for ($i=0;$i<count($roots);$i++){
                //$roots[$i]['COUNTER'] = ($roots[$i]['COUNTER']>5 && $roots[$i]['ID']!=4 && $roots[$i]['ID']!=2) ? $roots[$i]['COUNTER'] : 999999;
                $roots[$i]['COUNTER'] = 999999;
                $rootCount[$roots[$i]['ID']][0]=$roots[$i]['COUNTER'];
                if($roots[$i]['COUNTER'])$columns++;
            }
            
        }else{
            
            $rootCount=array(
                1 =>    array(0,''),
                2 =>    array(0,''),
                3 =>    array(0,''),
                4 =>    array(0,''),
                99 =>    array(0,'')
            );
            $columns=0;
            
            foreach($roots as $root){
                $rootCount[$root['ID']][0]=$root['COUNTER'];
                if($root['COUNTER'])$columns++;
            }
        
        }
        
        if ($this->urlRouter->siteLanguage=="ar") {
            $floatLeft="fl";
            $floatRight="fr";
            $fidx=1;
        } else {
            $floatLeft="fr";
            $floatRight="fl";
            $fidx=2;
        }
        /*
        $initialized=0;
        
        $rootIdx=0;
        $carsCount=0;
        */
        $rootCount=0;
        foreach ($roots as $root) {
            $this->urlRouter->rootId=$root['ID'];
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId=is_numeric($root["PURPOSES"]) ? (int) $root["PURPOSES"]: ($root["ID"] == 3 ? 3 : 0);

            if($root['COUNTER']==999999){
                
                $sections = $this->urlRouter->db->queryCacheResultSimpleArray(
                    "sections_{$this->urlRouter->siteLanguage}_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}",
                    "select s.ID, t.counter, t.unixtime, t.purposes
                    from section s 
                    left join root r on r.id=s.root_id 
                    left join counts t 
                        on t.country_id={$this->urlRouter->countryId}
                        and t.city_id={$this->urlRouter->cityId} 
                        and t.root_id=s.root_id 
                        and t.section_id=s.id
                        and t.purpose_id=0 
                    where s.root_id={$this->urlRouter->rootId}  
                    order by iif(s.ID=r.DIFFER_SECTION_ID,1,0),iif(t.counter=0,0,1), s.NAME_{$sl}",
                    null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache); 
                    
            }else{
                /*cache sections by root*/
                $sections = $this->urlRouter->db->queryCacheResultSimpleArray(
                    "sections_{$this->urlRouter->siteLanguage}_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}",
                    "select s.ID, t.counter, t.unixtime, t.purposes
                    from section s 
                    left join root r on r.id=s.root_id 
                    left join counts t 
                        on t.country_id={$this->urlRouter->countryId}
                        and t.city_id={$this->urlRouter->cityId} 
                        and t.root_id=s.root_id 
                        and t.section_id=s.id
                        and t.purpose_id=0 
                    where s.root_id={$this->urlRouter->rootId} 
                    and t.counter>0 
                    order by iif(s.ID=r.DIFFER_SECTION_ID,1,0), s.NAME_{$sl}",
                    null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache); 
                    
            }

                $this->urlRouter->db->queryCacheResultSimpleArray(
                    "purposes_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}_0",
                    "select p.ID, d.counter, d.unixtime 
                    from purpose p 
                    left join counts d 
                        on d.country_id={$this->urlRouter->countryId} 
                        and d.city_id={$this->urlRouter->cityId} 
                        and d.root_id={$this->urlRouter->rootId} 
                        and d.section_id=0 
                        and d.purpose_id=p.id
                        where d.counter > 0 
                    order by d.counter desc",
                    null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);
            
            $_link = $this->encode();
            $tmp ='';
            $split=0;
            $rowPerColumn=0;
            if($root['ID']==2){
                $tCount=count($sections);
                if($tCount<6)
                    $tmp .= '<ul>';
                elseif ( ($rootCount==0 || $rootCount==3) && $tCount>10 ){
                    $tmp .= '<ul class="u3 uc0">';
                    $split=3;
                }else{
                    $tmp .= '<ul class="u2 uc0">';
                    $split=2;
                }
                if($split){
                    $rowPerColumn=ceil($tCount / $split);
                }
            }else{                
                if($root['ID']!=2){
                     if($columns>3 && ( ($rootCount<3 && $root['ID']==4) || ($root['ID']!=4) ) ){
                        $tmp .= '<ul class="uhl">';
                     }else{
                         $tmp .= '<ul>'; 
                     }
                 }else{
                    $tmp .= '<ul>';                     
                 }
            }
            $tmp.="<li class='h'><a href='{$_link}'><span class='i i{$root['ID']}'></span>{$root[$this->field_name]}</a></li>";
            $pass=0;
            $colIndex=1;
            foreach($sections as $section) {
                //if($root['ID']==2)echo $section[0],"\n";
                $section[1] = is_null($section[1]) ? 0 : $section[1];
                $section[2] = is_null($section[2]) ? 0 : $section[2];
                $section[3] = is_null($section[3]) ? '' : $section[3];
                
                $this->urlRouter->sectionId = $section[0];
                $this->urlRouter->purposeId = is_numeric($section[3]) ? $section[3]+0 : ($this->urlRouter->rootId == 3 ? 3: 0);
                if($rowPerColumn){
                    if($pass && ($pass%$rowPerColumn==0)){
                        $tmp.='</ul><ul class="u'.$split.' uc'.$colIndex.'"><li class="h"></li>';
                        $colIndex++;
                    }
                }
                
                
                $tmp.= '<li<?= $this->checkNewUserContent('.$section[2].') ? " class=\"nl\"":"" ?>>'.
                           "<a href='". $this->encode() ."'>".
                            ($root['ID']==2 ?'<span class="z z'.$section[0].'"></span>': 
                               ($root['ID']==1?'<span class="x x'.$section[0].'"></span>':
                                    ($root['ID']==3?'<span class="v v'.$section[0].'"></span>':
                                        ($root['ID']==4?'<span class="y y'.$section[0].'"></span>':
                                            ($root['ID']==99?'<span class="u u'.$section[0].'"></span>':'')
                                        )
                                    )
                                )
                               ).
                            $this->urlRouter->sections[$this->urlRouter->sectionId][$fidx] .
                            " <span>({$section[1]})</span></a></li>";
                 $pass++;
                 if($columns > 3 && $root['ID']!=2){
                     if( ($rootCount<3 && $root['ID']==4 && $pass==5) || ($root['ID']!=4 && $pass==5) ){
                        break;
                     }
                 }
                 
            }
            if ($pass < count($sections)) {
                $tmp.= '<li class="mr">'."<a href='". $_link ."'>".($this->urlRouter->siteLanguage=='ar' ? 'عرض المزيد':'show more' ).'<span class="im go"></span></a></li>';
            }
            if($rowPerColumn){
                $left=($pass%$rowPerColumn);
                $left=$left ? $rowPerColumn - $left : 0 ;                
                for($i=0;$i<$left;$i++){
                    $tmp.='<li></li>';
                }
            }
            $tmp.='</ul>';
            
            if($pass) {
                $content.=$tmp;
                $rootCount++;
            }
        }
        $content.="</div>";
        /*$content .= '<?= $this->renderLoginBox() ?>'; */
        $content .= '<?= $this->fill_ad("zone_2", "adc") ?>';
        $content .= '<div class="g-page gplus" data-href="https://plus.google.com/+MourjanAds/posts" data-width="336" data-layout="landscape" data-rel="publisher"></div>';
        $content.='<div class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="336" data-show-faces="true" data-stream="false" data-show-border="false" data-header="false"></div>';
        $content .= '</div>';
            /*
            foreach($sections as $key => $section) {
                if (in_array($section[0], array(29, 63, 105, 117))) {
                    unset($sections[$key]);
                }
            }
            $split=0;
            $k=0;
            $isDouble=false;
            $perColumn=5;
            $maxRows=$perColumn;
            $clear=($rootIdx && $rootIdx%3==0) ? ' dcr':'';
            
            $tmp='<div class="'.$floatRight.$clear.'">';
            $isOdd=($miniColumns%2==1);
            if ($root['ID']==2 || (!$initialized && $isOdd)) {
                if ($root['ID']==2) {
                    $carsCount=count($sections);
                    $split=ceil($carsCount/2);
                    if ($miniColumns<3) {
                        $isRightFB=false;
                        $tmp='<div class="'.$floatRight.' dcr du2">';
                    }else 
                        $tmp='<div class="dul dcl">';
                }else {
                    $c = count($sections);
                    $maxRows = $perColumn*2;
                    $c = $c > $maxRows ? $maxRows : $c;
                    $split=ceil($c/2);
                    $isDouble=true;
                    $tmp='<div class="'.$floatRight.' du2">';
                    $rootIdx++;
                }
            }
            
            $tmp.='<ul>';
            
            $pass=false;
            $_link = $this->encode();
            
            $tmp.="<li class='h'><a href='{$_link}'><span class='i i{$root['ID']}'></span>{$root[$this->field_name]}</a></li>";
            $firstRow=0;
            foreach($sections as $section) {
                $this->urlRouter->sectionId = $section[0];
                $this->urlRouter->purposeId = is_numeric($section[3]) ? $section[3]+0 : ($this->urlRouter->rootId == 3 ? 3: 0);

                $this->urlRouter->db->queryCacheResultSimpleArray(
                    "purposes_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}_{$this->urlRouter->sectionId}",
                    "select p.ID, d.counter, d.unixtime 
                    from purpose p 
                    left join counts d 
                        on d.country_id={$this->urlRouter->countryId} 
                        and d.city_id={$this->urlRouter->cityId} 
                        and d.root_id={$this->urlRouter->rootId} 
                        and d.section_id={$this->urlRouter->sectionId}
                        and d.purpose_id=p.id
                        where d.counter > 0 
                    order by d.counter desc",
                    null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);

                //if (!in_array($this->urlRouter->sectionId, array(29, 63, 105, 117))) {
                    $pass=true;
                    
                    if ($split && $root['ID']==2) {
                        if ($k==$split) {
                            $firstRow=$k;
                            $tmp.='</ul>';
                            $tmp.='<ul><li class="h"></li>';
                        }
                    }

                    $tmp.= '<li<?= $this->checkNewUserContent('.$section[2].') ? " class=\"nl\"":"" ?>>'.
                           "<a href='". $this->encode() ."'>".
                            ($root['ID']==2 ?'<span class="z z'.$section[0].'"></span>': 
                               ($root['ID']==1?'<span class="x x'.$section[0].'"></span>':'')
                               ).
                            $this->urlRouter->sections[$this->urlRouter->sectionId][$fidx] .
                            " <span>({$section[1]})</span></a></li>";
                    
                    $k++;
                //}
                
                if ($root['ID']!=2 && $k==$maxRows) break;
            }
            if ($firstRow){
                $dif = ($firstRow*2)-$k;
                for($i=0; $i<$dif; $i++){
                    $tmp.='<li></li>';
                }
            }
            
            if ($root['ID']!=2 && $k < count($sections)) {
                if ($isDouble) {
                    $tmp.= '</ul><ul class="ua"><li class="mr">'."<a href='". $_link ."'>".($this->urlRouter->siteLanguage=='ar' ? 'عرض المزيد':'show more' ).'<span class="ico down"></span></a></li></ul></div>';
                }else {
                    $tmp.= '<li class="mr">'."<a href='". $_link ."'>".($this->urlRouter->siteLanguage=='ar' ? 'عرض المزيد':'show more' ).'<span class="ico down"></span></a></li></ul></div>';
                }
            }else {            
                $tmp.="</ul></div>";
            }
            if ($pass) {
                $content.=$tmp;
                $initialized=true;
                $rootIdx++;
            }
        }
        
        if ($carsCount < 11) $isRightFB=false;
        
       // if ($isRightFB)
       //     $content.='<div class="fb-like-box duf '.$floatLeft.' dcr" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="336" data-show-faces="true" data-stream="true" data-show-border="false" data-header="false"></div>';
       // else
            $content.='<div class="fb-like-box fl dcl" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="336" data-show-faces="true" data-stream="true" data-show-border="false" data-header="false"></div>';
            
        $content.='</div>';
             * 
             */
        return $content;
    }

    
    function index_country_list2(){
        $q = "select decode(r.ID,1,1,2,9,3,3,4,4,99,2), r.*, c.counter, c.unixtime, c.purposes
            from root r 
            left join counts c on c.country_id={$this->urlRouter->countryId} 
                    and c.root_id=r.id 
                    and c.city_id={$this->urlRouter->cityId} 
                    and c.section_id=0 and c.purpose_id=0 
            where c.counter>0 order by 1";
                    
        $roots=$this->urlRouter->db->queryResultArray($q);
        $content="<div class='dl'>";

        $k=0;
        $closed=false;
        $open=false;

        $sl = strtoupper($this->urlRouter->siteLanguage);
        /*
        $rootCount=array();
        foreach ($roots as $root){
            $this->urlRouter->rootId=$root['ID'];
            $sections = $this->urlRouter->db->queryCacheResultSimpleArray(
                "sections_{$this->urlRouter->siteLanguage}_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}",
                "select s.ID, t.counter, t.unixtime, t.purposes
                from section s 
                left join root r on r.id=s.root_id 
                left join counts t 
                    on t.country_id={$this->urlRouter->countryId}
                    and t.city_id={$this->urlRouter->cityId} 
                    and t.root_id=s.root_id 
                    and t.section_id=s.id
                    and t.purpose_id=0 
                where s.root_id={$this->urlRouter->rootId} 
                and t.counter>0 
                order by iif(s.ID=r.DIFFER_SECTION_ID,1,0), s.NAME_{$sl}",
                null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);
            $rootCount[$root['ID']]=array(count($sections),'');
        }*/
        $rootCount=array(
            1 =>    array(0,''),
            2 =>    array(0,''),
            3 =>    array(0,''),
            4 =>    array(0,''),
            99 =>    array(0,'')
        );
        foreach ($roots as $root) {
            $this->urlRouter->rootId=$root['ID'];
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId=is_numeric($root["PURPOSES"]) ? (int) $root["PURPOSES"]: ($root["ID"] == 3 ? 3 : 0);

            /*cache sections by root*/
            $sections = $this->urlRouter->db->queryCacheResultSimpleArray(
                "sections_{$this->urlRouter->siteLanguage}_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}",
                "select s.ID, t.counter, t.unixtime, t.purposes
                from section s 
                left join root r on r.id=s.root_id 
                left join counts t 
                    on t.country_id={$this->urlRouter->countryId}
                    and t.city_id={$this->urlRouter->cityId} 
                    and t.root_id=s.root_id 
                    and t.section_id=s.id
                    and t.purpose_id=0 
                where s.root_id={$this->urlRouter->rootId} 
                and t.counter>0 
                order by iif(s.ID=r.DIFFER_SECTION_ID,1,0), s.NAME_{$sl}",
                null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);    

            $this->urlRouter->db->queryCacheResultSimpleArray(
                "purposes_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}_0",
                "select p.ID, d.counter, d.unixtime 
                from purpose p 
                left join counts d 
                    on d.country_id={$this->urlRouter->countryId} 
                    and d.city_id={$this->urlRouter->cityId} 
                    and d.root_id={$this->urlRouter->rootId} 
                    and d.section_id=0 
                    and d.purpose_id=p.id
                    where d.counter > 0 
                order by d.counter desc",
                null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);

            $tmp="";
            if ($this->urlRouter->siteLanguage=="ar") {
                $floatLeft="fl";
                $floatRight="fr";
                $fidx=1;
            } else {
                $floatLeft="fr";
                $floatRight="fl";
                $fidx=2;
            }

            //echo $this->urlRouter->rootId, "\n";
            
            $colIndex=0;
            switch ($this->urlRouter->rootId) {
                case 2:
                    /*if ($open && !$closed) {
                        $tmp .= "</div>";
                    }
                    $closed=false;
                    $open=true;
                    $tmp.="<div class='dul'>";
                    /*$tmp .= '<?= $this->fill_ad("zone_2", "adc '.$floatLeft.'") ?>'; */
                    $tmp.="<ul class='p2'>";
                    $colIndex=0;
                    break;
/*
                case 3:
                    if ($open && !$closed) {
                        $tmp .= "</div>";
                        $closed=true;
                        $open=false;
                    }
                    $open=true;
                    $closed=false;
                    $tmp.="<div class='$floatRight'><ul>";
                    break;

                case 1:
                    $open=true;
                    $closed=false;
                    $tmp.="<div class='$floatRight'>";

                case 4:
                    if (!$open) {
                        $open=true;
                        $closed=false;
                        $tmp.="<div class='$floatRight'><ul>";
                    }else 
                        $tmp.="<ul>";
                    break;

                case 99:
                    if (!$open) {
                        $open=true;
                        $closed=false;
                        $tmp.="<div class='$floatRight'><ul>";
                    }else 
                        $tmp.="<ul>";
                    break;
*/
                default:
                    /*if (!$open) {
                        $open=true;
                        $closed=false;
                        $tmp.="<div class='$floatRight'><ul>";
                    }else */
                        $tmp.="<ul>";
                    break;
                
            }
            
            $pass=false;
            $_link = $this->encode();
            $tmp.="<li class='h h{$this->urlRouter->rootId}'><a href='{$_link}'><span></span>{$root[$this->field_name]}</a></li>";

            $dulCount=0;
            if ($this->urlRouter->rootId==2 && !empty($sections)) {
                $new_sec=array();
                foreach($sections as $section) {
                    $_name = $this->urlRouter->sections[$section[0]][$fidx];
                    if (empty($new_sec))
                        $_prev = $_name;

                    if (mb_substr($_prev, 0, 1, "UTF8") != mb_substr($_name, 0, 1, "UTF8"))
                        $new_sec[]=array();

                    $new_sec[] = $section;
                    $_prev = $_name;
                }
                $sections=$new_sec;

                $remaining = 0;
                $total=count($sections);
                if ($this->urlRouter->siteLanguage=='en') {
                    $dulCount=12;
                    $remaining = $total>=$dulCount ? $total-$dulCount : 0;
                    $col_items = ceil($remaining/4);
                    $s = $dulCount-1;
                }else {
                    $dulCount=11;
                    $remaining = $total>=$dulCount ? $total-$dulCount : 0;
                    $col_items = ceil($remaining/4);
                    $s = $dulCount-1;
                }
                //echo $total, "\t", $remaining, "\t", $col_items, "\n";
            }

            $last=0;
            $ulBroken=0;
            $dulCounter=0;
            foreach($sections as $section) {
                if (empty($section)) {
                    if ( !$ulBroken && !($root['ID']=="2" && $colIndex && $s<=0) && ( $dulCounter<$dulCount-1 || $colIndex) ) {                        
                        $tmp.= '<li><br /></li>';
                    }
                    $last=1;
                    $s--;
                    $dulCounter++;
                    continue;
                }
                $dulCounter++;
                $ulBroken=0;
                $this->urlRouter->sectionId = $section[0]+0;
                $this->urlRouter->purposeId = is_numeric($section[3]) ? $section[3]+0 : ($this->urlRouter->rootId == 3 ? 3: 0);

                $this->urlRouter->db->queryCacheResultSimpleArray(
                    "purposes_{$this->urlRouter->countryId}_{$this->urlRouter->cityId}_{$this->urlRouter->rootId}_{$this->urlRouter->sectionId}",
                    "select p.ID, d.counter, d.unixtime 
                    from purpose p 
                    left join counts d 
                        on d.country_id={$this->urlRouter->countryId} 
                        and d.city_id={$this->urlRouter->cityId} 
                        and d.root_id={$this->urlRouter->rootId} 
                        and d.section_id={$this->urlRouter->sectionId}
                        and d.purpose_id=p.id
                        where d.counter > 0 
                    order by d.counter desc",
                    null, 0, $this->urlRouter->cfg['ttl_long'], $this->force_cache);

                if ($this->urlRouter->sectionId!=99) {
                    $pass=true;
                    
                    if ($root['ID']=="2") {
                        if ($s<=0) {
                            $s=$col_items;
                            //if(!$last)$tmp.= '<li><br /></li>';
                            if (!$colIndex) {
                                $tmp.='<br />';
                            }
                            $tmp.= '</ul>';
                            $tmp.='<ul class="p4">';
                            $colIndex++;
                            $ulBroken=1;
                        }
                        $s--;
                    }

                    $tmp.= '<li<?= $this->checkNewUserContent('.$section[2].') ? " class=\"nl\"":"" ?>>'.
                           "<a href='". $this->encode() ."'>".
                            $this->urlRouter->sections[$this->urlRouter->sectionId][$fidx] .
                            " <b>({$section[1]})</b></a></li>";
                    $rootCount[$this->urlRouter->rootId][0]++;
                }
                $last=0;
                
            }
            /*
            if ($root['ID']=="2" && $dulCounter<$dulCount) {
                $dif=$dulCount-$dulCounter;
                for ($m=0;$m<$dif;$m++){
                    $tmp.='<li><br /></li>';
                }
            }*/
            $tmp.="</ul>";
            if ($pass) {
                $rootCount[$this->urlRouter->rootId][1]=$tmp;
                //$content.=$tmp;
            }
            $k++;
        }
        if ($rootCount[1][0]){
            $colCount=$rootCount[1][0]+$rootCount[99][0]+$rootCount[4][0];
            if ($colCount <= $rootCount[3][0]+2){
                //echo "0\t{$this->urlRouter->countryId}\n";
                $content.="<div class='{$floatRight}'>".$rootCount[1][1].$rootCount[99][1].$rootCount[4][1]."</div>";
                $content.="<div class='{$floatRight}'>".$rootCount[3][1]."</div>";
                if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>";            
            }else {
                if ((abs(($rootCount[1][0]+$rootCount[99][0])-($rootCount[3][0].$rootCount[4][0])) < 5) ){
                    //echo "1\t{$this->urlRouter->countryId}\n";
                    $content.="<div class='{$floatRight}'>".$rootCount[1][1].$rootCount[99][1]."</div>";
                    $content.="<div class='{$floatRight}'>".$rootCount[3][1].$rootCount[4][1]."</div>";
                    if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>";  
                }elseif( (abs(($rootCount[1][0]+$rootCount[4][0])-($rootCount[3][0].$rootCount[99][0]))) < 5 ){
                    //echo "2\t{$this->urlRouter->countryId}\n";
                    $content.="<div class='{$floatRight}'>".$rootCount[1][1].$rootCount[4][1]."</div>";
                    $content.="<div class='{$floatRight}'>".$rootCount[3][1].$rootCount[99][1]."</div>";
                    if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>"; 
                }else{
                    
                    if ($rootCount[4][0] <= $rootCount[99][0]){
                        $content.="<div class='{$floatRight}'>".$rootCount[1][1].$rootCount[99][1]."</div>";
                        $content.="<div class='{$floatRight}'>".$rootCount[3][1]."</div>";
                        if ($rootCount[2][0])$content.="<div class='dul um'>".$rootCount[2][1]."</div>"; 
                        
                        $newTmp=preg_split('/<li<\?/', $rootCount[4][1]);
                        $divBy=4;
                        if ($rootCount[2][0]==0) $divBy=1;
                        $cc=ceil($rootCount[4][0]/$divBy);
                        $cnt=count($newTmp);
                        for($i=1;$i<$cnt;$i++){
                            if ( $i>$cc && (($i-1)%$cc)==0){
                                $newTmp[$i]="</ul><ul><li class='h'></li><li<?".$newTmp[$i];
                            }elseif($i) {
                                $newTmp[$i]="<li<?".$newTmp[$i];
                            }
                        }
                        $rootCount[4][1]=  implode('', $newTmp);
                        
                        $replacementUl='<ul class="p4">';
                        if ($rootCount[2][0]==0) $replacementUl='<ul class="p2">';
                        $rootCount[4][1]=  preg_replace('/<ul>/', $replacementUl, $rootCount[4][1]);
                        $content.="<div class='dul'>".$rootCount[4][1]."</div>"; 
                    }else {
                        $content.="<div class='{$floatRight}'>".$rootCount[1][1].$rootCount[4][1]."</div>";
                        $content.="<div class='{$floatRight}'>".$rootCount[3][1]."</div>";
                        if ($rootCount[2][0])$content.="<div class='dul um'>".$rootCount[2][1]."</div>"; 
                       
                        $newTmp=preg_split('/<li<\?/', $rootCount[99][1]);
                        $divBy=4;
                        if ($rootCount[2][0]==0) $divBy=1;
                        $cc=ceil($rootCount[99][0]/$divBy);
                        $cnt=count($newTmp);
                        for($i=1;$i<$cnt;$i++){
                            if ( $i>$cc && (($i-1)%$cc)==0){
                                $newTmp[$i]="</ul><ul><li class='h'></li><li<?".$newTmp[$i];
                            }elseif($i) {
                                $newTmp[$i]="<li<?".$newTmp[$i];
                            }
                        }
                        $rootCount[99][1]=  implode('', $newTmp);
                        $replacementUl='<ul class="p4">';
                        if ($rootCount[2][0]==0) $replacementUl='<ul class="p2">';
                        $rootCount[99][1]=  preg_replace('/<ul>/', $replacementUl, $rootCount[99][1]);
                        $content.="<div class='dul'>".$rootCount[99][1]."</div>"; 
                    }
                } 
            }
        }else {            
            if ($rootCount[3][0]){
                $content.="<div class='{$floatRight}'>".$rootCount[3][1]."</div>";
                if ($rootCount[99][0] || $rootCount[4][0]) {
                    $content.="<div class='{$floatRight}'>".$rootCount[99][1].$rootCount[4][1]."</div>";
                }
                if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>";
            }else {
                if ($rootCount[99][0]){
                    $content.="<div class='{$floatRight}'>".$rootCount[99][1]."</div>";
                    if ($rootCount[4][0])$content.="<div class='{$floatRight}'>".$rootCount[4][1]."</div>";
                    if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>";
                }elseif ($rootCount[4][0]) {
                    $content.="<div class='{$floatRight}'>".$rootCount[4][1]."</div>";
                    if ($rootCount[2][0])$content.="<div class='dul'>".$rootCount[2][1]."</div>";
                }
            }
        }
        $content.='<div class="dul dulf"><div class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="458" data-show-faces="true" data-stream="true" data-border-color="#CEDAF4" data-header="false"></div></div>';
        /*
        if ($open && !$closed) {
            $content .= "</div>";
            $closed=true;
            $open=false;
        }*/
        $content.="</div>";
        return $content;
    }

    
    function top_menu(){
        $q = "select  decode(r.ID,1,2,2,1,3,3,4,4,99,99), r.*, c.counter, c.unixtime, c.purposes
            from root r 
            left join counts c on c.country_id={$this->urlRouter->countryId} 
                and c.root_id=r.id 
                and c.city_id={$this->urlRouter->cityId} 
                and c.section_id=0 and c.purpose_id=0 
            where r.blocked=0 and c.counter>0
            order by 1";
        $roots=$this->urlRouter->db->queryResultArray($q);
        $content="<div id='menu' class='menu'><div class='w sh rct'>";

        
        $k=0;
        //$content .= "<div class='row rct'>";
        $this->urlRouter->sectionId=0;
        $this->urlRouter->purposeId=0;
        foreach ($roots as $root) {
            $this->urlRouter->rootId = $root['ID'];

            if ($this->urlRouter->rootId==4)
                $this->urlRouter->purposeId=0;
            else
                $this->urlRouter->purposeId=is_numeric($root['PURPOSES']) ? $root['PURPOSES']+0:0;

            $_link=$this->encode();
            //if ($this->urlRouter->rootId==4)
            //    echo $this->urlRouter->countryId, "\t", $this->urlRouter->cityId,"\t", $this->urlRouter->sectionId, "\t", $this->urlRouter->purposeId, "\t", $_link, "\n";
            $content .= "<a id='m{$k}' href='{$_link}'><span class='i i{$root['ID']}'></span>{$root[$this->field_name]}</a>";
            
            $k++;
        }
        $content .= "<span id='m00' class='c'><?= \$this->countryCounter;?></span></div></div>";
        return $content;
    }

    
    
    function top_dropdowns(){
        $q = "select decode(r.ID,1,2,2,1,3,3,4,4,99,99), r.*, c.counter, c.unixtime,C.PURPOSES
            from root r left 
            join counts c on c.country_id={$this->urlRouter->countryId} 
                and c.root_id=r.id and c.city_id={$this->urlRouter->cityId} 
                and c.section_id=0 and c.purpose_id=0 
            where c.counter>0 order by 1";
                
        $roots=$this->urlRouter->db->queryResultArray($q);
        $content="";
        $ulContent="";
        $k=0;
        foreach ($roots as $root) {
            $this->urlRouter->rootId = $root['ID']+0;
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId=is_numeric($root['PURPOSES']) ? (int) $root['PURPOSES']:0;

            $q="select s.*, d.counter, d.unixtime, d.purposes
                from section s 
                left join counts d 
                        on d.country_id={$this->urlRouter->countryId} 
                        and d.city_id={$this->urlRouter->cityId} 
                        and d.root_id=s.root_id 
                        and d.section_id=s.id 
                        and d.purpose_id=0
                where s.root_id={$this->urlRouter->rootId}
                and d.counter>0 
                order by s.{$this->field_name}";
                
            $sections = $this->urlRouter->db->queryResultArray($q);
            $tmp="";
            $split=5;
            $shadow='shr';
            if ($this->urlRouter->siteLanguage=='ar')$shadow='shl';
            $count=count($sections);
            if ($count<11) {
                $tmp.="<ul id='um{$k}' class='mul sh'>";
                //$split=0;
            }elseif($count<21 || $root['ID']==99 || ($this->urlRouter->siteLanguage=='en' && $root['ID']==4)){
                $tmp.="<ul id='um{$k}' class='mul sh'>";
                //$split=2;
            }elseif($count<31 || $root['ID']==1 || $root['ID']==3 || $root['ID']==4){
                $tmp.="<ul id='um{$k}' class='mul sh'>";
                //$split=3;
            }else {
                $tmp.="<ul id='um{$k}' class='mul sh'>";
                //$split=4;
            }
            /*
            switch ($this->urlRouter->rootId) {
                case 2: 
                    $tmp.="<ul id='um{$k}' class='mul u4 {$shadow}'>";
                    $split=4;
                    break;
                case 3:
                    $tmp.="<ul id='um{$k}' class='mul u3 {$shadow}'>";
                    $split=3;
                    break;
                case 99:
                case 1:
                case 4:
                        $tmp.="<ul id='um{$k}' class='mul u2 {$shadow}'>";
                        $split=2;
                    break;
                default:
                    $tmp.="<ul id='um{$k}' class='mul {$shadow}'>";
                    break;
            }*/
            
            $pass=false;
            $i=0;
            $part=0;
            if ($split) $part=ceil($count/$split);
            $iTmp='';
            foreach($sections as $section) {
                if ($section['ID']==63 || $section['ID']==117 || $section['ID']==105 || $section['ID']==29) {
                    $pass=true;
                    $this->urlRouter->sectionId = $section['ID'];
                    $this->urlRouter->purposeId=is_numeric($section['PURPOSES']) ? (int) $section['PURPOSES']:0;

                    $_link = $this->encode();
                    $iTmp.= '<li <?= $this->checkNewUserContent('.$section['UNIXTIME'].')?';
                    $iTmp.='" class=\"nl\""';
                    $iTmp.=":''?>>";
                    $iTmp.= "<a href='{$_link}'>";
                    if($this->urlRouter->rootId==1){
                        $iTmp.='<span class="x x'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==2){
                        $iTmp.='<span class="z z'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==3){
                        $iTmp.='<span class="v v'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==4){
                        $iTmp.='<span class="y y'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==99){
                        $iTmp.='<span class="u u'.$section['ID'].'"></span>';
                    }else {
                        $iTmp.='<span class="v'.$section['ID'].'"></span>';
                    }
                    $iTmp.=$section[$this->field_name]." <b>({$section['COUNTER']})</b></a></li>";
                }else{
                    if ($split){
                        if ($i==0) $tmp.='<li><ul class="ml">';
                        elseif ( ($i)%$part==0) $tmp.='</ul></li><li><ul class="ml">';
                    }
                    $pass=true;
                    $this->urlRouter->sectionId = $section['ID'];
                    $this->urlRouter->purposeId=is_numeric($section['PURPOSES']) ? (int) $section['PURPOSES']:0;

                    $_link = $this->encode();
                    $tmp.= '<li <?= $this->checkNewUserContent('.$section['UNIXTIME'].')?';
                    $tmp.='" class=\"nl\""';
                    $tmp.=":''?>>";
                    $tmp.= "<a href='{$_link}'>";
                    if($this->urlRouter->rootId==1){
                        $tmp.='<span class="x x'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==2){
                        $tmp.='<span class="z z'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==3){
                        $tmp.='<span class="v v'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==4){
                        $tmp.='<span class="y y'.$section['ID'].'"></span>';
                    }elseif($this->urlRouter->rootId==99){
                        $tmp.='<span class="u u'.$section['ID'].'"></span>';
                    }else{
                        $tmp.='<span class="v'.$section['ID'].'"></span>';
                    }
                    $tmp.=$section[$this->field_name]." <b>({$section['COUNTER']})</b></a></li>";
                    $i++;
                }
            }
            if($iTmp){
                $tmp.=$iTmp;
            }
            if ($split) $tmp.='</ul></li>';
            $tmp.="</ul>";
            if ($pass) $ulContent.=$tmp;
            $k++;
        }
        
        $content .= $ulContent;
        $content .= $this->countryUL;
        //echo $content, "\n";
        return $content;
    }

    
    function insertBlock($content, $zone_id, $module_name='', $country_id=9999, $city_id=9999){        
        $cacheFile = "";
        if ($module_name)
            $cacheFile.="$module_name-";
        
        if ($country_id>0) 
            $cacheFile.=$this->urlRouter->countries[$country_id][3]."-";
        else
            $cacheFile.="zz-";
        
        $cacheFile.= "{$this->urlRouter->siteLanguage}-{$city_id}-{$zone_id}.php";
                
        $cacheHandle = fopen($this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}", 'w');
        fwrite($cacheHandle, $content);
        fclose($cacheHandle);
        //echo $this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}", "\n";
        return true; 
    }

  
}

if (php_sapi_name()=='cli'){
    new GenModules($router, $force);
}

//var_dump($_SERVER);

?>