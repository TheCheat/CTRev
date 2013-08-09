<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.config.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Конфигурация движка
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class config {

    /**
     * Конфиг. переменные
     * @var array $vars
     * @note protected, ибо предполагается, что неизменно в процессе работы.
     * Ибо это данные из БД и для изменения юзать add и set
     * @see add()
     * @see set()
     */
    private $vars = array();

    /**
     * Существует ли переменная?
     * @param string $var имя переменной
     * @return bool true, если существует
     */
    public function visset($var) {
        return isset($this->vars[$var]);
    }

    /**
     * Получение значения  переменной конфига
     * @param string $var имя переменной
     * @return mixed значение
     */
    public function v($var) {
        if (isset($this->vars[$var]))
            return $this->vars[$var];
    }

    /**
     * Добавление значения конфигурационного параметра
     * @param string $var имя параметра
     * @param string $value значение параметра
     * @param string $type тип параметра(int,string,text,date,folder,radio,select,checkbox,other)
     * @param array $allowed допустимые значения
     * @param string $cat категория конфигурации
     * @param int $sort сортировка параметра
     * @return null
     */
    public function add($var, $value, $type = 'int', $allowed = null, $cat = 'other', $sort = null) {
        $this->vars[$var] = $value;
        $update = array('name' => $var,
            'value' => $value,
            'type' => $type,
            'cat' => $cat);
        if ($allowed)
            $update['allowed'] = implode(';', (array) $allowed);
        if ($sort)
            $update['sort'] = implode(';', (array) $sort);
        db::o()->insert($update, 'config');
    }

    /**
     * Изменение значения конфигурационного параметра
     * @param string $var имя параметра
     * @param string $value значение параметра
     * @param int $sort сортировка параметра
     * @return null
     */
    public function set($var, $value, $sort = null) {
        $this->vars[$var] = (string) $value;
        $update = array('value' => (string) $value);
        if ($sort)
            $update['sort'] = (int) $sort;
        db::o()->p($var)->update($update, 'config', 'WHERE name=? LIMIT 1');
    }

    /**
     * Удаление параметра конфигурации
     * @param string $var имя переменной
     * @return null
     */
    public function remove($var) {
        unset($this->vars[$var]);
        db::o()->p($var)->delete('config', 'WHERE name=? LIMIT 1');
    }

    /**
     * Проверка, включен ли модуль
     * @param string $module имя модуля
     * @return bool true, если включен
     */
    public function mstate($module) {
        $disabled = &$this->vars['disabled_modules'];
        if (!is_array($disabled))
            $disabled = explode(';', $disabled);
        return allowed::o()->is_basic($module) || !in_array($module, $disabled);
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var config $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @param string|array $cat категория конфига
     * @return null 
     */
    private function __construct($cat = '') {
        $where = "";
        if ($cat) {
            if (is_array($cat))
                $where = ' IN(@' . count($cat) . '?)';
            else
                $where = ' =?';
            $where = ' WHERE cat ' . $where;
        }
        $r = db::o()->p($cat)->query("SELECT name, value FROM config" . $where);
        $this->vars = db::o()->fetch2array($r, null, array('name' => 'value'));
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
     * @param string|array $cat категория конфига
     * @return config $this
     */
    public static function o($cat = '') {
        if (!self::$o)
            self::$o = new self($cat);
        return self::$o;
    }

}

?>