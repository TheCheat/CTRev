<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/allowedft.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление типами файлов
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class allowedft_man {

    /**
     * Базовые типы файлов
     * @var array $basic_types
     */
    protected $basic_types = array(
        "images",
        "avatars",
        "torrents");

    /**
     * Базовый тип?
     * @param string $id имя типа
     * @return bool true, если базовый
     */
    public function is_basic($id) {
        $id = mb_strtolower($id);
        return in_array($id, $this->basic_types);
    }

    /**
     * Инициализация модуля типов файлов
     * @return null
     */
    public function init() {
        lang::o()->get('admin/allowedft');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $this->save($_POST);
                die();
                break;
            case "edit":
            case "add":
                $this->add($_GET['id']);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Добавление/редактирование типов файлов
     * @param string $id имя типа файлов
     * @return null
     */
    protected function add($id = null) {
        if ($id) {
            $r = db::o()->p($id)->query('SELECT * FROM allowed_ft WHERE name=? LIMIT 1');
            tpl::o()->assign('row', db::o()->fetch_assoc($r));
        }
        tpl::o()->display('admin/allowedft/add.tpl');
    }

    /**
     * Функция отображения типов файлов
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT * FROM allowed_ft');
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->register_modifier('aftbasic', array($this, 'is_basic'));
        tpl::o()->display('admin/allowedft/index.tpl');
    }

    /**
     * Сохранение типов файлов
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    public function save($data) {
        $admin_file = globals::g('admin_file');
        $oname = $data['old_name'];
        $cols = array(
            'name',
            'image',
            'types',
            'MIMES',
            'max_filesize',
            'max_width',
            'max_height',
            'makes_preview',
            'allowed');
        $data = rex($data, $cols);
        $data['makes_preview'] = (bool) $data['makes_preview'];
        $data['allowed'] = (bool) $data['allowed'];
        $data['max_filesize'] = (int) $data['max_filesize'];
        $data['max_width'] = (int) $data['max_width'];
        $data['max_height'] = (int) $data['max_height'];
        if (!validword($data['name']))
            throw new EngineException('allowedft_invalid_name');
        if (!$data['max_filesize'])
            throw new EngineException('allowedft_invalid_filesize');
        if (!$data['types'])
            throw new EngineException('allowedft_invalid_types');
        if ($oname)
            db::o()->p($oname)->update($data, 'allowed_ft', 'WHERE name=? LIMIT 1');
        else {
            db::o()->insert($data, 'allowed_ft');
            log_add('added_filetype', 'admin', $data['name']);
        }
        furl::o()->location($admin_file);
    }

}

class allowedft_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        lang::o()->get('admin/allowedft');
        $act = $_GET["act"];
        $id = $_POST["id"];
        switch ($act) {
            case "switch":
                $this->switch_state($id, $_POST['type']);
                break;
            case "delete":
                $this->delete($id);
                break;
        }
        ok();
    }

    /**
     * Изменение хар-ки типа
     * @param string $id имя типа файлов
     * @param string $type тип хар-ки
     * @return null
     */
    public function switch_state($id, $type = "allowed") {
        $type = $type == 'allowed' ? 'allowed' : 'makes_preview';
        db::o()->p($id)->update(array('_cb_' . $type => 'IF(' . $type . '="1","0","1")'), 'allowed_ft', '
            WHERE name=? LIMIT 1');
    }

    /**
     * Удаление типов файлов
     * @param string $id имя типа файлов
     * @return null
     */
    public function delete($id) {
        /* @var $aft allowedft_man */
        $aft = plugins::o()->get_module('allowedft', 1);
        if ($aft->is_basic($id))
            return;
        db::o()->p($id)->delete('allowed_ft', 'WHERE name=? LIMIT 1');
        log_add('deleted_filetype', 'admin', $id);
    }

}

?>