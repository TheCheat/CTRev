<?php

if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * Ниже будет реализовано в следующих версиях уже после релиза:
 * @todo recaptcha, массовая рассылка почты, управление странами,
 * магазин бонусов, проверка на отключенные модули вне их самих, 
 * отключение нек. модулей в папке modules(напр. messages, statics, chat, news, 
 * search_module и class.search в т.ч.) и части модулей(напр. invites, 
 * bookmarks(в т.ч. и кнопки) в usercp)
 * @todo апдейтер, магазин плагинов
 */
// $preloaded = array("cache", "db", "users", "lang");

require_once (ROOT . 'include/system/allowed.php');
require_once (ROOT . 'include/system/autoload.php');
require_once (ROOT . 'include/functions.php');
$start = timer(); // Start time
require_once (ROOT . 'include/smarty/Smarty.class.php');
require_once (ROOT . 'include/functions_smarty.php');
@set_error_handler("myerror_report"); // Присваиваем функцию myerror_report, вместо стандартной, помогает избежать раскрытия путей.

/**
 * Класс мультиязычной системы
 * @var lang
 */
$lang = new lang ();
/**
 * Класс Smarty Template Engine
 * @var tpl
 */
$tpl = new tpl ();

/**
 * Класс БД
 * @var db
 */
$db = new db();
$db->connect();
/**
 * Класс для работы с файлами
 * @var file 
 */
$file = new file();
/**
 * Класс кеша
 * @var cache
 */
$cache = new cache();
/**
 * Класс конфига
 * @var config
 */
$config = new config();
$cache->init();

init_baseurl();
$tpl->assign("BASEURL", $BASEURL);
$lang->change_folder($config->v('default_lang'));
init_spaths();
$tpl->register_modifier('lang', array($lang, 'v')); // языковая переменная
$tpl->register_modifier('config', array($config, 'v')); // конфиг. переменная


/**
 * Настройки плагинов/блоков
 * @var modsettings
 */
$modsettings = new modsettings();
/**
 * Плагиновая система движка
 * @var plugins
 */
$plugins = new plugins();
/**
 * Класс пользовательских функций
 * @var users
 */
$users = $plugins->get_class("users");
$tpl->register_modifier('perm', array($users, "perm")); // проверка на права
$tpl->register_modifier('user', array($users, "v")); // поле юзера

/**
 * Ниже "вбивается" копирайт продукта, изменять или удалять его строго запрещено!
 * В противном случае, Ваш аккаунт блокирутся, 
 * Вы не получаете самые последние обновления и поддержка Вам прекращается!
 */
$tpl->assign("copyright", "Powered by <a href=\"http://ctrev.cyber-tm.ru/\" title=\"Go to the official site of CTRev\">CTRev v." . ENGINE_VERSION . ' ' . ENGINE_STAGE . "</a> &copy; <a href=\"http://cyber-tm.ru\" title=\"Go to the official site of Cyber-Team\">Cyber-Team</a> 2008-" . date("Y"));
$tpl->assign("designed_by", "Дизайн сайта разработал <u>А. Воробей</u>. Для всех иконок возможно указание источника.");

// Автозагрузка классов в процессе работы
// Безусловно, можно было реализовать через паттерн Singleton,
// а бОльшую часть классов вообще инициализировать в ходе работы,
// но уже поздно, к тому же, это не худший вариант. По-крайней мере,
// так проще плагиновой системе.
// TODO: переделать на singleton?
$vars = allowed::o()->get("vars");
$c = count($vars);
for ($i = 0; $i < $c; $i++) {
    $_cvar = $_cobj = null;
    list($_cvar, $_cobj) = (array) $vars[$i];
    if (!$_cobj)
        $_cobj = $_cvar;
    ${$_cvar} = new system($_cvar, $_cobj);
}
//$tpl->assign_by_ref('config', $CONFIG);
require_once (ROOT . 'include/init.php');
?>