<?php

define('VG_ACCESS', true);

header('Content-Type: text/html; charset=utf-8');

session_start();

// error_reporting(0);  заглушить ощибки

require_once 'config.php';
require_once 'core/base/settings/int_settings.php';
require_once 'libraries/function.php';

use core\admin\model\AdminModel;
use core\base\controller\Route;
use core\base\exception\RouteException;
use core\base\exception\DBException;

$m = AdminModel::instance();
$m->select('comments', [
    'fields' => ['name', 'content'],
    'join' => [
        'comments_pages' => ['on' => ['id', 'com_id']],
        'pages' => [
            'fields' => ['name as page_name'],
            'on' => ['page_id', 'id']
        ],

        'pages pes' => [
            'fields' => ['contrex'],
            'on' => ['page_id', 'id']
            ]
        ],
    'join_structure' => true
]);

try {
    Route::routeDirection();
    
} catch (RouteException $e) {
    exit($e->getMessage());
    
} catch (DBException $e) { 
    exit($e->getMessage());
}

