<?php

namespace Core\Model\ASD;

const TS_MOBILE = 'mobiles';


trait MobileTrait
{
    abstract public function getConnection();
    abstract public function setBins($pk, array $bins);
    
    public function intMobileKey($uid, $number)
    {
        return $this->getConnection()->initKey(NS_USER, TS_MOBILE, $uid.'-'.$number);
    }
    
    
    private function getDigest(string $index_field_name, $value, array $filter) : array
    {
        $matches=[];
        $bins = array_keys($filter);
        
        if (!isset($filter[SET_RECORD_ID])) $bins[] = SET_RECORD_ID;
        if (!isset($filter[USER_UID])) $bins[] = USER_UID;
        
        $where = \Aerospike::predicateEquals($index_field_name, $value);
        
        $this->getConnection()->query(NS_USER, TS_MOBILE, $where,  
                function ($record) use (&$matches, $filter) 
                {
                    //error_log(var_export($record, TRUE));
                    
                    $matched=TRUE;
                    if ($filter)
                    {
                        foreach ($filter as $key => $val) 
                        {
                            if ($record['bins'][$key]!=$val)
                            {
                                $matched = FALSE;
                                break;
                            }
                        }
                    }
                    
                    if ($matched)
                    {
                        $matches[] = $record['key'];
                    }
                    
                }, $bins);
        return $matches;
    }
    
    
    public function mobileSetDeliveredSMS($id, $number) : bool
    {
        $keys = $this->getDigest(USER_MOBILE_NUMBER, $number, []);
        if ($keys)
        {
            return $this->setBins($keys[0], [USER_MOBILE_CODE_DELIVERED=>1]);
        }
        return FALSE;
    }
    
    
    public function mobileInsert(array $bins) : int
    {
        if (!isset($bins[USER_MOBILE_NUMBER]) || !isset($bins[USER_UID]))
        {
            error_log("Could not insert mobile: " . json_encode($bins));
            return 0;
        }

        if ($bins[USER_MOBILE_NUMBER]<=0 || $bins[USER_UID]<=0)
        {
            error_log("Could not insert mobile: " . json_encode($bins));
            return 0;
        }
        
        
        $bins[USER_MOBILE_DATE_REQUESTED]=time();
        $bins[USER_MOBILE_CODE_DELIVERED]=0;
        $bins[USER_MOBILE_SENT_SMS_COUNT]=0;
        if (!isset($bins[USER_MOBILE_FLAG]))
        {
            $bins[USER_MOBILE_FLAG]=0;
        }
        
        if (is_string($bins[USER_MOBILE_NUMBER]))
        {
            $bins[USER_MOBILE_NUMBER]= intval($bins[USER_MOBILE_NUMBER]);
        }
        
        $mobile_id=0;
        $this->genId('mobile_id', $mobile_id);
        $bins[SET_RECORD_ID]=$mobile_id;
        return ($this->setBins($this->getConnection()->initKey(NS_USER, TS_MOBILE, $bins[USER_UID].'-'.$bins[USER_MOBILE_NUMBER]), $bins)) ? $mobile_id : 0;
    }
    
    
    public function mobileActivation(int $uid, int $number, int $code) : bool
    {
        $keys = $this->getDigest(USER_MOBILE_NUMBER, $number, [USER_UID=>$uid, USER_MOBILE_ACTIVATION_CODE=>$code]);
        if ($keys)
        {
            return $this->setBins($keys[0], [USER_MOBILE_DATE_ACTIVATED=>time()]);
        }
        return FALSE;
    }
    
    
    public function assignNewActicationCode() : int
    {
        $code = mt_rand(1000, 9999);
        return $code;
    }
}
