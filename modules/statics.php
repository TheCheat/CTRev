<?php

/**
 * Project:             CTRev
 * @file                modules/statics.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var string $title
     */
    public $title = '';

    /**
     * Стат. страницы
     * @return null
     */
    public function init() {
        $url = $_GET['page'];
        $r = db::o()->p($url)->query('SELECT * FROM static WHERE url=? LIMIT 1');
        $row = db::o()->fetch_assoc($r);
        if (!$row)
            furl::o()->location('');
        $this->title = $row['title'];
        tpl::o()->assign('title', $row['title']);
        tpl::o()->assign('content', $row['content']);
        tpl::o()->assign('type', $row['type']);
        tpl::o()->display('static.tpl');
    }

}

?>