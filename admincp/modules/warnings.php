<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/warnings.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление предупреждениями блокировками
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class warnings_man {

    /**
     * Инициализация модуля предупреждений
     * @return null
     */
    public function init() {
        lang::o()->get('admin/bans');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $this->save($_POST);
                die();
                break;
            case "add":
                tpl::o()->display('admin/warnings/add.tpl');
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Функция отображения предупреждений
     * @param int $id ID предупреждения
     * @return null
     */
    protected function show($id = null) {
        $id = (int) $id;
        $r = db::o()->p($id)->query('SELECT b.*, u.username AS bu, u.group AS bg, 
            u2.username, u2.group FROM warnings AS b
            LEFT JOIN users AS u ON u.id=b.uid
            LEFT JOIN users AS u2 ON u2.id=b.byuid' .
                ($id ? " WHERE b.id=? LIMIT 1" : ""));
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->display('admin/warnings/index.tpl');
    }

    /**
     * Сохранение предупреждения
     * @param array $data массив данных
     * @return null
     */
    protected function save($data) {
        $admin_file = globals::g('admin_file');
        $id = (int) $data['id'];
        $cols = array(
            'user' => 'username',
            'reason',
            'notify');
        extract(rex($data, $cols));
        $notify = (bool) $notify;
        /* @var $etc etc */
        $etc = n("etc");
        if ($user || !$id) {
            $r = $etc->select_user(null, $user, "id,email,warnings_count");
            $uid = $r["id"];
            $email = $r["email"];
            $warns = $r["warnings_count"];
        }
        if ((!$uid && !$id) || !$reason)
            throw new EngineException('warnings_no_user');
        $etc->warn_user($uid, $reason, $warns, $notify, $email, $id);
        if ($id) {
            $this->show($id);
            return;
        }
        else
            furl::o()->location($admin_file);
    }

}

class warnings_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        lang::o()->get('admin/bans');
        $act = $_GET["act"];
        $id = (int) $_POST["id"];
        switch ($act) {
            case "edit":
                $this->edit($id);
                break;
            case "delete":
                $this->delete($id);
                break;
        }
        ok();
    }

    /**
     * Удаление предупреждения
     * @param int $id ID предупреждения
     * @return null
     */
    protected function delete($id) {
        $id = (int) $id;
        /* @var $etc etc */
        $etc = n("etc");
        $etc->unwarn_user(null, null, $id);
    }

    /**
     * Функция редактирования предупреждений
     * @param int $id ID предупреждения
     * @return null
     */
    protected function edit($id) {
        $id = (int) $id;
        $r = db::o()->p($id)->query('SELECT b.*, u.username FROM warnings AS b
            LEFT JOIN users AS u ON u.id=b.uid
            WHERE b.id=? LIMIT 1');
        tpl::o()->assign("res", db::o()->fetch_assoc($r));
        tpl::o()->display('admin/warnings/edit.tpl');
    }

}

?>