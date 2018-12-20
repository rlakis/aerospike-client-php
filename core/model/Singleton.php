<?php

abstract class Singleton {
    private static $instances = [];
    
    protected function __construct() {}
    
    public static function getInstance() : Singleton {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
            error_log(static::class . " created");
        }
        elseif (static::class!='Config') {
            error_log(static::class . " used");
        }
        return self::$instances[static::class];
    }
    
    
    private function __clone() {}
    private function __wakeup() {}
}