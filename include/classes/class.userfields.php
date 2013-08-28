<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.userfields.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Пользовательские поля
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class userfields extends pluginable_object {
    /**
     * Префикс для имени методов отображения
     */

    const method_input_prefix = 'input_field_';
    /**
     * Префикс для имени методов отображения
     */
    const method_show_prefix = 'show_field_';
    /**
     * Префикс для имени методов проверки
     */
    const method_check_prefix = 'check_field_';

    /**
     * Префикс для переменных в input
     */
    const var_prefix = 'custom_userfield_';

    /**
     * Доступные типы полей => тип значений
     * @var array $types
     */
    protected $types = array('int' => 0,
        'string' => 0,
        'text' => 0,
        'date' => 1,
        'folder' => 1,
        'radio' => 2,
        'select' => 2,
        'checkbox' => 0,
        'other' => 0);

    /**
     * Данные пользователя
     * @var array $data
     */
    protected $data = array();

    /**
     * Доп. поля
     * @var array $fields 
     */
    protected static $fields = array();

    /**
     * Данный тип отображения
     * @var string $type 
     */
    protected $type = "profile";

    /**
     * Допустимые типы отображений
     * @var array $allowed_types
     */
    protected $allowed_types = array('profile',
        'register');

    /**
     * Изменение типа отображений
     * @param string $type имя типа
     * @return display_userfields $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Задание массива пользовательских данных
     * @param array $data данные
     * @return display_userfields $this
     */
    public function set_user($data) {
        if (!$data)
            return;
        $this->data = $data;
        return $this;
    }

    /**
     * Получение значения переменной
     * @param string $var имя переменной
     * @return mixed значение
     */
    protected function get_data($var) {
        $var = self::var_prefix . $var;
        if ($this->data)
            return $this->data[$var];
        if (!users::o()->v())
            return "";
        return users::o()->v($var);
    }

    /**
     * Конструктор класса
     * @return null
     */
    protected function plugin_construct() {
        $this->load();
        $this->access_var("types", PVAR_ADD);
        $this->access_var("allowed_types", PVAR_ADD);
        /**
         * @note Ввод доп. пользовательских полей(input_userfields)
         * params:
         * string type тип ресурса(profile|register)
         * array user данные пользователя
         */
        tpl::o()->register_function('input_userfields', array($this, "input"));
        /**
         * @note Вывод доп. пользовательских полей(display_userfields)
         * params:
         * string type тип ресурса(profile|register)
         * array user данные пользователя
         */
        tpl::o()->register_function('display_userfields', array($this, "show"));
    }

    /**
     * Загрузка полей
     * @return null
     */
    protected function load() {
        if (self::$fields)
            return;
        self::$fields = db::o()->cname('userfields')->ckeys('field')->query('SELECT * FROM users_fields');
    }

    /**
     * Отображение в профиле
     * @param array $params массив параметров
     * @return string HTML код полей
     */
    public function show($params = null) {
        if ($params) {
            $this->change_type($params["type"]);
            $this->set_user($params['user']);
        }
        $r = "";
        foreach (self::$fields as $name => $row) {
            if ($this->type == 'profile' && !$row['show_profile'])
                continue;
            $value = $this->get_data($name);
            if (!$value)
                continue;
            $type = $row['type'];
            $allowed = $row['allowed'];
            $m = self::method_show_prefix . $name;
            if ($this->is_callable($m))
                $value = $this->call_method($m, array($value));
            else {
                if ($type == 'other')
                    $type = 'string';
                $value = input::o()->stype($type)->scurrent($value)->skeyed()->standart_types_display($allowed);
            }
            if (!$value)
                continue;
            $r .= "<dt>" . $row['name'] . "</dt>
                <dd>" . $value . "</dd>";
        }
        return $r;
    }

    /**
     * Сохранение настроек
     * @param array $data массив входных данных
     * @return array массив данных
     * @throws EngineException
     */
    public function save($data = null) {
        if (!$data)
            $data = $_POST;
        $a = array();
        foreach (self::$fields as $field => $row) {
            //if ($this->type == 'profile' && !$row['show_profile'] && $row['show_register'])
            //    continue;
            if ($this->type == 'register' && !$row['show_register'])
                continue;
            $l = mb_strlen($row['name']) - 1;
            $type = $row['type'];
            $allowed = $row['allowed'];
            $necessary = $row['name'][$l] == '*' ? true : false;
            $value = $data[self::var_prefix . $field];
            if ($type == 'other')
                $s = $this->call_method(self::method_check_prefix . $field, array(&$value));
            else
                $s = input::o()->standart_types_check($type, $value, $allowed, self::var_prefix . $field, true);
            if (!$s && $necessary)
                throw new EngineException('userfield_necessary_empty', mb_substr($row['name'], 0, $l));
            if ($s)
                $a[self::var_prefix . $field] = $value;
        }
        return $a;
    }

    /**
     * Отображение в ПУ/при регистрации
     * @param array $params массив параметров
     * @return null
     */
    public function input($params = null) {
        if ($params) {
            $this->change_type($params["type"]);
            $this->set_user($params['user']);
        }
        $r = "";
        foreach (self::$fields as $name => $row) {
            //if ($this->type == 'profile' && !$row['show_profile'] && $row['show_register'])
            //    continue;
            if ($this->type == 'register' && !$row['show_register'])
                continue;
            $type = $row['type'];
            $allowed = $row['allowed'];
            $value = $this->get_data($name);
            if ($type == 'other')
                $field = $this->call_method(self::method_input_prefix . $name, array($value));
            else
                $field = input::o()->stype($type)->scurrent($value)->skeyed()->standart_types(self::var_prefix . $name, $allowed);
            if (!$field)
                continue;
            $r .= "<dt>" . $row['name'] . ":</dt>
                <dd>" . $field . ($row['descr'] ? "<br><font size='1'>" . $row['descr'] . "</font>" : "") . "</dd>";
        }
        return $r;
    }

    /**
     * Выборка страны
     * @param string $value значение
     * @return string HTML код
     */
    protected function input_field_country($value) {
        return input::o()->scurrent($value)->snull()->select_countries(self::var_prefix . 'country');
    }

    /**
     * Вывод страны
     * @param string $value значение
     * @return string HTML код
     */
    protected function show_field_country($value) {
        return input::o()->get_country($value);
    }

    /**
     * Проверка ввода страны
     * @param int $value значение
     * @return bool true
     */
    protected function check_field_country(&$value) {
        $value = (int) $value;
        return true;
    }

    /**
     * Выборка страны
     * @param string $value значение
     * @return string HTML код
     */
    protected function input_field_website($value) {
        return input::o()->stype('string')->scurrent($value)->standart_types(self::var_prefix . 'website');
    }

    /**
     * Вывод сайта
     * @param string $value значение
     * @return string HTML код
     */
    protected function show_field_website($value) {
        return "<a href='" . $value . "'>" . $value . "</a>";
    }

    /**
     * Проверка ввода сайта
     * @param string $value значение
     * @return bool true
     */
    protected function check_field_website(&$value) {
        return preg_match(display::url_pattern, $value);
    }

}

?>