<?php

/**
 * Project:            	CTRev
 * File:                class.stats.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name                Ведение статистики на сайте
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

final class stats {

    /**
     * Массив значений статистики
     * @var array
     */
    private $res = array();

    /**
     * Конструктор
     * @global db $db
     * @return null
     */
    public function __construct() {
        global $db;
        if (!$this->res) {
            $res = $db->query("SELECT * FROM stats");
            $this->res = $db->fetch2array($res, null, array("name" => "value"));
        }
    }

    /**
     * Чтение значения из статистики
     * @param string $name поле
     * @return string значение
     */
    public function read($name = null) {
        if ($name)
            return $this->res[$name];
        else
            return $this->res;
    }

    /**
     * Запись значения в статистику
     * @global db $db
     * @param string $name поле
     * @param string $value значение
     * @return null
     */
    public function write($name, $value) {
        global $db;
        if (!isset($this->res[$name])) {
            $this->res[$name] = $value;
            $r = $db->insert(array("name" => $name, "value" => $value), "stats");
            return;
        }
        $this->res[$name] = $value;
        $db->update(array("value" => $value), "stats", "WHERE name=" . $db->esc($name) . " LIMIT 1");
    }

    /**
     * Удаление поля статистики
     * @global db $db
     * @param string $name поле
     * @return null
     */
    public function remove($name) {
        global $db;
        if (!isset($this->res[$name]))
            return;
        unset($this->res[$name]);
        $db->delete("stats", "WHERE name=" . $db->esc($name) . " LIMIT 1");
    }

}

?>