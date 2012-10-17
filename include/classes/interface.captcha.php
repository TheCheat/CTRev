<?php

/**
 * Project:            	CTRev
 * @file                include/classes/interface.captcha.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Интерфейс для Captcha
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

interface captcha_interface {

    /**
     * Функция вызова captcha
     * @return null
     */
    public function init();

    /**
     * Функция проверки кода captcha
     * @param array $error массив ошибок
     * @param string $var $_POST переменная для проверки введённого кода
     * @return null
     */
    public function check(&$error, $var = "captcha_code");

    /**
     * Функция очистки старых капч
     * @param string $only удаление только(old - старые, user пользователя)
     * @return null
     */
    public function clear($only = "");
}

?>