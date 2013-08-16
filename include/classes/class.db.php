<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.db.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для работы с БД
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

if (file_exists(ROOT . 'include/dbconn.php'))
    include_once ROOT . 'include/dbconn.php';
include_once ROOT . 'include/classes/db/interface.db_core.php';
if (!defined("dbtype"))
    define("dbtype", "mysql");
include_once ROOT . 'include/classes/db/class.db.' . dbtype . '.php';

abstract class db_parse extends db_core {

    /**
     * Счётчик переменных для замены
     * @var int $i
     */
    private $i = 0;

    /**
     * Массив параметров запроса
     * @var array $query_params
     */
    private $query_params = array();

    /**
     * Не парсить?
     * @var bool $noparse
     */
    private $noparse = false;

    /**
     * Без префикса?
     * @var bool $noprefix
     */
    protected $noprefix = false;

    /**
     * Отключает префикс таблиц на 1 запрос
     * @return db $this
     */
    public function no_prefix() {
        $this->noprefix = true;
        return $this;
    }

    /**
     * Не парсить?
     * @return db $this
     */
    public function no_parse() {
        $this->noparse = true;
        return $this;
    }

    /**
     * Добавление параметра/параметров для запроса
     * @param mixed $param параметр
     * если массив все значения - параметры
     * @param mixed $_ ещё параметры?
     * @return db $this
     */
    public function p($param, $_ = null) {
        if (!is_null($_)) {
            $c = func_num_args();
            for ($i = 0; $i < $c; $i++)
                $this->p(func_get_arg($i));
        } elseif (is_array($param))
            $this->query_params = array_merge($this->query_params, array_values($param));
        else
            $this->query_params[] = $param;
        return $this;
    }

    /**
     * Подстановка экранированных переменных, вместо ? и @?
     * @param array $matches данные парсинга
     * @return string значение
     * @throws EngineException
     */
    private function parse_var($matches) {
        if (!isset($this->query_params[$this->i]))
            throw new EngineException('db_parse_not_enough_params');
        $v = $this->query_params[$this->i];
        $c = 1;
        if ($matches[1] == "@")
            $v = $this->sesc($v);
        elseif ($matches[2]) {
            $c = (int) $matches[2];
            $v = "";
            for ($i = 0; $i < $c; $i++)
                $v .= ($v ? ", " : "") . $this->esc($this->query_params[$this->i + $i]);
        }
        else
            $v = $this->esc($v);
        $this->i+=$c;
        return $v;
    }

    /**
     * Реализация parse_tablename для парсинга
     * @param string $name имя таблицы
     * @return string имя таблицы с префиксом
     */
    abstract protected function parse_tablename($name);

    /**
     * Постановка префикса в имя таблицы
     * @param array $matches данные парсинга
     * @return string значение
     */
    private function parse_table($matches) {
        return " " . $matches[1] . ' `' . $this->parse_tablename($matches[2]) . '`';
    }

    /**
     * Парсинг запроса выборки
     * @note преобразует из:
     * SELECT * FROM table 
     * JOIN `table2` ON n3=n1
     * WHERE n1=? AND n2 LIKE '@?' 
     * OR n3 IN(@5?)
     * в:
     * SELECT * FROM prefix_table
     * JOIN `prefix_table2` ON n3=n1
     * WHERE n1='escaped\"value\"' AND n2 LIKE 'value\"escaped\_for\_search\%\"'
     * OR n3 IN('v1','v2','v3','v4','v5')
     * @note Помимо таблиц после ключевых слов FROM и JOIN 
     * работает с таблицами после слова TABLE [IF [NOT] EXISTS]
     * @note НЕ РАБОТЕТ в "_cb_" значениях методов update/insert
     * @param string $query запрос
     * @return null
     * @throws EngineException
     */
    protected function parse_query(&$query) {
        if ($this->noparse) {
            $this->noparse = false;
            return;
        }
        $this->i = 0;
        try {
            if ($this->query_params)
                $query = preg_replace_callback('/(\@|\@(\d+))?\?/', array($this, "parse_var"), $query);
            if (dbprefix && !$this->noprefix)
                $query = preg_replace_callback('/\s+(from|join|table(?:\s+if\s+(?:not\s+)?exists)?)\s+`?(\w+)`?/i', array($this, "parse_table"), $query);
        } catch (EngineException $e) {
            throw new EngineException($e->getMessage(), $query);
        }
        $this->query_params = array();
        $this->noprefix = false;
    }

}

abstract class db_cache extends db_parse {

    /**
     * Параметры кеширования запроса
     * @var mixed $cache_params
     */
    protected $cache_params = null;

    /**
     * Своё имя для кешированного файла
     * @param string $name имя кеш-файла
     * @return db_cache $this
     */
    public function cname($name) {
        if (!$this->cache_params)
            $this->cache_params = array();
        $this->cache_params["n"] = $name;
        return $this;
    }

    /**
     * Относительный путь сохранения кеша
     * @param string $path путь
     * @return db_cache $this
     */
    public function cpath($path) {
        if (!$this->cache_params)
            $this->cache_params = array();
        $this->cache_params['p'] = $path;
        return $this;
    }

    /**
     * Своё время обвноления
     * @param int $time своё время обновления кешированного файла
     * @return db_cache $this
     */
    public function ctime($time) {
        if (!$this->cache_params)
            $this->cache_params = array();
        $this->cache_params['t'] = (int) $time;
        return $this;
    }

    /**
     * Ключи кеширования
     * @param string $key столбец ключа в кешированном массиве
     * @param string $value стобец значения в кешированном массиве
     * если указано значение "__array", то столбцы пишутся в массив
     * @return db_cache $this
     */
    public function ckeys($key, $value = 0) {
        if (!$this->cache_params)
            $this->cache_params = array();
        $this->cache_params['k'] = array($key => $value);
        return $this;
    }

    /**
     * Функция, получающая строки из запроса при его кешировании
     * @param string $function функция row или assoc
     * @return db_cache $this
     */
    public function cfunction($function = 'assoc') {
        if (!$this->cache_params)
            $this->cache_params = array();
        $this->cache_params['f'] = $function == 'row' ? 'row' : 'assoc';
        return $this;
    }

    /**
     * Кешировать запрос
     * @return db $this
     */
    public function cache() {
        $this->cache_params = true;
        return $this;
    }

}

final class db extends db_cache {

    /**
     * Свой обработчик ошибок БД
     * @var callback $error_handler
     */
    private $error_handler = null;

    /**
     * Статистика по запросам
     * @var array $query_stat
     */
    public $query_stat = array();

    /**
     * Не выводить ошибки? о_О
     * @var bool $no_error
     */
    private $no_error = false;

    /**
     * Ошибки без шаблона?
     * @var bool $nt_error
     */
    private $nt_error = false;

    /**
     * Не обнулять no_error и no_csupc по окончании запроса
     * @var bool $no_reset
     */
    private $no_reset = false;

    /**
     * ID последнего запроса
     * @var resid $last_resource
     */
    private $last_resource = null;

    /**
     * Последняя запрос для вставки
     * @var string $last_query
     */
    private $last_query = "";

    /**
     * Последняя таблица для вставки
     * @var string $last_table
     */
    private $last_table = "";

    /**
     * Имя базы данных для запроса
     * @var string $prdb
     */
    private $prdb = "";

    /**
     * Вставка/обновление/удаление/выборка таблицы с AS
     * @var string $astable
     */
    private $astable = "";

    /**
     * Добавление IGNORE в UPDATE/INSERT
     * @var bool $ignore
     */
    private $ignore = false;

    /**
     * Возвращает имя таблицы с префиксом
     * @param string $name имя таблицы
     * @return string имя таблицы с префиксом
     */
    public static function table($name) {
        return dbprefix . $name;
    }

    /**
     * Реализация parse_tablename для парсинга
     * @param string $name имя таблицы
     * @return string имя таблицы с префиксом
     */
    protected function parse_tablename($name) {
        return self::table($name);
    }

    /**
     * Экранирование строки в условии запроса
     * @note Используйте только в крайнем случае!
     * Для подстановки переменных см. {@link db_parse}
     * @param string $value входное значение
     * @return string экранированное значение
     */
    public function esc($value) {
        if ($value === false)
            $value = '0';
        $value = parent::esc($value);
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
        if ($x === false)
            $x = '0';
        $x = parent::sesc($x);
        return $x;
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
     * Вставка/обновление/удаление/выборка таблицы с AS
     * @param string $as as что?
     * @return db $this
     */
    public function as_table($as) {
        $this->astable = $as;
        return $this;
    }

    /**
     * Запрос к базе, отличной от данной
     * @param string $name имя БД
     * @return db $this
     */
    public function prepend_db($name) {
        $this->prdb = $name;
        return $this;
    }

    /**
     * Добавление IGNORE в UPDATE/INSERT
     * @return db $this
     */
    public function ignore() {
        $this->ignore = true;
        return $this;
    }

    /**
     * Получение имени таблицы, экранированной, с префиксом и постфиксом
     * @param string $table имя таблицы
     * @return string имя с префиксом и постфиксом
     */
    private function get_tname($table) {
        if (!$this->noprefix)
            $table = self::table($table);
        $r = '`' . $table . '`';
        if ($this->prdb)
            $r = '`' . $this->prdb . '`.' . $r;
        if ($this->astable)
            $r .= ' AS `' . $this->astable . '`';
        $this->prepend_db('');
        $this->as_table('');
        $this->noprefix = false;
        return $r;
    }

    /**
     * Получение ID последнего запроса
     * @return resid ID последнего запроса
     */
    public function get_lastres() {
        return $this->last_resource;
    }

    /**
     * Отключает|включает обнуление no_error по окончании запроса
     * @param bool $state статус?
     * @return db $this
     */
    public function no_reset($state = true) {
        $this->no_reset = (bool) $state;
        return $this;
    }

    /**
     * Отключает|включает вывод ошибки на 1 запрос
     * @param bool $state статус?
     * @return db $this
     */
    public function no_error($state = true) {
        $this->no_error = (bool) $state;
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
     * Свой обработчик ошибок
     * @param callback $handler функция обработчика
     * @return db $this
     */
    public function errhandler($handler) {
        $this->error_handler = $handler;
        return $this;
    }

    /**
     * Вывод бэктрейса
     * @return string бэктрейс
     */
    private function print_backtrace() {
        $backtrace = debug_backtrace();
        foreach ($backtrace as $value) {
            if (!$value ['file'])
                continue;
            //if (!($value ['file'] == __FILE__ && $value ['line'] == __LINE__)) {
            $path = cut_path($value ['file']);
            $debug [] = "<b>" . lang::o()->v('file') . ":</b>&nbsp;" . $path . "<br>\n
			<b>" . lang::o()->v('line') . ":</b>&nbsp;" . $value ['line'];
            //}
        }
        $backtrace = implode("<br><font size=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;&#8659;</font><br>", $debug);
        return $backtrace;
    }

    /**
     * Вывод ошибки последнего запроса к БД
     * @param string $query запрос
     * @return null
     */
    public function err($query = null) {
        if ($this->error_handler) {
            $eh = $this->error_handler; // на случай, если php посчитает это за метод
            return $eh($query);
        }
        if ($this->errno() == 145 && class_exists('furl')) {
            preg_match('/Table \'(.*)\' is marked as crashed and should be repaired$/siu', $this->errtext(), $matches);
            //$table = substr ( $matches [1], strrpos ( $matches [1], "\\" ) + 1 );
            $table = mb_substr($matches [1], mb_strrpos($matches [1], "/") + 1);
            $this->no_parse()->query("REPAIR TABLE " . $table);
            furl::o()->location('', 1);
        }
        $error = $special ? $special : $this->errtext();
        $emess = lang::o()->v('db_error') . ": " . $error . (IN_DEVELOPMENT ? '(' . $query . ')' : "");
        if (!$this->nt_error && class_exists("tpl") && $this->connected) {
            tpl::o()->assign('backtrace', $this->print_backtrace());
            n("message")->stitle("db_error")->error($emess);
        }
        else
            print($emess);
        die();
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
            if (is_array($k_v)) {
                $k = key($k_v);
                $v = current($k_v);
                $k = is_numeric($k) ? 0 : $k;
                $v = is_numeric($v) ? 0 : $v;
            }
            else
                $k = $v = null;
            $key = $k ? $row[$k] : null;
            if (!is_null($key) && $v === '__array') {
                $rows[$key][] = $row;
                continue;
            }
            $row = (!$v ? $row : $row[current($k_v)]);
            if (!is_null($key))
                $rows[$key] = $row;
            else
                $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Таймер запроса
     * @note требуется вызывать 2 раза, в начале и конце запроса
     * @param string $query запрос
     * @param bool $over запрос из update/insert/delete/etc.?
     * @return null
     */
    private function query_timer($query = "", $over = true) {
        if (defined('INANNOUNCE'))
            return;
        $e = end($this->query_stat);
        if (!$e || !$e["start"])
            $this->query_stat [] = array(
                "start" => timer(),
                "over" => $over);
        else {
            if ($e["over"] && !$over)
                return;
            $current = &$this->query_stat [key($this->query_stat)];
            $query_end_time = timer(); // End time
            $query_time = ($query_end_time - $e['start']);
            $query_time = number_format($query_time, 8);
            unset($current['start']);
            unset($current['over']);
            $current = array(
                "seconds" => $query_time,
                "query" => $query);
        }
    }

    /**
     * Выполнение запроса к БД
     * @param string $query строка запроса
     * @return resid|array ID запроса или, если запрос кешируется,
     * массив из всех row|assoc, в зависимости от последнего параметра,
     * для данного запроса.
     */
    public function query($query) {
        $query = trim($query);
        $cparams = $this->cache_params;
        $this->cache_params = null;
        if ($cparams && class_exists('cache')) {
            $cached = true;
            if (is_array($cparams)) {
                $my_cache_name = $cparams['n'];
                $pathto = $cparams['p'];
                $mytime = $cparams['t'];
                $k_v = $cparams['k'];
                $function = $cparams['f'];
            } elseif (is_string($cparams))
                $my_cache_name = $cparams;
        }
        else
            $cached = false;
        if (!$function || ($function != 'row' && $function != 'assoc'))
            $function = 'assoc';
        if ($cached) {
            if (!$my_cache_name)
                $name = $pathto . 'sql_' . md5($query);
            else
                $name = $pathto . $my_cache_name;
            $result = cache::o()->read($name, $mytime);
        }
        if (!is_array($result)) {
            $this->query_timer("", false);
            $this->parse_query($query);
            $result = parent::query($query);
            if (!$result && !$this->no_error)
                $this->err($query);
            $this->last_resource = $result;
            if ($cached) {
                $rows = $this->fetch2array($result, $function, $k_v);
                $result = $rows;
                cache::o()->write($rows);
            }
            $this->query_timer($query, false);
        }
        if (!$this->no_reset)
            $this->no_error = false;
        return $result;
    }

    // Использование методов ниже не обязательно, но крайне желательно!

    /**
     * Подсчёт кол-ва значений в данной таблице
     * @param string $table таблица
     * @param string $where условие
     * @return int кол-во значений
     */
    public function count_rows($table, $where = null) {
        return $this->act_rows($table, '*', 'COUNT', $where);
    }

    /**
     * Функция выполнения математических действий со столбцом таблицы(MAX, MIN, AVG, SUM, COUNT)
     * @param string $table имя таблицы
     * @param string $column имя столбца
     * @param string $act действие(max, min, avg, sum)
     * @param string $where условие для запроса
     * @return float|integer значение
     */
    public function act_rows($table, $column, $act, $where = null) {
        $this->query_timer();
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
        $this->parse_query($where);
        $query = "SELECT " . $act . "(" . ($column != "*" ? "`" . $column . "`" : "*") . ")
            FROM " . $this->get_tname($table) . ($where ? " WHERE " . $where : "");
        $r = $this->no_parse()->query($query);
        $a = $this->fetch_row($r);
        $this->query_timer($query);
        return $a [0];
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
        $this->query_timer();
        $this->parse_query($where);
        $query = "SELECT * FROM " . $this->get_tname($table) . ' WHERE ' . $where .
                ($orderby ? " ORDER BY " . $orderby : "");
        $r = $this->no_parse()->query($query);
        $c = 0;
        while ($row = $this->fetch_assoc($r)) {
            if ($row[$col] == $value)
                break;
            $c++;
        }
        $this->query_timer($query);
        return $c;
    }

    /**
     * Вставка значений в таблицу
     * @param array $columns массив столбец=>значение
     * @param string $table имя таблицы
     * @param bool $multi мультивставка значений
     * @return null|int ID новой строки
     */
    public function insert($columns, $table, $multi = false) {
        if (!$multi)
            $this->query_timer();
        $cols = null;
        $vals = null;
        foreach ($columns as $col => $val) {
            if (!is_numeric($col))
                $cols .= ( $cols ? ", " : "") . "`" . $col . "`";
            $vals .= ( $vals ? ", " : "") . $this->esc($val);
        }
        $a = '';
        if ($this->ignore) {
            $a = "IGNORE";
            $this->ignore = false;
        }
        $st = "INSERT " . $a . " INTO " . $this->get_tname($table) . ($cols ? " (" . $cols . ")" : "") . " VALUE";
        if ($multi) {
            if ($this->last_table != $table || !$this->last_query)
                $this->last_query = $st . "S(" . $vals . ")";
            elseif ($this->last_query)
                $this->last_query .= ",(" . $vals . ")";
            $this->last_table = $table;
        } else {
            $query = $st . "(" . $vals . ")";
            $this->no_parse()->query($query);
            $this->query_timer($query);
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
     * Обновление значений в таблице
     * @param array $columns массив столбец=>значение, если указывается _cb_ перед именем столбца,
     *                         то экранирование значения не происходит.
     * @param string $table имя таблицы
     * @param string $suffix суффикс запроса(условие, сортировка, лимиттинг)
     * @return int кол-во изменённых строк
     */
    public function update($columns, $table, $suffix = null) {
        $this->query_timer();
        $vals = null;
        foreach ($columns as $col => $val) {
            $esc = mb_strpos($col, "_cb_") === false;
            if (!$esc)
                $col = mb_substr($col, 4);
            $vals .= ( $vals ? ", " : "") . "`" . $col . "`=" . ($esc ? $this->esc($val) : $val);
        }
        $a = '';
        if ($this->ignore) {
            $a = "IGNORE";
            $this->ignore();
        }
        $this->parse_query($suffix);
        $query = "UPDATE " . $a . " " . $this->get_tname($table) . " SET " . $vals . " " . $suffix;
        $this->no_parse()->query($query);
        $this->query_timer($query);
        return $this->affected_rows();
    }

    /**
     * Удаление значений из таблицы
     * @param string $table имя таблицы
     * @param string $suffix суффикс запроса(условие, сортировка, лимиттинг)
     * @return int кол-во удалённых строк
     */
    public function delete($table, $suffix = null) {
        $this->query_timer();
        $this->parse_query($suffix);
        $query = "DELETE FROM " . $this->get_tname($table) . " " . $suffix;
        $this->no_parse()->query($query);
        $this->query_timer($query);
        return $this->affected_rows();
    }

    /**
     * Удаление ВСЕХ значений из таблицы
     * @param string $table имя таблицы
     * @return int кол-во удалённых строк
     */
    public function truncate_table($table) {
        $this->no_parse()->query("TRUNCATE TABLE " . $this->get_tname($table));
        return $this->affected_rows();
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var db $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        
    }

    /**
     * Не клонируем
     * @return null 
     */
    private function __clone() {
        
    }

    /**
     * И не десериализуем
     * @return null 
     */
    private function __wakeup() {
        
    }

    /**
     * Получение объекта класса
     * @return db $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>