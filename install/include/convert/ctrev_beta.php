<?php

/**
 * Project:             CTRev
 * @file                install/include/convert/ctrev_beta.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Методы получения и обработки данных
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

require_once ROOT . 'include/classes/class.display.php';
require_once ROOT . 'include/classes/class.fbenc.php';
require_once ROOT . 'include/classes/class.bittorrent.php';
require_once ROOT . 'include/classes/class.users.php';

class get_convert {

    /**
     * Конвертируемая база данных
     * @var string $db
     */
    private $db = 'cyberhype';

    /**
     * Префикс таблиц
     * @var string $prefix
     */
    private $prefix = '';

    /**
     * Сопоставление групп
     * @var array $groups
     */
    private $groups = array();

    /**
     * Конструктор класса
     * @param string $db имя БД
     * @param string $prefix префикс таблиц
     * @param array $groups список групп
     * @return null
     */
    public function __construct($db, $prefix, $groups) {
        $this->db = $db;
        $this->prefix = $prefix;
        $this->groups = (array) $groups;
    }

    /**
     * Получение нового ID группы
     * @param int $group старый ID группы
     * @return int новый ID группы
     */
    public function get_group($group) {
        $group = (int) $group;
        if (!$this->groups || !is_array($this->groups) || !$this->groups[$group]) {
            printf(lang::o()->v('convert_cant_find_group'), $group);
            die();
        }
        return $this->groups[$group];
    }

}

?>