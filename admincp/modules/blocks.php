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
     * @var array $types
     */
    public static $types = array('left', 'top', 'bottom', 'right');

    /**
     * Инициализация управления блоками
     * @return null
     */
    public function init() {
        lang::o()->get('admin/blocks');
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
     * @param string $current данное значение
     * @param string $type тип(file или tpl)
     * @return string HTML код
     */
    public function files_selector($current, $type = 'file') {
        switch ($type) {
            case "file":
                $path = MODULES_PATH . '/' . BLOCKS_PATH;
                $regexp = '(\w+)\.php';
                $empty = false;
                break;
            case "tpl":
                // Разрешим пустой блок
                //$allblock = '|' . mpc(blocks::allblock);
                $path = cut_path(tpl::o()->template_dir) . blocks::blocks_path;
                $regexp = '(?(?=(?:(?:' . implode('|', array_map('mpc', self::$types)) . ')' .
                        mpc(blocks::block_standart) . ')' . $allblock . ')\/|(\w+))\.tpl';
                $empty = true;
                break;
            default:
                return;
        }
        return input::o()->select_folder($type, $path, $current, false, $empty, '/^' . $regexp . '$/siu', 1);
    }

    /**
     * Селектор типа
     * @param string $current данное значение
     * @return string HTML код
     */
    protected function types_selector($current = null) {
        $types = array();
        $c = count(self::$types);
        for ($i = 0; $i < $c; $i++)
            $types[self::$types[$i]] = lang::o()->v('blocks_block_type_' . self::$types[$i]);
        return input::o()->simple_selector('type', $types, true, $current);
    }

    /**
     * Селектор модулей
     * @param string $current данное значение
     * @return string HTML код
     */
    protected function modules_selector($current = null) {
        $arr = allowed::o()->get();
        $c = count($arr);
        for ($i = 0; $i < $c; $i++)
            if (strpos($arr[$i], '/'))
                unset($arr[$i]);
        $current = explode(';', $current);
        $arr[] = "index";
        return input::o()->simple_selector('module', $arr, false, $current, 5, true);
    }

    /**
     * Добавление/редактирование блока
     * @param int $id ID блока
     * @return null
     */
    protected function add($id = null) {
        $row = array();
        if ($id) {
            $id = (int) $id;
            $r = db::o()->query('SELECT * FROM blocks WHERE id=' . $id . ' LIMIT 1');
            $row = db::o()->fetch_assoc($r);
            $row["settings"] = unserialize($row["settings"]);
            $row["group_allowed"] = explode(";", $row["group_allowed"]);
            tpl::o()->assign('row', $row);
            $object = plugins::o()->get_module($row["file"], true);
            $lpre = "blocks_" . $row['file'];
            tpl::o()->assign('bsetting_manager', modsettings::o()->change_type('blocks')->display($row['id'], $object, $row["settings"], $lpre));
        }
        tpl::o()->assign('id', $id);
        tpl::o()->assign('types_selector', $this->types_selector($row['type']));
        tpl::o()->assign('modules_selector', $this->modules_selector($row["module"]));
        tpl::o()->register_modifier('files_selector', array($this, 'files_selector'));
        tpl::o()->display('admin/blocks/add.tpl');
    }

    /**
     * Отображение всех блоков
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT * FROM blocks');
        tpl::o()->assign('rows', db::o()->fetch2array($r, null, array('type' => '__array')));
        tpl::o()->assign('types', self::$types);
        tpl::o()->display('admin/blocks/index.tpl');
    }

    /**
     * Сохранение блока
     * @global string $admin_file
     * @param array $data массив данных блока
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $admin_file;
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
        $update['settings'] = serialize(modsettings::o()->change_type('blocks')->save($id, $data));
        if ($id) {
            db::o()->update($update, 'blocks', 'WHERE id=' . $id . ' LIMIT 1');
            log_add('changed_block', 'admin', $id);
        } else {
            db::o()->insert($update, 'blocks');
            log_add('added_block', 'admin');
        }
        db::o()->query('ALTER TABLE `blocks` ORDER BY `pos`');
        cache::o()->remove('blocks');
        furl::o()->location($admin_file);
    }

}

class blocks_man_ajax {

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
            case "switch":
                $this->switch_state((int) $_POST['id']);
                break;
            case "order":
                $this->save_order($_POST['blockid']);
                break;
        }
        cache::o()->remove('blocks');
        die('OK!');
    }

    /**
     * Удаление блока
     * @param int $id ID блока
     * @return null
     */
    protected function delete($id) {
        $id = intval($id);
        db::o()->delete('blocks', 'WHERE id=' . $id . ' LIMIT 1');
        modsettings::o()->change_type('blocks')->uncache($id);
        log_add('deleted_block', 'admin', $id);
    }

    /**
     * Включение/выключение блока
     * @param int $id ID блока
     * @return null
     */
    protected function switch_state($id) {
        db::o()->update(array('_cb_enabled' => 'IF(enabled="1","0","1")'), 'blocks', 'WHERE id=' . intval($id) . ' LIMIT 1');
        log_add('switched_block', 'admin', $id);
    }

    /**
     * Сохранение порядка блоков
     * @return null
     * @throws EngineException
     */
    protected function save_order($sort) {
        if (!$sort)
            throw new EngineException;
        foreach ($sort as $p => $obj)
            foreach ($obj as $s => $id)
                db::o()->update(array('pos' => (int) $s,
                    'type' => blocks_man::$types[$p]), 'blocks', 'WHERE id=' . intval($id) . ' LIMIT 1');
        db::o()->query('ALTER TABLE `blocks` ORDER BY `pos`');
    }

}

?>