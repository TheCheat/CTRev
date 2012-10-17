<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/config.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление конфигурацией сайта
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class config_man extends pluginable_object {
    /**
     * Префикс полей конфига
     */

    const config_prefix = 'config_';

    /**
     * Конструктор класса
     * @return null
     */
    protected function plugin_construct() {
        
    }

    /**
     * Инициализация модуля банов
     * @return null
     */
    public function init() {
        lang::o()->get('admin/config');
        $type = $_GET["type"];
        if ($type) {
            $cats = db::o()->query('SELECT cat FROM config GROUP BY cat');
            $prev = null;
            while (list($cat) = db::o()->fetch_row($cats)) {
                if ($cat == $type)
                    break;
                $prev = $cat;
            }
            list($next) = db::o()->fetch_row($cats);
            $r = db::o()->query('SELECT * FROM config' . ($type ? ' WHERE cat=' . db::o()->esc($type) : ""));
            $r = db::o()->fetch2array($r);
            if (!$r)
                die('No way');
            tpl::o()->assign('rows', $r);
            tpl::o()->assign('ptype', $prev);
            tpl::o()->assign('ntype', $next);
            tpl::o()->register_modifier('show_ctype', array($this, 'show_type'));
            tpl::o()->display('admin/config/main.tpl');
        } else {
            $r = db::o()->query('SELECT cat, COUNT(*) AS c FROM config GROUP BY cat');
            tpl::o()->assign('rows', db::o()->fetch2array($r));
            tpl::o()->display('admin/config/cats.tpl');
        }
    }

    /**
     * Вывод поля конфигурации для данного типа
     * @param array $row массив параметров
     * @return string html код поля
     */
    public function show_type($row) {
        if (!is_array($row) || !$row)
            return null;
        $type = $row['type'];
        $allowed = $row['allowed'];
        $name = $row['name'];
        $value = display::o()->html_encode($row['value']);
        $oname = $name;
        $name = config_man::config_prefix . $name;
        switch ($type) {
            case "int":
            case "string":
                return "<input type='text' name='" . $name . "' value='" . $value . "' size='35'>";
            case "text":
                return "<textarea rows='4' name='" . $name . "' cols='35'>" . $value . "</textarea>";
            case "date":
                return "<input type='hidden' value='1' name='" . $name . "'>" . input::o()->select_date($name, $allowed, $value);
            case "folder":
                return input::o()->select_folder($name, $allowed, $value);
            case "radio":
            case "select":
                $a = explode(";", $allowed);
                rsort($a);
                $c = count($a);
                if ($type == 'select')
                    $html .= '<select name="' . $name . '">';
                for ($i = 0; $i < $c; $i++) {
                    if (lang::o()->visset("config_field_" . $oname . "_" . $a[$i]))
                        $l = lang::o()->v("config_field_" . $oname . "_" . $a[$i]);
                    else
                        $l = lang::o()->v("config_value_" . $a[$i]);
                    $s = ($a[$i] == $value ? ($type == 'select' ? " selected='selected'" : " checked='checked'") : "");
                    if ($type == 'select')
                        $html .= "<option value='" . $a[$i] . "'" . $s . ">" . $l . "</option>";
                    else
                        $html .= "<input type='radio' name='" . $name . "' value='" . $a[$i] . "'" . $s . ">&nbsp;" . $l . " ";
                }
                if ($type == 'select')
                    $html .= '</select>';
                return $html;
            case "checkbox":
                $v = mb_substr($allowed, 0, mb_strpos($allowed, ";"));
                return "<input type='checkbox' name='" . $name . "'
                    value='" . $v . "'" . ($v == $value ? " checked='checked'" : "") . ">";
            case "other":
                $f = "show_field_" . $oname;
                return $this->call_method($f, $value);
        }
    }

    /**
     * Проверка переменной типа other
     * @param string $name имя переменной
     * @param string $value значение
     * @return bool true, если верное значение
     */
    public function check_field($name, $value) {
        $f = "check_field_" . $name;
        return (bool) $this->call_method($f, $value);
    }

}

class config_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $POST = globals::g('POST');
        lang::o()->get('admin/config');
        $cprefix = config_man::config_prefix;
        $cprefix_length = strlen($cprefix);
        $keys = "";
        $newcfg = array();
        $i = 0;
        $sort = array();
        foreach ($POST as $key => $value) {
            if (strpos($key, $cprefix) !== 0)
                continue;
            $key = substr($key, $cprefix_length);
            if (!config::o()->visset($key))
                continue;
            $keys .= ($keys ? ", " : "") . db::o()->esc($key);
            $newcfg[$key] = $value;
            $sort[$key] = ++$i;
        }
        if (!$keys)
            return;
        $r = db::o()->query('SELECT name,type,allowed FROM config WHERE name IN(' . $keys . ')');
        $c = 0;
        while (list($name, $type, $allowed) = db::o()->fetch_row($r)) {
            if (!$this->check_type($newcfg[$name], $type, $allowed, $name))
                continue;
            $c++;
            config::o()->set($name, $newcfg[$name], $sort[$name]);
        }
        db::o()->query('ALTER TABLE `config` ORDER BY `cat`, `sort`');
        log_add('changed_config', 'admin');
        print($c);
        die();
    }

    /**
     * Проверка значения конфигурации
     * @param mixed $value значение параметра
     * @param string $type тип параметра
     * @param string $allowed допустимые значения
     * @param string $name имя параметра
     * @return bool true, в случае успешного завершения
     */
    protected function check_type(&$value, $type = null, $allowed = null, $name = null) {
        switch ($type) {
            case "int":
                return is_numeric($value);
            case "text":
            case "string":
                return is_string($value);
            case "date":
                $value = display::o()->make_time(config_man::config_prefix . $name, $allowed);
                return true;
            case "folder":
                return validfolder($value, $allowed);
            case "radio":
            case "select":
                $allowed = explode(";", $allowed);
                return in_array($value, $allowed);
            case "checkbox":
                $value = (bool) $value;
                return true;
            case "other":
                /* @var $o cofig_man */
                $o = plugins::o()->get_module('config', 1);
                return (bool) $o->check_field($name, $value);
        }
    }

}

?>