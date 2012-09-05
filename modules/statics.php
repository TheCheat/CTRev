<?php

/**
 * Project:             CTRev
 * File:                statics.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Стат. страницы
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class statics {

    /**
     * Заголовок стат. страницы
     * @var string
     */
    public $title = '';

    /**
     * Стат. страницы
     * @global db $db
     * @global bbcodes $bbcodes
     * @global furl $furl
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $db, $bbcodes, $furl, $tpl;
        $url = $_GET['page'];
        $r = $db->query('SELECT * FROM static WHERE url=' . $db->esc($url) . ' LIMIT 1');
        $row = $db->fetch_assoc($r);
        if (!$row)
            $furl->location('');
        $this->title = $row['title'];
        $content = ($row['bbcode'] ? $bbcodes->format_text($row['content']) : $row['content']);
        $tpl->assign('title', $row['title']);
        $tpl->assign('content', $content);
        $tpl->display('static.tpl');
    }

}

?>