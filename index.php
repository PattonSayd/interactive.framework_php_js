<?php

define('VG_ACCESS', true);

header('Content-Type: text/html; charset=utf-8');

session_start();

// error_reporting(0);  заглушить ощибки

require_once 'config.php';
require_once 'core/base/settings/int_settings.php';
require_once 'libraries/function.php';

use core\base\controller\Route;
use core\base\exception\RouteException;
use core\base\exception\DBException;
use core\base\model\Crypt;

$crypt = Crypt::instance();
$crypt->encrypt('Yellow123');

try {
    Route::routeDirection();
    
} catch (RouteException $e) {
    exit($e->getMessage());
    
} catch (DBException $e) {
    exit($e->getMessage());
}

