<?php

/**
 * Project:            	CTRev
 * File:                php_config.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Насройка PHP
 * @version           	1.00
 */
define('ROOT', dirname(dirname(dirname(__FILE__))) . "/");
require_once ROOT . 'include/system/config_global.php';
if (IN_DEVELOPMENT) {
    @error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
    @ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT);
    @ini_set('display_errors', '1');
} else {
    @error_reporting(0);
    @ini_set('error_reporting', 0);
    @ini_set('display_errors', '0');
}

@ini_set('display_startup_errors', '0');
@ini_set('ignore_repeated_errors', '1');
@ini_set('sybct.allow_persistent', '0');
@ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);
@ignore_user_abort(1);
@set_time_limit(MAX_SCRIPT_EXECUTION_TIME);
if (function_exists("set_magic_quotes_runtime"))
    @set_magic_quotes_runtime(false);
@session_start();
@ob_start();
if (function_exists("mb_internal_encoding") || !defined('ININSTALL'))
    mb_internal_encoding('UTF-8');
?>