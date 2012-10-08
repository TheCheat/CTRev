<?php

/**
 * Project:            	CTRev
 * File:                plugin.simple.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Пример плагина
 * @version           	1.00
 * @tutorial            Имя файла должно быть plugin.{имя плагина}.php
 */
if (!defined('INSITE'))
    die('Remote access denied!');

// Имя класса должно иметь вид plugin_{имя плагина}
class plugin_simple {

    /**
     * Версия плагина
     * @var string $version
     */
    public $version = '1.00';

    /**
     * Имя плагина
     * @var string $name
     */
    public $name = 'Плагин для проверки';

    /**
     * Описание плагина
     * @var string $descr
     */
    public $descr = 'Простое описание плагина';

    /**
     * Совместимость с движком. Наилучшая
     * @var string $compatibility
     */
    public $compatibility = '1.00';

    /**
     * Совместимость с движком.  Мин. версия.
     * @var string $compatibility_min
     */
    public $compatibility_min = '1.00';

    /**
     * Совместимость с движком. Макс. версия.
     * @var string $compatibility_max
     */
    public $compatibility_max = '1.00';

    /**
     * Автор плагина
     * @var string $author
     */
    public $author = 'The Cheat';

    /**
     * Настройки плагина
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
     * Файл в папке plugins с языковыми переменными настроек(по-умолчанию - main)
     * В файле к каждому параметру должна быть переменная вида:
     * plugins_[имя плагина]_settings_[имя параметра]
     * для enum:
     * plugins_[имя плагина]_settings_[имя параметра]_[значение]
     * для описания под значениями:
     * plugins_[имя плагина]_settings_[имя параметра]_descr
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
     * Инициализация плагина
     * @param plugins $plugins объект плагиновой системы
     * @return null
     * @tutorial Здесь настраивается, какие классы плагин переопределяет, 
     * расширяет, какие хуки задействованы.
     */
    public function init($plugins) {
        
    }

    /**
     * Установка плагина
     * @param bool $re переустановка?
     * в данном случае необходимо лишь произвести изменения в файлах
     * @return null
     * @tutorial метод может возвращать false или 0, в случае, если была какая-то
     * критическая ошибка при удалении
     */
    public function install($re = false) {
        
    }

    /**
     * Удаление плагина
     * @param bool $replaced было ли успешно ВСЁ замененённое сохранено?
     * @return null 
     * @tutorial метод может возвращать false или 0, в случае, если была какая-то
     * критическая ошибка при удалении
     */
    public function uninstall($replaced = false) {
        
    }

}

?>