<?php

/**
 * Project:             CTRev
 * File:                class.tpl.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
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
     * @var array
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
     * @global lang $lang
     * @param string $file файл
     * @return string HTML код
     */
    public function fetch($file) {
        global $lang;
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

}

?>