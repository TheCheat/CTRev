<?php

/**
 * Project:            	CTRev
 * @file                include/include_announce.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Загрузка основных файлов движка для аннонсера
 * @version           	1.00
 */

define('INSITE', true);
define('INANNOUNCE', true);

require_once 'system/php_config.php';
require_once ROOT . 'include/classes/class.db.php';
require_once ROOT . 'include/classes/class.file.php'; // для кеша
require_once ROOT . 'include/classes/class.cache.php'; // для кеша конфига
require_once ROOT . 'include/classes/class.config.php';
require_once ROOT . 'include/classes/class.fbenc.php';
require_once ROOT . 'include/functions.php';
@set_error_handler("myerror_report"); // Присваиваем функцию myerror_report, вместо стандартной, помогает избежать раскрытия путей.

/**
 * Вывод ошибки последнего запроса к БД
 * @param string $query строка запрос
 * @return null
 */
function db_errhandler($query = null) {
    $bt = new fbenc();
    $bt->err(mysql_error());
}

db::o()->errhandler('db_errhandler');
$bt = new fbenc();
db::o()->connect();
db::o()->no_reset();
config::o('announce');
?>