<?php

/**
 * Project:             CTRev
 * File:                convert.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Конвертация данных.
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

require_once ROOT . 'include/classes/class.cache.php';
require_once ROOT . 'include/classes/class.modsettings.php';
require_once ROOT . 'include/classes/class.plugins.php';
require_once ROOT . 'include/classes/class.config.php';
require_once ROOT . 'include/classes/class.stats.php';

$db->connect();
$config = new config();
$cache = new cache();
$cache->init();
$stats = new stats();
$modsettings = new modsettings();
$plugins = new plugins();

class convert {
    /**
     * Путь к файлу таблицы с расширением
     * @const string fpath
     */

    const fpath = 'install/database/%s.conv';
    /**
     * Путь к файлу геттеров с расширением
     * @const string fpath
     */
    const gpath = 'install/include/convert/%s.php';
    /**
     * Имя плагина конвертации
     * @const string pname
     */
    const pname = 'convert_%s';
    /**
     * Временное поле stats для макс. ID категорий
     * @const string stfield
     */
    const stfield = 'convert_maxcatid';

    /**
     * Таблица конвертации
     * @var string
     */
    private $cfile = 'cyberhype';

    /**
     * Кол-во записей за раз
     * @var int
     */
    private $peronce = 100;

    /**
     * Массив запрашиваемых столбцов
     * @var array
     */
    private $columns = array();

    /**
     * Массив вставок
     * @var array
     */
    private $insert = array();

    /**
     * Временные данные
     * @var mixed
     */
    private $tmp = null;

    /**
     * Класс геттеров
     * @var object
     */
    private $getter = null;

    /**
     * Конвертируемая база данных
     * @var string
     */
    private $db = 'cyberhype';

    /**
     * Сопоставление групп
     * @var string
     */
    private $groups = '';

    /**
     * Уже конвертировано?
     * @var bool
     */
    private $converted = false;

    /**
     * Конструктор
     * @param bool $c конвертировано?
     * @return null
     */
    public function __construct(&$c) {
        $this->request_settings();
        $c = $this->converted;
    }

    /**
     * Инициализация AJAX части конвертации
     * @global cache $cache
     * @global lang $lang
     * @global tpl $tpl
     * @global config $config
     * @return null 
     */
    public function init() {
        global $cache, $lang, $tpl, $config;
        $lang->get('install/convert');
        $cache->init();
        if ($_GET['check']) {
            if (INSTALL_PAGE == "database")
                $this->check_settings();
            die('OK!');
        } else {
            $tpl->assign("config", $config);
            if (INSTALL_PAGE == "database")
                $this->show_database();
            elseif (INSTALL_PAGE == "convert")
                $this->show_convert();
            $tpl->display("convert/" . INSTALL_PAGE);
        }
    }

    /**
     * Запрос настроек конвертации
     * @global db $db
     * @return null
     */
    private function request_settings() {
        global $db;
        $r = $db->no_error()->query('SELECT `field`, `value` FROM `convert`');
        if (!$r)
            return;
        while (list($field, $value) = $db->fetch_row($r))
            $this->$field = $value;
    }

    /**
     * Отображение настроек БД
     * @global tpl $tpl
     * @global db $db
     * @return null
     */
    private function show_database() {
        global $tpl, $db;
        require_once ROOT . 'include/classes/class.input.php';
        $input = new input();
        $cfiles = $input->select_folder("file", 'install/database', $this->cfile, false, false, "/^(.*)\.conv$/siu", 1);
        $tpl->assign('cfiles', $cfiles);
        $r = $db->query('SELECT id, name FROM groups');
        $tpl->assign('groups', $db->fetch2array($r, 'assoc', array('id' => 'name')));
    }

    /**
     * Отображение конвертации
     * @global plugins $plugins
     * @global lang $lang
     * @global stats $stats
     * @return null
     */
    private function show_convert() {
        global $plugins, $lang, $db, $stats;
        require_once ROOT . sprintf(self::gpath, $this->cfile);
        $this->getter = new get_convert($this->db, unserialize($this->groups));
        if ($_GET['convert']) {
            if ($_GET['finish']) {
                $db->update(array('value' => '1'), 'convert', 'WHERE field="converted" LIMIT 1');
                $stats->remove(self::stfield);
                $pname = sprintf(self::pname, $this->db);
                $plugins->manager->delete($pname);
                $plugins->manager->add($pname);
                printf($lang->v('convert_plugin_installed'));
                print("<script type='text/javascript'>stop_loading();</script>");
                die();
            }
            $this->parse($_POST['toffset'], $_POST['loffset']);
            die();
        }
    }

    /**
     * Проверка настроек
     * @global lang $lang
     * @global db $db
     * @return null
     */
    private function check_settings() {
        global $lang, $db;
        $peronce = (int) $_POST['peronce'];
        if ($peronce < 20)
            $peronce = 20;
        $cdb = $_POST['db'];
        $cfile = $_POST['file'];
        $r = $db->query("SHOW DATABASES LIKE " . $db->esc($cdb));
        if (!$db->num_rows($r) || !$cdb)
            die(sprintf($lang->v('convert_wrong_db'), $cdb));
        if (!file_exists(ROOT . sprintf(self::fpath, $cfile)) || !file_exists(ROOT . sprintf(self::gpath, $cfile)))
            die(sprintf($lang->v('convert_cfile_not_exists'), $cfile));
        $db->query('DROP TABLE IF EXISTS `convert`');
        $db->query('CREATE TABLE `convert`(`field` VARCHAR( 200 ) NOT NULL,`value` TEXT NOT NULL, PRIMARY KEY ( `field` ))');
        $groups = array();
        foreach ((array) $_POST['groups'] as $id => $grs) {
            $id = (int) $id;
            if (!$id)
                continue;
            $grs = explode("|", $grs);
            $c = count($grs);
            for ($i = 0; $i < $c; $i++) {
                $gr = (int) trim($grs[$i]);
                if (!$gr)
                    continue;
                $groups[$gr] = $id;
            }
        }
        $i = array('peronce' => $peronce,
            'db' => $cdb,
            'cfile' => $cfile,
            'groups' => serialize($groups),
            'converted' => '0');
        foreach ($i as $f => $v)
            $db->insert(array("field" => $f, "value" => $v), "convert", true);
        $db->save_last_table();
    }

    /**
     * Таблицы конвертации
     * @return string содержимое
     */
    private function convert_tables() {
        return file_get_contents(sprintf(self::fpath, $this->cfile));
    }

    /**
     * Очистка таблиц перед вставкой
     * @global db $db
     * @global lang $lang
     * @global stats $stats
     * @return null
     */
    private function truncate_tables() {
        global $db, $lang, $stats;
        $content = $this->convert_tables();
        $c = preg_match_all('/\s*?^table\s+(\w+)/miu', $content, $matches);
        for ($i = 0; $i < $c; $i++)
            $db->truncate_table($matches[1][$i]);
        $stats->remove(self::stfield);
        printf($lang->v('convert_truncated_tables'), $c);
    }

    /**
     * Парсинг таблицы конвертации
     * @global cache $cache
     * @global db $db
     * @param int $toffset позиция таблицы для конвертации
     * @param int $loffset позиция значений
     * @return null
     */
    private function parse($toffset = 0, $loffset = 0) {
        global $cache, $db;
        $toffset = (int) $toffset;
        $loffset = (int) $loffset;
        if (!$toffset && !$loffset)
            $this->truncate_tables();
        $a = array();
        $finish = "<script type='text/javascript'>continue_convert(0, 0, true);</script>";
        $cachefile = 'convert/cparse-off' . $toffset;
        if (!($a = $cache->read($cachefile))) {
            $content = $this->convert_tables();
            $c = preg_match_all('/(^)\s*?table\s+(\w+)\/([\w\s,]+?)(?:\s*?\:\s*?(\w+))?(?:\s*?\?(.*?))?\s*?($)/miu', $content, $matches, PREG_OFFSET_CAPTURE, $toffset);
            $i = 0;
            if (!$matches)
                die($finish);
            $table = $matches[2][$i][0];
            $orderby = $matches[3][$i][0];
            $ftable = $matches[4][$i][0];
            if (!$ftable)
                $ftable = $table;
            $cond = trim($matches[5][$i][0]);
            $pos = $matches[6][$i][1];
            $i++;
            if ($matches[1][$i]) {
                $ntoffset = $matches[1][$i][1];
                $len = $ntoffset - $pos;
            }
            $data = trim($len ? mb_substr($content, $pos, $len) : mb_substr($content, $pos));
            $this->parse_columns($data);
            $a = array($table, $orderby, $ftable, $cond, $ntoffset, $this->columns, $this->insert);
            $cache->write($a, $cachefile);
        } else
            list($table, $orderby, $ftable, $cond, $ntoffset, $this->columns, $this->insert) = $a;
        $this->select4insert($table, $orderby, $ftable, $cond, $loffset);
        $c = $db->prepend_db($this->db)->count_rows($ftable, $cond);
        if ($c <= $loffset + $this->peronce) {
            if (!$ntoffset)
                die($finish);
            else
                die("<script type='text/javascript'>continue_convert(" . $ntoffset . ", '0');</script>");
        } else
            die("<script type='text/javascript'>continue_convert(" . $toffset . ", " . ($loffset + $this->peronce) . ");</script>");
    }

    /**
     * Предпарсинг аргументов
     * @param array $args массив аргументов
     * @param array $row массив значений
     * @return null
     */
    private function prepare_args(&$args, $row) {
        $c = count($args);
        $this->tmp = $row;
        $rexp = '\{([^\{\}]+)\}';
        for ($i = 0; $i < $c; $i++) {
            $arg = &$args[$i];
            if (preg_match('/^' . $rexp . '$/siu', $arg, $matches))
                $arg = $this->prepare_args_callback($matches, true);
            else
                $arg = preg_replace_callback('/' . $rexp . '/siu', array($this, 'prepare_args_callback'), $arg);
        }
    }

    /**
     * Callback функция для препарсинга аргументов
     * @global db $db
     * @param array $matches спарсенный массив данных
     * @param bool $nesc true, если не нужно экранировать значение столбца
     * @return mixed массив всех значений столбцов или одного
     */
    private function prepare_args_callback($matches, $nesc = false) {
        global $db;
        $row = $this->tmp;
        $m = $matches[1];
        $m = preg_replace('/`(\w+)`/iu', '$1', $m);
        if ($nesc && mb_strtolower($m) == '$row')
            return $row;
        else
            return $nesc ? $row[$m] : $db->esc($row[$m]);
    }

    /**
     * Выборка и вставка значений из таблицы
     * @global db $db
     * @global lang $lang
     * @param string 
     * @param string $table имя таблицы вставки
     * @param string $orderby сортировка таблицы выборки
     * @param string $ftable имя таблицы выборки
     * @param string $cond условие для выборки
     * @param int $limit ограничение
     * @return array массив значений
     */
    private function select4insert($table, $orderby, $ftable, $cond, $limit) {
        global $db, $lang;
        $query = "SELECT ";
        $c = count($this->columns);
        for ($i = 0; $i < $c; $i++)
            $query .= ($i ? ', ' : '') . $this->columns[$i];
        $orderby = '`' . implode('`, `', array_map('trim', explode(',', $orderby))) . '`';
        $query .= " FROM `" . $this->db . "`.`" . $ftable . "`" . ($cond ? " WHERE " . $cond : "") . "
            ORDER BY " . $orderby . "
            LIMIT " . $limit . ',' . $this->peronce;
        $r = $db->no_error()->query($query);
        if ($db->errno()) {
            printf($lang->v('convert_select_error'), $ftable, $db->errno(), $db->errtext());
            die();
        }
        while ($row = $db->fetch_assoc($r))
            $db->ignore()->insert($this->insert($row), $table, true);
        $db->no_error()->save_last_table();
        if ($db->errno()) {
            printf($lang->v('convert_insert_error'), $table, $db->errno(), $db->errtext());
            die();
        }
        printf($lang->v('convert_inserted_table'), $limit, $limit + $this->peronce - 1, $table, $ftable);
    }

    /**
     * Получение значений столбцов для вставки в таблицу
     * @param array $row массив значений
     * @return array массив полученных значений
     */
    private function insert($row) {
        $r = array();
        foreach ($this->insert as $icol => $exp) {
            if (is_array($exp)) {
                $args = $exp[1];
                $this->prepare_args($args, $row);
                $exp = call_user_func_array(array($this->getter, $exp[0]), $args);
            } else
                $exp = $row[$icol];
            $r[$icol] = $exp;
        }
        return $r;
    }

    /**
     * Запоминание столбцов
     * @param string $str входная строка
     * @param bool $parse парсить?
     * @return string эта же строка
     */
    private function store_cols($str, $parse = false) {
        if (!$str)
            return;
        if (mb_strtolower($str) == '$row')
            return $str;
        if (!$parse && !in_array($str, $this->columns))
            $this->columns[] = $str;
        elseif ($parse) {
            $c = preg_match_all('/\{([^\{\}]+)\}/siu', $str, $matches);
            for ($i = 0; $i < $c; $i++) {
                $col = $matches[1][$i];
                if (!in_array($col, $this->columns))
                    $this->columns[] = $col;
            }
        }
        return $str;
    }

    /**
     * Парсинг функции
     * @param string $icol столбец для вставки
     * @param string $exp функция
     * @return bool true, если успешно спарсенно
     */
    private function parse_function($icol, $exp) {
        if (!preg_match('/^(\w+)\((.*)\)$/siu', $exp, $matches))
            return false;
        $fname = "get_" . $matches[1];
        $args = trim($matches[2]);
        $c = preg_match_all('/(?:(?:\{([^\{\}]+)\})|(?:([\'"])(.*?)(?<!\\\)\\2))(?:\,|$)/siu', $args, $matches);
        $args = array();
        for ($i = 0; $i < $c; $i++) {
            $arg = $this->store_cols(trim($matches[1][$i]));
            if (!$arg)
                $arg = $this->store_cols($matches[3][$i], true);
            else
                $arg = '{' . $arg . '}';
            $args[] = $arg;
        }
        $this->insert[$icol] = array($fname, $args);
        return true;
    }

    /**
     * Парсинг столбцов таблицы
     * @param string $data данные для вставки/выборки
     * @return array запрос на выборку
     */
    private function parse_columns($data) {
        $c = preg_match_all('/^\s*?(\w+)(\s*?\|\s*?function)?(?:\s*?\:?(.*?))?\s*?$/miu', $data, $matches);
        for ($i = 0; $i < $c; $i++) {
            $icol = $matches[1][$i];
            $isf = (bool) $matches[2][$i];
            if (!$icol)
                continue;
            $exp = trim($matches[3][$i]);
            if (!$exp)
                $exp = '`' . $icol . '`';
            if ($isf && !$this->parse_function($icol, $exp))
                continue;
            elseif (!$isf) {
                $exp .= ' AS `' . $icol . '`';
                $this->store_cols($exp);
                $this->insert[$icol] = $exp;
            }
        }
    }

}

?>
