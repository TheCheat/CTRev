<?php

/**
 * Project:            	CTRev
 * File:                globals.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Глобальные переменные, только лучше
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class globals {

    /**
     * Массив переменных
     * @var array $vars
     */
    private static $vars = array();

    /**
     * Получение значения переменной
     * @param string $var имя переменной
     * @return mixed значение переменной
     */
    public static function g($var) {
        return self::$vars[$var];
    }

    /**
     * Установка значения переменной
     * @param string $var имя переменной
     * @param mixed $value значение переменной
     * @return null
     */
    public static function s($var, $value) {
        if (!$var)
            return;
        self::$vars[$var] = $value;
    }

}

?>