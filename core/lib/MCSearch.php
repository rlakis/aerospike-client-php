<?php
namespace Core\Lib;

class MCSearch extends \Manticoresearch\Search {
    const ID                = 'id';
    const UID               = 'user_id';
    const HOLD              = 'hold';
    const COUNTRY           = 'country';
    const CITY              = 'city';
    const CANONICAL         = 'canonical_id';

    const ROOT              = 'root_id';
    const SECTION           = 'section_id';
    const PURPOSE           = 'purpose_id';
    const LOCALITY          = 'locality_id';
    const TAG               = 'section_tag_id';
    const RTL               = 'rtl';
    const MEDIA             = 'media';
    const STARRED           = 'starred';
    const PUBLISHER_TYPE    = 'publisher_type';
    const DATE_ADDED        = 'date_added';
    const FEATURED_TTL      = 'featured_date_ended';

    
    private array $conditions = ['must'=>[], 'not'=>[]];
    
    public function __construct(\Manticoresearch\Client $client) {
        parent::__construct($client); 
        $this->setIndex('ad')->filter(static::HOLD, 'equals', 0)->filter(static::CANONICAL, 'equals', 0);        
    }
    
            
    public function idFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::ID]=['equals', $value];
            /*
            if ($exclude===true) {
                return $this->notFilter(static::ID, 'equals', $value);
            }
            else {
                return $this->filter(static::ID, 'equals', $value);                
            }
            */
        }
        return $this;
    }

    
    public function regionFilter(int $country_id, int $city_id=0) : self {
        if ($city_id>0) {
            $this->conditions['must'][static::CITY]=['equals', $city_id];
            //return $this->filter(static::CITY, 'equals', $city_id);
        }        
        elseif ($country_id>0) {
            $this->conditions['must'][static::COUNTRY]=['equals', $country_id];
            //return $this->filter(static::COUNTRY, 'equals', $country_id);
        }        
        return $this;
    }
    
    
     public function rootFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            //$this->intFilter(static::ROOT, $value, $exclude);
            $this->conditions[$exclude===false?'must':'not'][static::ROOT]=['equals', $value];
        }
        return $this;
    }
    
    
    public function sectionFilter(int $value, bool $exclude=false) : self {
        if ($value) {            
            //$this->intFilter('ANY('.static::SECTION.')', $value, $exclude);
            $this->conditions[$exclude===false?'must':'not'][static::SECTION]=['equals', $value];
            if (!$exclude) {
                unset($this->conditions['must'][static::ROOT]);
            }
        }
        return $this;
    }

    
    public function purposeFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::PURPOSE]=['equals', $value];
            //$this->intFilter(static::PURPOSE, $value, $exclude);
        }
        return $this;
    }
    
    
    public function uidFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::UID]=['equals', $value];
            unset($this->conditions['must'][static::COUNTRY]);
            unset($this->conditions['must'][static::CITY]);
        }
        return $this;
    }

    
    public function starred(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::STARRED]=['equals', $value];
        }
        return $this;
    }
    
    
    public function localityFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::LOCALITY]=['equals', $value];
        }
        return $this;
    }
    
    
    public function tagFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {            
            $this->conditions[$exclude===false?'must':'not'][static::TAG]=['equals', $value];
            if ($exclude===false) {
                unset($this->conditions['must'][static::SECTION]);
            }
        }
        return $this;
    }
    
    
    public function rtlFilter(array $value, bool $exclude=false) : self {
        if (!empty($value)) {
            $this->conditions[$exclude===false?'must':'not'][static::RTL]=['in', $value];
        }
        return $this;
    }
    
    
    public function publisherTypeFilter(int $value, bool $exclude=false) : self {
        if ($value>0) {
            $this->conditions[$exclude===false?'must':'not'][static::PUBLISHER_TYPE]=['equals', $value];
        }
        return $this;
    }

    
    public function mediaFilter(int $value=1, bool $exclude=false) : self {
        $this->conditions[$exclude===false?'must':'not'][static::MEDIA]=['equals', $value];
        return $this;
    }

    
    public function featuredFilter(bool $exclusive=true) : self {
        $this->filter(static::FEATURED_TTL,$exclusive?'gte':'lt', time());
        return $this;
    }
    
    
    public function get() : \Manticoresearch\ResultSet {
        foreach ($this->conditions['must'] as $attr => $condition) {
            $this->filter($attr, $condition[0], $condition[1]);
        }
        foreach ($this->conditions['not'] as $attr => $condition) {
            $this->notFilter($attr, $condition[0], $condition[1]);
        }
        return parent::get();
    }
    
    
    public function result() : array {
        $result=['error'=>'', 'warning'=>'', 'total'=>0, 'total_found'=>0, 'time'=>0, 'matches'=>[], 'object'=>null];
        try {
            //$b=$this->compile();
            //\error_log(PHP_EOL.\json_encode($b).PHP_EOL);
            $result['object']=$this->get();
        }
        catch (Exception $re) {
            \error_log("sgsgsdfgd");
        }
        
        if ($result['object']->getResponse()->hasError()) {
            $result['error']=$result['object']->getResponse()->getError();
        }
        $result['total']=$result['object']->count();
        $result['total_found']=$result['object']->getTotal();
        $result['time']=$result['object']->getTime();
        while ($result['object']->valid()) {
            $hits=$result['object']->current();
            $result['matches'][]= \intval($hits->getId());                    
            $result['object']->next();
        }
        $result['object']->rewind();
        
        return $result;
    }
    
    
}