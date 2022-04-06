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
const COOKIE_TIME = 60;    
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
            'resources/css/assets/root.css',
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
            'resources/js/scripts.js',
            'resources/js/root.js',
        ],
        
        'search' => [
            'resources/js/select2.min.js',
            'resources/js/datatables.min.js',
            'resources/js/datatables_basic.js',
        ],

        'add' => [
            'resources/js/tagsinput.min.js',
            'resources/js/form_floating_labels.js',
            'resources/js/validate.min.js',
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
            'resources/js/tinymce/tinymce_init.js' 
        ],
        
    ],
];

const USER_CSS_JS = [      
    'styles' => [
        'resources/vendors/bootstrap/bootstrap.min.css',
        'resources/vendors/fontawesome/css/all.min.css',
        'resources/vendors/themify-icons/themify-icons.css',
        'resources/vendors/nice-select/nice-select.css',
        'resources/vendors/owl-carousel/owl.theme.default.min.css',
        'resources/vendors/owl-carousel/owl.carousel.min.css',
        'resources/css/style.css',
    ],
    'scripts' => [
        'resources/vendors/jquery/jquery-3.2.1.min.js',
        'resources/vendors/bootstrap/bootstrap.bundle.min.js',
        'resources/vendors/skrollr.min.js',
        'resources/vendors/owl-carousel/owl.carousel.min.js',
        'resources/vendors/nice-select/jquery.nice-select.min.js',
        'resources/vendors/jquery.ajaxchimp.min.js',
        'resources/vendors/mail-script.js',
        'resources/js/main.js',
    ],
];

use core\base\exception\RouteException;

spl_autoload_register(function ($class_name) {

   $class_name = str_replace('\\', '/', $class_name);
    
    if (!@include_once $class_name . '.php') {
        throw new RouteException('Неверное имя файла для подключения - ' . $class_name);
    }
});
