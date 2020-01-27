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
    
    
    public function prepare(array &$data) : bool {
        $this->lastError='';
        //var_dump($this->bins);
        foreach ($data as $k => $v) {
            if (!isset($this->bins[$k])) {
                $this->lastError=$k . ' field does not exists!';
                return false;
            }
            if (!$this->bins[$k]->prepare($data[$k])) {
                $this->lastError=$this->bins[$k]->lastError;
                return false;
            }
        }
        return true;
    }
}
