<?php

/**
 * Project:             CTRev
 * @file                modules/downm.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        lang::o()->get("blocks/downm");
        switch ($_GET["act"]) {
            case "content":
                users::o()->check_perms("content", 1, 2);
                $sticky = isset($_GET["sticky"]) ? (bool) $_GET["sticky"] : true;
                $this->show_content($sticky);
                break;
            case "comments":
                users::o()->check_perms('comment', 1, 2);
                /* @var $comments comments */
                $comments = n("comments");
                $comments->usertable();
                break;
            default:
                users::o()->check_perms('profile', 1, 2);
                $this->show_online();
                break;
        }
    }

    /**
     * Вывод последнего контента
     * @return null
     */
    public function show_content($sticky = false) {
        lang::o()->get('profile');
        /* @var $users user_ajax */
        $users = plugins::o()->get_module("user", false, true);
        tpl::o()->assign("sticky", (int) $sticky);
        tpl::o()->display('blocks/contents/dcontent.tpl');
        $users->show_last_content(null, ($sticky ? "sticky='1'" : ""));
    }

    /**
     * Вывод списка online-пользователей
     * @return null
     */
    public function show_online() {
        $i = (int) config::o()->v('online_interval');
        if (!$i)
            $i = 15;
        $time = (time() - $i);
        $res = db::o()->p($time)->query('SELECT userdata FROM sessions
                WHERE time > ? GROUP BY IF(uid>0,uid,ip)');
        $res = db::o()->fetch2array($res);
        tpl::o()->assign("res", $res);
        $c = count($res);
        $mo = stats::o()->read("max_online");
        if (!intval($mo) || $mo < $c) {
            $mo = $c;
            stats::o()->write("max_online", $c);
            stats::o()->write("max_online_time", time());
        }
        $mot = stats::o()->read("max_online_time");
        tpl::o()->assign("record_total", $mo);
        tpl::o()->assign("record_time", $mot);
        /* @var $user user */
        $user = plugins::o()->get_module("user");
        lang::o()->get("profile");
        tpl::o()->register_modifier("gau", array($user, "get_age"));
        tpl::o()->assign("bdl", $this->bd_list());
        tpl::o()->display("blocks/contents/online.tpl");
    }

    /**
     * Список ДР
     * @return array список из БД
     */
    protected function bd_list() {
        return db::o()->p(date("m"), date("d"))->cname('birthday')->query("SELECT 
            username, `group`, birthday FROM users WHERE FROM_UNIXTIME(birthday, '%m') = ?
                    AND FROM_UNIXTIME(birthday, '%d') = ?");
    }

}

?>