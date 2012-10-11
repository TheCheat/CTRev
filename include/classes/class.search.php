<?php

/**
 * Project:            	CTRev
 * File:                class.search.php
 * 
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Поисковая система
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * @todo юзать Sphinx, если доступен
 */
class search {

    /**
     * Правила в PCRE для выборки слов
     * @todo добавить больше правил, вынести куда-нибудь отдельно
     * @var array $pcre_rules
     */
    protected $pcre_rules = array(
        'c|с',
        '($%value)$' => 'ый|ая|ое|ие|ой|ым|им|ую|их|ий|ого|ому|ом|ыми|ими');

    /**
     * Правила в PCRE для полнотекстового поиска в логическом режиме
     * @var array $bool_rules
     */
    protected $bool_rules = array(
        '^([\\\]([\+\(\<\>\~]))+' => '',
        '[\\\]\)$' => '',
        '([^\\\])[\\\]\*$' => '$1\w*',
    );

    /**
     * Правила инициализированы?
     * @var bool $rules_inited
     */
    protected $rules_inited = false;

    /**
     * Замена для поиска внутри файлов
     * @var string $infiles_replace
     */
    protected $infiles_replace = null;

    /**
     * Функция записи файла
     * @var string $infiles_replace_cb
     */
    protected $infiles_replace_cb = null;

    /**
     * Для array_map: regexp -> /$regexp/siu
     * @param string $regexp входной регексп
     * @return string выходной регексп
     */
    protected function regexp_replace($regexp) {
        if ($regexp[0] != '/')
            $regexp = '/' . $regexp . '/siu';
        return $regexp;
    }

    /**
     * Обрезка результата поиска
     * @param string $what что режем?
     * @param array $length как сильно?
     * @return null
     */
    public function cut_search(&$what, $length = 0) {
        if (!$length)
            $length = config::o()->v('max_search_symb');
        if (!$length)
            return;
        $what = display::o()->cut_word($what, 0, $length);
    }

    /**
     * Подсветка, дабы ничего лишнего не обрезать
     * @param array $matches массив спарсенного
     * @return string обработанная строка
     */
    protected function highlight_pcre_callback($matches) {
        return str_replace($matches[1], '<font class="highlighted">' . $matches[1] . '</font>', $matches[0]);
    }

    /**
     * Подсветка найденных слов в тексте и отсечение лишней части текста, в случае, когда много символов
     * @param string $text строка поиска
     * @param array|string $regexp регулярные выражения поиска. 
     * Группа в рег. выражении под номером 1 всегда должна быть искомым выражением.
     * @param bool $cut обрезать ли текст
     * @return bool true, если найдено
     */
    public function highlight_text(&$text, $regexp, $cut = true) {
        $cut_symb_max = config::o()->v('max_search_symb');
        if (!is_array($regexp))
            $regexp = array($regexp);
        $regexp = array_map(array($this, 'regexp_replace'), $regexp);
        $ntext = preg_replace_callback($regexp, array($this, 'highlight_pcre_callback'), $text);
        if ($ntext == $text) {
            if ($cut && $cut_symb_max)
                $text = display::o()->cut_word($text, 0, $cut_symb_max) . "...";
            return false;
        }
        $text = $ntext;
        if ($cut)
            $this->highlight_cut($text);
    }

    /**
     * Обрезка выделенного текста
     * @param string $text текст
     * @return null 
     */
    protected function highlight_cut(&$text) {
        $cut_symb_max = config::o()->v('max_search_symb');
        $ret = preg_split('/(\<font class\=\"highlighted\"\>(?:.+?)\<\/font\>)/siu', $text, - 1, PREG_SPLIT_DELIM_CAPTURE);
        $count = count($ret);
        $text = "";
        $max_symb = config::o()->v('max_symb_after_word');
        $smax_symb = longval($max_symb / 2);
        $lastword = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($i % 2 == 0) {
                $strlen = mb_strlen($ret [$i]);
                if ($i == 0) // режем начало
                    $text .= ( $strlen > $max_symb ? "..." . display::o()->cut_word($ret [$i], $strlen - $max_symb, $max_symb) : $ret [$i]);
                elseif ($i == $count - 1) // режем конец
                    $text .= ( $strlen > $max_symb ? display::o()->cut_word($ret [$i], 0, $max_symb) . "..." : $ret [$i]);
                else // и серединку
                    $text .= ( $strlen > $max_symb ? display::o()->cut_word($ret [$i], 0, $smax_symb) . "..." . display::o()->cut_word($ret [$i], $strlen - $smax_symb, $smax_symb) : $ret [$i]);
                if (mb_strlen($text) > $cut_symb_max && $cut_symb_max) { // кончаем нарезку, ибо слишком многа букафф
                    $text = display::o()->cut_word($text, 0, $lastword > $cut_symb_max ? $lastword : $cut_symb_max) . "...";
                    break;
                }
            } else {
                $text .= $ret [$i];
                $lastword = mb_strlen($text); // на случай, если последнее выделенное слово дальше максимума
            }
        }
    }

    /**
     * Инициализация правил
     * @return null
     */
    protected function rules_init() {
        if ($this->rules_inited)
            return;
        foreach ($this->pcre_rules as $key => $value)
            if (!is_numeric($key))
                if (strpos($key, '$%value') !== false) {
                    $this->pcre_rules [str_replace('$%value', $value, $key)] = $value;
                    unset($this->pcre_rules [$key]);
                }
    }

    /**
     * Выполнение правил поискового запроса
     * @param string $text текст поиска
     * @param bool $boolean поиск в логическом режиме?
     * @return null
     */
    protected function rules_execute(&$text, $boolean = false) {
        $this->rules_init();
        if ($boolean) {
            /**
             * Исключаем лишнее
             * Ограничение:
             * -(smtng something) второе слово будет учитываться в подсветке
             */
            if (mb_strpos($text, '\-') === 0) {
                $text = "";
                return;
            }
            foreach ($this->bool_rules as $key => $value)
                $text = preg_replace("/" . (is_numeric($key) ? $value : $key) . "/siu", $value, $text);
        }
        foreach ($this->pcre_rules as $key => $value)
            $text = preg_replace("/" . (is_numeric($key) ? $value : $key) . "/siu", '(' . $value . ')', $text);
        $text = '(?:\W|^)(' . $text . ')(?:\W|$)';
    }

    /**
     * Поиск со звёздочкой
     * @param string $value значение
     * @param string $column столбец
     * @return string условие
     */
    public function like_where($value, $column) {
        $value = str_replace(array('\*',
            '\?',
            '?',
            '*',
            '&#42;',
            '&#63;'), array('&#42;',
            '&#63;',
            '_',
            '%',
            '*',
            '?'), db::o()->sesc($value));
        return db::o()->cesc($column) . ' LIKE "' . $value . '"';
    }

    /**
     * Создание условия полнотекстового поиска и выделение слов для подсветки
     * @param string $value искомые слова
     * @param string|array $columns столбец\столбцы
     * @param array $regexp рег. выражение для подсветки текста
     * Если изначально переменной присвоено значение true, 
     * то будут вычислены рег. выражения, иначе - нет
     * @param bool $boolean поиск в логическом режиме
     * @return string условие поиска
     */
    public function make_where($value, $columns, &$regexp = true, $boolean = true) {
        if (is_array($columns))
            $columns = implode(',', array_map(array(db::o(), "cesc"), $columns));
        else
            $columns = db::o()->cesc($columns);
        $where = 'MATCH(' . $columns . ') AGAINST(' . db::o()->esc($value) .
                ($boolean ? ' IN BOOLEAN MODE' : '') . ')';
        if (!$regexp)
            return $where;
        $value = mpc(strval($value));
        $regexp = array();
        if ($boolean) { // выделяем фразы
            $r = preg_split('/(?:\s+|^)"([^\"]*)"(?:\s+|$)/siu', $value, - 1, PREG_SPLIT_DELIM_CAPTURE);
            $c = count($r);
            $value = "";
            for ($i = 0; $i < $c; $i++)
                if ($i % 2 == 0)
                    $value .= ($value ? " " : "") . $r[$i];
                else {
                    $this->rules_execute($r[$i], false);
                    $regexp [] = $r[$i];
                }
        }
        $value = preg_split('/\s+/', $value);
        $c = count($value);
        for ($i = 0; $i < $c; $i++) {
            if (mb_strlen($value [$i]) < 3)
                continue;
            $cvalue = $value [$i];
            $this->rules_execute($cvalue, $boolean);
            if ($cvalue)
                $regexp [] = $cvalue;
        }
        return $where;
    }

    /**
     * Функция поиска значений в данной таблице
     * @param string $table таблица
     * @param string|array $columns искомый столбец\столбцы
     * @param string $value значение
     * @param int $limit лимит поиска
     * @param string $id_col столбец ID
     * @return array результат поиска
     */
    public function pre_search($table, $columns, $value, $limit = 10, $id_col = "id") {
        if (!$id_col)
            $id_col = "id";
        $regexp = false; // reference
        $where = $this->make_where($value, $columns, $regexp);
        if (is_array($columns))
            $cols = $columns + array(-1 => $id_col);
        else
            $cols = array(
                $id_col,
                $columns);
        $cols = implode('`, `', $cols);
        $res = db::o()->query('SELECT `' . $cols . '` FROM `' . $table . '` WHERE ' . $where
                . ($limit ? " LIMIT " . $limit : ""));
        if ($where)
            return db::o()->fetch2array($res);
    }

    /**
     * Функция для поиска IP вида 127.0.0.*
     * @param string $ip IP адрес
     * @param string $column столбец поиска
     * @return string условие для поиска
     */
    public function search_ip($ip, $column = 'ip') {
        if (!$column)
            $column = 'ip';
        $ip1 = ip2ulong(str_replace("*", "0", $ip));
        $ip2 = ip2ulong(str_replace("*", "255", $ip));
        $column = '`' . $column . '`';
        if ($ip1 && $ip2)
            return '(' . $column . '<=' . $ip2 . ' AND ' . $column . '>=' . $ip1 . ')';
        else
            return '';
    }

    /**
     * Поиск настроек пользователя
     * @param string $area поле
     * @param string $value значение
     * @param string $column искомый столбец
     * @return string условие для поиска
     */
    public function search_settings($area, $value, $column = 'settings') {
        return '`' . $column . '` LIKE  "%' . db::o()->sesc($area) . ':' . db::o()->sesc($value) . "\n" . '%"';
    }

    /**
     * Функция замены внутри файлов(вызывается перед поиском)
     * @param string $with чем заменять
     * @param callback $callback callback функция для сохранения контента файла
     * первый параметр функции - путь к файлу
     * второй - заменённый контент
     * третий - массив старых ключей, где ключ - новый ключ, а значение - старый
     * @return search $this
     */
    public function replace_infiles($with, $callback = null) {
        $this->infiles_replace = $with;
        $this->infiles_replace_cb = $callback;
        return $this;
    }

    /**
     * Поиск/замена в файлах
     * @param string|array $dir путь к дирректории или массив файлов
     * @param string $what что ищем? (рег. выражение без делимиттеров)
     * @param bool $regexp регулярное выражение?
     * @param int $where где ищем?(для массива, 0 - в значениях, 1 - в ключах, 2 - и там, и там)
     * если указать -1, то не будет проверяться, слово ли ключ, а поиск будет по значению
     * @param callback $callback callback функция для получения контента файла
     * единственный параметр функции - путь к файлу
     * @param string $was пред. путь
     * @return array массив файлов(ключи) и подсвеченных результатов(значения)
     */
    public function search_infiles($dir, $what, $regexp = false, $where = null, $callback = null, $was = '') {
        if (!$dir || !$what)
            return;
        if (!is_array($dir) && is_dir(ROOT . $dir))
            $files = file::o()->open_folder($dir);
        else
            $dir = $files = (array) $dir;
        $r = array();
        if (!$regexp) {
            $what = mpc($what);
            $regexp = true;
        } else
            $what = str_replace('/', '\\/', $what);
        $owhat = $what;
        if (is_null($this->infiles_replace))
            $what = display::o()->html_encode($what);
        $what = '/(' . $what . ')/siu';
        $where = (int) $where;
        foreach ($files as $f) {
            if (!is_array($dir)) {
                $nf = $dir . '/' . $f;
                $wf = ($was ? $was . '/' : '') . $f;
            } else
                $wf = $nf = $f;
            if (!$f)
                return;
            $fr = ROOT . $nf;
            if (is_dir($fr)) {
                if (!is_array($dir))
                    $r = array_merge($r, $this->search_infiles($nf, $owhat, $regexp, $where, $callback, $wf));
                continue;
            }
            if ($callback)
                $c = call_user_func($callback, $nf);
            else
                $c = file_get_contents($fr);
            if (!$c)
                continue;
            if (!is_array($c)) {
                if (is_null($this->infiles_replace))
                    $c = display::o()->html_encode($c);
                if (!preg_match($what, $c))
                    continue;
                if (is_null($this->infiles_replace))
                    $this->highlight_text($c, $what, true);
                else {
                    $c = preg_replace($what, $this->infiles_replace, $c);
                    if ($this->infiles_replace_cb)
                        call_user_func($this->infiles_replace_cb, $nf, $c);
                    else
                        file::o()->write_file($c, $nf);
                }
                $r[$wf] = $c;
                continue;
            }
            $tmp = array();
            $b = $where == -1;
            if ($b)
                $where = 0;
            $keys = array();
            if (!is_null($this->infiles_replace) && !$this->infiles_replace_cb)
                continue;
            foreach ($c as $k => $v) {
                if (!$b && !validword($k))
                    continue;
                if (is_null($this->infiles_replace)) {
                    $v = display::o()->html_encode($v);
                    $k = display::o()->html_encode($k);
                }
                if ($where == 0 && !preg_match($what, $v))
                    continue;
                if ($where == 1 && !preg_match($what, $k))
                    continue;
                if ($where == 2 && !preg_match($what, $v) && !preg_match($what, $k))
                    continue;
                if ($where < 0 || $where > 2)
                    continue;
                if (is_null($this->infiles_replace)) {
                    if ($where == 0 || $where == 2)
                        $this->highlight_text($v, $what, false);
                    if ($where == 1 || $where == 2)
                        $this->highlight_text($k, $what, false);
                } else {
                    $ok = $k;
                    if ($where == 0 || $where == 2)
                        $v = preg_replace($what, $this->infiles_replace, $v);
                    if ($where == 1 || $where == 2)
                        $k = preg_replace($what, $this->infiles_replace, $k);
                    if ($ok != $k)
                        $keys[$k] = $ok;
                }
                $tmp[$k] = $v;
            }
            if (!$tmp)
                continue;
            if (!is_null($this->infiles_replace) && $this->infiles_replace_cb)
                call_user_func($this->infiles_replace_cb, $nf, $tmp, $keys);
            $r[$wf] = $tmp;
        }
        if (!$was)
            $this->replace_infiles(null, null);
        return $r;
    }

}

?>