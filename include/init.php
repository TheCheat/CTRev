<?php

/**
 * Project:            	CTRev
 * @file                include/init.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Инициализация функций и модификаторов для Smarty,
 * а так же защита от XSS
 * @version           	1.00
 * @attention            перед отправкой AJAX формы с input_form, необходимо первоначально вызвать make_tobbcode();
 */
if (!defined('INSITE'))
    die('Remote access denied!');

if (!defined('DATE_RSS'))
    define('DATE_RSS', 'D, d M Y H:i:s O');
if (!defined('DATE_ATOM'))
    define('DATE_ATOM', 'Y-m-d\TH:i:sP');

/**
 * Инициализация юзерей
 * @return null
 */
function users_init() {
    users::o()->init();
    if (!defined("DELAYED_SINIT"))
        users::o()->write_session();
    tpl::o()->assign('groups', users::o()->get_group());
    tpl::o()->assign('curlang', users::o()->get_lang());
    tpl::o()->assign('curtheme', users::o()->get_theme());
    tpl::o()->assign('curtheme_color', users::o()->get_theme(true));
    tpl::o()->assign('curuser', users::o()->v('username'));
    tpl::o()->assign('curgroup', users::o()->v('group'));
}

if (!defined('DELAYED_UINIT'))
    users_init();

tpl::o()->assign('URL_PATTERN', display::url_pattern);
tpl::o()->assign('slbox_mbinited', false);

// Кой-чаво для Smarty
// Для модификаторов нет описания, ибо проще посмотреть сами функции.
$bbcodes = bbcodes::o();
$input = input::o();
$display = display::o();

if (config::o()->v('torrents_on'))
    lang::o()->get('torrents', null, true, true);

/* @var $blocks blocks */
$blocks = n("blocks");
/* @var $message message */
$message = n("message");
tpl::o()->register_modifier('unserialize', 'unserialize');
tpl::o()->register_modifier('arr_current', 'current');
tpl::o()->register_modifier('arr_key', 'key');
tpl::o()->register_modifier('l2ip', 'long2ip');
tpl::o()->register_modifier('long', 'longval');
tpl::o()->register_modifier('is', 'is');
tpl::o()->register_modifier('sl', 'slashes_smarty');
tpl::o()->register_modifier('uamp', 'w3c_amp_replace');
tpl::o()->register_modifier('ue', 'urlencode');
tpl::o()->register_modifier('gval', 'smarty_group_value');
tpl::o()->register_modifier('null_text', array(
    $input,
    'set_null_text'));
tpl::o()->register_modifier('cut', array(
    $display,
    "cut_text"));
tpl::o()->register_modifier('he', array(
    $display,
    "html_encode"));
tpl::o()->register_modifier("ft", array(
    $bbcodes,
    "format_text"));
tpl::o()->register_modifier("ge", array(
    $display,
    "estimated_time"));
tpl::o()->register_modifier("ul", 'smarty_user_link');
tpl::o()->register_modifier('gc', array(
    $display,
    "group_color"));
tpl::o()->register_modifier('gcl', 'smarty_group_color_link');
tpl::o()->register_modifier("ua", array(
    $display,
    "useravatar"));
tpl::o()->register_modifier("pf", "smarty_print_format");
tpl::o()->register_modifier('cs', array(
    $display,
    "convert_size"));
tpl::o()->register_modifier("zodiac_sign", array(
    $display,
    'zodiac_image'));
tpl::o()->register_modifier("decus", array(
    users::o(),
    'decode_settings'));
tpl::o()->register_modifier("filetype", array(
    file::o(),
    'get_filetype'));
tpl::o()->register_modifier("is_writable", array(
    file::o(),
    'is_writable'));
tpl::o()->register_modifier('rnl', 'replace_newline');

/**
 * @note Создание тега для atom:id(для торрентов)(atom_tag)
 * params:
 * int time время постинга
 * string title заголовок
 * int id ID
 */
tpl::o()->register_function("atom_tag", "smarty_make_atom_tag");
/*
  tpl::o()->register_modifier("parse_array", array(
  $display,
  'parse_smarty_array')); */
/**
 * @note Вывод статистики запросов(query_stat)
 */
tpl::o()->register_function("query_stat", "query_stat");
/**
 * @note Получение ключа формы(fk)
 * params:
 * int ajax 2, если в AJAX, возвращается, как элемент объекта(напр. fk:'1',)
 * 1 если в AJAX, возвращается, как часть строки запроса(напр. ?fk=1&)
 * иначе если элемент формы(напр. <input type='hidden' value='1' name='fk'>)
 * по-умолчанию возвращается лишь значение ключа
 * string var имя ключа
 */
tpl::o()->register_function("fk", "get_formkey");
/**
 * @note Отображение блоков(display_blocks)
 * params:
 * string pos положение
 */
tpl::o()->register_function("display_blocks", array(
    $blocks,
    'display'));
/**
 * @note Вывод сообщения на экран(message)
 * params:
 * string lang_var языковая переменная, в соответствии с которой будет выводится на экран сообщение,
 * либо цельный текст.
 * array vars массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
 * string type тип выводимого значения, в зависимости от него будут выбраны различные стили вывода сообщения(error|success|info)
 * bool die если параметр установлен на true, то сразу после выведения сообщения, скрипт останавливается
 * string title заголовок, выше сообщения
 * string align расположение текста в сообщении(left|right|center)
 * bool no_image если параметр установлен на true, то статусная картинка не выводится
 * bool only_box если параметр установлен на true, то выводится только message.tpl
 */
tpl::o()->register_function('message', array($message, "info"));
/**
 * @note BBCode форма для ввода текста(input_form)
 * params:
 * string name имя формы
 * string text текст формы
 */
tpl::o()->register_function("input_form", array(
    $bbcodes,
    'input_form'));
/**
 * @note Генерация ЧПУ(gen_link)
 * params:
 * string module имя модуля
 * bool page является ли указанный модуль ссылкой на документ?
 * bool no_end нужно ли в конец добавлять .html/index.html?
 * bool nobaseurl не добавлять в начало $baseurl
 * bool slashes экранирует результат для JavaScript, иначе & заменяется на &amp;
 * string _filetype тип файла(вместо html)
 * string assign передать значение функции в переменную с именем assign?
 * mixed параметры ссылки
 */
tpl::o()->register_function("gen_link", array(
    furl::o(),
    'construct'));
/**
 * @note Форматирование времени UNIXTIME в человекопонятный формат(date)
 * params:
 * int time время
 * string format формат вывода(ymd или ymdhis, к примеру)
 */
tpl::o()->register_function("date", array(
    $display,
    'date'));
tpl::o()->register_modifier("date_time", array(
    $display,
    'date'));
/**
 * @note Поле выбора даты(select_date)
 * params:
 * string name префикс полей
 * string type тип формы(ymd, ymdhis, к примеру)
 * int time данное время в формате UNIXTIME
 * bool fromnull начинать с 0?
 */
tpl::o()->register_function("select_date", array(
    $input,
    "select_date"));

/**
 * @note Поле выбора часового пояса(select_gmt)
 * params:
 * string name имя поля
 * float current текущее значение
 */
tpl::o()->register_function("select_gmt", array(
    $input,
    'select_gmt'));
/**
 * @note Поле выбора страны(select_countries)
 * params:
 * array country выводимая страна(не для списка)(вкл. в себя name и image)
 * string name имя поля
 * int current текущее значение
 */
tpl::o()->register_function("select_countries", array(
    $input,
    'select_countries'));
/**
 * @note Получение кол-во используемой памяти(get_memory_usage)
 */
tpl::o()->register_function("get_memory_usage", "smarty_get_memory_usage");

/**
 * @note Поле выбора дирректорий(select_folder)
 * params:
 * string name имя поля
 * string folder имя дирректории в корне
 * int current текущее значение
 * bool onlydir только дирректории?
 * bool null пустое значение?
 * string regexp рег. выражение
 * int match номер группы рег. выражения
 */
tpl::o()->register_function("select_folder", array(
    $input,
    'select_folder'));
/**
 * @note Поле выбора групп(select_groups)
 * params:
 * string name имя поля
 * int current текущее значение
 * bool guest в т.ч. и гость?
 * bool null пустое значение?
 * bool multiple множественная выборка?
 */
tpl::o()->register_function("select_groups", array(
    $input,
    'select_groups'));
/**
 * @note Поле выбора интервала подписок(select_mailer)
 * params:
 * string name имя поля
 * int current текущее значение
 */
tpl::o()->register_function("select_mailer", array(
    $input,
    'select_mailer'));
/**
 * @note Поле выбора периода(select_periods)
 * params:
 * string name имя поля
 * int current текущее значение
 */
tpl::o()->register_function("select_periods", array(
    $input,
    'select_periods'));
/**
 * @note Поле выбора типа обратной связи(select_feedback)
 * params:
 * string name имя поля
 * int current текущее значение
 * bool null пустое значение?
 */
tpl::o()->register_function("select_feedback", array(
    $input,
    'select_feedback'));
/**
 * @note Генератор пароля(passgen)
 * params:
 * string pname имя поля пароля
 * string paname имя поля повтора пароля 
 */
tpl::o()->register_function("passgen", 'smarty_passgen');
/**
 * @note Поле выбора категорий(select_categories)
 * params:
 * string name имя поля
 * int size размер поля(если больше 1 множественная выборка)
 * int current текущее значение
 * bool null пустое значение?
 */
tpl::o()->register_function("select_categories", array(
    $input,
    'select_categories'));
/**
 * @note Простой селектор(simple_selector)
 * params:
 * string name имя поля
 * array values массив значений
 * bool keyed ключи в качестве значения опций?
 * mixed current текущее значение
 * int size размер поля(если больше 1 множественная выборка)
 * bool null пустое значение?
 * string lang_prefix языковой префикс для значений
 */
tpl::o()->register_function("simple_selector", array(
    $input,
    'simple_selector'));
/**
 * @note Создание настроек для модуля(modsettings_create)
 */
tpl::o()->register_function("modsettings_create", array(
    modsettings::o(),
    'create'));
unset($bbcodes);
unset($input);
unset($display);

unset($blocks);
unset($message);
/// Конец
/// Обнаруживаем IE
if (preg_match("/MSIE\\s*([0-9]+)/siu", $_SERVER ['HTTP_USER_AGENT'], $matches)) {
    lang::o()->get("ie_error");
    // Выгоняем IE ниже 8 версии
    if ($matches [1] < 8) {
        tpl::o()->display('ie_error.tpl');
        die();
    }
    tpl::o()->assign('MSIE', $matches [1]);
}

if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
    if (!empty($_GET)) {
        $_GET = strip_magic_quotes($_GET);
    }
    if (!empty($_POST)) {
        $_POST = strip_magic_quotes($_POST);
    }
    if (!empty($_COOKIE)) {
        $_COOKIE = strip_magic_quotes($_COOKIE);
    }
}
// INCLUDE BACK-END
globals::s('POST', $_POST);
$_POST = xss_array_protect($_POST);
$_GET = xss_array_protect($_GET);
$_REQUEST = $_POST + $_GET;
?>