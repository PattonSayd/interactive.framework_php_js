<?php

namespace core\base\settings;

class ShopSettings
{    
    use SettingsMethods;
    
    private $templates = [
        'input' => ['price', 'short', 'name'],
        'textarea' => ['goods_content']
    ];

    private $routes = [
        'admin' => [
            'alias' => 'todo',
        ],
    ]; 

}