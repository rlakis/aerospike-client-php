<?php

interface TableInterface {    
    public function fieldByName(string $fieldName): object;
}