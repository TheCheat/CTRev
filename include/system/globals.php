<?php

/**
 * Project:            	CTRev
 * @file                include/system/globals.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Глобальные переменные, только на 20% круче,
 * ибо никакого "побочного эффекта", который так все нанавидят.
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class globals {

    /**
     * Массив переменных
     * @var array $vars
     * @note POST - массив POST данных до экранирования
     * @note BASEURL - URL до корня сайта
     * @note PREBASEURL - часть BASEURL без протокола и домена
     * @note theme_path - URL до данной темы
     * @note atheme_path - URL до данной темы АЦ
     * @note color_path - URL до данного цвета изображения
     * @note eadmin_file - URL к админке
     * @note admin_file - URL к админке с выбранным модулем
     * @note ajax - AJAX-запрос?
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