<?php

/**
 * Project:             CTRev
 * File:                news.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
        lang::o()->get('news');
        $a = db::o()->query('SELECT n.*, u.username, u.group FROM news AS n
            LEFT JOIN users AS u ON u.id=n.poster_id
            ORDER BY n.posted_time DESC' .
                (config::o()->v('news_max') ? ' LIMIT ' . config::o()->v('news_max') : ""), 'news');
        tpl::o()->assign('rows', $a);
        tpl::o()->display('news/index.tpl');
    }

}

?>