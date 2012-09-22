<?php

/**
 * Project:            	CTRev
 * File:                blocks.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление блоками
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class blocks_man {

    /**
     * Типы блоков
     * @var array
     */
    public static $types = array('left', 'top', 'bottom', 'right');

    /**
     * Инициализация управления блоками
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get('admin/blocks');
        $act = $_GET['act'];
        switch ($act) {
            case "add":
            case "edit":
                $this->add((int) $_GET['id']);
                break;
            case "save":
                $this->save($_POST);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Селектор файлов
     * @global input $input
     * @global tpl $tpl
     * @param string $current данное значение
     * @param string $type тип(file или tpl)
     * @return string HTML код
     */
    public function files_selector($current, $type = 'file') {
        global $input, $tpl;
        switch ($type) {
            case "file":
                $path = MODULES_PATH . '/' . BLOCKS_PATH;
                $regexp = '(\w+)\.php';
                $empty = false;
                break;
            case "tpl":
                // Разрешим пустой блок
                //$allblock = '|' . mpc(blocks::allblock);
                $path = cut_path($tpl->template_dir) . blocks::blocks_path;
                $regexp = '(?(?=(?:(?:' . implode('|', array_map('mpc', self::$types)) . ')' .
                        mpc(blocks::block_standart) . ')' . $allblock . ')\/|(\w+))\.tpl';
                $empty = true;
                break;
            default:
                return;
        }
        return $input->select_folder($type, $path, $current, false, $empty, '/^' . $regexp . '$/siu', 1);
    }

    /**
     * Селектор типа
     * @global input $input
     * @global lang $lang
     * @param string $current данное значение
     * @return string HTML код
     */
    protected function types_selector($current = null) {
        global $input, $lang;
        $types = array();
        $c = count(self::$types);
        for ($i = 0; $i < $c; $i++)
            $types[self::$types[$i]] = $lang->v('blocks_block_type_' . self::$types[$i]);
        return $input->simple_selector('type', $types, true, $current);
    }

    /**
     * Селектор модулей
     * @global input $input
     * @param string $current данное значение
     * @return string HTML код
     */
    protected function modules_selector($current = null) {
        global $input;
        $arr = allowed::o()->get();
        $c = count($arr);
        for ($i = 0; $i < $c; $i++)
            if (strpos($arr[$i], '/'))
                unset($arr[$i]);
        $current = explode(';', $current);
        $arr[] = "index";
        return $input->simple_selector('module', $arr, false, $current, 5, true);
    }

    /**
     * Добавление/редактирование блока
     * @global db $db
     * @global tpl $tpl
     * @global plugins $plugins
     * @global modsettings $modsettings
     * @param int $id ID блока
     * @return null
     */
    protected function add($id = null) {
        global $db, $tpl, $plugins, $modsettings;
        $row = array();
        if ($id) {
            $id = (int) $id;
            $r = $db->query('SELECT * FROM blocks WHERE id=' . $id . ' LIMIT 1');
            $row = $db->fetch_assoc($r);
            $row["settings"] = unserialize($row["settings"]);
            $row["group_allowed"] = explode(";", $row["group_allowed"]);
            $tpl->assign('row', $row);
            $object = $plugins->get_module($row["file"], true);
            $lpre = "blocks_" . $row['file'];
            $tpl->assign('bsetting_manager', $modsettings->change_type('blocks')->display($row['id'], $object, $row["settings"], $lpre));
        }
        $tpl->assign('id', $id);
        $tpl->assign('types_selector', $this->types_selector($row['type']));
        $tpl->assign('modules_selector', $this->modules_selector($row["module"]));
        $tpl->register_modifier('files_selector', array($this, 'files_selector'));
        $tpl->display('admin/blocks/add.tpl');
    }

    /**
     * Отображение всех блоков
     * @global db $db
     * @global tpl $tpl
     * @return null
     */
    protected function show() {
        global $db, $tpl;
        $r = $db->query('SELECT * FROM blocks');
        $tpl->assign('rows', $db->fetch2array($r, null, array('type' => '__array')));
        $tpl->assign('types', self::$types);
        $tpl->display('admin/blocks/index.tpl');
    }

    /**
     * Сохранение блока
     * @global db $db
     * @global furl $furl
     * @global string $admin_file
     * @global cache $cache
     * @global modsettings $modsettings
     * @param array $data массив данных блока
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $db, $furl, $admin_file, $cache, $modsettings;
        $cols = array('title',
            'file',
            'type',
            'tpl',
            'module',
            'group_allowed',
            'enabled');
        if ($data['id'])
            $id = (int) $data['id'];
        $update = rex($data, $cols);
        $update['enabled'] = (bool) $update['enabled'];
        $update['module'] = implode(';', (array) $update['module']);
        $update['group_allowed'] = implode(';', (array) $update['group_allowed']);
        if (!$update['title'] || !$update['file'] || !in_array($update['type'], self::$types))
            throw new EngineException('blocks_invalid_input');
        $update['settings'] = serialize($modsettings->change_type('blocks')->save($id, $data));
        if ($id) {
            $db->update($update, 'blocks', 'WHERE id=' . $id . ' LIMIT 1');
            log_add('changed_block', 'admin', $id);
        } else {
            $db->insert($update, 'blocks');
            log_add('added_block', 'admin');
        }
        $db->query('ALTER TABLE `blocks` ORDER BY `pos`');
        $cache->remove('blocks');
        $furl->location($admin_file);
    }

}

class blocks_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global cache $cache
     * @return null
     */
    public function init() {
        global $cache;
        $act = $_GET['act'];
        switch ($act) {
            case "delete":
                $this->delete((int) $_POST['id']);
                break;
            case "switch":
                $this->switch_state((int) $_POST['id']);
                break;
            case "order":
                $this->save_order($_POST['blockid']);
                break;
        }
        $cache->remove('blocks');
        die('OK!');
    }

    /**
     * Удаление блока
     * @global db $db
     * @global modsettings $modsettings
     * @param int $id ID блока
     * @return null
     */
    protected function delete($id) {
        global $db, $modsettings;
        $id = intval($id);
        $db->delete('blocks', 'WHERE id=' . $id . ' LIMIT 1');
        $modsettings->change_type('blocks')->uncache($id);
        log_add('deleted_block', 'admin', $id);
    }

    /**
     * Включение/выключение блока
     * @global db $db
     * @param int $id ID блока
     * @return null
     */
    protected function switch_state($id) {
        global $db;
        $db->update(array('_cb_enabled' => 'IF(enabled="1","0","1")'), 'blocks', 'WHERE id=' . intval($id) . ' LIMIT 1');
        log_add('switched_block', 'admin', $id);
    }

    /**
     * Сохранение порядка блоков
     * @global db $db
     * @return null
     * @throws EngineException
     */
    protected function save_order($sort) {
        global $db;
        if (!$sort)
            throw new EngineException;
        foreach ($sort as $p => $obj)
            foreach ($obj as $s => $id)
                $db->update(array('pos' => (int) $s,
                    'type' => blocks_man::$types[$p]), 'blocks', 'WHERE id=' . intval($id) . ' LIMIT 1');
        $db->query('ALTER TABLE `blocks` ORDER BY `pos`');
    }

}

?>