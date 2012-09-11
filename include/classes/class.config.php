<?php

/**
 * Project:            	CTRev
 * File:                class.config.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Конфигурация движка
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class config {

    /**
     * Конфиг. переменные
     * @tutorial protected, ибо предполагается, что неизменно в процессе работы.
     * Ибо это данные из БД и для изменения юзать {@see config::add()} и {@see config::set()}
     * @var array
     */
    private $vars = array();

    /**
     * Конструктор конфига
     * @global db $db
     * @param string $cat категория конфига
     * @return null 
     */
    public function __construct($cat = '') {
        global $db;
        $vars = array('name' => 'value');
        if ((!$cat || $cat == "announce"))
            $c = array('n' => 'config/' . ($cat ? "announce" : "all"),
                'k' => $vars);
        $cat = $cat ? ' WHERE cat=' . $db->esc($cat) : '';
        $r = $db->query("SELECT name, value FROM config" . $cat, $c);
        if (is_array($r))
            $this->vars = $r;
        else
            $this->vars = $db->fetch2array($r, null, $vars);
    }

    /**
     * Существует ли переменная?
     * @param string $var имя переменной
     * @return bool true, если существует
     */
    public function visset($var) {
        return isset($this->vars[$var]);
    }

    /**
     * Получение значения  переменной конфига
     * @param string $var имя переменной
     * @return mixed значение
     */
    public function v($var) {
        if (isset($this->vars[$var]))
            return $this->vars[$var];
    }

    /**
     * Добавление значения конфигурационного параметра
     * @global db $db
     * @param string $var имя параметра
     * @param string $value значение параметра
     * @param string $type тип параметра(int,string,text,date,folder,radio,select,checkbox,other)
     * @param array $allowed допустимые значения
     * @param string $cat категория конфигурации
     * @param int $sort сортировка параметра
     * @return null
     */
    public function add($var, $value, $type = 'int', $allowed = null, $cat = 'other', $sort = null) {
        global $db;
        $this->vars[$var] = $value;
        $update = array('name' => $var,
            'value' => $value,
            'type' => $type,
            'cat' => $cat);
        if ($allowed)
            $update['allowed'] = implode(';', (array) $allowed);
        if ($sort)
            $update['sort'] = implode(';', (array) $sort);
        $db->insert($update, 'config');
        $this->uncache();
    }

    /**
     * Изменение значения конфигурационного параметра
     * @global db $db
     * @global cache $cache
     * @param string $var имя параметра
     * @param string $value значение параметра
     * @param int $sort сортировка параметра
     * @return null
     */
    public function set($var, $value, $sort = null) {
        global $db;
        $this->vars[$var] = (string) $value;
        $update = array('value' => (string) $value);
        if ($sort)
            $update['sort'] = (int) $sort;
        $db->update($update, 'config', 'WHERE name=' . $db->esc($var) . ' LIMIT 1');
        $this->uncache();
    }

    /**
     * Удаление кеша конфига
     * @global cache $cache 
     * @return null
     */
    private function uncache() {
        global $cache;
        $cache->remove('config/all');
        $cache->remove('config/announce');
    }

}

?>