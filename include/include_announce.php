<?php

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

final class db_announce extends db {

    /**
     * Вывод ошибки последнего запроса к БД
     * @global bittorrent $bt
     * @param string $query
     * @return null
     */
    public function err($query = null) {
        global $bt;
        $bt->err(mysql_error());
    }

}
$db = new db_announce();
$file = new file();
$cache = new cache();
$bt = new fbenc();
$db->connect();
$db->no_reset();
$config = new config('announce');
?>