<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/bans.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление банами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class bans_man {

    /**
     * Инициализация модуля банов
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
                tpl::o()->display('admin/bans/add.tpl');
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Функция показа банов
     * @param int $id ID бана
     * @return null
     */
    protected function show($id = null) {
        $r = db::o()->query('SELECT b.*, u.username AS bu, u.group AS bg, u2.username, u2.group FROM bans AS b
            LEFT JOIN users AS u ON u.id=b.uid
            LEFT JOIN users AS u2 ON u2.id=b.byuid' .
                ($id ? " WHERE b.id=" . longval($id) . ' LIMIT 1' : ""));
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->display('admin/bans/index.tpl');
    }

    /**
     * Сохранение бана
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        $admin_file = globals::g('admin_file');
        $id = (int) $data['id'];
        $cols = array(
            'user' => 'username',
            'email',
            'ip_f',
            'ip_t',
            'reason',
            'period',
            'up' => 'update');
        extract(rex($data, $cols));
        $ip_f = ip2ulong($ip_f);
        $ip_t = ip2ulong($ip_t);
        $period = (float) $period;
        /* @var $etc etc */
        $etc = n("etc");
        $uid = 0;
        if ($user) {
            $r = $etc->select_user(null, $user, "id");
            $uid = $r["id"];
        }
        if (!$uid && !$email && !$ip_f && !$ip_t)
            throw new EngineException("bans_nothing_banned");
        $etc->ban_user($uid, (!$id || $up ? $period : 0), $reason, $email, $ip_f, $ip_t, $id);
        if ($id) {
            $this->show($id);
            return;
        } else
            furl::o()->location($admin_file);
    }

}

class bans_man_ajax {

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
        die("OK!");
    }

    /**
     * Удаление бана
     * @param int $id ID бана
     * @return null
     */
    protected function delete($id) {
        $id = (int) $id;
        /* @var $etc etc */
        $etc = n("etc");
        $etc->unban_user(null, $id);
    }

    /**
     * Функция редактирования бана
     * @param int $id ID бана
     * @return null
     */
    protected function edit($id) {
        $id = (int) $id;
        $r = db::o()->query('SELECT b.*, u.username, u.group FROM bans AS b
            LEFT JOIN users AS u ON u.id=b.uid
            WHERE b.id=' . $id . ' LIMIT 1');
        tpl::o()->assign("res", db::o()->fetch_assoc($r));
        tpl::o()->display('admin/bans/edit.tpl');
    }

}

?>