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

    'styles' => [
        'resources/css/icons/styles.css',
        'resources/css/assets/bootstrap.min.css',
        'resources/css/assets/bootstrap_limitless.min.css',
        'resources/css/assets/layout.min.css',
        'resources/css/assets/components.min.css',
        'resources/css/assets/colors.min.css',
    ],

    'scripts' => [
        'resources/js/jquery.min.js',
        'resources/js/bootstrap.bundle.min.js',
        'resources/js/blockui.min.js',
        'resources/js/prism.min.js',
        'resources/js/app.js',
        'resources/js/datatables.min.js',
        'resources/js/buttons.min.js',
        'resources/js/select2.min.js',
        'resources/js/datatables_extension_colvis.js',
        'resources/js/tagsinput.min.js',
        'resources/js/form_floating_labels.js',
        'resources/js/validate.min.js',
        'resources/js/form_validation.js',
    ],
];

const USER_CSS_JS = [      
    'styles' => [],
    'scripts' => [],
];

use core\base\exception\RouteException;

spl_autoload_register(function ($class_name) {

   $class_name = str_replace('\\', '/', $class_name);
    
    if (!@include_once $class_name . '.php') {
        throw new RouteException('Неверное имя файла для подключения - ' . $class_name);
    }
});
