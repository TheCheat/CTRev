<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.cleanup.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
        "readcontent",
        "bans",
        "warnings",
        "peers",
        "users",
        "content",
        "chat",
        "sitemap");

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
        users::o()->admin_mode();
        users::o()->set_tmpvars(array('id' => -1));
        /* @var $mailer mailer */
        $mailer = n("mailer");
        $mailer->change_type('content')->cleanup();
        $mailer->change_type('categories')->cleanup();
        /* @var $attach attachments */
        $attach = n("attachments");
        $attach->clear();
        users::o()->groups_autoupdate();

        foreach ($this->methods as $m)
            $this->call_method('clear_' . $m);
        users::o()->remove_tmpvars();
        users::o()->admin_mode(false);
        //cache::o()->clear_ocache(config::o()->v('cache_oldtime') * 3600);
    }

    /**
     * Очистка сессий
     * @return null
     */
    protected function clear_sessions() {
        $hour = 3600; // Секунд в часу
        $maxtime = time() - $hour * config::o()->v('session_clear');
        db::o()->p($maxtime)->delete("sessions", 'WHERE time < ?');
    }

    /**
     * Очистка прочтённого контента
     * @return null
     */
    protected function clear_readcontent() {
        if (!config::o()->v('clean_rc_interval'))
            return;
        $day = 86400; // Секунд в день
        $time = stats::o()->read('last_clean_rc');
        if (time() < $time + config::o()->v('clean_rc_interval') * $day)
            return;
        db::o()->truncate_table('content_readed');
        stats::o()->write('last_clean_rc', time());
    }

    /**
     * Очистка банов
     * @return null
     */
    protected function clear_bans() {
        $r = db::o()->query("SELECT id,uid FROM bans WHERE to_time <> 0 AND to_time <= " . time());
        $ids = array();
        while ($row = db::o()->fetch_assoc($r)) {
            $uid = $row["uid"];
            if ($uid)
                db::o()->p($uid)->update(array("_cb_group" => 'old_group',
                    "old_group" => 0), "users", "WHERE id=? AND old_group<>0 LIMIT 1");
            $ids[] = $row["id"];
        }
        if (!$ids)
            return;
        db::o()->p($ids)->delete("bans", 'WHERE id IN(@' . count($ids) . '?)');
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
        $r = db::o()->p($when)->query("SELECT id,uid FROM warnings WHERE time <= ?");
        $ids = array();
        /* @var $etc etc */
        $etc = n("etc");
        while ($row = db::o()->fetch_assoc($r)) {
            $uid = $row["uid"];
            $etc->add_res('warnings', -1, "users", $uid);
            /// Да, да, да.. я тут не сделал удаление юзера из банов, если слишком мало предов, но так и задумывалось ;)
            $ids[] = $row["id"];
        }
        if (!$ids)
            return;
        db::o()->p($ids)->delete("warnings", 'WHERE id IN(@' . count($ids) . '?)');
    }

    /**
     * Очистка пиров
     * @return null
     */
    protected function clear_peers() {
        if (!config::o()->v('clean_peers_interval'))
            return;
        if (!config::o()->v('torrents_on'))
            return;
        $hour = 3600; // Секунд в часу
        $when = time() - config::o()->v('clean_peers_interval') * $hour;
        $r = db::o()->p($when)->query("SELECT peer_id,tid,seeder FROM content_peers WHERE time <= ?");
        $ids = array();
        $sl = array();
        while ($row = db::o()->fetch_assoc($r)) {
            $sl[$row['tid']][0] += ($row["seeder"] ? 1 : 0);
            $sl[$row['tid']][1] += (!$row["seeder"] ? 1 : 0);
            $ids[] = $row["peer_id"];
        }
        if (!$ids)
            return;
        if ($sl)
            foreach ($sl as $id => $cur) {
                if ($cur[0])
                    db::o()->p($id)->update(array("_cb_seeders" => 'IF(seeders>=' . $cur[0] . ',
                        seeders-' . $cur[0] . ',0)'), "content_torrents", 'WHERE cid=? LIMIT 1');
                if ($cur[1])
                    db::o()->p($id)->update(array("_cb_leechers" => 'IF(leechers>=' . $cur[1] . ',
                        leechers-' . $cur[1] . ',0)'), "content_torrents", 'WHERE cid=? LIMIT 1');
            }
        db::o()->p($ids)->delete("content_peers", 'WHERE peer_id IN(@' . count($ids) . '?)');
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
        $r = db::o()->p($when)->query("SELECT id FROM users WHERE last_visited <= ?");
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
        $r = db::o()->p($when)->query("SELECT cid FROM content_torrents WHERE last_active <= ?");
        /* @var $etc etc */
        $etc = n("etc");
        while (list($id) = db::o()->fetch_row($r))
            try {
                $etc->delete_content($id);
            } catch (EngineException $e) {
                
            }
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
        db::o()->p($when)->delete('chat', 'WHERE posted_time <= ?');
    }

    /**
     * Генерация sitemap.xml
     * @return null
     */
    protected function clear_sitemap() {
        /* @var $m main_page_ajax */
        $m = plugins::o()->get_module("main", 2, 1);
        $m->sitemap();
    }

}

?>