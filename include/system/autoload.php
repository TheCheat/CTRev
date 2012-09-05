<?php

/**
 * Project:            	CTRev
 * File:                autoload.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Автоподгрузка классов, тобишь "на лету"
 * @version           	1.00
 */

if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * Автоподгрузка классов из classes
 * @param string $class имя класса
 * @return null
 */
function __autoload($class) {
    if (allowed::o()->is($class, "classes")) {
        /**
         * Не нужно убирать любой постфикс у интерфейса, ибо интерфейс автоматически
         * загружается вместе с классом.
         */
        if (file_exists(ROOT . 'include/classes/interface.' . $class . '.php'))
            include ROOT . 'include/classes/interface.' . $class . '.php';
        if (file_exists(ROOT . 'include/classes/class.' . $class . '.php'))
            include ROOT . 'include/classes/class.' . $class . '.php';
    }
}

/**
 *  Динамическая подгрузка объектов, как бэ оптимизация, ибо часть объектов
 *      не всегда нужна и лишь кушает память.
 *  После подгрузки объект system в переменной заменяется необходимым объектом.
 */
class system {

    /**
     * Данный объект из дин. подгружаемого класса
     * @var object
     */
    private $_parent = null;

    /**
     * Имя объекта
     * @var string
     */
    private $_oname = "";

    /**
     * Имя переменной объекта
     * @var string
     */
    private $_vname = "";

    /**
     * Разрешить разрушить объект?
     * @var bool
     */
    private $_destruct = false;

    /**
     * Конструктор
     * @param string $var переменная
     * @param string $obj класс
     * @return null
     */
    public function __construct($var, $obj) {
        $this->_vname = $var;
        $this->_oname = $obj;
    }

    /**
     * Инициализация класса при вызове метода/обращении к св-ву
     * @global plugins $plugins
     * @return null
     */
    private function _initialize() {
        global $plugins;
        if (!$this->_parent && $this->_oname)
            $this->_parent = $plugins->get_class($this->_oname);
    }

    /**
     * Избавляемся от системнего класса
     * @global object ${имя_переменной}
     * @return object собстно объект
     */
    public function make_object() {
        global ${$this->_vname};
        if (!$this->_parent)
            $this->_initialize();
        $this->_destruct = true;
        ${$this->_vname} = $this->_parent;
        return $this->_parent;
    }

    /**
     * Вызов метода объекта
     * @param string $name имя метода
     * @param array $arguments массив аргументов
     * @return mixed то, что вернёт метод.
     */
    public function __call($name, $arguments) {
        $this->_initialize();
        if (!$this->_parent)
            return;
        $this->make_object();
        return call_user_func_array(array($this->_parent, $name), $arguments);
    }

    /**
     * Получение свойства объекта
     * @param string $name имя свойства
     * @return mixed свойство объекта
     */
    public function &__get($name) {
        $this->_initialize();
        if (!$this->_parent)
            return;
        $r = $this->_parent->$name;
        $this->make_object();
        return $r;
    }

    /**
     * Проверка существования свойства объекта
     * @param string $name имя свойства
     * @return bool true, если существует
     */
    public function __isset($name) {
        $this->_initialize();
        if (!$this->_parent)
            return;
        $r = isset($this->_parent->$name);
        $this->make_object();
        return $r;
    }

    /**
     * Присвоение свойству объекта нек. значения
     * @param string $name имя свойства
     * @param mixed $value значение
     * @return null
     */
    public function __set($name, $value) {
        $this->_initialize();
        if (!$this->_parent)
            return;
        $this->_parent->$name = $value;
        $this->make_object();
    }
}

// Свои исключения. С маджонгом и куртизанками
class EngineException extends Exception {

    /**
     * Переменные
     * @var array
     */
    protected $vars = array();

    /**
     * Конструктор исключения
     * @param string $message сообщение исключения
     * @param mixed $vars массив переменных(одна переменная) для vsprintf
     * @param int $code код сообщения
     * @param EngineException $previous предыдущее исключение
     * @return null
     */
    public function __construct($message = '', $vars = array(), $code = null, $previous = null) {
        $this->message = (string) $message;
        $this->code = (int) $code;
        $this->previous = $previous;
        $this->vars = (array) $vars;
    }

    /**
     * Получение сообщения
     * @global lang $lang
     * @return string сообщение
     */
    public function getEMessage() { // Ибо getMessage - final
        global $lang;
        $m = $lang->if_exists($this->message);
        $v = $this->vars;
        return $v ? vsprintf($m, $v) : $m;
    }

    /**
     * Получение массива переменных
     * @return array массив
     */
    public function getVars() {
        return $this->vars;
    }

    /**
     * "Ловля" исключений по-умолчанию
     * @global bool $ajax
     * @param bool $eajax ручная настройка из Ajax или нет
     * @return null 
     */
    public function defaultCatch($eajax = null) {
        global $ajax;
        $eajax = is_null($eajax) ? $ajax : $eajax;
        if ($eajax)
            die($this->getEMessage());
        else
            mess($this->getMessage(), $this->getVars());
    }

}

// пустой класс
class empty_class {

    public function __call($v1, $v2) {
        
    }

    public function __get($v1) {
        
    }

    public function __set($v1, $v2) {
        
    }

}

?>