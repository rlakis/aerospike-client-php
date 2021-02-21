<?php
\Config::instance()->incLayoutFile('Page');

use Core\Model\Classifieds;
use Core\Model\Ad;

class Home extends Page {
    
    //private bool $hasBottomBanner=false;
    private array $cache=[];

    function __construct() {
        \header('Vary: User-Agent');
        parent::__construct();
        $this->lang['description']=$this->lang['home_description'];
        if ($this->router->countryId>0) {
            $this->lang['description'].=' '.$this->lang['in'].' '.$this->title;
        }
        else {
            $this->lang['description'].=$this->lang['home_description_all'];
        }
        $this->render();
    }
        
    function side_pane(){
        //$this->renderSideUserPanel();
        $this->renderSideCountries();
        //$this->renderSideLike();
        //echo $this->fill_ad('zone_3', 'ad_s');
    }

    /*
    // deprecated
    function _main_pane(){        
        $adLang='';
        if (!$this->router->isArabic()) { $adLang=$this->router->language.'/'; }
        
        ?><div class="tv rcb"><div class="tx sh"><div class=tz><?= $this->lang['billboard'] ?><p class=ctr><?php
        if (!$this->router->siteTranslate) {
            if ($this->user->info['id']){
                echo '<a class=bt href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
            }
            else {
                echo '<a class="bt login" href="/post/'.$adLang.'" rel="nofollow">'.$this->lang['placeAd'].'</a>';
            }
        }
        ?></p></div></div></div><?php 
        
        parent::_main_pane();
    }

    */
    
    function main_pane() : void {
        echo '<!--googleoff: snippet-->';
        ?><section class="search-box main"><div class="viewable va-center ff-cols"><?php
        $labels=[];
        $kr=\array_keys($this->router->pageRoots);
        foreach ($kr as $id) {
            $labels[$id]="section-dat-{$this->router->countryId}-{$this->router->cityId}-{$id}-{$this->router->language}-c";
        }
        
        if ($this->router->db->as->getCacheMulti($labels, $recs)===\Aerospike::OK) {
            ?><div class=roots><?php
            foreach ($kr as $id) {
                if (!isset($recs[$id])) { continue; }
                $items=$recs[$id];
                
                $osecs=[];
                switch ($id) {
                    case 1:
                        $osecs=[748, 105];
                        break;
                    case 2:
                        $osecs=[75, 117, 1518];
                        break;
                    case 3:
                        $osecs=[29];
                        break;
                    case 4:
                        //$osecs=[29];
                        break;
                    case 99:
                        $osecs=[63];
                        break;
                }

                $append=[];
                foreach ($osecs as $val) {
                    if (isset($items[$val])) {
                        $append[$val]=$items[$val];
                        unset($items[$val]);
                    }                        
                }                   
                
                \uasort($items, function($a, $b){ return $b['counter'] <=> $a['counter']; });
                
                $sections=[];
                foreach ($items as $se => $row) {
                    $sections[]=[$se, $row['name'], $row['counter'], $this->checkNewUserContent($row['unixtime'])?1:0, $this->router->getURL($this->router->countryId, $this->router->cityId, $id, $se)];
                }
                foreach ($append as $se => $row) {
                    $sections[]=[$se, $row['name'], $row['counter'], $this->checkNewUserContent($row['unixtime'])?1:0, $this->router->getURL($this->router->countryId, $this->router->cityId, $id, $se)];
                    
                }
                if ($id===4) {
                    $this->router->logger()->info(\json_encode($items, \JSON_PRETTY_PRINT));
                }
                ?><div class=large data-ro="<?=$id?>" data-sections='<?=\json_encode($sections)?>' onclick="rootWidget(this);"><?php
                //if ($id==2) {                    
                    ?><i class=icr><svg><use xlink:href="<?=Config::instance()->cssURL?>/1.0/assets/ro.svg#<?=$id?>" /></svg></i><?php
                //}
                //else {                    
                /*    ?><div class=row><i class="icn ro i<?=$id?>"></i></div><?php*/
                //}

                ?><span class=row><?=$this->router->roots[$id][$this->name]?></span><?php
                /*?><div class=bar></div></div><?php*/
                ?></div><?php
            }
            ?></div><div id=rs class="col-12 lrs"></div><?php
        }
        ?></section><?php
        
        echo '<main>';
        
        $this->recommendedForYou();
        $this->searchingNow();
        $this->recentUploads();
        $this->mostPopular();
        

        echo '<!--googleon: snippet-->';
        $this->inlineJS('util.js')->inlineJS('home.js');
    }
    
    
    public function mostPopular() : void {
        if (1) return;
        ?><div class="row viewable pc" style="margin-bottom:40px"><div class=col-12><div class="card format1"><header class="plain"><h4><?=$this->lang['active_sections']?></h4></header><?php
        ?><div class="card-content"><?php
        $sections=[];
        $labels=[];
        $kr=\array_keys($this->router->pageRoots);
        foreach ($kr as $id) {
            $labels[$id]="section-dat-{$this->router->countryId}-{$this->router->cityId}-{$id}-{$this->router->language}-c";
        }
        
        
        if ($this->router->db->as->getCacheMulti($labels, $sections)===\Aerospike::OK) {
            //$this->router->logger()->info(\json_encode($records, JSON_PRETTY_PRINT));        
            /*
            if ($this->router->db->as->getCacheMulti($kk, $sections)===\Aerospike::OK) {
                foreach ($kr as $id) {
                    if (!isset($sections[$id])) {
                        $this->router->db->asSectionsData($this->router->countryId, $this->router->cityId, $id, $this->router->language, true);
                        $this->router->db->as->getCacheData("section-dat-{$this->router->countryId}-{$this->router->cityId}-{$id}-{$this->router->language}-c", $sections[$id]);
                    }
                }
            }
            */
            foreach ($kr as $root_id) {
                $items=$sections[$root_id];
                // temporary sort
                \uasort($items, function($a, $b){ return $b['counter'] <=> $a['counter']; });
                //$this->router->logger()->info(\json_encode($items, JSON_PRETTY_PRINT));            
                ?><div class=row><div class=col-12><?php
                ?><div class="col-3 va-center"><span class=icon><i class="icn ro i<?=$root_id?>"></i></span><?php
                ?><span class=name><?=$this->router->roots[$root_id][$this->name]?></span></div><?php
                $i=0;
                ?><div class="col-9 va-center"><?php
                foreach ($items as $section_id => $section) {
                    if ($section['counter']===0) { break; }
                    $url = $this->router->getURL($this->router->countryId, $this->router->cityId, $root_id, $section_id);
                    $cls = $this->checkNewUserContent($section['unixtime']) ? ' hot': '';
                    echo '<a class=btn href="', $url,'">', $section['name'], '<div>(<span class="', $cls, '"><b>', \number_format($section['counter']), '</b></span>)</div></a>';
                    $i++;
                    if ($i>=8) { break; }
                }
                ?></div></div></div><?php
            }
        }
        ?></div><?php
        ?></div></div></div><?php
        
    }


    private function cacheAddKey(string $key, array &$keys) : void {
        if (!isset($this->cache[$key])) {
            $keys[]=$key;
        }
    }
    
    
    public function searchingNow() : void {                       
        if ($this->router->countryId>0) {
            //var_dump($this->rootWidget(1));
            /*
            $keys=[];
            $this->cacheAddKey('top-'.($this->router->cityId>0?'city-'.$this->router->cityId:$this->router->countryId), $keys);
            
            $kr=\array_keys($this->router->pageRoots);
            foreach ($kr as $id) {
                $label="section-dat-{$this->router->countryId}-{$this->router->cityId}-{$id}-{$this->router->language}-c";
                $keys[$id]=$this->router->db->as->initStringKey(\Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
            }
               
            $status=$this->router->db->as->getConnection()->getMany(\array_values($keys), $recs);
            if ($status===\Aerospike::OK) {
                foreach ($recs as $sec) {
                    \preg_match('/section-dat-\d+-\d+-(\d+)-.*', $sec['key']['key'], $matches);
                    if (\is_array($matches) && \count($matches)===2) {
                        $id=$matches[1]+0;                    
                        if ($sec['bins']===null) {
                            $this->router->db->asSectionsData($this->router->countryId, $this->router->cityId, $id, $this->router->language, true);
                        }
                        else {
                            $sections[$id]=$sec['bins']['data'];
                        }
                    }
                }
            }        
            */
            
            $label='top-'.($this->router->cityId>0?'city-'.$this->router->cityId:$this->router->countryId);
            if ($this->router->db->as->getCacheData($label, $record)===\Aerospike::OK) {
                $key=Core\Model\NoSQL::instance()->initStringKey(Core\Data\NS_MOURJAN, \Core\Data\TS_CACHE, $label);
                ?><div class="row viewable"><div class=col-12><div class=hscard><?php
                ?><header class="plain"><h4><?=$this->router->isArabic()?'الأكثر طلباً من القراء':'What other people are looking for...'?></h4></header><?php
                /* <a href="#"><?=$this->lang['more']?></a> */
                $q='select id,rand() as r from ad where hold=0 and canonical_id=0 and media=1 ';
                if ($this->router->cityId>0) {
                    $q.='and city_id='.$this->router->cityId;
                }
                else {
                    $q.='and country_id='.$this->router->countryId;                    
                }
                $filter='';
                $labels=[];
                foreach ($record as $f) {
                    if (!empty($filter)) { $filter.=' or '; }
                    $filter.='(section_id='.$f['se'].' and purpose_id='.$f['pu'].')';
                    $this->cacheAddKey("section-dat-{$this->router->countryId}-{$this->router->cityId}-{$this->router->sections[$f['se']]['root_id']}-{$this->router->language}", $labels);
                }
                if (!empty($filter)) {
                    $q.=' and ('.$filter.')';
                }
                $q.=' order by r desc limit 5';
                $this->router->db->as->getCacheMulti($labels, $records);
                
                /*
                $rs=$this->router->db->ql->search($q);
                if ($rs['total_found']>0) {
                    echo '<div class=col-12>';
                    foreach ($rs['matches'] as $row) {
                        $this->adWidget($row['id']);
                    }
                    echo '</div>';
                }
                */
                $i=0;
                ?><div class="col-12 wad wse"><?php
                foreach ($record as $f) {
                    $this->sectionWidget($f['se'], $f['pu']);
                    $i++;
                    //if ($i>3) { break; }
                }
                ?><div class=space></div><?php
                ?></div></div></div></div><?php
            }
        }
    }
    
    
    public function recommendedForYou() : void {        
        $ql=$this->router->db->ql->resetFilters()->setSelect('id')
                ->region($this->router->countryId, $this->router->cityId)
                ->media()
                ->setSortBy('rand()')
                ->setLimits(0, 5, 100)
                ;
        $rs=$ql->query();
        /*
        $query=new Core\Lib\MCSearch($this->router->db->manticore);
        $rs=$query->mediaFilter()->regionFilter($this->router->countryId, $this->router->cityId)->sort('rand()')->limit(5)->setSource(['id'])->result();
        */
        if ($rs['total_found']>0) {
            ?><div class="row viewable"><div class=col-12><div class=hscard><header class=plain><h4><?=$this->router->isArabic()?'اعلانات قد تهمك':'Recommended for you'?></h4></header><?php
            ?><div class="col-12 wad"><?php
            foreach ($rs['matches'] as $id) {
                $this->adWidget($id);
            }
            ?><div class=space></div><?php
            ?></div><?php
            ?></div></div></div><?php
        }                  
    }

    
    public function recentUploads() : void {
        $ql=$this->router->db->ql->resetFilters()->setSelect('id')
                ->region($this->router->countryId, $this->router->cityId)
                ->media()
                ->setSortBy(Core\Lib\MCSearch::DATE_ADDED.' desc')
                ->setLimits(0, 5, 100)
                ;
        $rs=$ql->query();

         //$query=new Core\Lib\MCSearch($this->router->db->manticore);
         //$rs=$query->mediaFilter()->regionFilter($this->router->countryId, $this->router->cityId)->sort(Core\Lib\MCSearch::DATE_ADDED, 'desc')->limit(5)->setSource(['id'])->result();
         if ($rs['total_found']>0) {
             ?><div class="row viewable mb-64"><div class=col-12><div class=hscard><header class="plain"><h4><?=$this->router->isArabic()?'أحدث المنشورات':'Latest uploads'?></h4></header><?php
             ?><div class="col-12 wad"><?php
            foreach ($rs['matches'] as $id) {
                $this->adWidget($id);
            }
            ?><div class=space></div><?php
            ?></div><?php             
            ?></div></div></div><?php
         }                 
    }
    
    
    private function sectionWidget(int $section_id, int $purpose_id) : void {
        //style="background-color:var(--mpc<?=$purpose_id)"
        $status=$this->router->db->as->getCacheData("section-dat-{$this->router->countryId}-{$this->router->cityId}-{$this->router->sections[$section_id]['root_id']}-{$this->router->language}", $root);        
        /*?><div class=ad><div class=widget><?php*/
        ?><div class=ad><a class=widget href="<?=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->sections[$section_id]['root_id'], $section_id, $purpose_id)?>"><?php
        /*?><a class=ff-cols href="<?=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->sections[$section_id]['root_id'], $section_id, $purpose_id)?>"><?php*/
        ?><div class="image seclogo"><?php
        ?><div class=icon><img src="<?=$this->router->config->imgURL?>/se/<?=$section_id?>.svg" /></div><?php
        /*
        ?><i><img class="fpu<?=$purpose_id?>" src="<?=$this->router->config->imgURL?>/pu/<?=$purpose_id?>.svg" /></i><?php
         * 
         */
        ?><i style="background-color:var(--mpc<?=$purpose_id?>)"><img src="<?=$this->router->config->imgURL?>/pu/<?=$purpose_id?>.svg" /></i><?php
        ?><div class="box hint"><?php
        if (isset($root[$section_id]) && isset($root[$section_id]['purposes']) && isset($root[$section_id]['purposes'][$purpose_id])) {            
            ?><div><?=Ad::FormatSinceDate($root[$section_id]['purposes'][$purpose_id]['unixtime'], $this->lang)?></div><?php
            ?><div><?=\number_format($root[$section_id]['purposes'][$purpose_id]['counter']).' '.$this->lang['ads']?></div><?php
        }
        ?></div></div><?php
        ?><div class="content section"><?php
        
        if ($status===\Aerospike::OK) {
            ?><img src="<?=$this->router->config->imgURL?>/pu/<?=$purpose_id?>.svg" /><?php
            echo $this->sectionLabel($section_id, $purpose_id); 
        }
        ?></div></a></div><?php
    }
    
    
    private function adWidget(int $id) : void {
        $ad = new Ad($this->classifieds->getById($id));
                 
        $pic = null;
        $this->appendLocation = true;
            
        if (!($this->user()->level()===9||$this->user->info['id']==$ad->uid())) {
            $this->stat['ad-imp'][]=$id;
        }
            
        if ($ad->hasAltContent()) {
            $langSortIdx = $this->langSortingMode > -1 ? $this->langSortingMode : 0;
                
            if (($langSortIdx==2||!$this->router->isArabic()) && $ad->rtl()) {
                $ad->reverseContent()->setLTR();
                $this->appendLocation = false;
            } 
            elseif (($langSortIdx==1||$this->router->isArabic()) && $ad->rtl()==0) {
                $ad->reverseContent()->setRTL();
                $this->appendLocation = false;
            }
        }
        
        $itemScope = '';
        $itemDesc = '';
        $hasSchema = false;
        if ($ad->isRealEstate()||$ad->isCar()) {
            $hasSchema = true;
            $itemDesc = 'itemprop="description" ';
            $itemScope = ' itemscope itemtype="https://schema.org/Product"';
        }
            
        $isNewToUser = (isset($this->user->params['last_visit']) && $this->user->params['last_visit']>0 && $this->user->params['last_visit'] < $ad->epoch());            
        $textClass = "en";
        $liClass = "";
            
        if ($ad->rtl()) { $textClass = "ar"; }
            
        if ($this->router->siteTranslate) { $textClass = ''; }
            
        $pix_count = $ad->picturesCount();
        if ($pix_count) {
            $pic = '<div class=image>'; //<div class="cbox footer"></div>';                   
            $pix = $ad->picturePath();
            if ($this->router->isAcceptWebP) { $pix = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pix); }                
            $pic.= '<img src="'.$this->router->config->adImgURL.'/repos/m/'.$pix.'" />';                
            if ($pix_count>1) {
                //$pic.='<div class="cbox ctr">'.$pix_count.'&nbsp;<span class="icn icnsmall icn-camera"></span></div>';                  
            }
        }
        else {
            $pic= '<div class="image seclogo">'; //<div class="cbox footer"></div>';
            $pic.='<img src="'.$this->router->config->imgURL.'/se/'.$ad->sectionId().'.svg" />';
        }
            
        //if ($isNewToUser) { $pic.='<div class="cbox ctl new">NEW</div>'; }
        
        if ($ad->publisherType() && \in_array($ad->rootId(), [1,2,3]) && (!$ad->isJob() || ($ad->isJob() && $ad->isVacancies()))) {
            switch ($ad->publisherType()) {
                case 3:
                    $pic.='<div class="cbox cbr" value="a';
                    $pic.=$ad->rootId(). '">'.$this->lang['pub_3_'.$ad->rootId()].'</div>';
                    break;
                case 1:
                    $pic.='<div class="cbox cbr" value="p';
                    if ($ad->isJob()) {                            
                        $pic.='1">'.$this->lang['bpub_1'].'</div>';
                    }
                    else {
                        $pic.='0">'.$this->lang['pub_1'].'</div>';
                    }
                    break;
                default:
                    break;
            }
        }
            
        $pic.='<div class="cbox cbl">'.$ad->formattedSinceDate($this->lang).'</div></div>';            
            
        $favLink = '';

        if ($this->user()->isLoggedIn()) {
            if ($this->userFavorites) {
                $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                $liClass.= 'fon ';
            }
            elseif ($this->user->favorites) {
                if (in_array($ad->id(), $this->user->favorites)) {
                    $favLink = "<span onclick='fv(this)' class='i fav on' title='".$this->lang['removeFav']."'></span>";
                    $liClass.= 'fon ';
                }
            }
        }
                
        $isBot = preg_match('/googlebot/i',$_SERVER['HTTP_USER_AGENT']);
        if ($isBot) {
            if ($this->router->countryId) {
                $this->formatNumbers=strtoupper($this->router->countries[$this->router->countryId]['uri']);
            }
            elseif ($this->user->params['user_country']) {
                $this->formatNumbers=strtoupper($this->user->params['user_country']);                    
            }
        }
        else {
            if ($this->user->params['user_country']??0) {   
                $this->formatNumbers=strtoupper($this->user->params['user_country']);
            } elseif($this->router->countryId) {
                $this->formatNumbers=strtoupper($this->router->countries[$this->router->countryId]['uri']);
            }
        }
        
        echo '<div class=', $ad->isFeatured()?'"ad p" ':'ad ';
        echo $ad->htmlDataAttributes($this->formatNumbers), '>';
        echo '<div class=widget id=', $ad->id(), ' itemprop="itemListElement" ',  $itemScope, '>';
        if ($ad->isFeatured()) {
            ?><img class=mark src="<?=$this->router->config->imgURL?>/prtag-en.svg" /><?php
        }
        echo $pic;
            
        ?><div class=content><p class=<?=$textClass?> <?=$itemDesc?>><?php
        if ($ad->latitude()||$ad->longitude()) {
            echo '<a href="#" title="', $ad->location(), '"><i class="icn icnsmall icn-map-marker"></i></a>';
            echo $ad->text(); 
        }
        else {
            echo $ad->text();
        }

        ?></p></div><?php
        //if ($this->user()->isSuperUser() && isset($this->searchResults['body']['scores'][$ad->id()])) {
        //        echo '<span style="direction:ltr;display:block;padding-left:20px">', $this->searchResults['body']['scores'][$ad->id()], '</span>';
        //    }
        echo '<div class=tail>';    
        echo $this->getAdSection($ad->data(), $hasSchema);
        //echo '<div title="', $this->lang['reportAbuse'], '" class=abuse onclick="event.stopPropagation();report(this);"><i class="icn icn-ban"></i></div>';
        echo $favLink, '</div></div>';
        echo '</div>', "\n";
    }
    
    
    function sectionLabel(int $section_id, int $purpose_id) : string {
        $section = $this->router->sections[$section_id][$this->name];
        switch ($purpose_id) {
            case 1:
            case 2:
            case 8:
            case 999:
                $section = $section . ' ' . $this->router->purposes[$purpose_id][$this->name];
                break;
            case 6:
            case 7:
                $section = $this->router->purposes[$purpose_id][$this->name] . ' ' . $section;
                break;
            case 3:
                if ($this->router->isArabic()) {
                    $in = ' ';
                    $section = 'وظائف ' . $section;
                }
                else {
                    $section = $section . ' ' . $this->router->purposes[$purpose_id][$this->name];
                }
                break;
            case 4:
                $in = ' ';
                if (!$this->router->isArabic()) {  $in = ' ' . $this->lang['in'] . ' ';  }
                $section = $this->router->purposes[$purpose_id][$this->name] . $in . $section;
                break;
            case 5:
                break;
        }
        
        return $section;
        /*
        if (isset($this->router->countries[$this->router->countryId])) {
            $section = '<li><a href="' . 
                    $this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->sections[$section_id]['root_id'], $section_id, $purpose_id) . 
                    '">' . $section . '</a></li>';
        }
        return (stristr($section,'<li>')) ? '<ul>'.$section.'</ul>' : $section;
         * 
         */
    }


    function getAdSection($ad, bool $hasSchema=false, bool $isDetail=false) : string {
        $section = '';        
        $hasLink = true;
        if (!empty($this->router->sections)) {
            $extIDX = $this->router->isArabic() ? Classifieds::EXTENTED_AR : Classifieds::EXTENTED_EN;
            $locIDX = $this->router->isArabic() ? Classifieds::LOCALITIES_AR : Classifieds::LOCALITIES_EN;
            $extID = (isset($ad[$extIDX]) && isset($this->extended[$ad[$extIDX]])) ? $ad[$extIDX] : $this->extendedId;
            $hasLoc = (isset($ad[$locIDX]) && (is_array($ad[$locIDX]) && count($ad[$locIDX])>0) && (is_array($this->localities) && count($this->localities)>0));
            $locID = 0;
            if ($hasLoc) {
                $hasSchema = false;
                $locKeys = array_reverse(array_keys($ad[$locIDX]));
                foreach ($locKeys as $key) {
                    if (isset($this->localities[$key])) {
                        $locID = $key;
                        break;
                    }
                }
            }
    
            if ($isDetail || (!($extID && !$this->extendedId) && (!$hasLoc || ($hasLoc && $locID == $this->localityId)) && $this->router->purposeId && $this->router->sectionId)) {
                $hasLink=false;
            }

            $section = $this->router->sections[$ad[Classifieds::SECTION_ID]][$this->name];
            if ($extID) { $section = $this->extended[$extID]['name']; }
            
            switch ($ad[Classifieds::PURPOSE_ID]) {
                case 1:
                case 2:
                case 8:
                case 999:
                    if ($hasSchema) {
                        $section = '<span><span itemprop="name">' . $section . '</span>&nbsp;' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name].'</span>';
                    }
                    else {
                        $section = $section . ' ' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name];
                    }
                    break;
                case 6:
                case 7:
                    if ($hasSchema) {
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name] . ' <span itemprop="name">' . $section . '</span>';
                    }
                    else {
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name] . ' ' . $section;
                    }
                    break;
                case 3:
                    if ($this->router->isArabic()) {
                        $in = ' ';
                        if ($hasSchema)
                            $section = 'وظائف <span itemprop="name">' . $section . '</span>';
                        else
                            $section = 'وظائف ' . $section;
                    }
                    else {
                        if ($hasSchema)
                            $section = '<span itemprop="name">' . $section . '</span>&nbsp;' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name];
                        else
                            $section = $section . ' ' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name];
                    }
                    break;
                case 4:
                    $in=' ';
                    if (!$this->router->isArabic())
                        $in = ' ' . $this->lang['in'] . ' ';
                    if ($hasSchema)
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name] . $in . '<span itemprop="name">' . $section . '</span>';
                    else
                        $section = $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name] . $in . $section;
                    break;
                case 5:
                    if ($hasSchema)
                        $section = '<span itemprop="name">' . $section . '</span>';
                    else
                        $section = $section;
                    break;
            }
            
            if ($hasLoc) {
                $res='';
                $iter=0;
                foreach ($ad[$locIDX] as $id=>$loc) {
                    if (isset($this->localities[$id])) {
                        if ($iter===0) {
                            $tempSection=$section . ' ' . $this->lang['in'] . ' ' . $this->localities[$id]['name'];
                        }
                        else {
                            $tempSection=$this->localities[$id]['name'];
                        }
                        $iter++;
                        if ($iter>2) {break;}
                        if ($hasLink) {
                            $idx=2;
                            $uri='/'.$this->router->countries[$ad[Classifieds::COUNTRY_ID]][\Core\Data\Schema::BIN_URI].'/';
                            $uri.=$this->localities[$id]['uri'].'/';
                            $uri.=$this->router->sections[$ad[Classifieds::SECTION_ID]][\Core\Data\Schema::BIN_URI].'/';
                            $uri.=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][\Core\Data\Schema::BIN_URI].'/';
                            $uri.=($this->router->isArabic()?'':$this->router->language . '/') . 'c-' . $id . '-' . $idx . '/';
                            $res.='<li id=here><a href="' . $uri . '">' . $tempSection . '</a></li>';
                        }
                        else {
                            if ($res) { $res .= '  »|  '; }
                            $res.=$tempSection;
                        }
                    }
                }
                $section='<span>'.$res.'</span>';
            } 
            else {
                if ($this->router->countryId && $ad[Classifieds::COUNTRY_ID] != $this->router->countryId) {
                    $ad[Classifieds::COUNTRY_ID] = $this->router->countryId;
                    $ad[Classifieds::CITY_ID] = 0;
                }

                if ($this->router->cityId && $ad[Classifieds::CITY_ID] != $this->router->cityId){
                    $ad[Classifieds::CITY_ID] = $this->router->cityId;
                }
                
                if (isset($this->router->countries[$ad[Classifieds::COUNTRY_ID]])) {
                    $countryId=$ad[Classifieds::COUNTRY_ID];
                    $cityId=0;
                    if (!empty($this->router->countries[$countryId]['cities']) && $ad[Classifieds::CITY_ID] && isset($this->router->countries[$countryId]['cities'][$ad[Classifieds::CITY_ID]])) {
                        $cityId=$ad[Classifieds::CITY_ID];
                        if ($this->router->cityId!=$cityId) {
                            $section.=' '.$this->lang['in'].' '.$this->router->countries[$countryId]['cities'][$cityId]['name'];
                        }

                        //if (!mb_strstr($section, $this->router->countries[$countryId]['name'], true, "utf-8")) {
                        //    $section.=' ' . $this->router->countries[$countryId]['name'];
                        //}                                
                    } else {
                        //$section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->countries[$countryId]['name'];                    
                    }
                    
                    if ($hasLink) {
                        if ($extID) {
                            $idx = 2;
                            $uri = '/' . $this->router->countries[$ad[Classifieds::COUNTRY_ID]]['uri'] . '/';
                            if ($cityId) {
                                $idx = 3;
                                $uri.=$this->router->countries[$ad[Classifieds::COUNTRY_ID]]['cities'][$cityId]['uri'] . '/';
                            }
                            $uri.=$this->router->sections[$ad[Classifieds::SECTION_ID]][\Core\Data\Schema::BIN_URI] . '-' . $this->extended[$extID]['uri'] . '/';
                            $uri.=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][\Core\Data\Schema::BIN_URI] . '/';
                            $uri.=($this->router->language == 'ar' ? '' : $this->router->language . '/') . 'q-' . $extID . '-' . $idx . '/';
                            $section = '<li id=thre><a href="' . $uri . '">' . $section . '</a></li>';
                        }
                        else {
                            $section='<li><a href="'.$this->router->getURL($countryId, $cityId, $ad[Classifieds::ROOT_ID], $ad[Classifieds::SECTION_ID], $ad[Classifieds::PURPOSE_ID]).'"><span>'.$section.'</span></a></li>';
                        }
                    }
                }
            }
            
            if ($isDetail && $section) {
                return '<b>' . $section . '</b>';
            } 
            elseif(!$isDetail) {
                return (stristr($section,'<li>')) ? '<ul>'.$section.'</ul>' : $section;
            }
        }
    }
            
}
