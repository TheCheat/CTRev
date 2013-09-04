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
    protected $periods = array(
        1 => "hour",
        6 => "six_hours",
        12 => "twelve_hours",
        24 => "day",
        168 => "week",
        720 => "month",
        0 => "unended");

    /**
     * Типы обратной связи
     * @var array $feedback_types
     */
    public $feedback_types = array(
        "error",
        "suggest",
        "main");

    /**
     * Общие параметры функций
     * @var array $params
     */
    protected $params = array();

    /**
     * Текст для пустой опции
     * @var string $null_text
     */
    protected $null_text = '';

    /**
     * Установка текста для пустой опции
     * @param string $value строка
     * @return input $this
     */
    public function set_null_text($value) {
        $this->null_text = $value;
        return $this;
    }

    /**
     * Получение текста для пустой опции
     * @return string HTML код пустой опции
     */
    protected function get_null_text() {
        if (!$this->null_text)
            return empty_option;
        $r = "<option value='0'>" . lang::o()->if_exists($this->null_text) . '</option>';
        $this->null_text = "";
        return $r;
    }

    /**
     * Установка параметра текущего
     * @param mixed $value значение
     * @return input $this
     */
    public function scurrent($value) {
        return $this->sparam('current', $value);
    }

    /**
     * Установка параметра типа ч/либо
     * @param mixed $value значение
     * @return input $this
     */
    public function stype($value) {
        return $this->sparam('type', $value);
    }

    /**
     * Установка параметра размера поля
     * Обычно, если size > 1, то считается как multiple
     * @param int $value значение
     * @return input $this
     */
    public function ssize($value) {
        return $this->sparam('size', $value);
    }

    /**
     * Установка параметра значения с ключами и префикса языка
     * @param mixed $value true - ключи в кач. значений опций, если строка, то языковой префикс
     * @return input $this
     */
    public function skeyed($value = true) {
        return $this->sparam('keyed', $value);
    }

    /**
     * С нулевым элементом?
     * @param bool $value значение
     * @return input $this
     */
    public function snull($value = true) {
        return $this->sparam('null', (bool) $value);
    }

    /**
     * Выборка типа "radio"?
     * @param bool $value значение
     * @return input $this
     */
    public function sradio($value = true) {
        return $this->sparam('radio', (bool) $value);
    }

    /**
     * Установка общих параметров функции
     * @param string $key имя параметра
     * @param mixed $value значение
     * @return input $this
     */
    protected function sparam($key, $value) {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Объединение и сброс параметров
     * @param string $name имя
     * @param array $add доп. параметры
     * @return null
     */
    protected function join_params(&$name, $add = array()) {
        $name = array_merge($this->params, array('name' => $name), $add);
        $this->params = array();
        return $this;
    }

    /**
     * Селектор типа обратной связи
     * Параметры: current, null
     * @return string HTML код селектора
     */
    public function select_feedback($name = 'type') {
        if (!is_array($name))
            $this->join_params($name);
        $null = $name ['null'];
        $current = $name ['current'];
        $name = $name ['name'];
        if (!$name)
            $name = "type";
        return input::o()->skeyed("feedback_type_")->set_null_text('all')->snull($null)->scurrent($current)->simple_selector($name, $this->feedback_types);
    }

    /**
     * Поле Select для выбора GMT
     * Параметры: current
     * @param string $name имя для поля Select
     * @return string HTML код выборки
     */
    public function select_gmt($name = "timezone") {
        if (!is_array($name))
            $this->join_params($name);
        $current = $name ['current'];
        $name = $name ['name'];
        lang::o()->get('timezones');
        $name = ($name ? $name : "timezone");
        $select = "<select name=\"" . $name . "\">";
        $half = array(
            - 3,
            3,
            5,
            9);
        if (is_null($current)) {
            $gmt = date("O");
            $gmt = substr($gmt, 0, 1) . (substr($gmt, 1, 1) == "0" ? substr($gmt, 2, 1) : substr($gmt, 1, 2)) . ":" . substr($gmt, 3, 2);
            $current = $gmt;
        }
        for ($i = - 12; $i <= 12; $i += 0.5) {
            if ($i != longval($i) && !in_array(longval($i), $half))
                continue;
            $ii = ($i > 0 ? "+" . longval($i) : ($i != 0 ? "-" : "") . abs(longval($i))) . ":" . (abs($i - longval($i)) == 0.5 ? "30" : "00");
            $select .= "<option value=\"" . $i . "\"" . (($current == $i) ? " selected='selected'" : "") . ">(GMT " . $ii . ")&nbsp;" . lang::o()->v('timesone_gmt_' . $ii) . "</option>\n";
        }
        $select .= "</select>";
        return $select;
    }

    /**
     * Генерация массива значений для выборки даты
     * @param int $from от
     * @param int $to до
     * @param bool $months месяца?
     * @return array массив значений
     */
    protected function date_values($from = 1, $to = 59, $months = false) {
        $a = array();
        for ($i = $from; $i <= $to; $i++) {
            $v = $months ? lang::o()->v('month_' . self::$months [$i - 1]) : $i;
            if ($i == -1 || ($to != 23 && $to != 59 && $i == 0))
                $v = "--";
            $a[$i] = $v;
        }
        return $a;
    }

    /**
     * Форма ввода даты
     * Параметры: current, type, null
     * type тип ввода даты(y - год, m - месяц, d - день, h - час, i - минута, s - секунда), пример:
     * ymd - select года, месяца и дня
     * @param string $name добавочное имя к select
     * @return string HTML код выборки
     */
    public function select_date($name = "date") {
        if (!is_array($name))
            $this->join_params($name);
        $type = $name ["type"];
        $time = $name ["current"];
        $fromnull = $name ["null"];
        $name = $name ["name"];
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
        if ($beday) {
            $values = $this->date_values(($fromnull ? 0 : 1), 31);
            $tdate[] = $this->scurrent((int) date("d", $time))->skeyed()->
                    simple_selector($name . "_day", $values);
        }
        if ($bemonth) {
            $values = $this->date_values(($fromnull ? 0 : 1), 12, true);
            $tdate[] = $this->scurrent((int) date("m", $time))->skeyed()->
                    simple_selector($name . "_month", $values);
        }
        if ($beyear) {
            $text = "";
            if ($time > 0)
                $now = date("Y", $time);
            $text .= "<input name=\"" . $name . "_year\" type=\"text\" size=\"4\" maxlength=\"4\" value=\"" . ($now > 0 ? $now : "") . "\">";
            $tdate[] = $text;
        }
        if ($behour) {
            $values = $this->date_values(($fromnull ? -1 : 0), 23);
            $ttime[] = $this->scurrent((int) date("h", $time))->skeyed()->
                    simple_selector($name . "_hour", $values);
        }
        $values = $this->date_values(($fromnull ? -1 : 0), 59);
        if ($beminute)
            $ttime[] = $this->scurrent((int) date("i", $time))->skeyed()->
                    simple_selector($name . "_minute", $values);
        if ($besecond)
            $ttime[] = $this->scurrent((int) date("s", $time))->skeyed()->
                    simple_selector($name . "_second", $values);
        $text = implode("&nbsp;-&nbsp;", $tdate) .
                ($ttime ? "<div class='br'>" . implode("&nbsp;:&nbsp;", $ttime) . "</div>" : "");
        return $text;
    }

    /**
     * Функция выборки категорий
     * Параметры: current, size, null, type
     * @param string $name имя поля
     * @param string $nbsp префикс категорий(необходимо для рекурсии)
     * @param array $carr массив категорий(необходимо для рекурсии)
     * @return string HTML-код выборки
     */
    public function select_categories($name = "categories", $nbsp = null, $carr = null) {
        if (!is_array($name))
            $this->join_params($name);
        else {
            $nbsp = null;
            $carr = null;
        }
        $current = $name ['current'];
        $size = $name ['size'];
        $null = $name ["null"];
        $type = $name['type'];
        $name = $name ['name'];
        if (!$name)
            $name = "categories";
        if (!longval($size))
            $size = 5;
        /* @var $cats categories */
        $cats = n("categories");
        $cats->change_type($type);
        if (!is_array($carr) || !$carr) {
            $carr = $cats->get(null, 't');
            $select = "<select name='" . $name . "" . ($size == 1 ? "" : "[]'
multiple='multiple") . "' 
                " . ($null ? " onclick='clear_select(this)'" : "") . "
                size='" . longval($size) . "'>";
            $first_run = true;
            $nbsp = "";
            if ($null)
                $select .= $this->get_null_text();
        }
        $count = count($carr);
        for ($i = 0; $i < $count; $i++) {
            $id = $carr [$i] ["id"];
            $select .= "<option value='" . $id . "'
                " . ($current == $id ? " selected='selected'" : '') . ">" .
                    $nbsp . $carr [$i] ["name"] . "</option>";
            $a = $cats->get($id, 'c');
            if ($a)
                $select .= $this->stype($type)->select_categories($name, $nbsp . "&nbsp;&nbsp;&nbsp;", $a);
        }
        if ($first_run)
            $select .= "</select>";
        return $select;
    }

    /**
     * Получение массива категорий
     * @return array массив категорий, где ключ - ID
     */
    protected function get_countries() {
        return db::o()->cname('countries')->ckeys('id')->query('SELECT id, name, image FROM countries');
    }

    /**
     * Вывод иконки страны
     * @param int $id ID страны
     * @return HTML код изображения
     */
    public function get_country($id) {
        $res = $this->get_countries();
        $name = $res[$id]['name'];
        $image = $res[$id]['image'];
        return '<img src="' . config::o()->v('countries_folder') . '/' . $image . '"
                                     alt="' . $name . '" title="' . $name . '">';
    }

    /**
     * Функция вывода списка стран
     * Параметры: current, null
     * @param string $name имя для списка стран
     * @return string HTML код выборки списка стран
     */
    public function select_countries($name = 'country') {
        if (!is_array($name))
            $this->join_params($name);
        $country = $name ['country'];
        $current = $name ['current'];
        $null = $name['null'];
        $name = $name ['name'];
        $name = ($name ? $name : 'country');
        $select = "";
        $show_flag = "show_flag_image('" . addslashes(config::o()->v('countries_folder') . "/") . "',
			'#" . addslashes("country_" . $name) . "',
			'" . addslashes("flag_image_" . $name) . "');";
        $select .= "<script type=\"text/javascript\">
				jQuery(document).ready(function () {
					" . $show_flag . "
				});
				</script>";
        $res = $this->get_countries();
        $select .= "<select name=\"" . $name . "\" id=\"country_" . $name . "\" onchange=\"" . $show_flag . "\">";
        if ($null)
            $select .= $this->get_null_text();
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
            <img src=\"" . config::o()->v('countries_folder') . "/" . ($this_i ? $this_i : $image) . "\"
                height=\"20\" alt=\"\" align=\"left\"></span>" . $select;
        return $select;
    }

    /**
     * Выборка дирректорий
     * Параметры: current, null
     * @param string $name имя селектора
     * @param string $folder дирректория
     * @param bool $onlydir искать только дирректории?
     * @param string $regexp рег. выражение для выборки файлов(с делимиттерами)
     * @param int $match значение из рег. выражения
     * @return string HTML код селектора
     */
    public function select_folder($name = "theme", $folder = THEMES_PATH, $onlydir = false, $regexp = '', $match = '') {
        if (!is_array($name))
            $this->join_params($name, array('folder' => $folder,
                'onlydir' => $onlydir,
                'regexp' => $regexp,
                'match' => $match));
        $current = $name ["current"];
        $folder = $name ["folder"];
        $null = $name ["null"];
        if (!is_null($name ["onlydir"]))
            $onlydir = $name ["onlydir"];
        $regexp = $name ["regexp"];
        $match = $name ["match"];
        $name = $name ["name"];
        if (!$name)
            $name = "lang";
        if (!$folder)
            $folder = THEMES_PATH;
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
            if ($folder == THEMES_PATH && strtolower($value) == strtolower(ADMIN_THEME))
                continue;
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
        if ($null)
            $options = $this->get_null_text() . $options;
        $html = "<select name=\"" . $name . "\">" . $options . "</select>";
        return $html;
    }

    /**
     * Функция выборки групп
     * Параметры: current, null, size
     * @param string $name имя поля
     * @param bool $guest в т.ч. и гость
     * @return string HTML код выборки
     */
    public function select_groups($name = "group", $guest = false) {
        if (!is_array($name))
            $this->join_params($name, array('guest' => $guest));
        $current = $name ["current"];
        $guest = $name ["guest"];
        $null = $name ["null"];
        $size = $name ["size"];
        $name = $name ["name"];
        if (!$name)
            $name = "group";
        $sel = "<select name='" . $name . ($size > 1 ? "[]' size='" . $size . "' multiple='multiple'
            " . ($null ? " onclick='clear_select(this)" : "") : "") . "'>";
        $id = 0;
        if ($null)
            $sel .= $this->get_null_text();
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
     * Параметры: current
     * @param string $name имя селектора
     * @return string HTML код селектора
     */
    public function select_mailer($name = "interval") {
        lang::o()->get('usercp');
        if (!is_array($name))
            $this->join_params($name);
        $current = $name ["current"];
        $name = $name ["name"];
        if (!$name)
            $name = "interval";
        $arr = mailer::$allowed_interval;
        foreach ($arr as $i => $lv)
            $arr[$i] = lang::o()->v('usercp_mailer_interval_every_' . $lv);
        $html = $this->scurrent($current)->skeyed()->simple_selector($name, $arr);
        return $html;
    }

    /**
     * Функция выборки периодов, прежде всего, для банов
     * Параметры: current
     * @param string $name имя поля выборки
     * @return string HTML код селектора
     */
    public function select_periods($name = "period") {
        if (!is_array($name))
            $this->join_params($name);
        if (isset($name["current"]))
            $current = $name ['current'];
        $name = $name ['name'];
        if (!$name)
            $name = 'period';
        $c = $name . time();
        $sel = "<select name='sel_" . $name . "' onchange='period_selector(this);'>";
        $selected = !is_null($current) ? false : null;
        foreach ($this->periods as $time => $period) {
            if ((is_null($selected) || $current == $time) && !$selected) {
                $current = $time;
                $s = " selected='selected'";
                $selected = true;
            }
            else
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
     * Параметры: current, size, null, radio, keyed
     * @param string $name имя поля
     * @param array $values массив значений
     * @return string HTML код
     */
    public function simple_selector($name, $values) {
        if (!is_array($name))
            $this->join_params($name, array('values' => $values));
        $values = $name ['values'];
        $current = $name ['current'];
        $size = $name ['size'];
        $keyed = $name ['keyed'];
        $null = $name ['null'];
        $radio = $name['radio'];
        $name = $name ['name'];
        $lang_prefix = "";
        if (!is_numeric($keyed) && is_string($keyed)) {
            $lang_prefix = $keyed;
            $keyed = false;
        }
        if (!$values || !is_array($values))
            return;
        $html = "";
        if (!$radio)
            $html = "<select name='" . $name . ($size > 1 ? '[]\' size="' . $size . '" 
            ' . ($null ? 'onclick="clear_select(this)"' : '') . ' multiple="multiple"' : "") . "'>";
        if (!$radio && $null)
            $html .= $this->get_null_text();
        $c = count($values);
        foreach ($values as $k => $v) {
            $k = ($keyed ? $k : $v);
            if ($lang_prefix)
                $v = lang::o()->v($lang_prefix . $v);
            $s = '';
            if (is_array($current) ? in_array($k, $current) : $current == $k) {
                if (!$radio)
                    $s = ' selected="selected"';
                else
                    $s = ' checked="checked"';
            }
            if (!$radio)
                $html .= '<option value="' . $k . '"' . $s . '>' . $v . '</option>';
            else
                $html .= '<input type="radio" name="' . $name . '" value="' . $k . '"' . $s . '>&nbsp;' . $v .
                        ($c > 3 ? "<br>" : ' ');
        }
        if (!$radio)
            $html .= '</select>';
        return $html;
    }

    /**
     * Стандартные типы полей
     * Параметры type, current, size
     * @param string $name имя поля
     * @param mixed $allowed допустимые значения
     * @return null
     */
    public function standart_types($name, $allowed = null) {
        if (!is_array($name))
            $this->join_params($name, array('allowed' => $allowed));
        $type = $name['type'];
        $value = $name['current'];
        $size = $name['size'];
        $allowed = $name['allowed'];
        $keyed = $name['keyed'];
        $name = $name['name'];
        $radio = false;
        switch ($type) {
            case "int":
            case "string":
                return "<input type='text' name='" . $name . "' value='" . $value . "'" . ($size ? " size='" . $size . "'" : "") . ">";
            case "text":
                return "<textarea rows='4' name='" . $name . "' " . ($size ? " cols='" . $size . "'" : "") . ">" . $value . "</textarea>";
            case "date":
                return "<input type='hidden' value='1' name='" . $name . "'>" .
                        $this->stype($allowed)->scurrent($value)->select_date($name);
            case "folder":
                return $this->scurrent($value)->select_folder($name, $allowed);
            case "radio":
                $radio = true;
            case "select":
                if (!is_array($allowed))
                    $allowed = unserialize($allowed);
                return $this->scurrent($value)->sradio($radio)->ssize($size)->skeyed($keyed)->
                                simple_selector($name, $allowed);
            case "checkbox":
                return "<input type='checkbox' name='" . $name . "'
                    value='1'" . ($value ? " checked='checked'" : "") . ">";
        }
    }

    /**
     * Проверка стандартных типов
     * @param string $type имя типа
     * @param string $value значение
     * @param mixed $allowed допустимые значения
     * @param string $name имя поля
     * @param bool $keyed ключи в кач. значений опций?
     * @return bool всё верно?
     */
    public function standart_types_check($type, &$value, $allowed = null, $name = null, $keyed = false) {
        switch ($type) {
            case "int":
                return is_numeric($value);
            case "text":
            case "string":
                return is_string($value);
            case "date":
                $value = display::o()->make_time($name, $allowed);
                return true;
            case "folder":
                return validfolder($value, $allowed);
            case "radio":
            case "select":
                if (!is_array($allowed))
                    $allowed = unserialize($allowed);
                return $keyed ? isset($allowed[$value]) : in_array($value, (array) $allowed);
            case "checkbox":
                $value = (bool) $value;
                return true;
        }
        return false;
    }

    /**
     * Отображение значения в зависимости от типа поля
     * Параметры: type,current,keyed
     * @param array $allowed допустимые значения
     * @return mixed преобразованное значени
     */
    public function standart_types_display($allowed = null) {
        if ($this->params)
            $this->join_params($allowed, array('allowed' => $allowed));
        $type = $allowed['type'];
        $value = $allowed['current'];
        $keyed = $allowed['keyed'];
        $allowed = $allowed['allowed'];
        switch ($type) {
            case "int":
            case "text":
            case "string":
                return $value;
            case "date":
                return display::o()->date($value, $allowed);
            case "folder":
                return ($allowed ? $allowed . '/' : '') . $value;
            case "radio":
            case "select":
                if (!is_array($allowed))
                    $allowed = unserialize($allowed);
                return $keyed ? $allowed[$value] : $value;
            case "checkbox": // не отображаем
                return "";
        }
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