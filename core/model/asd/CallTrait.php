<?php


namespace Core\Model\ASD;

const TS_CALL = 'calls';


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
    
    
    public function inboundCall(array $call, int $uid=0) : int
    {
        $success = false;
        $bins=[];
        if($uid)
        {
            $bins['uid']=$uid;
        }
        
        if (isset($call['conversation_uuid']) && $call['direction']=='inbound')
        {     
            $bins['direction']= \Core\Model\MobileValidation::CLI_TYPE;
            if (isset($call['from']))
            {
                $bins['from'] = intval($call['from']);
            }
            
            if (isset($call['to']))
            {
                $bins['to'] = intval($call['to']);
            }
            
            switch ($call['status']) 
            {
                case 'started':
                    $bins['uuid'] = $call['conversation_uuid'];
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
    
    
    public function getCall(string $conversation_uuid) 
    {
        if ($this->getRecord($this->asCallKey($conversation_uuid), $rec) == \Core\Model\NoSQL::OK)
        {
            return $rec;
        }
        return FALSE;
    }
}