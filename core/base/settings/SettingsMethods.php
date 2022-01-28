<?php
namespace core\base\settings;

use core\base\controller\Singleton;

trait SettingsMethods
{
    use Singleton {
        instance as SingletonInstance;
    }
    
    private $settings;

    public static function get($property)
    {
        return self::instance()->$property;
    }

    public static function instance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        
        self::SingletonInstance()->settings = Settings::instance();

        $settings_properties = self::$_instance->settings->clueProperties(get_class());

        self::$_instance->setProperty($settings_properties);

        return self::$_instance;
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
