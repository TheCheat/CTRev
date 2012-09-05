<?php

define('INSITE', true);
if (ini_get('register_globals'))
    die("Отключите параметр register_globals в php.ini");
if (!defined('ADMIN_FILE') && file_exists("install") && !IN_DEVELOPMENT)
    die("Удалите инсталляцию движка(файл install.php и папку install из корня).<br/><b><u>Доступ ТОЛЬКО в АЦ!</u></b>");
if (!function_exists('mb_internal_encoding'))
    die("Пожалуйста, установите на ваш хост модуль PHP - mbstring для поддержки многоязычности в движке.");

require_once ('system/php_config.php');
require_once (ROOT . 'include/system/core.php');
?>