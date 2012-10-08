<?php

/**
 * Project:            	CTRev
 * File:                class.cleanup.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Очистка системы
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class cleanup extends pluginable_object {

    /**
     * Методы клинапа
     * @var array $methods
     */
    protected $methods = array(
        "sessions",
        "readtorrents",
        "bans",
        "warnings",
        "peers",
        "users",
        "torrents",
        "chat");

    /**
     * Конструктор класса
     * @return null
     */
    protected function plugin_construct() {
        $this->access_var('methods', PVAR_ADD);
    }

    /**
     * Выполнение очистки сайта
     * @param bool $force выполнять клинап вне зависимости от времени
     * @return null
     */
    public function execute($force = false) {
        if (!longval(config::o()->v('cleanup_each')))
            return;
        $hour = 3600; // Секунд в часу
        $time = stats::o()->read('last_cleanup');
        if (!$force && time() < $time + config::o()->v('cleanup_each') * $hour)
            return;
        stats::o()->write('last_cleanup', time());
        $am = users::o()->admin_mode(true);
        users::o()->set_tmpvars(array('id' => -1));
        /* @var $mailer mailer */
        $mailer = n("mailer");
        $mailer->change_type('torrents')->cleanup();
        $mailer->change_type('categories')->cleanup();
        users::o()->groups_autoupdate();

        foreach ($this->methods as $m)
            $this->call_method('clear_' . $m);
        users::o()->remove_tmpvars();
        if ($am)
            users::o()->admin_mode();
        //cache::o()->clear_ocache(config::o()->v('cache_oldtime') * 3600);
    }

    /**
     * Очистка сессий
     * @return null
     */
    protected function clear_sessions() {
        $hour = 3600; // Секунд в часу
        $maxtime = time() - $hour * config::o()->v('session_clear');
        db::o()->delete("sessions", ( 'WHERE time < ' . $maxtime));
    }

    /**
     * Очистка прочтённых торрентов
     * @return null
     */
    protected function clear_readtorrents() {
        if (!config::o()->v('clean_rt_interval'))
            return;
        $day = 86400; // Секунд в день
        $time = stats::o()->read('last_clean_rt');
        if (time() < $time + config::o()->v('clean_rt_interval') * $day)
            return;
        db::o()->truncate_table('read_torrents');
        stats::o()->write('last_clean_rt', time());
    }

    /**
     * Очистка банов
     * @return null
     */
    protected function clear_bans() {
        $r = db::o()->query("SELECT id,uid FROM bans WHERE to_time <> 0 AND to_time <= " . time());
        $ids = "";
        while ($row = db::o()->fetch_assoc($r)) {
            $uid = $row["uid"];
            if ($uid)
                db::o()->update(array("_cb_group" => 'old_group',
                    "old_group" => 0), "users", "WHERE id=" . $uid . " AND old_group<>0 LIMIT 1");
            $ids .= ( $ids ? ", " : "") . $row["id"];
        }
        if (!$ids)
            return;
        db::o()->delete("bans", 'WHERE id IN(' . $ids . ')');
    }

    /**
     * Очистка предупреждений
     * @return null
     */
    protected function clear_warnings() {
        if (!config::o()->v('clear_warn_period'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - config::o()->v('clear_warn_period') * $day;
        $r = db::o()->query("SELECT id,uid FROM warnings WHERE time <= " . $when);
        $ids = "";
        /* @var $etc etc */
        $etc = n("etc");
        while ($row = db::o()->fetch_assoc($r)) {
            $uid = $row["uid"];
            $etc->add_res('warnings', -1, "users", $uid);
            /// Да, да, да.. я тут не сделал удаление юзера из банов, если слишком мало предов, но так и задумывалось ;)
            $ids .= ( $ids ? ", " : "") . $row["id"];
        }
        if (!$ids)
            return;
        db::o()->delete("warnings", 'WHERE id IN(' . $ids . ')');
    }

    /**
     * Очистка пиров
     * @return null
     */
    protected function clear_peers() {
        if (!config::o()->v('clean_peers_interval'))
            return;
        $hour = 3600; // Секунд в часу
        $when = time() - config::o()->v('clean_peers_interval') * $hour;
        $r = db::o()->query("SELECT peer_id,tid,seeder FROM peers WHERE time <= " . $when);
        $ids = "";
        $sl = array();
        while ($row = db::o()->fetch_assoc($r)) {
            $sl[$row['tid']][0] += ($row["seeder"] ? 1 : 0);
            $sl[$row['tid']][1] += (!$row["seeder"] ? 1 : 0);
            $ids .= ( $ids ? ", " : "") . db::o()->esc($row["peer_id"]);
        }
        if (!$ids)
            return;
        if ($sl)
            foreach ($sl as $id => $cur) {
                if ($cur[0])
                    db::o()->update(array("seeders" => 'IF(seeders>=' . $cur[0] . ',seeders-' . $cur[0] . ',0)'), "torrents", 'WHERE id=' . $id . ' LIMIT 1');
                if ($cur[1])
                    db::o()->update(array("leechers" => 'IF(leechers>=' . $cur[1] . ',leechers-' . $cur[1] . ',0)'), "torrents", 'WHERE id=' . $id . ' LIMIT 1');
            }
        db::o()->delete("peers", 'WHERE peer_id IN(' . $ids . ')');
    }

    /**
     * Очистка неактивных пользователей
     * @return null
     */
    protected function clear_users() {
        if (!config::o()->v('del_inactive'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - config::o()->v('del_inactive') * $day;
        $r = db::o()->query("SELECT id FROM users WHERE last_visited <= " . $when);
        /* @var $etc etc */
        $etc = n("etc");
        while (list($id) = db::o()->fetch_row($r))
            $etc->delete_user($id);
    }

    /**
     * Очистка старых торрентов
     * @return null
     */
    protected function clear_torrents() {
        if (!config::o()->v('del_oldtorrents'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - config::o()->v('del_oldtorrents') * $day;
        $r = db::o()->query("SELECT id FROM torrents WHERE last_active <= " . $when);
        /* @var $etc etc */
        $etc = n("etc");
        while (list($id) = db::o()->fetch_row($r))
            $etc->delete_torrent($id);
    }

    /**
     * Очистка старых сообщений чата
     * @return null
     */
    protected function clear_chat() {
        if (!config::o()->v('chat_autoclear'))
            return;
        $hour = 3600; // Секунд в час
        $when = time() - config::o()->v('chat_autoclear') * $hour;
        db::o()->delete('chat', 'WHERE posted_time <= ' . $when);
    }

}

?>