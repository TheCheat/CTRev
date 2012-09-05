<?php

/**
 * Project:             CTRev
 * File:                ajax_index.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Некоторые Ajax функции главной и не только страницы
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class ajax_index {

    /**
     * Заполнено ли поле?
     * @var bool
     */
    protected $pattern_filled = true;

    /**
     * Функция инициализации Ajax функций для главной страницы
     * @global modsettings $modsettings
     * @return null
     */
    public function init() {
        global $modsettings;
        $act = $_GET ['act'];
        switch ($act) {
            case "modsettings":
                $modsettings->make_demo($_POST);
                break;
            case "calendar_torrents" :
                $year = (int) $_POST ["year"];
                $month = (int) $_POST ["month"];
                $this->calendar_torrents($month, $year);
                break;
            case "search_pre" :
                $text = $_POST ["text"];
                $this->search_pre($text);
                break;
            case "move_unread" :
                $time = (int) $_POST ["time"];
                $after = !($_POST ['type'] == "before");
                $this->move_unread($time, $after);
                break;
            case "save_last" :
                $time = (int) $_POST ["time"];
                $this->save_last($time);
                break;
            case "get_msgs" :
                $this->get_msgs();
                break;
            case "children_cat" :
                $id = (int) $_POST["id"];
                $type = $_POST['type'];
                $num = longval($_POST ['num']) + 1;
                $this->children_cat($id, $num, $type);
                break;
            case "check_pattern":
                $id = (int) $_GET["id"];
                $this->check_pattern($id);
                break;
            case "build_pattern":
                $id = (int) $_GET["id"];
                $this->build_pattern($id);
                return;
                break;
            default :
                break;
        }
        die();
    }

    /**
     * Подсчёт кол-ва торрентов в данном месяце и в данном году
     * @global plugins $plugins
     * @param int $month данный месяц
     * @param int $year данный год
     * @return null
     */
    protected function calendar_torrents($month, $year) {
        global $plugins;
        $calendar_block = $plugins->get_module("calendar", true);
        $to_print = $calendar_block->count_torrents($month, $year);
        print ('<script language="text/javascript">$torrents_per_dates = ' . $to_print . '</script>');
    }

    /**
     * Предпросмотр результатов поиска
     * @global search $search
     * @global tpl $tpl
     * @global config $config
     * @global lang $lang
     * @param string $text искомый текст
     */
    protected function search_pre($text) {
        global $search, $tpl, $config, $lang;
        $lang->get('search');
        $res = $search->pre_search("torrents", ($config->v('pre_search_title_only') ? "title" : array(
                    "title",
                    "content")), $text);
        $tpl->assign("res", $res);
        $tpl->display("torrents/pre_search.tpl");
    }

    /**
     * Функция получения кол-ва сообщений пользователя
     * @global plugins $plugins
     * @global tpl $tpl
     * @global lang $lang
     * @global users $users
     * @return null
     */
    protected function get_msgs() {
        global $plugins, $tpl, $lang, $users;
        $users->check_perms('pm');
        $lang->get("messages");
        $messages = $plugins->get_module("messages", false, true);
        list($inbox, $outbox, $unread) = $messages->count();
        if ($unread) {
            $res = $messages->unread();
            $time = (int) $_COOKIE ['time_last_msg'];
            $count = $messages->unread_count($res ["time"], true);
        }
        $tpl->assign("inbox", $inbox);
        $tpl->assign("outbox", $outbox);
        $tpl->assign("unread", $unread);
        $tpl->assign("count_after", $count);
        $tpl->assign("count_prev", $unread - $count - 1);
        $tpl->assign("unread_time", $time);
        $tpl->assign("unread_last", $res);
        $tpl->display("messages/ajax_index_get.tpl");
    }

    /**
     * Функция сохранения времени последнего закрытого сообщения в кукисах
     * @global users $users
     * @param int $time время сообщения
     * @return null
     */
    protected function save_last($time) {
        global $users;
        $time = (int) $time;
        $users->check_perms('pm');
        @ob_clean();
        $users->setcookie("time_last_msg", $time);
    }

    /**
     * Функция прочтения сообщения до или после данного времени
     * @global tpl $tpl
     * @global plugins $plugins
     * @global users $users
     * @global lang $lang
     * @param int $time данное время
     * @param bool $after true - до, иначе - после
     * @return null
     */
    protected function move_unread($time, $after = false) {
        global $tpl, $plugins, $users, $lang;
        $time = (int) $time;
        $users->check_perms('pm');
        $lang->get("messages");
        $messages = $plugins->get_module("messages", false, true);
        $row = $messages->unread($time, $after);
        if (!$row)
            return;
        $tpl->assign("unread_last", $row);
        $tpl->assign("after", $after);
        $tpl->assign("only_unread", true);
        $tpl->display("messages/ajax_index_get.tpl");
    }

    /**
     * Показывание подкат. категории
     * @global categories $cats
     * @global tpl $tpl
     * @param int $cat_id ID категории-родителя
     * @param int $num номер вложенности
     * @param string $type тип категории
     * @return null
     */
    protected function children_cat($cat_id, $num, $type = 'torrents') {
        global $cats, $tpl;
        $cat_id = (int) $cat_id;
        $tpl->assign("cats", $cats->change_type($type)->get($cat_id, 'c'));
        $tpl->assign("only_cat", true);
        $tpl->assign("cnum", $num);
        $tpl->display('categories.tpl');
    }

    /**
     * Парсинг шаблона
     * @global db $db
     * @global cache $cache
     * @param int $id ID шаблона
     * @return array массив данного шаблона
     */
    protected function parse_pattern($id) {
        global $db, $cache;
        $id = (int) $id;
        if (!($row = $cache->read('patterns/pattern-id' . $id))) {
            $r = $db->query('SELECT * FROM patterns WHERE id=' . $id . ' LIMIT 1');
            $row = $db->fetch_assoc($r);
            if (!$row)
                return;
            $row['pattern'] = unserialize($row['pattern']);
            foreach ($row['pattern'] as $k => $e) {
                if ($e['type'] == 'radio' || $e['type'] == 'select') {
                    $vals = &$e['values'];
                    $vals = explode(';', $vals);
                    $c = count($vals);
                    for ($i = 0; $i < $c; $i++) {
                        $vals[$i] = trim($vals[$i]);
                        $p = mb_strpos($vals[$i], ':');
                        if (!$p)
                            continue;
                        $f = mb_substr($vals[$i], 0, $p);
                        $s = mb_substr($vals[$i], $p + 1);
                        $vals[$i] = array(trim($f), trim($s));
                    }
                }
                $fd = &$e['formdata'];
                $r = preg_split('/^\{form\.([a-z0-9\_\-]+)\}/msiu', $fd, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                $fd = array();
                $c = count($r);
                $key = null;
                for ($i = 0; $i < $c; $i++) {
                    if ($i % 2 == 0)
                        $key = $r[$i];
                    else
                        $fd[$key] = preg_replace('/\{this\.\$value\}/siu', '{this.' . $e['rname'] . '}', $r[$i]);
                }
                $row['pattern'][$k] = $e;
            }
            $cache->write($row);
        }
        return $row;
    }

    /**
     * preg_replace_callback для шаблона
     * @param array $matches массив спарсенных переменных
     * @return mixed значение
     */
    protected function pattern_pcre_callback($matches) {
        $v = $_POST[$matches[1]];
        if (!$v)
            $this->pattern_filled = false;
        return $v;
    }

    /**
     * Обработка поля шаблона
     * @global bbcodes $bbcodes
     * @param array $el поле шаблона
     * @return string HTML код поля
     */
    public function patternfield_compile($el) {
        global $bbcodes;
        $html = '';
        $rname = $el['rname'];
        $s = false;
        switch ($el['type']) {
            case "input":
                $size = $el['size'] ? $el['size'] : 55;
                $html .= '<input type="text" name="' . $rname . '" size="' . $size . '">';
                break;
            case "textarea":
                $html .= $bbcodes->input_form($rname);
                break;
            case "select":
                $html .= '<select name="' . $rname . '">';
                $s = true;
            case "radio":
                foreach ($el['values'] as $value) {
                    if (is_array($value)) {
                        $key = $value[0];
                        $value = $value[1];
                    } else
                        $key = $value;
                    if ($s)
                        $html .= '<option value="' . $key . '">' . $value . '</option>';
                    else
                        $html .= ' <input type="radio" name="' . $rname . '" value="' . $key . '">' . $value;
                }
                if ($s)
                    $html .= '</select>';
                break;
            case "html":
                $html .= $el['html'];
                break;
        }
        return $html;
    }

    /**
     * Проверка и сборка шаблона для формы
     * @global lang $lang
     * @global display $display
     * @param int $id ID шаблона
     * @param array $data проверяемые данные
     * @return null 
     */
    protected function check_pattern($id) {
        global $lang, $display;
        $lang->get('admin/patterns');
        $row = $this->parse_pattern($id);
        $arr = array();
        foreach ($row['pattern'] as $e)
            foreach ($e['formdata'] as $key => $val) {
                $this->pattern_filled = true;
                $val = preg_replace_callback('/\{this\.([a-z0-9\_\-]+)\}/siu', array($this, 'pattern_pcre_callback'), $val);
                if (!$this->pattern_filled) {
                    if ($e['name'][0] == '*')
                        throw new EngineException('patterns_necessary_fields_not_filled');
                    else
                        continue;
                }
                $oval = $val = rtrim($val);
                if ($oval == ($val = preg_replace('/^\{nobr\}/siu', '', $val)) && $arr[$key] && $key != 'title') // в заголовки заранее не пишем
                    $val = "\n" . $val;
                $arr[$key] .= $val;
            }
        print('OK!' . $display->array_export_to_js($arr));
    }

    /**
     * Построение шаблона
     * @global lang $lang
     * @global tpl $tpl
     * @param int $id ID шаблона
     * @return null 
     */
    protected function build_pattern($id) {
        global $lang, $tpl;
        $lang->get('admin/patterns');
        $row = $this->parse_pattern($id);
        $tpl->register_modifier('patternfield_compile', array($this, 'patternfield_compile'));
        $tpl->assign('row', $row);
        $tpl->display('pattern.tpl');
    }

}

?>