<?php

/**
 * Project:            	CTRev
 * File:                bots.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление ботами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class bots_man {

    /**
     * Инициализация модуля ботов
     * @global lang $lang
     * @global array $POST
     * @return null
     */
    public function init() {
        global $lang, $POST;
        $lang->get('admin/bots');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $_POST['agent'] = $POST['agent'];
                $this->save($_POST);
                break;
            case "add":
            case "edit":
                $this->add($_GET['id']);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Отображение списка ботов
     * @global db $db
     * @global tpl $tpl
     * @return null
     */
    protected function show() {
        global $db, $tpl;
        $r = $db->query('SELECT * FROM bots');
        $tpl->assign('res', $db->fetch2array($r));
        $tpl->display('admin/bots/index.tpl');
    }

    /**
     * Добавление/редактирование бота
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID бота
     * @return null
     */
    protected function add($id = null) {
        global $db, $tpl;
        $id = (int) $id;
        if ($id) {
            $r = $db->query('SELECT * FROM bots WHERE id=' . $id . ' LIMIT 1');
            $tpl->assign("row", $db->fetch_assoc($r));
        }
        $tpl->assign("id", $id);
        $tpl->display('admin/bots/add.tpl');
    }

    /**
     * Сохранение бота
     * @global db $db
     * @global furl $furl
     * @global string $admin_file
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $db, $furl, $admin_file;
        $cols = array(
            'id',
            'name',
            'firstip',
            'lastip',
            'agent');
        extract(rex($data, $cols));
        $id = (int) $id;
        $firstip = ip2ulong($firstip);
        $lastip = ip2ulong($lastip);
        if ($lastip && !$firstip)
            $firstip = $lastip;
        if ($firstip && !$lastip)
            $lastip = $firstip;
        if ($firstip > $lastip && $lastip) {
            $t = $firstip;
            $firstip = $lastip;
            $lastip = $t;
        }
        if (!$name)
            throw new EngineException('bots_name_not_entered');
        if (!$firstip && !$lastip && !$agent)
            throw new EngineException('bots_data_not_entered');
        $update = array(
            'name' => $name,
            'firstip' => $firstip,
            'lastip' => $lastip,
            'agent' => $agent);
        if (!$id) {
            $db->insert($update, 'bots');
            log_add('added_bot', 'admin');
        } else {
            $db->update($update, 'bots', 'WHERE id=' . $id . ' LIMIT 1');
            log_add('changed_bot', 'admin', $id);
        }
        $furl->location($admin_file);
    }

}

class bots_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET["act"];
        $id = (int) $_POST["id"];
        switch ($act) {
            case "delete":
                $this->delete($id);
                break;
        }
        die("OK!");
    }

    /**
     * Удаление бота
     * @global db $db
     * @param int $id ID бота
     * @return null
     */
    protected function delete($id) {
        global $db;
        $id = (int) $id;
        $db->delete('bots', 'WHERE id=' . $id . ' LIMIT 1');
        log_add('deleted_bot', 'admin', $id);
    }

}

?>