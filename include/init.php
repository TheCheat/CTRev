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
 * @global users $users 
 * @global tpl $tpl
 * @return null
 */
function users_init() {
    global $users, $tpl;
    $users->init();
    if (!defined("DELAYED_SINIT"))
        $users->write_session();
    $tpl->assign('groups', $users->get_group());
    $tpl->assign('curlang', $users->get_lang());
    $tpl->assign('curtheme', $users->get_theme());
    $tpl->assign('curuser', $users->v('username'));
    $tpl->assign('curgroup', $users->v('group'));
}

/**
 * Класс преобразований значений, а так же вывода ч\л на экран
 * @var display
 */
$display = $plugins->get_class("display");

if (!defined('DELAYED_UINIT'))
    users_init();

$tpl->assign('URL_PATTERN', display::url_pattern);
$tpl->assign('slbox_mbinited', false);

// Кой-чаво для Smarty
// Для модификаторов нет описания, ибо проще посмотреть сами функции.
$tpl->register_modifier('unserialize', 'unserialize');
$tpl->register_modifier('arr_current', 'current');
$tpl->register_modifier('arr_key', 'key');
$tpl->register_modifier('l2ip', 'long2ip');
$tpl->register_modifier('long', 'longval');
$tpl->register_modifier('is', 'is');
$tpl->register_modifier('sl', 'slashes_smarty');
$tpl->register_modifier('uamp', 'w3c_amp_replace');
$tpl->register_modifier('ue', 'urlencode');
$tpl->register_modifier('gval', 'smarty_group_value');
$tpl->register_modifier('cut', array(
    $display,
    "cut_text"));
$tpl->register_modifier('he', array(
    $display,
    "html_encode"));
$tpl->register_modifier("ft", array(
    $bbcodes,
    "format_text"));
$tpl->register_modifier("ge", array(
    $display,
    "get_estimated_time"));
$tpl->register_modifier("ul", 'smarty_user_link');
$tpl->register_modifier('gc', array(
    $display,
    "group_color"));
$tpl->register_modifier('gcl', 'smarty_group_color_link');
$tpl->register_modifier("ua", array(
    $display,
    "display_user_avatar"));
$tpl->register_modifier("pf", "smarty_print_format");
$tpl->register_modifier('cs', array(
    $display,
    "convert_size"));
$tpl->register_modifier("zodiac_sign", array(
    $display,
    'get_zodiac_image'));
$tpl->register_modifier("decus", array(
    $users,
    'decode_settings'));
$tpl->register_modifier("filetype", array(
    $file,
    'get_filetype'));
$tpl->register_modifier("print_cats", array(
    $cats,
    'print_selected'));
$tpl->register_modifier("is_writable", array(
    $file,
    'is_writable'));
$tpl->register_modifier('rnl', 'replace_newline');

/**
 * Создание тега для atom:id(для торрентов)
 * @param int time - время постинга
 * @param string title - заголовок
 * @param int id - ID
 */
$tpl->register_function("atom_tag", "smarty_make_atom_tag");
/*
  $tpl->register_modifier("parse_array", array(
  $display,
  'parse_smarty_array')); */
/**
 * Вывод статистики запросов 
 */
$tpl->register_function("query_stat", "query_stat");
/**
 * Получение ключа формы 
 * @param int ajax - 2, если в AJAX, возвращается, как элемент объекта(напр. fk:'1',)
 * 1 - если в AJAX, возвращается, как часть строки запроса(напр. ?fk=1&)
 * иначе - если элемент формы(напр. <input type='hidden' value='1' name='fk'>)
 * по-умолчанию возвращается лишь значение ключа
 * @param string var - имя ключа
 */
$tpl->register_function("fk", "get_formkey");
/**
 * Отображение блоков
 * @param string pos - положение
 */
$tpl->register_function("display_blocks", array(
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
$tpl->register_function('message', 'mess');
/**
 * BBCode форма для ввода текста 
 * @param string name - имя формы
 * @param string text - текст формы
 */
$tpl->register_function("input_form", array(
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
$tpl->register_function("gen_link", array(
    $furl,
    'construct'));
$tpl->register_modifier("genlink", array(
    $furl,
    'construct'));
/**
 * Форматирование времени UNIXTIME в человекопонятный формат
 * @param int time - время
 * @param string format - формат вывода(ymd или ymdhis, к примеру)
 */
$tpl->register_function("date", array(
    $display,
    'date'));
$tpl->register_modifier("date_time", array(
    $display,
    'date'));
/**
 * Поле выбора даты
 * @param string name - префикс полей
 * @param string type - тип формы(ymd, ymdhis, к примеру)
 * @param int time - данное время в формате UNIXTIME
 * @param bool fromnull - начинать с 0?
 */
$tpl->register_function("select_date", array(
    $input,
    "select_date"));

/**
 * Поле выбора часового пояса
 * @param string name - имя поля
 * @param float current - текущее значение
 */
$tpl->register_function("select_gmt", array(
    $input,
    'select_gmt'));
/**
 * Поле выбора страны
 * @param array country - выводимая страна(не для списка)(вкл. в себя name и image)
 * @param string name - имя поля
 * @param int current - текущее значение
 */
$tpl->register_function("select_countries", array(
    $input,
    'select_countries'));
/**
 * Отображение комментариев
 * @param int resid - ID ресурса
 * @param string type - тип ресурса
 * @param string name - имя формы
 * @param bool no_form - без формы добавления
 */
$tpl->register_function("display_comments", array(
    $comments,
    'display'));
/**
 * Получение кол-во используемой памяти 
 */
$tpl->register_function("get_memory_usage", "smarty_get_memory_usage");

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
$tpl->register_function("select_folder", array(
    $input,
    'select_folder'));
/**
 * Отображение рейтинга
 * @param int rid - ID ресурса
 * @param string type - тип ресурса
 * @param int owner - владелец ресурса
 * @param array res - массив ресурса
 * @param int srid - доп. ID ресурса(для уникальности)
 * @param string stype - доп. тип ресурса(для уникальности)
 */
$tpl->register_function("display_rating", array(
    $rating,
    'display'));
/**
 * Поле выбора групп
 * @param string name - имя поля
 * @param int current - текущее значение
 * @param bool guest - в т.ч. и гость?
 * @param bool not_null - без пустого значение?
 * @param bool multiple - множественная выборка?
 */
$tpl->register_function("select_groups", array(
    $input,
    'select_groups'));

/**
 * Форма добавления опроса
 * @param int toid - ID ресурса
 * @param string type - тип ресурса
 * @param int pollid - ID опроса
 * @param bool full - полностью загружать страницу с опросом?
 */
$tpl->register_function("add_polls", array(
    $polls,
    "add_form"));
/**
 * Отображение опроса
 * @param int toid - ID ресурса
 * @param string type - тип ресурса
 * @param int pollid - ID опроса
 * @param bool votes - показывать результат опроса?
 * @param bool short - показывать результаты опроса как в блоке?
 */
$tpl->register_function("display_polls", array(
    $polls,
    "display"));
/**
 * Поле выбора интервала подписок
 * @param string name - имя поля
 * @param int current - текущее значение
 */
$tpl->register_function("select_mailer", array(
    $input,
    'select_mailer'));
/**
 * Поле выбора периода
 * @param string name - имя поля
 * @param int current - текущее значение
 */
$tpl->register_function("select_periods", array(
    $input,
    'select_periods'));
/**
 * Генератор пароля
 * @param string pname - имя поля пароля
 * @param string paname - имя поля повтора пароля 
 */
$tpl->register_function("passgen", 'smarty_passgen');
/**
 * Поле выбора категорий
 * @param string name - имя поля
 * @param int size - размер поля(если больше 1 - множественная выборка)
 * @param int current - текущее значение
 * @param bool not_null - без пустого значение?
 */
$tpl->register_function("select_categories", array(
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
$tpl->register_function("simple_selector", array(
    $input,
    'simple_selector'));
/**
 * Создание настроек для модуля
 */
$tpl->register_function("modsettings_create", array(
    $modsettings,
    'create'));
/// Конец
/// Обнаруживаем IE
if (preg_match("/MSIE\\s*([0-9]+)/siu", $_SERVER ['HTTP_USER_AGENT'], $matches)) {
    $lang->get("ie_error");
    // Выгоняем IE ниже 8 версии
    if ($matches [1] < 8) {
        $tpl->display('ie_error.tpl');
        die();
    }
    $tpl->assign('MSIE', $matches [1]);
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