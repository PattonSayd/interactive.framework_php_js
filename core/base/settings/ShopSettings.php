<?php

namespace core\base\settings;

use core\base\settings\Settings;

class ShopSettings
{   
    static private $_instance;
    private $settings;
    
    private $templates = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    private $routes = [
        'admin' => [
            'alias' => 'todo',
        ],
    ]; 

    static public function instance()
    {
        if(self::$_instance instanceof self)
            return self::$_instance;

        self::$_instance = new self;
        
        self::$_instance->settings = Settings::instance();

        $properties = self::$_instance->settings->clueProperties(get_class());

        self::$_instance->setProperty($properties);

        return self::$_instance;

    }

    public static function get($property)
    {
        return self::instance()->$property;  
    }

    protected function setProperty($properties)
    {
        if ($properties) {
            foreach ($properties as $name => $property) {
                $this->$name = $property;
            }
        }
    }
    
    private function __construct(){}
    private function clone(){}
}