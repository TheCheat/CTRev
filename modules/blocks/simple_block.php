<?php

/**
 * Project:         	CTRev
 * @file                modules/blocks/simple_block.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Пример блока с параметрами
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

/**
 * Имя класса должно совпадать с именем файла и иметь постфикс "_block" 
 */
class simple_block_block {

    /**
     * Настройки блока
     * @var array $settings
     */
    public $settings = array(
        "par1" => "string", // простой параметр-строка
        "par1t" => "text", // простой параметр-строка в виде поля с ББ-кодами, недоступно для массива
        "par2" => "integer", // простой параметр-целое число
        "par3" => "enum[1;2;3;4]", // простой параметр-выборка
        "par35" => "enum[0;1]", // простой параметр-выборка, будет представлен в виде Да/Нет
        "par4[]" => "string", // неограниченный строковой массив параметров
        "par5[2]" => "integer", // целочисленный массив параметров из 2 элементов
        "par6[string]" => "enum[test;tests;testers]", // массив параметров-выборки с строковыми ключами
        "par7[integer]" => "enum[test1;tests1;testers1]", // массив параметров-выборки с целочисленными ключами
        "par7[enum[test;tests;testers]]" => "string"); // !!! отображаться не будет

    /**
     * Файл в папке blocks с языковыми переменными настроек(по-умолчанию - main)
     * В файле к каждому параметру должна быть переменная вида:
     * blocks_[имя файла]_settings_[имя параметра]
     * для enum:
     * blocks_[имя файла]_settings_[имя параметра]_[значение]
     * для описания под значениями:
     * blocks_[имя файла]_settings_[имя параметра]_descr
     * @var string $settings_lang
     */
    public $settings_lang = "main";

    /**
     * Настройки по-умолчанию
     * @var array $defaults
     */
    public $defaults = array("par1" => "simple string",
        "par1t" => "Long, long, long text, with [b]BOLD[/b] word.",
        "par2" => "42",
        "par3" => "2",
        "par35" => "1",
        "par4" => array("string1", "string2", "string3"),
        "par5" => array('1', '2'),
        "par6" => array('element1' => "test", "element2" => "tests"),
        "par7" => array("1" => "test1", "3" => "tests1", "5" => "testers1"));

    /**
     * Функция для инициализации блока
     * @return null
     */
    public function init() {
        print (nl2br(print_r($this->settings, true)));
    }

}

?>