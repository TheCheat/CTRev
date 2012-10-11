<?php

/**
 * Project:            	CTRev
 * File:                core.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		"Ядро" системы
 * @version           	1.00
 * @todo                recaptcha
 * @todo                массовая рассылка почты
 * @todo                управление странами
 * @todo                магазин бонусов
 * @todo                проверка на отключенные модули вне их самих
 * @todo                отключение нек. модулей в папке modules(напр. messages, 
 * statics, chat, news, search_module и class.search в т.ч.) и части модулей(напр. 
 * invites, bookmarks(в т.ч. и кнопки) в usercp)
 * @todo                апдейтер
 * @todo                магазин плагинов
 */
if (!defined('INSITE'))
    die('Remote access denied!');
 
// $preloaded = array("cache", "db", "users", "lang");

require_once (ROOT . 'include/system/allowed.php');
require_once (ROOT . 'include/system/globals.php');
require_once (ROOT . 'include/system/autoload.php');
require_once (ROOT . 'include/functions.php');
globals::s('start', timer()); // Start time
require_once (ROOT . 'include/smarty/Smarty.class.php');
require_once (ROOT . 'include/functions_smarty.php');
@set_error_handler("myerror_report"); // Присваиваем функцию myerror_report, вместо стандартной, помогает избежать раскрытия путей.
db::o()->connect();
init_baseurl();
lang::o()->change_folder(config::o()->v('default_lang'));
init_spaths();
tpl::o()->register_modifier('lang', array(lang::o(), 'v')); // языковая переменная
tpl::o()->register_modifier('islang', array(lang::o(), 'visset')); // языковая переменная
tpl::o()->register_modifier('config', array(config::o(), 'v')); // конфиг. переменная
tpl::o()->register_modifier('perm', array(users::o(), "perm")); // проверка на права
tpl::o()->register_modifier('user', array(users::o(), "v")); // поле юзера

/**
 * Ниже "вбивается" копирайт продукта, изменять или удалять его строго запрещено!
 * В противном случае, Ваш аккаунт блокирутся, 
 * Вы не получаете самые последние обновления и поддержка Вам прекращается!
 */
tpl::o()->assign("copyright", "Powered by <a href=\"http://ctrev.cyber-tm.ru/\" title=\"Go to the official site of CTRev\">CTRev v." . ENGINE_VERSION . ' ' . ENGINE_STAGE . "</a> &copy; <a href=\"http://cyber-tm.ru\" title=\"Go to the official site of Cyber-Team\">Cyber-Team</a> 2008-" . date("Y"));
tpl::o()->assign("designed_by", "Дизайн сайта разработал <u>А. Воробей</u>. Для всех иконок возможно указание источника.");

//tpl::o()->assign_by_ref('config', $CONFIG);
require_once (ROOT . 'include/init.php');
?>