<?php

namespace Core\Model\ASD;

const TS_MOBILE = 'mobiles';


trait MobileTrait
{
    abstract public function getConnection();
    abstract public function genId(string $generator, &$sequence);
    abstract public function getBins($pk, array $bins);
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
    
    
    public function mobileGetOrderedBy(array $opts)
    {
        $result = [];
        $i = 0;
        $options = [
            \Aerospike::OPT_SCAN_PRIORITY => \Aerospike::SCAN_PRIORITY_MEDIUM,
            \Aerospike::OPT_READ_TIMEOUT => 5000,
            \Aerospike::OPT_SCAN_PERCENTAGE => 100,
            \Aerospike::OPT_SCAN_NOBINS => TRUE,
            
            ];
        $sort_by = ['id' => [], 'uid' => []];
        $status = $this->getConnection()->scan(NS_USER, TS_MOBILE, function ($record) use (&$result, &$i, &$sort_by) {
            $record['bins']['date_requested'] = \DateTime::createFromFormat("U", $record['bins']['date_requested'])->format('Y-m-d H:i:s');
            if (isset($record['bins']['date_activated']))
            {
                $record['bins']['date_activated'] = \DateTime::createFromFormat("U", $record['bins']['date_activated'])->format('Y-m-d H:i:s');
            } 
            else
            {
                $record['bins']['date_activated'] = '';
            }
            switch ($record['bins']['flag']) {
                case 0:
                    $record['bins']['flag'] = 'Android';
                    break;
                case 1:
                    $record['bins']['flag'] = 'Website';
                    break;
                case 2:
                    $record['bins']['flag'] = 'IOS';
                    break;

                default:
                    break;
            }
            $result[$i] = $record['bins'];
            $sort_by['id'][$i] = $record['bins']['id'];
            $sort_by['uid'][$i] = $record['bins']['uid'];
            $i++;
        }, [], $options);

        // check the status of the last operation
        if ($status == \Aerospike::ERR_SCAN_ABORTED) {
            echo "I think a sample of $i records is enough\n";
        } else if ($status !== \Aerospike::OK) {
            echo "An error occured while scanning[{$this->getConnection()->errorno()}] {$this->getConnection()->error()}\n";
        }
        array_multisort($sort_by['id'], SORT_ASC, $sort_by['uid'], SORT_DESC, SORT_NUMERIC, $result);
        $dups = [];

        $mask = "|%6.6s |%8.8s |%15.15s | %4.4s | %-7.7s | %9.9s | %-9.9s | %-19.19s | %-19.19s |\n";
        printf($mask, '------', '--------', '-----------------', '----', '-------', '---------', '---------', '-------------------', '-------------------');
        printf($mask, 'id', 'uid', 'number', 'code', 'flag', 'sms_count', 'delivered', 'date_requested', 'date_activated');
        printf($mask, '------', '--------', '-----------------', '----', '-------', '---------', '---------', '-------------------', '-------------------');
        foreach ($result as $record) 
        {
            printf($mask, $record['id'], $record['uid'], $record['number'], $record['code'], $record['flag'], $record['sms_count'], $record['delivered']?'Yes':'No', $record['date_requested'], $record['date_activated']);
            if (isset($dups[ $record['id'] ]))
            {
                $dups[ $record['id'] ]++;
            } else {
                $dups[$record['id']]=1;
            }
        }
        printf($mask, '------', '--------', '-----------------', '----', '-------', '---------', '---------', '-------------------', '-------------------');
        echo count($result), " mobile records", "\n";
       
        foreach ($dups as $id=>$value) 
        {
            if ($value>1)
            {
                echo $id, "\t", $value, "\n";
            }
                
            
        }
//        foreach ($result as $key => $value) 
//        {
//            for ($i=0;$i<$n;$i++)
//            {
//                $opts[$cols[$i]]['buffer'][$key] = $value[$cols[$i]]; 
//            }
//        }
        
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
        if (!$this->setBins($this->getConnection()->initKey(NS_USER, TS_MOBILE, $bins[USER_UID].'-'.$bins[USER_MOBILE_NUMBER]), $bins))
        {
            error_log("could not insert mobile record <". json_encode($bins).">");
            return 0;
        }
        return $mobile_id;
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
    
    
    public function assignNewActicationCode(int $mobile_id, int $uid, int $number) : int
    {
        $keys = $this->getDigest(USER_MOBILE_NUMBER, $number, [USER_UID=>$uid, SET_RECORD_ID=>$mobile_id]);
        if ($keys)
        {
            $code = mt_rand(1000, 9999);
            if ($this->setBins($keys[0], [USER_MOBILE_ACTIVATION_CODE=>$code]))
            {
                return $code;
            }
        }
        return 0;
    }


    public function mobileIncrSMS(int $uid, int $number) : bool
    {
        $pk = $this->getConnection()->initKey(NS_USER, TS_MOBILE, $uid.'-'.$number);
        $metadata=[];
        $status = $this->getConnection()->exists($pk, $metadata);
        if ($status==\Aerospike::OK) {
            $status = $this->getConnection()->increment($pk, USER_MOBILE_SENT_SMS_COUNT, 1, [\Aerospike::OPT_POLICY_KEY=>\Aerospike::POLICY_EXISTS_UPDATE]);
            return ($status==\Aerospike::OK);
        } 
        else 
        {
            error_log("record does not exists {$uid}-{$number}");
        }
        return FALSE;
        //$e = new Exception();
        //ob_start();
        //debug_print_backtrace();
        //$trace = ob_get_contents();
        //ob_end_clean();
        //error_log(__FUNCTION__.PHP_EOL. $trace);
    }
    
    

}
