<?php

namespace core\base\settings;

use core\base\controller\Singleton;
use core\base\settings\Settings;

class ShopSettings
{   
    use Singleton{ instance as traitInstance; }
    
    private $settings;
    
    private $templates = [
        'input' => ['price', 'short', 'name'],
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
        
        self::traitInstance()->settings = Settings::instance();

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
}