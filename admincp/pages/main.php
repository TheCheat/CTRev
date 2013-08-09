<?php

/**
 * Project:            	CTRev
 * @file                admincp/pages/main.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Индексная страница АЦ
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class main_page {
    /**
     * Файл sitemap.xml
     */

    const sitemap = "upload/sitemap.xml";

    /**
     * Инициализация индексной страницы АЦ
     * @return null
     */
    public function init() {
        lang::o()->get('admin/pages/main');
        if (!($a = cache::o()->read('admin_stats'))) {
            $uc = db::o()->count_rows('users');
            $tc = db::o()->count_rows('content');
            $cc = db::o()->count_rows('comments');
            $a = array('uc' => $uc, 'tc' => $tc, 'cc' => $cc);
            cache::o()->write($a);
        }
        tpl::o()->assign('sitemap', self::sitemap);
        tpl::o()->assign('PHP_VERSION', PHP_VERSION);
        tpl::o()->assign('MYSQL_VERSION', db::o()->version());
        tpl::o()->assign('row', $a);
        tpl::o()->display('admin/pages/main.tpl');
    }

}

class main_page_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        lang::o()->get('admin/pages/main');
        if (!users::o()->perm('system'))
            return;
        $act = $_GET["act"];
        users::o()->admin_mode();
        /* @var $etc etc */
        $etc = n("etc");
        $all = false;
        switch ($act) {
            case "attachments":
                $all = true;
            case "unattachments":
                /* @var $attach attachments */
                $attach = n("attachments");
                $attach->clear(0, $all);
                break;
            case "sitemap":
                $this->sitemap();
                ok();
                break;
            case "cleanup":
                /* @var $cleanup cleanup */
                $cleanup = n("cleanup");
                $cleanup->execute(true);
                break;
            case "cache":
                cache::o()->clear();
                break;
            case "cache_tpl":
                cache::o()->clear_tpl();
                break;
            case "stats":
                $st = stats::o()->read();
                foreach ($st as $s => $v)
                    stats::o()->write($s, 0);
                break;
            case "logs":
                /* @var $logs logs_man_ajax */
                $logs = plugins::o()->get_module('logs', 1, true);
                $logs->clear();
                break;
            case "peers":
                db::o()->truncate_table('content_peers');
                db::o()->update(array('leechers' => 0, 'seeders' => 0), 'content_torrents');
                break;
            case "downloaded":
                db::o()->truncate_table('content_downloaded');
                db::o()->update(array('downloaded' => 0), 'content_torrents');
                break;
            case "chat":
                /* @var $chat chat */
                $chat = plugins::o()->get_module('chat');
                $chat->truncate();
                break;
            case "pm":
                /* @var $pm messages_ajax */
                $pm = plugins::o()->get_module('messages', false, true);
                $pm->clear();
                break;
            case "ratings":
                $r = db::o()->query('SELECT toid, type FROM ratings GROUP BY toid, type');
                /* @var $rating rating */
                $rating = n("rating");
                while ($row = db::o()->fetch_assoc($r))
                    $rating->change_type($row['type'])->clear($row['toid']);
                break;

            // Далее: Важная часть сайта, да
            case "content":
                $r = db::o()->query('SELECT id FROM content');
                while (list($id) = db::o()->fetch_row($r))
                    try {
                        $etc->delete_content($id);
                    } catch (EngineException $e) {
                        
                    }
                break;
            case "comments":
                /* @var $comments comments */
                $comments = n("comments");
                $comments->clear(null, true);
                break;
            case "polls":
                /* @var $polls polls */
                $polls = n("polls");
                $polls->clear();
                break;
            case "news":
                /* @var $news news_ajax */
                $news = plugins::o()->get_module('news', false, true);
                $news->clear();
                break;
            case "bans":
                $r = db::o()->query('SELECT id FROM bans');
                while (list($id) = db::o()->fetch_row($r))
                    $etc->unban_user(null, $id);
                break;
            case "warnings":
                $r = db::o()->query('SELECT id FROM warnings');
                while (list($id) = db::o()->fetch_row($r))
                    $etc->unwarn_user(null, null, $id);
                break;
        }
        log_add('system_clean', 'admin', array(lang::o()->v('main_page_clear_' . $act), $act));
        ok();
    }

    /**
     * Генератор sitemap.xml
     * @return null
     */
    public function sitemap() {
        $file = main_page::sitemap;
        $r = db::o()->query('SELECT * FROM content');
        tpl::o()->assign('content', db::o()->fetch2array($r));
        $c = tpl::o()->fetch('content/sitemap.xtpl');
        file::o()->write_file($c, $file);
    }

}

?>