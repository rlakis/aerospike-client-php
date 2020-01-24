<?php
namespace Core\Data;
include_once dirname(__DIR__) . '/model/Singleton.php';

class Schema extends \Core\Model\Singleton {
    const TYPE_INTEGER      = 0;
    const TYPE_STRING       = 1;   
    const TYPE_BOOLEAN      = 2;   
    const TYPE_FLOAT        = 3;   
    const TYPE_LONG         = 4;   
    
    const FIELD_TYPE        = 0;
    const FIELD_LENGTH      = 1;
    const FIELD_DESC        = 2;
    
    protected array $tables;
    
    public static function instance() : Schema {
        return static::getInstance();
    }
    
    
    protected function __construct() {
        $this->tables = [
            'country' => [
                'ns'    => 'mourjan',
                'bins'  => [
                    'id'            => [static::FIELD_TYPE=>static::TYPE_INTEGER,   static::FIELD_LENGTH=>4,    static::FIELD_DESC=> 'country id'],
                    'name_ar'       => [static::FIELD_TYPE=>static::TYPE_STRING,    static::FIELD_LENGTH=>50,   static::FIELD_DESC=> 'arabic name'],
                    'name_en'       => [static::FIELD_TYPE=>static::TYPE_STRING,    static::FIELD_LENGTH=>50,   static::FIELD_DESC=> 'english name'],
                    'id_2'          => [static::FIELD_TYPE=>static::TYPE_STRING,    static::FIELD_LENGTH=>2,    static::FIELD_DESC=> 'iso 2 letters code'],
                    'blocked'       => [static::FIELD_TYPE=>static::TYPE_BOOLEAN,   static::FIELD_LENGTH=>1,    static::FIELD_DESC=> 'not active for publishing and listing'],
                    'latitude'      => [static::FIELD_TYPE=>static::TYPE_FLOAT,     static::FIELD_LENGTH=>8,    static::FIELD_DESC=> 'geo latitude'],
                    'longitude'     => [static::FIELD_TYPE=>static::TYPE_FLOAT,     static::FIELD_LENGTH=>8,    static::FIELD_DESC=> 'geo longitude'],
                    'code'          => [static::FIELD_TYPE=>static::TYPE_INTEGER,   static::FIELD_LENGTH=>4,    static::FIELD_DESC=> 'iso digits code'],
                    'currency_id'   => [static::FIELD_TYPE=>static::TYPE_STRING,    static::FIELD_LENGTH=>3,    static::FIELD_DESC=> 'iso currency 3 letters code'],
                    'locked'        => [static::FIELD_TYPE=>static::TYPE_BOOLEAN,   static::FIELD_LENGTH=>1,    static::FIELD_DESC=> 'allow changes or not'],
                    'unixtime'      => [static::FIELD_TYPE=>static::TYPE_LONG,      static::FIELD_LENGTH=>8,    static::FIELD_DESC=> 'last update unixtime']
                ],
                'pk'=>['id'],
            ],
            'city' => [
                'ns'    => 'mourjan',
                'bins'  => [
                ]
            ]
        ];       
    }
    
    
    public function metadata() : array {
        return $this->tables;
    }
}