<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/bots.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        $POST = globals::g('POST');
        lang::o()->get('admin/bots');
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
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT * FROM bots');
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->display('admin/bots/index.tpl');
    }

    /**
     * Добавление/редактирование бота
     * @param int $id ID бота
     * @return null
     */
    protected function add($id = null) {
        $id = (int) $id;
        if ($id) {
            $r = db::o()->p($id)->query('SELECT * FROM bots WHERE id=? LIMIT 1');
            tpl::o()->assign("row", db::o()->fetch_assoc($r));
        }
        tpl::o()->assign("id", $id);
        tpl::o()->display('admin/bots/add.tpl');
    }

    /**
     * Сохранение бота
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    public function save($data) {
        $admin_file = globals::g('admin_file');
        $cols = array(
            'id',
            'name',
            'firstip',
            'lastip',
            'agent');
        extract(rex($data, $cols));
        $id = (int) $id;
        /* @var $etc etc */
        $etc = n("etc");
        $etc->get_ips($firstip, $lastip, true);
        if (!$name)
            throw new EngineException('bots_empty_name');
        if (!$firstip && !$lastip && !$agent)
            throw new EngineException('bots_empty_data');
        $update = array(
            'name' => $name,
            'firstip' => $firstip,
            'lastip' => $lastip,
            'agent' => $agent);
        try {
            plugins::o()->pass_data(array("update" => &$update,
                "id" => $id), true)->run_hook('admin_bots_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        if (!$id) {
            db::o()->insert($update, 'bots');
            log_add('added_bot', 'admin');
        } else {
            db::o()->p($id)->update($update, 'bots', 'WHERE id=? LIMIT 1');
            log_add('changed_bot', 'admin', $id);
        }
        furl::o()->location($admin_file);
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
        ok();
    }

    /**
     * Удаление бота
     * @param int $id ID бота
     * @return null
     */
    public function delete($id) {
        $id = (int) $id;
        db::o()->p($id)->delete('bots', 'WHERE id=? LIMIT 1');
        log_add('deleted_bot', 'admin', $id);
    }

}

?>