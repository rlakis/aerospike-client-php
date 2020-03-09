<?php
namespace Core\Model;

class AdList extends \SplDoublyLinkedList {    
    private int $state;
    private int $uid;
    private int $alterUID;
    
    private int $rootId;
    private int $purposeId;
    private int $lang;
    
    private int $page;
    private int $limit;
    private int $countAll;

    private array $profiles;

    public function __construct() {
        $this->page = 0;
        $this->limit = 25;
        $this->state = 0;
        $this->countAll = 0;
        $this->profiles = [];
        $this->alterUID = \intval(\filter_input(\INPUT_GET, 'u',    \FILTER_SANITIZE_NUMBER_INT, ['$option'=>['default'=>0]]));
        $this->rootId   = \intval(\filter_input(\INPUT_GET, 'fro',  \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]));
        $this->purposeId= \intval(\filter_input(\INPUT_GET, 'fpu',  \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]));
        $this->lang     = \intval(\filter_input(\INPUT_GET, 'fhl',  \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]));
        if ($this->alterUID===0) {
            $this->alterUID = \intval(\filter_input(\INPUT_GET, 'fuid', \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]));
        }
        
        $this->uid = ($this->alterUID>0) ? $this->alterUID : Router::instance()->user->id();
    }
    
    
    public function setState(int $state) : AdList {
        $this->state = $state;
        return $this;
    }
    
        
    public function setUID(int $uid) : AdList {
        $this->uid = $uid;
        return $this;
    }


    public function cacheProfile(\Core\Lib\MCUser $profile) {
        $this->profiles[$profile->id]=$profile;
    }
    
    
    public function getCachedProfile(int $uid) : ?\Core\Lib\MCUser {
        if (isset($this->profiles[$uid])) {
            return $this->profiles[$uid];
        }
        $profile = new \Core\Lib\MCUser($uid);
        $this->cacheProfile($profile);
        return $profile;
    }
    
    
    public function fetchFromAdUser() : void {
        $this->data = [];
        $this->page = \intval( \filter_input(\INPUT_GET, 'o', \FILTER_SANITIZE_NUMBER_INT, ['options'=>['default'=>0]]) );
       
        $db = Router::instance()->db;
        $q = 'SELECT AD_USER.ID, AD_USER.CONTENT, AD_USER.PURPOSE_ID, AD_USER.SECTION_ID, ';
        $q.= 'AD_USER.RTL, AD_USER.STATE, AD_USER.COUNTRY_ID, AD_USER.CITY_ID, ';
        $q.= 'AD_USER.LATITUDE, AD_USER.LONGITUDE, AD_USER.WEB_USER_ID, ';
        $q.= 'DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', AD_USER.DATE_ADDED) DATE_ADDED, ';
        $q.= 'DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', AD_USER.LAST_UPDATE) LAST_UPDATE, ';
        
        $q.= '(select list(\'"\'||MEDIA.FILENAME||\'":\'||\'[\'||MEDIA.WIDTH||\',\'||MEDIA.HEIGHT||\']\') PICTURES ';
        $q.= 'from AD_MEDIA left join MEDIA on MEDIA.ID=AD_MEDIA.MEDIA_ID where AD_MEDIA.AD_ID=AD_USER.ID), ';
        
        $q.= 'WEB_USERS.FULL_NAME, WEB_USERS.DISPLAY_NAME, WEB_USERS.PROFILE_URL, WEB_USERS.LVL, WEB_USERS.USER_RANK ';
        
        $f = 'FROM AD_USER ';
        $f.= 'LEFT JOIN WEB_USERS on WEB_USERS.ID=AD_USER.WEB_USER_ID ';

        $w = 'where ';
        
        if ($this->state>6) {
            $q.= ', ';
            $q.= 'IIF(T_AD_FEATURED.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', T_AD_FEATURED.ended_date)) featured_date_ended, ';
            $q.= 'IIF(T_AD_BO.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', T_AD_BO.end_date)) bo_date_ended, WEB_USERS.provider ';
            
            $f.= 'LEFT JOIN T_AD_BO on T_AD_BO.AD_ID=AD_USER.ID and T_AD_BO.BLOCKED=0 ';
            $f.= 'LEFT JOIN T_AD_FEATURED on T_AD_FEATURED.AD_ID=AD_USER.ID and current_timestamp between T_AD_FEATURED.added_date and T_AD_FEATURED.ended_date ';

            $w.= "AD_USER.web_user_id={$this->uid} and AD_USER.state={$this->state} ";
            $o = 'ORDER BY bo_date_ended desc, AD_USER.LAST_UPDATE desc';

        }
        elseif ($this->state>0) {
            $user = Router::instance()->user;
            $adLevel= $user->isSuperUser() ? $adLevel=100000000 : 0;
            
            $q.= ', ';
            $q.= 'AD_USER.ADMIN_ID, AD_USER.DOC_ID, AD_OBJECT.super_admin, ';
            $q.= 'iif((AD_USER.section_id=190 or AD_USER.section_id=1179 or AD_USER.section_id=540), 1, 0) ppn, ';
            $q.= 'iif(AD_USER.state=4, 1, 0) primo, ';
            $q.= 'IIF(T_AD_FEATURED.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', T_AD_FEATURED.ended_date)) featured_date_ended, ';
            $q.= 'IIF(T_AD_BO.id is null, 0, DATEDIFF(SECOND, timestamp \'01-01-1970 00:00:00\', T_AD_BO.end_date)) bo_date_ended, WEB_USERS.provider ';
                        
            $f.= 'LEFT JOIN AD_OBJECT on AD_OBJECT.ID=AD_USER.ID ';
            $f.= 'LEFT JOIN T_AD_BO on T_AD_BO.AD_ID=AD_USER.ID and T_AD_BO.BLOCKED=0 ';
            $f.= 'LEFT JOIN T_AD_FEATURED on T_AD_FEATURED.AD_ID=AD_USER.ID and current_timestamp between T_AD_FEATURED.added_date and T_AD_FEATURED.ended_date ';
            if ($this->rootId>0) {
                $f.= 'left join section on AD_USER.section_id=section.id ';
            }
            
                                            
            if (preg_match("/https.*\.mourjan\.com\/admin\/?\?p=\d+/", $_SERVER['HTTP_REFERER'] ?? 'DIRECT_ACCESS')) {
                $w.=" (AD_USER.state between 1 and 4) and AD_USER.web_user_id={$this->uid} ";
            }
            else {
                if ($this->alterUID>0) {
                    $w.= '((AD_USER.state in (1,2,4)) and AD_USER.web_user_id='. $this->alterUID . ') ';
                }
                else {
                    $w.= '((AD_USER.state in (1,2,4)) or (AD_USER.state=3 and AD_USER.web_user_id='. $this->uid.')) ';
                }
            }
            $w.= ' and (AD_OBJECT.super_admin is null or AD_OBJECT.super_admin<='.$adLevel.') ';
            
            if ($this->purposeId>0) {
                $w.= "and AD_USER.purpose_id={$this->purposeId} ";
            }                            
            if ($this->lang===1) { 
                $w.= 'and (AD_USER.rtl in (1,2)) ';
            }                            
            else if ($this->lang===2) {
                $w.= 'and (AD_USER.rtl in (0,2)) ';
            }
                            
            if ($this->rootId>0) {
                $w.= "and section.root_id={$this->rootId} ";
            }                            
            $o = 'order by primo desc, AD_USER.state asc, bo_date_ended desc, AD_OBJECT.super_admin desc, ppn, AD_USER.LAST_UPDATE desc';
            
            //error_log($q.$f.$w);
        }
        else {
            // draft ads
            $w.= "AD_USER.WEB_USER_ID={$this->uid} and AD_USER.STATE={$this->state} ";
            $o = 'ORDER BY AD_USER.LAST_UPDATE desc';
        }

        $l = ' rows ' . (($this->page===0)?1:($this->page*$this->limit)+1) . ' to ' . (($this->page*$this->limit)+$this->limit);
        
        $fixes=[];
        $st = $db->prepareQuery($q.$f.$w.$o.$l, [\PDO::ATTR_CURSOR=>\PDO::CURSOR_FWDONLY]);
        if ($st->execute()) {
            while (($rs=$st->fetch(\PDO::FETCH_ASSOC))!==false) {
                $ad = new Ad();
                if ($rs['CONTENT'] && $rs['CONTENT'][0]==='"') {
                    $rs['CONTENT']=\trim(\stripcslashes($rs['CONTENT']), '"');
                    $fixes[$rs['ID']]=$rs['CONTENT'];                   
                }
                $ad->setParent($this)->parseDbRow($rs);
                $this->push($ad);
            }
        }
        $st->closeCursor();
        
        
        $ct = $db->prepareQuery('select count(AD_USER.ID) '.$f.$w);
        if ($ct->execute()) {
            $row=$ct->fetch(\PDO::FETCH_NUM);
            $this->countAll=$row[0];
        }
        $ct->closeCursor();
        
        foreach ($fixes as $id => $content) {
            $db->queryResultArray('update ad_user set content=? where id=?', [$content, $id]);
        }
        $db->commit();
    }
    
    
    public function dbCount() : int {
        return $this->countAll;
    }
    
    
    public function page() : int {
        return $this->page;
    }
    
    
    public function limit() : int {
        return $this->limit;
    }

    
    public function rootId() : int {
        return $this->rootId;
    }

    
    public function purposeId() : int {
        return $this->purposeId;
    }

    
    public function languageFilter() : int {
        return $this->lang;
    }

    
    public function userId() : int {
        return $this->alterUID;
    }

    
    
    public function current() : Ad {
        return parent::current();
        //return current($this->data);
    }

    /*
    public function key(): \scalar {
        return \key($this->data);
    }

    
    public function next(): void {
        \next($this->data);
    }

    
    public function rewind(): void {
        reset($this->data);
    }

    
    public function valid(): bool {
        return key($this->data) !== null;
    }
    */
}
