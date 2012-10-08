<?php

/**
 * Project:             CTRev
 * File:                class.tpl.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Шаблоны для инсталляции
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class tpl {

    /**
     * Массив данных
     * @var array $data
     */
    private $data = array();

    /**
     * Отображение шаблона
     * @param string $file файл
     * @return null
     */
    public function display($file) {
        print($this->fetch($file));
    }

    /**
     * Получение кода шаблона
     * @param string $file файл
     * @return string HTML код
     */
    public function fetch($file) {
        ob_start();
        $data = $this->data;
        include ROOT . 'install/style/' . $file . '.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Присвоение переменной
     * @param string $var переменная
     * @param mixed $value значение перемнной
     * @return tpl $this
     */
    public function assign($var, $value) {
        $this->data[$var] = $value;
        return $this;
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var tpl $o
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
     * @return tpl $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>