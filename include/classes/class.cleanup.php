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
     * @var array
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
     * Инициализация очистки сайта
     * @global users $users
     * @global config $config
     * @global stats $stats
     * @global mailer $mailer
     * @param bool $force выполнять клинап вне зависимости от времени
     * @return null
     */
    public function init($force = false) {
        global $users, $config, $stats, $mailer;
        if (!longval($config->v('cleanup_each')))
            return;
        $hour = 3600; // Секунд в часу
        $time = $stats->read('last_cleanup');
        if (!$force && time() < $time + $config->v('cleanup_each') * $hour)
            return;
        $stats->write('last_cleanup', time());
        $am = $users->admin_mode(true);
        $users->set_tmpvars(array('id' => -1));
        $mailer->change_type('torrents')->cleanup();
        $mailer->change_type('categories')->cleanup();
        $users->groups_autoupdate();

        foreach ($this->methods as $m)
            $this->call_method('clear_' . $m);
        $users->remove_tmpvars();
        if ($am)
            $users->admin_mode();
        //$cache->clear_ocache($config->v('cache_oldtime') * 3600);
    }

    /**
     * Очистка сессий
     * @global db $db
     * @global config $config
     * @return null
     */
    protected function clear_sessions() {
        global $db, $config;
        $hour = 3600; // Секунд в часу
        $maxtime = time() - $hour * $config->v('session_clear');
        $db->delete("sessions", ( 'WHERE time < ' . $maxtime));
    }

    /**
     * Очистка прочтённых торрентов
     * @global db $db
     * @global config $config
     * @global stats $stats
     * @return null
     */
    protected function clear_readtorrents() {
        global $db, $config, $stats;
        if (!$config->v('clean_rt_interval'))
            return;
        $day = 86400; // Секунд в день
        $time = $stats->read('last_clean_rt');
        if (time() < $time + $config->v('clean_rt_interval') * $day)
            return;
        $db->truncate_table('read_torrents');
        $stats->write('last_clean_rt', time());
    }

    /**
     * Очистка банов
     * @global db $db
     * @return null
     */
    protected function clear_bans() {
        global $db;
        $r = $db->query("SELECT id,uid FROM bans WHERE to_time <> 0 AND to_time <= " . time());
        $ids = "";
        while ($row = $db->fetch_assoc($r)) {
            $uid = $row["uid"];
            if ($uid)
                $db->update(array("_cb_group" => 'old_group',
                    "old_group" => 0), "users", "WHERE id=" . $uid . " AND old_group<>0 LIMIT 1");
            $ids .= ( $ids ? ", " : "") . $row["id"];
        }
        if (!$ids)
            return;
        $db->delete("bans", 'WHERE id IN(' . $ids . ')');
    }

    /**
     * Очистка предупреждений
     * @global db $db
     * @global config $config
     * @global etc $etc
     * @return null
     */
    protected function clear_warnings() {
        global $db, $config, $etc;
        if (!$config->v('clear_warn_period'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - $config->v('clear_warn_period') * $day;
        $r = $db->query("SELECT id,uid FROM warnings WHERE time <= " . $when);
        $ids = "";
        while ($row = $db->fetch_assoc($r)) {
            $uid = $row["uid"];
            $etc->add_res('warnings', -1, "users", $uid);
            /// Да, да, да.. я тут не сделал удаление юзера из банов, если слишком мало предов, но так и задумывалось ;)
            $ids .= ( $ids ? ", " : "") . $row["id"];
        }
        if (!$ids)
            return;
        $db->delete("warnings", 'WHERE id IN(' . $ids . ')');
    }

    /**
     * Очистка пиров
     * @global db $db
     * @global config $config
     * @return null
     */
    protected function clear_peers() {
        global $db, $config;
        if (!$config->v('clean_peers_interval'))
            return;
        $hour = 3600; // Секунд в часу
        $when = time() - $config->v('clean_peers_interval') * $hour;
        $r = $db->query("SELECT peer_id,tid,seeder FROM peers WHERE time <= " . $when);
        $ids = "";
        $sl = array();
        while ($row = $db->fetch_assoc($r)) {
            $sl[$row['tid']][0] += ($row["seeder"] ? 1 : 0);
            $sl[$row['tid']][1] += (!$row["seeder"] ? 1 : 0);
            $ids .= ( $ids ? ", " : "") . $db->esc($row["peer_id"]);
        }
        if (!$ids)
            return;
        if ($sl)
            foreach ($sl as $id => $cur) {
                if ($cur[0])
                    $db->update(array("seeders" => 'IF(seeders>=' . $cur[0] . ',seeders-' . $cur[0] . ',0)'), "torrents", 'WHERE id=' . $id . ' LIMIT 1');
                if ($cur[1])
                    $db->update(array("leechers" => 'IF(leechers>=' . $cur[1] . ',leechers-' . $cur[1] . ',0)'), "torrents", 'WHERE id=' . $id . ' LIMIT 1');
            }
        $db->delete("peers", 'WHERE peer_id IN(' . $ids . ')');
    }

    /**
     * Очистка неактивных пользователей
     * @global db $db
     * @global config $config
     * @global etc $etc
     * @return null
     */
    protected function clear_users() {
        global $db, $config, $etc;
        if (!$config->v('del_inactive'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - $config->v('del_inactive') * $day;
        $r = $db->query("SELECT id FROM users WHERE last_visited <= " . $when);
        while (list($id) = $db->fetch_row($r))
            $etc->delete_user($id);
    }

    /**
     * Очистка старых торрентов
     * @global db $db
     * @global config $config
     * @global etc $etc
     * @return null
     */
    protected function clear_torrents() {
        global $db, $config, $etc;
        if (!$config->v('del_oldtorrents'))
            return;
        $day = 86400; // Секунд в день
        $when = time() - $config->v('del_oldtorrents') * $day;
        $r = $db->query("SELECT id FROM torrents WHERE last_active <= " . $when);
        while (list($id) = $db->fetch_row($r))
            $etc->delete_torrent($id);
    }

    /**
     * Очистка старых сообщений чата
     * @global db $db
     * @global config $config
     * @return null
     */
    protected function clear_chat() {
        global $db, $config;
        if (!$config->v('chat_autoclear'))
            return;
        $hour = 3600; // Секунд в час
        $when = time() - $config->v('chat_autoclear') * $hour;
        $db->delete('chat', 'WHERE posted_time <= ' . $when);
    }

}

?>