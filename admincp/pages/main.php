<?php

/**
 * Project:            	CTRev
 * File:                main.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Индексная страница АЦ
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class main_page {

    /**
     * Инициализация индексной страницы АЦ
     * @global lang $lang
     * @global tpl $tpl
     * @global db $db
     * @global cache $cahce
     * @return null
     */
    public function init() {
        global $lang, $tpl, $db, $cache;
        $lang->get('admin/pages/main');
        if (!($a = $cache->read('admin_stats'))) {
            $uc = $db->count_rows('users');
            $tc = $db->count_rows('torrents');
            $cc = $db->count_rows('comments');
            $a = array('uc' => $uc, 'tc' => $tc, 'cc' => $cc);
            $cache->write($a);
        }
        $tpl->assign('PHP_VERSION', PHP_VERSION);
        $tpl->assign('MYSQL_VERSION', $db->version());
        $tpl->assign('row', $a);
        $tpl->display('admin/pages/main.tpl');
    }

}

class main_page_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global users $users
     * @global etc $etc
     * @global db $db
     * @global plugins $plugins
     * @global lang $lang
     * @global cache $cache
     * @global stats $stats
     * @global comments $comments
     * @global polls $polls
     * @global rating $rating
     * @global cleanup $cleanup
     * @return null
     */
    public function init() {
        global $users, $etc, $db, $plugins, $lang, $cache, $stats, $comments, $polls, $rating, $cleanup;
        $lang->get('admin/pages/main');
        if (!$users->perm('system'))
            return;
        $act = $_GET["act"];
        $users->admin_mode();
        switch ($act) {
            case "cleanup":
                $cleanup->init(true);
                break;
            case "cache":
                $cache->clear();
                break;
            case "cache_tpl":
                $cache->clear_tpl();
                break;
            case "stats":
                $st = $stats->read();
                foreach ($st as $s => $v)
                    $stats->write($s, 0);
                break;
            case "logs":
                $logs = $plugins->get_module('logs', 1, true);
                $logs->clear();
                break;
            case "peers":
                $db->truncate_table('peers');
                $db->update(array('leechers' => 0,
                    'seeders' => 0), 'torrents');
                break;
            case "downloaded":
                $db->truncate_table('downloaded');
                $db->update(array('downloaded' => 0), 'torrents');
                break;
            case "chat":
                $chat = $plugins->get_module('chat');
                $chat->truncate();
                break;
            case "pm":
                $pm = $plugins->get_module('messages', false, true);
                $pm->clear();
                break;
            case "ratings":
                $r = $db->query('SELECT toid, type FROM ratings GROUP BY toid, type');
                while ($row = $db->fetch_assoc($r))
                    $rating->change_type($row['type'])->clear($row['toid']);
                break;

            // Далее: Важная часть сайта, да
            case "torrents":
                $r = $db->query('SELECT id FROM torrents');
                while (list($id) = $db->fetch_row($r))
                    $etc->delete_torrent($id);
                break;
            case "comments":
                $comments->clear(null, true);
                break;
            case "polls":
                $polls->clear();
                break;
            case "news":
                $news = $plugins->get_module('news', false, true);
                $news->clear();
                break;
            case "bans":
                $r = $db->query('SELECT id FROM bans');
                while (list($id) = $db->fetch_row($r))
                    $etc->unban_user(null, $id);
                break;
            case "warnings":
                $r = $db->query('SELECT id FROM warnings');
                while (list($id) = $db->fetch_row($r))
                    $etc->unwarn_user(null, null, $id);
                break;
        }
        log_add('system_clean', 'admin', array($lang->v('main_page_clear_' . $act), $act));
        die("OK!");
    }

}

?>