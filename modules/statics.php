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
     * @return null
     */
    public function init() {
        $url = $_GET['page'];
        $r = db::o()->query('SELECT * FROM static WHERE url=' . db::o()->esc($url) . ' LIMIT 1');
        $row = db::o()->fetch_assoc($r);
        if (!$row)
            furl::o()->location('');
        $this->title = $row['title'];
        $content = ($row['bbcode'] ? bbcodes::o()->format_text($row['content']) : $row['content']);
        tpl::o()->assign('title', $row['title']);
        tpl::o()->assign('content', $content);
        tpl::o()->display('static.tpl');
    }

}

?>