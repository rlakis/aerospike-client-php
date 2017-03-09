<?php

namespace Core\Model\ASD;

trait UserTrait
{
    abstract public function getConnection();

    public function createUser(array $bins)
    {
        $key = $this->getConnection()->initKey("users", "generators", 'gen_id');
        $operations = [
            ["op" => \Aerospike::OPERATOR_INCR, "bin" => "profile_id", "val" => 1],
            ["op" => \Aerospike::OPERATOR_READ, "bin" => "profile_id"],
        ];
        
        if ($this->getConnection()->operate($key, $operations, $record)== \Aerospike::OK)
        {
            $uid = $record['profile_id'];
            $now = time();
            $record = [
                'id' => $uid, 
                'provider_id' => '',
                'email' => '',
                'provider' => '',
                'full_name' => '',
                'display_name' => '',
                'profile_url' => '',
                'date_added' => $now,
                'last_visited' => $now,
                'level' => 0,
                'name' => '',
                'user_email' => '',
                'password' => '',
                'rank' => 0,
                'prior_visited' => 0,
                'pblshr_status' => 0,
                'last_renewed' => 0,
                'dependants' => [],
                'options' => [],
                'mobile' => [],
                'devices' => []       
            ];
            foreach ($bins as $k => $v) 
            {
                $record[$k] = $v;                
            }
            error_log(json_encode($record));
        }
    }
    
    
    public function updateUser($info, string $provider)
    {
        error_log(__CLASS__ );
        $provider = strtolower($provider);
        $identifier = $info->identifier;
        $email = is_null($info->emailVerified) ? (is_null($info->email ? '' : $info->email)) : $info->emailVerified;
        if (strpos($email, '@')===false) 
        {
            $email='';
        }
        $fullName = trim(($info->firstName ? $info->firstName : '').' '.($info->lastName ? $info->lastName : ''));
        $dispName = (!is_null($info->displayName) ? $info->displayName : '');
        $infoStr = (!is_null($info->profileURL) ? $info->profileURL : '');
        $uid = 0;
        $where = \Aerospike::predicateEquals('provider_id', $identifier);
        $this->getConnection()->query("users", "profiles", $where,  
                function ($record) use (&$uid, $provider) 
                {
                    if ($record['bins']['provider']==$provider)
                    {
                        $uid = $record['bins']['id'];                        
                    }
                }, ['id', 'provider']);
                
        $bins = ['email'=>$email, 'full_name'=>$fullName, 'display_name'=>$dispName, 'profile_url'=>$infoStr];        
        if ($uid)
        {
            $pk = $this->initKey($uid);            
            $this->setBins($pk, $bins);
            $this->setVisitUnixtime($uid);            
        }
        else
        {
            $bins['provider_id'] = $identifier;
            $bins['provider'] = $provider;
            $this->createUser($bins);
        }
                
    }
    
    
    private function initKey(int $uid) 
    {
        return $this->getConnection()->initKey("users", "profiles", $uid);
    }
    
    
    private function getBins($pk, array $bins) : array
    {
        $record=[];
        if ($this->getConnection()->get($pk, $record, $bins) != \Aerospike::OK) 
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return [];
        }
        return $record['bins'];        
    }
    
    
    private function setBins($pk, array $bins) : bool
    {
        $status = $this->getConnection()->put($pk, $bins);
        if ($status != \Aerospike::OK) 
        {
            error_log( "Error [{$this->getConnection()->errorno()}] {$this->getConnection()->error()}" );
            return FALSE;
        }
        return TRUE;
    }
    
    
    public function setVisitUnixtime(int $uid)
    {
        $pk = $this->initKey($uid);
        $record = $this->getBins($pk, ["last_visited"]);
        if ($record)
        {        
            $this->setBins($pk, ['last_visited'=>time(), 'prior_visited'=>$record['last_visited']]);
        }
    }
    
    
    public function updateLinkedMobile(int $uid, int $number) : bool
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, ['mobile']);
        if ($record && isset($record['mobile']['number']) && $record['mobile']['number']==$number)
        {
            $record['mobile']['date_activated'] = time();
            return $this->setBins($pk, $record);            
        }
        return FALSE;
    }
    
    
    public function getVerifiedMobile(int $uid)
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, ['mobile']);
        if ($record && isset($record['mobile']['number']) && isset($record['mobile']['date_activated']))
        {
            $year_in_seconds = 31556926;
            if ($record['mobile']['date_activated']+$year_in_seconds>time()) 
            {
                return $record['mobile']['number'];
            }
        }
        return FALSE;        
    }
    
    
    public function getOptions(int $uid) : array
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, ['options']);
        return isset($record['options']) ? $record['options'] : $record;
    }

    
    public function getRank(int $uid) : int
    {
        $pk = $this->initKey($uid);
        $record=$this->getBins($pk, ['rank']);        
        return isset($record['rank']) ? $record['rank'] : 0;
    }
}