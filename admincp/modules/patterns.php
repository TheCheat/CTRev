<?php

/**
 * Project:            	CTRev
 * File:                patterns.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление шаблонами категорий
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class patterns_man {

    /**
     * Типы полей
     * @var array
     */
    protected $types = array(
        'input',
        'textarea',
        'radio',
        'select',
        'html');

    /**
     * Инициализация управления шаблонами
     * @global lang $lang
     * @global array $POST
     * @return null
     */
    public function init() {
        global $lang, $POST;
        $lang->get('admin/patterns');
        $act = $_GET['act'];
        switch ($act) {
            case "add":
            case "edit":
                $this->add((int) $_GET['id'], $act == "add");
                break;
            case "save":
                $_POST['html'] = $POST['html'];
                $_POST['descr'] = $POST['descr'];
                $this->save($_POST);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Добавление/редактирование шаблона
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID шаблона
     * @param bool $add добавление?
     * @return null
     */
    protected function add($id = null, $add = false) {
        global $tpl, $db;
        $row = array();
        if ($id) {
            $id = (int) $id;
            $r = $db->query('SELECT * FROM patterns WHERE id=' . $id . ' LIMIT 1');
            $row = $db->fetch_assoc($r);
            $row["pattern"] = unserialize($row["pattern"]);
        } else
            $row["pattern"] = array(array());
        $tpl->assign('id', $add ? 0 : $id);
        $tpl->assign('rows', $row);
        $tpl->assign('pat_types', $this->types);
        $tpl->display('admin/patterns/add.tpl');
    }

    /**
     * Вывод списка категорий
     * @global tpl $tpl
     * @global db $db
     * @return null 
     */
    protected function show() {
        global $tpl, $db;
        $r = $db->query('SELECT id, name FROM patterns');
        $tpl->assign('rows', $db->fetch2array($r));
        $tpl->display('admin/patterns/index.tpl');
    }

    /**
     * Сохранение шаблона
     * @global db $db
     * @global cache $cache
     * @global furl $furl
     * @global string $admin_file
     * @global display $display
     * @param array $data массив данных шаблона
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $db, $cache, $furl, $admin_file, $display;
        $cols = array(
            'name',
            'rname',
            'type',
            'size',
            'values',
            'html',
            'descr',
            'formdata');
        if ($data['id'])
            $id = (int) $data['id'];
        if (!$data['pattern_name'])
            $data['pattern_name'] = 'tmp' . time(); // Меньше ошибок - лучше
        $update = array();
        $update['name'] = $data['pattern_name'];
        $pattern = rex($data, $cols);
        $c = count($pattern['name']);
        $obj = array();
        foreach ($pattern as $type => $e) {
            if (!is_array($e) || count($e) != $c)
                throw new EngineException('patterns_invalid_data');
            for ($i = 0; $i < $c; $i++)
                $obj[$i][$type] = $e[$i];
        }
        $pattern = array();
        foreach ($obj as $i => $e) {
            $type = $e['type'];
            if (!$type || !in_array($type, $this->types))
                continue;
            if (!$e['name'])
                continue;
            if ((!$e['rname'] || !validword($e['rname'])) && $type != 'html')
                $e['rname'] = $display->translite($e['name']);
            if (!$e['formdata'])
                continue;
            $c = false;
            $size = (int) $e['size'];
            unset($e['size']);
            switch ($type) {
                case 'select':
                case 'radio':
                    if (!$e['values']) {
                        $c = true;
                        break;
                    }
                    unset($e['html']);
                    break;
                case 'html':
                    if (!$e['html']) {
                        $c = true;
                        break;
                    }
                    unset($e['rname']);
                    unset($e['descr']);
                    unset($e['values']);
                    break;
                case "input":
                    if ($size)
                        $e['size'] = $size;
                default:
                    unset($e['html']);
                    unset($e['values']);
                    break;
            }
            if (!$c)
                $pattern[] = $e;
        }
        $update['pattern'] = serialize($pattern);
        if ($id) {
            $db->update($update, 'patterns', 'WHERE id=' . $id . ' LIMIT 1');
            $cache->remove('patterns/pattern-id' . $id);
            log_add('changed_pattern', 'admin', $id);
        } else {
            $db->insert($update, 'patterns');
            log_add('added_pattern', 'admin');
        }
        $furl->location($admin_file);
    }

}

class patterns_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET['act'];
        switch ($act) {
            case "delete":
                $this->delete((int) $_POST['id']);
                break;
        }
        die('OK!');
    }

    /**
     * Удаление шаблона
     * @global db $db
     * @global cache $cache
     * @param int $id ID шаблона
     * @return null
     */
    protected function delete($id) {
        global $db, $cache;
        $id = (int) $id;
        $db->delete('patterns', 'WHERE id="' . $id . '" LIMIT 1');
        $cache->remove('patterns/pattern-id' . $id);
        log_add('deleted_pattern', 'admin', $id);
    }

}

?>