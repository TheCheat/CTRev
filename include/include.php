<?php

/**
 * Project:            	CTRev
 * File:                include.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Загрузка основных файлов движка
 * @version           	1.00
 */

define('INSITE', true);
if (ini_get('register_globals'))
    die("Отключите параметр register_globals в php.ini");
if (!function_exists('mb_internal_encoding'))
    die("Пожалуйста, установите на ваш хост модуль PHP - mbstring для поддержки многоязычности в движке.");

require_once ('system/php_config.php');
require_once (ROOT . 'include/system/core.php');
?>