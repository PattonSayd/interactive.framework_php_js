<?php

namespace core\base\controller;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $_instance;
        
    static public function instance()
    {
        if(self::$_instance instanceof self)
            return self::$_instance;

        return self::$_instance = new self;
        
    }

    private function __construct(){
        $a = Settings::instance();
        $b = ShopSettings::instance();
        exit();
    }
    private function clone(){}
}

