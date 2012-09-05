<?php

/**
 * Project:             CTRev
 * File:                torrents.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок торренты
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class torrents_block {

    /**
     * Инициализация блока-торрентов
     * @global lang $lang
     * @global plugins $plugins
     * @global users $users
     * @return null
     */
    public function init() {
        global $lang, $plugins, $users;
        $lang->get("torrents");
        if (!$users->perm('torrents'))
            return;
        $torrents = $plugins->get_module("torrents");
        if (!is_callable(array($torrents, "show")))
            return;
        $torrents->show();
    }

}