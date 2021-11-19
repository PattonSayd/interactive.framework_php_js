<?php 
namespace core\base\controller;


trait Singleton{

    private static $_instance;
    
    public static function instance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        self::$_instance = new self;

        return self::$_instance;
    }

    private function __construct()
    { }
    private function __clone()
    { }
    
}

?>