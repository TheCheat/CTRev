<?php

/**
 * Project:            	CTRev
 * File:                class.display.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс, содержащий методы для вывода ч/л на экран
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class display_html {

    /**
     * Интервалы дат знаков зодиака
     * @var array
     */
    protected $zodiac_intervals = array();

    /**
     * Английские названия знаков зодиака
     * @var array
     */
    protected $zodiac_signs = array(
        "21.01" => "aquarius",
        "21.02" => "pisces",
        "21.03" => "aries",
        "21.04" => "taurus",
        "21.05" => "gemini",
        "22.06" => "cancer",
        "23.07" => "leo",
        "24.08" => "virgo",
        "24.09" => "libra",
        "24.10" => "scorpio",
        "23.11" => "sagittarius",
        "22.12" => "capricorn");

    /**
     * @const paginator_id_prefix - префикс перед ID пейджинатора
     */

    const paginator_id_prefix = 'paginator';

    /**
     * Идентефицирование переменной для пейджинатора
     * @var int
     */
    protected $paginator_id = 0;

    /**
     * @const avatar_prefix - префикс имя файла аватары
     */

    const avatar_prefix = "avatar-id";

    /**
     * Отображение инициализации jQuery Uploadify
     * @global tpl $tpl
     * @global uploader $uploader
     * @param string $id_postfix постфикс к ID(uploadify_$postfix)
     * @param string $type_desc описание к типу
     * @param string $file_type файловый тип
     * @param array $scriptData массив посылаемых данных методом GET
     * @param string $onComplete функция, по окончанию загрузки(вх. параметр response - ответ сервера)
     * @param boolean $print выводить ли сразу div-ы
     * @param boolean $auto автозагрузка
     * @return null
     */
    public function display_uploadify($id_postfix, $type_desc = "", $file_type = "", $scriptData = null, $onComplete = "", $print = false, $auto = true) {
        global $tpl, $uploader;
        if (!is_array($scriptData))
            return;
        if ($file_type) {
            $uploader->init_ft();
            if (!$uploader->filetypes($file_type))
                return;
            $file_types = $uploader->filetypes($file_type);
            $file_types ["types"] = $uploader->convert_filetypes($file_type);
            $tpl->assign("type_desc", $type_desc);
            $tpl->assign("file_type", $file_types);
        }
        $n = 0;
        $scriptData ["from_ajax"] = 1;
        $scriptData ["cookies_by_request"] = 1;
        $scriptData ["login"] = $_COOKIE ["login"];
        $scriptData ["pwd"] = $_COOKIE ["pwd"];
        $scriptData ["short_sess"] = $_COOKIE ["short_sess"];
        $scriptData_str = "";
        foreach ($scriptData as $key => $value) {
            $scriptData_str .= ( $n ? ", " : "") . "'" . addslashes($key) . "': '" . addslashes($value) . "'";
            $n++;
        }
        $tpl->assign("postfix", $id_postfix);
        $tpl->assign("print_divs", $print);
        $tpl->assign("scriptData", $scriptData_str);
        $tpl->assign("onComplete", $onComplete);
        $tpl->assign("auto", $auto);
        $tpl->display("uploadify_init.tpl");
    }

    /**
     * Постраничный вывод записей
     * @global string $theme_path
     * @param int $count кол-во записей
     * @param int $perpage записей на страницу
     * @param string $file добавление переменных в URL на ссылке со страницей, может являться JS функцией, тогда
     * пейджинатор будет передавать номер страницы ей в качестве параметра
     * @param string $var переменная $_GET для страницы
     * @param int $page_on_this страниц за раз
     * @param bool $ajax при указании true, пейджинатор может воспринимать параметр file в качестве JS функции
     * @return array (el1, el2) - массив, содержащий в своём первом элементе строку пейджинатора,
     * а во втором - limit для БД
     */
    public function pages($count, $perpage = 50, $file = "", $var = "page", $page_on_this = 5, $ajax = false) {
        global $theme_path;
        if (!is_numeric($perpage) || !$perpage)
            $perpage = 50;
        if (!is_numeric($page_on_this) || !$page_on_this)
            $page_on_this = 5;
        if (!$var)
            $var = "page";
        if (!$_REQUEST [$var] || !is_numeric($_REQUEST [$var]))
            $page = 1;
        else
            $page = longval($_REQUEST [$var]);
        if (($page - 1) * $perpage >= $count)
            $page = longval($count / $perpage) - ($count % $perpage == 0 ? 0 : 1);
        $start = ($page - 1) * $perpage;
        $this->paginator_id += 1;
        $pages = "";
        if ($this->paginator_id == 1)
            $pages .= "<script type=\"text/javascript\"
	src=\"" . $theme_path . "js/jquery.paginator.js\"></script>";
        $pages .= "<div class=\"paginator\" id=\"" . self::paginator_id_prefix . $this->paginator_id . "\"></div>";
        $pages .= $this->pagintator_js($count, $perpage, $file, $var, $page_on_this, $ajax);
        if ($start <= $count && $start > 0)
            $limit = $start . ", " . $perpage;
        else
            $limit = "0, " . $perpage;
        return array(
            $pages,
            $limit);
    }

    /**
     * Вывод JS скрипта пейджинатора
     * @global lang $lang
     * @param int $count см. назначение из функции pages
     * @param int $perpage см. назначение из функции pages
     * @param string $file см. назначение из функции pages
     * @param string $var см. назначение из функции pages
     * @param int $page_on_this см. назначение из функции pages
     * @param bollean $ajax см. назначение из функции pages
     * @return string JS скрипт пейджинатора
     */
    public function pagintator_js($count, $perpage = 50, $file = "", $var = "page", $page_on_this = 5, $ajax = false) {
        global $lang;
        if (!is_numeric($perpage) || !$perpage)
            $perpage = 50;
        if (!is_numeric($page_on_this) || !$page_on_this)
            $page_on_this = 5;
        if (!$var)
            $var = "page";
        if (!$_REQUEST [$var] || !is_numeric($_REQUEST [$var]))
            $page = 1;
        else
            $page = longval($_REQUEST [$var]);
        if (!is_numeric($count) || !$count)
            $count = 0;
        $pagesTotal = longval($count / $perpage) + ($count % $perpage == 0 ? 0 : 1);
        $qs = preg_replace('/\&' . mpc($var) . '\=([0-9]+)/siu', '', $_SERVER ['QUERY_STRING']);
        $surl = w3c_amp_replace($_SERVER ['PHP_SELF'] . "?" . $qs . "&" . $var . "=%number%");
        if ($ajax && $file)
            $base_url = "javascript:" . $file . '(%number%);';
        else
            $base_url = slashes_smarty($file ? $file : $surl);
        return "<script type='text/javascript'>
		jQuery('#" . slashes_smarty(self::paginator_id_prefix . $this->paginator_id) . "').paginator({
                                'pagesTotal':" . $pagesTotal . ",
				'pagesSpan':" . $page_on_this . ",
				'pageCurrent':" . $page . ",
				'baseurl': '" . $base_url . "',
				'lang' : {next : '" . slashes_smarty($lang->v('paginator_next')) . "',
				last : '" . slashes_smarty($lang->v('paginator_last')) . "',
				prior : '" . slashes_smarty($lang->v('paginator_prev')) . "',
				first : '" . slashes_smarty($lang->v('paginator_first')) . "',
				arrowRight : String.fromCharCode(8594),
				arrowLeft : String.fromCharCode(8592)}});
		</script>";
    }

    /**
     * Функция для показа пользовательского аватара
     * @global string $BASEURL
     * @global lang $lang
     * @global users $users
     * @global string $theme_path
     * @global config $config
     * @global uploader $uploader
     * @param string $avatar_path путь к аватару
     * @return string HTML код аватары
     */
    public function display_user_avatar($avatar_path) {
        global $BASEURL, $lang, $users, $theme_path, $config, $uploader;
        $uploader->init_ft();
        $avatar_path = trim($avatar_path);
        $aft = $uploader->filetypes('avatars');
        $av_allowed = str_replace(";", "|", $aft ['types']);
        if (preg_match('/^' . display::url_pattern . '\.(' . $av_allowed . ')$/siu', $avatar_path))
            $url = $avatar_path;
        elseif (preg_match('/^' . self::avatar_prefix . $users->v('id') . '\.(' . $av_allowed . ')$/siu', $avatar_path))
            $url = $BASEURL . $config->v('avatars_folder') . '/' . $avatar_path;
        else
            $url = $theme_path . 'images/default_avatar.png';
        $max_width = $aft ['max_width'];
        $max_height = $aft ['max_height'];
        $style = ($max_width ? 'max-width: ' . $max_width . 'px;' : "") . ($max_height ? 'max-height: ' . $max_height . 'px;' : "");
        return '<img src="' . $url . '" alt="' . $lang->v('avatar') . '" style="' . $style . '">';
    }

    /**
     * Вывод изображения знака зодиака, согласно ДР пользователя
     * @global string $BASEURL
     * @global config $config
     * @global lang $lang
     * @param int $birthday день рождения в формате UNIXTIME
     * @return string HTML код картинки
     */
    public function get_zodiac_image($birthday) {
        global $BASEURL, $config, $lang;
        $lang->get('zodiac');
        $month = date("m", $birthday);
        $day = date("d", $birthday);
        $time = @mktime(null, null, null, $month, $day);
        if (!$this->zodiac_intervals)
            foreach ($this->zodiac_signs as $date => $word) {
                list ( $day, $month ) = explode(".", $date);
                $this->zodiac_intervals [$word] = @mktime(null, null, null, $month, $day);
            }
        reset($this->zodiac_intervals);
        $cur = current($this->zodiac_intervals);
        $first = @mktime(null, null, null, date("m", $cur) + 1, date("d", $cur));
        $break = false;
        while (!$break) {
            $curtime = current($this->zodiac_intervals);
            $word = key($this->zodiac_intervals);
            $nexttime = next($this->zodiac_intervals);
            if (!$nexttime) {
                $nexttime = $first;
                $break = true;
            }
            if ($time >= $curtime && $time < $nexttime)
                break;
        }
        return '<img src="' . $BASEURL . $config->v('zodiac_folder') . '/' . $word . '.png"
                 height="11" alt="' . $lang->v('zodiac_sign_' . $word) . '"
		 title="' . $lang->v('zodiac_sign_' . $word) . '">&nbsp;' . $lang->v('zodiac_sign_' . $word);
    }

    /**
     * Выборка файлов(для АЦ)
     * @global file $file
     * @global tpl $tpl
     * @global bool $ajax
     * @global lang $lang
     * @param string $path путь к дирректории
     * @param string $name имя дирректории
     * @param string $folder выбранная дирректория
     * @param array $apaths разрешённые дирректории внутри основной
     * @return null
     */
    public function filechooser($path, $name, $folder = null, $apaths = null) {
        global $file, $tpl, $ajax, $lang;
        if (!validfolder($name, $path))
            return;
        $lang->get('admin/filechooser');
        $npath = ($path ? $path . '/' : '') . $name;
        if ($apaths)
            $apaths = (array) $apaths;
        if ($folder) {
            $folder = preg_replace('/(?:^|^(.*)\/)[^\/]+\/\.\.\/$/siu', '\1', $folder);
            $folder = rtrim($folder, '/');
            $folder = validpath($folder . '/', false, $apaths);
            $folder = rtrim($folder, '/');
            if ($folder)
                $npath .= "/" . rtrim($folder, '/');
        }
        $rows = $file->open_folder($npath, false, '^.+(\.[a-z]+)?$');
        if ($rows === false)
            return;
        $file->sort($npath, $rows);
        $arr = array();
        foreach ($rows as $row) {
            $f = ROOT . $npath . '/' . $row;
            if (!$folder && $apaths && !validpath($row . '/', false, $apaths))
                continue;
            if ($row == 'index.html')
                continue;
            $arr[$row] = array(is_dir($f), is_writable($f), filesize($f), filemtime($f));
        }
        $tpl->assign('id', $name);
        $tpl->assign('files', $arr);
        $tpl->assign('deny_add', false);
        if ($folder)
            $tpl->assign('parent', $folder . '/');
        elseif ($apaths)
            $tpl->assign('deny_modify', true);
        if ($ajax)
            print("OK!");
        $tpl->display('admin/filechooser.tpl');
    }

    /**
     * Функция для экранирования HTML кода
     * @param string $html HTML код
     * @param bool $decode деэкранировать до нового экранирования?
     * @param bool $nonbsp не преопразовывать многократные пробелы в &nbsp;?
     * @return string текст с экранированными спец.символами HTML
     */
    public function html_encode($html, $decode = false, $nonbsp = false) {
        if ($decode)
            $html = $this->html_decode($html);
        $i = array("&", "<", ">", "'", '"');
        $t = array("&amp;", "&lt;", "&gt;", "&#39;", "&quot;");
        if (!$nonbsp) {
            $i[] = '  ';
            $t[] = "&nbsp;&nbsp;";
        }
        $html = str_replace($i, $t, $html);
        return $html;
    }

    /**
     * Функция для деэкранирования HTML кода
     * @param string $text текст с экранированными спец.символами HTML
     * @return string HTML код
     */
    public function html_decode($text) {
        $text = str_replace(array("&amp;", "&lt;", "&gt;", "&#39;", "&quot;", "&nbsp;&nbsp;"), array("&", "<", ">", "'", '"', '  '), $text);
        return $text;
    }

}

class display_time extends display_html {

    /**
     * Возвращает UNIX_TIMESTAMP сегодняшнего дня
     * @return int время
     */
    public function curday() {
        return mktime(null, null, null, date("n"), date("j"), date("Y"));
    }

    /**
     * Функция для форматирования времени
     * @global lang $lang
     * @param int $unixtime время в формате UNIXTIME
     * @param string $format формат отображения(y - год, m - месяц, d - день, h - час, i - минута, s - секунда)
     * @return string отформатированная дата
     */
    public function date($unixtime = null, $format = "ymd") {
        global $lang;
        if (is_array($unixtime)) {
            $format = $unixtime ['format'];
            $unixtime = $unixtime ['time'];
        }
        if ($unixtime)
            $unixtime = longval($unixtime);
        if (!$format)
            $format = "ymd";
        if (!$unixtime)
            $unixtime = time();
        if (strtoupper($format) == "RSS")
            return date(DATE_RSS, $unixtime);
        elseif (strtoupper($format) == "ATOM")
            return date(DATE_ATOM, $unixtime);
        else {
            $this->time_diff($unixtime);
            if (preg_match("/^([ymdhis]+)$/siu", $format)) {
                $format = strtolower($format);
                $year = date("Y", $unixtime);
                $month = (date("m", $unixtime) - 1);
                $month = $lang->v("month_" . input::$months [$month] . "_s");
                $day = date("j", $unixtime);
                $h_m = array();
                if (strpos($format, "h") !== false)
                    $h_m [] = date("H", $unixtime);
                if (strpos($format, "i") !== false)
                    $h_m [] = date("i", $unixtime);
                if (strpos($format, "s") !== false)
                    $h_m [] = date("s", $unixtime);
                if ($h_m)
                    $h_m = implode(":", $h_m);
                else
                    $h_m = "";
                return trim((strpos($format, "d") !== false ? $day : "") . " " .
                                (strpos($format, "m") !== false ? $month : "") . " " .
                                (strpos($format, "y") !== false ? $year : "") . " " . $h_m);
            } else
                return date($format, $unixtime);
        }
    }

    /**
     * Функция для изменения времени, в соответствии с часовым поясом и DST
     * @global users $users
     * @param int $time время, формат UNIXTIME
     * @return null
     */
    public function time_diff(&$time) {
        global $users;
        $time = longval($time);
        $user_timezone = $users->v('timezone');
        $dst = $users->v('dst');
        $now_dst = date("I");
        if ($now_dst && $dst)
            $user_timezone += 1;
        $cur_timezone = date("Z");
        $hour = 3600;
        $plus_sec = $user_timezone * $hour - $cur_timezone;
        $time += $plus_sec;
    }

    /**
     * Вывод разницы времени
     * @global lang $lang
     * @param int $from начальное время
     * @param int $to конечное время
     * @return string текст разницы
     */
    public function get_estimated_time($from, $to = 0) {
        global $lang;
        if ($to === "c")
            $to = time();
        if (!$to)
            $d = $from;
        else
            $d = $to - $from;
        if ($d < 60)
            return $lang->v('et_seconds');
        $ret = array(abs($d));
        $week = false;
        $weekstr = "et_weeks";
        $lg = array("et_seconds",
            "et_minutes",
            "et_hours",
            "et_days",
            //"et_weeks",
            "et_months",
            "et_year");
        $delta = array(60, 60, 24, 7, 12);
        for ($i = 0; $i < 5; $i++) {
            if ($i == 3) {
                if ($ret[$i] >= 7 && $ret[$i] < 30) {
                    $ret[$i + 1] = longval($ret[$i] / 7);
                    $ret[$i] -= $ret[$i + 1] * 7;
                    $week = true;
                } elseif ($ret[$i] >= 30) {
                    $ret[$i + 1] = longval($ret[$i] / 30);
                    $ret[$i] -= $ret[$i + 1] * 30;
                }
                continue;
            }
            if ($ret[$i] >= $delta[$i]) {
                $ret[$i + 1] = longval($ret[$i] / $delta[$i]);
                $ret[$i] -= $ret[$i + 1] * $delta[$i];
            } else
                break;
        }
        $answ = "";
        if ($week)
            $lg[4] = "et_weeks";
        $be = 0;
        for (; $i >= 1; $i--) {
            if ($be >= 2)
                break;
            if ($ret[$i] > 0 || ($i == 0 && !$be)) {
                $answ .= ( $answ ? " " : "") . ($i != 0 ? $ret[$i] . " " : "") . $lang->v($lg[$i]);
                $be++;
            }
        }
        return $answ;
    }

    /**
     * Преобразование получаемого времени в UNIXTIME
     * @param string $name префикс поля
     * @param string $must_be обязательные поля для выборки времени(формат ymdhis)
     * @return int время в формате UNIXTIME
     */
    public function make_time($name = "", $must_be = "") {
        $name = ($name ? $name . "_" : "");
        $yarr = array("year", "month", "day", "hour", "minute", "second");
        if ($must_be) {
            $must_be = strtolower($must_be);
            foreach ($yarr as $field) {
                if ($field == "minute")
                    $f = "i";
                else
                    $f = $field[0];
                if (strpos($must_be, $f) !== false && !longval($_REQUEST [$name . $field]))
                    return 0;
            }
        }
        $r = unsigned(@mktime((int) $_REQUEST [$name . "hour"], (int) $_REQUEST [$name . "minute"], (int) $_REQUEST [$name . "second"], (int) $_REQUEST [$name . "month"], (int) $_REQUEST [$name . "day"], (int) $_REQUEST [$name . "year"]));
        return $r;
    }

}

class display_modifier extends display_time {

    /**
     * Входящие символы при транслитировании
     * @var array|string
     */
    protected $from_transl = "а.б.в.г.д.е.ё.ж.з.и.й.к.л.м.н.о.п.р.с.т.у.ф.х.ц.ч.ш.щ.ъ.ы.ь.э.ю.я. .-";
    /*
     * Соответствесвующие им символы при транслитировании
     * @var array|string
     */
    protected $to_transl = "a.b.v.g.d.e.e.zh.z.i.y.k.l.m.n.o.p.r.c.t.u.f.h.c.ch.sh.sch..y..e.u.ya._.-";

    /**
     * Соответствие между транслитируемыми символами
     * @var array
     */
    protected $transl_rules = array();

    /**
     * Функция транслитирования строки
     * @param string $string входная строка
     * @param int $cut обрезать строку?
     * @return string транслитированная строка
     */
    public function translite($string, $cut = 0) {
        $string = mb_strtolower($string);
        if ($cut)
            $string = (mb_strlen($string) > $cut ? mb_substr($string, 0, $cut) : $string);
        if (!is_array($this->from_transl))
            $this->from_transl = explode(".", mb_strtolower($this->from_transl));
        if (!is_array($this->to_transl))
            $this->to_transl = explode(".", mb_strtolower($this->to_transl));
        $this->transl_rules = array_combine($this->from_transl, $this->to_transl);
        $string = preg_replace('/[' . mpc('.,;!@#$%^&*()"№;:?+=~`\'\"<>/\/{}') . ']/siu', '', $string);
        $out = "";
        for ($i = 0; $i < mb_strlen($string); $i++) {
            $si = s($string, $i); // для UTF-8
            if (isset($this->transl_rules [$si]))
                $out .= $this->transl_rules [$si];
            else if (is_numeric($si) || ($si > 'a' && $si < 'z'))
                $out .= $si;
            else
                $out .= urlencode($si);
        }
        if (in_array($out, array("edit", "delete", "add", "edit")))
            $out = "t" . $out;
        return $out;
    }

    /**
     * "Переворачивание" текста
     * @param string $string текст
     * @return string перевёрнутый
     */
    public function reverse_text($string) {
        //$string = mb_convert_encoding($string, 'windows-1251');
        //return mb_convert_encoding(strrev($string), 'UTF-8', 'windows-1251');
        $c = mb_strlen($string) - 1;
        $new = "";
        for ($i = $c; $i >= 0; $i--)
            $new .= s($string, $i);
        return $new;
    }

    /**
     * Функция для обрезания ../ и ./ в строке
     * @param string $path путь
     * @return string "обрезанный" путь
     */
    public function strip_subpath($path) {
        $path = preg_replace('/(\.|\.\.)(\/|\\\)/siu', '', $path);
        return $path;
    }

    /**
     * Обрезание текста и добавление трёх точек в конец
     * @param string $txt текст
     * @param int $car макс. длина
     * @return string обрезанный текст
     */
    public function cut_text($txt, $car) {
        if (mb_strlen($txt) > $car && $car)
            return mb_substr($txt, 0, $car) . "...";
        return $txt;
    }

    /**
     * Конвертирование размера файла, байты=>кибибайты,мебибайты,гибибайты,тебибайты
     * @param int $bytes размер файла в байтах
     * @return string размер файла в кибибайтах, или мебибайтах, или гибибайтах, или тебибайтах.
     */
    public function convert_size($bytes) {
        if ($bytes >= 1024 * 1024 * 1024 * 1024)
            return number_format($bytes / (1024 * 1024 * 1024 * 1024), 2) . "&nbsp;tBytes";
        elseif ($bytes >= 1024 * 1024 * 1024)
            return number_format($bytes / (1024 * 1024 * 1024), 2) . "&nbsp;gBytes";
        elseif ($bytes >= 1024 * 1024)
            return number_format($bytes / (1024 * 1024), 2) . "&nbsp;mBytes";
        elseif ($bytes >= 1024)
            return number_format($bytes / (1024), 2) . "&nbsp;kBytes";
        else
            return number_format($bytes, 2) . "&nbsp;Bytes";
    }

}

class display extends display_modifier {
    /**
     * Паттерн URL
     * 2 - протокол
     * 3 - домен
     * 4 - порт
     * 5 - оставшаяся часть
     *
     * про parse_url помним, но сие надёжнее :)
     * @const string url_pattern
     */

    const url_pattern = '((http|https|ftp|udp)\:\/\/((?:[a-zа-я0-9\-\_]+\.[a-zа-я0-9\-\_\.]+)|localhost)(\:[0-9]+)?([a-zа-я0-9\.\?\%\=\&\/\-\_\#\;]*?))';

    /**
     * Цвета для ратио(если первая буква $n, то цвет будет #$n$n0000)
     * @var string
     */
    protected $ratio_color = "fedcba9876";

    /**
     * Дельта порога для цвета
     * @var float
     */
    protected $ratio_per = 0.1;

    /**
     * Цвета для ратио торрента(если первая буква $n, то цвет будет #$n$n0000)
     * @var string
     */
    protected $slr_color = "fedcba987654321";

    /**
     * Дельта порога для цвета
     * @var float
     */
    protected $slr_per = 0.025;

    /**
     * Экранирование строки
     * @param string $val входная строка
     * @return string экранированная строка
     */
    public function jslashes($val) {
        $val = preg_replace('/\r?\n\r?/', '\r\n', $val);
        return addslashes($val);
    }

    /**
     * Экспорт массива в JS
     * @param array $array массив
     * @param array $export экспортировать лишь данные ключи
     * @return string массив в JS
     */
    public function array_export_to_js($array, $export = null) {
        $JS_array = "";
        foreach ($array as $key => $val)
            if (!is_array($val)) {
                if (!$export || in_array($key, $export))
                    $JS_array .= ( $JS_array ? ", " : "{") . '"' . $this->jslashes($key) . '": "' . $this->jslashes($val) . '"';
            } else
                $JS_array .= ( $JS_array ? ", " : "{") . '"' . $this->jslashes($key) . '": ' . $this->array_export_to_js($val);
        return $JS_array . ($JS_array ? "}" : "");
    }

    /**
     * Функция для "окрашивания" группы пользователя
     * @global users $users
     * @param int $group_id ID группы
     * @param string $group_name имя группы
     * @param bool $bbcode BBCode?
     * @return string HTML код окрашенной группы
     */
    public function group_color($group_id, $group_name = '', $bbcode = false) {
        global $users;
        if (!$group_id)
            $group_id = $users->guest_group;
        $quote = $this->html_encode('"');
        $bopen = $bbcode ? "[b]" : "<b>";
        $bclose = $bbcode ? "[/b]" : "</b>";
        $sopen = $bbcode ? "[s]" : "<s>";
        $sclose = $bbcode ? "[/s]" : "</s>";
        $fopen = $bbcode ? "[color=" . $quote : "<font color='";
        $fopen2 = $bbcode ? $quote . "]" : "' title=\"" . ($group_name ? $users->get_group_name($group_id) : "") . "\">";
        $fclose = $bbcode ? "[/color]" : '</font>';
        return $bopen . ($group_id == users::banned_group ? $sopen : "") .
                $fopen . $users->get_group_color($group_id) . $fopen2 . ($group_name ? $group_name :
                        $users->get_group_name($group_id)) . $fclose .
                ($group_id == users::banned_group ? $sclose : "") . $bclose;
    }

    /**
     * Удаление переменных времени
     * @param string $what удаляемые поля для выборки времени(формат ymdhis)
     * @param string $name имя выборки
     * @return null
     */
    public function remove_time_fields($what, $name = "") {
        $what = strtolower($what);
        $yarr = array("year", "month", "day", "hour", "minute", "second");
        $name = ($name ? $name . "_" : "");
        foreach ($yarr as $field) {
            if ($field == "minute")
                $f = "i";
            else
                $f = $field[0];
            if (strpos($must_be, $f)) {
                unset($_REQUEST [$name . $field]);
                unset($_POST [$name . $field]);
                unset($_GET [$name . $field]);
            }
        }
    }

    /**
     * Цвет ратио
     * @param float $ratio ратио
     * @param bool $slr для торрента?
     * @return string цвет
     */
    public function ratio_color($ratio, $slr = false) {
        if (!$slr) {
            $arr = $this->ratio_color;
            $per = $this->ratio_per;
        } else {
            $arr = $this->slr_color;
            $per = $this->slr_per;
        }
        $ratio = intval($ratio / $per);
        if ($arr[$ratio])
            return "#" . $arr[$ratio] . $arr[$ratio] . "0000";
        return "#000000";
    }

    /**
     * Автовключение сайта
     * @global config $config
     * @global furl $furl
     * @return null
     */
    public function site_autoon() {
        global $config, $furl;
        if (!$config->v('site_autoon') || $config->v('site_online'))
            return;
        $time = time();
        if ($time >= $config->v('site_autoon')) {
            $config->set('site_autoon', 0);
            $config->set('site_online', 1);
            $furl->location();
        }
    }

    /**
     * Проверка, является ли сайт offline на данный момент
     * @global users $users
     * @global config $config
     * @global tpl $tpl
     * @global lang $lang
     * @return null
     */
    public function siteoffline_check() {
        global $users, $config, $tpl, $lang;
        if ($users->perm('acp', 2))
            return;
        elseif (!$config->v('site_online')) {
            $lang->get("site_offline");
            $offline_reason = $config->v('siteoffline_reason');
            $tpl->assign("reason", $offline_reason);
            $tpl->display("site_offline.tpl");
            die();
        }
    }

}

?>