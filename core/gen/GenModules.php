<?php

ini_set('memory_limit', '2048M');
ini_set('error_reporting', E_ALL);
ini_set('display_error', 1);

$force = TRUE;
$invalidate = FALSE;

if (get_cfg_var("mourjan.server_id")=='99' && (PHP_VERSION_ID >= 70000)) {
    include get_cfg_var("mourjan.path").'/xhgui/external/header.php';    
}

if (php_sapi_name()=='cli') {
    $root_path = dirname(dirname(dirname(__FILE__)));
    
    include $root_path.'/index.php';

    if ($argc>1) {

        if ($argc == 3 && $argv[1] == 'counter') {
            $force = FALSE;
        } else {
            if ($argv[1]=='--invalidate') {
                $invalidate = true;
            } else {
                $force = intval($argv[1]) + 0;
            }
        }


    }
}

require_once $config['dir'] . '/core/layout/Site.php';


class GenModules extends Site
{

    var $num=10, $start=0;
    var $log=null,$last_id=0,$current_id;

    var $publicationId=0,$orderId=0,$rangeId=0;
    var $rootName="", $countryName="", $categoryName="", $sectionName="", $purposeName="";

    var $web_modules = "dev_modules";
    
    var $encode_statement = null; // deprecated
    var $topdropdown_roots_st = null;
    var $topdropdown_sections_st_en = null;
    var $topdropdown_sections_st_ar = null;
    var $force_cache;
    var $links;
    private $files;
    
    
    function __construct($router, $fc=TRUE)
    {        
        $this->files = [];
        $this->force_cache=$fc;
        
        parent::__construct($router);

        //$this->memgc();
        
        //$ps = $this->urlRouter->db->prepareQuery("SELECT COUNTRY_ID||':'||CITY_ID||':'||ROOT_ID||':'||SECTION_ID||':'||PURPOSE_ID CODE, PATH FROM URI");
        //$ps->execute();
        //$this->links = $ps->fetchAll(PDO::FETCH_KEY_PAIR);
        //$ps->closeCursor();

        //$this->generate("ar");
        //$this->generate("en");

    }


    function __destruct()
    {
        foreach ($this->files as $oldfile => $newfile)
        {
            $oldname = $this->urlRouter->cfg["dir"]."/core/gen/cache/".$oldfile;
            $newname = $this->urlRouter->cfg["dir"]."/core/gen/cache/".$newfile;
            if (file_exists($oldname)) {
                if (!rename($oldname, $newname)) {
                    echo "Error renming $oldfile to $newfile", "\n";
                }
            }
        }
        parent::__destruct();
    }


    function encode()
    {        
        $result = '';
        $pp = ($this->urlRouter->rootId==4)?0:$this->urlRouter->purposeId;
        $_code = $this->urlRouter->countryId.':'.$this->urlRouter->cityId.':'.$this->urlRouter->rootId.':'.$this->urlRouter->sectionId.':'.$pp;
        if (isset($this->links[$_code])) {
            $result.= $this->links[$_code].'/';
        } else {
            $result='/';
        }     
        if ($this->urlRouter->siteLanguage!='ar') {
            $result.=$this->urlRouter->siteLanguage.'/';
        }
        
        return $result;
    }

    
    function generate($lang="en")
    {
        if (!$this->links)
        {
            $ps = $this->urlRouter->db->prepareQuery("SELECT COUNTRY_ID||':'||CITY_ID||':'||ROOT_ID||':'||SECTION_ID||':'||PURPOSE_ID CODE, PATH FROM URI");
            $ps->execute();
            $this->links = $ps->fetchAll(PDO::FETCH_KEY_PAIR);
            $ps->closeCursor();
        }

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
        
        $countryOrder=[
            4,0,
            1,2,0,
            3,7,8,0,
            9,5,6,0,
            161,145,10,11,12,15,106,122
            ];
        
        $i=0;
        foreach ($countryOrder as $cid) {
            if($cid==0) {
                $cntUl.='</ul></li><li><ul class="ml">';
                continue;
            }
            if (!isset($this->urlRouter->countries[$cid]))continue;

            $country = $this->urlRouter->countries[$cid];
            
            if ($i==0) $cntUl.='<li><ul class="ml">';
            
            $this->urlRouter->countryId=$cid;
            
            $this->urlRouter->cityId=0;
            $this->urlRouter->siteLanguage='ar';
            $_link = $this->encode();
            $cntUl.='<li><a href="javascript:cl(\''.$_link.'\')"><span class="cf c'.$this->urlRouter->countryId.'"></span>'.$country['name'].'</a></li>';
            $i++;
            foreach($country['cities'] as $cid=>$city){
                $this->urlRouter->cityId=$cid;
                $_link = $this->encode();
                $cntUl.='<li><a class="pa" href="javascript:cl(\''.$_link.'\')">'.$city['name'].'</a></li>';
                $i++;
            }
            $this->urlRouter->siteLanguage=$lang;
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
        
        $this->insertBlock($this->top_menu(), 0, '', 0, 0);
        $this->insertBlock($this->top_dropdowns(), 99, '', 0, 0 );
        $this->insertBlock($this->index_country_list(), 2, 'index', 0, 0);

        foreach ($this->urlRouter->countries as $this->urlRouter->countryId=>$country) {
            $this->urlRouter->cityId=0;
            $this->urlRouter->rootId=0;
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId=0;
            
            //$this->urlRouter->alterCache($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->force_cache);

            $this->insertBlock($this->top_menu(), 0, '', $this->urlRouter->countryId, 0);
            $this->insertBlock($this->top_dropdowns(), 99, '', $this->urlRouter->countryId, 0);
            $this->insertBlock($this->index_country_list(), 2, 'index', $this->urlRouter->countryId, 0);

            foreach ($country['cities'] as $this->urlRouter->cityId=>$city) {
                //$this->urlRouter->alterCache($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->sectionId, $this->force_cache);
                $this->insertBlock($this->top_menu(), 0, '', $this->urlRouter->countryId, $this->urlRouter->cityId);
                $this->insertBlock($this->top_dropdowns(), 99, '', $this->urlRouter->countryId, $this->urlRouter->cityId);
                $this->insertBlock($this->index_country_list(), 2, 'index', $this->urlRouter->countryId, $this->urlRouter->cityId);
            }
        }
        $this->urlRouter->countryId=0;
        $this->urlRouter->cityId=0;
    }

    
    function index_country_list(){  
        $content = '<div class="dl">';
        $content .= '<div class="dur">';
       
        $roots=[];
        $tr = $this->urlRouter->db->getRootsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->siteLanguage);
        if (isset($tr[1])) $roots[1]=$tr[1];
        if (isset($tr[3])) $roots[3]=$tr[3];
        if (isset($tr[99])) $roots[99]=$tr[99];
        if (isset($tr[4])) $roots[4]=$tr[4];
        if (isset($tr[2])) $roots[2]=$tr[2];
        unset($tr);
        $sl = strtoupper($this->urlRouter->siteLanguage);

        $columns=0;        
        $rootCount=[1 => [0, ''], 2 => [0, ''], 3 => [0, ''], 4 => [0, ''], 99 => [0, '']];
        foreach($roots as $root_id=>$root){
            $rootCount[$root_id][0] = $root['counter'];
            if($root['counter']) $columns++;
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
        
        $rootCount=0;
        foreach ($roots as $this->urlRouter->rootId=>$root) {
            if (empty($root))                continue;
            $this->urlRouter->sectionId=0;
            if ($this->urlRouter->rootId==3) {
                $this->urlRouter->purposeId = 0;
            } else {
                $this->urlRouter->purposeId = (count($root['purposes'])==1) ? array_keys($root['purposes'])[0] : 0;
            }
            $sections = $this->urlRouter->db->getSectionsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->siteLanguage);
                     
            //if ($this->urlRouter->countryId==1 && $this->urlRouter->rootId!=0) {
                $arr2 = $this->array_msort($sections, array('counter'=>SORT_DESC, 'name'=>SORT_ASC));
                //var_dump($arr2);
                $sections = $arr2;
                
            //}
            
            $_link = $this->encode();
            $tmp ='';
            $split=0;
            $rowPerColumn=0;
            if($this->urlRouter->rootId==2){
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
                if($this->urlRouter->rootId!=2){
                     if($columns>3 && ( ($rootCount<3 && $this->urlRouter->rootId==4) || ($this->urlRouter->rootId!=4) ) ){
                        $tmp .= '<ul class="uhl">';
                     }else{
                         $tmp .= '<ul>'; 
                     }
                 }else{
                    $tmp .= '<ul>';                     
                 }
            }
            $tmp.="<li class='h'><a href='{$_link}'><span class='i i{$this->urlRouter->rootId}'></span>{$root['name']}</a></li>";
            $pass=0;
            $colIndex=1;
            
            foreach($sections as $this->urlRouter->sectionId=>$section) {
                if ($this->urlRouter->rootId==3) {
                    $this->urlRouter->purposeId = 3;
                } else {
                    $this->urlRouter->purposeId = (count($section['purposes'])==1) ? array_keys($section['purposes'])[0] : 0;
                }
                if($rowPerColumn){
                    if($pass && ($pass%$rowPerColumn==0)){
                        $tmp.='</ul><ul class="u'.$split.' uc'.$colIndex.'"><li class="h"></li>';
                        $colIndex++;
                    }
                }
                $tmp.= '<li<?= $this->checkNewUserContent('.$section['unixtime'].') ? " class=\"nl\"":"" ?>>'.
                           "<a href='". $this->encode() ."'>".
                            ($this->urlRouter->rootId==2 ?'<span class="z z'.$this->urlRouter->sectionId.'"></span>':
                               ($this->urlRouter->rootId==1?'<span class="x x'.$this->urlRouter->sectionId.'"></span>':
                                    ($this->urlRouter->rootId==3?'<span class="v v'.$this->urlRouter->sectionId.'"></span>':
                                        ($this->urlRouter->rootId==4?'<span class="y y'.$this->urlRouter->sectionId.'"></span>':
                                            ($this->urlRouter->rootId==99?'<span class="u u'.$this->urlRouter->sectionId.'"></span>':'')
                                        )
                                    )
                                )
                               ).
                            $section['name'] .
                            " <span>({$section['counter']})</span></a></li>";
                 $pass++;
                 if($columns > 3 && $this->urlRouter->rootId!=2){
                     if( ($rootCount<3 && $this->urlRouter->rootId==4 && $pass==5) || ($this->urlRouter->rootId!=4 && $pass==5) ){
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
        $content .= '<div class="aps aps336">';
        $content .= '<h3>'.($this->urlRouter->siteLanguage == 'en' ? 'Download <span class="og">mourjan</span> App':'تحميل تطبيق <span class="og">مرجان</span>' ).'</h3>';
        $content .= '<a target="_blank" href="https://itunes.apple.com/app/id876330682?mt=8"><span class="ios"></span></a>';
        $content .= '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.mourjan.classifieds"><span class="android"></span></a>';
        $content .= '</div>';
        //$content .= '<div class="g-page gplus" data-href="https://plus.google.com/+MourjanAds/posts" data-width="336" data-layout="landscape" data-rel="publisher"></div>';
        //$content.='<div class="fb-like-box" data-href="http://www.facebook.com/pages/Mourjan/318337638191015" data-width="336" data-show-faces="true" data-stream="false" data-show-border="false" data-header="false"></div>';
        $content .= '</div>';
        return $content;
    }
    
    
    function top_menu(){
        $roots = $this->urlRouter->db->getRootsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->siteLanguage);
        $content="<div id='menu' class='menu'><div class='w sh rct'>";

        $k=0;
        $this->urlRouter->sectionId=0;
        foreach ($roots as $this->urlRouter->rootId=>$root) {
            $this->urlRouter->purposeId=0;
            if ($this->urlRouter->rootId!=4 && count($root['purposes'])==1) {
                $this->urlRouter->purposeId= array_keys($root['purposes'])[0];
            }

            $_link=$this->encode();
            $content .= "<a id='m{$k}' href='{$_link}'><span class='i i{$this->urlRouter->rootId}'></span>{$root['name']}</a>";
            $k++;

        }
        $content .= "<span id='m00' class='c'><?= \$this->countryCounter;?></span></div></div>";
        return $content;
    }
    

    function array_msort($array, $cols) {
        $temp = $array;
        $cutoff=10;
        
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        
        $ret = array();
        $len = 0;
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                    $ret[$k][$col] = $array[$k][$col];
                
                $len++;
                if ($len>=$cutoff)                
                    break;
            }
            $len++;
            if ($len>=$cutoff)                
                break;
        }
        
        foreach ($temp as $key => $value) {
            if (!array_key_exists($key, $ret)) {
                $ret[$key] = $value;
            }
        }
        
        $movedown = [29, 63, 105, 177];
        foreach ($movedown as $value) {
            if (isset($ret[$value])) {
                $rec = $ret[$value];
                unset($ret[$value]);
                $ret[$value]=$rec;
            }
        }
        
        return $ret;
    }

    
    function top_dropdowns(){
        $content="";
        $ulContent="";
        $k=0;

        $roots = $this->urlRouter->db->getRootsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->siteLanguage);
        foreach ($roots as $this->urlRouter->rootId => $root) {
            $this->urlRouter->sectionId=0;
            $this->urlRouter->purposeId= (count($root['purposes'])==1) ? array_keys($root['purposes'])[0] : 0;

            $sections = $this->urlRouter->db->getSectionsData($this->urlRouter->countryId, $this->urlRouter->cityId, $this->urlRouter->rootId, $this->urlRouter->siteLanguage);
            
            
            
            $tmp="";
            $split=5;
            $shadow = ($this->urlRouter->siteLanguage=='ar') ? 'shl' : 'shr';
            $count=count($sections);
            if ($count<11) {
                $tmp.="<ul id='um{$k}' class='mul sh'>";
            }elseif($count<21 || $this->urlRouter->rootId==99 || ($this->urlRouter->siteLanguage=='en' && $this->urlRouter->rootId==4)){
                $tmp.="<ul id='um{$k}' class='mul sh'>";
            }elseif($count<31 || $this->urlRouter->rootId==1 || $this->urlRouter->rootId==3 || $this->urlRouter->rootId==4){
                $tmp.="<ul id='um{$k}' class='mul sh'>";
            }else {
                $tmp.="<ul id='um{$k}' class='mul sh'>";
            }

            switch ($this->urlRouter->rootId) {
                case 1:
                    $spanPrefix='<span class="x x';
                    break;
                case 2:
                    $spanPrefix='<span class="z z';
                    break;
                case 3:
                    $spanPrefix='<span class="v v';
                    break;
                case 4:
                    $spanPrefix='<span class="y y';
                    break;
                case 99:
                    $spanPrefix='<span class="u u';
                    break;
                default:
                    $spanPrefix='<span class="v';
                    break;
            }

            $pass=false;
            $i=0;
            $part=0;
            if ($split) $part=ceil($count/$split);
            $iTmp='';

            foreach($sections as $this->urlRouter->sectionId=>$section) {
                $this->urlRouter->purposeId=(count($section['purposes'])==1) ? array_keys($section['purposes'])[0] : 0;

                if ($this->urlRouter->sectionId==63 || $this->urlRouter->sectionId==117 || $this->urlRouter->sectionId==105 || $this->urlRouter->sectionId==29) {
                    $pass=true;

                    $_link = $this->encode();
                    $iTmp.= '<li <?= $this->checkNewUserContent('.$section['unixtime'].')?';
                    $iTmp.='" class=\"nl\""';
                    $iTmp.=":''?>>";
                    $iTmp.= "<a href='{$_link}'>".$spanPrefix.$this->urlRouter->sectionId.'"></span>';
                    $iTmp.=$section['name']." <b>({$section['counter']})</b></a></li>";
                }else{
                    if ($split){
                        if ($i==0) $tmp.='<li><ul class="ml">';
                        elseif ( ($i)%$part==0) $tmp.='</ul></li><li><ul class="ml">';
                    }
                    $pass=true;

                    $_link = $this->encode();
                    $tmp.= '<li <?= $this->checkNewUserContent('.$section['unixtime'].')?';
                    $tmp.='" class=\"nl\""';
                    $tmp.=":''?>>";
                    $tmp.= "<a href='{$_link}'>".$spanPrefix.$this->urlRouter->sectionId.'"></span>';
                    $tmp.=$section['name']." <b>({$section['counter']})</b></a></li>";
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
        return $content;
    }

    
    function insertBlock($content, $zone_id, $module_name='', $country_id=9999, $city_id=9999){        
        $cacheFile = "";
        if ($module_name)
            $cacheFile.="$module_name-";
        
        $cacheFile.=($country_id>0) ? $this->urlRouter->countries[$country_id]['uri']."-" : "zz-";        
        $cacheFile.= "{$this->urlRouter->siteLanguage}-{$city_id}-{$zone_id}.php";
        $cacheHandle = fopen($this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}.new", 'w');

        $this->files["{$cacheFile}.new"]=$cacheFile;

        fwrite($cacheHandle, $content);
        if (fclose($cacheHandle)) {
            //echo $this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}.new is written", "\n";
        } else {
            echo $this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}.new failed to close!", "\n";
        }
        //echo $this->urlRouter->cfg["dir"]."/core/gen/cache/{$cacheFile}", "\n";
        return true; 
    }
    
    
    private function deleteMemKeys($prefix, $version, $pattern=FALSE, $cb=NULL) 
    {
        $keys = $this->urlRouter->db->getCache()->keys("{$prefix}*");
        $run = [];
        echo 'GC: ' . $prefix . '*: ';
        if (!empty($keys)) 
        {
            $len = count($keys);
            echo $len . " cached keys";
            $deleted = 0;
            $dk = [];
            $limit = (strstr($prefix, "purpose")===FALSE) ? 10000 : 100000;
            if ($limit>$len) $limit=$len;
            for ($i=0; $i<$limit; $i++) 
            {
                if (preg_match("/(.*)\:(.*)-(\d+)$/", $keys[$i], $ret)===1)
                {
                    $key = $ret[2];
                    $kver = intval($ret[3], 10);

                    if ($kver == $version)
                        continue;
                    
                    if ($kver < $version) {
                        $dk[] = $key.'-'.$kver;
                    }
                    
                    if (count($dk)>300) 
                    {
                        $deleted += $this->urlRouter->db->getCache()->delete( $dk );
                        $dk = [];
                    }

                    if ($pattern!==FALSE && ($cb) && !isset($run[$key]) )
                    {
                        if (preg_match($pattern, $key, $params)==1)
                        {
                            
                            switch ($cb)
                            {
                                case "getCountriesData":
                                    $this->urlRouter->db->getCountriesData($params[1]);
                                    break;
                                
                                case "getRootsData":
                                    $this->urlRouter->db->getRootsData($params[1], $params[2], $params[3]);
                                    break;

                                case "getSectionsData":
                                    $this->urlRouter->db->getSectionsData($params[1], $params[2], $params[3], $params[4]);
                                    break;

                                case "getPurpusesData":
                                    $this->urlRouter->db->getPurpusesData($params[1], $params[2], $params[3], $params[4], $params[5]);
                                    break;
                                
                                case "getLocalitiesData":   
                                    $this->urlRouter->db->getLocalitiesData($params[1], $params[2], ($params[3]==''?NULL:$params[3]), $params[4]);
                                    break;
                                
                                case "getSectionTagsData":
                                    $this->urlRouter->db->getSectionTagsData($params[1], $params[2], $params[3], $params[4]);
                                    break;

                                default:
                                    break;
                            }

                            $run[$key]=1;
                        } else {
                            echo $pattern . "not matching ". $key, "\n";
                        }
                    }
                    
                }
            }

            if (!empty($dk))
                $deleted += $this->urlRouter->db->getCache()->delete( $dk );

            echo ", {$deleted} keys has been deleted", "\n";
        }
        
    }
    
    
    public function memgc() 
    {        
        //echo "Section count version: ", Db::$SectionsVersion, "\n";
        syslog(LOG_INFO, "GC: version before " . Db::$SectionsVersion);
        $this->deleteMemKeys('country-data-', Db::$SectionsVersion, "/country-data-(en|ar)/", "getCountriesData");
        $this->deleteMemKeys('city-data-', Db::$SectionsVersion);
        $this->deleteMemKeys('root-data-', Db::$SectionsVersion, "/(\d+)-(\d+)-(en|ar)/", "getRootsData");
        $this->deleteMemKeys('section-data-', Db::$SectionsVersion, "/(\d+)-(\d+)-(\d+)-(en|ar)/", "getSectionsData");
        $this->deleteMemKeys('purpose-data-', Db::$SectionsVersion, "/(\d+)-(\d+)-(\d+)-(\d+)-(en|ar)/", "getPurpusesData");
        
        //echo "Tags version: ", Db::$TagsVersion, "\n";
        $this->deleteMemKeys('tag-data-', $this->urlRouter->db->getCache()->get("tag-counts-version"), "/(\d+)-(\d+)-(\d+)-(en|ar)/", "getSectionTagsData");
        
        //echo "Localities version: ", Db::$LocalitiesVersion, "\n";
        $this->deleteMemKeys('locality-data-', Db::$LocalitiesVersion, "/(\d+)-(\d+)-(\d+|)-(en|ar)/", "getLocalitiesData");
        $this->deleteMemKeys('extended-purposes-', Db::$LocalitiesVersion);

    }

  
}


if (php_sapi_name()=='cli') {
    //echo ($invalidate ? "invalidate {$argv[2]}" : "loading") . " website modules generation @".date("r"), "\n";

    $generator = new GenModules($router, $force);
    if ($invalidate===TRUE) {
        $iredis=new Redis;
        if ($config['memstore']['tcp']) {
            $iredis->connect($config['memstore']['host'], $config['memstore']['port'], 1, NULL, 100);
        } else {
            $success = $iredis->connect($config['memstore']['socket']);
            if (!$success) {
                error_log("Could not connect to invalidate redis unix socket " . $config['memstore']['socket']);
                exit(0);
            }
        }
        $iredis->select(3);

        $objAsString = $iredis->get($argv[2]);
        if ($objAsString===FALSE)
            exit(0);
        $json = json_decode($objAsString);
        $invalidate_req = $argv[2];
        if (preg_match("/(.+)-(\d+)$/", $argv[2], $matches)) {
            if(isset($matches[1])){
                $invalidate_req = $matches[1];
            }
        }

        syslog(LOG_INFO, 'Invalidating ' . $argv[2] . ' as ' . $invalidate_req);
        //print_r($json);
        switch ($invalidate_req) {

            case 'country-data-invalidate':
                foreach ($json as $p)
                {
                    $generator->urlRouter->db->getCountriesData($p[0]);
                }
                break;

            case 'city-data-invalidate':
                foreach ($json as $p)
                {
                }
                break;

            case 'root-data-invalidate':
                foreach ($json as $p)
                {
                    $generator->urlRouter->db->getRootsData(intval($p[0]), intval($p[1]), $p[2]);
                }
                break;

            case 'section-data-invalidate':
                foreach ($json as $p)
                {
                    $generator->urlRouter->db->getSectionsData(intval($p[0]), intval($p[1]), intval($p[2]), $p[3]);
                    //echo intval($p[0]), "\t", intval($p[1]), "\t", intval($p[2]), "\t", $p[3], "\n";
                }
                break;

            case 'purpose-data-invalidate':
                foreach ($json as $p) {
                    $generator->urlRouter->db->getPurpusesData(
                            intval($p[0]), intval($p[1]), intval($p[2]), intval($p[3]), $p[4]);
                }
                break;

            case 'tag-data-invalidate':
                foreach ($json as $p) {
                    $generator->urlRouter->db->getSectionTagsData(
                            intval($p[0]), intval($p[1]), intval($p[2]), $p[3]);
                }
                break;

            case 'locality-data-invalidate':
                foreach ($json as $p) {
                    $generator->urlRouter->db->getLocalitiesData(
                            intval($p[0]), intval($p[1]), ($p[2]==''?NULL:intval($p[2])), $p[3]);
                }
                break;

            default:
                break;
        }
        
        $iredis->del($argv[2]);
        $iredis->close();
        //echo "finish website invalidate {$argv[2]} @",date("r"), "\n";

    } 
    else {
	syslog(LOG_INFO, "start website modules generation @".date("r"));
        $generator->generate('ar');
        $generator->generate('en');
	syslog(LOG_INFO, "finish website modules generation @".date("r"));
    }
}

?>
