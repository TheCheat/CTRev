<?php

/**
 * Project:            	CTRev
 * @file                include/system/core.php 
 * 
 * @todo                XBT Tracker. Магнет-ссылки. Возможность использования
 *                      магнетов без трекера(DHT/ретрекер) а-ля ThePirateBay.
 * 
 * @todo                Сделать в качестве примеров(именно в таком порядке):
 * @todo                Рип какой-нибудь темы.
 * @todo                Модуль: магазин бонусов
 * @todo                Плагины: анонимное скачивание торрентов, 
 *                      права/модераторы для категорий,
 *                      рейтинг с imdb/кинопоиска,
 *                      интегрировать api akismet,
 *                      интегрировать с MyBB
 * 
 * @todo                Описанное ниже возможно когда-то будет реализовано:
 * @todo                Массовая рассылка почты
 * @todo                Управление странами
 * @todo                Магазин плагинов
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		"Ядро" системы
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

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
tpl::o()->register_modifier('getlang', array(lang::o(), 'get')); // подключение языка прямо в шаблоне
tpl::o()->register_modifier('islang', array(lang::o(), 'visset')); // языковая переменная
tpl::o()->register_modifier('config', array(config::o(), 'v')); // конфиг. переменная
tpl::o()->register_modifier('mstate', array(config::o(), 'mstate')); // разрешён ли модуль
tpl::o()->register_modifier('perm', array(users::o(), "perm")); // проверка на права
tpl::o()->register_modifier('user', array(users::o(), "v")); // поле юзера

/**
 * Не смей удалять копирайт ниже, а то дядя Ваня тебя покарает своим большим чёрным банхаммером.
 */
tpl::o()->assign("copyright", "Powered by <a href=\"http://ctrev.cyber-tm.ru/\" title=\"Go to the official site of CTRev\">CTRev v." . ENGINE_VERSION . ' ' . ENGINE_STAGE . "</a> &copy; <a href=\"http://cyber-tm.ru\" title=\"Go to the official site of Cyber-Team\">Cyber-Team</a> 2008-2012");
tpl::o()->assign("designed_by", "Дизайн сайта разработал <u>А. Воробей</u>. All Rights Reserved.");

//tpl::o()->assign_by_ref('config', $CONFIG);
require_once (ROOT . 'include/init.php');
?>