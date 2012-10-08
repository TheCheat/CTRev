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

// Свои исключения. С маджонгом и куртизанками
class EngineException extends Exception {

    /**
     * Переменные
     * @var array $vars
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
     * @return string сообщение
     */
    public function getEMessage() { // Ибо getMessage - final
        $m = lang::o()->if_exists($this->message);
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
            message($this->getMessage(), $this->getVars());
    }

}

/**
 * @usage пустой класс
 */
class empty_class {

    /**
     * Вызов произовльного метода
     * @param mixed $v1 параметр 1
     * @param mixed $v2 параметр 2
     * @return null
     */
    public function __call($v1, $v2) {
        
    }

    /**
     * Получение произовльной переменной
     * @param string $v1 имя переменной
     * @return null 
     */
    public function __get($v1) {
        
    }

    /**
     * Установка значения произвольной переменной
     * @param string $v1 имя переменной
     * @param mixed $v2 значение
     * @return null 
     */
    public function __set($v1, $v2) {
        
    }

}

?>