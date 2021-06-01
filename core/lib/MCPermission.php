<?php

namespace Core\Lib;

include_once dirname(__DIR__) . '/model/Singleton.php';



class MCPermission extends \Core\Model\Singleton {
    private MCUser $user;

    const IDENTIFIERS=0;
    const SUPER_USER=1;
    const SEE_FOR_HELP=2;
    const CAN_BLOCK=3;
    const FILTER_USER=4;
    const SIMILAR_ADS=5;
    
    private array $privileges=[];
    private bool $authorized;
    
    private array $admins=[
        'names'=>[
            'r'=>       [MCPermission::IDENTIFIERS=>[2, 69905], MCPermission::SUPER_USER=>1], 
            'b'=>       [MCPermission::IDENTIFIERS=>[1], MCPermission::SUPER_USER=>1], 
            'm'=>       [MCPermission::IDENTIFIERS=>[2100, 123391], MCPermission::SUPER_USER=>1], 
            'samir'=>   [MCPermission::IDENTIFIERS=>[477618],  MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>1, MCPermission::SIMILAR_ADS=>1, MCPermission::SEE_FOR_HELP=>0], 
            'editor 1'=>[MCPermission::IDENTIFIERS=>[38813],   MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0], 
            'editor 2'=>[MCPermission::IDENTIFIERS=>[44835],   MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>1, MCPermission::FILTER_USER=>1, MCPermission::SIMILAR_ADS=>1, MCPermission::SEE_FOR_HELP=>1], 
            'editor 3'=>[MCPermission::IDENTIFIERS=>[53456],   MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0], 
            'editor 4'=>[MCPermission::IDENTIFIERS=>[166772],  MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0], 
            'editor 5'=>[MCPermission::IDENTIFIERS=>[516064],  MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0],  
            'editor 6'=>[MCPermission::IDENTIFIERS=>[897143],  MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0],  
            'editor 7'=>[MCPermission::IDENTIFIERS=>[897182],  MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>1, MCPermission::FILTER_USER=>1, MCPermission::SIMILAR_ADS=>1, MCPermission::SEE_FOR_HELP=>1], 
            'editor 8'=>[MCPermission::IDENTIFIERS=>[1028732], MCPermission::SUPER_USER=>0, MCPermission::CAN_BLOCK=>0, MCPermission::FILTER_USER=>0, MCPermission::SIMILAR_ADS=>0, MCPermission::SEE_FOR_HELP=>0], 
        ],
        'index'=>[]
    ];
    
    
    protected function __construct() {
        foreach ($this->admins['names'] as $name => $value) {
            foreach ($value[MCPermission::IDENTIFIERS] as $id) {
                $this->admins['index'][$id]=$name;
            }
        }
    }
    
    
    public static function instance() : self {
        return static::getInstance();
    }
    
    
    public function setUser(MCUser $user) : self {
        $this->user=$user;
        $this->privileges=$this->admins['names'][$this->admins['index'][$this->user->id]];
        $this->authorized=!($this->user->isBlocked() || $this->user->isSuspended());
        return $this;
    }
    
    
 
    
    public function isSuperAdmin() : bool {
        if ($this->authorized===false) return false;
        return $this->privileges[static::SUPER_USER]??false;
    }
    
    
    public function canBlockUser() : bool {
        if ($this->authorized===false) return false;
        if ($this->isSuperAdmin($this->user)) return true;
        return $this->privileges[static::CAN_BLOCK]??false;
    }


    public function canFilterPendingUserAds() : bool {
        if ($this->authorized===false) return false;
        if ($this->isSuperAdmin($this->user)) return true;
        return $this->privileges[static::FILTER_USER]??false;
    }
    
    
    public function canSeeAdsSentToAdmin() : bool {
        if ($this->authorized===false) return false;
        if ($this->isSuperAdmin($this->user)) return true;
        return $this->privileges[static::SEE_FOR_HELP]??false;
    }
    
    
    public function canSeeSimilarAds() : bool {
        if ($this->authorized===false) return false;
        if ($this->isSuperAdmin($this->user)) return true;
        return $this->privileges[static::SIMILAR_ADS]??false;
    }

    
    
}