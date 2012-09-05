<?php

define('ROOT', dirname(dirname(dirname(__FILE__))) . "/");
require_once ROOT . 'include/system/config_global.php';
if (IN_DEVELOPMENT) {
    @error_reporting(E_ALL & ~E_NOTICE);
    @ini_set('error_reporting', E_ALL & ~E_NOTICE);
    @ini_set('display_errors', '1');
} else {
    @error_reporting(0);
    @ini_set('error_reporting', 0);
    @ini_set('display_errors', '0');
}

@ini_set('display_startup_errors', '0');
@ini_set('ignore_repeated_errors', '1');
@ini_set('sybct.allow_persistent', '0');
@ignore_user_abort(1);
@set_time_limit(0);
if (function_exists("set_magic_quotes_runtime"))
    @set_magic_quotes_runtime(false);
@session_start();
@ob_start();
if (function_exists("mb_internal_encoding") || !defined('ININSTALL'))
    mb_internal_encoding('UTF-8');

?>