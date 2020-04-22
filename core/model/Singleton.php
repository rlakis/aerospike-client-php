<?php
namespace Core\Model;

abstract class Singleton {
    private static $instances = [];
    
    protected function __construct() {}
    
    protected static function getInstance() : Singleton {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }
        return self::$instances[static::class];
    }
    
    
    private function __clone() {}
    private function __wakeup() {}
}