<?php


namespace Core\Model\ASD;

const NS_VALIDATION = 'pv';
const TS_CALL = 'calls';
const TS_VALIDATION_REQUEST = 'requests';


trait CallTrait
{
    abstract public function getConnection() : \Aerospike;
    abstract public function genId(string $generator, &$sequence) : int;
    abstract public function getBins($pk, array $bins);
    abstract public function setBins($pk, array $bins);
    abstract public function exists($pk) : int;
    abstract public function getRecord(array $pk, &$record, array $bins=[]);
    
    private function asCallKey(string $conversation_uuid)
    {
        return $this->getConnection()->initKey(NS_USER, TS_CALL, $conversation_uuid);
    }    
    
    
    public static function is_valid($uuid) 
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                          '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
    
    /**
     * 
     * Generate v4 UUID
     * 
     * Version 4 UUIDs are pseudo-random.
     */
    public static function v4() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    
    /**
     * Generate v5 UUID
     * 
     * Version 5 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * @param	uuid	$namespace
     * @param	string	$name
     */
    public static function v5($namespace, $name) 
    {
        if(!self::is_valid($namespace)) return false;
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);
        // Binary Value
        $nstr = '';
        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) 
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }
        // Calculate hash value
        $hash = sha1($nstr . $name);
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 5
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }
        
    
    public function issueNewValidataionRequestKey(int $type, int $number, int $did_number=0, string $app='', int $uid, int $platform=0) : string
    {
        $request_id = static::v5(static::v4(), "{$type}-{$number}-{$app}-{$platform}");
        $bins = [
            'type'=>$type,
            'request_id'=>$request_id,
            'date_added'=>time(),
            'number'=>$number,
            'did'=>$did_number,
            'app'=>$app,
            'uid'=>$uid,
            'platform'=>$platform                
        ];
        
        switch ($type) 
        {
            case 0: // SMS
                $bins['timeout']=48*60*60;
                break;
            
            case 1: // CLI
                $bins['timeout']=3*60;
                break;
            
            case 2: // REVERSE CLI
                $bins['timeout']=5*60;
                break;
        }
        
        $pk = $this->asRequestValidationKey($type, $request_id);
        if ($this->setBins($pk, $bins))
        {            
            return $pk['key'];
        }
        return '';
    }
    
    
    public function getValidNumberCallRequests(int $type, int $number, int $did, &$result) : int
    {
        $result=[];
        
        $where = \Aerospike::predicateEquals('number', $number);
        $status = $this->getConnection()->query(NS_USER, TS_VALIDATION_REQUEST, $where, 
                    function ($record) use (&$result, $type, $did)
                    {
                        $epoch = $record['bins']['date_added']+$record['bins']['timeout'];
                        
                        if ($record['bins']['type']==$type && $record['bins']['did']==$did && $epoch>time())
                        {
                            $result[] = $record['bins'];                            
                        }                                               
                    });
        if (empty($result) && preg_match("/^1/", $number))
        {
            $number = intval(substr($number, 1));
            $where = \Aerospike::predicateEquals('number', $number);
            $status = $this->getConnection()->query(NS_USER, TS_VALIDATION_REQUEST, $where, 
                    function ($record) use (&$result, $type, $did)
                    {
                        $epoch = $record['bins']['date_added']+$record['bins']['timeout'];
                        
                        if ($record['bins']['type']==$type && $record['bins']['did']==$did && $epoch>time())
                        {
                            $result[] = $record['bins'];                            
                        }                                               
                    });
        }
        return $status;    
    }
    
    
    private function asRequestValidationKey(int $type, string $request_id)
    {        
        return $this->getConnection()->initKey(NS_USER, TS_VALIDATION_REQUEST, "{$type}-{$request_id}");        
    }   
    
    
    public function requestValidation(array $params)
    {
        if (!isset($params['type']))
        {
            $params['type'] = 0;
        }
               
        switch ($params['type']) 
        {
            case 0: // SMS
                break;
            
            case 1: // CLI
                break;
            
            case 2: // REVERSECLI
                break;

            default:
                break;
        }
    }
    
    
    public function outboundCall(array $call, int $uid=0) : int
    {
        $success = false;
        $bins=[];
        if($uid)
        {
            $bins['uid']=$uid;
        }
        
        if (isset($call['conversation_uuid']) && $call['direction']=='outbound')
        {            
            $bins['direction']= \Core\Model\MobileValidation::REVERSE_CLI_TYPE;
            switch ($call['status']) 
            {
                case 'started':
                    $bins['uuid'] = $call['uuid'];
                    $bins['date_added'] = time();
                    $bins[$call['status']] = 1;
                    break;
                
                case 'completed':
                    $bins[$call['status']] = 1;
                    $bins['duration'] = floatval($call['duration']);  
                    if ($call['start_time'])
                    {
                        $bins['start_time'] = $call['start_time'];
                    }
                    $bins['rate'] = floatval($call['rate']);
                    $bins['price'] = floatval($call['price']);
                    //$bins['fee'] = 2.0 * $bins['price'];
                    $bins['from'] = intval($call['from']);
                    $bins['to'] = intval($call['to']);
                    $bins['network'] = intval($call['network']);
                    break;
                
                case 'validated':
                    $bins[$call['status']] = 1;
                    $bins['valid_epoch'] = $call['validation_date'];
                    
                default:
                    $bins[$call['status']] = 1;
                    break;
            }
              
            $success = $this->setBins($this->asCallKey($call['conversation_uuid']), $bins);
        }       
        
        return $success ? 1 : 0;
    }
    
    
    public function inboundCall(array $call, array $req=[], int $uid=0) : int
    {
        $success = false;
        $bins=[];
        if($uid)
        {
            $bins['uid']=$uid;
        }
        
        if (isset($call['uuid']) || isset($call['conversation_uuid']) && $call['direction']=='inbound')
        {     
            
//            if (isset($call['from']))
//            {
//                $bins['from'] = intval($call['from']);
//            }
//            
//            if (isset($call['to']))
//            {
//                $bins['to'] = intval($call['to']);
//            }
            
            switch ($call['status']) 
            {                      
                case 'completed':
                    
                    $bins['uid'] = $req['uid'];
                    $bins['uuid'] = substr($call['uuid'],0,8).'-'.substr($call['uuid'],8,4).'-'.substr($call['uuid'],12,4).'-'.substr($call['uuid'],16,4).'-'.substr($call['uuid'],20);
                    $bins['date_added'] = $req['date_added'];
                    $bins[$call['status']] = 1;
                    $bins['duration'] = floatval($call['duration']);  
                    if ($call['start_time'])
                    {
                        $bins['start_time'] = $call['start_time'];
                    }
                    $bins['rate'] = floatval($call['rate']);
                    $bins['price'] = floatval($call['price']);
                    $bins['from'] = intval($call['from']);
                    $bins['to'] = intval($call['to']);
                    $bins['network'] = intval($call['network']);
                    
                    $reqPK = $this->asRequestValidationKey($req['type'], $req['request_id']);
                    $this->setBins($reqPK, ['completed'=>1]);
                    error_log($reqPK['key']);
                    $bins['direction']= \Core\Model\MobileValidation::CLI_TYPE;
                    $success = $this->setBins($this->asCallKey($reqPK['key']), $bins);
                    break;
                
                case 'validated':
                    $bins[$call['status']] = 1;
                    $bins['valid_epoch'] = $call['validation_date'];
                    error_log(json_encode($bins));
                    $success = $this->setBins($this->asCallKey($call['conversation_uuid']), $bins);
                    error_log("Result [{$success}] ".$this->asCallKey($call['conversation_uuid'])['key']);
                    break;
                
                default:
                    $bins[$call['status']] = 1;
                    break;
            }
              
            
        }
        return $success ? 1 : 0;
    }
    
    
    public function getCall(string $conversation_uuid) 
    {
        if ($this->getRecord($this->asCallKey($conversation_uuid), $rec) == \Core\Model\NoSQL::OK)
        {
            return $rec;
        }
        return FALSE;
    }
    
    
    public function tkey()
    {
        $key = $this->issueNewValidataionRequestKey(1, 9613287168, 'mourjan', 2);
        var_dump($key);
    }
}