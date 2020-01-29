<?php

namespace Core\Data;

class TableMetadata {
    protected string $ns;
    protected string $name;
    protected array $pk;
    protected array $uk;
    protected array $ix;
    protected array $bins;
    protected string $sequence;
    
    public string $lastError;
    
    private function __construct() {
        $this->pk=[];
        $this->uk=[];
        $this->ix=[];
        $this->bins=[];
        $this->sequence='';
    }
    
    public static function create(string $ns, string $name) : TableMetadata {
        $handle=new TableMetadata();
        $handle->ns=\trim($ns);
        $handle->name=\trim($name);
        $handle->sequence=$handle->name.'_id';
        return $handle;
    }
           
    
    public function setPrimaryKey(array $pk) : TableMetadata {
        $this->pk=$pk;
        return $this;        
    }
    
    
    public function setUniqueKey(array $uk) : TableMetadata {
        $this->pk=$uk;
        return $this;        
    }
    
    
    public function addField(BinField $bin) : TableMetadata {
        $this->bins[$bin->name()]=$bin;
        return $this;  
    }
    
    
    
    
    public function namespace() : string {
        return $this->ns;
    }
    
    
    public function name() : string {
        return $this->name;
    }
    
    
    public function sequenceKey() : string {
        return $this->sequence;
    }
    
    
    public function prepare(array &$data) : bool {
        $this->lastError='';
        //var_dump($this->bins);
        foreach ($data as $k => $v) {
            if (!isset($this->bins[$k])) {
                $this->lastError=$k . ' field does not exists!';
                return false;
            }
            //$value=$data[$k];
            if (!$this->bins[$k]->prepare( $v )) {
                $this->lastError=$this->bins[$k]->lastError;
                return false;
            }
            $data[$k]=$v;
        }
        return true;
    }
    
    
    public function beforeInsert(array &$data) : bool {
        $this->lastError='';
        foreach ($this->bins as $field) {           
            if ($field->isRequired() && !isset($data[$field->name()])) {
                if ($field->isString()) {
                    $data[$field->name()]=$field->defaultStrValue();
                }
                else if ($field->isNumeric()) {
                    $data[$field->name()]=$field->isDouble()?$field->defaultDoubleValue():$field->defaultIntValue();                    
                }
                else {
                    $this->lastError=$field->name() . ' data type is not defined!';
                    return false;
                }
            }            
        }
        return true;
    }
    
    /*
    public function intPrimaryKey() : int {
        
    }
    
    
    public function strPrimaryKey() : string {
        
    }
     * 
     */
}
