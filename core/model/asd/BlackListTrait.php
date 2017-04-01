<?php

namespace Core\Model\ASD;

const TS_BLACKLIST          = 'blacklist';
const BLACK_LIST_REASON     = 'reason';


trait BlackListTrait
{
    abstract public function getConnection();
    abstract public function genId(string $generator, &$sequence);
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;
    
    private function asKey(string $contact)
    {
        return $this->getConnection()->initKey(NS_USER, TS_BLACKLIST, $contact);
    }    
    
    
    public function blacklistInsert(string $contact, string $reason, int $uid=0) : int
    {
        $bins = [];
        if (is_numeric($contact))
        {
            $bins[USER_MOBILE_NUMBER] = intval($contact);
        } 
        else
        {
            $bins[USER_PROVIDER_EMAIL] = $contact;
        }
        
        if ($uid)
        {
            $bins[USER_UID] = $uid;
        }
        $bins[BLACK_LIST_REASON] = $reason;
        return $this->setBins($this->asKey($contact), $bins) ? 1 : 0;
    }
    
    
    public function isBlacklistedContacts(array $contacts) : bool
    {
        foreach ($contacts as $contact) 
        {
            if ($this->exists($this->asKey($contact)))
            {
                return TRUE;
            }
        }
        return FALSE;        
    }
    
    
    public function getBlackListedReason($contact) 
    {
        if (($rec = $this->getBins($this->asKey($contact), [BLACK_LIST_REASON]))!==FALSE)
        {
            return $rec[BLACK_LIST_REASON];
        }
        return FALSE;
    }
}