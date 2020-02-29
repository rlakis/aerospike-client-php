<?php
\Config::instance()->incLayoutFile('Page');

use Core\Model\Classifieds;
use Core\Model\Ad;

class Home extends Page {
    
    private bool $hasBottomBanner=false;
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


    function main_pane() : void {
        echo '<!--googleoff: snippet-->';
        ?><section class=search-box style="margin-bottom:8px; box-shadow: 0 1px 4px aliceblue;"><div class="viewable va-center ff-cols"><?php
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
                if ($id===1) {
                    $this->router->logger()->info(\json_encode($items, \JSON_PRETTY_PRINT));
                }
                ?><div class=large data-ro="<?=$id?>" data-sections='<?=\json_encode($sections)?>' onclick="rootWidget(this);"><?php
                ?><div class=row><i class="icn ro i<?=$id?>"></i></div><?php
                ?><span class=row><?=$this->router->roots[$id][$this->name]?></span><?php
                ?><div class=bar></div></div><?php 
            }
            ?></div><div id="rs" class="col-12 lrs"></div><?php
        }
        ?></section><?php
        
        echo '<main>';
        /*
        echo '<div class="row viewable home">';                
        foreach ($sections as $root_id => $items) {
            echo '<div class=col-4><div class=card>';
            echo '<div class=card-header style="background-color:var(--color-',$root_id,');"><i class="icn icn-', $root_id, '"></i></div>';
            echo '<div class=card-content>';
            echo '<h4 class=card-title>', $this->router->pageRoots[$root_id]['name'],'</h4>';
            echo '<ul>';
            $i=0;
            foreach ($items as $section_id => $section) {
                if ($section['counter']==0) { break; }
                $link = $this->router->getURL($this->router->countryId, $this->router->cityId, $root_id, $section_id);
                $cls = $this->checkNewUserContent($section['unixtime']) ? ' hot': '';
                echo '<li><a href="', $link,'">', $section['name'], '<span class="float-right', $cls, '">', \number_format($section['counter'],0), '</span></a></li>';
                $i++;
                if ($i>=10) { break; }
            }
            echo '</ul>';
            echo '</div></div></div>';
        }*/
        
        /*
        echo '<div class=col-4><div class="card test"><div class=card-content>';
        echo '<ul>';
        echo '<li><a href="', $this->router->getLanguagePath('/post/'), '"><i class="icn s icn-82"></i><span>', $this->lang['postFree'], '</span></a></li>';
        if ($this->user()->id()) {
            $balance_label= $this->lang['myBalance']. ' is '.$this->user()->getBalance() . ' coins';
            echo '<li><a href="', $this->router->getLanguagePath('/statement/'), '"><i class="icn icnsmall icn-84"></i><span>', $balance_label, '</span></a></li>';
        }
        echo '<li><a href="', $this->router->getLanguagePath('/contact/'), '"><i class="icn s icn-88"></i><span>', $this->lang['contactUs'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/about/'), '"><i class="icn s icn-83"></i><span>', $this->lang['aboutUs'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/terms/'), '"><i class="icn s icn-85"></i><span>', $this->lang['termsConditions'], '</span></a></li>';
        echo '<li><a href="', $this->router->getLanguagePath('/privacy/'), '"><i class="icn s icn-81"></i><span>', $this->lang['privacyPolicy'], '</span></a></li>';
        echo '</ul></div></div>', "\n"; // card
        echo '</div>'; // col-4
        echo '</div>', "\n";
        */
        
        $this->searchingNow();
        $this->recommendedForYou();
        $this->recentUploads();
        $this->mostPopular();
        

        echo '<!--googleon: snippet-->';
        $this->inlineJS('util.js')->inlineJS('home.js');
    }
    
    
    public function mostPopular() : void {
        ?><div class="row viewable" style="margin-bottom:40px"><div class=col-12><div class="card format1"><header class="plain"><h4>Most active sections</h4></header><?php
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
                ?><div class="col-3 va-center"><span style="display:inherit;width:60px;justify-content:center;margin-inline-end:8px"><i class="icn ro i<?=$root_id?>"></i></span><?php
                ?><span style="color:var(--mcLightColor);font-size:14pt;font-weight:bold"><?=$this->router->roots[$root_id][$this->name]?></span></div><?php
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
                ?><div class="row viewable"><div class=col-12><div class="card format2"><?php
                ?><header class="plain"><h4><?=$this->router->isArabic()?'الأكثر طلباً من القراء':'What other people are looking for...'?></h4><a href="#">View All</a></header><?php
               
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
                echo '<div style="max-height:310px;" class=col-12>';
                foreach ($record as $f) {
                    $this->sectionWidget($f['se'], $f['pu']);
                    $i++;
                    if ($i>3) { break; }
                }
                echo '</div>';
                ?></div></div></div><?php
            }
        }
    }
    
    
    public function recommendedForYou() : void {
        ?><div class="row viewable"><div class=col-12><div class="card format2"><header class="plain"><h4><?=$this->router->isArabic()?'اعلانات قد تهمك':'Recommended for you'?></h4><a href="#">View All</a></header><?php
        $q='select id,rand() as r from ad where hold=0 and canonical_id=0 and media=1 and country_id='.$this->router->countryId;
         if ($this->router->cityId>0) {
             $q.=' and city_id='.$this->router->cityId;
         }
         $q.=' order by r desc limit 5';
         $rs=$this->router->db->ql->search($q);
         if ($rs['total_found']>0) {
             echo '<div class=col-12>';
             foreach ($rs['matches'] as $row) {
                $this->adWidget($row['id']);
            }
            echo '</div>';
         }
        ?></div></div></div><?php
    }

    
    public function recentUploads() : void {
         ?><div class="row viewable"><div class=col-12><div class="card format2"><header class="plain"><h4><?=$this->router->isArabic()?'أحدث المنشورات':'Latest uploads'?></h4></header><?php
         $q='select id from ad where hold=0 and canonical_id=0 and media=1 and country_id='.$this->router->countryId;
         if ($this->router->cityId>0) {
             $q.=' and city_id='.$this->router->cityId;
         }
         $q.=' order by date_added desc limit 5';
         $rs=$this->router->db->ql->search($q);
         if ($rs['total_found']>0) {
             echo '<div class=col-12>';
             foreach ($rs['matches'] as $row) {
                $this->adWidget($row['id']);
            }
            echo '</div>';
         }
         ?></div></div></div><?php
        
    }
    
    
    private function sectionWidget(int $section_id, int $purpose_id) : void {
        $status=$this->router->db->as->getCacheData("section-dat-{$this->router->countryId}-{$this->router->cityId}-{$this->router->sections[$section_id]['root_id']}-{$this->router->language}", $root);        
        ?><div class=ad><?php
        ?><a href="<?=$this->router->getURL($this->router->countryId, $this->router->cityId, $this->router->sections[$section_id]['root_id'], $section_id, $purpose_id)?>"><div class=card><div class="card-image seclogo"><?php
        ?><img class=fill-<?=$this->router->sections[$section_id]['root_id']?> src="/web/css/1.0/assets/se/<?=$section_id?>.svg" /><?php
        ?><div style="position:absolute;height:22px;width:100%;bottom:0;background-color:#2d3e4f;opacity:0.9"><?php
        ?><div class="cbox cbl"><?=Ad::FormatSinceDate($root[$section_id]['purposes'][$purpose_id]['unixtime'], $this->lang)?></div><?php
        ?><div class="cbox cbr"><?=\number_format($root[$section_id]['purposes'][$purpose_id]['counter']).' '.$this->lang['ads']?></div><?php
        ?></div><?php
        ?></div><?php
        ?><div class=card-content><?php
        if ($status===\Aerospike::OK) {
            echo $this->sectionLabel($section_id, $purpose_id);
        }
        ?></div><?php        
        ?></a></div></div><?php
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
            $pic = '<div class=card-image>'; //<div class="cbox footer"></div>';                   
            $pix = $ad->picturePath();
            if ($this->router->isAcceptWebP) { $pix = preg_replace('/\.(?:png|jpg|jpeg)/', '.webp', $pix); }                
            $pic.= '<img src="'.$this->router->config->adImgURL.'/repos/m/'.$pix.'" />';                
            if ($pix_count>1) {
                //$pic.='<div class="cbox ctr">'.$pix_count.'&nbsp;<span class="icn icnsmall icn-camera"></span></div>';                  
            }
        }
        else {
            $pic= '<div class="card-image seclogo">'; //<div class="cbox footer"></div>';
            $pic.='<img src="'.$this->router->config->imgURL.'/200/'.$ad->sectionId().$this->router->_png.'" />';
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
            if ($this->user->params['user_country']) {   
                $this->formatNumbers=strtoupper($this->user->params['user_country']);
            } elseif($this->router->countryId) {
                $this->formatNumbers=strtoupper($this->router->countries[$this->router->countryId]['uri']);
            }
        }
        
        echo '<div class=ad ', $ad->htmlDataAttributes($this->formatNumbers), '>';
        echo '<div class="card', ($ad->isFeatured()?' premium':''),'" id=', $ad->id(), ' itemprop="itemListElement" ',  $itemScope, '>', "\n";
        if ($ad->isFeatured()) {
            ?><img class="tag" src="/web/css/1.0/assets/prtag-en.svg" /><?php
        }
        echo $pic, "\n";
            
        echo '<div class=card-content>', "\n";
        echo '<div class="adc block-with-text card-description ', $textClass, '" ';
            
        echo $itemDesc, '>', "\n";
        if ($ad->latitude()||$ad->longitude()) {
            echo '<a href="#" title="', $ad->location(), '"><i class="icn icnsmall icn-map-marker"></i></a>', "\n"; 
        }
        echo $ad->text(), '</div>', "\n";

        echo '</div>', "\n";
        //if ($this->user()->isSuperUser() && isset($this->searchResults['body']['scores'][$ad->id()])) {
        //        echo '<span style="direction:ltr;display:block;padding-left:20px">', $this->searchResults['body']['scores'][$ad->id()], '</span>';
        //    }
        echo '<div class=card-footer>', "\n";    
        echo $this->getAdSection($ad->data(), $hasSchema);
        //echo '<div title="', $this->lang['reportAbuse'], '" class=abuse onclick="event.stopPropagation();report(this);"><i class="icn icn-ban"></i></div>';
        echo $favLink, '</div>', "\n", '</div>', "\n";
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
                        $section = '<span itemprop="name">' . $section . '</span>&nbsp;' . $this->router->purposes[$ad[Classifieds::PURPOSE_ID]][$this->name];
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
                    $in = ' ';
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
                $res = '';
                $iter = 0;
                foreach ($ad[$locIDX] as $id=>$loc) {
                    if (isset($this->localities[$id])) {
                        if ($iter==0) {
                            $tempSection = $section . ' ' . $this->lang['in'] . ' ' . $this->localities[$id]['name'];
                        }
                        else {
                            $tempSection = $this->localities[$id]['name'];
                        }
                        $iter++;
                        if ($iter>2) {break;}
                        if ($hasLink) {
                            $idx = 2;
                            $uri = '/' . $this->router->countries[$ad[Classifieds::COUNTRY_ID]][\Core\Data\Schema::BIN_URI] . '/';
                            $uri.=$this->localities[$id]['uri'] . '/';
                            $uri.=$this->router->sections[$ad[Classifieds::SECTION_ID]][\Core\Data\Schema::BIN_URI] . '/';
                            $uri.=$this->router->purposes[$ad[Classifieds::PURPOSE_ID]][\Core\Data\Schema::BIN_URI] . '/';
                            $uri.=($this->router->isArabic()?'':$this->router->language . '/') . 'c-' . $id . '-' . $idx . '/';
                            $res.='<li><a href="' . $uri . '">' . $tempSection . '</a></li>';
                        }
                        else {
                            if ($res) { $res .= '  »|  '; }
                            $res.=$tempSection;
                        }
                    }
                }
                $section = $res;
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
                    $countryId = $ad[Classifieds::COUNTRY_ID];
                    $cityId = 0;
                    if (!empty($this->router->countries[$countryId]['cities']) && $ad[Classifieds::CITY_ID] && isset($this->router->countries[$countryId]['cities'][$ad[Classifieds::CITY_ID]])) {
                        $cityId = $ad[Classifieds::CITY_ID];
                        if ($this->router->cityId!=$cityId) {
                            $section = $section . ' ' . $this->lang['in'] . ' ' . $this->router->countries[$countryId]['cities'][$cityId]['name'];
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
                            $section = '<li><a href="' . $uri . '">' . $section . '</a></li>';
                        }
                        else {
                            $section = '<li><a href="' . $this->router->getURL($countryId, $cityId, $ad[Classifieds::ROOT_ID], $ad[Classifieds::SECTION_ID], $ad[Classifieds::PURPOSE_ID]) . '">' . $section . '</a></li>';
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
