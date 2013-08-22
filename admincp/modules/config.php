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
     * Префикс для имени методов отображения
     */

    const method_show_prefix = 'show_field_';
    /**
     * Префикс для имени методов проверки
     */
    const method_check_prefix = 'check_field_';
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
        if ($type)
            $this->show_config($type);
        else
            $this->show_types();
    }

    /**
     * Отображение конфигураций типа
     * @param string $type имя тип
     * @return null
     */
    protected function show_config($type) {
        $cats = db::o()->query('SELECT cat FROM config GROUP BY cat');
        $prev = null;
        while (list($cat) = db::o()->fetch_row($cats)) {
            if ($cat == $type)
                break;
            $prev = $cat;
        }
        list($next) = db::o()->fetch_row($cats);
        $r = db::o()->p($type)->query('SELECT * FROM config' . ($type ? ' WHERE cat=?' : ""));
        $r = db::o()->fetch2array($r);
        if (!$r)
            die('No way');
        tpl::o()->assign('rows', $r);
        tpl::o()->assign('ptype', $prev);
        tpl::o()->assign('ntype', $next);
        tpl::o()->register_modifier('show_ctype', array($this, 'show_type'));
        tpl::o()->display('admin/config/main.tpl');
    }

    /**
     * Отображение типов
     * @return null
     */
    protected function show_types() {
        $r = db::o()->query('SELECT cat, COUNT(*) AS c FROM config GROUP BY cat');
        tpl::o()->assign('rows', db::o()->fetch2array($r));
        tpl::o()->display('admin/config/cats.tpl');
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
        if ($type == 'other') {
            $f = self::method_show_prefix . $name;
            return $this->call_method($f, $value);
        } elseif ($type == 'radio' || $type == 'select') {
            $a = explode(";", $allowed);
            $allowed = array();
            $c = count($a);
            for ($i = 0; $i < $c; $i++) {
                if (lang::o()->visset("config_field_" . $name . "_" . $a[$i]))
                    $l = lang::o()->v("config_field_" . $name . "_" . $a[$i]);
                else
                    $l = lang::o()->v("config_value_" . $a[$i]);
                $allowed[$a[$i]] = $l;
            }
        }
        else
            input::o()->ssize(35);
        return input::o()->stype($type)->scurrent($value)->skeyed()->standart_types(config_man::config_prefix . $name, $allowed);
    }

    /**
     * Проверка переменной типа other
     * @param string $name имя переменной
     * @param string $value значение
     * @return bool true, если верное значение
     */
    public function check_field($name, &$value) {
        $f = self::method_check_prefix . $name;
        return (bool) $this->call_method($f, array(&$value));
    }

    /**
     * Отключённые модули
     * @param string $value значение поля
     * @return string HTML код поля
     */
    protected function show_field_disabled_modules($value) {
        $modules = allowed::o()->get();
        $c = count($modules);
        for ($i = 0; $i < $c; $i++)
            if (allowed::o()->is_basic($modules[$i]))
                unset($modules[$i]);
        return input::o()->scurrent(explode(";", $value))->ssize(4)->snull()->simple_selector("config_disabled_modules", $modules);
    }

    /**
     * Проверка поля отключённых модулей
     * @param string $value значение поля
     * @return bool true, если значение поля нормальное
     */
    protected function check_field_disabled_modules(&$value) {
        if (!is_array($value))
            $value = "";
        else
            $value = implode(';', array_filter($value));
        return true;
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
        $keys = array();
        $newcfg = array();
        $i = 0;
        $sort = array();
        foreach ($POST as $key => $value) {
            if (strpos($key, $cprefix) !== 0)
                continue;
            $key = substr($key, $cprefix_length);
            if (!config::o()->visset($key))
                continue;
            $keys[] = $key;
            $newcfg[$key] = $value;
            $sort[$key] = ++$i;
        }
        if (!$keys)
            return;
        try {
            plugins::o()->pass_data(array("newcfg" => &$newcfg,
                "sort" => &$sort), true)->run_hook('admin_config_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        $r = db::o()->p($keys)->query('SELECT name,type,allowed FROM config WHERE name IN(@' . count($keys) . '?)');
        $c = 0;
        while (list($name, $type, $allowed) = db::o()->fetch_row($r)) {
            if (!$this->check_type($type, $newcfg[$name], $allowed, $name))
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
    public function check_type($type, &$value, $allowed = null, $name = null) {
        if ($type == 'other') {
            /* @var $o cofig_man */
            $o = plugins::o()->get_module('config', 1);
            return (bool) $o->check_field($name, $value);
        } elseif ($type == 'radio' || $type == 'select')
            $allowed = explode(";", $allowed);
        return input::o()->standart_types_check($type, $value, $allowed, config_man::config_prefix . $name);
    }

}

?>