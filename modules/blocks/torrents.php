<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/torrents.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        lang::o()->get("torrents");
        if (!users::o()->perm('torrents'))
            return;
        /* @var $torrents torrents */
        $torrents = plugins::o()->get_module("torrents");
        if (!is_callable(array($torrents, "show")))
            return;
        $torrents->show();
    }

}