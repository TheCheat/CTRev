<?php

/**
 * Project:            	CTRev
 * File:                class.db.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для работы с БД
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class db { // не final, ибо err переопределяется в аннонсере

    /**
     * Подлючена ли БД?
     * @var bool
     */

    protected $connected = false;

    /**
     * Статистика по запросам
     * @var array
     */
    public $query_stat = array();

    /**
     * Не выводить ошибки? о_О
     * @var bool
     */
    protected $no_error = false;

    /**
     * Ошибки без шаблона?
     * @var bool
     */
    protected $nt_error = false;

    /**
     * Не обнулять no_error и no_csupc по окончании запроса
     * @var bool
     */
    protected $no_reset = false;

    /**
     * ID последнего запроса
     * @var resid
     */
    protected $last_resource = null;

    /**
     * Последняя запрос для вставки
     * @var string
     */
    protected $last_query = "";

    /**
     * Последняя таблица для вставки
     * @var string
     */
    protected $last_table = "";

    /**
     * Коннект к БД
     * @return null
     */
    public function connect() {
        include_once ROOT . 'include/dbconn.php';
        if ($this->connected)
            return;
        @mysql_connect($dbhost, $dbuser, $dbpass) or $this->err("mysql_connect");

        mysql_select_db($dbname) or $this->err("mysql_select_db");

        $q = "SET NAMES " . $charset . " COLLATE " . $charset . '_general_ci';
        mysql_query($q) or $this->err($q);
        $q = "SET character_set_client=" . $this->esc($charset);
        mysql_query($q) or $this->err($q);
        $q = "SET character_set_results=" . $this->esc($charset);
        mysql_query($q) or $this->err($q);
        $q = "SET collation_connection=@@collation_database;";
        mysql_query($q) or $this->err($q);
        register_shutdown_function("mysql_close");
        $this->connected = true;
    }

    /**
     * Получение ID последнего запроса
     * @return resid ID последнего запроса
     */
    public function get_lastres() {
        return $this->last_resource;
    }

    /**
     * Подсчёт кол-ва значений в данной таблице
     * @param string $table таблица
     * @param string $where условие
     * @return int кол-во значений
     */
    public function count_rows($table, $where = null) {
        return $this->act_row($table, '*', 'COUNT', $where);
    }

    // Использование 4 методов ниже не обязательно, но крайне желательно!

    /**
     * Удаление ВСЕХ значений из таблицы
     * @param string $table имя таблицы
     * @return int кол-во удалённых строк
     */
    public function truncate_table($table) {
        $this->query("TRUNCATE TABLE `" . $table . "`");
        return $this->affected_rows();
    }

    /**
     * Удаление значений из таблицы
     * @param string $table имя таблицы
     * @param string $suffix суффикс запроса(условие, сортировка, лимиттинг)
     * @return int кол-во удалённых строк
     */
    public function delete($table, $suffix = null) {
        $this->query("DELETE FROM `" . $table . "` " . $suffix);
        return $this->affected_rows();
    }

    /**
     * Обновление значений в таблице
     * @param array $columns массив столбец=>значение, если указывается _cb_ перед именем столбца,
     *                         то экранирование значения не происходит.
     * @param string $table имя таблицы
     * @param string $suffix суффикс запроса(условие, сортировка, лимиттинг)
     * @return int кол-во изменённых строк
     */
    public function update($columns, $table, $suffix = null) {
        $vals = null;
        foreach ($columns as $col => $val) {
            $esc = mb_strpos($col, "_cb_") === false;
            if (!$esc)
                $col = mb_substr($col, 4);
            $vals .= ( $vals ? ", " : "") . "`" . $col . "`=" . ($esc ? $this->esc($val) : $val);
        }
        $this->query("UPDATE `" . $table . "` SET " . $vals . " " . $suffix);
        return $this->affected_rows();
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
     * Вставка значений в таблицу
     * @param array $columns массив столбец=>значение
     * @param string $table имя таблицы
     * @param bool $multi мультивставка значений
     * @return null|int ID новой строки
     */
    public function insert($columns, $table, $multi = false) {
        $cols = null;
        $vals = null;
        foreach ($columns as $col => $val) {
            if (!is_numeric($col))
                $cols .= ( $cols ? ", " : "") . "`" . $col . "`";
            $vals .= ( $vals ? ", " : "") . $this->esc($val);
        }
        $st = "INSERT INTO `" . $table . "`" . ($cols ? " (" . $cols . ")" : "") . " VALUE";
        if ($multi) {
            if ($this->last_table != $table || !$this->last_query)
                $this->last_query = $st . "S(" . $vals . ")";
            elseif ($this->last_query)
                $this->last_query .= ",(" . $vals . ")";
            $this->last_table = $table;
        } else {
            $this->query($st . "(" . $vals . ")");
            return $this->insert_id();
        }
    }

    /**
     * Сохранение последней таблицы
     * @return int ID новой строки
     */
    public function save_last_table() {
        if ($this->last_query && $this->last_table)
            $this->query($this->last_query);
        $this->last_table = $this->last_query = "";
        return $this->insert_id();
    }

    /**
     * Отключает|включает обнуление no_error по окончании запроса
     * @return db $this
     */
    public function no_reset() {
        $this->no_reset = !$this->no_reset;
        return $this;
    }

    /**
     * Отключает|включает вывод ошибки на 1 запрос
     * @return db $this
     */
    public function no_error() {
        $this->no_error = !$this->no_error;
        return $this;
    }

    /**
     * Выводить ошибки без шаблона и бэктрейса?
     * @param bool $tpld так выводить или нет?
     * @return db $this
     */
    public function nt_error($tpld = false) {
        $this->nt_error = !$tpld;
        return $this;
    }

    /**
     * Выполнение запроса к БД
     * @global cache $cache
     * @param string $query строка запроса
     * @param array|string|bool $cparams true - если кешировать, если string -
     * аналогично массив с ключом n и значением этой строки, либо массив параметров кеша,
     * ибо их достаточно много
     * ключ значение действие
     * n    string   своё имя для кешированного файла
     * p    string   относительный путь сохранения кеша
     * t    int      своё время обновления кешированного файла
     * k    array    если указан ключ в массиве - столбец ключа в кешированном массиве
     *               если указано значение - стобец значения в кешированном массиве
     *               если указано значение "__array", то столбцы пишутся в массив
     * f    string   функция, получающая строки из запроса при его кешировании
     *               (row|assoc)
     *
     * @return resid|array ID запроса или, если запрос кешируется,
     * массив из всех row|assoc, в зависимости от последнего параметра,
     * для данного запроса.
     */
    public function query($query, $cparams = array()) {
        global $cache;
        $query = trim($query);
        if ($cparams && $cache) {
            $cached = true;
            if (is_array($cparams)) {
                $my_cache_name = $cparams['n'];
                $pathto = $cparams['p'];
                $mytime = $cparams['t'];
                $k_v = $cparams['k'];
                $function = $cparams['f'];
            } elseif (is_string($cparams))
                $my_cache_name = $cparams;
        } else
            $cached = false;
        if (!$function || ($function != 'row' && $function != 'assoc'))
            $function = 'assoc';
        //$cached = $config->v('cache_on') && $cached;
        if ($cached) {
            if (!$my_cache_name)
                $name = $pathto . 'sql_' . md5($query);
            else
                $name = $pathto . $my_cache_name;
            $result = $cache->read($name, $mytime);
        }
        if (!is_array($result)) {
            if (!defined('INANNOUNCE'))
                $query_start_time = timer(); // Start time
            $result = @mysql_query($query);
            if (!$result && !$this->no_error)
                $this->err($query);
            $this->last_resource = $result;
            if (!defined('INANNOUNCE')) {
                $query_end_time = timer(); // End time
                $query_time = ($query_end_time - $query_start_time);
                $querytime = $querytime + $query_time;
                $query_time = number_format($query_time, 8);
                $this->query_stat [] = array(
                    "seconds" => $query_time,
                    "query" => $query);
            }
            if ($cached) {
                /* if ($this->num_rows($result) == 1 && !$k_v) {
                  $function = 'fetch_' . $function;
                  $rows = $this->$function($result);
                  } else */
                $rows = $this->fetch2array($result, $function, $k_v);
                $result = $rows;
                $cache->write($rows);
            }
        }
        if (!$this->no_reset)
            $this->no_error = false;
        $this->cache_params = null;
        return $result;
    }

    /**
     * Получение массива всех строк из запроса
     * @param resid $result ID запроса
     * @param string $type тип строки(в виде ассоц. масива(assoc) или массива где ключи - целые числа(row))
     * @param array $k_v если указан ключ в массиве - столбец ключа в массиве
     *               если указано значение - стобец значения в массиве
     *               если указано значение "__array", то столбцы пишутся в массив
     * @return array массива всех строк из запроса
     */
    public function fetch2array($result, $type = "assoc", $k_v = null) {
        if ($type != "assoc" && $type != "row")
            $type = "assoc";
        $f = "fetch_" . $type;
        $rows = array();
        while ($row = $this->$f($result)) {
            $key = is_array($k_v) && !is_int(key($k_v)) ? $row[key($k_v)] : null;
            if (!is_null($key) && current($k_v) === '__array') {
                $rows[$key][] = $row;
                continue;
            }
            $row = (!is_array($k_v) || is_int(current($k_v)) ? $row : $row[current($k_v)]);
            if (!is_null($key))
                $rows[$key] = $row;
            else
                $rows[] = $row;
        }
        return $rows;
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
     * Нахождение позиции данного значения при данных условиях с сортировкой
     * @param string $table имя таблицы
     * @param callback_str $where условие выполняемое для всех значений
     * @param string $col искомый столбец
     * @param mixed $value значение искомого элемента
     * @param callback_str $orderby сортировка
     * @return int позиция данного значения(начиная с 0)
     */
    public function get_current_pos($table, $where, $col, $value, $orderby = null) {
        $r = $this->query("SELECT * FROM `" . $table . '` WHERE ' . $where .
                ($orderby ? " ORDER BY " . $orderby : ""));
        $c = 0;
        while ($row = $this->fetch_assoc($r)) {
            if ($row[$col] == $value)
                break;
            $c++;
        }
        return $c;
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
     * Вывод бэктрейса
     * @global lang $lang
     * @return string бэктрейс
     */
    protected function print_backtrace() {
        global $lang;
        $backtrace = debug_backtrace();
        foreach ($backtrace as $value) {
            if (!$value ['file'])
                continue;
            //if (!($value ['file'] == __FILE__ && $value ['line'] == __LINE__)) {
            $path = cut_path($value ['file']);
            $debug [] = "<b>" . $lang->v('file') . ":</b>&nbsp;" . $path . "<br>\n
			<b>" . $lang->v('line') . ":</b>&nbsp;" . $value ['line'];
            //}
        }
        $backtrace = implode("<br><font size=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;&#8659;</font><br>", $debug);
        return $backtrace;
    }

    /**
     * Вывод ошибки последнего запроса к БД
     * @global furl $furl
     * @global lang $lang
     * @global tpl $tpl
     * @param string $query
     * @return null
     */
    public function err($query = null) {
        global $furl, $lang, $tpl;
        if ($this->errno() == 145 && $furl) {
            preg_match('/Table \'(.*)\' is marked as crashed and should be repaired$/siu', mysql_error(), $matches);
            //$table = substr ( $matches [1], strrpos ( $matches [1], "\\" ) + 1 );
            $table = mb_substr($matches [1], mb_strrpos($matches [1], "/") + 1);
            $this->query("REPAIR TABLE " . $table, true);
            $furl->location('', 1);
        }
        $error = mysql_error();
        $emess = $lang->v('db_error') . ": " . $error . (IN_DEVELOPMENT ? '(' . $query . ')' : "");
        if (!$this->nt_error && $tpl && $this->connected) {
            $tpl->assign('backtrace', $this->print_backtrace());
            error($emess, "", 'db_error');
        } else
            print($emess);
        die();
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
     * @param string $value входное значение
     * @return string экранированное значение
     */
    public function esc($value) {
        $value = "'" . mysql_real_escape_string((string) $value) . "'";
        return $value;
    }

    /**
     * "Экранирование" имени столбца
     * @param string $column имя столбца
     * @return string `имя столбца`
     */
    public function cesc($column) {
        return '`' . $column . '`';
    }

    /**
     * Экранирование строки для использование в LIKE
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
     * Функция выполнения математических действий со столбцом таблицы(MAX, MIN, AVG, SUM, COUNT)
     * @param string $table имя таблицы
     * @param string $column имя столбца
     * @param string $act действие(max, min, avg, sum)
     * @param string $where условие для запроса
     * @return float|integer значение
     */
    public function act_row($table, $column, $act, $where = null) {
        $act = mb_strtoupper($act);
        switch ($act) {
            case "MAX":
            case "MIN":
            case "AVG":
            case "SUM":
            case "COUNT":
                break;
            default:
                $act = "SUM";
                break;
        }
        $r = $this->query("SELECT " . $act . "(" . ($column != "*" ? "`" . $column . "`" : "*") . ")
            FROM " . $table . ($where ? " WHERE " . $where : ""));
        $a = $this->fetch_row($r);
        return $a [0];
    }

    /**
     * Подсчёт кол-ва значений в таблице
     * @param string $table имя таблицы
     * @param string $where условие для запроса
     * @return int кол-во значений в таблице
     */
    public function row_count($table, $where = "") {
        return $this->act_row($table, "*", "COUNT", $where);
    }

    /**
     * Версия MySQL
     * @return string версия 
     */
    public function version() {
        return mysql_get_server_info();
    }

}

?>