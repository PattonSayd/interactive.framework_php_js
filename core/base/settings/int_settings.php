<?php

defined('VG_ACCESS') or die('error int settings');

const MS_MODE = false; // browser Microsoft Explorer

const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATE = 'core/admin/view/';
const UPLOAD_DIR = 'userfiles/';

const COOKIE_VERSION = '1.0.0'; 
const CRYPT_KEY = 'F-JaNdRgUkXp2r5u$C&F)J@NcRfUjXn2v9y$B&E)H@McQfTj2s5v8y/B?E(H+MbQkXn2r5u8x/A?D(G+RfUjXnZr4u7x!A%D@McQfTjWnZq4t7w!';      
const COOKIE_TIME = 60;    
const BLOCK_TIME = 3;      

const QTY = 8;             
const QTY_LINKS = 3;       

const ADMIN_CSS_JS = [     
    'styles' => ['css/main.css'],
    'scripts' => ['js/frameworkfunction.js', 'js/scripts.js'],
];
const USER_CSS_JS = [      
    'styles' => ['css/main.css'],
    'scripts' => [],
];

use core\base\exception\RouteException;

spl_autoload_register(function ($class_name) {

   $class_name = str_replace('\\', '/', $class_name);
    
    if (!@include_once $class_name . '.php') {
        throw new RouteException('Неверное имя файла для подключения -' . $class_name);
    }
});
