<?php

defined('VG_ACCESS') or die('error int settings');

const MS_MODE = false; // browser Microsoft Explorer
const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATE = 'core/admin/view/';
const UPLOAD_DIR = 'userfiles/';
const DEFAULT_IMAGE_DIRECTORY = 'default_images';    
const END_SLASH = '/';
const COOKIE_VERSION = '1.0.0'; 
const CRYPT_KEY = 'F-JaNdRgUkXp2r5u$C&F)J@NcRfUjXn2v9y$B&E)H@McQfTj2s5v8y/B?E(H+MbQkXn2r5u8x/A?D(G+RfUjXnZr4u7x!A%D@McQfTjWnZq4t7w!';      
const COOKIE_TIME = 99999;    
const BLOCK_TIME = 3;      
const QTY = 8;
const QTY_LINKS = 3;       

const ADMIN_CSS_JS = [  

    'styles' => [
        'main' => [
            'resources/css/icons/styles.css',
            'resources/css/assets/bootstrap.min.css',
            'resources/css/assets/bootstrap_limitless.min.css',
            'resources/css/assets/layout.min.css',
            'resources/css/assets/components.min.css',
            'resources/css/assets/colors.min.css',
            'resources/css/root.css',
        ]
    ],

    'scripts' => [
        'main' => [
            'resources/js/jquery.min.js',
            'resources/js/bootstrap.bundle.min.js',
            'resources/js/blockui.min.js',
            'resources/js/prism.min.js',
            'resources/js/app.js',
            'resources/js/ajax_sitemap.js',
            'resources/js/alert.js',
            'resources/js/search.js',
            'resources/js/main.js',
        ],
        
        'search' => [
            'resources/js/select2.min.js',
            'resources/js/datatables.min.js',
            'resources/js/datatables_basic.js',
        ],

        'add' => [
            'resources/js/tagsinput.min.js',
            'resources/js/form_floating_labels.js',
            'resources/js/form_validation.js',
            'resources/js/typeahead.bundle.min.js',
            'resources/js/bootstrap_multiselect.js',
            'resources/js/tokenfield.min.js',
            'resources/js/formatter.min.js',
            'resources/js/touchspin.min.js',
            'resources/js/maxlength.min.js',
            'resources/js/uniform.min.js',
            'resources/js/form_checkboxes_radios.js',
            'resources/js/switchery.min.js',
            'resources/js/switch.min.js',
            'resources/js/switch.min.js',
            'resources/js/tinymce/tinymce.min.js',
            'resources/js/tinymce/tinymce_init.js',
            'resources/js/add.js',
        ],
        
    ],
];

const USER_CSS_JS = [      
    'styles' => [
        'resources/vendors/bootstrap/bootstrap.min.css',
        
    ],
    'scripts' => [
        'resources/vendors/jquery/jquery-3.2.1.min.js',
        
    ],
];

use core\base\exception\RouteException;

spl_autoload_register(function ($class_name) {

   $class_name = str_replace('\\', '/', $class_name);
    
    if (!@include_once $class_name . '.php') {
        throw new RouteException('Неверное имя файла для подключения - ' . $class_name);
    }
});
