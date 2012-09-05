<?php

/**
 * Project:            	CTRev
 * File:                warnings.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
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
     * @global lang $lang
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $tpl;
        $lang->get('admin/bans');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $this->save($_POST);
                die();
                break;
            case "add":
                $tpl->display('admin/warnings/add.tpl');
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Функция показа предупреждений
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID предупреждения
     * @return null
     */
    protected function show($id = null) {
        global $db, $tpl;
        $r = $db->query('SELECT b.*, u.username AS bu, u.group AS bg, u2.username, u2.group FROM warnings AS b
            LEFT JOIN users AS u ON u.id=b.uid
            LEFT JOIN users AS u2 ON u2.id=b.byuid' .
                ($id ? " WHERE b.id=" . longval($id) . ' LIMIT 1' : ""));
        $tpl->assign('res', $db->fetch2array($r));
        $tpl->display('admin/warnings/index.tpl');
    }

    /**
     * Сохранение предупреждения
     * @global etc $etc
     * @global furl $furl
     * @global string $admin_file
     * @param array $data массив данных
     * @param int $id ID предупреждения
     * @return null
     */
    protected function save($data) {
        global $etc, $furl, $admin_file;
        $id = (int) $data['id'];
        $cols = array(
            'user' => 'username',
            'reason',
            'notify');
        extract(rex($data, $cols));
        $notify = (bool) $notify;
        if ($user || !$id) {
            $r = $etc->select_user(null, $user, "id,email,warnings_count");
            $uid = $r["id"];
            $email = $r["email"];
            $warns = $r["warnings_count"];
        }
        if ((!$uid && !$id) || !$reason)
            throw new EngineException('warnings_no_user');
        $etc->warn_user($uid, $reason, $warns, $notify, $email, true, $id);
        if ($id) {
            $this->show($id);
            return;
        } else
            $furl->location($admin_file);
    }

}

class warnings_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get('admin/bans');
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
     * Удаление предупреждения
     * @global etc $etc
     * @param int $id ID предупреждения
     * @return null
     */
    protected function delete($id) {
        global $etc;
        $id = (int) $id;
        $etc->unwarn_user(null, null, $id);
    }

    /**
     * Функция редактирования предупреждений
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID предупреждения
     * @return null
     */
    protected function edit($id) {
        global $db, $tpl;
        $id = (int) $id;
        $r = $db->query('SELECT b.*, u.username FROM warnings AS b
            LEFT JOIN users AS u ON u.id=b.uid
            WHERE b.id=' . $id . ' LIMIT 1');
        $tpl->assign("res", $db->fetch_assoc($r));
        $tpl->display('admin/warnings/edit.tpl');
    }

}

?>