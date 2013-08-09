<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.modsettings.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Настройки для блоков/плагинов
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class modsettings {
    /**
     * ID модуля, при котором настройки не кешируются 
     */

    const nocache_id = "::nocache::";

    /**
     * Неограниченный массив
     */
    const unlimited = "unlimited";

    /**
     * Тип настроек
     * @var string $type
     */
    private $type = 'blocks';

    /**
     * Значения ENUM параметра
     * @var array $enum_vals
     */
    private $enum_vals = array();

    /**
     * Типы ключей
     * @var array $key_types
     */
    private $key_types = array(
        'simple',
        'limited',
        'unlimited',
        'integer',
        'string');

    /**
     * Типы значений
     * @var array $val_types
     */
    private $val_types = array('integer',
        'string',
        'text',
        'enum');

    /**
     * Значения инициализованы?
     * @var bool $tinited
     */
    private $tinited = false;

    /**
     * Изменение типа настроек
     * @param string $type тип
     * @return modsettings $this
     */
    public function change_type($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Проверка ID модуля
     * @param int|string $id ID модуля
     * @return bool true, если верный
     */
    private function check_id($id) {
        return $id && ($id === self::nocache_id || is_numeric($id) || validword($id));
    }

    /**
     * Проверка на принадлежность к массиву значений ENUM
     * @param string $str строка
     * @return bool true, если входит в массив значений
     */
    private function enum($str) {
        return in_array($str, $this->enum_vals);
    }

    /**
     * Фильтер значений enum
     * @param string $str строка
     * @return bool true, если правильное значение 
     */
    private function enum_filter($str) {
        if (!validword($str, null, 1) && !is_numeric($str))
            return false;
        return true;
    }

    /**
     * Обработка ключа настроек
     * @param object $object объект модуля
     * @param string $param параметр
     * @param string $type тип значения
     * @param array $parsed массив спарсенного
     * @return mixed кол-во ключей
     */
    private function key(&$object, &$param, &$type, &$parsed) {
        $count = null;
        if (preg_match('/^(\w+)(?:\[(|[0-9]+|string|integer)\])?$/siu', $param, $matches)) {
            $nparam = $matches [1];
            $count = $matches [2];
            if ($param != $nparam) {
                $object->settings [$nparam] = $object->settings [$param];
                unset($object->settings [$param]);
                $param = $nparam;
            }
            if (!is_null($count)) {
                $count = mb_strtolower($count);
                if (!$count)
                    $count = self::unlimited;
                $object->settings [$param] = array($object->settings [$param], $count);
            }
        } else {
            unset($object->settings [$param]);
            return false;
        }
        if (!$count)
            $object->settings [$param] = array($object->settings [$param]);
        elseif ($type == 'text') {
            unset($object->settings [$param]);
            return false;
        }
        $type = $object->settings [$param][0];
        $type = mb_strtolower($type);
        if (mb_strpos($type, "enum") === 0) {
            $this->enum_vals = mb_substr($type, 5, mb_strlen($type) - 6);
            $this->enum_vals = explode(";", $this->enum_vals);
            $this->enum_vals = array_unique(array_map('trim', $this->enum_vals));
            $this->enum_vals = array_filter($this->enum_vals, array($this, 'enum_filter'));
            sort($this->enum_vals);
            if (count($this->enum_vals) < 2) {
                unset($object->settings [$param]);
                return false;
            }
            $object->settings [$param][0] = $type = $this->enum_vals;
        }
        $parsed[$param] = $object->settings[$param];
        return $count;
    }

    /**
     * Проверка кол-ва значений
     * @param object $object объект модуля
     * @param array $settings массив параметров
     * @param mixed $count кол-во ключей
     * @param string $param параметр
     * @return bool true, если успешно проверено
     */
    private function count(&$object, &$settings, $count, $param) {
        if (!$count)
            return true;
        if (!is_array($settings [$param])) {
            unset($object->settings [$param]);
            return false;
        }
        if (is_numeric($count)) {
            if ($count != count($settings [$param])) {
                unset($object->settings [$param]);
                return false;
            }
        } elseif ($count != self::unlimited) {
            switch ($count) {
                case "integer" :
                    $keys = array_map("intval", array_keys($settings [$param]));
                    break;
                case "string" :
                    $keys = array_map("strval", array_keys($settings [$param]));
                    break;
            }
            $settings [$param] = array_combine($keys, $settings [$param]);
        }
        return true;
    }

    /**
     * Обработка значения настроек
     * @param object $object объект модуля
     * @param array $settings массив параметров
     * @param mixed $count кол-во ключей
     * @param string $param параметр
     * @param string $type тип значения
     * @return null
     */
    private function value(&$object, $settings, $count, $param, $type) {
        switch ($type) {
            case "text" :
            case "string" :
                if ($count)
                    $object->settings [$param] = array_map("strval", $settings [$param]);
                else
                    $object->settings [$param] = strval($settings [$param]);
                break;
            case "integer" :
                if ($count)
                    $object->settings [$param] = array_map("intval", $settings [$param]);
                else
                    $object->settings [$param] = longval($settings [$param]);
                break;
            default :
                if (!is_array($type))
                    unset($object->settings [$param]);
                else {
                    if ($count) {
                        $ret = array_filter($settings [$param], array($this, "enum"));
                        if ($ret)
                            $object->settings [$param] = $ret;
                        else
                            unset($object->settings [$param]);
                    } else {
                        if (in_array($settings [$param], $this->enum_vals))
                            $object->settings [$param] = $settings [$param];
                        else
                            unset($object->settings [$param]);
                    }
                }
                break;
        }
    }

    /**
     * Функция обработки параметров модуля
     * @param int|string $id ID модуля
     * @param object $object объект модуля
     * @param array $settings параметры модуля
     * @return array обработанные настройки модуля
     */
    public function parse($id, &$object, $settings) {
        if (!isset($object->settings) || !is_array($object->settings))
            return;
        if (!$this->check_id($id))
            return;
        $type = $this->type;
        $cached = false;
        if ($id !== self::nocache_id && config::o()->v('cache_on') && config::o()->v('cache_modsettings')) {
            $crc32 = crc32(serialize($object->settings));
            $a = cache::o()->read('modsettings/' . $type . '-id' . $id);
            $cached = true;
            if ($a && $a[0] != $crc32)
                $a = null;
            if ($a) {
                list($null, $object->settings, $parsed) = $a;
                return $parsed;
            }
        }
        $parsed = array();
        foreach ($object->settings as $param => $type) {
            $count = $this->key($object, $param, $type, $parsed);
            if ($count === false)
                continue;
            if (!isset($settings[$param]) && isset($object->defaults) && is_array($object->defaults))
                $settings[$param] = $object->defaults[$param];
            if (!$this->count($object, $settings, $count, $param))
                continue;
            if (is_null($settings [$param])) {
                unset($object->settings [$param]);
                continue;
            }
            $this->value($object, $settings, $count, $param, $type);
        }
        $a = array($crc32, $object->settings, $parsed);
        if ($cached)
            cache::o()->write($a);
        return $parsed;
    }

    /**
     * Обработчик параметров
     * @param array $params массив параметров
     * @return string HTML код параметров
     */
    public function selector($params) {
        $val = $params['val'];
        $key = $params['key'];
        $k = $params['k'];
        $s = $params['s'];
        $langvar = $params['langvar'];
        $after = '';
        $cs = $s[1];
        $html = '';
        $unlim = false;
        switch ($cs) {
            case "integer":
            case "string":
                $html .= '<input type="text" name="key[' . $k . '][]" 
                               size="' . ($cs == 'integer' ? 7 : 25) . '" value="' . $key . '"> &rArr; ';
            case self::unlimited:
                $unlim = true;
            default:
                if ($cs)
                    $after = "[]";
                break;
        }
        $cs = $s[0];
        $name = 'value[' . $k . ']';
        $namea = $name . $after;
        switch ($cs) {
            case "integer":
            case "string":
                $html .= '<input type="text" name="' . $namea . '" 
                               size="' . ($cs == 'integer' ? 7 : 25) . '" value="' . $val . '">';
                break;
            case "text":
                $html .= bbcodes::o()->input_form($name, $val);
                break;
            default:
                if (!is_array($cs))
                    break;
                if ($cs == array(0, 1))
                    $html .= '<input type="checkbox" name="' . $namea . '" value="1"' .
                            ($val ? " checked='checked'" : '') . '>';
                else {
                    $a = array();
                    foreach ($cs as $i) {
                        $lv = lang::o()->v($langvar . '_' . $i, true);
                        $a[$i] = $lv ? $lv : $i;
                    }
                    $html .= input::o()->scurrent($val)->skeyed()->simple_selector($namea, $a);
                }
                break;
        }
        tpl::o()->assign('unlim', $unlim);
        return $html;
    }

    /**
     * Подготовка настроек к отображению
     * @param array $parsed спарсенные настройки
     * @param array $settings данные настройки
     * @return null
     */
    private function prepare($parsed, &$settings) {
        if (!$parsed || !is_array($parsed))
            return;
        foreach ($parsed as $key => $e) {
            if (!$settings[$key])
                $settings[$key] = array('');
            else
                $settings[$key] = (array) $settings[$key];
            if (is_numeric($e[1]) && count($settings[$key]) != $e[1]) {
                $a = array();
                for ($i = 0; $i < $e[1]; $i++)
                    $a[$i] = '';
                $settings[$key] = $a;
            }
        }
    }

    /**
     * Отображение настроек для их редактирования
     * @note языковые файлы должны находится в папке %тип%/%settings_lang%
     * где %тип% - тип настроек, а %settings_lang% - переменная в объекте
     * @param int|string $id ID модуля
     * @param object $object объект с полями settings и settings_lang
     * @param array $settings массив настроенного
     * @param string $langprefix языковой префикс для настройки
     * @param bool $parsed уже спарсенно? тогда в {$settings} содержится массив спарсенного
     * @return string HTML код настроек
     */
    public function display($id, $object, $settings, $langprefix, $parsed = false) {
        $lng = 'main';
        if (!$this->check_id($id))
            return;
        if (!isset($object->settings) || !$object->settings)
            return;
        if (isset($object->settings_lang) && $object->settings_lang)
            $lng = $object->settings_lang;
        $type = $this->type;
        lang::o()->get($type . '/' . $lng);
        if (!$parsed)
            $parsed = $this->parse($id, $object, $settings);
        else
            $parsed = $settings;
        tpl::o()->assign('parsed_settings', $parsed);
        if (isset($object->settings))
            $settings = $object->settings;
        $this->prepare($parsed, $settings);
        tpl::o()->register_function('parameters_compiler', array($this, 'selector'));
        tpl::o()->assign('used_settings', $settings);
        tpl::o()->assign('settings_langprefix', $langprefix);
        return tpl::o()->fetch('modsettings/index.tpl');
    }

    /**
     * Сохранение настроек
     * @param int|string $id ID модуля
     * @param array $data массив данных
     * @return array массив настроек
     */
    public function save($id, $data) {
        $type = $this->type;
        $keys = (array) $data["key"];
        $values = (array) $data['value'];
        $settings = array();
        foreach ($values as $key => $v) {
            if (is_array($v)) {
                $a = array();
                $c = count($v);
                for ($i = 0; $i < $c; $i++) {
                    if (isset($keys[$key][$i]))
                        $a[$keys[$key][$i]] = $v[$i];
                    else
                        $a[] = $v[$i];
                }
            } else
                $a = $v;
            $settings[$key] = $a;
        }
        if ($id !== self::nocache_id)
            $this->uncache($id);
        return $settings;
    }

    /**
     * Удаление кеша настроек
     * @param int|string $id ID модуля
     * @return null
     */
    public function uncache($id) {
        if (!$this->check_id($id))
            return;
        $type = $this->type;
        cache::o()->remove('modsettings/' . $type . '-id' . $id);
    }

    /**
     * Создание настроек модуля
     * @return null
     */
    public function create() {
        lang::o()->get('modsettings');
        if (!$this->tinited) {
            $t = array();
            foreach ($this->key_types as $type)
                $t[$type] = lang::o()->v('modsettings_keytype_' . $type);
            $this->key_types = input::o()->skeyed()->simple_selector('keytype[]', $t);
            $t = array();
            foreach ($this->val_types as $type)
                $t[$type] = lang::o()->v('modsettings_valtype_' . $type);
            $this->val_types = input::o()->skeyed()->simple_selector('valtype[]', $t);
            $this->tinited = true;
        }
        tpl::o()->assign('mkeytypes', $this->key_types);
        tpl::o()->assign('mvaltypes', $this->val_types);
        tpl::o()->display('modsettings/add.tpl');
    }

    /**
     * Построение настроек из формы
     * @param array $data массив данных
     * @return array массив настроек
     */
    public function make($data) {
        $data_params = array(
            'params' => 'mparam',
            'key' => 'keytype',
            'val' => 'valtype',
            'limit' => 'keylimit',
            'enum' => 'enumvals');
        extract(rex($data, $data_params));
        if (!$params)
            return;
        $params = (array) $params;
        $key = (array) $key;
        $val = (array) $val;
        $limit = (array) $limit;
        $enum = (array) $enum;
        $r = array();
        foreach ($params as $k => $param) {
            if (!$param || !validword($param))
                continue;
            $v = $val[$k];
            $l = (int) $limit[$k];
            $e = trim($enum[$k]);
            $k = $key[$k];
            $a = "";
            switch ($k) {
                case "unlimited":
                    $a = "[]";
                case "simple":
                    break;
                case "limited":
                    if (!$l || $l < 2)
                        $a = false;
                    else
                        $a = "[" . $l . "]";
                    break;
                case "string":
                case "integer":
                    $a = "[" . $k . "]";
                    break;
                default:
                    $a = false;
                    break;
            }
            if ($a === false)
                continue;
            $param = $param . $a;
            $a = "";
            switch ($v) {
                case "string":
                case "integer":
                case "text":
                    $a = $v;
                    break;
                case "enum":
                    if (!preg_match('/^([a-z0-9\-\_]+(;|$))+$/si', $e))
                        $a = false;
                    else
                        $a = $v . '[' . $e . ']';
            }
            if ($a === false)
                continue;
            $r[$param] = $a;
        }
        return $r;
    }

    /**
     * Вывод для настроек параметров по-умолчанию
     * @param array $data массив данных
     * @return null
     */
    public function make_demo($data) {
        users::o()->check_perms('acp', 2);
        $settings = $this->make($data);
        if (!$settings)
            return;
        $defaults = $this->save(self::nocache_id, $data);
        $arr = array("settings" => $settings, "settings_lang" => "__doesnotexists");
        $obj = new arr2obj($arr);
        print($this->display(self::nocache_id, $obj, $defaults, ''));
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var modsettings $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        
    }

    /**
     * Не клонируем
     * @return null 
     */
    private function __clone() {
        
    }

    /**
     * И не десериализуем
     * @return null 
     */
    private function __wakeup() {
        
    }

    /**
     * Получение объекта класса
     * @return modsettings $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>