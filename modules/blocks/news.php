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
     * @global tpl $tpl
     * @global lang $lang
     * @global db $db
     * @global config $config
     * @return null
     */
    public function init() {
        global $tpl, $lang, $db, $config;
        $lang->get('news');
        $a = $db->query('SELECT n.*, u.username, u.group FROM news AS n
            LEFT JOIN users AS u ON u.id=n.poster_id
            ORDER BY n.posted_time DESC' .
                ($config->v('news_max') ? ' LIMIT ' . $config->v('news_max') : ""), 'news');
        $tpl->assign('rows', $a);
        $tpl->display('news/index.tpl');
    }

}

?>