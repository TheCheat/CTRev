<?php

/**
 * Project:             CTRev
 * File:                downm.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Нижник блок, Ajax часть
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class downm {

    /**
     * Инициализация Ajax-части нижнего блока
     * @global lang $lang
     * @global users $users
     * @global comments $comments
     * @return null
     */
    public function init() {
        global $lang, $users, $comments;
        $lang->get("blocks/downm");
        switch ($_GET["act"]) {
            case "torrents":
                $users->check_perms('torrents', 1, 2);
                $sticky = isset($_GET["sticky"]) ? (bool) $_GET["sticky"] : true;
                $this->show_torrents($sticky);
                break;
            case "comments":
                $users->check_perms('comment', 1, 2);
                $comments->usertable();
                break;
            default:
                $users->check_perms('profile', 1, 2);
                $this->show_online();
                break;
        }
    }

    /**
     * Вывод последних торрентов
     * @global plugins $plugins
     * @global lang $lang
     * @global tpl $tpl
     * @return null
     */
    protected function show_torrents($sticky = false) {
        global $plugins, $lang, $tpl;
        $lang->get('profile');
        $users = $plugins->get_module("user", false, true);
        $tpl->assign("sticky", (int) $sticky);
        $tpl->display('blocks/contents/dtorrents.tpl');
        $users->show_last_torrents(null, ($sticky ? "sticky='1'" : ""));
    }

    /**
     * Вывод списка online-пользователей
     * @global db $db
     * @global tpl $tpl
     * @global config $config
     * @global stats $stats
     * @global plugins $plugins
     * @global lang $lang
     * @return null
     */
    protected function show_online() {
        global $db, $tpl, $config, $stats, $plugins, $lang;
        $res = $db->query('SELECT userdata FROM sessions
                WHERE time > ' . (time() - $config->v('online_interval')) . '
                GROUP BY IF(uid>0,uid,ip)');
        $res = $db->fetch2array($res);
        $tpl->assign("res", $res);
        $c = count($res);
        $mo = $stats->read("max_online");
        if (!intval($mo) || $mo < $c) {
            $mo = $c;
            $stats->write("max_online", $c);
            $stats->write("max_online_time", time());
        }
        $mot = $stats->read("max_online_time");
        $tpl->assign("record_total", $mo);
        $tpl->assign("record_time", $mot);
        $user = $plugins->get_module("user");
        $lang->get("profile");
        $tpl->register_modifier("gau", array($user, "get_age"));
        $tpl->assign("bdl", $this->bd_list());
        $tpl->display("blocks/contents/online.tpl");
    }

    /**
     * Список ДР
     * @global db $db
     * @return array список из БД
     */
    protected function bd_list() {
        global $db;
        return $db->query("SELECT username, `group`, birthday FROM users
                WHERE FROM_UNIXTIME(birthday, '%m') = " . date("m") . "
                    AND FROM_UNIXTIME(birthday, '%d') = " . date("d"), 'birthday');
    }

}

?>