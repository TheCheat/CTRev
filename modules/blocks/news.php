<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/news.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок новости
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class news_block {

    /**
     * Инициализация блока новостей
     * @return null
     */
    public function init() {
        if (!config::o()->mstate('news'))
            return;
        lang::o()->get('news');
        $l = (int) config::o()->v('news_max');
        $a = db::o()->cname('news')->query('SELECT n.*, u.username, u.group FROM news AS n
            LEFT JOIN users AS u ON u.id=n.poster_id
            ORDER BY n.posted_time DESC' .
                ($l ? ' LIMIT ' . $l : ""));
        tpl::o()->assign('rows', $a);
        tpl::o()->display('news/index.tpl');
    }

}

?>