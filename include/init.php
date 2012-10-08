<?php

/**
 * @tutorial перед отправкой AJAX формы с input_form, необходимо первоначально вызвать make_tobbcode(); !
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

/* @var $blocks blocks */
$blocks = n("blocks");
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
tpl::o()->register_modifier('cut', array(
    display::o(),
    "cut_text"));
tpl::o()->register_modifier('he', array(
    display::o(),
    "html_encode"));
tpl::o()->register_modifier("ft", array(
    $bbcodes,
    "format_text"));
tpl::o()->register_modifier("ge", array(
    display::o(),
    "get_estimated_time"));
tpl::o()->register_modifier("ul", 'smarty_user_link');
tpl::o()->register_modifier('gc', array(
    display::o(),
    "group_color"));
tpl::o()->register_modifier('gcl', 'smarty_group_color_link');
tpl::o()->register_modifier("ua", array(
    display::o(),
    "display_user_avatar"));
tpl::o()->register_modifier("pf", "smarty_print_format");
tpl::o()->register_modifier('cs', array(
    display::o(),
    "convert_size"));
tpl::o()->register_modifier("zodiac_sign", array(
    display::o(),
    'get_zodiac_image'));
tpl::o()->register_modifier("decus", array(
    users::o(),
    'decode_settings'));
tpl::o()->register_modifier("filetype", array(
    file::o(),
    'get_filetype'));
tpl::o()->register_modifier("print_cats", array(
    'categories',
    'print_selected'));
tpl::o()->register_modifier("is_writable", array(
    file::o(),
    'is_writable'));
tpl::o()->register_modifier('rnl', 'replace_newline');

/**
 * Создание тега для atom:id(для торрентов)
 * @param int time - время постинга
 * @param string title - заголовок
 * @param int id - ID
 */
tpl::o()->register_function("atom_tag", "smarty_make_atom_tag");
/*
  tpl::o()->register_modifier("parse_array", array(
  display::o(),
  'parse_smarty_array')); */
/**
 * Вывод статистики запросов 
 */
tpl::o()->register_function("query_stat", "query_stat");
/**
 * Получение ключа формы 
 * @param int ajax - 2, если в AJAX, возвращается, как элемент объекта(напр. fk:'1',)
 * 1 - если в AJAX, возвращается, как часть строки запроса(напр. ?fk=1&)
 * иначе - если элемент формы(напр. <input type='hidden' value='1' name='fk'>)
 * по-умолчанию возвращается лишь значение ключа
 * @param string var - имя ключа
 */
tpl::o()->register_function("fk", "get_formkey");
/**
 * Отображение блоков
 * @param string pos - положение
 */
tpl::o()->register_function("display_blocks", array(
    $blocks,
    'display'));
/**
 * Вывод сообщения на экран
 * @param string lang_var - языковая переменная, в соответствии с которой будет выводится на экран сообщение,
 * либо цельный текст.
 * @param array vars - массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
 * @param string type - тип выводимого значения, в зависимости от него будут выбраны различные стили вывода сообщения(error|success|info)
 * @param bool die - если параметр установлен на true, то сразу после выведения сообщения, скрипт останавливается
 * @param string title - заголовок, выше сообщения
 * @param string align - расположение текста в сообщении(left|right|center)
 * @param bool no_image - если параметр установлен на true, то статусная картинка не выводится
 * @param bool only_box - если параметр установлен на true, то выводится только message.tpl
 */
tpl::o()->register_function('message', 'mess');
/**
 * BBCode форма для ввода текста 
 * @param string name - имя формы
 * @param string text - текст формы
 */
tpl::o()->register_function("input_form", array(
    $bbcodes,
    'input_form'));
/**
 * Генерация ЧПУ * 
 * @param string module - имя модуля
 * @param bool page - является ли указанный модуль ссылкой на документ?
 * @param bool no_end - нужно ли в конец добавлять .html/index.html?
 * @param bool nobaseurl - не добавлять в начала $BASEURL
 * @param bool slashes - экранирует результат для JavaScript, иначе & заменяется на &amp;
 * @param mixed параметры ссылки
 */
tpl::o()->register_function("gen_link", array(
    furl::o(),
    'construct'));
tpl::o()->register_modifier("genlink", array(
    furl::o(),
    'construct'));
/**
 * Форматирование времени UNIXTIME в человекопонятный формат
 * @param int time - время
 * @param string format - формат вывода(ymd или ymdhis, к примеру)
 */
tpl::o()->register_function("date", array(
    display::o(),
    'date'));
tpl::o()->register_modifier("date_time", array(
    display::o(),
    'date'));
/**
 * Поле выбора даты
 * @param string name - префикс полей
 * @param string type - тип формы(ymd, ymdhis, к примеру)
 * @param int time - данное время в формате UNIXTIME
 * @param bool fromnull - начинать с 0?
 */
tpl::o()->register_function("select_date", array(
    $input,
    "select_date"));

/**
 * Поле выбора часового пояса
 * @param string name - имя поля
 * @param float current - текущее значение
 */
tpl::o()->register_function("select_gmt", array(
    $input,
    'select_gmt'));
/**
 * Поле выбора страны
 * @param array country - выводимая страна(не для списка)(вкл. в себя name и image)
 * @param string name - имя поля
 * @param int current - текущее значение
 */
tpl::o()->register_function("select_countries", array(
    $input,
    'select_countries'));
/**
 * Получение кол-во используемой памяти 
 */
tpl::o()->register_function("get_memory_usage", "smarty_get_memory_usage");

/**
 * Поле выбора дирректорий
 * @param string name - имя поля
 * @param string folder - имя дирректории в корне
 * @param int current - текущее значение
 * @param bool onlydir - только дирректории?
 * @param bool empty - пустое значение?
 * @param string regexp - рег. выражение
 * @param int match - номер группы рег. выражения
 */
tpl::o()->register_function("select_folder", array(
    $input,
    'select_folder'));
/**
 * Поле выбора групп
 * @param string name - имя поля
 * @param int current - текущее значение
 * @param bool guest - в т.ч. и гость?
 * @param bool not_null - без пустого значение?
 * @param bool multiple - множественная выборка?
 */
tpl::o()->register_function("select_groups", array(
    $input,
    'select_groups'));
/**
 * Поле выбора интервала подписок
 * @param string name - имя поля
 * @param int current - текущее значение
 */
tpl::o()->register_function("select_mailer", array(
    $input,
    'select_mailer'));
/**
 * Поле выбора периода
 * @param string name - имя поля
 * @param int current - текущее значение
 */
tpl::o()->register_function("select_periods", array(
    $input,
    'select_periods'));
/**
 * Генератор пароля
 * @param string pname - имя поля пароля
 * @param string paname - имя поля повтора пароля 
 */
tpl::o()->register_function("passgen", 'smarty_passgen');
/**
 * Поле выбора категорий
 * @param string name - имя поля
 * @param int size - размер поля(если больше 1 - множественная выборка)
 * @param int current - текущее значение
 * @param bool not_null - без пустого значение?
 */
tpl::o()->register_function("select_categories", array(
    $input,
    'select_categories'));
/**
 * Простой селектор
 * @param string name - имя поля
 * @param array values - массив значений
 * @param bool keyed - ключи в качестве значения опций?
 * @param mixed current - текущее значение
 * @param int size - размер поля(если больше 1 - множественная выборка)
 * @param bool empty - пустое значение?
 */
tpl::o()->register_function("simple_selector", array(
    $input,
    'simple_selector'));
/**
 * Создание настроек для модуля
 */
tpl::o()->register_function("modsettings_create", array(
    modsettings::o(),
    'create'));
unset($bbcodes);
unset($input);

unset($comments);
unset($rating);
unset($polls);
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
if (XSS_PROTECT) {
    $POST = $_POST;
    $GET = $_GET;
    $_POST = xss_array_protect($_POST);
    $_GET = xss_array_protect($_GET);
    $_REQUEST = $_POST + $_GET;
}
?>