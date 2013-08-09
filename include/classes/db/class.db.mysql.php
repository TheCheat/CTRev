<?php

/**
 * Project:            	CTRev
 * @file                include/classes/db/class.db.mysql.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для работы с БД MySQL
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

abstract class db_core implements db_core_interface {

    /**
     * Подлючена ли БД?
     * @var bool $connected
     */
    protected $connected = false;

    /**
     * Коннект к БД
     * @global string $dbhost
     * @global string $dbuser
     * @global string $dbpass
     * @global string $dbname
     * @global string $charset
     * @return null
     */
    public function connect() {
        if ($this->connected)
            return;
        @mysql_connect(dbhost, dbuser, dbpass) or $this->err("mysql_connect");

        mysql_select_db(dbname) or $this->err("mysql_select_db");

        $q = "SET NAMES " . charset . " COLLATE " . charset . '_general_ci';
        mysql_query($q) or $this->err($q);
        $q = "SET character_set_client=" . $this->esc(charset);
        mysql_query($q) or $this->err($q);
        $q = "SET character_set_results=" . $this->esc(charset);
        mysql_query($q) or $this->err($q);
        $q = "SET collation_connection=@@collation_database;";
        mysql_query($q) or $this->err($q);
        register_shutdown_function("mysql_close");
        $this->connected = true;
    }

    /**
     * Последний вставленный ID
     * @param resid $res ID запроса
     * @return int ID новой строки
     */
    public function insert_id($res = null) {
        return $res ? mysql_insert_id($res) : mysql_insert_id();
    }

    /**
     * Выполнение запроса к БД
     * @param string $query строка запроса
     * @return resid ID запроса
     */
    public function query($query) {
        return @mysql_query($query);
    }

    /**
     * Получение строки результата запроса (row)
     * @param resid $result ID запроса
     * @return array массив, где ключи - целые числа
     */
    public function fetch_row($result) {
        return mysql_fetch_row($result);
    }

    /**
     * Получение строки результата запроса (assoc)
     * @param resid $result ID запроса
     * @return array массив, где ключи - столбцы, заданные в запросе
     */
    public function fetch_assoc($result) {
        return mysql_fetch_assoc($result);
    }

    /**
     * Получение кол-ва строк результата запроса
     * @param resid $result ID запроса
     * @return int кол-во строк результата
     */
    public function num_rows($result) {
        return mysql_num_rows($result);
    }

    /**
     * Проверка сколько строк было изменено
     * @param resid $link_identifier ID запроса
     * @return int изменённые строки
     */
    public function affected_rows($link_identifier = null) {
        $a_rows = $link_identifier ? mysql_affected_rows($link_identifier) : mysql_affected_rows();
        $info_str = mysql_info();
        // PHP 5.3
        preg_match("/Rows matched: ([0-9]+)/si", $info_str, $r_matched);
        return ($a_rows < 1) ? ($r_matched [1] ? $r_matched [1] : 0) : $a_rows;
    }

    /**
     * Получение текста ошибки
     * @return string текст ошибки
     */
    public function errtext() {
        return mysql_error();
    }

    /**
     * Возвращает номер ошибки запроса
     * @return int номер ошибки
     */
    public function errno() {
        return mysql_errno();
    }

    /**
     * Экранирование строки в условии запроса
     * @note Используйте только в крайнем случае!
     * Для подстановки переменных см. {@link db_parse}
     * @param string $value входное значение
     * @return string экранированное значение
     */
    public function esc($value) {
        $value = "'" . mysql_real_escape_string((string) $value) . "'";
        return $value;
    }

    /**
     * Экранирование строки для использование в LIKE
     * @note Используйте только в крайнем случае!
     * Для подстановки переменных см. {@link db_parse}
     * @param string $x входное значение
     * @return string экранированное значение
     */
    public function sesc($x) {
        return str_replace(array(
            "%",
            "_"), array(
            "\\%",
            "\\_"), mysql_real_escape_string($x));
    }

    /**
     * Версия MySQL
     * @return string версия 
     */
    public function version() {
        return mysql_get_server_info();
    }

    /**
     * Создание условия для полнотекстового поиска
     * @param string $value искомые слова
     * @param string|array $columns столбец\столбцы
     * @param bool $boolean поиск в логическом режиме
     * @return string условие
     */
    public function fulltext_search($columns, $value, $boolean) {
        if (is_array($columns))
            $columns = implode(',', array_map(array($this, "cesc"), $columns));
        else
            $columns = $this->cesc($columns);
        $where = 'MATCH(' . $columns . ') AGAINST(' . $this->esc($value) .
                ($boolean ? ' IN BOOLEAN MODE' : '') . ')';
        return $where;
    }

}

?>