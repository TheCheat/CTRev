<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.input.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для вывода различных инпатов на экран
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class input {

    /**
     * Все месяца года на английском, для языковых переменных
     * @var array $months
     */
    public static $months = array(
        "january",
        "febrary",
        "march",
        "april",
        "may",
        "june",
        "july",
        "august",
        "september",
        "october",
        "november",
        "december");

    /**
     * Доступные периоды
     * @var array $periods
     */
    protected static $periods = array(
        1 => "hour",
        6 => "six_hours",
        12 => "twelve_hours",
        24 => "day",
        168 => "week",
        720 => "month",
        0 => "unended");

    /**
     * Поле Select для выбора GMT
     * @param string $name имя для поля Select
     * @param float $current данный часовой пояс
     * @return string HTML код выборки
     */
    public function select_gmt($name = "timezone", $current = null) {
        if (is_array($name)) {
            $current = $name ['current'];
            $name = $name ['name'];
        }
        lang::o()->get('timezones');
        $name = ($name ? $name : "timezone");
        $select = "<select name=\"" . $name . "\">";
        $half = array(
            - 3,
            3,
            5,
            9);
        $gmt = date("O");
        $gmt = substr($gmt, 0, 1) . (substr($gmt, 1, 1) == "0" ? substr($gmt, 2, 1) : substr($gmt, 1, 2)) . ":" . substr($gmt, 3, 2);
        for ($i = - 12; $i <= 12; $i += 0.5) {
            if ($i != longval($i) && !in_array(longval($i), $half))
                continue;
            $ii = ($i > 0 ? "+" . longval($i) : ($i != 0 ? "-" : "") . abs(longval($i))) . ":" . (abs($i - longval($i)) == 0.5 ? "30" : "00");
            $select .= "<option value=\"" . $i . "\"" . (($gmt == $ii && is_null($current)) || (!is_null($current) && $current == $i) ? " selected='selected'" : "") . ">(GMT " . $ii . ")&nbsp;" . lang::o()->v('timesone_gmt_' . $ii) . "</option>\n";
        }
        $select .= "</select>";
        return $select;
    }

    /**
     * Форма ввода даты
     * @param string $name добавочное имя к select
     * @param string $type тип ввода даты(y - год, m - месяц, d - день, h - час, i - минута, s - секунда), пример:
     * ymd - select года, месяца и дня
     * @param int $time предустановленное время(в формате UNIXTIME)
     * @param int $fromnull начинаем с 0
     * @return string HTML код выборки
     */
    public function select_date($name = "date", $type = "ymd", $time = null, $fromnull = false) {
        if (is_array($name)) {
            $type = $name ["type"];
            $time = $name ["time"];
            $fromnull = $name ["fromnull"];
            $name = $name ["name"];
        }
        $type = ($type ? $type : "ymd");
        $name = ($name ? $name : "date");
        $time = ($time ? $time : null);
        $type = strtolower($type);
        $beyear = strpos($type, "y") !== false;
        $bemonth = strpos($type, "m") !== false;
        $beday = strpos($type, "d") !== false;
        $behour = strpos($type, "h") !== false;
        $beminute = strpos($type, "i") !== false;
        $besecond = strpos($type, "s") !== false;
        $ttime = $tdate = array();
        $now = - 1;
        $text = "";
        if ($beday) {
            if ($time > 0)
                $now = date("d", $time);
            $text .= "<select name=\"" . $name . "_day\">";
            if ($fromnull)
                $text .= "<option value=\"0\"" . ($now == 0 ? " selected=\"selected\"" : "") . ">--</option>";
            for ($i = 1; $i <= 31; $i++) {
                if (strlen($i) < 2)
                    $i = "0" . $i;
                $text .= "<option value=\"" . $i . "\"" . ($now == $i ? " selected=\"selected\"" : "") . ">" . $i . "</option>";
            }
            $text .= "</select>";
            $tdate[] = $text;
        }
        $text = "";
        if ($bemonth) {
            if ($time > 0)
                $now = date("m", $time);
            $text .= "<select name=\"" . $name . "_month\">";
            if ($fromnull)
                $text .= "<option value=\"0\"" . ($now == 0 ? " selected=\"selected\"" : "") . ">----</option>";
            for ($i = 1; $i <= 12; $i++) {
                $text .= "<option value=\"" . $i . "\"" . ($now == $i ? " selected=\"selected\"" : "") . ">" . lang::o()->v('month_' . self::$months [$i - 1]) . "</option>";
            }
            $text .= "</select>";
            $tdate[] = $text;
        }
        $text = "";
        if ($beyear) {
            if ($time > 0)
                $now = date("Y", $time);
            $text .= "<input name=\"" . $name . "_year\" type=\"text\" size=\"4\" maxlength=\"4\" value=\"" . ($now > 0 ? $now : "") . "\">";
            $tdate[] = $text;
        }
        $text = "";
        if ($behour) {
            if ($time > 0)
                $now = date("h", $time);
            $text .= "<select name=\"" . $name . "_hour\">";
            if ($fromnull)
                $text .= "<option value=\"-1\"" . ($now == 0 ? " selected=\"selected\"" : "") . ">--</option>";
            for ($i = 0; $i <= 23; $i++) {
                if (strlen($i) < 2)
                    $i = "0" . $i;
                $text .= "<option value=\"" . $i . "\"" . ($now == $i ? " selected=\"selected\"" : "") . ">" . $i . "</option>";
            }
            $text .= "</select>";
            $ttime[] = $text;
        }
        $text = "";
        if ($beminute) {
            if ($time > 0)
                $now = date("i", $time);
            $text .= "<select name=\"" . $name . "_minute\">";
            if ($fromnull)
                $text .= "<option value=\"-1\"" . ($now == 0 ? " selected=\"selected\"" : "") . ">--</option>";
            for ($i = 0; $i <= 59; $i++) {
                if (strlen($i) < 2)
                    $i = "0" . $i;
                $text .= "<option value=\"" . $i . "\"" . ($now == $i ? " selected=\"selected\"" : "") . ">" . $i . "</option>";
            }
            $text .= "</select>";
            $ttime[] = $text;
        }
        $text = "";
        if ($besecond) {
            if ($time > 0)
                $now = date("s", $time);
            $text .= "<select name=\"" . $name . "_second\">";
            if ($fromnull)
                $text .= "<option value=\"-1\"" . ($now == 0 ? " selected=\"selected\"" : "") . ">--</option>";
            for ($i = 0; $i <= 59; $i++) {
                if (strlen($i) < 2)
                    $i = "0" . $i;
                $text .= "<option value=\"" . $i . "\"" . ($now == $i ? " selected=\"selected\"" : "") . ">" . $i . "</option>";
            }
            $text .= "</select>";
            $ttime[] = $text;
        }
        $text = implode("&nbsp;-&nbsp;", $tdate) .
                ($ttime ? "<div class='br'>" . implode("&nbsp;:&nbsp;", $ttime) . "</div>" : "");
        return $text;
    }

    /**
     * Функция выборки категорий
     * @param string $name имя поля
     * @param int $size размер поля выборки
     * @param int $current выбранная категория
     * @param bool $not_null без нулевого элемента?
     * @param string $nbsp префикс категорий(необходимо для рекурсии)
     * @param array $carr массив категорий(необходимо для рекурсии)
     * @return string HTML-код выборки
     */
    public function select_categories($name = "categories", $size = 5, $current = null, $not_null = false, $nbsp = null, $carr = null) {
        if (is_array($name)) {
            $current = $name ['current'];
            $size = $name ['size'];
            $not_null = $name ["not_null"];
            $name = $name ['name'];
            $nbsp = null;
            $carr = null;
        }
        if (!$name)
            $name = "categories";
        if (!longval($size))
            $size = 5;
        /* @var $cats categories */
        $cats = n("categories");
        if (!is_array($carr) || !$carr) {
            $carr = $cats->get(null, 't');
            $select = "<select name='" . $name . "" . ($size == 1 ? "" : "[]' multiple='multiple") . "' 
                " . (!$not_null ? " onclick='clear_select(this)'" : "") . "
                size='" . longval($size) . "'>";
            $first_run = true;
            $nbsp = "";
            if (!$not_null)
                $select .= empty_option;
        }
        $count = count($carr);
        for ($i = 0; $i < $count; $i++) {
            $id = $carr [$i] ["id"];
            $select .= "<option value='" . $id . "'
                " . ($current == $id ? " selected='selected'" : '') . ">" . $nbsp . $carr [$i] ["name"] . "</option>";
            $a = $cats->get($id, 'c');
            if ($a)
                $select .= $this->select_categories("", "", "", "", $nbsp . "&nbsp;&nbsp;&nbsp;", $a);
        }
        if ($first_run)
            $select .= "</select>";
        return $select;
    }

    /**
     * Функция вывода списка стран\одной страны
     * @param array $country выводимая страна(не для списка)(вкл. в себя name и image)
     * @param string $name имя для списка стран
     * @param int $current ID данной страны
     * @return string HTML код выборки списка стран\данной страны
     */
    public function select_countries($country = '', $name = 'country', $current = 0) {
        $baseurl = globals::g('baseurl');
        if (is_array($country)) {
            $name = $country ['name'];
            $current = $country ['current'];
            $country = $country ['country'];
        }
        $name = ($name ? $name : 'country');
        $select = "";
        $show_flag = "show_flag_image('" . addslashes($baseurl . config::o()->v('countries_folder') . "/") . "',
			'#" . addslashes("country_" . $name) . "',
			'" . addslashes("flag_image_" . $name) . "');";
        $select .= "<script type=\"text/javascript\">
				jQuery(document).ready(function () {
					" . $show_flag . "
				});
				</script>";
        $res = db::o()->query('SELECT id, name, image FROM countries', 'countries');
        $select .= "<select name=\"" . $name . "\" id=\"country_" . $name . "\" onchange=\"" . $show_flag . "\">";
        $select .= empty_option;
        foreach ($res as $row) {
            $id = $row ['id'];
            $cname = $row ['name'];
            $image = $row ['image'];
            if ($current == $id)
                $this_i = $image;
            $select .= "<option value=\"" . $id . "\"
                rel=\"" . $image . "\"" . ($current == $id ? " selected=\"selected\"
                    id=\"cselected_" . $name . "\"" : "") . ">" . $cname . "</option>";
        }
        $select .= "</select>";
        $select = "<span id=\"flag_image_" . $name . "\" style=\"display:none;\">
            <img src=\"" . $baseurl . config::o()->v('countries_folder') . "/" . ($this_i ? $this_i : $image) . "\"
                height=\"20\" alt=\"\" align=\"left\"></span>" . $select;
        return $select;
    }

    /**
     * Выборка дирректорий
     * @param string $name имя селектора
     * @param string $folder дирректория
     * @param string $current данный язык
     * @param bool $onlydir искать только дирректории?
     * @param bool $empty включить пустой селектор?
     * @param string $regexp рег. выражение для выборки файлов(с делимиттерами)
     * @param int $match значение из рег. выражения
     * @return string HTML код селектора
     */
    public function select_folder($name = "lang", $folder = LANGUAGES_PATH, $current = '', $onlydir = false, $empty = false, $regexp = '', $match = '') {
        if (is_array($name)) {
            $current = $name ["current"];
            $folder = $name ["folder"];
            $empty = $name ["empty"];
            if (!is_null($name ["onlydir"]))
                $onlydir = $name ["onlydir"];
            $regexp = $name ["regexp"];
            $match = $name ["match"];
            $name = $name ["name"];
        }
        if (!$name)
            $name = "lang";
        if (!$folder)
            $folder = LANGUAGES_PATH;
        if ($folder == LANGUAGES_PATH || $folder == THEMES_PATH)
            $onlydir = true;
        $res = file::o()->open_folder($folder, $onlydir);
        $count = count($res);
        $options = "";
        for ($i = 0; $i < $count; $i++) {
            $cur = $res [$i];
            $value = $cur;
            $matches = array();
            if ($folder == LANGUAGES_PATH && lang::o()->visset("lang_" . $cur))
                $value = lang::o()->v("lang_" . $cur);
            if ($regexp && !preg_match($regexp, $cur, $matches))
                continue;
            if ($matches && $match)
                $value = $cur = $matches[$match];
            $options .= "<option value='" . $cur . "'" .
                    ($cur == $current ? " selected='selected'" : "") . ">" . $value
                    . "</option>";
        }
        if (!$options)
            return;
        if ($empty)
            $options = empty_option . $options;
        $html = "<select name=\"" . $name . "\">" . $options . "</select>";
        return $html;
    }

    /**
     * Функция выборки групп
     * @param string $name имя поля
     * @param int $current ID группы, выбранной по-умолчанию
     * @param bool $guest в т.ч. и гость
     * @param bool $not_null убрать опцию "для всех групп"
     * @param bool $multiple выбор нескольких групп
     * @return string HTML код выборки
     */
    public function select_groups($name = "group", $current = null, $guest = false, $not_null = false, $multiple = false) {
        if (is_array($name)) {
            $current = $name ["current"];
            $guest = $name ["guest"];
            $not_null = $name ["not_null"];
            $multiple = $name ["multi"];
            $name = $name ["name"];
        }
        if (!$name)
            $name = "group";
        $sel = "<select name='" . $name . ($multiple ? "[]' size='4' multiple='multiple'
            " . (!$not_null ? " onclick='clear_select(this)" : "") : "") . "'>";
        $id = 0;
        if (!$not_null)
            $sel .= empty_option;
        foreach (users::o()->get_group() as $id => $group)
            if ($guest || (!$guest && !$group ['guest'])) {
                $s = ((!is_array($current) ? $current == $id : in_array($id, $current)) ? " selected='selected'" : "");
                $sel .= "<option value='" . $id . "'" .
                        $s . ">" .
                        users::o()->get_group_name($id) . "</option>";
            }
        $sel .= "</select>";
        return $sel;
    }

    /**
     * Выборка интервалов подписок
     * @param string $name имя селектора
     * @param int $current данная подписка
     * @return string HTML код селектора
     */
    public function select_mailer($name = "interval", $current = '') {
        lang::o()->get('usercp');
        if (is_array($name)) {
            $current = $name ["current"];
            $name = $name ["name"];
        }
        if (!$name)
            $name = "interval";
        $arr = mailer::$allowed_interval;
        foreach ($arr as $i => $lv)
            $arr[$i] = lang::o()->v('usercp_mailer_interval_every_' . $lv);
        $html = $this->simple_selector($name, $arr, true, $current);
        return $html;
    }

    /**
     * Функция выборки периодов, прежде всего, для банов
     * @param string $name имя поля выборки
     * @param string $current данный период
     * @return string HTML код селектора
     */
    public function select_periods($name = "period", $current = null) {
        if (is_array($name)) {
            if (isset($name["current"]))
                $current = $name ['current'];
            $name = $name ['name'];
        }
        if (!$name)
            $name = 'period';
        $c = $name . time();
        $sel = "<select name='sel_" . $name . "' onchange='period_selector(this);'>";
        $selected = !is_null($current) ? false : null;
        foreach (self::$periods as $time => $period) {
            if ((is_null($selected) || $current == $time) && !$selected) {
                $current = $time;
                $s = " selected='selected'";
                $selected = true;
            } else
                $s = "";
            $sel .= "<option value='" . $time . "'" . $s . ">" . lang::o()->v("period_" . $period) . "</option>";
        }
        $sel .= "<option value='-1'>" . lang::o()->v('period_other') . "</option>";
        $sel .= "</select>";
        $sel .= "<div" . ($selected ? " class='hidden'" : "") . "><input type='text' name='" . $name . "' value='" . $current . "'><br>
            <font size='1'>(" . lang::o()->v('period_notice_in_hours') . ")</font></div>";
        return $sel;
    }

    /**
     * Простой селектор
     * @param string $name имя поля
     * @param array $values массив значений
     * @param bool $keyed ключи в кач. значений опций?
     * @param mixed $current данное значение
     * @param int $size размер поля
     * @param bool $empty пустое значение?
     * @return string HTML код
     */
    public function simple_selector($name, $values, $keyed = false, $current = null, $size = 1, $empty = false) {
        if (is_array($name)) {
            $values = $name ['values'];
            $current = $name ['current'];
            $size = $name ['size'];
            $keyed = $name ['keyed'];
            $empty = $name ['empty'];
            $name = $name ['name'];
        }
        if (!$values || !is_array($values))
            return;
        $html = "<select name='" . $name . ($size > 1 ? '[]\' size="' . $size . '" 
            ' . ($empty ? 'onclick="clear_select(this)"' : '') . ' multiple="multiple"' : "") . "'>";
        if ($empty)
            $html .= empty_option;
        foreach ($values as $k => $v) {
            $k = ($keyed ? $k : $v);
            $s = '';
            if (is_array($current) ? in_array($k, $current) : $current == $k)
                $s = ' selected="selected"';
            $html .= '<option value="' . $k . '"' . $s . '>' . $v . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    // Реализация Singleton для переопределяемого класса

    /**
     * Объект данного класса
     * @var input $o
     */
    protected static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    protected function __construct() {
        
    }

    /**
     * Не клонируем
     * @return null 
     */
    protected function __clone() {
        
    }

    /**
     * И не десериализуем
     * @return null 
     */
    protected function __wakeup() {
        
    }

    /**
     * Получение объекта класса
     * @return input $this
     */
    public static function o() {
        if (!self::$o) {
            $cn = __CLASS__;
            $c = n($cn, true);
            self::$o = new $c();
        }
        return self::$o;
    }

}

?>