<?php

if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * Пустая функция, для обозначения чего-то в будущем
 */
function fNULL() {
    
}

/**
 * Проверка на то, разрешено ли в параметре {@link $v} действие {@link $c}
 * @param int $v значение параметра
 * @param int $c значение действия
 * @return bool true, если да
 */
function is($v, $c) {
    return ($v & $c) === $c;
}

/**
 * Инициализация класса, если сие ещё не произошло для последующей
 *  передачи переменной в аргументе метода по ссылке.
 * Собстно костыль, ибо PHP не любит ссылки.
 * @param object $obj объект инициализации
 * @return object в случае, если лень писать в отдельную строку
 */
function ref(&$obj) {
    if ($obj instanceof system)
        return $obj->make_object();
    else
        return $obj;
}

/**
 * Проверка на существование дирректории
 * @param string $folder проверяемая дирректория
 * @param string $where где находится
 * @return bool если сие существует
 */
function validfolder($folder, $where = THEMES_PATH) {
    return (validword($folder) || !$where) && is_dir(ROOT . ($where ? $where . "/" : "") . $folder);
}

/**
 * Удаляем из пути двойные слеши и попытки переместиться назад 
 * @param string $path исходный путь
 * @param bool $htaccess разрешить .htaccess?
 * @param string $subpath путь, который должен быть в начале
 * @return string обработанный путь
 */
function validpath($path, $htaccess = false, $subpath = '') {
    if (!$path)
        return '';
    if (!$htaccess && preg_match('/\.htaccess$/siu', $path))
        return '';
    $path = preg_replace('/\.?\.\//', '', $path);
    $path = preg_replace('/\/{2,}/', '\/', $path);
    $path = ltrim($path, '/');
    if ($subpath) {
        $subpath = (array) $subpath;
        $a = false;
        foreach ($subpath as $sp)
            if (mb_strpos($path, $sp . '/') === 0)
                $a = true;
        if (!$a)
            return '';
    }
    return $path;
}

/**
 * ID ли это? (long и больше 0)
 * @param mixed $id проверяемая переменная
 * @return bool true, если таки ID
 */
function validid($id) {
    return longval($id) == $id && $id > 0;
}

/**
 * Получение|Изменение символа в UTF-8 строке
 * @param string $string строка
 * @param int $i индекс символа
 * @param char $val то, чем можно заменить
 * @return null|char символ
 */
function s(&$string, $i, $val = null) {
    if ($val)
        $string = mb_substr($string, 0, $i) . $val . mb_substr($string, $i + 1);
    else
        return mb_substr($string, $i, 1);
}

/**
 * Свой вывод ошибок
 * @param int $errorno номер ошибки
 * @param string $errormsg текст ошибки
 * @param string $file файл с ошибкой
 * @param int $line диния с ошибкой
 * @return null;
 */
function myerror_report($errorno, $errormsg, $file, $line) {
    if (error_reporting() == 0) {
        return;
    }
    $file = cut_path($file);
    $errormsg = cut_path($errormsg);
    switch ($errorno) {
        case E_USER_NOTICE :
        case E_STRICT :
        case E_NOTICE :
            return;
            break;
        case E_COMPILE_ERROR :
        case E_CORE_ERROR :
        case E_USER_ERROR :
        case E_RECOVERABLE_ERROR :
        case E_ERROR :
            $errtext = "Error";
            break;
        case E_COMPILE_WARNING :
        case E_CORE_WARNING :
        case E_USER_WARNING :
        case E_WARNING :
            $errtext = "Warning";
            break;
        case E_DEPRECATED :
            $errtext = "Deprecated";
            break;
        case E_PARSE :
            $errtext = "Parsing Error";
            break;
//case E_STRICT :
//	$errtext = "Strict Error";
//	break;
        default :
            $errtext = "Unknown Error";
            break;
    }
    if (defined('INANNOUNCE')) {
        $bt = new fbenc();
        $bt->err("[{$errtext}] №" . $errorno . ": " . $errormsg . "(" . $file . ":" . $line . ")");
    } else
        echo "<i>[{$errtext}]</i> №<b>" . $errorno . "</b>: " . $errormsg . " in <b>" . $file . "</b>, line <b>" . $line . "</b><br>";
}

/**
 * Функция отправки E-mail сообщения
 * @param string $subject тема сообщения
 * @param string $body текст сообщения
 * @param string|array $to кому
 * @param string $error ошибоки
 * @return null
 */
function send_mail($subject, $body, $to, &$error = '') {
    $to = (is_array($to) ? implode($to, ", ") : $to);
//@header('Content-Type: text/plain');
    if (config::o()->v('smtp_method') == "external") {
        $params ['smtpServer'] = config::o()->v('smtp_host');
        $params ['port'] = config::o()->v('smtp_port');
        $params ['localdomain'] = $_SERVER ['SERVER_NAME'];
        $params ['username'] = config::o()->v('smtp_user');
        $params ['password'] = config::o()->v('smtp_password');
        $smtp = new smtp($to, $subject, $body, $params);
        $error = $smtp->endError;
        unset($smtp);
    } else {
        $headers = 'From: ' . config::o()->v('contact_email') . "\r\n" .
                'Reply-To: ' . config::o()->v('contact_email') . "\r\n" .
                'Content-Type: text/html; charset=utf-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        $error = @mail($to, $subject, $body, $headers);
    }
}

/**
 * Слово ли это? (соотв. $check)
 * @param string $word проверяемая переменная
 * @param string $check часть регэкспа по кот. проверяем слово ли это?
 * latin - только латинские символы, цифры, '-' и '_'
 * @param int $min мин. кол-во символов
 * @return bool true, если таки слово
 */
function validword($word, $check = 'latin', $min = 2) {
    if ($check == 'latin' || !$check)
        $check = '[a-z0-9\_\-]';
    $min = (int) $min;
    if (!$min)
        $min = 2;
    return $word && !is_numeric($word) && preg_match('/^' . $check . '{' . $min . ',}$/siu', $word);
}

/**
 * Удаление "магических" кавычек
 * @param array $arr- исследуемый массив
 * @return array полученный массив
 */
function strip_magic_quotes($arr) {
    if (!function_exists("set_magic_quotes_runtime"))
        return;
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $arr [$k] = strip_magic_quotes($v);
        } else {
            $arr [$k] = stripslashes($v);
        }
    }
    return $arr;
}

/**
 * Функция вывода сообщения
 * @global bool $ajax
 * @param string $lang_var языковая переменная, в соответствии с которой будет выводится на экран сообщение,
 * либо цельный текст.
 * @param array $vars массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
 * @param string $type тип выводимого значения, в зависимости от него будут выбраны различные стили вывода сообщения(error|success|info)
 * @param bool $die если параметр установлен на true, то сразу после выведения сообщения, скрипт останавливается
 * @param string $title заголовок, выше сообщения, если 0, то не выводится
 * @param string $align расположение текста в сообщении(left|right|center)
 * @param bool $no_image если параметр установлен на true, то статусная картинка не выводится
 * @param bool $only_box если параметр установлен на true, то выводится только message.tpl
 * @return null
 */
function mess($lang_var, $vars = array(), $type = "error", $die = true, $title = false, $align = 'left', $no_image = false, $only_box = false) {
    global $ajax;
    if (is_array($lang_var)) {
        if ($lang_var ['vars'])
            $vars = $lang_var ['vars'];
        if ($lang_var ['type'])
            $type = $lang_var ['type'];
        if ($lang_var ['title'])
            $title = $lang_var ['title'];
        if ($type != "error")
            $die = false;
        if (isset($lang_var ['die']))
            $die = $lang_var ['die'];
        if ($lang_var ['align'])
            $align = $lang_var ['align'];
        if (isset($lang_var ['no_image']))
            $no_image = $lang_var ['no_image'];
        if ($lang_var ['lang_var'])
            $lang_var = $lang_var ['lang_var'];
    }
    if ($die && !tpl::o()->displayed('overall_header.tpl') && !tpl::o()->displayed('admin/header.tpl') && !$only_box && !$ajax)
        tpl::o()->display('overall_header.tpl');
    $type = ($type ? $type : "error");
    $align = ($align ? $align : "left");
    if (!$title && $title !== 0)
        $title = lang::o()->v($type);
    elseif ($title && lang::o()->visset($title))
        $title = lang::o()->v($title);
    tpl::o()->assign('type', $type);
    tpl::o()->assign('align', $align);
    tpl::o()->assign('title', $title);
    tpl::o()->assign('no_image', $no_image);
    $lv = lang::o()->if_exists($lang_var);
    $vars = $vars ? (array) $vars : null;
    if (is_array($vars))
        tpl::o()->assign('message', vsprintf($lv, $vars));
    else
        tpl::o()->assign('message', $lv);
    if ($die)
        tpl::o()->assign("died_mess", true);
    tpl::o()->display('message.tpl');
    if ($die && !$only_box && !$ajax) {
        if (tpl::o()->displayed('overall_header.tpl') && !tpl::o()->displayed('overall_footer.tpl'))
            tpl::o()->display('overall_footer.tpl');
        elseif (tpl::o()->displayed('admin/header.tpl') && !tpl::o()->displayed('admin/footer.tpl'))
            tpl::o()->display('admin/footer.tpl');
    }
    if ($die)
        die();
}

/**
 * Функция вывода ошибки(именно ошибки типа fatal error, а не сообщения об ошибке,
 * которое выводится через функцию mess)
 * @global bool $ajax
 * @param string|array $lang_var языковая переменная, в соответствии с коорой будет выводится на экран сообщение,
 * либо цельный текст, так же, может содержать в себе все остальные паремтры в качестве ассоциативного массива.
 * @param array $vars массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
 * @param string $title заголовок, выше сообщения
 * @return null
 */
function error($lang_var, $vars = array(), $title = false) {
    global $ajax;
    ob_end_clean();
    if (!$title && !is_null($title))
        $title = lang::o()->v('error');
    elseif ($title && lang::o()->visset($title))
        $title = lang::o()->v($title);
    if (lang::o()->visset($lang_var)) {
        $vars = (!is_array($vars) && $vars ? array(
                    $vars) : $vars);
        if (is_array($vars))
            $message = vsprintf(lang::o()->v($lang_var), $vars);
        else
            $message = lang::o()->v($lang_var);
    } else
        $message = $lang_var;
    if ($ajax) {
        print($title . ": " . $message);
        die();
    }
    tpl::o()->assign('message', $message);
    tpl::o()->assign('title', $title);
    tpl::o()->display("error.tpl");
    die();
}

/**
 * Обрезаем XSS "примочки" у массивов
 * @param string|array $arr "обрезаемый" массив
 * @return string|array "обрезанный" массив
 */
function xss_array_protect($arr) {
    if (!$arr)
        return $arr;
    if (!is_array($arr) && !is_numeric($arr)) {
        return display::o()->html_encode($arr);
    } else {
        foreach ($arr as $key => $value) {
            if (is_array($value))
                $arr [$key] = xss_array_protect($value);
            elseif (!is_numeric($value))
                $arr [$key] = display::o()->html_encode($value);
        }
        return $arr;
    }
}

/**
 * Получение размера файла из его ресурса
 * @param resource $handler ресурса файла
 * @return int размер файла
 */
function fsize($handler) {
    if (is_resource($handler)) {
        $s = fstat($handler);
        return $s['size'];
    } else
        return 0;
}

/**
 * Функция подсчёта данного времени в миллисекундах
 * @return int данное время
 */
function timer() {
    list ( $usec, $sec ) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/**
 * Функция для обрезания полного пути
 * @param string $path путь
 * @return string обрезанный путь
 */
function cut_path($path) {
    $path = str_replace(ROOT, '', $path);
    $path = str_replace(substr(ROOT, 0, strlen(ROOT) - 1) . "\\", '', $path);
    return $path;
}

/**
 * Обрезаем ненужные нам слеши
 * @param string|array $arr "обрезаемый" массив
 * @return string|array "обрезанный" массив
 */
function strip_quotes($arr) {
    if (!$arr)
        return $arr;
    if (!is_array($arr)) {
        return stripslashes($arr);
    } else {
        foreach ($arr as $key => $value) {
            if (is_array($value))
                $arr [$key] = strip_quotes($value);
            else
                $arr [$key] = stripslashes($value);
        }
        return $arr;
    }
}

/**
 * Аналог preg_quote с предустановленным делимиттером '/'
 * @param string $str входящая строка
 * @param string $del делимиттер
 * @return string экранированный для регулярок текст
 */
function mpc($str, $del = '/') {
    return preg_quote((string) $str, $del);
}

/**
 * Реализация функции ip2ulong с выводом беззнакового значения
 * @param string $ip IP адресс
 * @return int беззнаковое целочисленное представление IP адреса
 */
function ip2ulong($ip) {
    return substr($ip, 0, 3) > 127 ? ((ip2long($ip) & 0x7FFFFFFF) + 0x80000000) : ip2long($ip);
}

/**
 * Выделение беззнакового числа
 * @param string $string строка
 * @return int число
 */
function unsigned($string) {
    return sprintf('%u', $string);
}

/**
 * Аналог intval для long
 * @param string $string строка
 * @return int число
 */
function longval($string) {
    $string = floatval(trim($string));
    $a = explode('.', $string);
    $i = reset($a);
    return $i;
}

/**
 * Инициализация базового пути к сайту
 * @global string $BASEURL
 * @global string $PREBASEURL
 * @return null
 */
function init_baseurl() {
    global $BASEURL, $PREBASEURL;
    if (class_exists("config") && config::o()->v('baseurl'))
        $PREBASEURL = (config::o()->v('baseurl') == "/" ? "/" : config::o()->v('baseurl') . "/");
    else
        $PREBASEURL = preg_replace('/^(.*)(\/|\\\)(.*?)$/siu', '\1/', $_SERVER['PHP_SELF']);
    $BASEURL = 'http://' . $_SERVER ['SERVER_NAME'] . $PREBASEURL;
}

/**
 * Инициализация путей для Smarty
 * @global string $BASEURL
 * @global string $theme_path
 * @global string $_style
 * @return null
 */
function init_spaths() {
    global $BASEURL, $theme_path, $_style;
    if ($users && $_style == users::o()->get_theme() && $_style)
        return;
    if ($users && users::o()->get_theme())
        $_style = users::o()->get_theme();
    else
        $_style = DEFAULT_THEME;
    tpl::o()->left_delimiter = "[*";
    tpl::o()->right_delimiter = "*]";
    tpl::o()->set_theme($_style);
    $theme_path = $BASEURL . THEMES_PATH . '/' . $_style . '/';
    tpl::o()->assign('theme_path', $theme_path);
}

/**
 * Функция получения ключа для передачи в форму, для защиты от CSRF
 * @param int $ajax 2, если в AJAX, возвращается, как элемент объекта(напр. fk:'1',)
 * 1 - если в AJAX, возвращается, как часть строки запроса(напр. ?fk=1&)
 * иначе - если элемент формы(напр. <input type='hidden' value='1' name='fk'>)
 * по-умолчанию возвращается лишь значение ключа
 * @param string $var имя ключа
 * @return string сформированное значение ключа
 */
function get_formkey($ajax = null, $var = "fk") {
    if (is_array($ajax)) {
        if ($ajax["var"])
            $var = $ajax["var"];
        if (isset($ajax["ajax"]))
            $ajax = $ajax["ajax"];
        else
            $ajax = null;
    }
    if (!$var || !is_string($var))
        $var = "fk";
    //// Перепереперепереперестраховался
    $ret = md5(config::o()->v('secret_key') . md5(session_id() . config::o()->v('secret_key')) .
            users::o()->get_ip() . users::o()->v('id') . $_SERVER["HTTP_USER_AGENT"] . $var);
    if (is_null($ajax))
        return $ret;
    elseif ($ajax == 2)
        return "'" . addslashes($var) . "':'" . $ret . "',";
    elseif ($ajax == 1)
        return $var . "=" . $ret . "&";
    else
        return "<input type='hidden' value='" . $ret . "' name='" . $var . "'>";
}

/**
 * Проверка ключа формы для защиты от CSRF
 * @param string $var имя ключа
 * @return bool true, если ключ верен
 * @throws EngineException 
 */
function check_formkey($var = "fk") {
    if (users::o()->check_adminmode())
        return true;
    if (!$var)
        $var = "fk";
    $ret = get_formkey(null, $var) == $_REQUEST[$var];
    if (!$ret)
        throw new EngineException("bad_check_key");
    return $ret;
}

/**
 * Анти-флуд проверка
 * @param string $table таблица
 * @param string $where условие
 * @param array $column столбец автора и времени постинга соотв.
 * @return null
 * @throws EngineException 
 */
function anti_flood($table, $where, $columns = array("poster_id", "posted_time")) {
    if (!is_array($columns) || !config::o()->v('antispam_time'))
        return;
    list($author, $time_var) = $columns;
    $time = time() - config::o()->v('antispam_time');
    $lang_var = 'anti_flood_subj';
    $uid = users::o()->v('id') ? users::o()->v('id') : -1;
    $c = db::o()->query('SELECT `' . $time_var . '` FROM `' . $table . '` WHERE ' . ($where ? $where . " AND " : "") .
            '`' . $author . "`=" . $uid . "
                AND `" . $time_var . "` >= " . $time . '
                ORDER BY `' . $time_var . '` DESC LIMIT 1');
    $c = db::o()->fetch_assoc($c);
    if ($c) {
        $intrvl_time = display::o()->get_estimated_time(config::o()->v('antispam_time') + 1, time() - $c[$time_var]);
        throw new EngineException($lang_var, $intrvl_time);
    }
}

/**
 * Выделение нужной части из массива для последующего извлечения
 * @param array $data массив данных
 * @param array $data_params массив извлекаемых переменных
 * @return array нужная часть
 */
function rex($data, $data_params) {
    $a = array();
    foreach ($data_params as $k => $v)
        $a[is_numeric($k) ? $v : $k] = $data[$v];
    return $a;
}

/**
 * Соединение массивов внутри массива
 * @param array $input входные данные
 * @return array соединённый массив 
 */
function array_merge_inside($input) {
    $c = count($input);
    $output = array();
    for ($i = 0; $i < $c; $i++)
        $output = array_merge($output, $input[$i]);
    return $output;
}

/**
 * Добавление логов
 * @param string $subject тема записи
 * @param string $type тип записи(user|admin|system|other)
 * @param array $vars массив переменных для vsprintf
 * @param int $touid действие по отношению к пользователю
 * @return null
 */
function log_add($subject, $type = "user", $vars = array(), $touid = null) {
    $langs = lang::o()->get('logs', DEFAULT_LANG, false);
    $subject = "log_" . $subject;
    if (!isset($langs[$subject]))
        $subject = 'NOSUBJECT_' . $subject;
    else
        $subject = $langs[$subject];
    $descr = "";
    if (!$type)
        $type = "user";
    if (isset($langs["log_" . $subject . "_descr"]))
        $descr = $langs["log_" . $subject . "_descr"];
    elseif ($vars)
        $descr = "%s";
    if ($vars) {
        $vars = (array) $vars;
        $descr = vsprintf($descr, $vars);
    }
    $contents = array(
        "subject" => $subject,
        "descr" => $descr,
        "type" => $type,
        "time" => time(),
        "byuid" => users::o()->v('id'), // -1 - система
        "byip" => users::o()->get_ip(),
        "touid" => (int) $touid);
    db::o()->insert($contents, "logs");
}

/**
 * Добавление своего значения в массив после {@link $value}
 * @param array $array массив
 * @param mixed $value после чего добавить?
 * @param mixed $what что добавить?
 * @param mixed $key добавляемый ключ 
 * @return array новый массив
 */
function array_value_append($array, $value, $what, $key = null) {
    $na = array();
    foreach ($array as $k => $v) { // foreach - лучший вариант, ящитаю(есть ещё array_values+array_search+array_splice(к примеру))
        $na[$k] = $v;
        unset($array[$k]);
        if ($v == $value) {
            if ($key)
                $na[$key] = $what;
            else
                $na[] = $what;
            break;
        }
    }
    return array_merge($na, $array);
}

/**
 * Выводит сообщение об отключенной функции
 * @return null
 */
function disabled() {
    mess('function_was_disabled_by_admin', null, "info", false);
}

if (!function_exists('class_alias')) {

    /**
     * Создание алиаса к классу
     * @param string $original имя оригинального класса
     * @param string $alias имя алиаса
     * @return bool true, если успешно создали алиас
     */
    function class_alias($original, $alias) {
        if (!class_exists($original, false))
            return false;
        $p = ROOT . CLASS_ALIASES;
        $f = file_get_contents($p);
        $string = "if (!class_exists('" . $alias . "') && class_exists('" . $original . "')){class " . $alias . " extends " . $original . "{}}\n";
        if (mb_strpos($f, $string) === false) {
            if (class_exists($alias))
                return true;
            if (!!validword($alias))
                return false;
            $f = mb_substr($f, 0, mb_strlen($f) - 2);
            $f .= $string . '?>';
        } else
            return true;
        file_put_contents($p, $f);
        load_aliases();
        return true;
    }

    /**
     * Загрузка алиасов классов
     * @return null
     */
    function load_aliases() {
        include ROOT . CLASS_ALIASES;
    }

    /**
     * Очистка алиасов классов
     * @return null
     */
    function clear_aliases() {
        $content = '<?php
// Autogenerated file. DO NOT EDIT
?>';
        file::o()->write_file($content, CLASS_ALIASES);
    }

}

// Преобразование массива в объект
final class arr2obj {

    /**
     * Массив переменных
     * @var array
     */
    private $vars = array();

    /**
     * Конструктор
     * @param array $vars массив переменных
     * @return null
     */
    public function __construct($vars) {
        $this->vars = (array) $vars;
    }

    /**
     * Запрос значения поля
     * @param string $name имя поля
     * @return mixed значение
     */
    public function &__get($name) {
        return $this->vars[$name];
    }

    /**
     * Установка значения поля
     * @param string $name имя поля
     * @param mixed $value новое значение поля
     * @return null
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Проверка на существование поля
     * @param string $name имя поля
     * @return bool true если существует
     */
    public function __isset($name) {
        return isset($this->vars[$name]);
    }

}

/**
 * Бонус за сидирование
 * @param int $uoffset загрузил за интервал апдейта
 * @param int $time время загрузки
 * @param int $user ID пользователя
 * @return null
 */
function peer_bonus($uoffset, $time, $user) {
    $d = 0;
    if (!config::o()->v('announce_interval'))
        return;
    $k = (time() - $time) / (config::o()->v('announce_interval') * 60);
    if ($k > 2)
        return; // что-то тут не то.
    if ($k > 1)
        $k = 1; // Слоупоки не приветствуются
    if (config::o()->v('maxbonus_mb')) {
        $d = $uoffset / (config::o()->v('maxbonus_mb') * 1024 * 1024);
        if ($d > 1)
            $d = 1;
        if ($d < 0)
            $d = 0;
    }
    $bonus = config::o()->v('minbonus') + (config::o()->v('maxbonus') - config::o()->v('minbonus')) * $d;
    $bonus = number_format($k * $bonus, 2);
    if ($bonus < 1 || $bonus < config::o()->v('minbonus') * 0.1)
        return; // Да ладно, всего 1 бонус, ну.. или чуть побольше, в зависимости от желаний администратора.
    /* @var $etc etc */
    $etc = n("etc");
    $etc->add_res('bonus', $bonus, "users", $user);
}

/**
 * Получение объекта переопределённого класса
 * @param string $class имя класса
 * @param bool $name только имя?
 * @return object объект класса
 */
function n($class, $name = false) {
    if (!class_exists('plugins'))
        return $name ? $class : new $class();
    return plugins::o()->get_class($class, $name);
}

?>