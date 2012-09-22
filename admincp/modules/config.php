<?php

/**
 * Project:            	CTRev
 * File:                config.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @const string config_prefix
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
     * @global lang $lang
     * @global db $db
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $db, $tpl;
        $lang->get('admin/config');
        $type = $_GET["type"];
        if ($type) {
            $cats = $db->query('SELECT cat FROM config GROUP BY cat');
            $prev = null;
            while (list($cat) = $db->fetch_row($cats)) {
                if ($cat == $type)
                    break;
                $prev = $cat;
            }
            list($next) = $db->fetch_row($cats);
            $r = $db->query('SELECT * FROM config' . ($type ? ' WHERE cat=' . $db->esc($type) : ""));
            $r = $db->fetch2array($r);
            if (!$r)
                die('No way');
            $tpl->assign('rows', $r);
            $tpl->assign('ptype', $prev);
            $tpl->assign('ntype', $next);
            $tpl->register_modifier('show_ctype', array($this, 'show_type'));
            $tpl->display('admin/config/main.tpl');
        } else {
            $r = $db->query('SELECT cat, COUNT(*) AS c FROM config GROUP BY cat');
            $tpl->assign('rows', $db->fetch2array($r));
            $tpl->display('admin/config/cats.tpl');
        }
    }

    /**
     * Вывод поля конфигурации для данного типа
     * @global input $input
     * @global lang $lang
     * @global display $display
     * @param array $row массив параметров
     * @return string html код поля
     */
    public function show_type($row) {
        global $lang, $input, $display;
        if (!is_array($row) || !$row)
            return null;
        $type = $row['type'];
        $allowed = $row['allowed'];
        $name = $row['name'];
        $value = $display->html_encode($row['value']);
        $oname = $name;
        $name = config_man::config_prefix . $name;
        switch ($type) {
            case "int":
            case "string":
                return "<input type='text' name='" . $name . "' value='" . $value . "' size='35'>";
            case "text":
                return "<textarea rows='4' name='" . $name . "' cols='35'>" . $value . "</textarea>";
            case "date":
                return "<input type='hidden' value='1' name='" . $name . "'>" . $input->select_date($name, $allowed, $value);
            case "folder":
                return $input->select_folder($name, $allowed, $value);
            case "radio":
            case "select":
                $a = explode(";", $allowed);
                rsort($a);
                $c = count($a);
                if ($type == 'select')
                    $html .= '<select name="' . $name . '">';
                for ($i = 0; $i < $c; $i++) {
                    if ($lang->visset("config_field_" . $oname . "_" . $a[$i]))
                        $l = $lang->v("config_field_" . $oname . "_" . $a[$i]);
                    else
                        $l = $lang->v("config_value_" . $a[$i]);
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
     * @global lang $lang
     * @global array $POST
     * @global config $config
     * @global db $db
     * @return null
     */
    public function init() {
        global $lang, $POST, $config, $db;
        $lang->get('admin/config');
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
            if (!$config->visset($key))
                continue;
            $keys .= ($keys ? ", " : "") . $db->esc($key);
            $newcfg[$key] = $value;
            $sort[$key] = ++$i;
        }
        if (!$keys)
            return;
        $r = $db->query('SELECT name,type,allowed FROM config WHERE name IN(' . $keys . ')');
        $c = 0;
        while (list($name, $type, $allowed) = $db->fetch_row($r)) {
            if (!$this->check_type($newcfg[$name], $type, $allowed, $name))
                continue;
            $c++;
            $config->set($name, $newcfg[$name], $sort[$name]);
        }
        $db->query('ALTER TABLE `config` ORDER BY `cat`, `sort`');
        log_add('changed_config', 'admin');
        print($c);
        die();
    }

    /**
     * Проверка значения конфигурации
     * @global display $display
     * @global plugins $plugins
     * @param mixed $value значение параметра
     * @param string $type тип параметра
     * @param string $allowed допустимые значения
     * @param string $name имя параметра
     * @return bool true, в случае успешного завершения
     */
    protected function check_type(&$value, $type = null, $allowed = null, $name = null) {
        global $display, $plugins;
        switch ($type) {
            case "int":
                return is_numeric($value);
            case "text":
            case "string":
                return is_string($value);
            case "date":
                $value = $display->make_time(config_man::config_prefix . $name, $allowed);
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
                $o = $plugins->get_module('config', 1);
                return (bool) $o->check_field($name, $value);
        }
    }

}

?>