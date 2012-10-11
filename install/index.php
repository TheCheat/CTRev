<?php

/**
 * Project:             CTRev
 * File:                index.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Инсталляция сайта
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

define('ININSTALL', true);
include "include/functions.php";
include "include/system/globals.php";
include "include/system/php_config.php";
include "include/classes/class.file.php";
include "include/classes/class.db.php";
include "include/classes/class.lang.php";
include "install/include/class.tpl.php";

define('ILOCK_FILE', 'install/lock');
if (file_exists(ILOCK_FILE))
    define('INSTALL_LOCKED', true);
else
    define('INSTALL_LOCKED', false);
db::o()->nt_error();
$pages = array(
    'license',
    'check',
    'database',
    'import',
    'admin',
    'config',
    'finish');

define("COPYRIGHT", "Powered by <a href=\"http://ctrev.cyber-tm.ru/\" title=\"Go to the official site of CTRev\">CTRev v." . ENGINE_VERSION . ' ' . ENGINE_STAGE . "</a> &copy; <a href=\"http://cyber-tm.ru\" title=\"Go to the official site of Cyber-Team\">Cyber-Team</a> 2008-" . date("Y"));
$l = $_GET['install_lang'];
if ($l) {
    setcookie('lang', $l);
    $_COOKIE['lang'] = $l;
}

if (!validfolder($_COOKIE['lang'], LANGUAGES_PATH)) {
    setcookie('lang', '');
    $lng = DEFAULT_LANG;
} else
    $lng = $_COOKIE['lang'];
lang::o()->change_folder($lng);

lang::o()->get('system');
lang::o()->get('main');
lang::o()->get('install/main');
if (!defined('INCONVERT')) {
    if (INSTALL_LOCKED) {
        $page = end($pages);
        $pages = array($page);
    }
    define('INSTALL_FILE', 'install');
} else {
    lang::o()->get('install/convert');
    $pages = array(
        "notice",
        "database",
        "convert",
        "finish");
    define('INSTALL_FILE', 'convert');
}
if (defined('INCONVERT')) {
    if (!INSTALL_LOCKED)
        die('You must install at first!');
    $converted = false;
    include ROOT . 'install/include/convert.php';
    $main = new convert($converted);
    if ($converted) {
        $page = end($pages);
        $pages = array($page);
    }
} else {
    include ROOT . 'install/include/main.php';
    $main = new main();
}


if (!$page) {
    if (!in_array($_GET['page'], $pages))
        $page = reset($pages);
    else
        $page = $_GET['page'];
}
define('INSTALL_PAGE', $page);
define('CONTENT_PATH', 'install/style/content/');
define('IMAGES_PATH', 'install/style/content/images/');
define('JS_PAGES', '["' . implode('", "', array_map('addslashes', $pages)) . '"]');


if ($_GET['page'])
    $main->init();
else {
    include 'include/classes/class.input.php';
    tpl::o()->assign('pages', $pages);
    tpl::o()->assign('clang', $lng);
    tpl::o()->assign('input', input::o());
    tpl::o()->display('contents');
}
?>